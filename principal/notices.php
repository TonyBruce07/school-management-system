<?php
/**
 * principal/notices.php
 * Read-only view of all notices (public + internal). No approval
 * workflow exists in the schema, so none is invented here — this is
 * oversight/viewing only, per the current project requirements.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('principal');

$pageTitle = 'Notices';

$stmt = $pdo->query("SELECT title, description, date, visibility FROM notices ORDER BY date DESC");
$notices = $stmt->fetchAll();

require __DIR__ . '/../includes/portal_header.php';
?>
<div class="card p-3">
  <h6 class="mb-3">Notices (<?= count($notices) ?>)</h6>
  <?php if (empty($notices)): ?>
    <div class="empty-state"><i class="bi bi-megaphone"></i>No notices have been published yet.</div>
  <?php else: foreach ($notices as $n): ?>
    <div class="notice-card">
      <div class="date"><?= formatDate($n['date']) ?> · <span class="text-uppercase small"><?= e($n['visibility']) ?></span></div>
      <h6 class="mb-1"><?= e($n['title']) ?></h6>
      <p class="mb-0 text-muted-custom"><?= e($n['description']) ?></p>
    </div>
  <?php endforeach; endif; ?>
</div>
<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
