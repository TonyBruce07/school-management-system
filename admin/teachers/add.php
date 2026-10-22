<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$pageTitle = 'Add Teacher';
$errors = [];
$old = ['teacher_id'=>'','name'=>'','subject'=>'','contact_no'=>'','email'=>'','create_login'=>'','username'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    foreach ($old as $k => $d) $old[$k] = trim($_POST[$k] ?? $d);

    if ($old['teacher_id'] === '') $errors[] = 'Teacher ID is required.';
    if ($old['name'] === '') $errors[] = 'Teacher name is required.';

    if ($old['teacher_id'] !== '') {
        $chk = $pdo->prepare("SELECT id FROM teachers WHERE teacher_id = ?");
        $chk->execute([$old['teacher_id']]);
        if ($chk->fetch()) $errors[] = 'This Teacher ID already exists.';
    }

    $createLogin = $old['create_login'] === '1';
    $tempPassword = null;
    if ($createLogin) {
        if ($old['username'] === '') $errors[] = 'Username is required to create a login account.';
        else {
            $chkU = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $chkU->execute([$old['username']]);
            if ($chkU->fetch()) $errors[] = 'That username is already taken.';
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $userId = null;
            if ($createLogin) {
                $tempPassword = bin2hex(random_bytes(4));
                $hash = password_hash($tempPassword, PASSWORD_DEFAULT);
                $u = $pdo->prepare("INSERT INTO users (username, password_hash, role, status) VALUES (?, ?, 'teacher', 'active')");
                $u->execute([$old['username'], $hash]);
                $userId = (int)$pdo->lastInsertId();
            }
            $t = $pdo->prepare("INSERT INTO teachers (user_id, teacher_id, name, subject, contact_no, email) VALUES (?, ?, ?, ?, ?, ?)");
            $t->execute([$userId, $old['teacher_id'], $old['name'], $old['subject'] ?: null, $old['contact_no'] ?: null, $old['email'] ?: null]);
            $pdo->commit();

            $msg = 'Teacher added successfully.';
            if ($createLogin) $msg .= " Login username: {$old['username']}, temporary password: {$tempPassword}.";
            setFlash('success', $msg);
            header('Location: ' . BASE_URL . '/admin/teachers/index.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Could not save the teacher. Please try again.';
        }
    }
}

require __DIR__ . '/../../includes/portal_header.php';
?>
<div class="card p-4" style="max-width:720px;">
  <h6 class="mb-3">Add Teacher</h6>
  <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <form method="post" class="needs-validation" novalidate>
    <?= csrfField() ?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Teacher ID <span class="required-mark">*</span></label>
        <input type="text" name="teacher_id" class="form-control" value="<?= e($old['teacher_id']) ?>" required></div>
      <div class="col-md-6"><label class="form-label">Full Name <span class="required-mark">*</span></label>
        <input type="text" name="name" class="form-control" value="<?= e($old['name']) ?>" required></div>
      <div class="col-md-6"><label class="form-label">Subject</label>
        <input type="text" name="subject" class="form-control" value="<?= e($old['subject']) ?>"></div>
      <div class="col-md-6"><label class="form-label">Contact Number</label>
        <input type="text" name="contact_no" class="form-control" value="<?= e($old['contact_no']) ?>"></div>
      <div class="col-md-6"><label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= e($old['email']) ?>"></div>
    </div>
    <hr class="my-4">
    <h6 class="mb-3">Login Account (optional)</h6>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" value="1" name="create_login" id="createLogin" <?= $old['create_login']==='1'?'checked':'' ?>>
      <label class="form-check-label" for="createLogin">Create a portal login account for this teacher</label>
    </div>
    <div class="col-md-6"><label class="form-label">Username</label>
      <input type="text" name="username" class="form-control" value="<?= e($old['username']) ?>" placeholder="e.g. teacher3">
      <div class="form-text">A secure temporary password will be generated and shown once after saving.</div>
    </div>
    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Teacher</button>
      <a href="<?= BASE_URL ?>/admin/teachers/index.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
