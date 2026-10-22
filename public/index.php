<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Home';

// Latest public notices
$noticeStmt = $pdo->prepare("SELECT title, description, date FROM notices WHERE visibility = 'public' ORDER BY date DESC LIMIT 3");
$noticeStmt->execute();
$notices = $noticeStmt->fetchAll();

// Upcoming public events
$eventStmt = $pdo->prepare("SELECT event_name, date, description FROM events WHERE visibility = 'public' AND date >= CURDATE() ORDER BY date ASC LIMIT 3");
$eventStmt->execute();
$events = $eventStmt->fetchAll();

// Achievements
$achStmt = $pdo->prepare("SELECT title, description FROM achievements ORDER BY date DESC LIMIT 3");
$achStmt->execute();
$achievements = $achStmt->fetchAll();

// Gallery preview
$galleryStmt = $pdo->prepare("SELECT title, image_path FROM gallery ORDER BY created_at DESC LIMIT 6");
$galleryStmt->execute();
$galleryItems = $galleryStmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<section class="hero-section">
  <div class="container">
    <h1><?= e(SCHOOL_NAME) ?></h1>
    <p class="mb-2"><?= e(SCHOOL_TRUST) ?></p>
    <p>Nurturing academic excellence and character for the students of <?= e(SCHOOL_ADDRESS) ?>.</p>
    <a href="<?= BASE_URL ?>/public/admission.php" class="btn btn-gold btn-lg mt-2">Admission Information</a>
  </div>
</section>

<section class="section">
  <div class="container">
    <h2 class="section-title">About the School</h2>
    <p class="section-subtitle">A trusted institution serving the local community</p>
    <div class="row">
      <div class="col-lg-8">
        <p>St. Stephen's English Medium Higher Secondary School, managed by Navjeevan Seva Mandal, aims to provide students with a supportive environment for academic learning, personal development, and responsible citizenship. The school community encourages curiosity, discipline, creativity, and respect for others.</p>
        <a href="<?= BASE_URL ?>/public/about.php" class="btn btn-outline-primary">Read More About Us</a>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <h2 class="section-title">School Achievements</h2>
    <p class="section-subtitle">Recognitions and milestones</p>
    <div class="row g-4">
      <?php if (empty($achievements)): ?>
        <div class="col-12"><div class="empty-state"><i class="bi bi-trophy"></i>No achievements have been published yet.</div></div>
      <?php else: foreach ($achievements as $a): ?>
        <div class="col-md-4">
          <div class="info-card">
            <i class="bi bi-trophy text-warning fs-3 mb-2"></i>
            <h5><?= e($a['title']) ?></h5>
            <p class="text-muted-custom mb-0"><?= e($a['description']) ?></p>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="row">
      <div class="col-lg-6">
        <h2 class="section-title">Upcoming Events</h2>
        <?php if (empty($events)): ?>
          <div class="empty-state"><i class="bi bi-calendar-event"></i>No upcoming events.</div>
        <?php else: foreach ($events as $ev): ?>
          <div class="notice-card">
            <div class="date"><?= formatDate($ev['date']) ?></div>
            <h6 class="mb-1"><?= e($ev['event_name']) ?></h6>
            <p class="mb-0 small text-muted-custom"><?= e($ev['description']) ?></p>
          </div>
        <?php endforeach; endif; ?>
        <a href="<?= BASE_URL ?>/public/events.php" class="small">View all events &rarr;</a>
      </div>
      <div class="col-lg-6">
        <h2 class="section-title">Latest Notices</h2>
        <?php if (empty($notices)): ?>
          <div class="empty-state"><i class="bi bi-megaphone"></i>No notices have been published yet.</div>
        <?php else: foreach ($notices as $n): ?>
          <div class="notice-card">
            <div class="date"><?= formatDate($n['date']) ?></div>
            <h6 class="mb-1"><?= e($n['title']) ?></h6>
            <p class="mb-0 small text-muted-custom"><?= e($n['description']) ?></p>
          </div>
        <?php endforeach; endif; ?>
        <a href="<?= BASE_URL ?>/public/notices.php" class="small">View all notices &rarr;</a>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <h2 class="section-title">Gallery</h2>
    <div class="row g-3">
      <?php if (empty($galleryItems)): ?>
        <div class="col-12"><div class="empty-state"><i class="bi bi-images"></i>No gallery images have been uploaded yet.</div></div>
      <?php else: foreach ($galleryItems as $g): ?>
        <div class="col-md-4 col-6 gallery-item">
          <img src="<?= UPLOAD_URL . e($g['image_path']) ?>" alt="<?= e($g['title']) ?>">
        </div>
      <?php endforeach; endif; ?>
    </div>
    <div class="text-center mt-3">
      <a href="<?= BASE_URL ?>/public/gallery.php" class="btn btn-outline-primary btn-sm">View Full Gallery</a>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <h2 class="section-title">Admission Information</h2>
        <p>Parents or guardians may contact the school office to enquire about admission availability for the desired class. The school office can provide information about the application procedure, available seats, required documents, and any applicable admission requirements.</p>
      </div>
      <div class="col-lg-5 text-lg-end">
        <a href="<?= BASE_URL ?>/public/admission.php" class="btn btn-primary btn-lg">Learn About Admission</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
