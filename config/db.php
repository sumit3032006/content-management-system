<?php
/**
 * db.php
 * PDO database connection (prepared statements everywhere protects against SQL injection).
 * Update the credentials below to match your hosting environment.
 */

// ---- Database credentials ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'cms_db');
define('DB_USER', 'root');
define('DB_PASS', '');       // set your MySQL password here on live hosting
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false, // real prepared statements
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Never leak DB credentials/errors to the visitor
    error_log('DB Connection Error: ' . $e->getMessage());
    die('Database connection failed. Please check your configuration in config/db.php.');
}
