<?php
// pages/profile.php
session_start();
require_once __DIR__ . '/../dbconfig.inc.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login.php');
    exit;
}
$user = $_SESSION['user'];
$role = $_SESSION['role'];

$pdo = getDatabaseConnection();

if ($role === 'customer') {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE user_id = :uid");
    $stmt->execute([':uid' => $user['id']]);
    $info = $stmt->fetch();
} elseif ($role === 'owner') {
    $stmt = $pdo->prepare("SELECT * FROM owners WHERE user_id = :uid");
    $stmt->execute([':uid' => $user['id']]);
    $info = $stmt->fetch();
} else {
    $info = $user; // For manager (or expand as needed)
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="container about-box">
    <h1>My Profile</h1>
    <table style="margin:0 auto;">
        <?php if (is_array($info) && count($info)): ?>
            <?php foreach ($info as $k => $v): ?>
                <?php if (!is_numeric($k) && $k !== 'password'): ?>
                    <tr>
                        <th style="text-align:right; padding:8px;"><?= ucwords(str_replace('_',' ',$k)) ?>:</th>
                        <td style="padding:8px;"><?= htmlspecialchars($v) ?></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="2" style="text-align:center; color: #a00; padding: 20px;">
                    No profile information found.
                </td>
            </tr>
        <?php endif; ?>
    </table>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
