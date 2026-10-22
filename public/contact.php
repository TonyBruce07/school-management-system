<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Contact / Location';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <h1 class="section-title">Contact / Location</h1>
    <p class="section-subtitle">Get in touch with <?= e(SCHOOL_NAME) ?></p>
    <div class="contact-grid">
      <div class="info-card">
        <h6><i class="bi bi-geo-alt"></i> Address</h6>
        <p class="mb-0"><?= e(SCHOOL_ADDRESS) ?></p>
      </div>
      <div class="info-card contact-map">
        <div>
          <i class="bi bi-map fs-1 text-muted-custom"></i>
          <p class="text-muted-custom mb-0 mt-2">[Map to be added once the school confirms exact coordinates]</p>
        </div>
      </div>
      <div class="info-card">
        <h6><i class="bi bi-telephone"></i> Phone</h6>
        <p class="mb-0">For enquiries regarding admissions, school activities, and other information, please contact the school office. 9654111121</p>
      </div>
      <div class="info-card">
        <h6><i class="bi bi-envelope"></i> Email</h6>
        <p class="mb-0">For general enquiries, parents and guardians may contact the school office by email. stephen12@gmail.com</p>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
