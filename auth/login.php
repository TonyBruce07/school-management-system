<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, go straight to the correct dashboard.
if (isLoggedIn()) {
    header('Location: ' . dashboardUrlForRole(currentRole()));
    exit;
}

$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
$pageTitle = 'Portal Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> | <?= e(SCHOOL_NAME) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<style>
  body { background: linear-gradient(135deg, var(--color-primary-dark), var(--color-primary)); min-height: 100vh; display:flex; align-items:center; }
  .login-card { max-width: 420px; width: 100%; margin: auto; }
  .login-logo { width: 56px; height: 56px; margin-bottom: 0.75rem; }
</style>
</head>
<body>
  <div class="container">
    <div class="card login-card p-4 p-md-5 shadow-lg">
      <div class="text-center mb-4">
        <img src="<?= BASE_URL ?>/assets/images/logo-placeholder.svg" alt="School Logo" class="login-logo">
        <h4 class="mb-0"><?= e(SCHOOL_NAME) ?></h4>
        <p class="text-muted-custom small">Secure Management Portal Login</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= e($error) ?></div>
      <?php endif; ?>

      <form action="<?= BASE_URL ?>/auth/authenticate.php" method="post" class="needs-validation" novalidate>
        <div class="mb-3">
          <label for="username" class="form-label">Username <span class="required-mark">*</span></label>
          <input type="text" class="form-control" id="username" name="username" required autofocus>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password <span class="required-mark">*</span></label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">
          <i class="bi bi-box-arrow-in-right"></i> Login
        </button>
      </form>

      <div class="text-center mt-4">
        <a href="<?= BASE_URL ?>/public/index.php" class="small"><i class="bi bi-arrow-left"></i> Back to public website</a>
      </div>
    </div>
  </div>
</body>
</html>
