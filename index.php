<?php
/**
 * index.php
 * Public homepage. Pulls the "home" page content from the database if it
 * exists, otherwise shows a default hero section, plus latest blog posts.
 */
require_once __DIR__ . '/includes/functions.php';

$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = 'home' AND status = 'published' LIMIT 1");
$stmt->execute();
$homePage = $stmt->fetch();

$latestPosts = $pdo->query("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 3")->fetchAll();

$pageTitle = $homePage['meta_title'] ?? setting('site_name', SITE_NAME_DEFAULT);
$metaDescription = $homePage['meta_description'] ?? setting('site_tagline');
include __DIR__ . '/includes/site-header.php';
?>

<header class="py-5 bg-primary text-white text-center">
  <div class="container">
    <h1 class="display-5 fw-bold"><?= e(setting('site_name', SITE_NAME_DEFAULT)) ?></h1>
    <p class="lead"><?= e(setting('site_tagline', 'A complete PHP powered content management system')) ?></p>
    <a href="<?= BASE_URL ?>/contact.php" class="btn btn-light btn-lg mt-2">Get in Touch</a>
  </div>
</header>

<main class="container py-5">
  <?php if ($homePage): ?>
    <div class="mx-auto" style="max-width:800px;">
      <?= $homePage['content'] ?>
    </div>
  <?php else: ?>
    <p class="text-center text-muted">Welcome! Edit this homepage from the admin panel (Pages &rarr; Home).</p>
  <?php endif; ?>

  <?php if ($latestPosts): ?>
  <hr class="my-5">
  <h3 class="mb-4 text-center">Latest From Our Blog</h3>
  <div class="row g-4">
    <?php foreach ($latestPosts as $post): ?>
    <div class="col-md-4">
      <div class="card h-100 shadow-sm">
        <?php if ($post['featured_image']): ?>
          <img src="<?= BASE_URL.'/'.e($post['featured_image']) ?>" class="card-img-top" style="height:180px;object-fit:cover;">
        <?php endif; ?>
        <div class="card-body">
          <h5 class="card-title"><?= e($post['title']) ?></h5>
          <p class="card-text text-muted small"><?= e(excerpt($post['excerpt'] ?: $post['content'], 100)) ?></p>
          <a href="blog-single.php?slug=<?= e($post['slug']) ?>" class="btn btn-sm btn-outline-primary">Read More</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/site-footer.php'; ?>
