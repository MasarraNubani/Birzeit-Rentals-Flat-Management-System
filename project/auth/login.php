<?php
session_start();
require_once __DIR__ . '/../dbconfig.inc.php';

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email !== '' && $password !== '') {
        $pdo = getDatabaseConnection();
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            // Check password securely
            if ($user && password_verify($password, $user['password'])) {
                // Remove password from session for security
                unset($user['password']);
                $_SESSION['user'] = $user;
                $_SESSION['role'] = $user['role'];
                header('Location: ../pages/home.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Database connection failed.';
        }
    } else {
        $error = 'Please fill in both fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Birzeit Rentals Flat</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container">
  <section class="about-box">
    <h1>Login</h1>
    <?php if ($error): ?>
      <p style="color: red; text-align: center; margin-bottom: 20px;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" action="login.php" autocomplete="off">
      <label for="email">Email:</label>
      <input
          type="email"
          id="email"
          name="email"
          required
          value="<?= htmlspecialchars($email) ?>"
          autocomplete="username"
      >

      <label for="password">Password:</label>
      <input
          type="password"
          id="password"
          name="password"
          required
          autocomplete="current-password"
      >

      <button type="submit" class="btn">Login</button>
    </form>
  </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
