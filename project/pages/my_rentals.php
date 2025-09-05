<?php
session_start();
require_once __DIR__ . '/../dbconfig.inc.php';

if (empty($_SESSION['user']) || ($_SESSION['role'] ?? '') !== 'customer') {
    header('Location: ../auth/login.php');
    exit;
}

$userId = $_SESSION['user']['id'];
$pdo = getDatabaseConnection();

$sql = "SELECT r.*, 
               f.reference_number, f.rent_cost, f.location, f.owner_id,
               o.name AS owner_name, o.address AS owner_city, o.telephone_number AS owner_phone, o.email AS owner_email
        FROM rentals r
        JOIN flats f ON r.flat_id = f.id
        JOIN owners o ON f.owner_id = o.id
        WHERE r.customer_id = :cid
        ORDER BY r.rental_start DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':cid' => $userId]);
$rentals = $stmt->fetchAll();

function rentalStatus($start, $end) {
    $today = date('Y-m-d');
    if ($start <= $today && $end >= $today) return 'current';
    return 'past';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Rented Flats</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container about-box">
  <h1>My Rented Flats</h1>
  <?php if (count($rentals) === 0): ?>
    <p>No rentals found.</p>
  <?php else: ?>
    <table class="search-table">
      <thead>
        <tr>
          <th>Reference</th>
          <th>Monthly Cost</th>
          <th>Start Date</th>
          <th>End Date</th>
          <th>Location</th>
          <th>Owner</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rentals as $r): 
        $status = rentalStatus($r['rental_start'], $r['rental_end']);
        ?>
        <tr class="rental-<?= $status ?>">
          <td>
            <a class="ref-btn" href="flat_detail.php?ref=<?= urlencode($r['reference_number']) ?>" target="_blank">
              <?= htmlspecialchars($r['reference_number']) ?>
            </a>
          </td>
          <td>$<?= number_format($r['rent_cost'], 2) ?></td>
          <td><?= htmlspecialchars($r['rental_start']) ?></td>
          <td><?= htmlspecialchars($r['rental_end']) ?></td>
          <td><?= htmlspecialchars($r['location']) ?></td>
          <td>
            <a href="owner_card.php?id=<?= urlencode($r['owner_id']) ?>" class="owner-link" target="_blank">
              <?= htmlspecialchars($r['owner_name']) ?>
            </a>
          </td>
          <td><?= ucfirst($status) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
