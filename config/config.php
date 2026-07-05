<?php
/**
 * config.php
 * Global site configuration & constants.
 * Edit BASE_URL to match your live domain / local XAMPP path.
 */

// ---- Error reporting (turn off display_errors in production) ----
error_reporting(E_ALL);
ini_set('display_errors', 0); // set to 1 only while debugging locally

// ---- Site base URL ----
// Examples:
//   XAMPP local:      http://localhost/cms
//   Hostinger/Infinity: https://yourdomain.com
define('BASE_URL', 'http://localhost/cms');
// ---- Filesystem paths ----
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');

// ---- Upload restrictions ----
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('MAX_UPLOAD_SIZE', 3 * 1024 * 1024); // 3 MB

// ---- Site name fallback (overridden by DB settings table) ----
define('SITE_NAME_DEFAULT', 'My Professional CMS');

// ---- Secure session settings ----
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    // Uncomment the line below when serving over HTTPS in production
    // ini_set('session.cookie_secure', 1);
    session_start();
}
