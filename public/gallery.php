<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Gallery';

$stmt = $pdo->prepare("SELECT title, image_path, description FROM gallery ORDER BY created_at DESC");
$stmt->execute();
$items = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <h1 class="section-title">Gallery</h1>
    <p class="section-subtitle">Moments from school life</p>
    <div class="row g-3">
      <?php if (empty($items)): ?>
        <div class="col-12"><div class="empty-state"><i class="bi bi-images"></i>No gallery images have been uploaded yet.</div></div>
      <?php else: foreach ($items as $g): ?>
        <div class="col-md-4 col-6 gallery-item">
          <img src="<?= UPLOAD_URL . e($g['image_path']) ?>" alt="<?= e($g['title']) ?>" title="<?= e($g['description']) ?>">
          <p class="small text-muted-custom mt-1 mb-0"><?= e($g['title']) ?></p>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
