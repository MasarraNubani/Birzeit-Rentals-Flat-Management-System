<?php
// pages/register_owner_step3.php
session_start();
require_once __DIR__ . '/../dbconfig.inc.php';

if (!isset($_SESSION['reg_owner']['national_id'], $_SESSION['reg_owner']['password'])) {
    header('Location: register_owner_step1.php');
    exit;
}

$data = $_SESSION['reg_owner'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        die("unable to connect to database.");
    }

    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM owners WHERE national_id = :nid");
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
          <h2 style="color:darkred;text-align:center;">National ID is already registered for an owner. Please check and try again.</h2>
          <p style="text-align:center;"><a href="register_owner_step1.php" class="btn">Return to Registration</a></p>
        </main>
        <?php include __DIR__ . '/../includes/footer.php'; ?>
        </body>
        </html>
        <?php
        exit;
    }
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO users (email, password, role)
        VALUES (:email, :password, 'owner')
    ");
    $stmt->execute([
        ':email'    => $data['email'],
        ':password' => $hashed_password
    ]);
    $userId = $pdo->lastInsertId();

    do {
        $ownerId = str_pad(random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
        $chk     = $pdo->prepare("SELECT COUNT(*) FROM owners WHERE owner_id = :oid");
        $chk->execute([':oid' => $ownerId]);
        $exists  = $chk->fetchColumn() > 0;
    } while ($exists);

    $stmt2 = $pdo->prepare("
        INSERT INTO owners
            (user_id, national_id, name, address, date_of_birth, email,
             mobile_number, telephone_number, bank_name, bank_branch, account_number, owner_id)
        VALUES
            (:uid, :nid, :name, :addr, :dob, :email,
             :mobile, :tel, :bank, :branch, :acct, :oid)
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
        ':bank'   => $data['bank_name'],
        ':branch' => $data['bank_branch'],
        ':acct'   => $data['account_number'],
        ':oid'    => $ownerId
    ]);

    unset($_SESSION['reg_owner']);

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <title>Registration Successful!</title>
      <link rel="stylesheet" href="../style.css">
    </head>
    <body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="container about-box">
      <h1>Registration Successful!</h1>
      <p>Welcome, <?= htmlspecialchars($data['name']) ?>.</p>
      <p>Your Owner ID is: <strong><?= $ownerId ?></strong></p>
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
  <title>Register Owner – Step 3</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container about-box">
  <h1>Confirm Your Details</h1>
  <form method="post" action="register_owner_step3.php">
    <p><strong>National ID:</strong> <?= htmlspecialchars($data['national_id']) ?></p>
    <p><strong>Name:</strong> <?= htmlspecialchars($data['name']) ?></p>
    <p><strong>Address:</strong> <?= htmlspecialchars($data['address']) ?></p>
    <p><strong>Date of Birth:</strong> <?= htmlspecialchars($data['date_of_birth']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($data['email']) ?></p>
    <p><strong>Mobile Number:</strong> <?= htmlspecialchars($data['mobile_number']) ?></p>
    <p><strong>Telephone Number:</strong> <?= htmlspecialchars($data['telephone_number']) ?></p>
    <p><strong>Bank Name:</strong> <?= htmlspecialchars($data['bank_name']) ?></p>
    <p><strong>Bank Branch:</strong> <?= htmlspecialchars($data['bank_branch']) ?></p>
    <p><strong>Account Number:</strong> <?= htmlspecialchars($data['account_number']) ?></p>
    <button type="submit" class="btn">Confirm and Finish</button>
  </form>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
