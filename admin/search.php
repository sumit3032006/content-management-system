<?php
/**
 * admin/search.php
 * Global search across pages, blog posts, and contact messages.
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$q = clean($_GET['q'] ?? '');
$pages = $posts = $messages = [];

if ($q !== '') {
    $like = "%$q%";

    $stmt = $pdo->prepare("SELECT id, title, slug, status FROM pages WHERE title LIKE ? OR slug LIKE ? LIMIT 20");
    $stmt->execute([$like, $like]);
    $pages = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT id, title, slug, status FROM blog_posts WHERE title LIKE ? OR slug LIKE ? LIMIT 20");
    $stmt->execute([$like, $like]);
    $posts = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT id, name, email, subject FROM contact_messages WHERE name LIKE ? OR email LIKE ? OR subject LIKE ? LIMIT 20");
    $stmt->execute([$like, $like, $like]);
    $messages = $stmt->fetchAll();
}

$pageTitle = 'Global Search';
include __DIR__ . '/includes/header.php';
?>

<form class="mb-4" method="GET">
  <div class="input-group">
    <input type="text" name="q" class="form-control" placeholder="Search pages, posts, messages..." value="<?= e($q) ?>" autofocus>
    <button class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
  </div>
</form>

<?php if ($q !== ''): ?>
<div class="row g-3">
  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white"><strong>Pages</strong> (<?= count($pages) ?>)</div>
      <ul class="list-group list-group-flush">
        <?php foreach ($pages as $r): ?>
          <li class="list-group-item"><a href="page-edit.php?id=<?= $r['id'] ?>"><?= e($r['title']) ?></a> <span class="badge bg-light text-dark float-end"><?= e($r['status']) ?></span></li>
        <?php endforeach; ?>
        <?php if (!$pages): ?><li class="list-group-item text-muted">No matches.</li><?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white"><strong>Blog Posts</strong> (<?= count($posts) ?>)</div>
      <ul class="list-group list-group-flush">
        <?php foreach ($posts as $r): ?>
          <li class="list-group-item"><a href="blog-edit.php?id=<?= $r['id'] ?>"><?= e($r['title']) ?></a> <span class="badge bg-light text-dark float-end"><?= e($r['status']) ?></span></li>
        <?php endforeach; ?>
        <?php if (!$posts): ?><li class="list-group-item text-muted">No matches.</li><?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white"><strong>Messages</strong> (<?= count($messages) ?>)</div>
      <ul class="list-group list-group-flush">
        <?php foreach ($messages as $r): ?>
          <li class="list-group-item"><a href="messages.php"><?= e($r['name']) ?></a> — <?= e($r['subject'] ?: 'No subject') ?></li>
        <?php endforeach; ?>
        <?php if (!$messages): ?><li class="list-group-item text-muted">No matches.</li><?php endif; ?>
      </ul>
    </div>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
