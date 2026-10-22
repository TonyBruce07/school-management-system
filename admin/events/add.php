<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$pageTitle = 'Add Event';
$errors = [];
$old = ['event_name' => '', 'date' => date('Y-m-d'), 'time' => '', 'description' => '', 'visibility' => 'internal'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    foreach ($old as $k => $d) $old[$k] = trim($_POST[$k] ?? $d);

    if ($old['event_name'] === '') $errors[] = 'Event name is required.';
    if ($old['date'] === '') $errors[] = 'Date is required.';
    if (!in_array($old['visibility'], ['public', 'internal'], true)) $errors[] = 'Invalid visibility.';

    $photoFilename = null;
    if (!empty($_FILES['photo']['name'])) {
        $photoFilename = handleImageUpload($_FILES['photo'], 'event');
        if ($photoFilename === null) $errors[] = 'Photo upload failed. Use JPG/PNG/WebP under 2 MB.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO events (event_name, date, time, description, photo, created_by, visibility) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$old['event_name'], $old['date'], $old['time'] ?: null, $old['description'] ?: null, $photoFilename, $_SESSION['user_id'], $old['visibility']]);
        setFlash('success', 'Event added successfully.');
        header('Location: ' . BASE_URL . '/admin/events/index.php');
        exit;
    }
}

require __DIR__ . '/../../includes/portal_header.php';
?>
<div class="card p-4" style="max-width:720px;">
  <h6 class="mb-3">Add Event</h6>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
    <?= csrfField() ?>
    <div class="mb-3">
      <label class="form-label">Event Name <span class="required-mark">*</span></label>
      <input type="text" name="event_name" class="form-control" value="<?= e($old['event_name']) ?>" required>
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Date <span class="required-mark">*</span></label>
        <input type="date" name="date" class="form-control" value="<?= e($old['date']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Time</label>
        <input type="time" name="time" class="form-control" value="<?= e($old['time']) ?>">
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="4"><?= e($old['description']) ?></textarea>
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Photo</label>
        <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp" class="form-control">
      </div>
      <div class="col-md-6">
        <label class="form-label">Visibility <span class="required-mark">*</span></label>
        <select name="visibility" class="form-select">
          <option value="internal" <?= $old['visibility'] === 'internal' ? 'selected' : '' ?>>Internal</option>
          <option value="public" <?= $old['visibility'] === 'public' ? 'selected' : '' ?>>Public</option>
        </select>
      </div>
    </div>
    <div class="d-flex gap-2 mt-3">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save</button>
      <a href="<?= BASE_URL ?>/admin/events/index.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
