<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$pageTitle = 'Edit Event';
$errors = [];

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();

require __DIR__ . '/../../includes/portal_header.php';

if (!$event) {
    echo '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Event not found.</div>';
    require __DIR__ . '/../../includes/portal_footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $eventName = trim($_POST['event_name'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $visibility = trim($_POST['visibility'] ?? 'internal');

    if ($eventName === '') $errors[] = 'Event name is required.';
    if ($date === '') $errors[] = 'Date is required.';
    if (!in_array($visibility, ['public', 'internal'], true)) $errors[] = 'Invalid visibility.';

    $photoFilename = $event['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $uploaded = handleImageUpload($_FILES['photo'], 'event');
        if ($uploaded === null) $errors[] = 'Photo upload failed. Use JPG/PNG/WebP under 2 MB.';
        else $photoFilename = $uploaded;
    }

    if (empty($errors)) {
        $upd = $pdo->prepare("UPDATE events SET event_name=?, date=?, time=?, description=?, photo=?, visibility=? WHERE id=?");
        $upd->execute([$eventName, $date, $time ?: null, $description ?: null, $photoFilename, $visibility, $id]);
        setFlash('success', 'Event updated.');
        header('Location: ' . BASE_URL . '/admin/events/index.php');
        exit;
    }
    $event = array_merge($event, compact('eventName', 'date', 'time', 'description', 'visibility'));
}
?>
<div class="card p-4" style="max-width:720px;">
  <h6 class="mb-3">Edit Event</h6>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <?php if ($event['photo']): ?>
    <img src="<?= UPLOAD_URL . e($event['photo']) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:6px;" class="mb-3">
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="mb-3">
      <label class="form-label">Event Name <span class="required-mark">*</span></label>
      <input type="text" name="event_name" class="form-control" value="<?= e($event['event_name']) ?>" required>
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Date <span class="required-mark">*</span></label>
        <input type="date" name="date" class="form-control" value="<?= e($event['date']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Time</label>
        <input type="time" name="time" class="form-control" value="<?= e($event['time']) ?>">
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="4"><?= e($event['description']) ?></textarea>
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Replace Photo</label>
        <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp" class="form-control">
      </div>
      <div class="col-md-6">
        <label class="form-label">Visibility <span class="required-mark">*</span></label>
        <select name="visibility" class="form-select">
          <option value="internal" <?= $event['visibility'] === 'internal' ? 'selected' : '' ?>>Internal</option>
          <option value="public" <?= $event['visibility'] === 'public' ? 'selected' : '' ?>>Public</option>
        </select>
      </div>
    </div>
    <div class="d-flex gap-2 mt-3">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Changes</button>
      <a href="<?= BASE_URL ?>/admin/events/index.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
