<?php
require_once __DIR__ . '/../dbconfig.inc.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/notify.php'; 

if (session_status() === PHP_SESSION_NONE) session_start();

$pdo = getDatabaseConnection();

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    http_response_code(403);
    exit('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (isset($_POST['approve'])) {
        $id = (int)$_POST['approve'];

        try {
            $stmt = $pdo->prepare("UPDATE flats SET is_approved = 1 WHERE id = :id");
            $stmt->execute([':id' => $id]);

            $q = $pdo->prepare("
                SELECT u.id AS user_id, f.reference_number
                FROM flats f
                JOIN owners o ON o.id = f.owner_id
                JOIN users  u ON u.id = o.user_id
                WHERE f.id = :id
            ");
            $q->execute([':id' => $id]);
            if ($row = $q->fetch()) {
                notify(
                    $pdo,
                    (int)$row['user_id'],
                    'Your flat was approved',
                    'Flat ' . $row['reference_number'] . ' has been approved by manager.',
                    BASE_URL . '/pages/flat_detail.php?id=' . $id
                );
            }

            header('Location: ' . BASE_URL . '/manager/approve_flats.php?ok=1');
            exit;

        } catch (Throwable $th) {
            http_response_code(500);
            exit('Error approving flat: ' . e($th->getMessage()));
        }
    }

    if (isset($_POST['remove'])) {
        $id = (int)$_POST['remove'];

        $info = $pdo->prepare("
            SELECT u.id AS user_id, f.reference_number
            FROM flats f
            JOIN owners o ON o.id = f.owner_id
            JOIN users  u ON u.id = o.user_id
            WHERE f.id = :id
        ");
        $info->execute([':id' => $id]);
        $ownerInfo = $info->fetch();

        $pdo->beginTransaction();
        try {
  
            $pdo->prepare("DELETE FROM images        WHERE flat_id = :id")->execute([':id' => $id]);
            $pdo->prepare("DELETE FROM viewing_times WHERE flat_id = :id")->execute([':id' => $id]);
            $pdo->prepare("DELETE FROM flats         WHERE id      = :id")->execute([':id' => $id]);

            $pdo->commit();

            if ($ownerInfo) {
                notify(
                    $pdo,
                    (int)$ownerInfo['user_id'],
                    'Flat removed by manager',
                    'Your flat ' . $ownerInfo['reference_number'] . ' was removed by the manager.',
                    BASE_URL . '/owner/view_my_flats.php'
                );
            }

            header('Location: ' . BASE_URL . '/manager/approve_flats.php?removed=1');
            exit;

        } catch (Throwable $th) {
            $pdo->rollBack();
            http_response_code(500);
            exit('Error removing flat: ' . e($th->getMessage()));
        }
    }
}

$perPage = 20;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$total = (int)$pdo->query("SELECT COUNT(*) FROM flats WHERE is_approved = 0")->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

$stmt = $pdo->prepare("
    SELECT id, reference_number, location, rent_cost
    FROM flats
    WHERE is_approved = 0
    ORDER BY id DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

$baseListUrl = rtrim(BASE_URL, '/') . '/manager/approve_flats.php';
$prevUrl = $page > 1 ? $baseListUrl . '?page=' . ($page - 1) : null;
$nextUrl = $page < $pages ? $baseListUrl . '?page=' . ($page + 1) : null;

$prefix = rtrim(BASE_URL, '/') . '/';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Approve Flats · Manager</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= e(BASE_URL) ?>/style.css">
  <style>
    .pager {display:flex; gap:8px; align-items:center; justify-content:center; margin:16px 0;}
    .pager .btn {padding:8px 12px;}
    .stats {margin:8px 0 16px; color:#555;}
    .table td .btn {padding:6px 10px; font-size:14px;}
    .btn-view {background:#e5e7eb; border:1px solid #d1d5db;}
    .btn-approve {background:#10b981; color:#fff; border:0;}
    .btn-danger {background:#ef4444; color:#fff; border:0;}
  </style>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="container">
  <h1>Pending Flats for Approval</h1>

  <?php if (isset($_GET['ok'])): ?>
    <div class="notice success">Approved successfully.</div>
  <?php endif; ?>
  <?php if (isset($_GET['removed'])): ?>
    <div class="notice success">Removed successfully.</div>
  <?php endif; ?>

  <div class="stats">
    <strong>Total pending:</strong> <?= (int)$total ?> &middot;
    <strong>Page:</strong> <?= (int)$page ?> / <?= (int)$pages ?>
  </div>

  <?php if (!$rows): ?>
    <p>No pending flats.</p>
  <?php else: ?>
    <div class="pager">
      <?php if ($prevUrl): ?><a class="btn" href="<?= e($prevUrl) ?>">&larr; Prev</a><?php endif; ?>
      <span>Page <?= (int)$page ?> of <?= (int)$pages ?></span>
      <?php if ($nextUrl): ?><a class="btn" href="<?= e($nextUrl) ?>">Next &rarr;</a><?php endif; ?>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>#</th><th>Reference</th><th>Location</th><th>Rent</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= e($r['reference_number']) ?></td>
            <td><?= e($r['location']) ?></td>
            <td><?= number_format((float)$r['rent_cost']) ?></td>
            <td style="display:flex; gap:8px; flex-wrap:wrap;">
              <a class="btn btn-view" href="<?= e($prefix) ?>pages/flat_detail.php?id=<?= (int)$r['id'] ?>">View</a>

              <form method="post" action="<?= e($baseListUrl) ?>" style="display:inline;">
                <?php csrf_field(); ?>
                <input type="hidden" name="approve" value="<?= (int)$r['id'] ?>">
                <button type="submit" class="btn btn-approve">Approve</button>
              </form>

              <form method="post" action="<?= e($baseListUrl) ?>" style="display:inline;" onsubmit="return confirm('Remove this flat?');">
                <?php csrf_field(); ?>
                <input type="hidden" name="remove" value="<?= (int)$r['id'] ?>">
                <button type="submit" class="btn btn-danger">Remove</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="pager">
      <?php if ($prevUrl): ?><a class="btn" href="<?= e($prevUrl) ?>">&larr; Prev</a><?php endif; ?>
      <span>Page <?= (int)$page ?> of <?= (int)$pages ?></span>
      <?php if ($nextUrl): ?><a class="btn" href="<?= e($nextUrl) ?>">Next &rarr;</a><?php endif; ?>
    </div>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
