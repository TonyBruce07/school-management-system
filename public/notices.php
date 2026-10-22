<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Notices';

$stmt = $pdo->prepare("SELECT title, description, date FROM notices WHERE visibility = 'public' ORDER BY date DESC");
$stmt->execute();
$notices = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <h1 class="section-title">Notices</h1>
    <p class="section-subtitle">Official announcements from the school</p>
    <?php if (empty($notices)): ?>
      <div class="empty-state"><i class="bi bi-megaphone"></i>No notices have been published yet.</div>
    <?php else: foreach ($notices as $n): ?>
      <div class="notice-card">
        <div class="date"><?= formatDate($n['date']) ?></div>
        <h6 class="mb-1"><?= e($n['title']) ?></h6>
        <p class="mb-0 text-muted-custom"><?= e($n['description']) ?></p>
      </div>
    <?php endforeach; endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
