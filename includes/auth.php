<?php
/**
 * includes/auth.php
 * -----------------------------------------------------------------
 * Session + authorization helpers.
 * Include this file at the very top of EVERY protected page
 * (student/teacher/admin/principal), before any HTML output.
 * -----------------------------------------------------------------
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Returns true if a user is currently logged in.
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id'], $_SESSION['role']);
}

/**
 * Returns the logged-in user's role, or null.
 */
function currentRole(): ?string
{
    return $_SESSION['role'] ?? null;
}

/**
 * Forces login. Call at the top of every protected page.
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

/**
 * Forces login AND restricts the page to one or more roles.
 * Example: requireRole('admin');
 *          requireRole(['admin', 'principal']);
 *
 * IMPORTANT: This is the real security boundary. Hiding a button in
 * the UI is not enough — every protected page must call this.
 */
function requireRole($roles): void
{
    requireLogin();
    $roles = is_array($roles) ? $roles : [$roles];
    if (!in_array(currentRole(), $roles, true)) {
        http_response_code(403);
        require __DIR__ . '/../includes/403.php';
        exit;
    }
}

/**
 * Logs a user in: sets up the session after credentials have
 * already been verified by auth/authenticate.php.
 */
function loginUser(array $user): void
{
    session_regenerate_id(true); // prevent session fixation
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];
}

/**
 * Destroys the session completely.
 */
function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie('PHPSESSID', '', time() - 42000, $params['path'], $params['domain']);
    }
    session_destroy();
}

/**
 * Returns the correct dashboard URL for the logged-in user's role.
 */
function dashboardUrlForRole(string $role): string
{
    return match ($role) {
        'student'   => BASE_URL . '/student/dashboard.php',
        'teacher'   => BASE_URL . '/teacher/dashboard.php',
        'admin'     => BASE_URL . '/admin/dashboard.php',
        'principal' => BASE_URL . '/principal/dashboard.php',
        default     => BASE_URL . '/auth/login.php',
    };
}
