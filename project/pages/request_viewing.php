<?php
require_once __DIR__ . '/../dbconfig.inc.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/notify.php';
if (session_status()===PHP_SESSION_NONE) session_start();

$pdo  = getDatabaseConnection();
$role = $_SESSION['role'] ?? ($_SESSION['user']['role'] ?? null);

if (empty($_SESSION['user']) || $role !== 'customer') {
  header('Location: ' . BASE_URL . '/auth/login.php'); exit;
}

$uid = (int)$_SESSION['user']['id'];
$st  = $pdo->prepare("SELECT id FROM customers WHERE user_id = :u");
$st->execute([':u'=>$uid]);
$customer = $st->fetch();
if (!$customer) { http_response_code(403); exit('Customer profile not found'); }
$customer_id = (int)$customer['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();

  $flat_id = (int)($_POST['flat_id'] ?? 0);
  $slot_id = (int)($_POST['slot_id'] ?? 0);
  $date    = trim($_POST['date'] ?? '');
  $time    = trim($_POST['time'] ?? '');

  if ($flat_id <= 0) { http_response_code(400); exit('Bad request'); }

  $qFlat = $pdo->prepare("
    SELECT f.id, f.reference_number, f.is_approved,
           u.id AS owner_user_id
    FROM flats f
    JOIN owners o ON o.id = f.owner_id
    JOIN users  u ON u.id = o.user_id
    WHERE f.id = :fid
  ");
  $qFlat->execute([':fid'=>$flat_id]);
  $flatInfo = $qFlat->fetch();
  if (!$flatInfo) {
    $_SESSION['flash'] = 'Flat not found.';
    header('Location: '.BASE_URL.'/pages/flats.php'); exit;
  }
  // if (!$flatInfo['is_approved']) { ... }

  if ($slot_id > 0) {
    $st = $pdo->prepare("SELECT id, day_of_week, time_from, is_booked FROM viewing_times WHERE id=:sid AND flat_id=:fid");
    $st->execute([':sid'=>$slot_id, ':fid'=>$flat_id]);
    $slot = $st->fetch();
    if (!$slot || $slot['is_booked']) {
      $_SESSION['flash']='Slot unavailable';
      header('Location: '.BASE_URL.'/pages/flat_detail.php?id='.$flat_id); exit;
    }

    $days = ['Sunday'=>0,'Monday'=>1,'Tuesday'=>2,'Wednesday'=>3,'Thursday'=>4,'Friday'=>5,'Saturday'=>6];
    $targetDow = $days[$slot['day_of_week']] ?? null;
    if ($targetDow === null) {
      $_SESSION['flash']='Invalid slot';
      header('Location: '.BASE_URL.'/pages/flat_detail.php?id='.$flat_id); exit;
    }
    $todayDow = (int)date('w');
    $delta = ($targetDow - $todayDow + 7) % 7;
    if ($delta === 0 && date('H:i:s') > $slot['time_from']) $delta = 7;
    $date = date('Y-m-d', strtotime("+$delta day"));
    $time = $slot['time_from'];

    try{
      $pdo->beginTransaction();

      $dup = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE flat_id=:fid AND customer_id=:cid AND appointment_date=:d AND appointment_time=:t");
      $dup->execute([':fid'=>$flat_id, ':cid'=>$customer_id, ':d'=>$date, ':t'=>$time]);
      if ((int)$dup->fetchColumn() > 0) {
        $pdo->rollBack();
        $_SESSION['flash']='You already booked this slot.';
        header('Location: '.BASE_URL.'/pages/flat_detail.php?id='.$flat_id); exit;
      }

      $pdo->prepare("INSERT INTO appointments (customer_id, flat_id, appointment_date, appointment_time, is_confirmed)
                     VALUES (:cid,:fid,:d,:t,0)")
          ->execute([':cid'=>$customer_id, ':fid'=>$flat_id, ':d'=>$date, ':t'=>$time]);
      $pdo->prepare("UPDATE viewing_times SET is_booked=1 WHERE id=:sid")->execute([':sid'=>$slot_id]);

      $pdo->commit();
      $_SESSION['flash']='Appointment booked on '.$date.' at '.substr($time,0,5);

      if (!empty($flatInfo['owner_user_id'])) {
        notify(
          $pdo,
          (int)$flatInfo['owner_user_id'],
          'New viewing request',
          'A customer booked a viewing for flat '.$flatInfo['reference_number'].' on '.$date.' '.substr($time,0,5).'.',
          BASE_URL.'/owner/preview_appointments.php'
        );
      }
      notify(
        $pdo,
        (int)$uid,
        'Viewing booked',
        'Your viewing for flat '.$flatInfo['reference_number'].' is booked on '.$date.' '.substr($time,0,5).'.',
        BASE_URL.'/pages/flat_detail.php?id='.$flat_id
      );

    }catch(Throwable $e){
      $pdo->rollBack();
      $_SESSION['flash']='Error: '.e($e->getMessage());
    }

  } else {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date) || !preg_match('/^\d{2}:\d{2}/',$time)) {
      $_SESSION['flash']='Please select date and time.';
      header('Location: '.BASE_URL.'/pages/flat_detail.php?id='.$flat_id); exit;
    }

    $conf = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE flat_id=:fid AND appointment_date=:d AND appointment_time=:t");
    $conf->execute([':fid'=>$flat_id, ':d'=>$date, ':t'=>$time]);
    if ((int)$conf->fetchColumn() > 0) {
      $_SESSION['flash']='Selected time is already booked.';
      header('Location: '.BASE_URL.'/pages/flat_detail.php?id='.$flat_id); exit;
    }

    $pdo->prepare("INSERT INTO appointments (customer_id, flat_id, appointment_date, appointment_time, is_confirmed)
                   VALUES (:cid,:fid,:d,:t,0)")
        ->execute([':cid'=>$customer_id, ':fid'=>$flat_id, ':d'=>$date, ':t'=>$time]);

    $_SESSION['flash']='Appointment request sent for '.$date.' at '.$time;

    if (!empty($flatInfo['owner_user_id'])) {
      notify(
        $pdo,
        (int)$flatInfo['owner_user_id'],
        'New viewing request',
        'A customer requested a viewing for flat '.$flatInfo['reference_number'].' on '.$date.' '.substr($time,0,5).'.',
        BASE_URL.'/owner/preview_appointments.php'
      );
    }
    notify(
      $pdo,
      (int)$uid,
      'Viewing request submitted',
      'Your viewing request for flat '.$flatInfo['reference_number'].' was submitted for '.$date.' '.substr($time,0,5).'.',
      BASE_URL.'/pages/flat_detail.php?id='.$flat_id
    );
  }

  header('Location: ' . BASE_URL . '/pages/flat_detail.php?id=' . $flat_id); exit;
}

http_response_code(405); echo 'Method not allowed';
