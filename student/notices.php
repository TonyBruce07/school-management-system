<?php
/**
 * student/notices.php
 * Notices visible to logged-in students (both public and internal
 * notices — only public visitors are restricted to 'public' only).
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$pageTitle = 'Notices';
require __DIR__ . '/../includes/portal_header.php';

$stmt = $pdo->prepare("SELECT title, description, date FROM notices ORDER BY date DESC");
$stmt->execute();
$notices = $stmt->fetchAll();
?>

<div class="card p-3">
  <h6 class="mb-3">Notices</h6>
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

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
