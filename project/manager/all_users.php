<?php
// manager/all_users.php
session_start();
require_once __DIR__ . '/../dbconfig.inc.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'manager') {
    die('Unauthorized access.');
}

$pdo = getDatabaseConnection();

$stmt = $pdo->query("SELECT id, email, role FROM users ORDER BY role, id");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Users Management</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container about-box">
    <h1>Users Management</h1>
    <table class="user-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Role</th>
            <th>View</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= ucfirst($u['role']) ?></td>
            <td>
              <a href="user_card.php?id=<?= $u['id'] ?>" class="user-card-btn" target="_blank">
                View Card
              </a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
