<?php
/**
 * install/reset_admin_password.php
 *
 * ONE-TIME USE ONLY. Run this once after importing schema.sql if you
 * cannot log in with admin / Admin@123 (bcrypt hashes can occasionally
 * behave differently across PHP builds). This regenerates the hash
 * correctly for YOUR server's PHP version.
 *
 * IMPORTANT: Delete this file (or the whole /install folder) immediately
 * after use. Leaving it publicly accessible is a security risk.
 */
require_once __DIR__ . '/../config/db.php';

$username = 'admin';
$newPassword = 'Admin@123'; // change this if you want a different password

$hash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
$stmt->execute([$hash, $username]);

if ($stmt->rowCount() > 0) {
    echo "Success! Password for '$username' has been reset to: $newPassword<br>";
    echo "<strong>Please delete this file (install/reset_admin_password.php) now.</strong>";
} else {
    echo "No user found with username '$username'. Make sure schema.sql was imported.";
}
