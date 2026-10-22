<?php
/**
 * principal/teachers.php
 * Oversight-only view of all teachers. No add/edit/delete/account
 * management — those remain Admin-only. No password hashes shown.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('principal');

$pageTitle = 'Teachers';
$search = trim($_GET['q'] ?? '');

$where = '';
$params = [];
if ($search !== '') {
    $where = "WHERE t.name LIKE ? OR t.teacher_id LIKE ? OR t.subject LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$stmt = $pdo->prepare(
    "SELECT t.teacher_id, t.name, t.subject, t.contact_no, t.email, u.username, u.status AS account_status
     FROM teachers t LEFT JOIN users u ON u.id = t.user_id
     $where ORDER BY t.name"
);
$stmt->execute($params);
$teachers = $stmt->fetchAll();

require __DIR__ . '/../includes/portal_header.php';
?>
<div class="card p-3">
  <h6 class="mb-3">Teachers (<?= count($teachers) ?>)</h6>
  <form method="get" class="row g-2 mb-3">
    <div class="col-sm-6"><input type="text" name="q" class="form-control form-control-sm" placeholder="Search name, ID or subject" value="<?= e($search) ?>"></div>
    <div class="col-sm-3"><button class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-search"></i> Filter</button></div>
    <div class="col-sm-3"><a href="<?= BASE_URL ?>/principal/teachers.php" class="btn btn-outline-secondary btn-sm w-100">Clear</a></div>
  </form>

  <?php if (empty($teachers)): ?>
    <div class="empty-state"><i class="bi bi-person-badge"></i>No teachers found.</div>
  <?php else: ?>
    <div class="table-responsive-wrapper">
      <table class="table table-hover align-middle">
        <thead><tr><th>ID</th><th>Name</th><th>Subject</th><th>Contact</th><th>Email</th><th>Username</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($teachers as $t): ?>
          <tr>
            <td><?= e($t['teacher_id']) ?></td>
            <td><?= e($t['name']) ?></td>
            <td><?= e($t['subject'] ?? '-') ?></td>
            <td><?= e($t['contact_no'] ?? '-') ?></td>
            <td><?= e($t['email'] ?? '-') ?></td>
            <td><?= e($t['username'] ?? '-') ?></td>
            <td>
              <?php if ($t['account_status'] === 'active'): ?><span class="badge badge-status-present">Active</span>
              <?php elseif ($t['account_status'] === 'inactive'): ?><span class="badge badge-status-absent">Inactive</span>
              <?php else: ?><span class="badge bg-secondary">No login</span><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
