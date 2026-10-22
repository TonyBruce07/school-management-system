<?php
/**
 * teacher/notices.php
 * Notice management for teachers. Per spec, "Teacher and Administrator
 * can manage notices" — teachers share the same notices table as
 * admin (includes/... visibility rules unchanged) rather than having
 * a separate "teacher notices" concept. Implemented as a single file
 * (list + add + edit + delete) to match the existing flat teacher/
 * folder structure, unlike admin's subfolder-per-module layout.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('teacher');

$pageTitle = 'Notices';
$errors = [];
$editId = (int)($_GET['edit'] ?? 0);
$editing = null;

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    verifyCsrf();
    $stmt = $pdo->prepare("DELETE FROM notices WHERE id = ?");
    $stmt->execute([(int)$_POST['id']]);
    setFlash('success', 'Notice deleted.');
    header('Location: ' . BASE_URL . '/teacher/notices.php');
    exit;
}

// Handle add / edit submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['add', 'edit'], true)) {
    verifyCsrf();
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date = trim($_POST['date'] ?? date('Y-m-d'));
    $visibility = trim($_POST['visibility'] ?? 'internal');

    if ($title === '') $errors[] = 'Title is required.';
    if ($description === '') $errors[] = 'Description is required.';
    if (!in_array($visibility, ['public', 'internal'], true)) $errors[] = 'Invalid visibility.';

    if (empty($errors)) {
        if ($_POST['action'] === 'add') {
            $stmt = $pdo->prepare("INSERT INTO notices (title, description, date, created_by, visibility) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $date, $_SESSION['user_id'], $visibility]);
            setFlash('success', 'Notice published.');
        } else {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE notices SET title=?, description=?, date=?, visibility=? WHERE id=?");
            $stmt->execute([$title, $description, $date, $visibility, $id]);
            setFlash('success', 'Notice updated.');
        }
        header('Location: ' . BASE_URL . '/teacher/notices.php');
        exit;
    } elseif (($_POST['action'] ?? '') === 'edit') {
        $editing = array_merge(['id' => (int)$_POST['id']], compact('title', 'description', 'date', 'visibility'));
    }
}

if ($editId && !$editing) {
    $stmt = $pdo->prepare("SELECT * FROM notices WHERE id = ?");
    $stmt->execute([$editId]);
    $editing = $stmt->fetch() ?: null;
}

$notices = $pdo->query("SELECT id, title, description, date, visibility FROM notices ORDER BY date DESC")->fetchAll();

require __DIR__ . '/../includes/portal_header.php';
?>

<div class="card p-4 mb-4">
  <h6 class="mb-3"><?= $editing ? 'Edit Notice' : 'Add Notice' ?></h6>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="post" class="needs-validation" novalidate>
    <?= csrfField() ?>
    <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
    <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int)$editing['id'] ?>"><?php endif; ?>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Title <span class="required-mark">*</span></label>
        <input type="text" name="title" class="form-control" value="<?= e($editing['title'] ?? '') ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Date</label>
        <input type="date" name="date" class="form-control" value="<?= e($editing['date'] ?? date('Y-m-d')) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Visibility</label>
        <select name="visibility" class="form-select">
          <option value="internal" <?= ($editing['visibility'] ?? 'internal') === 'internal' ? 'selected' : '' ?>>Internal</option>
          <option value="public" <?= ($editing['visibility'] ?? '') === 'public' ? 'selected' : '' ?>>Public</option>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label">Description <span class="required-mark">*</span></label>
        <textarea name="description" class="form-control" rows="3" required><?= e($editing['description'] ?? '') ?></textarea>
      </div>
    </div>
    <div class="d-flex gap-2 mt-3">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> <?= $editing ? 'Save Changes' : 'Publish' ?></button>
      <?php if ($editing): ?><a href="<?= BASE_URL ?>/teacher/notices.php" class="btn btn-outline-secondary">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card p-3">
  <h6 class="mb-3">All Notices (<?= count($notices) ?>)</h6>
  <?php if (empty($notices)): ?>
    <div class="empty-state"><i class="bi bi-megaphone"></i>No notices have been published yet.</div>
  <?php else: foreach ($notices as $n): ?>
    <div class="notice-card d-flex justify-content-between align-items-start flex-wrap gap-2">
      <div>
        <div class="date"><?= formatDate($n['date']) ?> · <span class="text-uppercase small"><?= e($n['visibility']) ?></span></div>
        <h6 class="mb-1"><?= e($n['title']) ?></h6>
        <p class="mb-0 small text-muted-custom"><?= e($n['description']) ?></p>
      </div>
      <div class="text-nowrap">
        <a href="<?= BASE_URL ?>/teacher/notices.php?edit=<?= $n['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
        <form action="<?= BASE_URL ?>/teacher/notices.php" method="post" class="d-inline">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= $n['id'] ?>">
          <button type="submit" class="btn btn-sm btn-outline-danger btn-delete-confirm" data-label="this notice"><i class="bi bi-trash"></i></button>
        </form>
      </div>
    </div>
  <?php endforeach; endif; ?>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
