<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'School Achievements';

$stmt = $pdo->prepare("SELECT title, description, date, image FROM achievements ORDER BY date DESC");
$stmt->execute();
$achievements = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <h1 class="section-title">School Achievements</h1>
    <p class="section-subtitle">Recognitions, awards and milestones</p>
    <div class="row g-4">
      <?php if (empty($achievements)): ?>
        <div class="col-12"><div class="empty-state"><i class="bi bi-trophy"></i>No achievements have been published yet.</div></div>
      <?php else: foreach ($achievements as $a): ?>
        <div class="col-md-4">
          <div class="info-card">
            <i class="bi bi-trophy text-warning fs-3 mb-2"></i>
            <h5><?= e($a['title']) ?></h5>
            <?php if ($a['date']): ?><div class="small text-muted-custom mb-2"><?= formatDate($a['date']) ?></div><?php endif; ?>
            <p class="mb-0"><?= e($a['description']) ?></p>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
