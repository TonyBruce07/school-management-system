<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$pageTitle = 'Notices';
$stmt = $pdo->query("SELECT id, title, date, visibility FROM notices ORDER BY date DESC");
$notices = $stmt->fetchAll();

require __DIR__ . '/../../includes/portal_header.php';
?>
<div class="card p-3">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h6 class="mb-0">Notices (<?= count($notices) ?>)</h6>
    <a href="<?= BASE_URL ?>/admin/notices/add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add Notice</a>
  </div>
  <?php if (empty($notices)): ?>
    <div class="empty-state"><i class="bi bi-megaphone"></i>No notices have been published yet.</div>
  <?php else: ?>
    <div class="table-responsive-wrapper">
      <table class="table table-hover align-middle">
        <thead><tr><th>Title</th><th>Date</th><th>Visibility</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($notices as $n): ?>
          <tr>
            <td><?= e($n['title']) ?></td>
            <td><?= formatDate($n['date']) ?></td>
            <td>
              <?php if ($n['visibility'] === 'public'): ?><span class="badge bg-primary">Public</span>
              <?php else: ?><span class="badge bg-secondary">Internal</span><?php endif; ?>
            </td>
            <td class="text-nowrap">
              <a href="<?= BASE_URL ?>/admin/notices/edit.php?id=<?= $n['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
              <form action="<?= BASE_URL ?>/admin/notices/delete.php" method="post" class="d-inline">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= $n['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger btn-delete-confirm" data-label="this notice" title="Delete"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
