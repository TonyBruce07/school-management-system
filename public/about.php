<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'About School';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <h1 class="section-title">About <?= e(SCHOOL_NAME) ?></h1>
    <p class="section-subtitle"><?= e(SCHOOL_TRUST) ?></p>
    <div class="row g-4">
      <div class="col-lg-8">
        <h5>Our Institution</h5>
        <p>The school seeks to create an inclusive learning environment where students can strengthen their academic foundations, discover their interests, and develop the skills needed for future opportunities.</p>
        <h5>Our Mission</h5>
        <p>To support students in becoming knowledgeable, confident, responsible, and compassionate individuals through quality education, practical learning, and positive values.</p>
        <h5>Our Vision</h5>
        <p>To foster a learning community where every student is encouraged to grow academically, socially, and creatively, and to contribute positively to society.</p>
      </div>
      <div class="col-lg-4">
        <div class="info-card">
          <h6>Quick Facts</h6>
          <ul class="list-unstyled mb-0 small text-muted-custom">
            <li class="mb-2"><i class="bi bi-people"></i> Approx. 600–700 students</li>
            <li class="mb-2"><i class="bi bi-person-badge"></i> Approx. 28 teaching staff</li>
            <li><i class="bi bi-geo-alt"></i> <?= e(SCHOOL_ADDRESS) ?></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
