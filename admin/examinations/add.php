<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$pageTitle = 'Add Examination';
$errors = [];
$old = ['class_id'=>'','division'=>'','subject'=>'','exam_date'=>'','start_time'=>'','end_time'=>'','description'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    foreach ($old as $k => $d) $old[$k] = trim($_POST[$k] ?? $d);

    if ($old['class_id'] === '') $errors[] = 'Class is required.';
    if ($old['division'] === '') $errors[] = 'Division is required.';
    if ($old['subject'] === '') $errors[] = 'Subject is required.';
    if ($old['exam_date'] === '') $errors[] = 'Exam date is required.';
    if ($old['start_time'] === '' || $old['end_time'] === '') $errors[] = 'Start and end time are required.';
    elseif ($old['end_time'] <= $old['start_time']) $errors[] = 'End time must be after start time.';

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO examinations (class_id, division, subject, exam_date, start_time, end_time, description)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$old['class_id'], $old['division'], $old['subject'], $old['exam_date'], $old['start_time'], $old['end_time'], $old['description'] ?: null]);
        setFlash('success', 'Examination added successfully.');
        header('Location: ' . BASE_URL . '/admin/examinations/index.php');
        exit;
    }
}

$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();
require __DIR__ . '/../../includes/portal_header.php';
?>
<div class="card p-4" style="max-width:680px;">
  <h6 class="mb-3">Add Examination</h6>
  <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <form method="post" class="needs-validation" novalidate>
    <?= csrfField() ?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Class <span class="required-mark">*</span></label>
        <select name="class_id" class="form-select" required>
          <option value="">Select class</option>
          <?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= $old['class_id']==$c['id']?'selected':'' ?>><?= e($c['class_name']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="col-md-6"><label class="form-label">Division <span class="required-mark">*</span></label>
        <input type="text" name="division" maxlength="5" class="form-control" value="<?= e($old['division']) ?>" required></div>
      <div class="col-md-6"><label class="form-label">Subject <span class="required-mark">*</span></label>
        <input type="text" name="subject" class="form-control" value="<?= e($old['subject']) ?>" required></div>
      <div class="col-md-6"><label class="form-label">Exam Date <span class="required-mark">*</span></label>
        <input type="date" name="exam_date" class="form-control" value="<?= e($old['exam_date']) ?>" required></div>
      <div class="col-md-6"><label class="form-label">Start Time <span class="required-mark">*</span></label>
        <input type="time" name="start_time" class="form-control" value="<?= e($old['start_time']) ?>" required></div>
      <div class="col-md-6"><label class="form-label">End Time <span class="required-mark">*</span></label>
        <input type="time" name="end_time" class="form-control" value="<?= e($old['end_time']) ?>" required></div>
      <div class="col-12"><label class="form-label">Notes</label>
        <input type="text" name="description" class="form-control" value="<?= e($old['description']) ?>"></div>
    </div>
    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save</button>
      <a href="<?= BASE_URL ?>/admin/examinations/index.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
