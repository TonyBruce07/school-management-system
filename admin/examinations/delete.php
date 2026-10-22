<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/examinations/index.php');
    exit;
}
verifyCsrf();
$id = (int)($_POST['id'] ?? 0);
$del = $pdo->prepare("DELETE FROM examinations WHERE id = ?");
$del->execute([$id]);
setFlash('success', 'Examination entry deleted.');
header('Location: ' . BASE_URL . '/admin/examinations/index.php');
exit;
