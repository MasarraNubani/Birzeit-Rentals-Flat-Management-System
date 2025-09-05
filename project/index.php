<?php
session_start();

$prefix = "./"; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Birzeit Rentals Flat</title>
  <link rel="stylesheet" href="<?= $prefix ?>style.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<header class="custom-header">
    <img src="<?= $prefix ?>assets/Logo2.png" alt="Birzeit Rentals Flat Logo" class="logo-image">
    <nav class="top-nav" aria-label="Main Navigation">
        <a href="<?= $prefix ?>index.php" class="active">Home</a>
        <a href="<?= $prefix ?>pages/about_us.php">About Us</a>
        <a href="<?= $prefix ?>pages/flats.php">Flats</a>
        <a href="<?= $prefix ?>auth/register_customer_step1.php">Register as Customer</a>
        <a href="<?= $prefix ?>auth/register_owner_step1.php">Register as Owner</a>
        <a href="<?= $prefix ?>pages/search.php">Search</a>
        <a href="<?= $prefix ?>auth/login.php">Login</a>
    </nav>
</header>

<main>
    <section class="hero" aria-label="Welcome Section">
      <h1>Welcome to Birzeit Rentals Flat</h1>
      <p>The easiest way to find or list your apartment in Birzeit. Fast, safe, and smart!</p>
    </section>

    <section class="container features" aria-label="Site Features">
      <article class="feature-card">
        <h3>Search Apartments</h3>
        <p>Filter by location, price, size, and more. Find your next home easily.</p>
      </article>
      <article class="feature-card">
        <h3>List Your Property</h3>
        <p>Are you an owner? List your apartment in just a few clicks.</p>
      </article>
      <article class="feature-card">
        <h3>Secure Platform</h3>
        <p>All transactions are verified and appointments managed online.</p>
      </article>
    </section>
</main>

<footer class="site-footer">
    <address>
        <strong>Address:</strong> Birzeit<br>
        <strong>Phone:</strong> <a href="tel:+972566101783">📞 Support: +972566101783</a><br>
        <strong>Email:</strong> <a href="mailto:masarranubani14@gmail.com">✉️ support@birzeit-rental-flats.com</a>
    </address>
    <p>&copy; 2025 Birzeit Rental Flats. All rights reserved</p>
</footer>

</body>
</html>
