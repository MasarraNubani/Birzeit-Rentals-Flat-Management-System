<?php
// manager/inquire_flats.php
if (session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../dbconfig.inc.php';

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    header('Location: ' . BASE_URL . '/auth/login.php'); exit;
}

$pdo = getDatabaseConnection();


$from_date       = trim($_GET['from_date']       ?? ''); 
$to_date         = trim($_GET['to_date']         ?? '');
$location        = trim($_GET['location']        ?? '');
$owner_name      = trim($_GET['owner_name']      ?? '');
$customer_name   = trim($_GET['customer_name']   ?? ''); 
$approved        = trim($_GET['approved']        ?? ''); // '', '1', '0'
$min_price       = trim($_GET['min_price']       ?? '');
$max_price       = trim($_GET['max_price']       ?? '');
$rental_from     = trim($_GET['rental_from']     ?? ''); 
$rental_to       = trim($_GET['rental_to']       ?? '');


$where = [];
$params = [];

if ($from_date !== '') {
    $where[] = 'f.available_from >= :f_from';
    $params[':f_from'] = $from_date;
}
if ($to_date !== '') {
    $where[] = '(f.available_to IS NULL OR f.available_to <= :f_to)';
    $params[':f_to'] = $to_date;
}
if ($location !== '') {
    $where[] = 'f.location LIKE :loc';
    $params[':loc'] = '%' . $location . '%';
}
if ($owner_name !== '') {
    $where[] = 'o.name LIKE :oname';
    $params[':oname'] = '%' . $owner_name . '%';
}
if ($approved === '1' || $approved === '0') {
    $where[] = 'f.is_approved = :ap';
    $params[':ap'] = (int)$approved;
}
if ($min_price !== '' && is_numeric($min_price)) {
    $where[] = 'f.rent_cost >= :minp';
    $params[':minp'] = (float)$min_price;
}
if ($max_price !== '' && is_numeric($max_price)) {
    $where[] = 'f.rent_cost <= :maxp';
    $params[':maxp'] = (float)$max_price;
}


$sql = "
SELECT
  f.id              AS flat_id,
  f.reference_number,
  f.location,
  f.rent_cost,
  f.available_from,
  f.available_to,
  f.is_approved,
  o.id              AS owner_id,
  o.name            AS owner_name,
  rr.rental_start   AS last_rental_start,
  rr.rental_end     AS last_rental_end,
  c.id              AS customer_id,
  c.name            AS customer_name
FROM flats f
JOIN owners o         ON o.id = f.owner_id
LEFT JOIN (
    SELECT r1.*
    FROM rentals r1
    JOIN (
        SELECT flat_id, MAX(rental_start) AS max_start
        FROM rentals
        GROUP BY flat_id
    ) x ON x.flat_id = r1.flat_id AND x.max_start = r1.rental_start
) rr ON rr.flat_id = f.id
LEFT JOIN customers c ON c.id = rr.customer_id
";

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

if ($rental_from !== '' || $rental_to !== '') {
    $sql .= $where ? ' AND ' : ' WHERE ';
    $conds = [];
    if ($rental_from !== '') { $conds[] = 'rr.rental_start >= :r_from'; $params[':r_from'] = $rental_from; }
    if ($rental_to   !== '') { $conds[] = 'rr.rental_end   <= :r_to';   $params[':r_to']   = $rental_to;   }
    $sql .= implode(' AND ', $conds);
}

$sql .= ' ORDER BY f.id DESC';

/* =========================
   Pagination
   ========================= */
$perPage = 20;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$count_sql = "SELECT COUNT(*) FROM (" . $sql . ") t";
$stc = $pdo->prepare($count_sql);
$stc->execute($params);
$total = (int)$stc->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

$sql .= " LIMIT :lim OFFSET :off";
$st = $pdo->prepare($sql);
foreach ($params as $k=>$v) { $st->bindValue($k, $v); }
$st->bindValue(':lim', $perPage, PDO::PARAM_INT);
$st->bindValue(':off', $offset,  PDO::PARAM_INT);
$st->execute();
$rows = $st->fetchAll();

$BASE = rtrim(BASE_URL, '/') . '/';
$baseListUrl = $BASE . 'manager/inquire_flats.php';

function buildPageUrl(array $keep, $pageNum) {
    $keep['page'] = $pageNum;
    return htmlspecialchars($_SERVER['PHP_SELF'] . '?' . http_build_query($keep), ENT_QUOTES, 'UTF-8');
}

$kept = $_GET; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manager - Flats Inquiry</title>
  <link rel="stylesheet" href="<?= e(BASE_URL) ?>/style.css">
  <style>
    .btn-link{display:inline-block;background:#1961a0;color:#fff !important;padding:4px 12px;border-radius:6px;text-decoration:none;font-weight:bold;transition:.2s;margin:0 4px}
    .btn-link:hover{background:#0f3859}
    .status-badge{display:inline-block;border-radius:999px;padding:2px 8px;font-size:12px}
    .status-approved{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
    .status-pending{background:#fff7ed;color:#9a3412;border:1px solid #fed7aa}
    .pager{display:flex;gap:8px;align-items:center;justify-content:center;margin:16px 0}
    .pager .btn{padding:8px 12px}
    .filters .row{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:8px}
    .filters label{display:flex;flex-direction:column;font-size:14px}
    .filters input,.filters select{padding:6px}
  </style>
</head>
<body>
<?php include __DIR__.'/../includes/header.php'; ?>

<main class="container about-box">
  <h1>Flats Inquiry</h1>

  <form method="get" class="filters">
    <div class="row">
      <label>Available From
        <input type="date" name="from_date" value="<?= e($from_date) ?>">
      </label>
      <label>Available To
        <input type="date" name="to_date" value="<?= e($to_date) ?>">
      </label>
      <label>Location
        <input type="text" name="location" placeholder="e.g. Birzeit" value="<?= e($location) ?>">
      </label>
      <label>Owner
        <input type="text" name="owner_name" value="<?= e($owner_name) ?>">
      </label>
      <label>Approved
        <select name="approved">
          <option value="">Any</option>
          <option value="1" <?= $approved==='1'?'selected':''; ?>>Approved</option>
          <option value="0" <?= $approved==='0'?'selected':''; ?>>Pending</option>
        </select>
      </label>
    </div>
    <div class="row">
      <label>Min Price
        <input type="number" step="0.01" name="min_price" value="<?= e($min_price) ?>">
      </label>
      <label>Max Price
        <input type="number" step="0.01" name="max_price" value="<?= e($max_price) ?>">
      </label>
      <label>Rental From (last)
        <input type="date" name="rental_from" value="<?= e($rental_from) ?>">
      </label>
      <label>Rental To (last)
        <input type="date" name="rental_to" value="<?= e($rental_to) ?>">
      </label>
      <label>Customer (last)
        <input type="text" name="customer_name" value="<?= e($customer_name) ?>">
      </label>
    </div>
    <button type="submit" class="btn">Search</button>
  </form>

  <div style="margin:8px 0; color:#555;">
    <strong>Total:</strong> <?= (int)$total ?> &middot; <strong>Page:</strong> <?= (int)$page ?> / <?= (int)$pages ?>
  </div>

  <?php if (!$rows): ?>
    <p style="text-align:center;margin:30px 0;">No results found for the selected filters.</p>
  <?php else: ?>
    <div class="pager">
      <?php if ($page>1): ?>
        <a class="btn" href="<?= buildPageUrl($kept, $page-1) ?>">&larr; Prev</a>
      <?php endif; ?>
      <span>Page <?= (int)$page ?> of <?= (int)$pages ?></span>
      <?php if ($page<$pages): ?>
        <a class="btn" href="<?= buildPageUrl($kept, $page+1) ?>">Next &rarr;</a>
      <?php endif; ?>
    </div>

    <table class="search-table">
      <thead>
      <tr>
        <th>#</th>
        <th>Ref.</th>
        <th>Price</th>
        <th>Location</th>
        <th>Status</th>
        <th>Owner</th>
        <th>Last Rental (Start → End)</th>
        <th>Customer (Last)</th>
        <th>Actions</th>
      </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['flat_id'] ?></td>
          <td><?= e($r['reference_number']) ?></td>
          <td><?= number_format((float)$r['rent_cost'],2) ?></td>
          <td><?= e($r['location']) ?></td>
          <td>
            <?php if ((int)$r['is_approved'] === 1): ?>
              <span class="status-badge status-approved">Approved</span>
            <?php else: ?>
              <span class="status-badge status-pending">Pending</span>
            <?php endif; ?>
          </td>
          <td>
            <a class="btn-link" target="_blank"
               href="<?= e($BASE) ?>manager/user_card.php?type=owner&id=<?= (int)$r['owner_id'] ?>">
              <?= e($r['owner_name']) ?>
            </a>
          </td>
          <td>
            <?php if ($r['last_rental_start']): ?>
              <?= e($r['last_rental_start']) ?> → <?= e($r['last_rental_end']) ?>
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
          <td>
            <?php if ($r['customer_id']): ?>
              <a class="btn-link" target="_blank"
                 href="<?= e($BASE) ?>manager/user_card.php?type=customer&id=<?= (int)$r['customer_id'] ?>">
                <?= e($r['customer_name']) ?>
              </a>
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
          <td>
            <a class="btn-link" target="_blank"
               href="<?= e($BASE) ?>pages/flat_detail.php?id=<?= (int)$r['flat_id'] ?>">View</a>
            <?php if ((int)$r['is_approved'] === 0): ?>
              <a class="btn-link" target="_blank"
                 href="<?= e($BASE) ?>manager/approve_flats.php?ref=<?= urlencode($r['reference_number']) ?>">Approve</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <div class="pager">
      <?php if ($page>1): ?>
        <a class="btn" href="<?= buildPageUrl($kept, $page-1) ?>">&larr; Prev</a>
      <?php endif; ?>
      <span>Page <?= (int)$page ?> of <?= (int)$pages ?></span>
      <?php if ($page<$pages): ?>
        <a class="btn" href="<?= buildPageUrl($kept, $page+1) ?>">Next &rarr;</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</main>

<?php include __DIR__.'/../includes/footer.php'; ?>
</body>
</html>
