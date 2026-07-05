<?php
/**
 * functions.php
 * Reusable helper functions used across the whole CMS (admin + frontend).
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

/* ---------------------------------------------------------
 |  Security Helpers
 * -------------------------------------------------------*/

// Escape output to prevent XSS
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Generate a CSRF token and store it in the session
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Output a hidden CSRF field for forms
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

// Verify CSRF token on POST requests
function csrf_verify() {
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid or expired form submission (CSRF check failed). Please go back and try again.');
    }
}

// Basic input sanitizer for strings
function clean($value) {
    return trim(strip_tags($value ?? ''));
}

/* ---------------------------------------------------------
 |  Slug / String Helpers
 * -------------------------------------------------------*/

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text ?: 'n-a';
}

// Ensure a slug is unique within a table (excluding a given id when editing)
function unique_slug(PDO $pdo, $table, $slug, $excludeId = null) {
    $base = $slug;
    $i = 1;
    while (true) {
        $sql = "SELECT id FROM `$table` WHERE slug = ?" . ($excludeId ? " AND id != ?" : "");
        $stmt = $pdo->prepare($sql);
        $excludeId ? $stmt->execute([$slug, $excludeId]) : $stmt->execute([$slug]);
        if (!$stmt->fetch()) return $slug;
        $slug = $base . '-' . (++$i);
    }
}

function excerpt($text, $limit = 150) {
    $text = trim(strip_tags($text ?? ''));
    return strlen($text) > $limit ? substr($text, 0, $limit) . '...' : $text;
}

function format_date($date, $format = 'd M Y') {
    if (!$date) return '';
    return date($format, strtotime($date));
}

/* ---------------------------------------------------------
 |  Auth Helpers
 * -------------------------------------------------------*/

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function current_user_role() {
    return $_SESSION['user_role'] ?? null;
}

function is_admin() {
    return current_user_role() === 'admin';
}

// Redirect to login if not authenticated (used at top of every admin page)
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

// Restrict a page to admin role only
function require_admin() {
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        die('Access denied. This section is restricted to administrators.');
    }
}

/* ---------------------------------------------------------
 |  Activity Log
 * -------------------------------------------------------*/

function log_activity(PDO $pdo, $action, $module = null) {
    $userId = $_SESSION['user_id'] ?? null;
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, module) VALUES (?,?,?)");
    $stmt->execute([$userId, $action, $module]);
}

/* ---------------------------------------------------------
 |  Settings Helper (cached per request)
 * -------------------------------------------------------*/

function get_settings(PDO $pdo) {
    static $settings = null;
    if ($settings === null) {
        $settings = [];
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        foreach ($stmt->fetchAll() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $settings;
}

function setting($key, $default = '') {
    global $pdo;
    $settings = get_settings($pdo);
    return $settings[$key] ?? $default;
}

/* ---------------------------------------------------------
 |  File Upload Helper (images)
 * -------------------------------------------------------*/

function handle_image_upload($fileInputName, $subfolder = '') {
    if (empty($_FILES[$fileInputName]['name'])) {
        return ['success' => false, 'message' => 'No file uploaded.'];
    }

    $file = $_FILES[$fileInputName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error occurred.'];
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'File too large. Max size is 3MB.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Invalid file type. Allowed: ' . implode(', ', ALLOWED_IMAGE_TYPES)];
    }

    // Validate it's really an image (defends against disguised uploads)
    if (!@getimagesize($file['tmp_name'])) {
        return ['success' => false, 'message' => 'File is not a valid image.'];
    }

    $targetDir = UPLOAD_PATH . ($subfolder ? '/' . trim($subfolder, '/') : '');
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $newName = uniqid('img_', true) . '.' . $ext;
    $targetPath = $targetDir . '/' . $newName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => false, 'message' => 'Failed to save uploaded file.'];
    }

    $relativePath = 'uploads' . ($subfolder ? '/' . trim($subfolder, '/') : '') . '/' . $newName;

    return [
        'success'  => true,
        'file_name'=> $newName,
        'path'     => $relativePath,
        'size'     => $file['size'],
        'type'     => $ext,
    ];
}

/* ---------------------------------------------------------
 |  Pagination Helper
 * -------------------------------------------------------*/

function paginate($totalRows, $perPage, $currentPage) {
    $totalPages = max(1, (int)ceil($totalRows / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    return compact('totalPages', 'currentPage', 'offset');
}

function render_pagination($totalPages, $currentPage, $baseUrl) {
    if ($totalPages <= 1) return '';
    $sep = (strpos($baseUrl, '?') === false) ? '?' : '&';
    $html = '<nav><ul class="pagination justify-content-center">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i === $currentPage ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . e($baseUrl) . $sep . 'page=' . $i . '">' . $i . '</a></li>';
    }
    $html .= '</ul></nav>';
    return $html;
}
