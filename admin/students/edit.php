<?php
/**
 * admin/students/edit.php
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$pageTitle = 'Edit Student';
$errors = [];

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$student = $stmt->fetch();

require __DIR__ . '/../../includes/portal_header.php';

if (!$student) {
    echo '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Student not found.</div>';
    require __DIR__ . '/../../includes/portal_footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $name         = trim($_POST['name'] ?? '');
    $studentCode  = trim($_POST['student_id'] ?? '');
    $classId      = trim($_POST['class_id'] ?? '');
    $division     = trim($_POST['division'] ?? '');
    $dob          = trim($_POST['date_of_birth'] ?? '');
    $address      = trim($_POST['address'] ?? '');
    $contact      = trim($_POST['contact'] ?? '');
    $parentId     = $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;

    if ($name === '') $errors[] = 'Student name is required.';
    if ($studentCode === '') $errors[] = 'Student ID is required.';
    if ($classId === '') $errors[] = 'Class is required.';
    if ($division === '') $errors[] = 'Division is required.';

    if ($studentCode !== '') {
        $chk = $pdo->prepare("SELECT id FROM students WHERE student_id = ? AND id != ?");
        $chk->execute([$studentCode, $id]);
        if ($chk->fetch()) $errors[] = 'This Student ID is already used by another student.';
    }

    $photoFilename = $student['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $uploaded = handleImageUpload($_FILES['photo'], 'student');
        if ($uploaded === null) {
            $errors[] = 'Photo upload failed. Use JPG/PNG/WebP under 2 MB.';
        } else {
            $photoFilename = $uploaded;
        }
    }

    if (empty($errors)) {
        $upd = $pdo->prepare(
            "UPDATE students SET student_id=?, name=?, photo=?, class_id=?, division=?, date_of_birth=?, address=?, parent_id=?, contact=?
             WHERE id = ?"
        );
        $upd->execute([$studentCode, $name, $photoFilename, $classId, $division, $dob ?: null, $address ?: null, $parentId, $contact ?: null, $id]);

        setFlash('success', 'Student updated successfully.');
        header('Location: ' . BASE_URL . '/admin/students/view.php?id=' . $id);
        exit;
    }
    $student = array_merge($student, [
        'student_id' => $studentCode, 'name' => $name, 'class_id' => $classId, 'division' => $division,
        'date_of_birth' => $dob, 'address' => $address, 'contact' => $contact, 'parent_id' => $parentId,
    ]);
}

$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();
$parents = $pdo->query("SELECT id, name, contact_no FROM parents ORDER BY name")->fetchAll();
?>

<div class="card p-4" style="max-width:820px;">
  <h6 class="mb-3">Edit Student — <?= e($student['name']) ?></h6>

  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Student ID <span class="required-mark">*</span></label>
        <input type="text" name="student_id" class="form-control" value="<?= e($student['student_id']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Full Name <span class="required-mark">*</span></label>
        <input type="text" name="name" class="form-control" value="<?= e($student['name']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Class <span class="required-mark">*</span></label>
        <select name="class_id" class="form-select" required>
          <?php foreach ($classes as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $student['class_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['class_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Division <span class="required-mark">*</span></label>
        <input type="text" name="division" class="form-control" maxlength="5" value="<?= e($student['division']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Date of Birth</label>
        <input type="date" name="date_of_birth" class="form-control" value="<?= e($student['date_of_birth']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Contact Number</label>
        <input type="text" name="contact" class="form-control" value="<?= e($student['contact']) ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Address</label>
        <input type="text" name="address" class="form-control" value="<?= e($student['address']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Parent / Guardian</label>
        <select name="parent_id" class="form-select">
          <option value="">— None —</option>
          <?php foreach ($parents as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $student['parent_id'] == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?> (<?= e($p['contact_no']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Replace Photo</label>
        <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp" class="form-control">
      </div>
    </div>
    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Changes</button>
      <a href="<?= BASE_URL ?>/admin/students/view.php?id=<?= $id ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
