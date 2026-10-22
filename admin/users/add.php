<?php
/**
 * admin/users/add.php
 * Creates a standalone login account for a role with no linked
 * profile table (admin or principal). Student/teacher accounts are
 * created together with their profile from the Students/Teachers
 * modules, so those two roles are intentionally not offered here.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$pageTitle = 'Add User Account';
$errors = [];
$old = ['username' => '', 'role' => 'principal'];
$generatedPassword = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $old['username'] = trim($_POST['username'] ?? '');
    $old['role'] = trim($_POST['role'] ?? '');

    if ($old['username'] === '') $errors[] = 'Username is required.';
    if (!in_array($old['role'], ['admin', 'principal'], true)) $errors[] = 'Please select a valid role.';

    if ($old['username'] !== '') {
        $chk = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $chk->execute([$old['username']]);
        if ($chk->fetch()) $errors[] = 'That username is already taken.';
    }

    if (empty($errors)) {
        $generatedPassword = bin2hex(random_bytes(4));
        $hash = password_hash($generatedPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, status) VALUES (?, ?, ?, 'active')");
        $stmt->execute([$old['username'], $hash, $old['role']]);
        setFlash('success', "Account created. Username: {$old['username']}, temporary password: {$generatedPassword} (share this securely — it will not be shown again).");
        header('Location: ' . BASE_URL . '/admin/users/index.php');
        exit;
    }
}

require __DIR__ . '/../../includes/portal_header.php';
?>
<div class="card p-4" style="max-width:560px;">
  <h6 class="mb-3">Add Admin / Principal Account</h6>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="post" class="needs-validation" novalidate>
    <?= csrfField() ?>
    <div class="mb-3">
      <label class="form-label">Username <span class="required-mark">*</span></label>
      <input type="text" name="username" class="form-control" value="<?= e($old['username']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Role <span class="required-mark">*</span></label>
      <select name="role" class="form-select">
        <option value="principal" <?= $old['role'] === 'principal' ? 'selected' : '' ?>>Principal</option>
        <option value="admin" <?= $old['role'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
      </select>
    </div>
    <p class="small text-muted-custom">A secure temporary password will be generated and shown once after saving.</p>
    <div class="d-flex gap-2 mt-3">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Create Account</button>
      <a href="<?= BASE_URL ?>/admin/users/index.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
