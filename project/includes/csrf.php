<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): void {
    echo '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ok = isset($_POST['csrf_token'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf_token']);
        if (!$ok) {
            http_response_code(403);
            exit('Invalid CSRF token');
        }
    }
}
