<?php
/**
 * principal/dashboard.php
 * Oversight-only overview. All figures are live queries against the
 * existing schema — nothing hard-coded, nothing the Principal can
 * edit from here.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('principal');

$pageTitle = 'Principal Dashboard';

$totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalTeachers = (int)$pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
$totalClasses  = (int)$pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();

$todayAttendance = $pdo->query(
    "SELECT SUM(status='Present') AS present, SUM(status='Absent') AS absent, COUNT(*) AS total
     FROM attendance WHERE date = CURDATE()"
)->fetch();
$todayPresent = (int)($todayAttendance['present'] ?? 0);
$todayAbsent  = (int)($todayAttendance['absent'] ?? 0);
$todayTotal   = (int)($todayAttendance['total'] ?? 0);

$feeSummary = $pdo->query(
    "SELECT COALESCE(SUM(total_fee),0) AS total, COALESCE(SUM(paid_amount),0) AS paid, COALESCE(SUM(pending_amount),0) AS pending
     FROM fees"
)->fetch();

$totalNotices = (int)$pdo->query("SELECT COUNT(*) FROM notices")->fetchColumn();

$upcomingExams = $pdo->query(
    "SELECT ex.subject, ex.exam_date, c.class_name, ex.division
     FROM examinations ex LEFT JOIN classes c ON c.id = ex.class_id
     WHERE ex.exam_date >= CURDATE() ORDER BY ex.exam_date ASC LIMIT 5"
)->fetchAll();

$recentNotices = $pdo->query("SELECT title, date, visibility FROM notices ORDER BY date DESC LIMIT 5")->fetchAll();
$upcomingEvents = $pdo->query("SELECT event_name, date, visibility FROM events WHERE date >= CURDATE() ORDER BY date ASC LIMIT 5")->fetchAll();

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
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-building"></i></div>
      <div><div class="stat-value"><?= $totalClasses ?></div><div class="stat-label">Total Classes</div></div></div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
      <div><div class="stat-value"><?= $todayPresent ?>/<?= $todayTotal ?></div><div class="stat-label">Today's Attendance (Present)</div></div></div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-4">
    <div class="stat-card"><div class="stat-icon"><i class="bi bi-wallet2"></i></div>
      <div><div class="stat-value"><?= formatCurrency($feeSummary['total']) ?></div><div class="stat-label">Total Fees (All Students)</div></div></div>
  </div>
  <div class="col-sm-6 col-lg-4">
    <div class="stat-card"><div class="stat-icon" style="background:rgba(46,125,79,0.1); color:var(--color-success);"><i class="bi bi-cash"></i></div>
      <div><div class="stat-value"><?= formatCurrency($feeSummary['paid']) ?></div><div class="stat-label">Total Collected</div></div></div>
  </div>
  <div class="col-sm-6 col-lg-4">
    <div class="stat-card"><div class="stat-icon" style="background:rgba(179,38,30,0.1); color:var(--color-danger);"><i class="bi bi-exclamation-circle"></i></div>
      <div><div class="stat-value"><?= formatCurrency($feeSummary['pending']) ?></div><div class="stat-label">Total Pending</div></div></div>
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
      <a href="<?= BASE_URL ?>/principal/examinations.php" class="small">View all examinations &rarr;</a>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card p-3">
      <h6 class="mb-3"><i class="bi bi-megaphone"></i> Recent Notices (<?= $totalNotices ?> total)</h6>
      <?php if (empty($recentNotices)): ?>
        <div class="empty-state py-3"><i class="bi bi-megaphone"></i>No notices yet.</div>
      <?php else: foreach ($recentNotices as $n): ?>
        <div class="notice-card">
          <div class="date"><?= formatDate($n['date']) ?> · <span class="text-uppercase small"><?= e($n['visibility']) ?></span></div>
          <div class="small"><?= e($n['title']) ?></div>
        </div>
      <?php endforeach; endif; ?>
      <a href="<?= BASE_URL ?>/principal/notices.php" class="small">View all notices &rarr;</a>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card p-3">
      <h6 class="mb-3"><i class="bi bi-calendar-event"></i> Upcoming Events</h6>
      <?php if (empty($upcomingEvents)): ?>
        <div class="empty-state py-3"><i class="bi bi-calendar-event"></i>No upcoming events.</div>
      <?php else: foreach ($upcomingEvents as $ev): ?>
        <div class="notice-card">
          <div class="date"><?= formatDate($ev['date']) ?> · <span class="text-uppercase small"><?= e($ev['visibility']) ?></span></div>
          <div class="small"><?= e($ev['event_name']) ?></div>
        </div>
      <?php endforeach; endif; ?>
      <a href="<?= BASE_URL ?>/principal/events.php" class="small">View all events &rarr;</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
