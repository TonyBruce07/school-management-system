<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/teachers/index.php');
    exit;
}
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT user_id, name FROM teachers WHERE id = ?");
$stmt->execute([$id]);
$teacher = $stmt->fetch();

if ($teacher) {
    $pdo->beginTransaction();
    try {
        // attendance.teacher_id and regular_timetable.teacher_id are
        // ON DELETE SET NULL, so historical records are preserved.
        $del = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
        $del->execute([$id]);

        if (!empty($teacher['user_id'])) {
            $delUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $delUser->execute([$teacher['user_id']]);
        }
        $pdo->commit();
        setFlash('success', 'Teacher "' . $teacher['name'] . '" was deleted.');
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('danger', 'Could not delete this teacher. Please try again.');
    }
} else {
    setFlash('danger', 'Teacher not found.');
}

header('Location: ' . BASE_URL . '/admin/teachers/index.php');
exit;
