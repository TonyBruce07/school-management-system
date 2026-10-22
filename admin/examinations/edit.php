<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$pageTitle = 'Edit Examination';
$errors = [];

$stmt = $pdo->prepare("SELECT * FROM examinations WHERE id = ?");
$stmt->execute([$id]);
$exam = $stmt->fetch();

require __DIR__ . '/../../includes/portal_header.php';

if (!$exam) {
    echo '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Examination entry not found.</div>';
    require __DIR__ . '/../../includes/portal_footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $classId = trim($_POST['class_id'] ?? '');
    $division = trim($_POST['division'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $examDate = trim($_POST['exam_date'] ?? '');
    $start = trim($_POST['start_time'] ?? '');
    $end = trim($_POST['end_time'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    if ($classId === '') $errors[] = 'Class is required.';
    if ($division === '') $errors[] = 'Division is required.';
    if ($subject === '') $errors[] = 'Subject is required.';
    if ($examDate === '') $errors[] = 'Exam date is required.';
    if ($start === '' || $end === '') $errors[] = 'Start and end time are required.';
    elseif ($end <= $start) $errors[] = 'End time must be after start time.';

    if (empty($errors)) {
        $upd = $pdo->prepare("UPDATE examinations SET class_id=?, division=?, subject=?, exam_date=?, start_time=?, end_time=?, description=? WHERE id=?");
        $upd->execute([$classId, $division, $subject, $examDate, $start, $end, $desc ?: null, $id]);
        setFlash('success', 'Examination updated successfully.');
        header('Location: ' . BASE_URL . '/admin/examinations/index.php');
        exit;
    }
    $exam = array_merge($exam, compact('classId','division','subject','examDate','start','end','desc'));
    $exam['class_id'] = $classId; $exam['exam_date'] = $examDate; $exam['start_time'] = $start; $exam['end_time'] = $end; $exam['description'] = $desc;
}

$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();
?>
<div class="card p-4" style="max-width:680px;">
  <h6 class="mb-3">Edit Examination</h6>
  <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <form method="post" class="needs-validation" novalidate>
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Class <span class="required-mark">*</span></label>
        <select name="class_id" class="form-select" required>
          <?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= $exam['class_id']==$c['id']?'selected':'' ?>><?= e($c['class_name']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="col-md-6"><label class="form-label">Division <span class="required-mark">*</span></label>
        <input type="text" name="division" maxlength="5" class="form-control" value="<?= e($exam['division']) ?>" required></div>
      <div class="col-md-6"><label class="form-label">Subject <span class="required-mark">*</span></label>
        <input type="text" name="subject" class="form-control" value="<?= e($exam['subject']) ?>" required></div>
      <div class="col-md-6"><label class="form-label">Exam Date <span class="required-mark">*</span></label>
        <input type="date" name="exam_date" class="form-control" value="<?= e($exam['exam_date']) ?>" required></div>
      <div class="col-md-6"><label class="form-label">Start Time <span class="required-mark">*</span></label>
        <input type="time" name="start_time" class="form-control" value="<?= e(substr($exam['start_time'],0,5)) ?>" required></div>
      <div class="col-md-6"><label class="form-label">End Time <span class="required-mark">*</span></label>
        <input type="time" name="end_time" class="form-control" value="<?= e(substr($exam['end_time'],0,5)) ?>" required></div>
      <div class="col-12"><label class="form-label">Notes</label>
        <input type="text" name="description" class="form-control" value="<?= e($exam['description']) ?>"></div>
    </div>
    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Changes</button>
      <a href="<?= BASE_URL ?>/admin/examinations/index.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
