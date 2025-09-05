<?php
$image = isset($flat['image_path']) && $flat['image_path'] ? $flat['image_path'] : 'assets/no-image.png';
$imgUrl = rtrim(BASE_URL,'/') . '/' . ltrim($image,'/'); 
?>
<div class="flat-card">
  <a href="<?= e(BASE_URL) ?>/pages/flat_detail.php?ref=<?= urlencode($flat['reference_number']) ?>">
    <img src="<?= e($imgUrl) ?>" alt="Flat image" style="width:100%;height:180px;object-fit:cover;">
    <h3><?= e($flat['location']) ?></h3>
  </a>
  <div class="flat-info" style="padding:8px 0;">
    <p><strong>Price:</strong> <?= number_format($flat['rent_cost'], 2) ?> NIS</p>
    <p><?= (int)$flat['bedrooms'] ?> bd / <?= (int)$flat['bathrooms'] ?> bath</p>
    <p><strong>Available from:</strong> <?= e($flat['available_from']) ?></p>
    <p><strong>Furnished:</strong> <?= $flat['is_furnished'] ? 'Yes' : 'No' ?></p>
    <p><strong>Ref#:</strong> <?= e($flat['reference_number']) ?></p>
  </div>
  <a href="<?= e(BASE_URL) ?>/pages/rent.php?ref=<?= urlencode($flat['reference_number']) ?>" class="btn" style="padding:4px 14px;font-size:14px;">Rent</a>
</div>
