<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$role = $_SESSION['role'] ?? 'guest';
?>

<nav class="side-nav">
  <ul>
    <?php if ($role === 'customer'): ?>
      <li><a href="home.php">🏠 Home</a></li>
      <li><a href="search.php">🔍 Search Flats</a></li>
      <li><a href="pages/my_rentals.php">🏢 My Rentals</a></li>
      <li><a href="profile.php">👤 Profile</a></li>
      <li><a href="auth/logout.php">🔓 Logout</a></li>
    <?php elseif ($role === 'owner'): ?>
      <li><a href="home.php">🏠 Home</a></li>
      <li><a href="my_flats.php">🗂️ My Flats</a></li>
      <li><a href="add_flat.php">➕ Add Flat</a></li>
      <li><a href="appointments.php">📅 Appointments</a></li>
      <li><a href="profile.php">👤 Profile</a></li>
      <li><a href="auth/logout.php">🔓 Logout</a></li>
    <?php elseif ($role === 'manager'): ?>
      <li><a href="manager_home.php">🏠 Home</a></li>
      <li><a href="approve_flats.php">✅ Approve Flats</a></li>
      <li><a href="flats_inquiry.php">🔎 Flats Inquiry</a></li>
      <li><a href="users.php">👥 Users</a></li>
      <li><a href="profile.php">👤 Profile</a></li>
      <li><a href="auth/logout.php">🔓 Logout</a></li>
    <?php else: // guest ?>
      <li><a href="index.html">🏠 Home</a></li>
      <li><a href="pages/about_us.html">About Us</a></li>
      <li><a href="pages/flats.php">Flats</a></li>
      <li><a href="pages/search.php">Search Flats</a></li>
      <li><a href="auth/register_customer_step1.php">Register as Customer</a></li>
      <li><a href="auth/register_owner_step1.php">Register as Owner</a></li>
      <li><a href="auth/login.php">Login</a></li>
    <?php endif; ?>
  </ul>
</nav>
