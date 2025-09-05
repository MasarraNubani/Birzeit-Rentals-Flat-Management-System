<?php
if (session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../dbconfig.inc.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/notify.php';

if (empty($_SESSION['user'])) { header('Location: '.BASE_URL.'/auth/login.php'); exit; }

$pdo = getDatabaseConnection();
$user_id = (int)$_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['mark_all'])) {
    csrf_verify();
    notify_mark_all_read($pdo, $user_id);
    $_SESSION['flash'] = 'All notifications marked as read.';
    header('Location: '.BASE_URL.'/pages/notifications.php'); exit;
}

$st = $pdo->prepare("SELECT id,title,body,url,is_read,created_at FROM notifications WHERE user_id=:u ORDER BY created_at DESC LIMIT 200");
$st->execute([':u'=>$user_id]);
$rows = $st->fetchAll();

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Notifications</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= e(BASE_URL) ?>/style.css">
  <style>
    .notif-list{display:flex;flex-direction:column;gap:10px}
    .notif{padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff}
    .notif.unread{border-color:#f59e0b;background:#fff7ed}
    .notif h4{margin:0 0 6px}
    .notif time{font-size:12px;color:#6b7280}
    .notif .link{margin-top:6px;display:inline-block}
    .notif-actions{margin:12px 0}
  </style>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container">
  <h1>Notifications</h1>

  <form method="post" class="notif-actions">
    <?php csrf_field(); ?>
    <button class="btn" name="mark_all" value="1">Mark all as read</button>
  </form>

  <?php if (!$rows): ?>
    <p>No notifications yet.</p>
  <?php else: ?>
    <section class="notif-list">
      <?php foreach ($rows as $n): ?>
        <article class="notif <?= $n['is_read'] ? '' : 'unread' ?>">
          <h4><?= e($n['title']) ?></h4>
          <?php if (!empty($n['body'])): ?><div><?= nl2br(e($n['body'])) ?></div><?php endif; ?>
          <time><?= e($n['created_at']) ?></time>
          <?php if (!empty($n['url'])): ?>
            <div><a class="link" href="<?= e($n['url']) ?>">Open</a></div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
