<?php
/**
 * ضبط الاتصال + ثوابت عامة للمسارات
 * عدّلي BASE_URL حسب اسم مجلد مشروعك داخل htdocs
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    // إعدادات كوكي الجلسة (تعمل على http محلياً، وحطي secure=true على https)
    session_set_cookie_params([
        'httponly' => true,
        'secure'   => false,
        'samesite' => 'Lax',
    ]);
    session_start();
}

define('BASE_URL', 'http://localhost/project'); // ✳️ عدّلي "project" لاسم مجلدك
define('BASE_PATH', __DIR__);

// إعداد اتصال XAMPP الافتراضي (عدّلي إن كان المنفذ مختلف)
define('DBHOST',    '127.0.0.1');
define('DBPORT',    '3306');
define('DBNAME',    'web1211512_prefix_1211512'); // ✳️ أو اسم DB المحلي اللي أنشأتيه
define('DBUSER',    'root');
define('DBPASS',    '');

// DSN موحد
define('DBCONNSTRING', 'mysql:host=' . DBHOST . ';port=' . DBPORT . ';dbname=' . DBNAME . ';charset=utf8mb4');

/**
 * اتصال PDO مع إعدادات آمنة
 */
function getDatabaseConnection(bool $showErrors = true): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    try {
        $pdo = new PDO(DBCONNSTRING, DBUSER, DBPASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        if ($showErrors) {
            http_response_code(500);
            exit('DB Connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
        throw $e;
    }
}

/** دالة هيلبر لتطهير المخرجات */
function e(?string $str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}
