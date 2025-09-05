<?php
require_once __DIR__ . '/../dbconfig.inc.php';
if (session_status()===PHP_SESSION_NONE) session_start();

$pdo = getDatabaseConnection();

$sortable = ['reference_number','rent_cost','available_from','location','bedrooms','bathrooms'];
$sort = $_GET['sort'] ?? ($_COOKIE['flat_sort'] ?? 'rent_cost');
$sort = in_array($sort, $sortable, true) ? $sort : 'rent_cost';

$dir = strtoupper($_GET['dir'] ?? ($_COOKIE['flat_dir'] ?? 'ASC'));
$dir = in_array($dir, ['ASC','DESC'], true) ? $dir : 'ASC';

setcookie('flat_sort', $sort, time() + 30*86400, '/');
setcookie('flat_dir',  $dir,  time() + 30*86400, '/');

$sql = "SELECT id, reference_number, location, rent_cost, bedrooms, bathrooms
        FROM flats WHERE is_approved = 1";
$params = [];
$filter = [];

if (!empty($_GET['location'])) {
    $filter[] = "location LIKE :loc";
    $params[':loc'] = '%'.$_GET['location'].'%';
}
if (!empty($_GET['min_price'])) {
    $filter[] = "rent_cost >= :minp";
    $params[':minp'] = (float)$_GET['min_price'];
}
if (!empty($_GET['max_price'])) {
    $filter[] = "rent_cost <= :maxp";
    $params[':maxp'] = (float)$_GET['max_price'];
}
if (!empty($_GET['bedrooms'])) {
    $filter[] = "bedrooms = :beds";
    $params[':beds'] = (int)$_GET['bedrooms'];
}
if (isset($_GET['is_furnished']) && $_GET['is_furnished'] !== '') {
    $filter[] = "is_furnished = :f";
    $params[':f'] = (int)$_GET['is_furnished'];
}
if ($filter) $sql .= ' AND ' . implode(' AND ', $filter);

$sql .= " ORDER BY $sort $dir LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Search Flats</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= e(BASE_URL) ?>/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="container">
  <h1 class="page-title">Search Results</h1>

  <form method="get" action="<?= e(BASE_URL) ?>/pages/search.php" class="search-form">
    <input type="text" name="location" placeholder="Location" value="<?= e($_GET['location'] ?? '') ?>">
    <input type="number" name="min_price" placeholder="Min price" value="<?= e($_GET['min_price'] ?? '') ?>">
    <input type="number" name="max_price" placeholder="Max price" value="<?= e($_GET['max_price'] ?? '') ?>">
    <input type="number" name="bedrooms" placeholder="Bedrooms" value="<?= e($_GET['bedrooms'] ?? '') ?>">
    <select name="is_furnished">
      <option value="">Furnished?</option>
      <option value="1" <?= (($_GET['is_furnished'] ?? '')==='1')?'selected':''; ?>>Yes</option>
      <option value="0" <?= (($_GET['is_furnished'] ?? '')==='0')?'selected':''; ?>>No</option>
    </select>
    <select name="sort">
      <?php foreach ($sortable as $s): ?>
        <option value="<?= e($s) ?>" <?= $sort===$s?'selected':''; ?>>Sort by: <?= e($s) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="dir">
      <option <?= $dir==='ASC'?'selected':''; ?> value="ASC">ASC</option>
      <option <?= $dir==='DESC'?'selected':''; ?> value="DESC">DESC</option>
    </select>
    <button type="submit">Filter</button>
  </form>

  <?php if (!$rows): ?>
    <p>No flats found.</p>
  <?php else: ?>
    <div class="cards">
      <?php foreach ($rows as $r): ?>
        <div class="card">
          <h3><?= e($r['reference_number'] ?? 'Flat') ?></h3>
          <p><?= e($r['location']) ?> — <?= number_format((float)$r['rent_cost']) ?> / month</p>
          <p>Beds: <?= (int)$r['bedrooms'] ?>, Baths: <?= (int)$r['bathrooms'] ?></p>
          <a class="btn" href="<?= e(BASE_URL) ?>/pages/flat_detail.php?id=<?= (int)$r['id'] ?>">View</a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
