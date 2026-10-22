<footer class="site-footer">
  <div class="container">
    <div class="row gy-4">
      <div class="col-md-4">
        <h5 class="footer-title"><?= e(SCHOOL_NAME) ?></h5>
        <p class="footer-trust"><?= e(SCHOOL_TRUST) ?></p>
        <p class="footer-address"><i class="bi bi-geo-alt"></i> <?= e(SCHOOL_ADDRESS) ?></p>
      </div>
      <div class="col-md-4">
        <h6 class="footer-heading">Quick Links</h6>
        <ul class="footer-links">
          <li><a href="<?= BASE_URL ?>/public/about.php">About School</a></li>
          <li><a href="<?= BASE_URL ?>/public/admission.php">Admission Information</a></li>
          <li><a href="<?= BASE_URL ?>/public/notices.php">Notices</a></li>
          <li><a href="<?= BASE_URL ?>/public/contact.php">Contact / Location</a></li>
          <li><a href="<?= BASE_URL ?>/auth/login.php">Portal Login</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h6 class="footer-heading">Contact</h6>
        <p class="mb-1">[School-provided content to be added]</p>
        <p class="mb-0">[School-provided content to be added]</p>
      </div>
    </div>
    <hr class="footer-divider">
    <p class="footer-copy">&copy; <?= date('Y') ?> <?= e(SCHOOL_NAME) ?>. All rights reserved.</p>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/script.js?v=<?= ASSET_VERSION ?>"></script>
</body>
</html>
