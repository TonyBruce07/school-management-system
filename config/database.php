<?php
/**
 * config/database.php
 * -----------------------------------------------------------------
 * Central database connection file.
 * Every other file that needs the database includes THIS file only.
 * Never scatter credentials elsewhere in the application.
 * -----------------------------------------------------------------
 * CHANGE THESE VALUES to match your local XAMPP / MySQL setup.
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3307'); // This XAMPP instance runs MySQL on port 3307
define('DB_NAME', 'school_management');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP MySQL root password is blank

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Never expose raw database errors to end users.
    error_log('Database connection failed: ' . $e->getMessage());
    die('A system error occurred. Please try again later or contact the administrator.');
}
