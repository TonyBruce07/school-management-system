<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Access Denied</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
  <div class="text-center">
    <h1 class="display-4 fw-bold text-danger">403</h1>
    <p class="lead">You do not have permission to access this page.</p>
    <a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/auth/login.php" class="btn btn-primary">Return to Login</a>
  </div>
</body>
</html>
