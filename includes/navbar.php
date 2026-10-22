<?php
/**
 * includes/navbar.php
 * Public website navigation bar.
 */
$current = basename($_SERVER['PHP_SELF']);
function navActive(string $file, string $current): string
{
    return $file === $current ? ' active' : '';
}
?>
<header class="site-header">
  <nav class="navbar navbar-expand-lg navbar-light site-navbar">
    <div class="container">
      <a class="navbar-brand" href="<?= BASE_URL ?>/public/index.php">
        <img src="<?= BASE_URL ?>/assets/images/logo-placeholder.svg" alt="School Logo" class="brand-logo">
        <span class="brand-text">
          <span class="brand-name"><?= e(SCHOOL_NAME) ?></span>
          <span class="brand-trust"><?= e(SCHOOL_TRUST) ?></span>
        </span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <li class="nav-item"><a class="nav-link<?= navActive('index.php', $current) ?>" href="<?= BASE_URL ?>/public/index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link<?= navActive('about.php', $current) ?>" href="<?= BASE_URL ?>/public/about.php">About</a></li>
          <li class="nav-item"><a class="nav-link<?= navActive('achievements.php', $current) ?>" href="<?= BASE_URL ?>/public/achievements.php">Achievements</a></li>
          <li class="nav-item"><a class="nav-link<?= navActive('gallery.php', $current) ?>" href="<?= BASE_URL ?>/public/gallery.php">Gallery</a></li>
          <li class="nav-item"><a class="nav-link<?= navActive('events.php', $current) ?>" href="<?= BASE_URL ?>/public/events.php">Events</a></li>
          <li class="nav-item"><a class="nav-link<?= navActive('notices.php', $current) ?>" href="<?= BASE_URL ?>/public/notices.php">Notices</a></li>
          <li class="nav-item"><a class="nav-link<?= navActive('admission.php', $current) ?>" href="<?= BASE_URL ?>/public/admission.php">Admission</a></li>
          <li class="nav-item"><a class="nav-link<?= navActive('contact.php', $current) ?>" href="<?= BASE_URL ?>/public/contact.php">Contact</a></li>
          <li class="nav-item ms-lg-2">
            <a class="btn btn-login" href="<?= BASE_URL ?>/auth/login.php">
              <i class="bi bi-box-arrow-in-right"></i> Login
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</header>
