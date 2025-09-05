<?php
// includes/header.php
require_once __DIR__ . '/../dbconfig.inc.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$prefix = rtrim(BASE_URL, '/') . '/';
$userLoggedIn = !empty($_SESSION['user']);
$role = $_SESSION['role'] ?? ($_SESSION['user']['role'] ?? null);

$notifCount = 0;
if ($userLoggedIn) {
    require_once __DIR__ . '/../includes/notify.php';
    $pdo_header = getDatabaseConnection();
    $notifCount = notify_unread_count($pdo_header, (int)$_SESSION['user']['id']);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Birzeit Rentals</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= htmlspecialchars($prefix) ?>style.css">
  <style>
    .notif-link{ position:relative; padding-right:22px; }
    .notif-badge{
      position:absolute; top:-6px; right:-10px;
      background:#dc2626; color:#fff; border-radius:999px;
      min-width:18px; height:18px;
      display:inline-flex; align-items:center; justify-content:center;
      font-size:12px; font-weight:bold; padding:0 5px;
    }
  </style>
</head>
<body>
<header class="custom-header" id="main-header">
  <img src="<?= htmlspecialchars($prefix) ?>assets/Logo2.png"
       alt="Birzeit Rentals Flat Logo" class="logo-image" id="logo">

  <nav class="top-nav" aria-label="Main Navigation">
    <?php if ($userLoggedIn): ?>
      <a href="<?= htmlspecialchars($prefix) ?>pages/home.php">Home</a>
      <a href="<?= htmlspecialchars($prefix) ?>pages/about_us.php">About Us</a>
      <a href="<?= htmlspecialchars($prefix) ?>pages/flats.php">Flats</a>
      <a href="<?= htmlspecialchars($prefix) ?>pages/search.php">Search</a>
      <a href="<?= htmlspecialchars($prefix) ?>pages/profile.php">My Profile</a>

      <?php if ($role === 'customer'): ?>
        <a href="<?= htmlspecialchars($prefix) ?>pages/my_rentals.php">My Rentals</a>
      <?php elseif ($role === 'owner'): ?>
        <a href="<?= htmlspecialchars($prefix) ?>owner/view_my_flats.php">My Flats</a>
        <a href="<?= htmlspecialchars($prefix) ?>owner/add_flat.php">Add Flat</a>
      <?php elseif ($role === 'manager'): ?>
        <a href="<?= htmlspecialchars($prefix) ?>manager/approve_flats.php">Approve Flats</a>
      <?php endif; ?>

      <!-- Notifications -->
      <a href="<?= htmlspecialchars($prefix) ?>pages/notifications.php" class="notif-link">
        Notifications
        <span id="notif-badge" class="notif-badge" style="display:<?= $notifCount ? 'inline-flex':'none' ?>">
          <?= (int)$notifCount ?>
        </span>
      </a>

      <a href="<?= htmlspecialchars($prefix) ?>auth/logout.php">Logout</a>
    <?php else: ?>
      <a href="<?= htmlspecialchars($prefix) ?>index.php">Home</a>
      <a href="<?= htmlspecialchars($prefix) ?>pages/about_us.php">About Us</a>
      <a href="<?= htmlspecialchars($prefix) ?>pages/flats.php">Flats</a>
      <a href="<?= htmlspecialchars($prefix) ?>auth/register_customer_step1.php">Register as Customer</a>
      <a href="<?= htmlspecialchars($prefix) ?>auth/register_owner_step1.php">Register as Owner</a>
      <a href="<?= htmlspecialchars($prefix) ?>pages/search.php">Search</a>
      <a href="<?= htmlspecialchars($prefix) ?>auth/login.php">Login</a>
    <?php endif; ?>
  </nav>
</header>
<main class="container">

<?php if ($userLoggedIn): ?>
<script>
  (function(){
    function refreshBadge(){
      fetch('<?= htmlspecialchars($prefix) ?>pages/notifications_count.php', {credentials:'same-origin'})
        .then(r=>r.json()).then(data=>{
          var b = document.getElementById('notif-badge');
          if(!b) return;
          var c = (data && typeof data.count!=='undefined') ? data.count : 0;
          b.textContent = c;
          b.style.display = c>0 ? 'inline-flex' : 'none';
        }).catch(()=>{});
    }
    setInterval(refreshBadge, 60000);
  })();
</script>
<?php endif; ?>
