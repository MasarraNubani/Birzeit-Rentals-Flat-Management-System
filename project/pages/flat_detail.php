<?php
require_once __DIR__ . '/../dbconfig.inc.php';
require_once __DIR__ . '/../includes/csrf.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$pdo = getDatabaseConnection();

$flatId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$ref    = isset($_GET['ref']) ? trim($_GET['ref']) : '';

$isManager  = (($_SESSION['role'] ?? ($_SESSION['user']['role'] ?? null)) === 'manager');
$role       = $_SESSION['role'] ?? ($_SESSION['user']['role'] ?? null);
$isCustomer = ($role === 'customer');

if ($flatId > 0) {
    $sql = "SELECT f.*, o.name AS owner_name
            FROM flats f
            JOIN owners o ON o.id = f.owner_id
            WHERE f.id = :id " . ($isManager ? "" : "AND f.is_approved = 1");
    $st = $pdo->prepare($sql);
    $st->execute([':id' => $flatId]);
    $flat = $st->fetch();
} elseif ($ref !== '') {
    $sql = "SELECT f.*, o.name AS owner_name
            FROM flats f
            JOIN owners o ON o.id = f.owner_id
            WHERE f.reference_number = :ref " . ($isManager ? "" : "AND f.is_approved = 1");
    $st = $pdo->prepare($sql);
    $st->execute([':ref' => $ref]);
    $flat = $st->fetch();
    $flatId = $flat['id'] ?? 0;
} else {
    $flat = null;
}

if (!$flat) {
    http_response_code(404);
    $pageTitle = 'Flat Not Found';
} else {
    $pageTitle = 'Flat ' . e($flat['reference_number'] ?? '#');
}

$images = [];
if ($flatId) {
    $st = $pdo->prepare("SELECT image_path, caption FROM images WHERE flat_id = :id ORDER BY id ASC");
    $st->execute([':id' => $flatId]);
    $images = $st->fetchAll();
}

$marketing = [];
if ($flatId) {
    $st = $pdo->prepare("SELECT title, description, url FROM marketing_info WHERE flat_id = :id ORDER BY id ASC");
    $st->execute([':id' => $flatId]);
    $marketing = $st->fetchAll();
}

$viewing = [];
if ($flatId) {
    $st = $pdo->prepare("
        SELECT id, day_of_week, time_from, time_to, contact_phone, is_booked
        FROM viewing_times
        WHERE flat_id = :id
        ORDER BY FIELD(day_of_week,'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), time_from
    ");
    $st->execute([':id' => $flatId]);
    $viewing = $st->fetchAll();
}

$BASE  = rtrim(BASE_URL, '/') . '/';
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);

$ownerDelete = false;
if (!empty($_SESSION['user'])) {
  $userRole = $_SESSION['role'] ?? ($_SESSION['user']['role'] ?? null);
  if ($userRole === 'manager') {
    $ownerDelete = true;
  } elseif ($userRole === 'owner') {
    $stOwn = $pdo->prepare("SELECT id FROM owners WHERE user_id = :uid");
    $stOwn->execute([':uid' => (int)$_SESSION['user']['id']]);
    $ownRow = $stOwn->fetch();
    if ($ownRow && (int)$ownRow['id'] === (int)($flat['owner_id'] ?? 0)) {
      $ownerDelete = true;
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= e($pageTitle) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= e(BASE_URL) ?>/style.css">
  <style>
    .gallery{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;margin:12px 0}
    .gallery img{width:100%;height:180px;object-fit:cover;border-radius:10px}
    .meta{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;margin:12px 0}
    .meta .card{padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff}
    .badge{display:inline-block;background:#eee;color:#333;padding:3px 8px;border-radius:999px;font-size:12px;margin-right:6px}
    .viewing-table{width:100%;border-collapse:collapse;margin-top:10px}
    .viewing-table th,.viewing-table td{border:1px solid #e5e7eb;padding:8px;text-align:left}
    .viewing-table th{background:#f9fafb}
    .actions .btn{margin-right:8px}
    .notice{padding:10px;border-radius:10px;margin:10px 0}
    .notice.success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
    .notice.error{background:#fef2f2;color:#7f1d1d;border:1px solid #fecaca}
  </style>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="container">
  <?php if ($flash): ?>
    <div class="notice success"><?= e($flash) ?></div>
  <?php endif; ?>

  <?php if (!$flat): ?>
    <h1>Flat Not Found</h1>
    <p>The flat you requested does not exist or is not approved yet.</p>
  <?php else: ?>
    <h1>Flat <?= e($flat['reference_number']) ?> — <?= e($flat['location']) ?></h1>

    <?php if ($images): ?>
      <div class="gallery">
        <?php foreach ($images as $img):
          $src = $img['image_path'];
          if (strpos($src, 'http://') !== 0 && strpos($src, 'https://') !== 0) {
              $src = $BASE . ltrim($src, '/');
          }
        ?>
          <figure>
            <img src="<?= e($src) ?>" alt="<?= e($img['caption'] ?? 'Flat image') ?>">
            <?php if (!empty($img['caption'])): ?>
              <figcaption><?= e($img['caption']) ?></figcaption>
            <?php endif; ?>
          </figure>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p><em>No images uploaded for this flat.</em></p>
    <?php endif; ?>

    <div class="meta">
      <div class="card">
        <h3>Basic Info</h3>
        <p><strong>Address:</strong> <?= e($flat['address']) ?></p>
        <p><strong>Rent:</strong> <?= number_format((float)$flat['rent_cost'], 2) ?> / month</p>
        <p><strong>Available:</strong> <?= e($flat['available_from']) ?><?php if(!empty($flat['available_to'])): ?> → <?= e($flat['available_to']) ?><?php endif; ?></p>
        <p>
          <span class="badge"><?= (int)$flat['bedrooms'] ?> bd</span>
          <span class="badge"><?= (int)$flat['bathrooms'] ?> ba</span>
          <?php if (!is_null($flat['size_sqm'])): ?><span class="badge"><?= (float)$flat['size_sqm'] ?> sqm</span><?php endif; ?>
        </p>
        <p>
          <?php if ($flat['is_furnished']): ?><span class="badge">Furnished</span><?php endif; ?>
          <?php if ($flat['has_heating']): ?><span class="badge">Heating</span><?php endif; ?>
          <?php if ($flat['has_air_conditioning']): ?><span class="badge">A/C</span><?php endif; ?>
          <?php if ($flat['has_access_control']): ?><span class="badge">Access control</span><?php endif; ?>
          <?php if ($flat['has_parking']): ?><span class="badge">Parking</span><?php endif; ?>
          <?php if ($flat['has_storage']): ?><span class="badge">Storage</span><?php endif; ?>
          <?php if ($flat['has_playground']): ?><span class="badge">Playground</span><?php endif; ?>
          <?php if (!empty($flat['backyard_type']) && $flat['backyard_type']!=='none'): ?><span class="badge">Backyard: <?= e($flat['backyard_type']) ?></span><?php endif; ?>
        </p>
      </div>

      <div class="card">
        <h3>Owner</h3>
        <p><?= e($flat['owner_name'] ?? 'Owner') ?></p>
      </div>

      <?php if (!empty($flat['description'])): ?>
      <div class="card">
        <h3>Description</h3>
        <p><?= nl2br(e($flat['description'])) ?></p>
      </div>
      <?php endif; ?>
    </div>

    <section>
      <h2>Marketing</h2>
      <?php if (!$marketing): ?>
        <p><em>No marketing information.</em></p>
      <?php else: ?>
        <ul>
          <?php foreach ($marketing as $m): ?>
            <li>
              <strong><?= e($m['title']) ?></strong>
              <?php if (!empty($m['url'])): ?>
                — <a href="<?= e($m['url']) ?>" target="_blank" rel="noopener">link</a>
              <?php endif; ?>
              <?php if (!empty($m['description'])): ?>
                <div><?= nl2br(e($m['description'])) ?></div>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <section>
      <h2>Viewing Times</h2>
      <?php if (!$viewing): ?>
        <p><em>No viewing times provided.</em></p>
      <?php else: ?>
        <table class="viewing-table">
          <thead><tr><th>Day</th><th>From</th><th>To</th><th>Phone</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
          <?php foreach ($viewing as $v): ?>
            <tr>
              <td><?= e($v['day_of_week']) ?></td>
              <td><?= e(substr($v['time_from'],0,5)) ?></td>
              <td><?= e(substr($v['time_to'],0,5)) ?></td>
              <td><?= e($v['contact_phone']) ?></td>
              <td><?= $v['is_booked'] ? 'Booked' : 'Available' ?></td>
              <td>
              <?php if ($isCustomer && !$v['is_booked']): ?>
                <form method="post" action="<?= e($BASE) ?>pages/request_viewing.php" style="display:inline">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="flat_id" value="<?= (int)$flatId ?>">
                  <input type="hidden" name="slot_id" value="<?= (int)$v['id'] ?>">
                  <button class="btn">Book</button>
                </form>
              <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>

    <?php if ($isCustomer): ?>
      <section class="actions" style="margin-top:16px;padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff">
        <h2>Actions</h2>
        <!-- Rent Now -->
        <form method="post" action="<?= e($BASE) ?>pages/rent.php" style="display:inline-block;margin-left:8px">
          <?php csrf_field(); ?>
          <input type="hidden" name="flat_id" value="<?= (int)$flatId ?>">
          <label>Start <input type="date" name="rental_start" required min="<?= e($flat['available_from']) ?>" <?= !empty($flat['available_to'])?'max="'.e($flat['available_to']).'"':''; ?>></label>
          <label>End <input type="date" name="rental_end" required  min="<?= e($flat['available_from']) ?>" <?= !empty($flat['available_to'])?'max="'.e($flat['available_to']).'"':''; ?>></label>
          <button class="btn">Rent Now</button>
        </form>
      </section>
    <?php endif; ?>

    <p style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap;">
      <a class="btn" href="<?= e(BASE_URL) ?>/pages/flats.php">Back to list</a>
      <?php if ($isManager): ?>
        <a class="btn" href="<?= e(BASE_URL) ?>/manager/approve_flats.php">Pending list</a>
      <?php endif; ?>

      <?php if ($ownerDelete): ?>
        <form method="post"
              action="<?= e($BASE) ?>owner/delete_flat.php"
              onsubmit="return confirm('Delete this flat? This cannot be undone.');"
              style="display:inline-block;">
          <?php csrf_field(); ?>
          <input type="hidden" name="id" value="<?= (int)$flatId ?>">
          <button class="btn btn-danger">Delete this flat</button>
        </form>
      <?php endif; ?>
    </p>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
