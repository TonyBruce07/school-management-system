<?php
/**
 * admin/students/delete.php
 * POST-only destructive action. Only admins may delete students
 * (per spec — teachers cannot, and requireRole() enforces this
 * server-side regardless of what the UI shows).
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/students/index.php');
    exit;
}
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);

$stmt = $pdo->prepare("SELECT user_id, name FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if ($student) {
    $pdo->beginTransaction();
    try {
        // attendance, fees, examinations link by class not student except attendance/fees
        // which cascade automatically via ON DELETE CASCADE foreign keys.
        $del = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $del->execute([$id]);

        // The login account is specific to this student — remove it too.
        if (!empty($student['user_id'])) {
            $delUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $delUser->execute([$student['user_id']]);
        }

        $pdo->commit();
        setFlash('success', 'Student "' . $student['name'] . '" was deleted.');
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('danger', 'Could not delete this student. Please try again.');
    }
} else {
    setFlash('danger', 'Student not found.');
}

header('Location: ' . BASE_URL . '/admin/students/index.php');
exit;
