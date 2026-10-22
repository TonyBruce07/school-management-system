<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$pageTitle = 'Edit Notice';
$errors = [];

$stmt = $pdo->prepare("SELECT * FROM notices WHERE id = ?");
$stmt->execute([$id]);
$notice = $stmt->fetch();

require __DIR__ . '/../../includes/portal_header.php';

if (!$notice) {
    echo '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Notice not found.</div>';
    require __DIR__ . '/../../includes/portal_footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $visibility = trim($_POST['visibility'] ?? 'internal');

    if ($title === '') $errors[] = 'Title is required.';
    if ($description === '') $errors[] = 'Description is required.';
    if (!in_array($visibility, ['public', 'internal'], true)) $errors[] = 'Invalid visibility.';

    if (empty($errors)) {
        $upd = $pdo->prepare("UPDATE notices SET title=?, description=?, date=?, visibility=? WHERE id=?");
        $upd->execute([$title, $description, $date ?: date('Y-m-d'), $visibility, $id]);
        setFlash('success', 'Notice updated.');
        header('Location: ' . BASE_URL . '/admin/notices/index.php');
        exit;
    }
    $notice = array_merge($notice, compact('title', 'description', 'date', 'visibility'));
}
?>
<div class="card p-4" style="max-width:720px;">
  <h6 class="mb-3">Edit Notice</h6>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="post" class="needs-validation" novalidate>
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="mb-3">
      <label class="form-label">Title <span class="required-mark">*</span></label>
      <input type="text" name="title" class="form-control" value="<?= e($notice['title']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Description <span class="required-mark">*</span></label>
      <textarea name="description" class="form-control" rows="4" required><?= e($notice['description']) ?></textarea>
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Date</label>
        <input type="date" name="date" class="form-control" value="<?= e($notice['date']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Visibility <span class="required-mark">*</span></label>
        <select name="visibility" class="form-select">
          <option value="internal" <?= $notice['visibility'] === 'internal' ? 'selected' : '' ?>>Internal</option>
          <option value="public" <?= $notice['visibility'] === 'public' ? 'selected' : '' ?>>Public</option>
        </select>
      </div>
    </div>
    <div class="d-flex gap-2 mt-3">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Changes</button>
      <a href="<?= BASE_URL ?>/admin/notices/index.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
