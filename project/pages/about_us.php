<?php
session_start();
$prefix = '../'; 
$userLoggedIn = isset($_SESSION['user']) && isset($_SESSION['role']);
$role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>About Us - Birzeit Rentals Flat</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="<?= $prefix ?>style.css">
</head>
<body>

<header class="custom-header" id="main-header">
    <img src="<?= $prefix ?>assets/Logo2.png" alt="Birzeit Rentals Flat Logo" class="logo-image" id="logo">
    <nav class="top-nav" aria-label="Main Navigation">
      <?php if ($userLoggedIn): ?>
        <a href="<?= $prefix ?>pages/home.php">Home</a>
        <a href="<?= $prefix ?>pages/about_us.php" class="active">About Us</a>
        <a href="<?= $prefix ?>pages/flats.php">Flats</a>
        <a href="<?= $prefix ?>pages/search.php">Search</a>
        <a href="<?= $prefix ?>pages/profile.php">My Profile</a>
        <?php if ($role == 'customer'): ?>
            <a href="<?= $prefix ?>pages/my_rentals.php">My Rentals</a>
            <a href="<?= $prefix ?>pages/basket.php">Basket</a>
        <?php elseif ($role == 'owner'): ?>
            <a href="<?= $prefix ?>owner/view_my_flats.php">My Flats</a>
            <a href="<?= $prefix ?>owner/add_flat.php">Add Flat</a>
        <?php elseif ($role == 'manager'): ?>
            <a href="<?= $prefix ?>pages/approve_flats.php">Approve Flats</a>
            <a href="<?= $prefix ?>pages/all_users.php">Users Management</a>
        <?php endif; ?>
        <a href="<?= $prefix ?>auth/logout.php">Logout</a>
      <?php else: ?>
        <a href="<?= $prefix ?>index.php">Home</a>
        <a href="<?= $prefix ?>pages/about_us.php" class="active">About Us</a>
        <a href="<?= $prefix ?>pages/flats.php">Flats</a>
        <a href="<?= $prefix ?>auth/register_customer_step1.php">Register as Customer</a>
        <a href="<?= $prefix ?>auth/register_owner_step1.php">Register as Owner</a>
        <a href="<?= $prefix ?>pages/search.php">Search</a>
        <a href="<?= $prefix ?>auth/login.php">Login</a>
      <?php endif; ?>
    </nav>
</header>

<section class="container about-box fade-in">
  <h2>The Agency</h2>
  <p>
    <strong>Birzeit Rentals Flat</strong> was established in 2025 to provide secure, simple, and efficient rental services in Birzeit, especially for students and families.<br>
    <strong>History & Awards:</strong> The agency has won awards for customer service and innovative digital solutions.<br>
    <strong>Management Hierarchy:</strong> <br>
    CEO: Masarra Nubani<br>
  </p>
</section>

<section class="container about-box fade-in">
  <h2>About Birzeit City</h2>
  <p>
    Birzeit is a Palestinian city north of Ramallah, well known for Birzeit University and its vibrant student community. The city features a mild climate, historical landmarks, and active cultural life. 
    <br>
    <strong>Population:</strong> ~10,000<br>
    <strong>Main Landmarks:</strong> Birzeit University, Old City Market, Churches and Mosques.<br>
    <strong>Learn more:</strong> 
    <a href="https://en.wikipedia.org/wiki/Birzeit" target="_blank" rel="noopener">Wikipedia - Birzeit</a>
  </p>
</section>

<section class="container about-box fade-in">
  <h2>Main Business Activities</h2>
  <ul style="text-align:left; max-width:650px; margin:auto; font-size:18px;">
    <li>Listing and verifying apartments for rent.</li>
    <li>Connecting property owners with potential renters.</li>
    <li>Arranging appointments and managing rental contracts.</li>
    <li>Customer support and property marketing.</li>
    <li>Providing up-to-date market and neighborhood information.</li>
  </ul>
</section>

<section class="container about-box fade-in">
  <h1>About Birzeit Rentals Flat</h1>
  <p>
    Birzeit Rentals Flat is a platform designed to make the process of renting and listing apartments in Birzeit simple, secure, and student-friendly.
  </p>
</section>

<section class="container fade-in">
  <img src="<?= $prefix ?>assets/S4.jpg" alt="Birzeit City" class="about-img">
</section>

<section class="container about-box fade-in">
  <h2>Why Choose Us?</h2>
  <p>
    Whether you are a student looking for a peaceful place to study or a property owner looking for reliable tenants, Birzeit Rentals Flat provides tools and services to make your experience seamless.
  </p>
</section>

<section class="container features fade-in" aria-label="Key Features">
  <article class="feature-card">
    <h3>Verified Listings</h3>
    <p>All apartments are checked and verified before they appear on the site.</p>
  </article>
  <article class="feature-card">
    <h3>Smart Matching</h3>
    <p>Get suggestions based on your budget, location, and needs.</p>
  </article>
  <article class="feature-card">
    <h3>Online Scheduling</h3>
    <p>Book viewings and get confirmations without phone calls.</p>
  </article>
</section>

<section class="container about-box center-content fade-in">
  <img src="<?= $prefix ?>assets/S6.jpg" alt="Friendly community" class="about-img">
  <blockquote>
    “More than just listings – we connect people to homes.”
  </blockquote>
</section>

<section class="container about-box fade-in">
  <h2>Our Vision</h2>
  <p>
    We aim to create a housing ecosystem in Birzeit that is efficient, fair, and beneficial for everyone — students, families, and landlords alike.
  </p>
</section>

<section class="container fade-in">
  <img src="<?= $prefix ?>assets/S1.jpg" alt="Birzeit Apartments" class="about-img">
</section>

<footer>
    <address>
        <strong>Address:</strong> Birzeit<br>
        <strong>Phone:</strong> <a href="tel:+972566101783">📞 Support: +972566101783</a><br>
        <strong>Email:</strong> <a href="mailto:masarranubani14@gmail.com">✉️ Email: support@Birzeit Rental Flats.com</a>
    </address>
    <p>&copy; 2025 Birzeit Rental Flats. All rights reserved</p>
</footer>

<script>
  window.addEventListener('scroll', function () {
    const header = document.getElementById('main-header');
    const logo = document.getElementById('logo');
    if (window.scrollY > 50) {
      header.classList.add('scrolled-header');
      logo.classList.add('scrolled-logo');
    } else {
      header.classList.remove('scrolled-header');
      logo.classList.remove('scrolled-logo');
    }

    document.querySelectorAll('.fade-in').forEach(el => {
      const rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight - 100) {
        el.classList.add('visible');
      }
    });
  });
</script>

</body>
</html>
