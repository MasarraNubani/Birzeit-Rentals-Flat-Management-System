<?php
// includes/notify.php

function notify(PDO $pdo, int $user_id, string $title, string $body = '', ?string $url = null): void {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, body, url) VALUES (:u,:t,:b,:url)");
    $stmt->execute([':u'=>$user_id, ':t'=>$title, ':b'=>$body, ':url'=>$url]);
}

function notify_unread_count(PDO $pdo, int $user_id): int {
    $st = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=:u AND is_read=0");
    $st->execute([':u'=>$user_id]);
    return (int)$st->fetchColumn();
}

function notify_mark_all_read(PDO $pdo, int $user_id): void {
    $st = $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=:u AND is_read=0");
    $st->execute([':u'=>$user_id]);
}
