<?php
session_start();
require_once __DIR__ . '/../dbconfig.inc.php';

if (!isset($_SESSION['reg_customer']['national_id'])) {
    header('Location: register_customer_step1.php');
    exit;
}

$data = $_SESSION['reg_customer'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        die("❌ Unable to connect to database.");
    }

    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE national_id = :nid");
    $stmtCheck->execute([':nid' => $data['national_id']]);
    if ($stmtCheck->fetchColumn() > 0) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <title>Registration Error</title>
          <link rel="stylesheet" href="../style.css">
        </head>
        <body>
        <?php include __DIR__ . '/../includes/header.php'; ?>
        <main class="container about-box">
          <h2 style="color:darkred;text-align:center;">National ID is already registered. Please check and try again.</h2>
          <p style="text-align:center;"><a href="register_customer_step1.php" class="btn">Return to Registration</a></p>
        </main>
        <?php include __DIR__ . '/../includes/footer.php'; ?>
        </body>
        </html>
        <?php
        exit;
    }

    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (email, password, role)
        VALUES (:email, :password, 'customer')
    ");
    $stmt->execute([
        ':email'    => $data['email'],
        ':password' => $hashedPassword
    ]);
    $userId = $pdo->lastInsertId();

    do {
        $customerId = str_pad(random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
        $chk = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE customer_id = :cid");
        $chk->execute([':cid' => $customerId]);
        $exists = $chk->fetchColumn() > 0;
    } while ($exists);

    $stmt2 = $pdo->prepare("
        INSERT INTO customers 
          (user_id, national_id, name, address, date_of_birth, email, mobile_number, telephone_number, customer_id)
        VALUES
          (:uid, :nid, :name, :addr, :dob, :email, :mobile, :tel, :cid)
    ");
    $stmt2->execute([
        ':uid'    => $userId,
        ':nid'    => $data['national_id'],
        ':name'   => $data['name'],
        ':addr'   => $data['address'],
        ':dob'    => $data['date_of_birth'],
        ':email'  => $data['email'],
        ':mobile' => $data['mobile_number'],
        ':tel'    => $data['telephone_number'],
        ':cid'    => $customerId
    ]);

    unset($_SESSION['reg_customer']);

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <title>Registration Confirmed</title>
      <link rel="stylesheet" href="../style.css">
    </head>
    <body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <main class="container about-box">
      <h1>Registration Successful!</h1>
      <p>Welcome, <?= htmlspecialchars($data['name']) ?>.</p>
      <p>Your Customer ID is: <strong><?= $customerId ?></strong></p>
      <p>You can now <a href="../auth/login.php" class="btn">login</a> with your email.</p>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Confirm Registration – Step 3</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container about-box">
  <h1>Review Your Details</h1>
  <form method="post" action="register_customer_step3.php">
    <p><strong>National ID:</strong> <?= htmlspecialchars($data['national_id']) ?></p>
    <p><strong>Full Name:</strong> <?= htmlspecialchars($data['name']) ?></p>
    <p><strong>Address:</strong> <?= htmlspecialchars($data['address']) ?></p>
    <p><strong>Date of Birth:</strong> <?= htmlspecialchars($data['date_of_birth']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($data['email']) ?></p>
    <p><strong>Mobile Number:</strong> <?= htmlspecialchars($data['mobile_number']) ?></p>
    <p><strong>Telephone Number:</strong> <?= htmlspecialchars($data['telephone_number']) ?></p>
    <button type="submit" class="btn">Confirm Registration</button>
  </form>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
