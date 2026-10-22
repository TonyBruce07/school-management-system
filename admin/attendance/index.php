<?php
/**
 * admin/attendance/index.php
 * Read-only attendance overview for administrators. Daily marking is
 * done by teachers (Teacher Portal, next phase) — this page lets
 * admin staff review records by class/division/date, per spec
 * section 19's "Attendance" nav item and the dashboard's "Today's
 * Attendance" stat.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$pageTitle = 'Attendance Overview';

$classId  = $_GET['class_id'] ?? '';
$division = trim($_GET['division'] ?? '');
$date     = $_GET['date'] ?? date('Y-m-d');

$where = ['a.date = ?'];
$params = [$date];
if ($classId !== '') { $where[] = 's.class_id = ?'; $params[] = $classId; }
if ($division !== '') { $where[] = 's.division = ?'; $params[] = $division; }
$whereSql = implode(' AND ', $where);

$stmt = $pdo->prepare(
    "SELECT s.student_id, s.name, s.division, c.class_name, a.status, t.name AS teacher_name
     FROM attendance a
     JOIN students s ON s.id = a.student_id
     LEFT JOIN classes c ON c.id = s.class_id
     LEFT JOIN teachers t ON t.id = a.teacher_id
     WHERE $whereSql
     ORDER BY c.class_name, s.division, s.name"
);
$stmt->execute($params);
$records = $stmt->fetchAll();

$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();

require __DIR__ . '/../../includes/portal_header.php';
?>
<div class="card p-3">
  <h6 class="mb-3">Attendance Overview</h6>
  <form method="get" class="row g-2 mb-3">
    <div class="col-sm-3">
      <select name="class_id" class="form-select form-select-sm">
        <option value="">All Classes</option>
        <?php foreach ($classes as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $classId == $c['id'] ? 'selected' : '' ?>><?= e($c['class_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-sm-2">
      <input type="text" name="division" class="form-control form-control-sm" placeholder="Division" value="<?= e($division) ?>">
    </div>
    <div class="col-sm-3">
      <input type="date" name="date" class="form-control form-control-sm" value="<?= e($date) ?>">
    </div>
    <div class="col-sm-2">
      <button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-search"></i> Filter</button>
    </div>
    <div class="col-sm-2">
      <a href="<?= BASE_URL ?>/admin/attendance/index.php" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
    </div>
  </form>

  <?php if (empty($records)): ?>
    <div class="empty-state"><i class="bi bi-calendar-x"></i>No attendance records found for <?= formatDate($date) ?>.</div>
  <?php else: ?>
    <div class="table-responsive-wrapper">
      <table class="table table-hover align-middle">
        <thead><tr><th>Student ID</th><th>Name</th><th>Class</th><th>Division</th><th>Status</th><th>Marked By</th></tr></thead>
        <tbody>
          <?php foreach ($records as $r): ?>
            <tr>
              <td><?= e($r['student_id']) ?></td>
              <td><?= e($r['name']) ?></td>
              <td><?= e($r['class_name'] ?? '-') ?></td>
              <td><?= e($r['division'] ?? '-') ?></td>
              <td><?php if ($r['status'] === 'Present'): ?><span class="badge badge-status-present">Present</span>
                  <?php else: ?><span class="badge badge-status-absent">Absent</span><?php endif; ?></td>
              <td class="text-muted-custom"><?= e($r['teacher_name'] ?? '-') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
