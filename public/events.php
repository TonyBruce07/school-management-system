<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Events';

$stmt = $pdo->prepare("SELECT event_name, date, time, description, photo FROM events WHERE visibility = 'public' ORDER BY date DESC");
$stmt->execute();
$events = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <h1 class="section-title">Events</h1>
    <p class="section-subtitle">School events and important dates</p>
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
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
