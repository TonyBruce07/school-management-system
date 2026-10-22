<?php
/**
 * principal/students.php
 * Oversight-only view of all students. No add/edit/delete — those
 * remain Admin-only. No password hashes or authentication data
 * are selected or displayed.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('principal');

$pageTitle = 'Students';

$search   = trim($_GET['q'] ?? '');
$classId  = $_GET['class_id'] ?? '';

$where = [];
$params = [];
if ($search !== '') {
    $where[] = '(s.name LIKE ? OR s.student_id LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($classId !== '') {
    $where[] = 's.class_id = ?';
    $params[] = $classId;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = $pdo->prepare(
    "SELECT s.student_id, s.name, s.division, s.contact, c.class_name,
            p.name AS parent_name, p.contact_no AS parent_contact,
            u.status AS account_status
     FROM students s
     LEFT JOIN classes c ON c.id = s.class_id
     LEFT JOIN parents p ON p.id = s.parent_id
     LEFT JOIN users u ON u.id = s.user_id
     $whereSql
     ORDER BY c.class_name, s.division, s.name"
);
$stmt->execute($params);
$students = $stmt->fetchAll();

$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();

require __DIR__ . '/../includes/portal_header.php';
?>
<div class="card p-3">
  <h6 class="mb-3">Students (<?= count($students) ?>)</h6>

  <form method="get" class="row g-2 mb-3">
    <div class="col-sm-5"><input type="text" name="q" class="form-control form-control-sm" placeholder="Search name or student ID" value="<?= e($search) ?>"></div>
    <div class="col-sm-3">
      <select name="class_id" class="form-select form-select-sm">
        <option value="">All Classes</option>
        <?php foreach ($classes as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $classId == $c['id'] ? 'selected' : '' ?>><?= e($c['class_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-sm-2"><button class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-search"></i> Filter</button></div>
    <div class="col-sm-2"><a href="<?= BASE_URL ?>/principal/students.php" class="btn btn-outline-secondary btn-sm w-100">Clear</a></div>
  </form>

  <?php if (empty($students)): ?>
    <div class="empty-state"><i class="bi bi-people"></i>No students found.</div>
  <?php else: ?>
    <div class="table-responsive-wrapper">
      <table class="table table-hover align-middle">
        <thead><tr><th>ID</th><th>Name</th><th>Class</th><th>Division</th><th>Parent/Guardian</th><th>Contact</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($students as $s): ?>
            <tr>
              <td><?= e($s['student_id']) ?></td>
              <td><?= e($s['name']) ?></td>
              <td><?= e($s['class_name'] ?? '-') ?></td>
              <td><?= e($s['division'] ?? '-') ?></td>
              <td><?= e($s['parent_name'] ?? '-') ?><?= $s['parent_contact'] ? ' (' . e($s['parent_contact']) . ')' : '' ?></td>
              <td><?= e($s['contact'] ?? '-') ?></td>
              <td>
                <?php if ($s['account_status'] === 'active'): ?><span class="badge badge-status-present">Active</span>
                <?php elseif ($s['account_status'] === 'inactive'): ?><span class="badge badge-status-absent">Inactive</span>
                <?php else: ?><span class="badge bg-secondary">No login</span><?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
