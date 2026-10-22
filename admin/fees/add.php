<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$pageTitle = 'Add Fee Record';
$errors = [];
$old = ['student_id' => '', 'total_fee' => '', 'paid_amount' => '0', 'due_date' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    foreach ($old as $k => $d) $old[$k] = trim($_POST[$k] ?? $d);

    if ($old['student_id'] === '') $errors[] = 'Please select a student.';
    if ($old['total_fee'] === '' || !is_numeric($old['total_fee']) || (float)$old['total_fee'] < 0) $errors[] = 'Total fee must be a valid non-negative amount.';
    if ($old['paid_amount'] === '' || !is_numeric($old['paid_amount']) || (float)$old['paid_amount'] < 0) $errors[] = 'Paid amount must be a valid non-negative amount.';
    if (!empty($old['total_fee']) && !empty($old['paid_amount']) && is_numeric($old['total_fee']) && is_numeric($old['paid_amount']) && (float)$old['paid_amount'] > (float)$old['total_fee']) {
        $errors[] = 'Paid amount cannot exceed the total fee.';
    }

    if (empty($errors)) {
        $total = (float)$old['total_fee'];
        $paid  = (float)$old['paid_amount'];
        $status = $paid <= 0 ? 'Pending' : ($paid >= $total ? 'Paid' : 'Partial');

        $stmt = $pdo->prepare(
            "INSERT INTO fees (student_id, total_fee, paid_amount, due_date, status) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$old['student_id'], $total, $paid, $old['due_date'] ?: null, $status]);

        setFlash('success', 'Fee record added successfully.');
        header('Location: ' . BASE_URL . '/admin/fees/index.php');
        exit;
    }
}

$students = $pdo->query(
    "SELECT s.id, s.name, s.student_id, c.class_name, s.division
     FROM students s LEFT JOIN classes c ON c.id = s.class_id ORDER BY s.name"
)->fetchAll();

require __DIR__ . '/../../includes/portal_header.php';
?>
<div class="card p-4" style="max-width:640px;">
  <h6 class="mb-3">Add Fee Record</h6>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <form method="post" class="needs-validation" novalidate>
    <?= csrfField() ?>
    <div class="mb-3">
      <label class="form-label">Student <span class="required-mark">*</span></label>
      <select name="student_id" class="form-select" required>
        <option value="">Select student</option>
        <?php foreach ($students as $s): ?>
          <option value="<?= $s['id'] ?>" <?= $old['student_id'] == $s['id'] ? 'selected' : '' ?>>
            <?= e($s['name']) ?> (<?= e($s['student_id']) ?>) — <?= e($s['class_name'] ?? '-') ?> <?= e($s['division'] ?? '') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Total Fee (₹) <span class="required-mark">*</span></label>
        <input type="number" step="0.01" min="0" name="total_fee" class="form-control" value="<?= e($old['total_fee']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Paid Amount (₹) <span class="required-mark">*</span></label>
        <input type="number" step="0.01" min="0" name="paid_amount" class="form-control" value="<?= e($old['paid_amount']) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Due Date</label>
        <input type="date" name="due_date" class="form-control" value="<?= e($old['due_date']) ?>">
      </div>
    </div>
    <p class="small text-muted-custom mt-2">Status (Paid / Partial / Pending) is calculated automatically from the amounts.</p>
    <div class="d-flex gap-2 mt-3">
      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save</button>
      <a href="<?= BASE_URL ?>/admin/fees/index.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
