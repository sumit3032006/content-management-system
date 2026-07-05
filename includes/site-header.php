<?php
/**
 * includes/site-header.php
 * Public-facing header with dynamic navigation pulled from the pages table.
 * Expects $pageTitle and optional $metaDescription to be set by the including page.
 */
$navPages = $pdo->query("SELECT title, slug FROM pages WHERE status = 'published' AND show_in_menu = 1 ORDER BY menu_order ASC")->fetchAll();
$currentFile = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? setting('site_name', SITE_NAME_DEFAULT)) ?></title>
<meta name="description" content="<?= e($metaDescription ?? setting('site_tagline')) ?>">
<?php if (setting('site_favicon')): ?><link rel="icon" href="<?= BASE_URL.'/'.e(setting('site_favicon')) ?>"><?php endif; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/site.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= BASE_URL ?>/index.php">
      <?php if (setting('site_logo')): ?>
        <img src="<?= BASE_URL.'/'.e(setting('site_logo')) ?>" height="32" alt="logo">
      <?php else: ?>
        <i class="bi bi-layers-fill text-primary"></i>
      <?php endif; ?>
      <?= e(setting('site_name', SITE_NAME_DEFAULT)) ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link <?= $currentFile==='index.php'?'active':'' ?>" href="<?= BASE_URL ?>/index.php">Home</a></li>
        <?php foreach ($navPages as $np): ?>
          <?php if ($np['slug'] === 'home') continue; ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/page.php?slug=<?= e($np['slug']) ?>"><?= e($np['title']) ?></a></li>
        <?php endforeach; ?>
        <li class="nav-item"><a class="nav-link <?= $currentFile==='blog.php'?'active':'' ?>" href="<?= BASE_URL ?>/blog.php">Blog</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentFile==='contact.php'?'active':'' ?>" href="<?= BASE_URL ?>/contact.php">Contact</a></li>
      </ul>
    </div>
  </div>
</nav>
