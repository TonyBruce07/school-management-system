<?php
/**
 * principal/timetable.php
 * Read-only view of the full regular timetable. No add/edit/delete.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('principal');

$pageTitle = 'Regular Timetable';

$stmt = $pdo->query(
    "SELECT rt.day, rt.subject, rt.time_slot, rt.division, c.class_name, t.name AS teacher_name
     FROM regular_timetable rt
     LEFT JOIN classes c ON c.id = rt.class_id
     LEFT JOIN teachers t ON t.id = rt.teacher_id
     ORDER BY c.class_name, rt.division, FIELD(rt.day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), rt.time_slot"
);
$rows = $stmt->fetchAll();

require __DIR__ . '/../includes/portal_header.php';
?>
<div class="card p-3">
  <h6 class="mb-3">Regular Timetable (<?= count($rows) ?> slots)</h6>
  <?php if (empty($rows)): ?>
    <div class="empty-state"><i class="bi bi-clock"></i>No timetable slots have been added yet.</div>
  <?php else: ?>
    <div class="table-responsive-wrapper">
      <table class="table table-hover align-middle">
        <thead><tr><th>Class</th><th>Division</th><th>Day</th><th>Time</th><th>Subject</th><th>Teacher</th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= e($r['class_name'] ?? '-') ?></td>
              <td><?= e($r['division']) ?></td>
              <td><?= e($r['day']) ?></td>
              <td><?= e($r['time_slot']) ?></td>
              <td><?= e($r['subject']) ?></td>
              <td class="text-muted-custom"><?= e($r['teacher_name'] ?? '-') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
