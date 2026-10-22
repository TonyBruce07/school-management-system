<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$pageTitle = 'Events';
$stmt = $pdo->query("SELECT id, event_name, date, time, visibility, photo FROM events ORDER BY date DESC");
$events = $stmt->fetchAll();

require __DIR__ . '/../../includes/portal_header.php';
?>
<div class="card p-3">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h6 class="mb-0">Events (<?= count($events) ?>)</h6>
    <a href="<?= BASE_URL ?>/admin/events/add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add Event</a>
  </div>
  <?php if (empty($events)): ?>
    <div class="empty-state"><i class="bi bi-calendar-event"></i>No events have been published yet.</div>
  <?php else: ?>
    <div class="table-responsive-wrapper">
      <table class="table table-hover align-middle">
        <thead><tr><th>Photo</th><th>Event</th><th>Date</th><th>Time</th><th>Visibility</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($events as $ev): ?>
          <tr>
            <td>
              <?php if ($ev['photo']): ?>
                <img src="<?= UPLOAD_URL . e($ev['photo']) ?>" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
              <?php else: ?>
                <div class="d-flex align-items-center justify-content-center bg-light" style="width:48px;height:48px;border-radius:6px;"><i class="bi bi-image text-muted-custom"></i></div>
              <?php endif; ?>
            </td>
            <td><?= e($ev['event_name']) ?></td>
            <td><?= formatDate($ev['date']) ?></td>
            <td><?= $ev['time'] ? date('h:i A', strtotime($ev['time'])) : '-' ?></td>
            <td>
              <?php if ($ev['visibility'] === 'public'): ?><span class="badge bg-primary">Public</span>
              <?php else: ?><span class="badge bg-secondary">Internal</span><?php endif; ?>
            </td>
            <td class="text-nowrap">
              <a href="<?= BASE_URL ?>/admin/events/edit.php?id=<?= $ev['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
              <form action="<?= BASE_URL ?>/admin/events/delete.php" method="post" class="d-inline">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= $ev['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger btn-delete-confirm" data-label="this event" title="Delete"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
