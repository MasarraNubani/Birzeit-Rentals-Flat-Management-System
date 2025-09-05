<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../dbconfig.inc.php';
$pdo = getDatabaseConnection();
if (!$pdo) { exit('❌ Unable to connect to database.'); }


$sql = "
  SELECT
    f.id,
    f.description,
    f.location,
    f.rent_cost,
    COALESCE(
      (SELECT i.image_path FROM images i WHERE i.flat_id = f.id ORDER BY i.id ASC LIMIT 1),
      'assets/no-image.png'
    ) AS image_path
  FROM flats f
  WHERE f.is_approved = 1
  ORDER BY f.available_from DESC
  LIMIT 4
";
$flats = $pdo->query($sql)->fetchAll();

function url_for_asset(string $path): string {
  if (preg_match('#^https?://#i', $path)) return $path;                 
  return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');                
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home - Birzeit Rentals Flat</title>
  <link rel="stylesheet" href="<?= e(BASE_URL) ?>/style.css">
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<section class="container layout-container">
  <nav class="side-nav" aria-label="Main Navigation">
    <strong>Main Navigation</strong>
    <hr class="nav-divider">
    <?php if (!empty($_SESSION['role'])): ?>
      <?php if ($_SESSION['role'] === 'owner'): ?>
        <a href="../owner/home.php" class="active">Home</a>
        <a href="../owner/view_my_flats.php">My Flats</a>
        <a href="../owner/preview_appointments.php">Preview Appointments</a>
        <a href="../owner/add_flat.php">Add Flat</a>
        <a href="../pages/flats.php">All Flats</a>
        <a href="../auth/logout.php">Logout</a>
      <?php elseif ($_SESSION['role'] === 'customer'): ?>
        <a href="home.php" class="active">Home</a>
        <a href="search.php">Flat Search</a>
        <a href="my_rentals.php">My Rentals</a>
        <a href="../auth/logout.php">Logout</a>
      <?php elseif ($_SESSION['role'] === 'manager'): ?>
        <a href="home.php" class="active">Home</a>
        <a href="../manager/approve_flats.php">Approve Flats</a>
        <a href="../manager/inquire_flats.php">Flats Inquire</a>
        <a href="../manager/all_users.php">Users Management</a>
        <a href="../auth/logout.php">Logout</a>
      <?php endif; ?>
    <?php else: ?>
      <a href="home.php" class="active">Home</a>
      <a href="search.php">Flat Search</a>
      <a href="../auth/register_customer_step1.php">Register as Customer</a>
      <a href="../auth/register_owner_step1.php">Register as Owner</a>
      <a href="../auth/login.php">Login</a>
    <?php endif; ?>
  </nav>

  <main class="main-content">
    <section class="hero fade-in">
      <h1>Welcome <?= isset($_SESSION['user']['email']) ? e($_SESSION['user']['email']) : 'Guest' ?>!</h1>
      <p>Discover the best apartments in Birzeit. Browse, book viewings, and rent—all in one place.</p>
      <a href="search.php" class="btn">Search Flats</a>
    </section>

    <section class="flats-page fade-in">
      <h2 class="page-title">Featured Apartments</h2>
      <section class="flats-grid">
        <?php if ($flats): ?>
          <?php foreach ($flats as $flat): ?>
            <?php
              $imgSrc = url_for_asset($flat['image_path']);   // يصير مثل http://localhost/project/uploads/.. أو ../assets/no-image.png
            ?>
            <section class="flat-card">
              <img src="<?= e($imgSrc) ?>" alt="Flat Image">
              <?php if (!empty($flat['description'])): ?>
                <h3><?= e($flat['description']) ?></h3>
              <?php endif; ?>
              <p><strong>Price:</strong> $<?= number_format((float)$flat['rent_cost'], 2) ?>/month</p>
              <p><strong>Location:</strong> <?= e($flat['location']) ?></p>
              <a href="flat_detail.php?id=<?= (int)$flat['id'] ?>" class="btn">View Details</a>
            </section>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="no-flats">No featured apartments at the moment.</p>
        <?php endif; ?>
      </section>
    </section>
  </main>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
document.addEventListener('scroll', function() {
  document.querySelectorAll('.fade-in').forEach(el => {
    if (el.getBoundingClientRect().top < window.innerHeight - 100) {
      el.classList.add('visible');
    }
  });
});
</script>
</body>
</html>
