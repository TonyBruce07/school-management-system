<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/events/index.php');
    exit;
}
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
$stmt->execute([$id]);

setFlash('success', 'Event deleted.');
header('Location: ' . BASE_URL . '/admin/events/index.php');
exit;
