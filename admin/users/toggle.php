<?php
/**
 * admin/users/toggle.php
 * Flips a user account between active/inactive. POST-only, CSRF
 * protected, and an admin cannot deactivate their own account
 * (server-side check — not just a hidden button).
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/users/index.php');
    exit;
}
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);

if ($id === (int)$_SESSION['user_id']) {
    setFlash('danger', 'You cannot deactivate your own account.');
    header('Location: ' . BASE_URL . '/admin/users/index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if ($user) {
    $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
    $upd = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    $upd->execute([$newStatus, $id]);
    setFlash('success', 'Account status updated.');
} else {
    setFlash('danger', 'Account not found.');
}

header('Location: ' . BASE_URL . '/admin/users/index.php');
exit;
