<?php
/**
 * admin/fees/index.php
 * View + add + edit only, per spec (no delete, no online payment).
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$pageTitle = 'Fees';
$search = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');

$where = [];
$params = [];
if ($search !== '') {
    $where[] = '(s.name LIKE ? OR s.student_id LIKE ?)';
    $params[] = "%$search%"; $params[] = "%$search%";
}
if ($status !== '') {
    $where[] = 'f.status = ?';
    $params[] = $status;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = $pdo->prepare(
    "SELECT f.id, f.total_fee, f.paid_amount, f.pending_amount, f.due_date, f.status,
            s.name AS student_name, s.student_id, c.class_name, s.division
     FROM fees f
     JOIN students s ON s.id = f.student_id
     LEFT JOIN classes c ON c.id = s.class_id
     $whereSql
     ORDER BY f.due_date ASC"
);
$stmt->execute($params);
$fees = $stmt->fetchAll();

$statusBadge = ['Paid' => 'badge-status-paid', 'Partial' => 'badge-status-partial', 'Pending' => 'badge-status-pending'];

require __DIR__ . '/../../includes/portal_header.php';
?>
<div class="card p-3">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h6 class="mb-0">Fee Records (<?= count($fees) ?>)</h6>
    <a href="<?= BASE_URL ?>/admin/fees/add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add Fee Record</a>
  </div>

  <form method="get" class="row g-2 mb-3">
    <div class="col-sm-5"><input type="text" name="q" class="form-control form-control-sm" placeholder="Search student name or ID" value="<?= e($search) ?>"></div>
    <div class="col-sm-3">
      <select name="status" class="form-select form-select-sm">
        <option value="">All Statuses</option>
        <?php foreach (['Paid','Partial','Pending'] as $s): ?>
          <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-sm-2"><button class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-search"></i> Filter</button></div>
    <div class="col-sm-2"><a href="<?= BASE_URL ?>/admin/fees/index.php" class="btn btn-outline-secondary btn-sm w-100">Clear</a></div>
  </form>

  <?php if (empty($fees)): ?>
    <div class="empty-state"><i class="bi bi-cash-coin"></i>No fee records found.</div>
  <?php else: ?>
    <div class="table-responsive-wrapper">
      <table class="table table-hover align-middle">
        <thead><tr><th>Student</th><th>Class</th><th>Total</th><th>Paid</th><th>Pending</th><th>Due Date</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($fees as $f): ?>
          <tr>
            <td><?= e($f['student_name']) ?> <span class="text-muted-custom small">(<?= e($f['student_id']) ?>)</span></td>
            <td><?= e($f['class_name'] ?? '-') ?> <?= e($f['division'] ?? '') ?></td>
            <td><?= formatCurrency($f['total_fee']) ?></td>
            <td><?= formatCurrency($f['paid_amount']) ?></td>
            <td><?= formatCurrency($f['pending_amount']) ?></td>
            <td><?= formatDate($f['due_date']) ?></td>
            <td><span class="badge <?= $statusBadge[$f['status']] ?? 'badge-status-pending' ?>"><?= e($f['status']) ?></span></td>
            <td><a href="<?= BASE_URL ?>/admin/fees/edit.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
