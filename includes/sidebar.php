<?php
/**
 * includes/sidebar.php
 * Role-aware sidebar for the secure portal (student/teacher/admin/principal).
 * Include AFTER auth.php has run requireRole(), so currentRole() is known.
 */
$role = currentRole();
$current = basename($_SERVER['PHP_SELF']);

$menus = [
    'student' => [
        ['dashboard.php', 'bi-speedometer2', 'Dashboard'],
        ['profile.php', 'bi-person', 'My Profile'],
        ['attendance.php', 'bi-calendar-check', 'Attendance'],
        ['examination.php', 'bi-journal-text', 'Examination Timetable'],
        ['timetable.php', 'bi-clock', 'Regular Timetable'],
        ['fees.php', 'bi-cash-coin', 'Fees'],
        ['notices.php', 'bi-megaphone', 'Notices'],
        ['events.php', 'bi-calendar-event', 'Events'],
    ],
    'teacher' => [
        ['dashboard.php', 'bi-speedometer2', 'Dashboard'],
        ['profile.php', 'bi-person', 'My Profile'],
        ['timetable.php', 'bi-clock', 'My Timetable'],
        ['attendance.php', 'bi-calendar-check', 'Attendance'],
        ['students.php', 'bi-people', 'Student Information'],
        ['notices.php', 'bi-megaphone', 'Notices'],
        ['events.php', 'bi-calendar-event', 'Events'],
    ],
    'admin' => [
        ['dashboard.php', 'bi-speedometer2', 'Dashboard'],
        ['students/index.php', 'bi-people', 'Students'],
        ['teachers/index.php', 'bi-person-badge', 'Teachers'],
        ['attendance/index.php', 'bi-calendar-check', 'Attendance'],
        ['examinations/index.php', 'bi-journal-text', 'Examination Timetable'],
        ['timetable/index.php', 'bi-clock', 'Regular Timetable'],
        ['fees/index.php', 'bi-cash-coin', 'Fees'],
        ['notices/index.php', 'bi-megaphone', 'Notices'],
        ['events/index.php', 'bi-calendar-event', 'Events'],
        ['users/index.php', 'bi-shield-lock', 'User Accounts'],
    ],
    'principal' => [
        ['dashboard.php', 'bi-speedometer2', 'Dashboard'],
        ['profile.php', 'bi-person', 'My Profile'],
        ['students.php', 'bi-people', 'Students'],
        ['teachers.php', 'bi-person-badge', 'Teachers'],
        ['attendance.php', 'bi-calendar-check', 'Attendance'],
        ['examinations.php', 'bi-journal-text', 'Examination Timetable'],
        ['timetable.php', 'bi-clock', 'Regular Timetable'],
        ['fees.php', 'bi-cash-coin', 'Fees'],
        ['notices.php', 'bi-megaphone', 'Notices'],
        ['events.php', 'bi-calendar-event', 'Events'],
    ],
];

$items = $menus[$role] ?? [];
?>
<aside class="portal-sidebar" id="portalSidebar">
  <div class="sidebar-brand">
    <span class="sidebar-role-badge"><?= e(ucfirst($role)) ?> Portal</span>
    <button type="button" class="sidebar-close d-lg-none" id="sidebarClose" aria-label="Close navigation">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>
  <ul class="sidebar-nav">
    <?php foreach ($items as [$link, $icon, $label]): ?>
      <li class="<?= str_ends_with($current, basename($link)) ? 'active' : '' ?>">
        <a href="<?= BASE_URL ?>/<?= $role ?>/<?= $link ?>">
          <i class="bi <?= $icon ?>"></i> <span><?= e($label) ?></span>
        </a>
      </li>
    <?php endforeach; ?>
    <li class="sidebar-logout">
      <a href="<?= BASE_URL ?>/auth/logout.php">
        <i class="bi bi-box-arrow-right"></i> <span>Logout</span>
      </a>
    </li>
  </ul>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
