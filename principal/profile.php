<?php
/**
 * principal/profile.php
 * There is no dedicated "principal" profile table in the existing
 * schema — the users table is the only record of this account, so
 * that's what is shown. Resolved strictly via the session's user_id,
 * never a request parameter (same anti-IDOR pattern used by
 * getCurrentStudent()/getCurrentTeacher()).
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('principal');

$pageTitle = 'My Profile';

$stmt = $pdo->prepare("SELECT username, role, status, created_at FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$account = $stmt->fetch();

require __DIR__ . '/../includes/portal_header.php';
?>
<div class="card p-4" style="max-width:520px;">
  <div class="text-center mb-4">
    <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-light" style="width:100px;height:100px;">
      <i class="bi bi-person-badge fs-1 text-muted-custom"></i>
    </div>
    <h5 class="mb-0"><?= e($account['username'] ?? '') ?></h5>
    <p class="text-muted-custom text-capitalize mb-0"><?= e($account['role'] ?? '') ?></p>
  </div>
  <table class="table table-borderless mb-0">
    <tr><th class="w-25 text-muted-custom">Username</th><td><?= e($account['username'] ?? '-') ?></td></tr>
    <tr><th class="text-muted-custom">Role</th><td class="text-capitalize"><?= e($account['role'] ?? '-') ?></td></tr>
    <tr><th class="text-muted-custom">Account Status</th>
      <td><?php if (($account['status'] ?? '') === 'active'): ?><span class="badge badge-status-present">Active</span>
          <?php else: ?><span class="badge badge-status-absent">Inactive</span><?php endif; ?></td></tr>
    <tr><th class="text-muted-custom">Account Created</th><td><?= formatDate($account['created_at'] ?? null) ?></td></tr>
  </table>
</div>
<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
