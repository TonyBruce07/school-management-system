<?php
/**
 * student/examination.php
 * Examination timetable filtered to the student's own class + division.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$student = getCurrentStudent($pdo);
$pageTitle = 'Examination Timetable';

require __DIR__ . '/../includes/portal_header.php';

if (!$student) {
    echo '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Your account is not yet linked to a student record. Please contact the school administrator.</div>';
    require __DIR__ . '/../includes/portal_footer.php';
    exit;
}

$stmt = $pdo->prepare(
    "SELECT subject, exam_date, start_time, end_time, description
     FROM examinations
     WHERE class_id = ? AND division = ?
     ORDER BY exam_date ASC"
);
$stmt->execute([$student['class_id'], $student['division']]);
$exams = $stmt->fetchAll();
?>

<div class="card p-3">
  <h6 class="mb-3">
    Examination Timetable — <?= e($student['class_name'] ?? '-') ?> · Division <?= e($student['division'] ?? '-') ?>
  </h6>
  <?php if (empty($exams)): ?>
    <div class="empty-state"><i class="bi bi-journal-text"></i>No examination timetable has been published yet.</div>
  <?php else: ?>
    <div class="table-responsive-wrapper">
      <table class="table table-hover align-middle">
        <thead>
          <tr><th>Subject</th><th>Date</th><th>Time</th><th>Notes</th></tr>
        </thead>
        <tbody>
          <?php foreach ($exams as $ex): ?>
            <tr>
              <td><?= e($ex['subject']) ?></td>
              <td><?= formatDate($ex['exam_date']) ?></td>
              <td><?= date('h:i A', strtotime($ex['start_time'])) ?> – <?= date('h:i A', strtotime($ex['end_time'])) ?></td>
              <td class="text-muted-custom"><?= e($ex['description'] ?? '-') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
