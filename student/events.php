<?php
/**
 * student/events.php
 * Events visible to logged-in students (both public and internal).
 * Read-only — no add/edit/delete controls, matching the public Events
 * page's redesigned card language (image first, then details).
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$pageTitle = 'Events';
require __DIR__ . '/../includes/portal_header.php';

$stmt = $pdo->prepare("SELECT event_name, date, time, description, photo, visibility FROM events ORDER BY date DESC");
$stmt->execute();
$events = $stmt->fetchAll();
?>

<h6 class="mb-3">Events</h6>
<?php if (empty($events)): ?>
  <div class="empty-state"><i class="bi bi-calendar-event"></i>No events have been published yet.</div>
<?php else: ?>
  <div class="events-grid">
    <?php foreach ($events as $ev): ?>
      <article class="event-card">
        <div class="event-card-image">
          <?php if (!empty($ev['photo'])): ?>
            <img src="<?= UPLOAD_URL . e($ev['photo']) ?>" alt="<?= e($ev['event_name']) ?>">
          <?php else: ?>
            <i class="bi bi-calendar-event placeholder-icon"></i>
          <?php endif; ?>
        </div>
        <div class="event-card-body">
          <div class="date">
            <i class="bi bi-calendar3"></i>
            <?= formatDate($ev['date']) ?><?= $ev['time'] ? ' · ' . date('h:i A', strtotime($ev['time'])) : '' ?>
          </div>
          <h3 class="h6"><?= e($ev['event_name']) ?></h3>
          <p><?= e($ev['description']) ?></p>
          <span class="badge bg-secondary event-card-badge text-uppercase"><?= e($ev['visibility']) ?></span>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
