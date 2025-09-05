<?php
require_once __DIR__ . '/../dbconfig.inc.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$pdo = getDatabaseConnection();
$stmt = $pdo->prepare("SELECT id, name, address, telephone_number, email FROM owners WHERE id = :id");
$stmt->execute([':id' => $id]);
$owner = $stmt->fetch();

if (!$owner) {
    echo "<p>Owner not found.</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Owner Card</title>
  <link rel="stylesheet" href="../style.css">
  <style>
    body {
      background: #f8f8f8;
      font-family: Arial, sans-serif;
    }
    table.owner-card-table {
      margin: 50px auto;
      border-collapse: collapse;
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 2px 18px #3332;
      min-width: 370px;
      font-size: 18px;
    }
    table.owner-card-table th, table.owner-card-table td {
      padding: 14px 20px;
      text-align: left;
      border-bottom: 1px solid #e5e5e5;
    }
    table.owner-card-table th {
      background: #f4f4f4;
      color: #454a3a;
      width: 160px;
      font-weight: bold;
      font-size: 17px;
    }
    table.owner-card-table td a {
      color: #454a3a;
      text-decoration: underline;
    }
    table.owner-card-table td a:hover {
      color: #8b9b6c;
    }
    .icon { margin-right: 6px; }
  </style>
</head>
<body>
  <table class="owner-card-table">
    <tr>
      <th>Reference ID</th>
      <td><?= htmlspecialchars($owner['id']) ?></td>
    </tr>
    <tr>
      <th>Name</th>
      <td><?= htmlspecialchars($owner['name']) ?></td>
    </tr>
    <tr>
      <th>City</th>
      <td><?= htmlspecialchars($owner['address']) ?></td>
    </tr>
    <tr>
      <th>Phone</th>
      <td><span class="icon">📞</span><?= htmlspecialchars($owner['telephone_number']) ?></td>
    </tr>
    <tr>
      <th>Email</th>
      <td><span class="icon">✉️</span>
        <a href="mailto:<?= htmlspecialchars($owner['email']) ?>">
          <?= htmlspecialchars($owner['email']) ?>
        </a>
      </td>
    </tr>
  </table>
</body>
</html>
