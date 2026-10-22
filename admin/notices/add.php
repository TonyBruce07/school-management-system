<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$pageTitle = 'Add Notice';
$errors = [];
$old = ['title' => '', 'description' => '', 'date' => date('Y-m-d'), 'visibility' => 'internal'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    foreach ($old as $k => $d) $old[$k] = trim($_POST[$k] ?? $d);

    if ($old['title'] === '') $errors[] = 'Title is required.';
    if ($old['description'] === '') $errors[] = 'Description is required.';
    if (!in_array($old['visibility'], ['public', 'internal'], true)) $errors[] = 'Invalid visibility.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO notices (title, description, date, created_by, visibility) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$old['title'], $old['description'], $old['date'] ?: date('Y-m-d'), $_SESSION['user_id'], $old['visibility']]);
        setFlash('success', 'Notice published successfully.');
        header('Location: ' . BASE_URL . '/admin/notices/index.php');
        exit;
    }
}

require __DIR__ . '/../../includes/portal_header.php';
?>
<div class="card p-4" style="max-width:720px;">
  <h6 class="mb-3">Add Notice</h6>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="post" class="needs-validation" novalidate>
    <?= csrfField() ?>
    <div class="mb-3">
      <label class="form-label">Title <span class="required-mark">*</span></label>
      <input type="text" name="title" class="form-control" value="<?= e($old['title']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Description <span class="required-mark">*</span></label>
      <textarea name="description" class="form-control" rows="4" required><?= e($old['description']) ?></textarea>
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Date</label>
        <input type="date" name="date" class="form-control" value="<?= e($old['date']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Visibility <span class="required-mark">*</span></label>
        <select name="visibility" class="form-select">
          <option value="internal" <?= $old['visibility'] === 'internal' ? 'selected' : '' ?>>Internal (students/teachers only)</option>
          <option value="public" <?= $old['visibility'] === 'public' ? 'selected' : '' ?>>Public (visible on the school website)</option>
        </select>
      </div>
    </div>
    <div class="d-flex gap-2 mt-3">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Publish</button>
      <a href="<?= BASE_URL ?>/admin/notices/index.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
