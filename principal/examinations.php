<?php
/**
 * principal/examinations.php
 * Read-only view of the full examination timetable. No add/edit/delete.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('principal');

$pageTitle = 'Examination Timetable';

$stmt = $pdo->query(
    "SELECT ex.subject, ex.exam_date, ex.start_time, ex.end_time, ex.description, ex.division, c.class_name
     FROM examinations ex LEFT JOIN classes c ON c.id = ex.class_id
     ORDER BY ex.exam_date DESC"
);
$exams = $stmt->fetchAll();

require __DIR__ . '/../includes/portal_header.php';
?>
<div class="card p-3">
  <h6 class="mb-3">Examination Timetable (<?= count($exams) ?>)</h6>
  <?php if (empty($exams)): ?>
    <div class="empty-state"><i class="bi bi-journal-text"></i>No examinations scheduled yet.</div>
  <?php else: ?>
    <div class="table-responsive-wrapper">
      <table class="table table-hover align-middle">
        <thead><tr><th>Class</th><th>Division</th><th>Subject</th><th>Date</th><th>Time</th><th>Notes</th></tr></thead>
        <tbody>
          <?php foreach ($exams as $ex): ?>
            <tr>
              <td><?= e($ex['class_name'] ?? '-') ?></td>
              <td><?= e($ex['division']) ?></td>
              <td><?= e($ex['subject']) ?></td>
              <td><?= formatDate($ex['exam_date']) ?></td>
              <td><?= date('h:i A', strtotime($ex['start_time'])) ?>–<?= date('h:i A', strtotime($ex['end_time'])) ?></td>
              <td class="text-muted-custom"><?= e($ex['description'] ?? '-') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
