<?php
/**
 * blog.php
 * Public blog listing: search, category filter, pagination.
 */
require_once __DIR__ . '/includes/functions.php';

$search = clean($_GET['q'] ?? '');
$categorySlug = clean($_GET['category'] ?? '');
$perPage = 6;
$currentPage = max(1, (int)($_GET['page'] ?? 1));

$where = ["b.status = 'published'"];
$params = [];

if ($search !== '') {
    $where[] = "(b.title LIKE ? OR b.excerpt LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}
if ($categorySlug !== '') {
    $where[] = "c.slug = ?";
    $params[] = $categorySlug;
}
$whereSql = 'WHERE ' . implode(' AND ', $where);

$total = $pdo->prepare("SELECT COUNT(*) FROM blog_posts b LEFT JOIN categories c ON c.id=b.category_id $whereSql");
$total->execute($params);
$totalRows = (int)$total->fetchColumn();
$p = paginate($totalRows, $perPage, $currentPage);

$stmt = $pdo->prepare("
  SELECT b.*, c.name AS category_name, c.slug AS category_slug
  FROM blog_posts b LEFT JOIN categories c ON c.id = b.category_id
  $whereSql ORDER BY b.created_at DESC LIMIT $perPage OFFSET {$p['offset']}
");
$stmt->execute($params);
$posts = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$pageTitle = 'Blog - ' . setting('site_name', SITE_NAME_DEFAULT);
include __DIR__ . '/includes/site-header.php';
?>

<header class="py-5 bg-light text-center border-bottom">
  <div class="container"><h1 class="fw-bold">Our Blog</h1></div>
</header>

<main class="container py-5">
  <div class="row">
    <div class="col-lg-8">
      <form class="d-flex mb-4" method="GET">
        <input type="text" name="q" class="form-control me-2" placeholder="Search articles..." value="<?= e($search) ?>">
        <button class="btn btn-primary"><i class="bi bi-search"></i></button>
      </form>

      <?php if (!$posts): ?>
        <p class="text-muted">No blog posts found.</p>
      <?php endif; ?>

      <?php foreach ($posts as $post): ?>
      <div class="card mb-4 shadow-sm">
        <div class="row g-0">
          <?php if ($post['featured_image']): ?>
          <div class="col-md-4">
            <img src="<?= BASE_URL.'/'.e($post['featured_image']) ?>" class="img-fluid rounded-start h-100" style="object-fit:cover;">
          </div>
          <?php endif; ?>
          <div class="col-md-<?= $post['featured_image'] ? '8' : '12' ?>">
            <div class="card-body">
              <?php if ($post['category_name']): ?><span class="badge bg-primary mb-2"><?= e($post['category_name']) ?></span><?php endif; ?>
              <h5 class="card-title"><a href="blog-single.php?slug=<?= e($post['slug']) ?>" class="text-decoration-none text-dark"><?= e($post['title']) ?></a></h5>
              <p class="card-text text-muted"><?= e(excerpt($post['excerpt'] ?: $post['content'], 140)) ?></p>
              <p class="card-text"><small class="text-muted"><?= format_date($post['created_at']) ?> &bull; <?= (int)$post['views'] ?> views</small></p>
              <a href="blog-single.php?slug=<?= e($post['slug']) ?>" class="btn btn-sm btn-outline-primary">Read More</a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

      <?= render_pagination($p['totalPages'], $p['currentPage'], 'blog.php?q=' . urlencode($search) . '&category=' . urlencode($categorySlug)) ?>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>Categories</strong></div>
        <ul class="list-group list-group-flush">
          <li class="list-group-item"><a class="text-decoration-none" href="blog.php">All Posts</a></li>
          <?php foreach ($categories as $cat): ?>
            <li class="list-group-item"><a class="text-decoration-none" href="blog.php?category=<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/site-footer.php'; ?>
