<?php
if (session_status()===PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../dbconfig.inc.php';
require_once __DIR__ . '/../includes/notify.php';

if (empty($_SESSION['user'])) { echo json_encode(['count'=>0]); exit; }

$pdo = getDatabaseConnection();
$user_id = (int)$_SESSION['user']['id'];
echo json_encode(['count' => notify_unread_count($pdo, $user_id)]);
