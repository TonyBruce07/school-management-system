<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$pageTitle = 'Add Timetable Slot';
$errors = [];
$old = ['class_id'=>'','division'=>'','day'=>'','subject'=>'','teacher_id'=>'','time_slot'=>''];
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    foreach ($old as $k => $d) $old[$k] = trim($_POST[$k] ?? $d);

    if ($old['class_id'] === '') $errors[] = 'Class is required.';
    if ($old['division'] === '') $errors[] = 'Division is required.';
    if (!in_array($old['day'], $days, true)) $errors[] = 'A valid day is required.';
    if ($old['subject'] === '') $errors[] = 'Subject is required.';
    if ($old['time_slot'] === '') $errors[] = 'Time slot is required.';

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO regular_timetable (class_id, division, day, subject, teacher_id, time_slot) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$old['class_id'], $old['division'], $old['day'], $old['subject'], $old['teacher_id'] ?: null, $old['time_slot']]);
        setFlash('success', 'Timetable slot added.');
        header('Location: ' . BASE_URL . '/admin/timetable/index.php');
        exit;
    }
}

$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();
$teachers = $pdo->query("SELECT id, name FROM teachers ORDER BY name")->fetchAll();
require __DIR__ . '/../../includes/portal_header.php';
?>
<div class="card p-4" style="max-width:680px;">
  <h6 class="mb-3">Add Timetable Slot</h6>
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
      <div class="col-md-6"><label class="form-label">Day <span class="required-mark">*</span></label>
        <select name="day" class="form-select" required>
          <option value="">Select day</option>
          <?php foreach ($days as $d): ?><option value="<?= $d ?>" <?= $old['day']===$d?'selected':'' ?>><?= $d ?></option><?php endforeach; ?>
        </select></div>
      <div class="col-md-6"><label class="form-label">Time Slot <span class="required-mark">*</span></label>
        <input type="text" name="time_slot" class="form-control" placeholder="e.g. 9:00 - 9:45" value="<?= e($old['time_slot']) ?>" required></div>
      <div class="col-md-6"><label class="form-label">Subject <span class="required-mark">*</span></label>
        <input type="text" name="subject" class="form-control" value="<?= e($old['subject']) ?>" required></div>
      <div class="col-md-6"><label class="form-label">Teacher</label>
        <select name="teacher_id" class="form-select">
          <option value="">— Unassigned —</option>
          <?php foreach ($teachers as $t): ?><option value="<?= $t['id'] ?>" <?= $old['teacher_id']==$t['id']?'selected':'' ?>><?= e($t['name']) ?></option><?php endforeach; ?>
        </select></div>
    </div>
    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save</button>
      <a href="<?= BASE_URL ?>/admin/timetable/index.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
