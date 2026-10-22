<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$pageTitle = 'Edit Timetable Slot';
$errors = [];
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

$stmt = $pdo->prepare("SELECT * FROM regular_timetable WHERE id = ?");
$stmt->execute([$id]);
$slot = $stmt->fetch();

require __DIR__ . '/../../includes/portal_header.php';

if (!$slot) {
    echo '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Timetable slot not found.</div>';
    require __DIR__ . '/../../includes/portal_footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $classId = trim($_POST['class_id'] ?? '');
    $division = trim($_POST['division'] ?? '');
    $day = trim($_POST['day'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $teacherId = trim($_POST['teacher_id'] ?? '');
    $timeSlot = trim($_POST['time_slot'] ?? '');

    if ($classId === '') $errors[] = 'Class is required.';
    if ($division === '') $errors[] = 'Division is required.';
    if (!in_array($day, $days, true)) $errors[] = 'A valid day is required.';
    if ($subject === '') $errors[] = 'Subject is required.';
    if ($timeSlot === '') $errors[] = 'Time slot is required.';

    if (empty($errors)) {
        $upd = $pdo->prepare("UPDATE regular_timetable SET class_id=?, division=?, day=?, subject=?, teacher_id=?, time_slot=? WHERE id=?");
        $upd->execute([$classId, $division, $day, $subject, $teacherId ?: null, $timeSlot, $id]);
        setFlash('success', 'Timetable slot updated.');
        header('Location: ' . BASE_URL . '/admin/timetable/index.php');
        exit;
    }
    $slot = array_merge($slot, ['class_id'=>$classId,'division'=>$division,'day'=>$day,'subject'=>$subject,'teacher_id'=>$teacherId,'time_slot'=>$timeSlot]);
}

$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();
$teachers = $pdo->query("SELECT id, name FROM teachers ORDER BY name")->fetchAll();
?>
<div class="card p-4" style="max-width:680px;">
  <h6 class="mb-3">Edit Timetable Slot</h6>
  <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <form method="post" class="needs-validation" novalidate>
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Class <span class="required-mark">*</span></label>
        <select name="class_id" class="form-select" required>
          <?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= $slot['class_id']==$c['id']?'selected':'' ?>><?= e($c['class_name']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="col-md-6"><label class="form-label">Division <span class="required-mark">*</span></label>
        <input type="text" name="division" maxlength="5" class="form-control" value="<?= e($slot['division']) ?>" required></div>
      <div class="col-md-6"><label class="form-label">Day <span class="required-mark">*</span></label>
        <select name="day" class="form-select" required>
          <?php foreach ($days as $d): ?><option value="<?= $d ?>" <?= $slot['day']===$d?'selected':'' ?>><?= $d ?></option><?php endforeach; ?>
        </select></div>
      <div class="col-md-6"><label class="form-label">Time Slot <span class="required-mark">*</span></label>
        <input type="text" name="time_slot" class="form-control" value="<?= e($slot['time_slot']) ?>" required></div>
      <div class="col-md-6"><label class="form-label">Subject <span class="required-mark">*</span></label>
        <input type="text" name="subject" class="form-control" value="<?= e($slot['subject']) ?>" required></div>
      <div class="col-md-6"><label class="form-label">Teacher</label>
        <select name="teacher_id" class="form-select">
          <option value="">— Unassigned —</option>
          <?php foreach ($teachers as $t): ?><option value="<?= $t['id'] ?>" <?= $slot['teacher_id']==$t['id']?'selected':'' ?>><?= e($t['name']) ?></option><?php endforeach; ?>
        </select></div>
    </div>
    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Changes</button>
      <a href="<?= BASE_URL ?>/admin/timetable/index.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
