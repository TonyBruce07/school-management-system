<?php
/**
 * teacher/dashboard.php
 * All data is scoped to the logged-in teacher via getCurrentTeacher()
 * and getTeacherAssignedClasses() — no request parameter decides identity.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('teacher');

$teacher = getCurrentTeacher($pdo);
$pageTitle = 'Dashboard';

if (!$teacher) {
    require __DIR__ . '/../includes/portal_header.php';
    echo '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Your account is not yet linked to a teacher record. Please contact the school administrator.</div>';
    require __DIR__ . '/../includes/portal_footer.php';
    exit;
}

$teacherId = (int)$teacher['id'];
$assignedClasses = getTeacherAssignedClasses($pdo, $teacherId);

$todayName = date('l'); // e.g. "Monday"
$todayStmt = $pdo->prepare(
    "SELECT rt.time_slot, rt.subject, rt.division, c.class_name
     FROM regular_timetable rt LEFT JOIN classes c ON c.id = rt.class_id
     WHERE rt.teacher_id = ? AND rt.day = ?
     ORDER BY rt.time_slot"
);
$todayStmt->execute([$teacherId, $todayName]);
$todaySlots = $todayStmt->fetchAll();

// How many of today's assigned-class students already have attendance marked today
$totalStudentsTaught = 0;
$markedToday = 0;
if ($assignedClasses) {
    $classDivPairs = [];
    $params = [];
    foreach ($assignedClasses as $cd) {
        $classDivPairs[] = '(s.class_id = ? AND s.division = ?)';
        $params[] = $cd['class_id'];
        $params[] = $cd['division'];
    }
    $whereCd = implode(' OR ', $classDivPairs);

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM students s WHERE $whereCd");
    $countStmt->execute($params);
    $totalStudentsTaught = (int)$countStmt->fetchColumn();

    $markedStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM attendance a
         JOIN students s ON s.id = a.student_id
         WHERE a.date = CURDATE() AND ($whereCd)"
    );
    $markedStmt->execute($params);
    $markedToday = (int)$markedStmt->fetchColumn();
}

$recentNotices = $pdo->query("SELECT title, date FROM notices ORDER BY date DESC LIMIT 5")->fetchAll();
$upcomingEvents = $pdo->query("SELECT event_name, date FROM events WHERE date >= CURDATE() ORDER BY date ASC LIMIT 5")->fetchAll();

require __DIR__ . '/../includes/portal_header.php';
?>

<div class="card p-4 mb-4">
  <h4 class="mb-1"><?= e($teacher['name']) ?></h4>
  <p class="text-muted-custom mb-0"><?= e($teacher['subject'] ?? '-') ?> · Teacher ID: <?= e($teacher['teacher_id']) ?></p>
</div>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-people"></i></div>
      <div><div class="stat-value"><?= count($assignedClasses) ?></div><div class="stat-label">Assigned Class/Divisions</div></div></div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-person-lines-fill"></i></div>
      <div><div class="stat-value"><?= $totalStudentsTaught ?></div><div class="stat-label">Students Taught</div></div></div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
      <div><div class="stat-value"><?= $markedToday ?>/<?= $totalStudentsTaught ?></div><div class="stat-label">Attendance Marked Today</div></div></div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-clock"></i></div>
      <div><div class="stat-value"><?= count($todaySlots) ?></div><div class="stat-label"><?= e($todayName) ?>'s Periods</div></div></div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="card p-3">
      <h6 class="mb-3"><i class="bi bi-clock"></i> Today's Timetable (<?= e($todayName) ?>)</h6>
      <?php if (empty($todaySlots)): ?>
        <div class="empty-state py-3"><i class="bi bi-clock"></i>No periods scheduled for today.</div>
      <?php else: ?>
        <div class="table-responsive-wrapper">
          <table class="table table-sm table-hover align-middle">
            <thead><tr><th>Time</th><th>Class</th><th>Subject</th></tr></thead>
            <tbody>
              <?php foreach ($todaySlots as $slot): ?>
                <tr>
                  <td><?= e($slot['time_slot']) ?></td>
                  <td><?= e($slot['class_name'] ?? '-') ?> <?= e($slot['division']) ?></td>
                  <td><?= e($slot['subject']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card p-3 mb-3">
      <h6 class="mb-3"><i class="bi bi-lightning"></i> Quick Actions</h6>
      <div class="d-flex flex-wrap gap-2">
        <a href="<?= BASE_URL ?>/teacher/attendance.php" class="btn btn-primary btn-sm"><i class="bi bi-calendar-check"></i> Mark Attendance</a>
        <a href="<?= BASE_URL ?>/teacher/students.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-people"></i> Student Information</a>
        <a href="<?= BASE_URL ?>/teacher/timetable.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-clock"></i> My Timetable</a>
        <a href="<?= BASE_URL ?>/teacher/notices.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-megaphone"></i> Notices</a>
        <a href="<?= BASE_URL ?>/teacher/events.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-calendar-event"></i> Events</a>
      </div>
    </div>
    <div class="card p-3">
      <h6 class="mb-3"><i class="bi bi-megaphone"></i> Recent Notices</h6>
      <?php if (empty($recentNotices)): ?>
        <div class="empty-state py-3"><i class="bi bi-megaphone"></i>No notices yet.</div>
      <?php else: foreach ($recentNotices as $n): ?>
        <div class="notice-card"><div class="date"><?= formatDate($n['date']) ?></div><div class="small"><?= e($n['title']) ?></div></div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
