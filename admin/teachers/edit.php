<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$pageTitle = 'Edit Teacher';
$errors = [];

$stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = ?");
$stmt->execute([$id]);
$teacher = $stmt->fetch();

require __DIR__ . '/../../includes/portal_header.php';

if (!$teacher) {
    echo '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Teacher not found.</div>';
    require __DIR__ . '/../../includes/portal_footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $teacherCode = trim($_POST['teacher_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $contact = trim($_POST['contact_no'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($teacherCode === '') $errors[] = 'Teacher ID is required.';
    if ($name === '') $errors[] = 'Teacher name is required.';
    if ($teacherCode !== '') {
        $chk = $pdo->prepare("SELECT id FROM teachers WHERE teacher_id = ? AND id != ?");
        $chk->execute([$teacherCode, $id]);
        if ($chk->fetch()) $errors[] = 'This Teacher ID is used by another teacher.';
    }

    if (empty($errors)) {
        $upd = $pdo->prepare("UPDATE teachers SET teacher_id=?, name=?, subject=?, contact_no=?, email=? WHERE id=?");
        $upd->execute([$teacherCode, $name, $subject ?: null, $contact ?: null, $email ?: null, $id]);
        setFlash('success', 'Teacher updated successfully.');
        header('Location: ' . BASE_URL . '/admin/teachers/index.php');
        exit;
    }
    $teacher = array_merge($teacher, ['teacher_id'=>$teacherCode,'name'=>$name,'subject'=>$subject,'contact_no'=>$contact,'email'=>$email]);
}
?>
<div class="card p-4" style="max-width:720px;">
  <h6 class="mb-3">Edit Teacher — <?= e($teacher['name']) ?></h6>
  <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <form method="post" class="needs-validation" novalidate>
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Teacher ID <span class="required-mark">*</span></label>
        <input type="text" name="teacher_id" class="form-control" value="<?= e($teacher['teacher_id']) ?>" required></div>
      <div class="col-md-6"><label class="form-label">Full Name <span class="required-mark">*</span></label>
        <input type="text" name="name" class="form-control" value="<?= e($teacher['name']) ?>" required></div>
      <div class="col-md-6"><label class="form-label">Subject</label>
        <input type="text" name="subject" class="form-control" value="<?= e($teacher['subject']) ?>"></div>
      <div class="col-md-6"><label class="form-label">Contact Number</label>
        <input type="text" name="contact_no" class="form-control" value="<?= e($teacher['contact_no']) ?>"></div>
      <div class="col-md-6"><label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= e($teacher['email']) ?>"></div>
    </div>
    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Changes</button>
      <a href="<?= BASE_URL ?>/admin/teachers/index.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
