<?php
session_start();
require_once __DIR__ . '/../dbconfig.inc.php';

if (!isset($_SESSION['reg_customer'])) {
    header('Location: register_customer_step1.php');
    exit;
}

$errors = [];
$email = $_SESSION['reg_customer']['email'] ?? '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'This email is already registered.';
        }
    }

    if (!preg_match('/^[0-9].{4,13}[a-z]$/', $password)) {
        $errors[] = 'Password must be 6–15 characters, start with a digit, and end with a lowercase letter.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Password and confirmation do not match.';
    }

    if (empty($errors)) {
        $_SESSION['reg_customer']['email'] = $email;
        $_SESSION['reg_customer']['password'] = $password;
        header('Location: register_customer_step3.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Customer – Step 2</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container">
  <section class="about-box">
    <h1>Customer Registration – Step 2</h1>

    <?php if ($errors): ?>
      <section class="about-box" style="background-color: #f8d7da; color: #721c24;">
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>

    <form method="post" action="register_customer_step2.php">
      <p>
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required
               value="<?= htmlspecialchars($email) ?>">
      </p>

      <p>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </p>

      <p>
        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
      </p>

      <button type="submit" class="btn">Continue to Step 3</button>
    </form>
  </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
