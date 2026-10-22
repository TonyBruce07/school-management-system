<?php
/**
 * admin/students/add.php
 * Add a new student. Optionally creates the linked login account
 * (users row, role='student') and/or a new parent record in the
 * same transaction, matching the existing schema's relationships.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$pageTitle = 'Add Student';
$errors = [];
$old = [
    'student_id' => '', 'name' => '', 'class_id' => '', 'division' => '',
    'date_of_birth' => '', 'address' => '', 'contact' => '',
    'parent_id' => '', 'new_parent_name' => '', 'new_parent_contact' => '', 'new_parent_email' => '',
    'create_login' => '', 'username' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    foreach ($old as $key => $default) {
        $old[$key] = trim($_POST[$key] ?? $default);
    }

    if ($old['student_id'] === '') $errors[] = 'Student ID is required.';
    if ($old['name'] === '') $errors[] = 'Student name is required.';
    if ($old['class_id'] === '') $errors[] = 'Class is required.';
    if ($old['division'] === '') $errors[] = 'Division is required.';

    // Uniqueness check for student_id
    if ($old['student_id'] !== '') {
        $chk = $pdo->prepare("SELECT id FROM students WHERE student_id = ?");
        $chk->execute([$old['student_id']]);
        if ($chk->fetch()) $errors[] = 'This Student ID already exists.';
    }

    // Parent resolution: either an existing parent_id, or new parent fields
    $parentId = $old['parent_id'] !== '' ? (int)$old['parent_id'] : null;
    $creatingNewParent = $old['parent_id'] === '' && $old['new_parent_name'] !== '';

    // Optional login account
    $createLogin = $old['create_login'] === '1';
    $username = $old['username'];
    $tempPassword = null;
    if ($createLogin) {
        if ($username === '') $errors[] = 'Username is required to create a login account.';
        else {
            $chkU = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $chkU->execute([$username]);
            if ($chkU->fetch()) $errors[] = 'That username is already taken.';
        }
    }

    // Photo upload (optional)
    $photoFilename = null;
    if (!empty($_FILES['photo']['name'])) {
        $photoFilename = handleImageUpload($_FILES['photo'], 'student');
        if ($photoFilename === null) $errors[] = 'Photo upload failed. Use JPG/PNG/WebP under 2 MB.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            if ($creatingNewParent) {
                $pStmt = $pdo->prepare("INSERT INTO parents (name, contact_no, email) VALUES (?, ?, ?)");
                $pStmt->execute([$old['new_parent_name'], $old['new_parent_contact'], $old['new_parent_email'] ?: null]);
                $parentId = (int)$pdo->lastInsertId();
            }

            $userId = null;
            if ($createLogin) {
                $tempPassword = bin2hex(random_bytes(4)); // e.g. "a1b2c3d4"
                $hash = password_hash($tempPassword, PASSWORD_DEFAULT);
                $uStmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, status) VALUES (?, ?, 'student', 'active')");
                $uStmt->execute([$username, $hash]);
                $userId = (int)$pdo->lastInsertId();
            }

            $sStmt = $pdo->prepare(
                "INSERT INTO students (user_id, student_id, name, photo, class_id, division, date_of_birth, address, parent_id, contact)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $sStmt->execute([
                $userId, $old['student_id'], $old['name'], $photoFilename,
                $old['class_id'], $old['division'], $old['date_of_birth'] ?: null,
                $old['address'] ?: null, $parentId, $old['contact'] ?: null,
            ]);

            $pdo->commit();

            $msg = 'Student added successfully.';
            if ($createLogin) {
                $msg .= " Login username: {$username}, temporary password: {$tempPassword} (share this with the student/parent securely).";
            }
            setFlash('success', $msg);
            header('Location: ' . BASE_URL . '/admin/students/index.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Could not save the student. Please try again.';
        }
    }
}

$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();
$parents = $pdo->query("SELECT id, name, contact_no FROM parents ORDER BY name")->fetchAll();

require __DIR__ . '/../../includes/portal_header.php';
?>

<div class="card p-4" style="max-width:820px;">
  <h6 class="mb-3">Add Student</h6>

  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
    <?= csrfField() ?>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Student ID <span class="required-mark">*</span></label>
        <input type="text" name="student_id" class="form-control" value="<?= e($old['student_id']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Full Name <span class="required-mark">*</span></label>
        <input type="text" name="name" class="form-control" value="<?= e($old['name']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Class <span class="required-mark">*</span></label>
        <select name="class_id" class="form-select" required>
          <option value="">Select class</option>
          <?php foreach ($classes as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $old['class_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['class_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Division <span class="required-mark">*</span></label>
        <input type="text" name="division" class="form-control" maxlength="5" value="<?= e($old['division']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Date of Birth</label>
        <input type="date" name="date_of_birth" class="form-control" value="<?= e($old['date_of_birth']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Contact Number</label>
        <input type="text" name="contact" class="form-control" value="<?= e($old['contact']) ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Address</label>
        <input type="text" name="address" class="form-control" value="<?= e($old['address']) ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Photo</label>
        <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp" class="form-control">
      </div>
    </div>

    <hr class="my-4">
    <h6 class="mb-3">Parent / Guardian</h6>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Existing Parent</label>
        <select name="parent_id" class="form-select">
          <option value="">— None / create new below —</option>
          <?php foreach ($parents as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $old['parent_id'] == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?> (<?= e($p['contact_no']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <p class="small text-muted-custom mt-3 mb-2">Or create a new parent record:</p>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Parent Name</label>
        <input type="text" name="new_parent_name" class="form-control" value="<?= e($old['new_parent_name']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Parent Contact</label>
        <input type="text" name="new_parent_contact" class="form-control" value="<?= e($old['new_parent_contact']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Parent Email</label>
        <input type="email" name="new_parent_email" class="form-control" value="<?= e($old['new_parent_email']) ?>">
      </div>
    </div>

    <hr class="my-4">
    <h6 class="mb-3">Login Account (optional)</h6>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" value="1" name="create_login" id="createLogin" <?= $old['create_login'] === '1' ? 'checked' : '' ?>>
      <label class="form-check-label" for="createLogin">Create a portal login account for this student</label>
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" value="<?= e($old['username']) ?>" placeholder="e.g. student123">
        <div class="form-text">A secure temporary password will be generated and shown once after saving.</div>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Student</button>
      <a href="<?= BASE_URL ?>/admin/students/index.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
