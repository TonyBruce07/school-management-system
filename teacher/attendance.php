<?php
/**
 * teacher/attendance.php
 * Mark or correct daily attendance for one class/division at a time.
 * The chosen class/division is validated server-side against
 * getTeacherAssignedClasses() — a teacher cannot mark attendance for
 * a class they are not scheduled to teach, no matter what the form
 * is edited to submit.
 *
 * Uses INSERT ... ON DUPLICATE KEY UPDATE against the existing
 * UNIQUE(student_id, date) constraint, so re-submitting the same
 * date corrects the previously recorded status (spec requirement:
 * "Teacher can correct attendance").
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireRole('teacher');

$teacher = getCurrentTeacher($pdo);
$pageTitle = 'Attendance';

require __DIR__ . '/../includes/portal_header.php';

if (!$teacher) {
    echo '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i>Your account is not yet linked to a teacher record. Please contact the school administrator.</div>';
    require __DIR__ . '/../includes/portal_footer.php';
    exit;
}

$teacherId = (int)$teacher['id'];
$assignedClasses = getTeacherAssignedClasses($pdo, $teacherId);

function isAssigned(array $assignedClasses, $classId, $division): bool
{
    foreach ($assignedClasses as $c) {
        if ((string)$c['class_id'] === (string)$classId && (string)$c['division'] === (string)$division) {
            return true;
        }
    }
    return false;
}

$errors = [];

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $classId  = $_POST['class_id'] ?? '';
    $division = trim($_POST['division'] ?? '');
    $date     = $_POST['date'] ?? date('Y-m-d');
    $statuses = $_POST['status'] ?? []; // [student_id => 'Present'|'Absent']

    if (!isAssigned($assignedClasses, $classId, $division)) {
        $errors[] = 'You are not assigned to that class/division.';
    } elseif (empty($statuses)) {
        $errors[] = 'No students to mark.';
    } else {
        $upsert = $pdo->prepare(
            "INSERT INTO attendance (student_id, teacher_id, date, status)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE status = VALUES(status), teacher_id = VALUES(teacher_id)"
        );
        foreach ($statuses as $studentId => $status) {
            if (!in_array($status, ['Present', 'Absent'], true)) continue;
            $upsert->execute([(int)$studentId, $teacherId, $date, $status]);
        }
        setFlash('success', 'Attendance saved for ' . formatDate($date) . '.');
        header('Location: ' . BASE_URL . '/teacher/attendance.php?class_id=' . urlencode($classId) . '&division=' . urlencode($division) . '&date=' . urlencode($date));
        exit;
    }
}

// Selection (GET) — which class/division/date to view/mark
$selClassId  = $_GET['class_id'] ?? ($_POST['class_id'] ?? '');
$selDivision = trim($_GET['division'] ?? ($_POST['division'] ?? ''));
$selDate     = $_GET['date'] ?? ($_POST['date'] ?? date('Y-m-d'));

$students = [];
$existingStatus = [];
if ($selClassId !== '' && $selDivision !== '') {
    if (!isAssigned($assignedClasses, $selClassId, $selDivision)) {
        $errors[] = 'You are not assigned to that class/division.';
        $selClassId = '';
        $selDivision = '';
    } else {
        $stmt = $pdo->prepare("SELECT id, student_id, name FROM students WHERE class_id = ? AND division = ? ORDER BY name");
        $stmt->execute([$selClassId, $selDivision]);
        $students = $stmt->fetchAll();

        $existStmt = $pdo->prepare(
            "SELECT student_id, status FROM attendance WHERE date = ? AND student_id IN (
                SELECT id FROM students WHERE class_id = ? AND division = ?
             )"
        );
        $existStmt->execute([$selDate, $selClassId, $selDivision]);
        foreach ($existStmt->fetchAll() as $row) {
            $existingStatus[$row['student_id']] = $row['status'];
        }
    }
}
?>

<div class="card p-3 mb-3">
  <h6 class="mb-3">Select Class, Division and Date</h6>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <?php if (empty($assignedClasses)): ?>
    <div class="empty-state"><i class="bi bi-calendar-x"></i>You have no assigned classes in the timetable yet. Contact the administrator.</div>
  <?php else: ?>
    <form method="get" class="row g-2">
      <div class="col-sm-4">
        <select name="class_division" class="form-select form-select-sm" onchange="
            var v=this.value.split('|'); var f=this.form;
            f.class_id.value=v[0]||''; f.division.value=v[1]||''; f.submit();">
          <option value="">Select class & division</option>
          <?php foreach ($assignedClasses as $cd): $val = $cd['class_id'] . '|' . $cd['division']; ?>
            <option value="<?= e($val) ?>" <?= ($selClassId == $cd['class_id'] && $selDivision === $cd['division']) ? 'selected' : '' ?>>
              <?= e($cd['class_name'] ?? '-') ?> · Division <?= e($cd['division']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <input type="hidden" name="class_id" value="<?= e($selClassId) ?>">
        <input type="hidden" name="division" value="<?= e($selDivision) ?>">
      </div>
      <div class="col-sm-3">
        <input type="date" name="date" class="form-control form-control-sm" value="<?= e($selDate) ?>" onchange="this.form.submit()">
      </div>
    </form>
  <?php endif; ?>
</div>

<?php if ($selClassId !== '' && $selDivision !== ''): ?>
  <div class="card p-3">
    <h6 class="mb-3">Mark Attendance — <?= formatDate($selDate) ?></h6>
    <?php if (empty($students)): ?>
      <div class="empty-state"><i class="bi bi-people"></i>No students found in this class/division.</div>
    <?php else: ?>
      <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="class_id" value="<?= e($selClassId) ?>">
        <input type="hidden" name="division" value="<?= e($selDivision) ?>">
        <input type="hidden" name="date" value="<?= e($selDate) ?>">
        <div class="table-responsive-wrapper">
          <table class="table table-hover align-middle">
            <thead><tr><th>Student ID</th><th>Name</th><th>Present</th><th>Absent</th></tr></thead>
            <tbody>
              <?php foreach ($students as $s):
                $current = $existingStatus[$s['id']] ?? 'Present'; ?>
                <tr>
                  <td><?= e($s['student_id']) ?></td>
                  <td><?= e($s['name']) ?></td>
                  <td><input type="radio" name="status[<?= $s['id'] ?>]" value="Present" <?= $current === 'Present' ? 'checked' : '' ?>></td>
                  <td><input type="radio" name="status[<?= $s['id'] ?>]" value="Absent" <?= $current === 'Absent' ? 'checked' : '' ?>></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Attendance</button>
      </form>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
