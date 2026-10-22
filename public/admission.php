<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Admission Information';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <h1 class="section-title">Admission Information</h1>
    <p class="section-subtitle">How to apply to <?= e(SCHOOL_NAME) ?></p>
    <div class="row g-4">
      <div class="col-lg-8">
        <h5>Admission Process</h5>
        <p>Parents or guardians may contact the school office to enquire about admission availability for the desired class. The school office can provide information about the application procedure, available seats, required documents, and any applicable admission requirements.</p>
        <h5>Eligibility</h5>
        <p>Eligibility depends on the class in which admission is sought, the student's previous academic records, and the applicable school and education-board requirements. Please confirm the requirements with the school office.</p>
        <h5>Required Documents</h5>
        <p>Documents that may be requested include:
- Previous school report card or transfer certificate, where applicable.
- Student's birth certificate or other accepted age proof
- Passport-size photographs
- Parent or guardian contact information
- Any additional documents required by the school</p>
        <h5>Fee Structure</h5>
        <p>Fees depend on the applicable class and school fee policy. Parents and guardians should contact the school office for the current fee structure, payment schedule, and any applicable charges.</p>
      </div>
      <div class="col-lg-4">
        <div class="info-card">
          <h6>Have Questions?</h6>
          <p class="small text-muted-custom">Contact the school office for admission enquiries.</p>
          <a href="<?= BASE_URL ?>/public/contact.php" class="btn btn-outline-primary btn-sm w-100">Contact Us</a>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
