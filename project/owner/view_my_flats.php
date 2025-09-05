<?php
session_start();
require_once __DIR__ . '/../dbconfig.inc.php';
require_once __DIR__ . '/../includes/csrf.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'owner') {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = (int)$_SESSION['user']['id'];

$pdo = getDatabaseConnection();
$stmt = $pdo->prepare("SELECT id FROM owners WHERE user_id = :uid");
$stmt->execute([':uid' => $user_id]);
$ownerRow = $stmt->fetch();
if (!$ownerRow) {
    die("❌ Owner information not found!");
}
$owner_id = (int)$ownerRow['id'];

$stmt2 = $pdo->prepare("
    SELECT id, reference_number, location, address, rent_cost, available_from, available_to, bedrooms, bathrooms, is_approved, is_furnished
    FROM flats
    WHERE owner_id = :oid
    ORDER BY id DESC
");
$stmt2->execute([':oid' => $owner_id]);
$flats = $stmt2->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Flats</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container about-box">
    <h1>My Flats</h1>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice success">Flat deleted successfully.</div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'not_approved'): ?>
        <p class="error-message centered-message">
            The flat is either not found or still pending approval from management.
        </p>
    <?php endif; ?>

    <?php if (count($flats) === 0): ?>
        <p class="info-message centered-message">
            You have not added any flats yet. <br>
            <a href="add_flat.php" class="btn">Add New Flat</a>
        </p>
    <?php else: ?>
        <section class="search-results">
            <table class="search-table" aria-label="My Flats">
                <thead>
                    <tr>
                        <th>Ref.</th>
                        <th>Location</th>
                        <th>Address</th>
                        <th>Price (NIS)</th>
                        <th>Available From</th>
                        <th>Available To</th>
                        <th>Bedrooms</th>
                        <th>Bathrooms</th>
                        <th>Furnished</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($flats as $f): ?>
                    <tr>
                        <td><?= htmlspecialchars($f['reference_number']) ?></td>
                        <td><?= htmlspecialchars($f['location']) ?></td>
                        <td><?= htmlspecialchars($f['address']) ?></td>
                        <td><?= number_format($f['rent_cost'],2) ?></td>
                        <td><?= htmlspecialchars($f['available_from']) ?></td>
                        <td><?= htmlspecialchars($f['available_to']) ?></td>
                        <td><?= (int)$f['bedrooms'] ?></td>
                        <td><?= (int)$f['bathrooms'] ?></td>
                        <td><?= $f['is_furnished'] ? 'Yes' : 'No' ?></td>
                        <td>
                            <?php if ($f['is_approved']): ?>
                                <span class="status-confirmed">Approved</span>
                            <?php else: ?>
                                <span class="status-pending">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td style="display:flex;gap:8px;flex-wrap:wrap;">
                            <a href="../pages/flat_detail.php?id=<?= (int)$f['id'] ?>" target="_blank" class="btn btn-view">View</a>

                            <form method="post"
                                  action="<?= e(BASE_URL) ?>/owner/delete_flat.php"
                                  onsubmit="return confirm('Delete this flat? This cannot be undone.');"
                                  style="display:inline-block;">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
