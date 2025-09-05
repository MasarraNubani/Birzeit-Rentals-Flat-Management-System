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
  $start   = $_POST['rental_start'] ?? '';
  $end     = $_POST['rental_end'] ?? '';

  if ($flat_id<=0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$end) || $end < $start) {
    $_SESSION['flash'] = 'Invalid dates.';
    header('Location: ' . BASE_URL . '/pages/flat_detail.php?id=' . $flat_id); exit;
  }

  $st = $pdo->prepare("
      SELECT f.available_from, f.available_to, f.reference_number, f.rent_cost,
             u.id AS owner_user_id
      FROM flats f
      JOIN owners o ON o.id = f.owner_id
      JOIN users  u ON u.id = o.user_id
      WHERE f.id=:id AND f.is_approved=1
  ");
  $st->execute([':id'=>$flat_id]);
  $flat = $st->fetch();
  if (!$flat) { $_SESSION['flash']='Flat not found'; header('Location: '.BASE_URL.'/pages/flats.php'); exit; }

  if ($start < $flat['available_from']) {
    $_SESSION['flash'] = 'Start date must be on/after available-from date.';
    header('Location: ' . BASE_URL . '/pages/flat_detail.php?id=' . $flat_id); exit;
  }
  if (!empty($flat['available_to']) && $end > $flat['available_to']) {
    $_SESSION['flash'] = 'End date exceeds available period.';
    header('Location: ' . BASE_URL . '/pages/flat_detail.php?id=' . $flat_id); exit;
  }

  $sql = "SELECT COUNT(*) FROM rentals
          WHERE flat_id = :fid
            AND NOT (rental_end < :start OR rental_start > :end)";
  $st = $pdo->prepare($sql);
  $st->execute([':fid'=>$flat_id, ':start'=>$start, ':end'=>$end]);
  if ($st->fetchColumn() > 0) {
    $_SESSION['flash'] = 'This flat is already rented in the selected period.';
    header('Location: ' . BASE_URL . '/pages/flat_detail.php?id=' . $flat_id);
    exit;
  }

  $pdo->prepare("INSERT INTO rentals (customer_id, flat_id, rental_start, rental_end, total_amount)
                 VALUES (:cid, :fid, :s, :e, NULL)")
      ->execute([':cid'=>$customer_id, ':fid'=>$flat_id, ':s'=>$start, ':e'=>$end]);

  if (!empty($flat['owner_user_id'])) {
    notify(
      $pdo,
      (int)$flat['owner_user_id'],
      'New rental request',
      'A customer requested to rent flat '.$flat['reference_number'].' from '.$start.' to '.$end.'.',
      BASE_URL.'/owner/view_my_flats.php'
    );
  }

  notify(
    $pdo,
    (int)$uid,
    'Rental request submitted',
    'Your rental request for flat '.$flat['reference_number'].' from '.$start.' to '.$end.' has been submitted.',
    BASE_URL.'/pages/flat_detail.php?id='.$flat_id
  );

  $_SESSION['flash'] = 'Rental request submitted.';
  header('Location: ' . BASE_URL . '/pages/flat_detail.php?id=' . $flat_id);
  exit;
}

http_response_code(405); echo 'Method not allowed';
