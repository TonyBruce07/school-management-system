<?php
/**
 * admin/students/view.php
 * Read-only detail view. ?id= is intentional here — unlike the
 * student portal, the admin role is authorized to view any student.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$id = (int)($_GET['id'] ?? 0);
$pageTitle = 'View Student';

$stmt = $pdo->prepare(
    "SELECT s.*, c.class_name, p.name AS parent_name, p.contact_no AS parent_contact, p.email AS parent_email,
            u.username, u.status AS account_status
     FROM students s
     LEFT JOIN classes c ON c.id = s.class_id
     LEFT JOIN parents p ON p.id = s.parent_id
     LEFT JOIN users u ON u.id = s.user_id
     WHERE s.id = ? LIMIT 1"
);
$stmt->execute([$id]);
$student = $stmt->fetch();

require __DIR__ . '/../../includes/portal_header.php';

if (!$student) {
    echo '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Student not found.</div>';
    require __DIR__ . '/../../includes/portal_footer.php';
    exit;
}

$attendancePercent = calculateAttendancePercentage($pdo, $id);
$photoUrl = $student['photo'] ? UPLOAD_URL . e($student['photo']) : null;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h6 class="mb-0">Student Details</h6>
  <div>
    <a href="<?= BASE_URL ?>/admin/students/edit.php?id=<?= $id ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
    <a href="<?= BASE_URL ?>/admin/students/index.php" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-left"></i> Back</a>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="card p-4 text-center">
      <?php if ($photoUrl): ?>
        <img src="<?= $photoUrl ?>" class="rounded-circle mx-auto mb-3" style="width:110px;height:110px;object-fit:cover;">
      <?php else: ?>
        <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-light" style="width:110px;height:110px;"><i class="bi bi-person fs-1 text-muted-custom"></i></div>
      <?php endif; ?>
      <h5 class="mb-1"><?= e($student['name']) ?></h5>
      <p class="text-muted-custom mb-2"><?= e($student['student_id']) ?></p>
      <div class="stat-value" style="font-size:1.3rem;"><?= $attendancePercent ?>%</div>
      <div class="stat-label">Attendance</div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card p-4 mb-3">
      <h6 class="mb-3">Student Information</h6>
      <table class="table table-borderless mb-0">
        <tr><th class="w-25 text-muted-custom">Class</th><td><?= e($student['class_name'] ?? '-') ?></td></tr>
        <tr><th class="text-muted-custom">Division</th><td><?= e($student['division'] ?? '-') ?></td></tr>
        <tr><th class="text-muted-custom">Date of Birth</th><td><?= formatDate($student['date_of_birth']) ?></td></tr>
        <tr><th class="text-muted-custom">Address</th><td><?= e($student['address'] ?? '-') ?></td></tr>
        <tr><th class="text-muted-custom">Contact</th><td><?= e($student['contact'] ?? '-') ?></td></tr>
        <tr><th class="text-muted-custom">Login Username</th><td><?= e($student['username'] ?? '— none —') ?></td></tr>
        <tr><th class="text-muted-custom">Account Status</th>
          <td><?php if ($student['account_status'] === 'active'): ?><span class="badge badge-status-present">Active</span>
              <?php elseif ($student['account_status'] === 'inactive'): ?><span class="badge badge-status-absent">Inactive</span>
              <?php else: ?><span class="badge bg-secondary">No login</span><?php endif; ?></td></tr>
      </table>
    </div>
    <div class="card p-4">
      <h6 class="mb-3">Parent / Guardian</h6>
      <?php if ($student['parent_name']): ?>
        <table class="table table-borderless mb-0">
          <tr><th class="w-25 text-muted-custom">Name</th><td><?= e($student['parent_name']) ?></td></tr>
          <tr><th class="text-muted-custom">Contact</th><td><?= e($student['parent_contact'] ?? '-') ?></td></tr>
          <tr><th class="text-muted-custom">Email</th><td><?= e($student['parent_email'] ?? '-') ?></td></tr>
        </table>
      <?php else: ?>
        <p class="text-muted-custom mb-0">No parent/guardian on file.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
