<?php
/**
 * admin/students/index.php
 * List all students with search (by name/student_id) and class/division filter.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

$pageTitle = 'Students';

$search   = trim($_GET['q'] ?? '');
$classId  = $_GET['class_id'] ?? '';
$division = trim($_GET['division'] ?? '');

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
if ($division !== '') {
    $where[] = 's.division = ?';
    $params[] = $division;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = $pdo->prepare(
    "SELECT s.id, s.student_id, s.name, s.division, s.contact, c.class_name, u.status AS account_status
     FROM students s
     LEFT JOIN classes c ON c.id = s.class_id
     LEFT JOIN users u ON u.id = s.user_id
     $whereSql
     ORDER BY c.class_name, s.division, s.name"
);
$stmt->execute($params);
$students = $stmt->fetchAll();

$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name")->fetchAll();

require __DIR__ . '/../../includes/portal_header.php';
?>

<div class="card p-3">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h6 class="mb-0">All Students (<?= count($students) ?>)</h6>
    <a href="<?= BASE_URL ?>/admin/students/add.php" class="btn btn-primary btn-sm"><i class="bi bi-person-plus"></i> Add Student</a>
  </div>

  <form method="get" class="row g-2 mb-3">
    <div class="col-sm-4">
      <input type="text" name="q" class="form-control form-control-sm" placeholder="Search name or student ID" value="<?= e($search) ?>">
    </div>
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
    <div class="col-sm-2">
      <button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-search"></i> Filter</button>
    </div>
    <div class="col-sm-1">
      <a href="<?= BASE_URL ?>/admin/students/index.php" class="btn btn-outline-secondary btn-sm w-100" title="Clear"><i class="bi bi-x"></i></a>
    </div>
  </form>

  <?php if (empty($students)): ?>
    <div class="empty-state"><i class="bi bi-people"></i>No students found.</div>
  <?php else: ?>
    <div class="table-responsive-wrapper">
      <table class="table table-hover align-middle">
        <thead><tr><th>ID</th><th>Student</th><th>Class</th><th>Division</th><th>Contact</th><th>Account</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($students as $s): ?>
            <tr>
              <td><?= e($s['student_id']) ?></td>
              <td><?= e($s['name']) ?></td>
              <td><?= e($s['class_name'] ?? '-') ?></td>
              <td><?= e($s['division'] ?? '-') ?></td>
              <td><?= e($s['contact'] ?? '-') ?></td>
              <td>
                <?php if ($s['account_status'] === 'active'): ?>
                  <span class="badge badge-status-present">Active</span>
                <?php elseif ($s['account_status'] === 'inactive'): ?>
                  <span class="badge badge-status-absent">Inactive</span>
                <?php else: ?>
                  <span class="badge bg-secondary">No login</span>
                <?php endif; ?>
              </td>
              <td class="text-nowrap">
                <a href="<?= BASE_URL ?>/admin/students/view.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
                <a href="<?= BASE_URL ?>/admin/students/edit.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                <form action="<?= BASE_URL ?>/admin/students/delete.php" method="post" class="d-inline">
                  <?= csrfField() ?>
                  <input type="hidden" name="id" value="<?= $s['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger btn-delete-confirm" data-label="<?= e($s['name']) ?>" title="Delete"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
