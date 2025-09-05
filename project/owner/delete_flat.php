<?php
// project/owner/delete_flat.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../dbconfig.inc.php';
require_once __DIR__ . '/../includes/csrf.php';

$pdo = getDatabaseConnection();

if (empty($_SESSION['user'])) {
  header('Location: ' . BASE_URL . '/auth/login.php');
  exit;
}

$role     = $_SESSION['role'] ?? ($_SESSION['user']['role'] ?? null);
$user_id  = (int)($_SESSION['user']['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method Not Allowed');
}

csrf_verify();

$flat_id = (int)($_POST['id'] ?? 0);
if ($flat_id <= 0) {
  http_response_code(400);
  exit('Bad Request: missing id');
}

$flat = $pdo->prepare("
  SELECT f.id, f.owner_id
  FROM flats f
  WHERE f.id = :id
");
$flat->execute([':id' => $flat_id]);
$row = $flat->fetch();

if (!$row) {
  http_response_code(404);
  exit('Flat not found');
}

if ($role === 'owner') {
  $st = $pdo->prepare("SELECT id FROM owners WHERE user_id = :uid");
  $st->execute([':uid' => $user_id]);
  $ownerRow = $st->fetch();
  if (!$ownerRow || (int)$ownerRow['id'] !== (int)$row['owner_id']) {
    http_response_code(403);
    exit('Forbidden');
  }
} elseif ($role !== 'manager') {
  http_response_code(403);
  exit('Forbidden');
}

$imgs = $pdo->prepare("SELECT image_path FROM images WHERE flat_id = :id");
$imgs->execute([':id' => $flat_id]);
$imagePaths = $imgs->fetchAll(PDO::FETCH_COLUMN);

try {
  $pdo->beginTransaction();

  $pdo->prepare("DELETE FROM appointments WHERE flat_id = :id")->execute([':id' => $flat_id]);
  $pdo->prepare("DELETE FROM rentals      WHERE flat_id = :id")->execute([':id' => $flat_id]);
  $pdo->prepare("DELETE FROM viewing_times WHERE flat_id = :id")->execute([':id' => $flat_id]);
  $pdo->prepare("DELETE FROM marketing_info WHERE flat_id = :id")->execute([':id' => $flat_id]);
  $pdo->prepare("DELETE FROM images       WHERE flat_id = :id")->execute([':id' => $flat_id]);

  $pdo->prepare("DELETE FROM flats WHERE id = :id")->execute([':id' => $flat_id]);

  $pdo->commit();
} catch (Throwable $e) {
  $pdo->rollBack();
  http_response_code(500);
  exit('Error deleting flat: ' . e($e->getMessage()));
}

$baseAbs = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR; 
foreach ($imagePaths as $rel) {
  $abs = $baseAbs . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rel), DIRECTORY_SEPARATOR);
  if (is_file($abs)) { @unlink($abs); }
}

$redirect = ($role === 'manager')
  ? BASE_URL . '/manager/approve_flats.php?deleted=1'
  : BASE_URL . '/owner/view_my_flats.php?deleted=1';

header('Location: ' . $redirect);
exit;
