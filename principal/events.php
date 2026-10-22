<?php
/**
 * principal/events.php
 * Read-only view of all events (public + internal), including photos
 * via the same UPLOAD_URL mechanism already used by Admin, Teacher,
 * and Student — no separate upload/storage system introduced.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('principal');

$pageTitle = 'Events';

$stmt = $pdo->query("SELECT event_name, date, time, description, photo, visibility FROM events ORDER BY date DESC");
$events = $stmt->fetchAll();

require __DIR__ . '/../includes/portal_header.php';
?>
<div class="card p-3">
  <h6 class="mb-3">Events (<?= count($events) ?>)</h6>
  <?php if (empty($events)): ?>
    <div class="empty-state"><i class="bi bi-calendar-event"></i>No events have been published yet.</div>
  <?php else: foreach ($events as $ev): ?>
    <div class="notice-card d-flex gap-3 align-items-start">
      <?php if (!empty($ev['photo'])): ?>
        <img src="<?= UPLOAD_URL . e($ev['photo']) ?>" alt="<?= e($ev['event_name']) ?>" style="width:64px;height:64px;object-fit:cover;border-radius:6px;flex-shrink:0;">
      <?php endif; ?>
      <div>
        <div class="date"><?= formatDate($ev['date']) ?><?= $ev['time'] ? ' · ' . date('h:i A', strtotime($ev['time'])) : '' ?> · <span class="text-uppercase small"><?= e($ev['visibility']) ?></span></div>
        <h6 class="mb-1"><?= e($ev['event_name']) ?></h6>
        <p class="mb-0 text-muted-custom"><?= e($ev['description']) ?></p>
      </div>
    </div>
  <?php endforeach; endif; ?>
</div>
<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
