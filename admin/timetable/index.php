<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$pageTitle = 'Regular Timetable';

$stmt = $pdo->query(
    "SELECT rt.id, rt.day, rt.subject, rt.time_slot, rt.division, c.class_name, t.name AS teacher_name
     FROM regular_timetable rt
     LEFT JOIN classes c ON c.id = rt.class_id
     LEFT JOIN teachers t ON t.id = rt.teacher_id
     ORDER BY c.class_name, rt.division, FIELD(rt.day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), rt.time_slot"
);
$rows = $stmt->fetchAll();

require __DIR__ . '/../../includes/portal_header.php';
?>
<div class="card p-3">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h6 class="mb-0">Regular Timetable (<?= count($rows) ?> slots)</h6>
    <a href="<?= BASE_URL ?>/admin/timetable/add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add Slot</a>
  </div>
  <?php if (empty($rows)): ?>
    <div class="empty-state"><i class="bi bi-clock"></i>No timetable slots have been added yet.</div>
  <?php else: ?>
    <div class="table-responsive-wrapper">
      <table class="table table-hover align-middle">
        <thead><tr><th>Class</th><th>Division</th><th>Day</th><th>Time</th><th>Subject</th><th>Teacher</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= e($r['class_name'] ?? '-') ?></td>
              <td><?= e($r['division']) ?></td>
              <td><?= e($r['day']) ?></td>
              <td><?= e($r['time_slot']) ?></td>
              <td><?= e($r['subject']) ?></td>
              <td class="text-muted-custom"><?= e($r['teacher_name'] ?? '-') ?></td>
              <td class="text-nowrap">
                <a href="<?= BASE_URL ?>/admin/timetable/edit.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                <form action="<?= BASE_URL ?>/admin/timetable/delete.php" method="post" class="d-inline">
                  <?= csrfField() ?><input type="hidden" name="id" value="<?= $r['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger btn-delete-confirm" data-label="this timetable slot"><i class="bi bi-trash"></i></button>
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
