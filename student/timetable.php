<?php
/**
 * student/timetable.php
 * Regular (weekly) timetable filtered to the student's own class + division.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$student = getCurrentStudent($pdo);
$pageTitle = 'Regular Timetable';

require __DIR__ . '/../includes/portal_header.php';

if (!$student) {
    echo '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Your account is not yet linked to a student record. Please contact the school administrator.</div>';
    require __DIR__ . '/../includes/portal_footer.php';
    exit;
}

$stmt = $pdo->prepare(
    "SELECT rt.day, rt.subject, rt.time_slot, t.name AS teacher_name
     FROM regular_timetable rt
     LEFT JOIN teachers t ON t.id = rt.teacher_id
     WHERE rt.class_id = ? AND rt.division = ?
     ORDER BY FIELD(rt.day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), rt.time_slot"
);
$stmt->execute([$student['class_id'], $student['division']]);
$rows = $stmt->fetchAll();

$byDay = [];
foreach ($rows as $r) {
    $byDay[$r['day']][] = $r;
}
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
?>

<div class="card p-3">
  <h6 class="mb-3">
    Regular Timetable — <?= e($student['class_name'] ?? '-') ?> · Division <?= e($student['division'] ?? '-') ?>
  </h6>
  <?php if (empty($rows)): ?>
    <div class="empty-state"><i class="bi bi-clock"></i>No timetable has been published yet.</div>
  <?php else: ?>
    <?php foreach ($days as $day): if (empty($byDay[$day])) continue; ?>
      <h6 class="mt-3 text-muted-custom"><?= $day ?></h6>
      <div class="table-responsive-wrapper mb-2">
        <table class="table table-sm table-hover align-middle">
          <thead><tr><th>Time</th><th>Subject</th><th>Teacher</th></tr></thead>
          <tbody>
            <?php foreach ($byDay[$day] as $slot): ?>
              <tr>
                <td><?= e($slot['time_slot']) ?></td>
                <td><?= e($slot['subject']) ?></td>
                <td class="text-muted-custom"><?= e($slot['teacher_name'] ?? '-') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
