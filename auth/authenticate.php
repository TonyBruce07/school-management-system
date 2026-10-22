<?php
/**
 * auth/authenticate.php
 * Processes the login form. Never rendered directly by the user.
 *
 * Login flow (per project spec):
 * receive request -> validate input -> find account -> verify password
 * -> check account status -> identify role -> create session -> redirect
 */
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    $_SESSION['login_error'] = 'Please enter both username and password.';
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, username, password_hash, role, status FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();

// Deliberately generic error message — never reveal whether the
// username exists or whether it was the password that was wrong.
$genericError = 'Invalid username or password.';

if (!$user || !password_verify($password, $user['password_hash'])) {
    $_SESSION['login_error'] = $genericError;
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

if ($user['status'] !== 'active') {
    $_SESSION['login_error'] = 'This account has been deactivated. Please contact the school administrator.';
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

loginUser($user);
header('Location: ' . dashboardUrlForRole($user['role']));
exit;
