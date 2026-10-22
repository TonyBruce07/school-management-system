<?php
/**
 * config/config.php
 * General application-wide configuration.
 * Included by includes/auth.php, which is in turn included by every
 * protected page.
 */

// Base URL of the project as placed inside htdocs.
// Example: if the project folder is xampp/htdocs/school-management-system
// then BASE_URL should be '/school-management-system'
define('BASE_URL', '/school-management-system');

define('SCHOOL_NAME', "St. Stephen's English Medium Higher Secondary School");
define('SCHOOL_TRUST', 'Navjeevan Seva Mandal');
define('SCHOOL_ADDRESS', 'Kuida Village, Selamba Post, Sagbara Taluka, Narmada District, Gujarat, India');

// Bump this any time CSS/JS files change so browsers don't serve a
// stale cached copy — appended as ?v=... on every stylesheet/script tag.
define('ASSET_VERSION', '7');

// Upload settings
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL', BASE_URL . '/assets/uploads/');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_IMAGE_EXT', ['jpg', 'jpeg', 'png', 'webp']);

// Session must be started before anything is output.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kolkata');

// Show errors during development only. Set to 0 for production deployment.
define('APP_DEBUG', true);
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

require_once __DIR__ . '/database.php';
