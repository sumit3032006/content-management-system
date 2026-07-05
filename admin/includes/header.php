<?php
/**
 * admin/includes/header.php
 * Common HTML head + top navbar for every admin page.
 * Expects $pageTitle to be set by the including page.
 */
$pageTitle = $pageTitle ?? 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> | Admin - <?= e(setting('site_name', SITE_NAME_DEFAULT)) ?></title>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<!-- Admin custom styles -->
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
<!-- TinyMCE Rich Text Editor -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>
<div class="admin-wrapper">

<!-- Sidebar -->
<?php include __DIR__ . '/sidebar.php'; ?>

<!-- Main content area -->
<div class="admin-content">

  <!-- Top navbar -->
  <nav class="navbar navbar-light bg-white border-bottom px-3 admin-topbar">
    <button class="btn btn-outline-secondary d-lg-none" id="sidebarToggle"><i class="bi bi-list"></i></button>
    <span class="fw-semibold ms-2 flex-grow-1"><?= e($pageTitle) ?></span>
    <a href="search.php" class="btn btn-sm btn-outline-secondary me-2" title="Global Search"><i class="bi bi-search"></i></a>
    <div class="dropdown">
      <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
        <i class="bi bi-person-circle fs-4 me-1"></i>
        <span class="d-none d-sm-inline"><?= e($_SESSION['user_name'] ?? 'User') ?></span>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
        <li><a class="dropdown-item" href="change-password.php"><i class="bi bi-key me-2"></i>Change Password</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
      </ul>
    </div>
  </nav>

  <main class="p-3 p-md-4">
