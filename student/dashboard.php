<?php
/**
 * student/dashboard.php
 * Student dashboard: welcome panel + summary cards + recent notices/events.
 * All data is scoped to the logged-in student via getCurrentStudent().
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('student');

$student = getCurrentStudent($pdo);
$pageTitle = 'Dashboard';

if (!$student) {
    require __DIR__ . '/../includes/portal_header.php';
    echo '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Your account is not yet linked to a student record. Please contact the school administrator.</div>';
    require __DIR__ . '/../includes/portal_footer.php';
    exit;
}

$studentId = (int)$student['id'];

// Attendance %
$attendancePercent = calculateAttendancePercentage($pdo, $studentId);

// Pending fees
$feeStmt = $pdo->prepare("SELECT pending_amount FROM fees WHERE student_id = ? ORDER BY id DESC LIMIT 1");
$feeStmt->execute([$studentId]);
$fee = $feeStmt->fetch();
$pendingFee = $fee['pending_amount'] ?? null;

// Next upcoming exam (student's class/division)
$examStmt = $pdo->prepare(
    "SELECT subject, exam_date FROM examinations
     WHERE class_id = ? AND division = ? AND exam_date >= CURDATE()
     ORDER BY exam_date ASC LIMIT 1"
);
$examStmt->execute([$student['class_id'], $student['division']]);
$nextExam = $examStmt->fetch();

// Latest notice visible to students (public + internal — students see both)
$noticeStmt = $pdo->prepare("SELECT title, date FROM notices ORDER BY date DESC LIMIT 1");
$noticeStmt->execute();
$latestNotice = $noticeStmt->fetch();

// Recent notices (list)
$recentNoticesStmt = $pdo->prepare("SELECT title, description, date FROM notices ORDER BY date DESC LIMIT 5");
$recentNoticesStmt->execute();
$recentNotices = $recentNoticesStmt->fetchAll();

// Upcoming events
$upcomingEventsStmt = $pdo->prepare("SELECT event_name, date, description FROM events WHERE date >= CURDATE() ORDER BY date ASC LIMIT 5");
$upcomingEventsStmt->execute();
$upcomingEvents = $upcomingEventsStmt->fetchAll();

require __DIR__ . '/../includes/portal_header.php';
?>

<div class="card p-4 mb-4">
  <div class="d-flex flex-wrap align-items-center gap-3">
    <div>
      <h4 class="mb-1"><?= e($student['name']) ?></h4>
      <p class="text-muted-custom mb-0">
        <?= e($student['class_name'] ?? '-') ?> · Division <?= e($student['division'] ?? '-') ?>
        · Student ID: <?= e($student['student_id']) ?>
      </p>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
      <div>
        <div class="stat-value"><?= $attendancePercent ?>%</div>
        <div class="stat-label">Attendance</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon"><i class="bi bi-cash-coin"></i></div>
      <div>
        <div class="stat-value"><?= $pendingFee !== null ? formatCurrency($pendingFee) : '-' ?></div>
        <div class="stat-label">Pending Fees</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon"><i class="bi bi-journal-text"></i></div>
      <div>
        <div class="stat-value"><?= $nextExam ? formatDate($nextExam['exam_date']) : 'None' ?></div>
        <div class="stat-label"><?= $nextExam ? e($nextExam['subject']) . ' Exam' : 'Upcoming Exam' ?></div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon"><i class="bi bi-megaphone"></i></div>
      <div>
        <div class="stat-value" style="font-size:1rem;"><?= $latestNotice ? e($latestNotice['title']) : 'None' ?></div>
        <div class="stat-label">Latest Notice</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="card p-3">
      <h6 class="mb-3"><i class="bi bi-megaphone"></i> Recent Notices</h6>
      <?php if (empty($recentNotices)): ?>
        <div class="empty-state py-3"><i class="bi bi-megaphone"></i>No notices have been published yet.</div>
      <?php else: foreach ($recentNotices as $n): ?>
        <div class="notice-card">
          <div class="date"><?= formatDate($n['date']) ?></div>
          <h6 class="mb-1"><?= e($n['title']) ?></h6>
          <p class="mb-0 small text-muted-custom"><?= e($n['description']) ?></p>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card p-3">
      <h6 class="mb-3"><i class="bi bi-calendar-event"></i> Upcoming Events</h6>
      <?php if (empty($upcomingEvents)): ?>
        <div class="empty-state py-3"><i class="bi bi-calendar-event"></i>No upcoming events.</div>
      <?php else: foreach ($upcomingEvents as $ev): ?>
        <div class="notice-card">
          <div class="date"><?= formatDate($ev['date']) ?></div>
          <h6 class="mb-1"><?= e($ev['event_name']) ?></h6>
          <p class="mb-0 small text-muted-custom"><?= e($ev['description']) ?></p>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
