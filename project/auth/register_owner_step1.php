<?php
session_start();

$errors = [];
$old = [
    'national_id'=> '',
    'name'=> '',
    'address'=> '',
    'date_of_birth'=> '',
    'email'=> '',
    'mobile_number'=> '',
    'telephone_number'=> '',
    'bank_name'=> '',
    'bank_branch'=> '',
    'account_number'=> ''
];

if (isset($_SESSION['reg_owner'])) {
    foreach ($old as $k => $v) {
        if (isset($_SESSION['reg_owner'][$k])) {
            $old[$k] = $_SESSION['reg_owner'][$k];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $v) {
        $old[$k] = trim($_POST[$k] ?? '');
    }

    if (!preg_match('/^[0-9]{7,20}$/', $old['national_id'])) {
        $errors['national_id'] = "National ID must be numbers only (7–20 digits).";
    }
    if ($old['name'] === '' || mb_strlen($old['name']) > 100) {
        $errors['name'] = "Please enter your full name (max 100 chars).";
    }
    if ($old['address'] === '') {
        $errors['address'] = "Address is required.";
    }
    if ($old['date_of_birth'] === '' || strtotime($old['date_of_birth']) === false) {
        $errors['date_of_birth'] = "Date of birth is required and must be a valid date.";
    } elseif (strtotime($old['date_of_birth']) > time()) {
        $errors['date_of_birth'] = "Date of birth cannot be in the future.";
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    }
    if ($old['mobile_number'] === '') {
        $errors['mobile_number'] = "Mobile number is required.";
    }
    if ($old['telephone_number'] === '') {
        $errors['telephone_number'] = "Telephone number is required.";
    }
    if ($old['bank_name'] === '') {
        $errors['bank_name'] = "Bank name is required.";
    }
    if ($old['bank_branch'] === '') {
        $errors['bank_branch'] = "Bank branch is required.";
    }
    if ($old['account_number'] === '') {
        $errors['account_number'] = "Account number is required.";
    }
    if (empty($errors)) {
        $_SESSION['reg_owner'] = $old;
        header('Location: register_owner_step2.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Owner – Step 1</title>
  <link rel="stylesheet" href="../style.css">
  <style>
    .field-error { border: 1.5px solid #d9534f !important; background: #fbe9e9; }
    .error-list { color: #b71c1c; background: #fdecea; border-radius: 6px; padding: 12px; margin-bottom: 24px; }
  </style>
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container about-box">
  <h1>Owner Registration – Step 1</h1>
  
  <?php if ($errors): ?>
    <section class="error-list" aria-live="polite" tabindex="0">
      <strong>Please correct the following errors:</strong>
      <ul>
        <?php foreach ($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?>
      </ul>
    </section>
  <?php endif; ?>
  
  <form method="post" action="register_owner_step1.php" novalidate>
    <p>
      <label for="national_id">National ID Number</label>
      <input type="text" id="national_id" name="national_id" required
             value="<?= htmlspecialchars($old['national_id']) ?>"
             class="<?= isset($errors['national_id']) ? 'field-error' : '' ?>">
    </p>
    <p>
      <label for="name">Full Name</label>
      <input type="text" id="name" name="name" required
             value="<?= htmlspecialchars($old['name']) ?>"
             class="<?= isset($errors['name']) ? 'field-error' : '' ?>">
    </p>
    <p>
      <label for="address">Address</label>
      <input type="text" id="address" name="address" required
             value="<?= htmlspecialchars($old['address']) ?>"
             class="<?= isset($errors['address']) ? 'field-error' : '' ?>">
    </p>
    <p>
      <label for="date_of_birth">Date of Birth</label>
      <input type="date" id="date_of_birth" name="date_of_birth" required
             value="<?= htmlspecialchars($old['date_of_birth']) ?>"
             class="<?= isset($errors['date_of_birth']) ? 'field-error' : '' ?>">
    </p>
    <p>
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" required
             value="<?= htmlspecialchars($old['email']) ?>"
             class="<?= isset($errors['email']) ? 'field-error' : '' ?>">
    </p>
    <p>
      <label for="mobile_number">Mobile Number</label>
      <input type="tel" id="mobile_number" name="mobile_number" required
             value="<?= htmlspecialchars($old['mobile_number']) ?>"
             class="<?= isset($errors['mobile_number']) ? 'field-error' : '' ?>">
    </p>
    <p>
      <label for="telephone_number">Telephone Number</label>
      <input type="tel" id="telephone_number" name="telephone_number" required
             value="<?= htmlspecialchars($old['telephone_number']) ?>"
             class="<?= isset($errors['telephone_number']) ? 'field-error' : '' ?>">
    </p>
    <p>
      <label for="bank_name">Bank Name</label>
      <input type="text" id="bank_name" name="bank_name" required
             value="<?= htmlspecialchars($old['bank_name']) ?>"
             class="<?= isset($errors['bank_name']) ? 'field-error' : '' ?>">
    </p>
    <p>
      <label for="bank_branch">Bank Branch</label>
      <input type="text" id="bank_branch" name="bank_branch" required
             value="<?= htmlspecialchars($old['bank_branch']) ?>"
             class="<?= isset($errors['bank_branch']) ? 'field-error' : '' ?>">
    </p>
    <p>
      <label for="account_number">Account Number</label>
      <input type="text" id="account_number" name="account_number" required
             value="<?= htmlspecialchars($old['account_number']) ?>"
             class="<?= isset($errors['account_number']) ? 'field-error' : '' ?>">
    </p>
    <button type="submit" class="btn">Continue to Step 2</button>
  </form>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
