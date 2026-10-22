<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$pageTitle = 'Edit Fee Record';
$errors = [];

$stmt = $pdo->prepare(
    "SELECT f.*, s.name AS student_name, s.student_id AS student_code
     FROM fees f JOIN students s ON s.id = f.student_id WHERE f.id = ?"
);
$stmt->execute([$id]);
$fee = $stmt->fetch();

require __DIR__ . '/../../includes/portal_header.php';

if (!$fee) {
    echo '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Fee record not found.</div>';
    require __DIR__ . '/../../includes/portal_footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $total   = trim($_POST['total_fee'] ?? '');
    $paid    = trim($_POST['paid_amount'] ?? '');
    $dueDate = trim($_POST['due_date'] ?? '');

    if ($total === '' || !is_numeric($total) || (float)$total < 0) $errors[] = 'Total fee must be a valid non-negative amount.';
    if ($paid === '' || !is_numeric($paid) || (float)$paid < 0) $errors[] = 'Paid amount must be a valid non-negative amount.';
    if (is_numeric($total) && is_numeric($paid) && (float)$paid > (float)$total) $errors[] = 'Paid amount cannot exceed the total fee.';

    if (empty($errors)) {
        $status = (float)$paid <= 0 ? 'Pending' : ((float)$paid >= (float)$total ? 'Paid' : 'Partial');
        $upd = $pdo->prepare("UPDATE fees SET total_fee=?, paid_amount=?, due_date=?, status=? WHERE id=?");
        $upd->execute([$total, $paid, $dueDate ?: null, $status, $id]);
        setFlash('success', 'Fee record updated.');
        header('Location: ' . BASE_URL . '/admin/fees/index.php');
        exit;
    }
    $fee = array_merge($fee, ['total_fee' => $total, 'paid_amount' => $paid, 'due_date' => $dueDate]);
}
?>
<div class="card p-4" style="max-width:640px;">
  <h6 class="mb-3">Edit Fee Record — <?= e($fee['student_name']) ?> (<?= e($fee['student_code']) ?>)</h6>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="post" class="needs-validation" novalidate>
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Total Fee (₹) <span class="required-mark">*</span></label>
        <input type="number" step="0.01" min="0" name="total_fee" class="form-control" value="<?= e($fee['total_fee']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Paid Amount (₹) <span class="required-mark">*</span></label>
        <input type="number" step="0.01" min="0" name="paid_amount" class="form-control" value="<?= e($fee['paid_amount']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Due Date</label>
        <input type="date" name="due_date" class="form-control" value="<?= e($fee['due_date']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Pending Amount (auto)</label>
        <input type="text" class="form-control" value="<?= formatCurrency($fee['pending_amount']) ?>" disabled>
      </div>
    </div>
    <div class="d-flex gap-2 mt-3">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Changes</button>
      <a href="<?= BASE_URL ?>/admin/fees/index.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
