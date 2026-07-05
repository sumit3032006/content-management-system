<?php
/**
 * blog-single.php
 * Displays a single published blog post with its tags, category, and
 * increments the view counter once per page load.
 */
require_once __DIR__ . '/includes/functions.php';

$slug = clean($_GET['slug'] ?? '');
$stmt = $pdo->prepare("
  SELECT b.*, c.name AS category_name, c.slug AS category_slug
  FROM blog_posts b LEFT JOIN categories c ON c.id = b.category_id
  WHERE b.slug = ? AND b.status = 'published' LIMIT 1
");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Post Not Found';
    include __DIR__ . '/includes/site-header.php';
    echo '<main class="container py-5 text-center"><h1 class="display-4">404</h1><p class="text-muted">This blog post does not exist or is unpublished.</p><a href="' . BASE_URL . '/blog.php" class="btn btn-primary">Back to Blog</a></main>';
    include __DIR__ . '/includes/site-footer.php';
    exit;
}

// Increment view count (simple counter, no dedup needed for a portfolio project)
$pdo->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?")->execute([$post['id']]);

// Fetch tags
$tagStmt = $pdo->prepare("SELECT t.name, t.slug FROM tags t JOIN blog_post_tags bpt ON bpt.tag_id = t.id WHERE bpt.post_id = ?");
$tagStmt->execute([$post['id']]);
$tags = $tagStmt->fetchAll();

// Related posts (same category)
$related = [];
if ($post['category_id']) {
    $r = $pdo->prepare("SELECT title, slug, featured_image FROM blog_posts WHERE category_id = ? AND id != ? AND status='published' LIMIT 3");
    $r->execute([$post['category_id'], $post['id']]);
    $related = $r->fetchAll();
}

$pageTitle = $post['meta_title'] ?: $post['title'];
$metaDescription = $post['meta_description'] ?: excerpt($post['content'], 150);
include __DIR__ . '/includes/site-header.php';
?>

<header class="py-5 bg-light text-center border-bottom">
  <div class="container">
    <?php if ($post['category_name']): ?><span class="badge bg-primary mb-2"><?= e($post['category_name']) ?></span><?php endif; ?>
    <h1 class="fw-bold"><?= e($post['title']) ?></h1>
    <p class="text-muted"><?= format_date($post['created_at'], 'd M Y') ?> &bull; <?= (int)$post['views'] ?> views</p>
  </div>
</header>

<main class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <?php if ($post['featured_image']): ?>
        <img src="<?= BASE_URL.'/'.e($post['featured_image']) ?>" class="img-fluid rounded mb-4" alt="<?= e($post['title']) ?>">
      <?php endif; ?>

      <div class="blog-content"><?= $post['content'] ?></div>

      <?php if ($tags): ?>
      <div class="mt-4">
        <?php foreach ($tags as $tag): ?>
          <span class="badge bg-light text-dark border me-1">#<?= e($tag['name']) ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if ($related): ?>
      <hr class="my-5">
      <h5 class="mb-3">Related Posts</h5>
      <div class="row g-3">
        <?php foreach ($related as $rp): ?>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <?php if ($rp['featured_image']): ?><img src="<?= BASE_URL.'/'.e($rp['featured_image']) ?>" class="card-img-top" style="height:120px;object-fit:cover;"><?php endif; ?>
            <div class="card-body">
              <a href="blog-single.php?slug=<?= e($rp['slug']) ?>" class="text-decoration-none text-dark small fw-semibold"><?= e($rp['title']) ?></a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <a href="blog.php" class="btn btn-outline-secondary mt-4"><i class="bi bi-arrow-left"></i> Back to Blog</a>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/site-footer.php'; ?>
