<?php
/**
 * admin/blog-edit.php
 * Edit an existing blog post, including category and tags.
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();
if (!$post) { header('Location: blogs.php'); exit; }

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$tagStmt = $pdo->prepare("SELECT t.name FROM tags t JOIN blog_post_tags bpt ON bpt.tag_id = t.id WHERE bpt.post_id = ?");
$tagStmt->execute([$id]);
$existingTags = implode(', ', array_column($tagStmt->fetchAll(), 'name'));

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $title = clean($_POST['title'] ?? '');
    $excerpt = clean($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $categoryId = (int)($_POST['category_id'] ?? 0) ?: null;
    $metaTitle = clean($_POST['meta_title'] ?? '');
    $metaDesc = clean($_POST['meta_description'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['published','draft']) ? $_POST['status'] : 'draft';
    $tagsInput = clean($_POST['tags'] ?? '');

    if ($title === '') {
        $error = 'Title is required.';
    } else {
        $slug = unique_slug($pdo, 'blog_posts', slugify($title), $id);

        $featuredImage = $post['featured_image'];
        if (!empty($_FILES['featured_image']['name'])) {
            $upload = handle_image_upload('featured_image', 'blog');
            if ($upload['success']) { $featuredImage = $upload['path']; }
            else { $error = $upload['message']; }
        }

        if (!$error) {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE blog_posts SET title=?, slug=?, excerpt=?, content=?, category_id=?, featured_image=?, meta_title=?, meta_description=?, status=? WHERE id=?");
            $stmt->execute([$title, $slug, $excerpt, $content, $categoryId, $featuredImage, $metaTitle, $metaDesc, $status, $id]);

            // Reset tags and re-link
            $pdo->prepare("DELETE FROM blog_post_tags WHERE post_id = ?")->execute([$id]);
            if ($tagsInput !== '') {
                foreach (array_filter(array_map('trim', explode(',', $tagsInput))) as $tagName) {
                    $tagSlug = slugify($tagName);
                    $tagStmt2 = $pdo->prepare("SELECT id FROM tags WHERE slug = ?");
                    $tagStmt2->execute([$tagSlug]);
                    $tag = $tagStmt2->fetch();
                    if (!$tag) {
                        $insertTag = $pdo->prepare("INSERT INTO tags (name, slug) VALUES (?,?)");
                        $insertTag->execute([$tagName, $tagSlug]);
                        $tagId = $pdo->lastInsertId();
                    } else { $tagId = $tag['id']; }
                    $link = $pdo->prepare("INSERT IGNORE INTO blog_post_tags (post_id, tag_id) VALUES (?,?)");
                    $link->execute([$id, $tagId]);
                }
            }

            $pdo->commit();
            log_activity($pdo, "Updated blog post: $title", 'blogs');
            header('Location: blogs.php');
            exit;
        }
    }
} else {
    $_POST = $post;
    $_POST['tags'] = $existingTags;
}

$pageTitle = 'Edit Blog Post';
include __DIR__ . '/includes/header.php';
?>

<form method="POST" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
          <div class="mb-3">
            <label class="form-label">Post Title *</label>
            <input type="text" name="title" class="form-control" required value="<?= e($_POST['title']) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Excerpt</label>
            <textarea name="excerpt" class="form-control" rows="2" maxlength="500"><?= e($_POST['excerpt']) ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" id="editor" rows="12"><?= $_POST['content'] ?></textarea>
          </div>
        </div>
      </div>
      <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>SEO Settings</strong></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Meta Title</label>
            <input type="text" name="meta_title" class="form-control" maxlength="200" value="<?= e($_POST['meta_title']) ?>">
          </div>
          <div class="mb-0">
            <label class="form-label">Meta Description</label>
            <textarea name="meta_description" class="form-control" rows="2" maxlength="300"><?= e($_POST['meta_description']) ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm mb-3">
        <div class="card-header bg-white"><strong>Publish</strong></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <?php foreach (['draft','published'] as $st): ?>
                <option value="<?= $st ?>" <?= $_POST['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary w-100">Update Post</button>
          <a href="blogs.php" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
        </div>
      </div>

      <div class="card shadow-sm mb-3">
        <div class="card-header bg-white"><strong>Category & Tags</strong></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select">
              <option value="">-- None --</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $post['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-0">
            <label class="form-label">Tags (comma separated)</label>
            <input type="text" name="tags" class="form-control" value="<?= e($_POST['tags']) ?>">
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>Featured Image</strong></div>
        <div class="card-body">
          <?php if (!empty($post['featured_image'])): ?>
            <img src="<?= BASE_URL . '/' . e($post['featured_image']) ?>" class="img-fluid rounded mb-2">
          <?php endif; ?>
          <input type="file" name="featured_image" class="form-control" accept="image/*">
          <small class="text-muted">Leave empty to keep current image.</small>
        </div>
      </div>
    </div>
  </div>
</form>

<script>tinymce.init({ selector: '#editor', height: 400, menubar: false, plugins: 'link image lists code table', toolbar: 'undo redo | bold italic underline | bullist numlist | link image table | code' });</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
