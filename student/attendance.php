<?php
/**
 * student/attendance.php
 * Shows the logged-in student's own attendance only (scoped by
 * getCurrentStudent()'s student.id — never a request parameter).
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$student = getCurrentStudent($pdo);
$pageTitle = 'Attendance';

require __DIR__ . '/../includes/portal_header.php';

if (!$student) {
    echo '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Your account is not yet linked to a student record. Please contact the school administrator.</div>';
    require __DIR__ . '/../includes/portal_footer.php';
    exit;
}

$studentId = (int)$student['id'];

$countStmt = $pdo->prepare(
    "SELECT
        SUM(status = 'Present') AS present_days,
        SUM(status = 'Absent') AS absent_days,
        COUNT(*) AS total_days
     FROM attendance WHERE student_id = ?"
);
$countStmt->execute([$studentId]);
$counts = $countStmt->fetch();
$presentDays = (int)($counts['present_days'] ?? 0);
$absentDays  = (int)($counts['absent_days'] ?? 0);
$totalDays   = (int)($counts['total_days'] ?? 0);
$percentage  = calculateAttendancePercentage($pdo, $studentId);

$recordsStmt = $pdo->prepare("SELECT date, status FROM attendance WHERE student_id = ? ORDER BY date DESC LIMIT 60");
$recordsStmt->execute([$studentId]);
$records = $recordsStmt->fetchAll();
?>

<div class="row g-3 mb-4">
  <div class="col-sm-4">
    <div class="stat-card">
      <div class="stat-icon"><i class="bi bi-percent"></i></div>
      <div><div class="stat-value"><?= $percentage ?>%</div><div class="stat-label">Attendance %</div></div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(46,125,79,0.1); color:var(--color-success);"><i class="bi bi-check-circle"></i></div>
      <div><div class="stat-value"><?= $presentDays ?></div><div class="stat-label">Present Days</div></div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(179,38,30,0.1); color:var(--color-danger);"><i class="bi bi-x-circle"></i></div>
      <div><div class="stat-value"><?= $absentDays ?></div><div class="stat-label">Absent Days</div></div>
    </div>
  </div>
</div>

<div class="card p-3">
  <h6 class="mb-3">Daily Attendance Record</h6>
  <?php if (empty($records)): ?>
    <div class="empty-state"><i class="bi bi-calendar-x"></i>No attendance has been recorded yet.</div>
  <?php else: ?>
    <div class="table-responsive-wrapper">
      <table class="table table-hover align-middle">
        <thead><tr><th>Date</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($records as $r): ?>
            <tr>
              <td><?= formatDate($r['date']) ?></td>
              <td>
                <?php if ($r['status'] === 'Present'): ?>
                  <span class="badge badge-status-present">Present</span>
                <?php else: ?>
                  <span class="badge badge-status-absent">Absent</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
