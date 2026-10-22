<?php
/**
 * teacher/students.php
 * Read-only student information, scoped to the teacher's assigned
 * classes/divisions only (via getTeacherAssignedClasses()). Teachers
 * cannot edit or delete student records — that stays admin-only.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('teacher');

$teacher = getCurrentTeacher($pdo);
$pageTitle = 'Student Information';

require __DIR__ . '/../includes/portal_header.php';

if (!$teacher) {
    echo '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Your account is not yet linked to a teacher record. Please contact the school administrator.</div>';
    require __DIR__ . '/../includes/portal_footer.php';
    exit;
}

$teacherId = (int)$teacher['id'];
$assignedClasses = getTeacherAssignedClasses($pdo, $teacherId);

$students = [];
if ($assignedClasses) {
    $conditions = [];
    $params = [];
    foreach ($assignedClasses as $cd) {
        $conditions[] = '(s.class_id = ? AND s.division = ?)';
        $params[] = $cd['class_id'];
        $params[] = $cd['division'];
    }
    $whereSql = implode(' OR ', $conditions);
    $stmt = $pdo->prepare(
        "SELECT s.id, s.student_id, s.name, s.division, s.contact, c.class_name
         FROM students s LEFT JOIN classes c ON c.id = s.class_id
         WHERE $whereSql
         ORDER BY c.class_name, s.division, s.name"
    );
    $stmt->execute($params);
    $students = $stmt->fetchAll();
}
?>
<div class="card p-3">
  <h6 class="mb-3">Students in Your Assigned Classes (<?= count($students) ?>)</h6>
  <?php if (empty($students)): ?>
    <div class="empty-state"><i class="bi bi-people"></i>No students found in your assigned classes.</div>
  <?php else: ?>
    <div class="table-responsive-wrapper">
      <table class="table table-hover align-middle">
        <thead><tr><th>Student ID</th><th>Name</th><th>Class</th><th>Division</th><th>Contact</th><th>Attendance %</th></tr></thead>
        <tbody>
          <?php foreach ($students as $s): ?>
            <tr>
              <td><?= e($s['student_id']) ?></td>
              <td><?= e($s['name']) ?></td>
              <td><?= e($s['class_name'] ?? '-') ?></td>
              <td><?= e($s['division'] ?? '-') ?></td>
              <td><?= e($s['contact'] ?? '-') ?></td>
              <td><?= calculateAttendancePercentage($pdo, (int)$s['id']) ?>%</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
