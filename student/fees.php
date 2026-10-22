<?php
/**
 * student/fees.php
 * Fee information for the logged-in student only.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$student = getCurrentStudent($pdo);
$pageTitle = 'Fees';

require __DIR__ . '/../includes/portal_header.php';

if (!$student) {
    echo '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Your account is not yet linked to a student record. Please contact the school administrator.</div>';
    require __DIR__ . '/../includes/portal_footer.php';
    exit;
}

$stmt = $pdo->prepare(
    "SELECT total_fee, paid_amount, pending_amount, due_date, status
     FROM fees WHERE student_id = ? ORDER BY id DESC"
);
$stmt->execute([(int)$student['id']]);
$feeRecords = $stmt->fetchAll();

$statusBadge = [
    'Paid'    => 'badge-status-paid',
    'Partial' => 'badge-status-partial',
    'Pending' => 'badge-status-pending',
];
?>

<?php if (empty($feeRecords)): ?>
  <div class="card p-3">
    <div class="empty-state"><i class="bi bi-cash-coin"></i>No fee records are available yet.</div>
  </div>
<?php else: ?>
  <?php $latest = $feeRecords[0]; ?>
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
      <div class="stat-card">
        <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
        <div><div class="stat-value"><?= formatCurrency($latest['total_fee']) ?></div><div class="stat-label">Total Fee</div></div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(46,125,79,0.1); color:var(--color-success);"><i class="bi bi-cash"></i></div>
        <div><div class="stat-value"><?= formatCurrency($latest['paid_amount']) ?></div><div class="stat-label">Paid Amount</div></div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(179,38,30,0.1); color:var(--color-danger);"><i class="bi bi-exclamation-circle"></i></div>
        <div><div class="stat-value"><?= formatCurrency($latest['pending_amount']) ?></div><div class="stat-label">Pending Amount</div></div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="stat-card">
        <div class="stat-icon"><i class="bi bi-calendar-event"></i></div>
        <div><div class="stat-value" style="font-size:1.1rem;"><?= formatDate($latest['due_date']) ?></div><div class="stat-label">Due Date</div></div>
      </div>
    </div>
  </div>

  <div class="card p-3">
    <h6 class="mb-3">Fee Records</h6>
    <div class="table-responsive-wrapper">
      <table class="table table-hover align-middle">
        <thead><tr><th>Total Fee</th><th>Paid</th><th>Pending</th><th>Due Date</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($feeRecords as $f): ?>
            <tr>
              <td><?= formatCurrency($f['total_fee']) ?></td>
              <td><?= formatCurrency($f['paid_amount']) ?></td>
              <td><?= formatCurrency($f['pending_amount']) ?></td>
              <td><?= formatDate($f['due_date']) ?></td>
              <td><span class="badge <?= $statusBadge[$f['status']] ?? 'badge-status-pending' ?>"><?= e($f['status']) ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
