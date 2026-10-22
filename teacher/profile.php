<?php
/**
 * teacher/profile.php
 * Read-only profile for the logged-in teacher only.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('teacher');

$teacher = getCurrentTeacher($pdo);
$pageTitle = 'My Profile';

require __DIR__ . '/../includes/portal_header.php';

if (!$teacher) {
    echo '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Your account is not yet linked to a teacher record. Please contact the school administrator.</div>';
    require __DIR__ . '/../includes/portal_footer.php';
    exit;
}

$assignedClasses = getTeacherAssignedClasses($pdo, (int)$teacher['id']);
?>
<div class="row g-4">
  <div class="col-lg-4">
    <div class="card p-4 text-center">
      <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center bg-light" style="width:110px;height:110px;">
        <i class="bi bi-person-badge fs-1 text-muted-custom"></i>
      </div>
      <h5 class="mb-1"><?= e($teacher['name']) ?></h5>
      <p class="text-muted-custom mb-0">Teacher ID: <?= e($teacher['teacher_id']) ?></p>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card p-4 mb-3">
      <h6 class="mb-3">Teacher Information</h6>
      <table class="table table-borderless mb-0">
        <tr><th class="w-25 text-muted-custom">Name</th><td><?= e($teacher['name']) ?></td></tr>
        <tr><th class="text-muted-custom">Subject</th><td><?= e($teacher['subject'] ?? '-') ?></td></tr>
        <tr><th class="text-muted-custom">Contact</th><td><?= e($teacher['contact_no'] ?? '-') ?></td></tr>
        <tr><th class="text-muted-custom">Email</th><td><?= e($teacher['email'] ?? '-') ?></td></tr>
      </table>
    </div>
    <div class="card p-4">
      <h6 class="mb-3">Assigned Classes / Divisions</h6>
      <?php if (empty($assignedClasses)): ?>
        <p class="text-muted-custom mb-0">No classes are currently assigned to you in the timetable.</p>
      <?php else: ?>
        <ul class="mb-0">
          <?php foreach ($assignedClasses as $cd): ?>
            <li><?= e($cd['class_name'] ?? '-') ?> · Division <?= e($cd['division']) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
