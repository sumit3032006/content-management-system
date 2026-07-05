<?php
/**
 * admin/categories.php
 * Manage blog categories and tags (add / delete) on one screen.
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'add_category') {
        $name = clean($_POST['name'] ?? '');
        if ($name !== '') {
            $slug = unique_slug($pdo, 'categories', slugify($name));
            $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?,?)")->execute([$name, $slug]);
            log_activity($pdo, "Added category: $name", 'categories');
        }
    } elseif ($action === 'delete_category') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        log_activity($pdo, "Deleted category #$id", 'categories');
    } elseif ($action === 'add_tag') {
        $name = clean($_POST['name'] ?? '');
        if ($name !== '') {
            $slug = unique_slug($pdo, 'tags', slugify($name));
            $pdo->prepare("INSERT INTO tags (name, slug) VALUES (?,?)")->execute([$name, $slug]);
            log_activity($pdo, "Added tag: $name", 'tags');
        }
    } elseif ($action === 'delete_tag') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM tags WHERE id = ?")->execute([$id]);
        log_activity($pdo, "Deleted tag #$id", 'tags');
    }
    header('Location: categories.php');
    exit;
}

$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM blog_posts WHERE category_id = c.id) AS post_count FROM categories c ORDER BY c.name")->fetchAll();
$tags = $pdo->query("SELECT t.*, (SELECT COUNT(*) FROM blog_post_tags WHERE tag_id = t.id) AS use_count FROM tags t ORDER BY t.name")->fetchAll();

$pageTitle = 'Categories & Tags';
include __DIR__ . '/includes/header.php';
?>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-header bg-white"><strong>Categories</strong></div>
      <div class="card-body">
        <form method="POST" class="d-flex mb-3">
          <input type="hidden" name="action" value="add_category">
          <?= csrf_field() ?>
          <input type="text" name="name" class="form-control me-2" placeholder="New category name" required>
          <button class="btn btn-primary"><i class="bi bi-plus-lg"></i></button>
        </form>
        <ul class="list-group">
          <?php foreach ($categories as $cat): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= e($cat['name']) ?> <span class="badge bg-light text-dark"><?= $cat['post_count'] ?> posts</span>
            <form method="POST" onsubmit="return confirm('Delete this category?');">
              <input type="hidden" name="action" value="delete_category">
              <input type="hidden" name="id" value="<?= $cat['id'] ?>">
              <?= csrf_field() ?>
              <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </li>
          <?php endforeach; ?>
          <?php if (!$categories): ?><li class="list-group-item text-muted">No categories yet.</li><?php endif; ?>
        </ul>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-header bg-white"><strong>Tags</strong></div>
      <div class="card-body">
        <form method="POST" class="d-flex mb-3">
          <input type="hidden" name="action" value="add_tag">
          <?= csrf_field() ?>
          <input type="text" name="name" class="form-control me-2" placeholder="New tag name" required>
          <button class="btn btn-primary"><i class="bi bi-plus-lg"></i></button>
        </form>
        <div class="d-flex flex-wrap gap-2">
          <?php foreach ($tags as $tag): ?>
          <form method="POST" onsubmit="return confirm('Delete this tag?');" class="d-inline">
            <input type="hidden" name="action" value="delete_tag">
            <input type="hidden" name="id" value="<?= $tag['id'] ?>">
            <?= csrf_field() ?>
            <button class="btn btn-sm btn-outline-secondary">
              <?= e($tag['name']) ?> (<?= $tag['use_count'] ?>) <i class="bi bi-x"></i>
            </button>
          </form>
          <?php endforeach; ?>
          <?php if (!$tags): ?><span class="text-muted">No tags yet.</span><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
