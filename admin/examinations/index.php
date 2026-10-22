<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$pageTitle = 'Examination Timetable';

$stmt = $pdo->query(
    "SELECT e.id, e.subject, e.exam_date, e.start_time, e.end_time, e.description, e.division, c.class_name
     FROM examinations e LEFT JOIN classes c ON c.id = e.class_id
     ORDER BY e.exam_date DESC"
);
$exams = $stmt->fetchAll();

require __DIR__ . '/../../includes/portal_header.php';
?>
<div class="card p-3">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h6 class="mb-0">Examination Timetable (<?= count($exams) ?>)</h6>
    <a href="<?= BASE_URL ?>/admin/examinations/add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add Exam</a>
  </div>
  <?php if (empty($exams)): ?>
    <div class="empty-state"><i class="bi bi-journal-text"></i>No examinations scheduled yet.</div>
  <?php else: ?>
    <div class="table-responsive-wrapper">
      <table class="table table-hover align-middle">
        <thead><tr><th>Class</th><th>Division</th><th>Subject</th><th>Date</th><th>Time</th><th>Notes</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($exams as $ex): ?>
            <tr>
              <td><?= e($ex['class_name'] ?? '-') ?></td>
              <td><?= e($ex['division']) ?></td>
              <td><?= e($ex['subject']) ?></td>
              <td><?= formatDate($ex['exam_date']) ?></td>
              <td><?= date('h:i A', strtotime($ex['start_time'])) ?>–<?= date('h:i A', strtotime($ex['end_time'])) ?></td>
              <td class="text-muted-custom"><?= e($ex['description'] ?? '-') ?></td>
              <td class="text-nowrap">
                <a href="<?= BASE_URL ?>/admin/examinations/edit.php?id=<?= $ex['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                <form action="<?= BASE_URL ?>/admin/examinations/delete.php" method="post" class="d-inline">
                  <?= csrfField() ?><input type="hidden" name="id" value="<?= $ex['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger btn-delete-confirm" data-label="this exam entry"><i class="bi bi-trash"></i></button>
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
