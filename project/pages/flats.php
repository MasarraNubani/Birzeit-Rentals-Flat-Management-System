<?php
require_once __DIR__ . '/../dbconfig.inc.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$pdo = getDatabaseConnection();

$sql = "
  SELECT 
    f.id, f.reference_number, f.location, f.address, f.rent_cost, 
    f.bedrooms, f.bathrooms,
    (
      SELECT image_path 
      FROM images i 
      WHERE i.flat_id = f.id 
      ORDER BY i.id ASC 
      LIMIT 1
    ) AS cover_image
  FROM flats f
  WHERE f.is_approved = 1
  ORDER BY f.id DESC
  LIMIT 60
";
$rows = $pdo->query($sql)->fetchAll();

$BASE = rtrim(BASE_URL, '/') . '/';
$placeholder = $BASE . 'assets/no-image.png'; 
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Available Flats</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= e(BASE_URL) ?>/style.css">
  <style>
    .cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;margin-top:16px}
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,.04)}
    .card .thumb{width:100%;height:170px;object-fit:cover;display:block;background:#f3f4f6}
    .card .body{padding:12px 14px}
    .card h3{margin:0 0 8px;font-size:18px}
    .meta{color:#6b7280;font-size:14px;margin:6px 0}
    .price{font-weight:600}
    .card .actions{padding:12px 14px;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end}
    .btn{display:inline-block;padding:8px 12px;border-radius:10px;text-decoration:none}
    .btn-view{background:#111827;color:#fff}
  </style>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="container">
  <h1>Available Flats</h1>

  <?php if (!$rows): ?>
    <p>No flats available.</p>
  <?php else: ?>
    <div class="cards">
      <?php foreach ($rows as $r):
        $img = $r['cover_image'];
        if ($img && strpos($img, 'http') !== 0) {
          $img = $BASE . ltrim($img, '/');
        }
        if (!$img) $img = $placeholder;
      ?>
        <article class="card">
          <img class="thumb" src="<?= e($img) ?>" alt="Flat image">
          <div class="body">
            <h3><?= e($r['reference_number'] ?? 'Flat') ?></h3>
            <div class="meta"><?= e($r['location']) ?></div>
            <div class="meta"><span class="price"><?= number_format((float)$r['rent_cost']) ?></span> / month</div>
            <div class="meta">Beds: <?= (int)$r['bedrooms'] ?> • Baths: <?= (int)$r['bathrooms'] ?></div>
          </div>
          <div class="actions">
            <a class="btn btn-view" href="<?= e($BASE) ?>pages/flat_detail.php?id=<?= (int)$r['id'] ?>">View</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
