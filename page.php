<?php
/**
 * page.php
 * Renders any published CMS page by its slug, e.g. page.php?slug=about
 * Used for About, Services, and any custom pages created from the admin panel.
 */
require_once __DIR__ . '/includes/functions.php';

$slug = clean($_GET['slug'] ?? '');
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'published' LIMIT 1");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    http_response_code(404);
    $pageTitle = 'Page Not Found';
    include __DIR__ . '/includes/site-header.php';
    echo '<main class="container py-5 text-center"><h1 class="display-4">404</h1><p class="text-muted">The page you are looking for does not exist.</p><a href="' . BASE_URL . '/index.php" class="btn btn-primary">Back to Home</a></main>';
    include __DIR__ . '/includes/site-footer.php';
    exit;
}

$pageTitle = $page['meta_title'] ?: $page['title'];
$metaDescription = $page['meta_description'];
include __DIR__ . '/includes/site-header.php';
?>

<header class="py-5 bg-light text-center border-bottom">
  <div class="container">
    <h1 class="fw-bold"><?= e($page['title']) ?></h1>
  </div>
</header>

<main class="container py-5">
  <?php if ($page['featured_image']): ?>
    <img src="<?= BASE_URL.'/'.e($page['featured_image']) ?>" class="img-fluid rounded mb-4" alt="<?= e($page['title']) ?>">
  <?php endif; ?>
  <div class="mx-auto" style="max-width:800px;">
    <?= $page['content'] ?>
  </div>
</main>

<?php include __DIR__ . '/includes/site-footer.php'; ?>
