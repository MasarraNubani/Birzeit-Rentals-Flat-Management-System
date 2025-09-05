<?php
session_start();
require_once __DIR__ . '/../dbconfig.inc.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'manager') {
    die('Unauthorized access.');
}

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$user_id) die('Invalid user.');

$pdo = getDatabaseConnection();

$stmt = $pdo->prepare("SELECT id, email, role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) die('User not found.');

$info = null;
switch ($user['role']) {
    case 'owner':
        $stmt2 = $pdo->prepare("SELECT name, address, mobile_number, telephone_number, email FROM owners WHERE user_id = ?");
        $stmt2->execute([$user_id]);
        $info = $stmt2->fetch();
        break;
    case 'customer':
        $stmt2 = $pdo->prepare("SELECT name, address, mobile_number, telephone_number, email FROM customers WHERE user_id = ?");
        $stmt2->execute([$user_id]);
        $info = $stmt2->fetch();
        break;
    case 'manager':
        $info = [
            'name' => 'Manager',
            'address' => '-',
            'mobile_number' => '-',
            'telephone_number' => '-',
            'email' => $user['email']
        ];
        break;
    default:
        die('Invalid user type.');
}

// icons
$email_icon = "&#9993;";
$phone_icon = "&#128222;";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Card - <?= htmlspecialchars($info['name']) ?></title>
    <link rel="stylesheet" href="../style.css">

</head>
<body>
<div class="user-card-box">
    <h2><?= htmlspecialchars($info['name']) ?></h2>
    <span class="role"><?= ucfirst($user['role']) ?></span>
    <div class="field"><strong>Address:</strong> <?= htmlspecialchars($info['address']) ?></div>
    <div class="field">
        <span class="icon"><?= $phone_icon ?></span>
        <strong>Mobile:</strong>
        <?= htmlspecialchars($info['mobile_number']) ?>
    </div>
    <div class="field">
        <span class="icon"><?= $phone_icon ?></span>
        <strong>Telephone:</strong>
        <?= htmlspecialchars($info['telephone_number']) ?>
    </div>
    <div class="field">
        <span class="icon"><?= $email_icon ?></span>
        <strong>Email:</strong>
        <a href="mailto:<?= htmlspecialchars($info['email']) ?>">
            <?= htmlspecialchars($info['email']) ?>
        </a>
    </div>
</div>
</body>
</html>
