<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/notices/index.php');
    exit;
}
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM notices WHERE id = ?");
$stmt->execute([$id]);

setFlash('success', 'Notice deleted.');
header('Location: ' . BASE_URL . '/admin/notices/index.php');
exit;
