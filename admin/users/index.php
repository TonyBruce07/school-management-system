<?php
/**
 * admin/users/index.php
 * Lists every login account. Password hashes are never selected or
 * displayed. Activation/deactivation is a POST-only action.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$pageTitle = 'User Accounts';

$stmt = $pdo->query(
    "SELECT u.id, u.username, u.role, u.status, u.created_at,
            s.name AS student_name, t.name AS teacher_name
     FROM users u
     LEFT JOIN students s ON s.user_id = u.id
     LEFT JOIN teachers t ON t.user_id = u.id
     ORDER BY u.role, u.username"
);
$users = $stmt->fetchAll();

require __DIR__ . '/../../includes/portal_header.php';
?>
<div class="card p-3">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h6 class="mb-0">User Accounts (<?= count($users) ?>)</h6>
    <a href="<?= BASE_URL ?>/admin/users/add.php" class="btn btn-primary btn-sm"><i class="bi bi-person-plus"></i> Add Admin / Principal Account</a>
  </div>
  <p class="small text-muted-custom">Student and teacher login accounts are created from the Students and Teachers modules. This page manages all accounts and lets you activate or deactivate any of them.</p>

  <div class="table-responsive-wrapper">
    <table class="table table-hover align-middle">
      <thead><tr><th>Username</th><th>Role</th><th>Linked Record</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= e($u['username']) ?></td>
          <td class="text-capitalize"><?= e($u['role']) ?></td>
          <td class="text-muted-custom"><?= e($u['student_name'] ?? $u['teacher_name'] ?? '-') ?></td>
          <td>
            <?php if ($u['status'] === 'active'): ?><span class="badge badge-status-present">Active</span>
            <?php else: ?><span class="badge badge-status-absent">Inactive</span><?php endif; ?>
          </td>
          <td class="text-muted-custom small"><?= formatDate($u['created_at']) ?></td>
          <td>
            <?php if ((int)$u['id'] === (int)$_SESSION['user_id']): ?>
              <span class="text-muted-custom small">(your account)</span>
            <?php else: ?>
              <form action="<?= BASE_URL ?>/admin/users/toggle.php" method="post" class="d-inline">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                <?php if ($u['status'] === 'active'): ?>
                  <button type="submit" class="btn btn-sm btn-outline-danger">Deactivate</button>
                <?php else: ?>
                  <button type="submit" class="btn btn-sm btn-outline-success">Activate</button>
                <?php endif; ?>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
