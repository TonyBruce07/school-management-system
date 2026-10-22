<?php
/**
 * student/profile.php
 * Displays the logged-in student's own profile only.
 * Data is fetched exclusively via getCurrentStudent() — never from
 * a GET/POST id — so a student cannot view anyone else's profile.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$student = getCurrentStudent($pdo);
$pageTitle = 'My Profile';

require __DIR__ . '/../includes/portal_header.php';

if (!$student) {
    echo '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Your account is not yet linked to a student record. Please contact the school administrator.</div>';
    require __DIR__ . '/../includes/portal_footer.php';
    exit;
}

$photoUrl = $student['photo'] ? UPLOAD_URL . e($student['photo']) : null;
?>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="card p-4 text-center">
      <?php if ($photoUrl): ?>
        <img src="<?= $photoUrl ?>" alt="<?= e($student['name']) ?>" class="rounded-circle mx-auto mb-3" style="width:120px;height:120px;object-fit:cover;">
      <?php else: ?>
        <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-light" style="width:120px;height:120px;">
          <i class="bi bi-person fs-1 text-muted-custom"></i>
        </div>
      <?php endif; ?>
      <h5 class="mb-1"><?= e($student['name']) ?></h5>
      <p class="text-muted-custom mb-0">Student ID: <?= e($student['student_id']) ?></p>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card p-4 mb-3">
      <h6 class="mb-3">Student Information</h6>
      <table class="table table-borderless mb-0">
        <tr><th class="w-25 text-muted-custom">Name</th><td><?= e($student['name']) ?></td></tr>
        <tr><th class="text-muted-custom">Class</th><td><?= e($student['class_name'] ?? '-') ?></td></tr>
        <tr><th class="text-muted-custom">Division</th><td><?= e($student['division'] ?? '-') ?></td></tr>
        <tr><th class="text-muted-custom">Date of Birth</th><td><?= formatDate($student['date_of_birth']) ?></td></tr>
        <tr><th class="text-muted-custom">Address</th><td><?= e($student['address'] ?? '-') ?></td></tr>
        <tr><th class="text-muted-custom">Contact</th><td><?= e($student['contact'] ?? '-') ?></td></tr>
      </table>
    </div>

    <div class="card p-4">
      <h6 class="mb-3">Parent / Guardian Information</h6>
      <?php if ($student['parent_name']): ?>
        <table class="table table-borderless mb-0">
          <tr><th class="w-25 text-muted-custom">Name</th><td><?= e($student['parent_name']) ?></td></tr>
          <tr><th class="text-muted-custom">Contact</th><td><?= e($student['parent_contact'] ?? '-') ?></td></tr>
          <tr><th class="text-muted-custom">Email</th><td><?= e($student['parent_email'] ?? '-') ?></td></tr>
        </table>
      <?php else: ?>
        <p class="text-muted-custom mb-0">No parent/guardian information on file.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
