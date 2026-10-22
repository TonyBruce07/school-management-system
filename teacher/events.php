<?php
/**
 * teacher/events.php
 * Event management for teachers, sharing the same events table and
 * photo-upload handling as admin/events/ — single-file CRUD to match
 * the existing flat teacher/ folder structure.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('teacher');

$pageTitle = 'Events';
$errors = [];
$editId = (int)($_GET['edit'] ?? 0);
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    verifyCsrf();
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([(int)$_POST['id']]);
    setFlash('success', 'Event deleted.');
    header('Location: ' . BASE_URL . '/teacher/events.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['add', 'edit'], true)) {
    verifyCsrf();
    $eventName = trim($_POST['event_name'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $visibility = trim($_POST['visibility'] ?? 'internal');

    if ($eventName === '') $errors[] = 'Event name is required.';
    if ($date === '') $errors[] = 'Date is required.';
    if (!in_array($visibility, ['public', 'internal'], true)) $errors[] = 'Invalid visibility.';

    $photoFilename = null;
    $existingPhoto = $_POST['existing_photo'] ?? null;
    if (!empty($_FILES['photo']['name'])) {
        $photoFilename = handleImageUpload($_FILES['photo'], 'event');
        if ($photoFilename === null) $errors[] = 'Photo upload failed. Use JPG/PNG/WebP under 2 MB.';
    } else {
        $photoFilename = $existingPhoto ?: null;
    }

    if (empty($errors)) {
        if ($_POST['action'] === 'add') {
            $stmt = $pdo->prepare(
                "INSERT INTO events (event_name, date, time, description, photo, created_by, visibility) VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$eventName, $date, $time ?: null, $description ?: null, $photoFilename, $_SESSION['user_id'], $visibility]);
            setFlash('success', 'Event added.');
        } else {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("UPDATE events SET event_name=?, date=?, time=?, description=?, photo=?, visibility=? WHERE id=?");
            $stmt->execute([$eventName, $date, $time ?: null, $description ?: null, $photoFilename, $visibility, $id]);
            setFlash('success', 'Event updated.');
        }
        header('Location: ' . BASE_URL . '/teacher/events.php');
        exit;
    } elseif (($_POST['action'] ?? '') === 'edit') {
        $editing = array_merge(['id' => (int)$_POST['id'], 'photo' => $existingPhoto], compact('eventName', 'date', 'time', 'description', 'visibility'));
    }
}

if ($editId && !$editing) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$editId]);
    $editing = $stmt->fetch() ?: null;
}

$events = $pdo->query("SELECT id, event_name, date, time, visibility, photo FROM events ORDER BY date DESC")->fetchAll();

require __DIR__ . '/../includes/portal_header.php';
?>

<div class="card p-4 mb-4">
  <h6 class="mb-3"><?= $editing ? 'Edit Event' : 'Add Event' ?></h6>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <?php if (!empty($editing['photo'])): ?>
    <img src="<?= UPLOAD_URL . e($editing['photo']) ?>" style="width:72px;height:72px;object-fit:cover;border-radius:6px;" class="mb-3">
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
    <?= csrfField() ?>
    <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
    <?php if ($editing): ?>
      <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
      <input type="hidden" name="existing_photo" value="<?= e($editing['photo'] ?? '') ?>">
    <?php endif; ?>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Event Name <span class="required-mark">*</span></label>
        <input type="text" name="event_name" class="form-control" value="<?= e($editing['event_name'] ?? '') ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Date <span class="required-mark">*</span></label>
        <input type="date" name="date" class="form-control" value="<?= e($editing['date'] ?? date('Y-m-d')) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Time</label>
        <input type="time" name="time" class="form-control" value="<?= e($editing['time'] ?? '') ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"><?= e($editing['description'] ?? '') ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label">Photo</label>
        <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp" class="form-control">
      </div>
      <div class="col-md-6">
        <label class="form-label">Visibility</label>
        <select name="visibility" class="form-select">
          <option value="internal" <?= ($editing['visibility'] ?? 'internal') === 'internal' ? 'selected' : '' ?>>Internal</option>
          <option value="public" <?= ($editing['visibility'] ?? '') === 'public' ? 'selected' : '' ?>>Public</option>
        </select>
      </div>
    </div>
    <div class="d-flex gap-2 mt-3">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> <?= $editing ? 'Save Changes' : 'Save' ?></button>
      <?php if ($editing): ?><a href="<?= BASE_URL ?>/teacher/events.php" class="btn btn-outline-secondary">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card p-3">
  <h6 class="mb-3">All Events (<?= count($events) ?>)</h6>
  <?php if (empty($events)): ?>
    <div class="empty-state"><i class="bi bi-calendar-event"></i>No events have been published yet.</div>
  <?php else: ?>
    <div class="table-responsive-wrapper">
      <table class="table table-hover align-middle">
        <thead><tr><th>Photo</th><th>Event</th><th>Date</th><th>Time</th><th>Visibility</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($events as $ev): ?>
          <tr>
            <td>
              <?php if ($ev['photo']): ?>
                <img src="<?= UPLOAD_URL . e($ev['photo']) ?>" style="width:44px;height:44px;object-fit:cover;border-radius:6px;">
              <?php else: ?>
                <div class="d-flex align-items-center justify-content-center bg-light" style="width:44px;height:44px;border-radius:6px;"><i class="bi bi-image text-muted-custom"></i></div>
              <?php endif; ?>
            </td>
            <td><?= e($ev['event_name']) ?></td>
            <td><?= formatDate($ev['date']) ?></td>
            <td><?= $ev['time'] ? date('h:i A', strtotime($ev['time'])) : '-' ?></td>
            <td>
              <?php if ($ev['visibility'] === 'public'): ?><span class="badge bg-primary">Public</span>
              <?php else: ?><span class="badge bg-secondary">Internal</span><?php endif; ?>
            </td>
            <td class="text-nowrap">
              <a href="<?= BASE_URL ?>/teacher/events.php?edit=<?= $ev['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
              <form action="<?= BASE_URL ?>/teacher/events.php" method="post" class="d-inline">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $ev['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger btn-delete-confirm" data-label="this event"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
