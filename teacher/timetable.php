<?php
/**
 * teacher/timetable.php
 * View-only: shows only slots where regular_timetable.teacher_id
 * matches this teacher. Structural timetable changes (which class/
 * teacher/subject is scheduled) remain an Admin-only operation
 * (admin/timetable/) since that affects the whole school's schedule,
 * not just this teacher's own view of it.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('teacher');

$teacher = getCurrentTeacher($pdo);
$pageTitle = 'My Timetable';

require __DIR__ . '/../includes/portal_header.php';

if (!$teacher) {
    echo '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Your account is not yet linked to a teacher record. Please contact the school administrator.</div>';
    require __DIR__ . '/../includes/portal_footer.php';
    exit;
}

$stmt = $pdo->prepare(
    "SELECT rt.day, rt.subject, rt.time_slot, rt.division, c.class_name
     FROM regular_timetable rt LEFT JOIN classes c ON c.id = rt.class_id
     WHERE rt.teacher_id = ?
     ORDER BY FIELD(rt.day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), rt.time_slot"
);
$stmt->execute([(int)$teacher['id']]);
$rows = $stmt->fetchAll();

$byDay = [];
foreach ($rows as $r) { $byDay[$r['day']][] = $r; }
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
?>
<div class="card p-3">
  <h6 class="mb-3">My Timetable</h6>
  <?php if (empty($rows)): ?>
    <div class="empty-state"><i class="bi bi-clock"></i>No timetable slots are currently assigned to you.</div>
  <?php else: foreach ($days as $day): if (empty($byDay[$day])) continue; ?>
    <h6 class="mt-3 text-muted-custom"><?= $day ?></h6>
    <div class="table-responsive-wrapper mb-2">
      <table class="table table-sm table-hover align-middle">
        <thead><tr><th>Time</th><th>Class</th><th>Subject</th></tr></thead>
        <tbody>
          <?php foreach ($byDay[$day] as $slot): ?>
            <tr>
              <td><?= e($slot['time_slot']) ?></td>
              <td><?= e($slot['class_name'] ?? '-') ?> <?= e($slot['division']) ?></td>
              <td><?= e($slot['subject']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endforeach; endif; ?>
</div>
<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
