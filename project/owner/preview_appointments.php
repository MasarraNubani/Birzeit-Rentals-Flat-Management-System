<?php
// owner/preview_appointments.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../dbconfig.inc.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/notify.php';

if (empty($_SESSION['user']) || (($_SESSION['role'] ?? ($_SESSION['user']['role'] ?? null)) !== 'owner')) {
    header('Location: ../auth/login.php');
    exit;
}

$pdo = getDatabaseConnection();
$user_id = (int)$_SESSION['user']['id'];

$stmt = $pdo->prepare("SELECT id FROM owners WHERE user_id = :uid");
$stmt->execute([':uid' => $user_id]);
$ownerRow = $stmt->fetch();
if (!$ownerRow) { die("❌ Owner profile not found!"); }
$owner_id = (int)$ownerRow['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $appt_id = (int)($_POST['accept'] ?? $_POST['reject'] ?? 0);
    if ($appt_id <= 0) { $_SESSION['flash'] = 'Bad request.'; header('Location: preview_appointments.php'); exit; }

    $q = $pdo->prepare("
        SELECT a.id, a.is_confirmed, a.appointment_date, a.appointment_time,
               f.id AS flat_id, f.reference_number, f.owner_id,
               c.id AS customer_id, u.id AS customer_user_id
        FROM appointments a
        JOIN flats f     ON f.id = a.flat_id
        JOIN customers c ON c.id = a.customer_id
        JOIN users u     ON u.id = c.user_id
        WHERE a.id = :aid AND f.owner_id = :oid
        LIMIT 1
    ");
    $q->execute([':aid' => $appt_id, ':oid' => $owner_id]);
    $appt = $q->fetch();

    if (!$appt) {
        $_SESSION['flash'] = 'Appointment not found.';
        header('Location: preview_appointments.php'); exit;
    }

    if (isset($_POST['accept'])) {
        if ((int)$appt['is_confirmed'] === 1) {
            $_SESSION['flash'] = 'Appointment already confirmed.';
            header('Location: preview_appointments.php'); exit;
        }

        $up = $pdo->prepare("UPDATE appointments SET is_confirmed = 1 WHERE id = :aid");
        $up->execute([':aid' => $appt_id]);

        if (!empty($appt['customer_user_id'])) {
            $date = $appt['appointment_date'];
            $time = substr((string)$appt['appointment_time'], 0, 5);
            notify(
                $pdo,
                (int)$appt['customer_user_id'],
                'Viewing confirmed',
                'Your viewing for flat '.$appt['reference_number'].' is confirmed on '.$date.' at '.$time.'.',
                BASE_URL . '/pages/flat_detail.php?ref=' . urlencode($appt['reference_number'])
            );
        }

        $_SESSION['flash'] = 'Appointment accepted successfully!';
        header('Location: preview_appointments.php'); exit;
    }

    if (isset($_POST['reject'])) {
        if ((int)$appt['is_confirmed'] === 1) {
            $_SESSION['flash'] = 'Cannot reject a confirmed appointment.';
            header('Location: preview_appointments.php'); exit;
        }

        try {
            $pdo->beginTransaction();


            $free = $pdo->prepare("
                UPDATE viewing_times vt
                JOIN flats f ON f.id = vt.flat_id
                SET vt.is_booked = 0
                WHERE f.id = :fid
                  AND vt.day_of_week = DAYNAME(:d)
                  AND vt.time_from   = :t
                  AND vt.is_booked   = 1
                LIMIT 1
            ");
            $free->execute([
                ':fid' => (int)$appt['flat_id'],
                ':d'   => $appt['appointment_date'],
                ':t'   => $appt['appointment_time']
            ]);

            $del = $pdo->prepare("DELETE FROM appointments WHERE id = :aid");
            $del->execute([':aid' => $appt_id]);

            $pdo->commit();

            if (!empty($appt['customer_user_id'])) {
                $date = $appt['appointment_date'];
                $time = substr((string)$appt['appointment_time'], 0, 5);
                notify(
                    $pdo,
                    (int)$appt['customer_user_id'],
                    'Viewing declined',
                    'Your viewing request for flat '.$appt['reference_number'].' on '.$date.' at '.$time.' was declined by the owner.',
                    BASE_URL . '/pages/flat_detail.php?ref=' . urlencode($appt['reference_number'])
                );
            }

            $_SESSION['flash'] = 'Appointment rejected successfully.';
            header('Location: preview_appointments.php'); exit;

        } catch (Throwable $th) {
            $pdo->rollBack();
            $_SESSION['flash'] = 'Error: ' . htmlspecialchars($th->getMessage(), ENT_QUOTES, 'UTF-8');
            header('Location: preview_appointments.php'); exit;
        }
    }
}

$query = "
    SELECT 
        a.id AS appointment_id,
        f.reference_number,
        f.location,
        a.appointment_date,
        a.appointment_time,
        a.is_confirmed,
        c.name  AS customer_name,
        c.mobile_number AS customer_mobile,
        c.email AS customer_email
    FROM appointments a
    JOIN flats f     ON a.flat_id = f.id
    JOIN customers c ON a.customer_id = c.id
    WHERE f.owner_id = :owner_id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
";
$stmt2 = $pdo->prepare($query);
$stmt2->execute([':owner_id' => $owner_id]);
$appointments = $stmt2->fetchAll();

$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Preview Appointments</title>
    <link rel="stylesheet" href="../style.css">
    <style>
      .notice{padding:10px;border-radius:10px;margin:10px 0}
      .notice.success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
      .notice.error{background:#fef2f2;color:#7f1d1d;border:1px solid #fecaca}
      .btn-accept{background:#10b981;color:#fff;border:0;padding:6px 10px;border-radius:8px}
      .btn-reject{background:#ef4444;color:#fff;border:0;padding:6px 10px;border-radius:8px}
      .btn-accept:disabled,.btn-reject:disabled{opacity:.6;cursor:not-allowed}
      .actions{display:flex;gap:8px;flex-wrap:wrap}
    </style>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container about-box">
    <h1>Preview Appointments</h1>

    <?php if ($flash): ?>
      <div class="notice success"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (count($appointments) === 0): ?>
        <p class="no-appointments">No appointment requests yet.</p>
    <?php else: ?>
        <section class="search-results">
            <table class="search-table" aria-label="Appointments">
                <thead>
                    <tr>
                        <th>Ref.</th>
                        <th>Flat Location</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Customer</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($appointments as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['reference_number']) ?></td>
                        <td><?= htmlspecialchars($a['location']) ?></td>
                        <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                        <td><?= htmlspecialchars(substr($a['appointment_time'], 0, 5)) ?></td>
                        <td><?= htmlspecialchars($a['customer_name']) ?></td>
                        <td><?= htmlspecialchars($a['customer_mobile']) ?></td>
                        <td><?= htmlspecialchars($a['customer_email']) ?></td>
                        <td>
                            <?php if ($a['is_confirmed']): ?>
                                <span class="status-confirmed">Confirmed</span>
                            <?php else: ?>
                                <span class="status-pending">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                          <?php if (!$a['is_confirmed']): ?>
                            <div class="actions">
                              <form method="post" action="preview_appointments.php">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="accept" value="<?= (int)$a['appointment_id'] ?>">
                                <button type="submit" class="btn-accept">Accept</button>
                              </form>

                              <form method="post" action="preview_appointments.php" onsubmit="return confirm('Reject this appointment?');">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="reject" value="<?= (int)$a['appointment_id'] ?>">
                                <button type="submit" class="btn-reject">Reject</button>
                              </form>
                            </div>
                          <?php else: ?>
                            <span>—</span>
                          <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
