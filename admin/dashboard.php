<?php
/**
 * admin/dashboard.php
 * All statistics are live COUNT()/SUM() queries — nothing hard-coded.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$pageTitle = 'Admin Dashboard';

$totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalTeachers = (int)$pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();

$todayAttendance = $pdo->prepare(
    "SELECT SUM(status='Present') AS present, SUM(status='Absent') AS absent, COUNT(*) AS total
     FROM attendance WHERE date = CURDATE()"
);
$todayAttendance->execute();
$today = $todayAttendance->fetch();
$todayPresent = (int)($today['present'] ?? 0);
$todayAbsent  = (int)($today['absent'] ?? 0);
$todayTotal   = (int)($today['total'] ?? 0);

$pendingFees = (float)$pdo->query("SELECT COALESCE(SUM(pending_amount),0) FROM fees WHERE pending_amount > 0")->fetchColumn();

$upcomingExamsStmt = $pdo->query(
    "SELECT e.subject, e.exam_date, c.class_name, e.division
     FROM examinations e LEFT JOIN classes c ON c.id = e.class_id
     WHERE e.exam_date >= CURDATE() ORDER BY e.exam_date ASC LIMIT 5"
);
$upcomingExams = $upcomingExamsStmt->fetchAll();

$recentNotices = $pdo->query("SELECT title, date, visibility FROM notices ORDER BY date DESC LIMIT 5")->fetchAll();
$recentEvents  = $pdo->query("SELECT event_name, date, visibility FROM events WHERE date >= CURDATE() ORDER BY date ASC LIMIT 5")->fetchAll();

require __DIR__ . '/../includes/portal_header.php';
?>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-people"></i></div>
      <div><div class="stat-value"><?= $totalStudents ?></div><div class="stat-label">Total Students</div></div></div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-person-badge"></i></div>
      <div><div class="stat-value"><?= $totalTeachers ?></div><div class="stat-label">Total Teachers</div></div></div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
      <div><div class="stat-value"><?= $todayPresent ?>/<?= $todayTotal ?></div><div class="stat-label">Today's Attendance (Present)</div></div></div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card"><div class="stat-icon" style="background:rgba(179,38,30,0.1); color:var(--color-danger);"><i class="bi bi-cash-coin"></i></div>
      <div><div class="stat-value"><?= formatCurrency($pendingFees) ?></div><div class="stat-label">Total Pending Fees</div></div></div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="card p-3">
      <h6 class="mb-3"><i class="bi bi-journal-text"></i> Upcoming Examinations</h6>
      <?php if (empty($upcomingExams)): ?>
        <div class="empty-state py-3"><i class="bi bi-journal-text"></i>No upcoming examinations.</div>
      <?php else: foreach ($upcomingExams as $ex): ?>
        <div class="notice-card">
          <div class="date"><?= formatDate($ex['exam_date']) ?></div>
          <div class="small"><?= e($ex['subject']) ?> — <?= e($ex['class_name'] ?? '-') ?> <?= e($ex['division']) ?></div>
        </div>
      <?php endforeach; endif; ?>
      <a href="<?= BASE_URL ?>/admin/examinations/index.php" class="small">Manage examinations &rarr;</a>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card p-3">
      <h6 class="mb-3"><i class="bi bi-megaphone"></i> Recent Notices</h6>
      <?php if (empty($recentNotices)): ?>
        <div class="empty-state py-3"><i class="bi bi-megaphone"></i>No notices yet.</div>
      <?php else: foreach ($recentNotices as $n): ?>
        <div class="notice-card">
          <div class="date"><?= formatDate($n['date']) ?> · <span class="text-uppercase small"><?= e($n['visibility']) ?></span></div>
          <div class="small"><?= e($n['title']) ?></div>
        </div>
      <?php endforeach; endif; ?>
      <a href="<?= BASE_URL ?>/admin/notices/index.php" class="small">Manage notices &rarr;</a>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card p-3">
      <h6 class="mb-3"><i class="bi bi-calendar-event"></i> Upcoming Events</h6>
      <?php if (empty($recentEvents)): ?>
        <div class="empty-state py-3"><i class="bi bi-calendar-event"></i>No upcoming events.</div>
      <?php else: foreach ($recentEvents as $ev): ?>
        <div class="notice-card">
          <div class="date"><?= formatDate($ev['date']) ?> · <span class="text-uppercase small"><?= e($ev['visibility']) ?></span></div>
          <div class="small"><?= e($ev['event_name']) ?></div>
        </div>
      <?php endforeach; endif; ?>
      <a href="<?= BASE_URL ?>/admin/events/index.php" class="small">Manage events &rarr;</a>
    </div>
  </div>
</div>

<div class="row g-3 mt-1">
  <div class="col-12">
    <div class="card p-3 d-flex flex-row flex-wrap gap-2">
      <a href="<?= BASE_URL ?>/admin/students/add.php" class="btn btn-primary btn-sm"><i class="bi bi-person-plus"></i> Add Student</a>
      <a href="<?= BASE_URL ?>/admin/teachers/add.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-person-badge"></i> Add Teacher</a>
      <a href="<?= BASE_URL ?>/admin/notices/add.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-megaphone"></i> Post Notice</a>
      <a href="<?= BASE_URL ?>/admin/events/add.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-calendar-event"></i> Add Event</a>
      <a href="<?= BASE_URL ?>/admin/users/index.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-shield-lock"></i> User Accounts</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
