<?php
session_start();
require_once __DIR__ . '/../dbconfig.inc.php';

if (!isset($_SESSION['user']) || (($_SESSION['role'] ?? ($_SESSION['user']['role'] ?? null)) !== 'owner')) {
    header('Location: ../auth/login.php'); exit;
}

$pdo = getDatabaseConnection();
$user_id = (int)$_SESSION['user']['id'];

$stmt = $pdo->prepare("SELECT id FROM owners WHERE user_id = :uid");
$stmt->execute([':uid'=>$user_id]);
$ownerRow = $stmt->fetch();
if (!$ownerRow) { die("❌ Owner information not found!"); }
$real_owner_id = (int)$ownerRow['id'];

$errors = [];
$MAX_PER_FILE = 5 * 1024 * 1024; // 5MB
$ALLOWED = ['image/jpeg','image/png','image/webp']; // MIME
$ALLOWED_EXT = ['jpg','jpeg','png','webp'];        

$uploadDirAbs  = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
$uploadDirWeb  = 'uploads/'; 
if (!is_dir($uploadDirAbs)) { @mkdir($uploadDirAbs, 0775, true); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location             = trim($_POST['location'] ?? '');
    $address              = trim($_POST['address'] ?? '');
    $rent_cost            = (float)($_POST['rent_cost'] ?? 0);
    $available_from       = trim($_POST['available_from'] ?? '');
    $available_to         = trim($_POST['available_to'] ?? '');
    $bedrooms            = (int)($_POST['bedrooms'] ?? 1);
    $bathrooms           = (int)($_POST['bathrooms'] ?? 1);
    $size_sqm            = isset($_POST['size_sqm']) && $_POST['size_sqm'] !== '' ? (float)$_POST['size_sqm'] : null;
    $has_heating         = isset($_POST['has_heating']) ? 1 : 0;
    $has_air_conditioning= isset($_POST['has_air_conditioning']) ? 1 : 0;
    $has_access_control  = isset($_POST['has_access_control']) ? 1 : 0;
    $has_parking         = isset($_POST['has_parking']) ? 1 : 0;
    $backyard_type       = $_POST['backyard_type'] ?? 'none';
    $has_playground      = isset($_POST['has_playground']) ? 1 : 0;
    $has_storage         = isset($_POST['has_storage']) ? 1 : 0;
    $description         = trim($_POST['description'] ?? '');
    $is_furnished        = isset($_POST['is_furnished']) ? 1 : 0;

    if ($location === '' || $address === '' || $rent_cost <= 0 || $available_from === '' || $bedrooms <= 0) {
        $errors[] = 'Please fill all required fields correctly.';
    }

    $slot_day   = $_POST['slot_day']   ?? [];
    $slot_from  = $_POST['slot_from']  ?? [];
    $slot_to    = $_POST['slot_to']    ?? [];
    $slot_phone = $_POST['slot_phone'] ?? [];

    $filesOK = [];
    if (!empty($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
        $count = count($_FILES['photos']['name']);
        for ($i=0;$i<$count;$i++) {
            $err = $_FILES['photos']['error'][$i];
            if ($err === UPLOAD_ERR_NO_FILE) continue; 
            if ($err !== UPLOAD_ERR_OK) { $errors[] = 'Upload error on image #' . ($i+1); continue; }

            $tmp  = $_FILES['photos']['tmp_name'][$i];
            $name = $_FILES['photos']['name'][$i];
            $size = (int)$_FILES['photos']['size'][$i];
            $type = mime_content_type($tmp) ?: $_FILES['photos']['type'][$i];

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if ($size > $MAX_PER_FILE) { $errors[] = "Image '$name' exceeds 5MB."; continue; }
            if (!in_array($type, $ALLOWED, true) || !in_array($ext, $ALLOWED_EXT, true)) {
                $errors[] = "Image '$name' type not allowed."; continue;
            }
            $filesOK[] = ['tmp'=>$tmp, 'orig'=>$name, 'ext'=>$ext];
        }
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            do {
                $reference_number = 'FL' . str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
                $chk = $pdo->prepare("SELECT COUNT(*) FROM flats WHERE reference_number = :ref");
                $chk->execute([':ref'=>$reference_number]);
            } while ($chk->fetchColumn() > 0);

            // إدخال الشقة
            $stmt2 = $pdo->prepare("INSERT INTO flats 
                (owner_id, location, address, rent_cost, available_from, available_to, bedrooms, bathrooms, size_sqm,
                 has_heating, has_air_conditioning, has_access_control, has_parking, backyard_type, has_playground,
                 has_storage, description, is_furnished, reference_number)
                VALUES
                (:oid, :location, :address, :cost, :from, :to, :beds, :baths, :size, :heating, :ac, :access, :parking,
                 :backyard, :play, :storage, :desc, :furnished, :ref)");
            $stmt2->execute([
                ':oid'=>$real_owner_id, ':location'=>$location, ':address'=>$address, ':cost'=>$rent_cost,
                ':from'=>$available_from, ':to'=>$available_to ?: null, ':beds'=>$bedrooms, ':baths'=>$bathrooms,
                ':size'=>$size_sqm, ':heating'=>$has_heating, ':ac'=>$has_air_conditioning,
                ':access'=>$has_access_control, ':parking'=>$has_parking, ':backyard'=>$backyard_type,
                ':play'=>$has_playground, ':storage'=>$has_storage, ':desc'=>$description, ':furnished'=>$is_furnished,
                ':ref'=>$reference_number
            ]);
            $flat_id = (int)$pdo->lastInsertId();

            $daysWhitelist = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            for ($i = 0; $i < count($slot_day); $i++) {
                $d = trim($slot_day[$i] ?? '');
                $f = trim($slot_from[$i] ?? '');
                $t = trim($slot_to[$i] ?? '');
                $p = trim($slot_phone[$i] ?? '');
                if ($d && $f && $t && $p && in_array($d, $daysWhitelist, true)) {
                    $pdo->prepare("INSERT INTO viewing_times (flat_id, day_of_week, time_from, time_to, contact_phone)
                                   VALUES (:fid,:d,:f,:t,:p)")
                        ->execute([':fid'=>$flat_id, ':d'=>$d, ':f'=>$f, ':t'=>$t, ':p'=>$p]);
                }
            }

            if ($filesOK) {
                $insImg = $pdo->prepare("INSERT INTO images (flat_id, image_path, caption) VALUES (:fid,:path,:cap)");
                foreach ($filesOK as $idx => $f) {
                    $newName = 'flat_'.$flat_id.'_'.time().'_'.($idx+1).'.'.$f['ext'];
                    $destAbs = $uploadDirAbs . $newName;
                    if (!move_uploaded_file($f['tmp'], $destAbs)) {
                        throw new RuntimeException('Failed to move uploaded file.');
                    }
                    $webPath = $uploadDirWeb . $newName; // مثال: uploads/flat_1_...jpg
                    $insImg->execute([':fid'=>$flat_id, ':path'=>$webPath, ':cap'=>null]);
                }
            }

            $pdo->commit();

            require_once __DIR__ . '/../includes/notify.php';
            $mgrs = $pdo->query("SELECT id FROM users WHERE role='manager'")->fetchAll(PDO::FETCH_COLUMN);
            if ($mgrs) {
                foreach ($mgrs as $mgrUserId) {
                    notify($pdo, (int)$mgrUserId,
                        'New flat pending approval',
                        'A new flat (Ref: '.$reference_number.') was added and awaits your approval.',
                        BASE_URL.'/manager/approve_flats.php'
                    );
                }
            }

            header('Location: view_my_flats.php'); exit;

        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Error saving flat: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add New Flat</title>
  <link rel="stylesheet" href="../style.css">
  <style>
    .slot-row{display:grid;grid-template-columns:1.2fr 1fr 1fr 1.2fr auto;gap:8px;margin-bottom:8px}
    .slot-row input,.slot-row select{padding:6px}
    .slot-wrap{padding:12px;border:1px dashed #d1d5db;border-radius:12px;background:#fafafa;margin-top:12px}
    .slot-head{font-weight:600;margin-bottom:8px}
    .btn-sm{padding:6px 10px;border-radius:8px}
    .upload-wrap{padding:12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;margin-top:12px}
    .preview{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}
    .preview img{width:100px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #eee}
  </style>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="container about-box">
  <h1>Add New Flat</h1>

  <?php if ($errors): ?>
    <div style="background:#fef2f2;color:#7f1d1d;border:1px solid #fecaca;padding:12px;border-radius:10px;margin-bottom:12px">
      <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e,ENT_QUOTES) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" action="add_flat.php" enctype="multipart/form-data">
    <label>Location <input type="text" name="location" required></label>
    <label>Address <input type="text" name="address" required></label>
    <label>Monthly Price (NIS) <input type="number" name="rent_cost" required step="0.01"></label>
    <label>Available From <input type="date" name="available_from" required></label>
    <label>Available To <input type="date" name="available_to"></label>
    <label>Bedrooms <input type="number" name="bedrooms" required min="1" value="1"></label>
    <label>Bathrooms <input type="number" name="bathrooms" required min="1" value="1"></label>
    <label>Size (sqm) <input type="number" name="size_sqm" step="0.1"></label>

    <label><input type="checkbox" name="has_heating"> Heating</label>
    <label><input type="checkbox" name="has_air_conditioning"> Air Conditioning</label>
    <label><input type="checkbox" name="has_access_control"> Access Control</label>
    <label><input type="checkbox" name="has_parking"> Parking</label>
    <label>
      Backyard Type
      <select name="backyard_type">
        <option value="none">None</option>
        <option value="individual">Individual</option>
        <option value="shared">Shared</option>
      </select>
    </label>
    <label><input type="checkbox" name="has_playground"> Playground</label>
    <label><input type="checkbox" name="has_storage"> Storage</label>
    <label><input type="checkbox" name="is_furnished"> Furnished</label>

    <label>Additional Description
      <textarea name="description" rows="3"></textarea>
    </label>

    <div class="upload-wrap">
      <strong>Photos (JPG/PNG/WEBP, up to 5MB each)</strong><br>
      <input type="file" name="photos[]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple onchange="showPreview(this)">
      <div id="preview" class="preview"></div>
    </div>

    <!-- Viewing slots -->
    <div class="slot-wrap">
      <div class="slot-head">Viewing Times (optional)</div>
      <div id="slots">
        <div class="slot-row">
          <select name="slot_day[]">
            <option value="">Day…</option>
            <option>Sunday</option><option>Monday</option><option>Tuesday</option>
            <option>Wednesday</option><option>Thursday</option><option>Friday</option><option>Saturday</option>
          </select>
          <input type="time" name="slot_from[]" >
          <input type="time" name="slot_to[]" >
          <input type="text" name="slot_phone[]" placeholder="Contact phone">
          <button type="button" class="btn btn-sm" onclick="removeRow(this)">✖</button>
        </div>
      </div>
      <button type="button" class="btn btn-sm" onclick="addRow()">+ Add time</button>
    </div>

    <button type="submit" class="btn">Add Flat</button>
  </form>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
function addRow(){
  const row = document.createElement('div');
  row.className = 'slot-row';
  row.innerHTML = `
    <select name="slot_day[]">
      <option value="">Day…</option>
      <option>Sunday</option><option>Monday</option><option>Tuesday</option>
      <option>Wednesday</option><option>Thursday</option><option>Friday</option><option>Saturday</option>
    </select>
    <input type="time" name="slot_from[]">
    <input type="time" name="slot_to[]">
    <input type="text" name="slot_phone[]" placeholder="Contact phone">
    <button type="button" class="btn btn-sm" onclick="removeRow(this)">✖</button>`;
  document.getElementById('slots').appendChild(row);
}
function removeRow(btn){ btn.parentElement.remove(); }

function showPreview(input){
  const box = document.getElementById('preview');
  box.innerHTML='';
  if (!input.files) return;
  [...input.files].forEach(f=>{
    const url = URL.createObjectURL(f);
    const img = document.createElement('img');
    img.src = url;
    box.appendChild(img);
  });
}
</script>
</body>
</html>
