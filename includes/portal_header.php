<?php
/**
 * includes/portal_header.php
 * Shared <head>, topbar and sidebar opening for the SECURE PORTAL.
 * Expects $pageTitle. Must be included AFTER requireRole().
 */
if (!isset($pageTitle)) {
    $pageTitle = 'Dashboard';
}
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> | <?= e(SCHOOL_NAME) ?> Portal</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= ASSET_VERSION ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= ASSET_VERSION ?>">
</head>
<body class="portal-body">
<div class="portal-wrapper">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="portal-main">
    <header class="portal-topbar">
      <button class="sidebar-toggle d-lg-none" type="button" id="sidebarToggle" aria-expanded="false" aria-controls="portalSidebar" aria-label="Toggle navigation menu"><i class="bi bi-list"></i></button>
      <h1 class="portal-page-title"><?= e($pageTitle) ?></h1>
      <div class="portal-user">
        <i class="bi bi-person-circle"></i>
        <span class="portal-username"><?= e($_SESSION['username'] ?? '') ?></span>
      </div>
    </header>
    <main class="portal-content">
      <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
          <?= e($flash['message']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
