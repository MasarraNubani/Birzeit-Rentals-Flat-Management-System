<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['reg_customer'] = [
        'national_id'      => trim($_POST['national_id'] ?? ''),
        'name'             => trim($_POST['name'] ?? ''),
        'address'          => trim($_POST['address'] ?? ''),
        'date_of_birth'    => trim($_POST['date_of_birth'] ?? ''),
        'email'            => trim($_POST['email'] ?? ''),
        'mobile_number'    => trim($_POST['mobile_number'] ?? ''),
        'telephone_number' => trim($_POST['telephone_number'] ?? ''),
    ];
    header('Location: register_customer_step2.php');
    exit;
}

$data = $_SESSION['reg_customer'] ?? [
    'national_id'=>'',
    'name'=>'',
    'address'=>'',
    'date_of_birth'=>'',
    'email'=>'',
    'mobile_number'=>'',
    'telephone_number'=>''
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Customer – Step 1</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>

<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container">
  <section class="about-box">
    <h1>Customer Registration – Step 1</h1>
    <form method="post" action="register_customer_step1.php">
      
      <p>
        <label for="national_id">National ID Number</label>
        <input type="text" id="national_id" name="national_id" required
               value="<?= htmlspecialchars($data['national_id']) ?>">
      </p>
      
      <p>
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required
               value="<?= htmlspecialchars($data['name']) ?>">
      </p>
      
      <p>
        <label for="address">Address</label>
        <input type="text" id="address" name="address" required
               value="<?= htmlspecialchars($data['address']) ?>">
      </p>
      
      <p>
        <label for="date_of_birth">Date of Birth</label>
        <input type="date" id="date_of_birth" name="date_of_birth" required
               value="<?= htmlspecialchars($data['date_of_birth']) ?>">
      </p>
      
      <p>
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required
               value="<?= htmlspecialchars($data['email']) ?>">
      </p>
      
      <p>
        <label for="mobile_number">Mobile Number</label>
        <input type="tel" id="mobile_number" name="mobile_number" required
               value="<?= htmlspecialchars($data['mobile_number']) ?>">
      </p>
      
      <p>
        <label for="telephone_number">Telephone Number</label>
        <input type="tel" id="telephone_number" name="telephone_number" required
               value="<?= htmlspecialchars($data['telephone_number']) ?>">
      </p>
      
      <button type="submit" class="btn">Continue to Step 2</button>
    </form>
  </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
