<?php
/**
 * admin/blog-add.php
 * Create a new blog post with category, tags (comma separated), featured image.
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
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
        $slug = unique_slug($pdo, 'blog_posts', slugify($title));

        $featuredImage = null;
        if (!empty($_FILES['featured_image']['name'])) {
            $upload = handle_image_upload('featured_image', 'blog');
            if ($upload['success']) { $featuredImage = $upload['path']; }
            else { $error = $upload['message']; }
        }

        if (!$error) {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, excerpt, content, category_id, featured_image, meta_title, meta_description, status, created_by) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$title, $slug, $excerpt, $content, $categoryId, $featuredImage, $metaTitle, $metaDesc, $status, $_SESSION['user_id']]);
            $postId = $pdo->lastInsertId();

            // Handle tags: create if not exist, then link
            if ($tagsInput !== '') {
                foreach (array_filter(array_map('trim', explode(',', $tagsInput))) as $tagName) {
                    $tagSlug = slugify($tagName);
                    $tagStmt = $pdo->prepare("SELECT id FROM tags WHERE slug = ?");
                    $tagStmt->execute([$tagSlug]);
                    $tag = $tagStmt->fetch();
                    if (!$tag) {
                        $insertTag = $pdo->prepare("INSERT INTO tags (name, slug) VALUES (?,?)");
                        $insertTag->execute([$tagName, $tagSlug]);
                        $tagId = $pdo->lastInsertId();
                    } else {
                        $tagId = $tag['id'];
                    }
                    $link = $pdo->prepare("INSERT IGNORE INTO blog_post_tags (post_id, tag_id) VALUES (?,?)");
                    $link->execute([$postId, $tagId]);
                }
            }

            $pdo->commit();
            log_activity($pdo, "Created blog post: $title", 'blogs');
            header('Location: blogs.php');
            exit;
        }
    }
}

$pageTitle = 'Add Blog Post';
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
            <input type="text" name="title" class="form-control" required value="<?= e($_POST['title'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Excerpt (short summary)</label>
            <textarea name="excerpt" class="form-control" rows="2" maxlength="500"><?= e($_POST['excerpt'] ?? '') ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" id="editor" rows="12"><?= $_POST['content'] ?? '' ?></textarea>
          </div>
        </div>
      </div>
      <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>SEO Settings</strong></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Meta Title</label>
            <input type="text" name="meta_title" class="form-control" maxlength="200" value="<?= e($_POST['meta_title'] ?? '') ?>">
          </div>
          <div class="mb-0">
            <label class="form-label">Meta Description</label>
            <textarea name="meta_description" class="form-control" rows="2" maxlength="300"><?= e($_POST['meta_description'] ?? '') ?></textarea>
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
              <option value="draft">Draft</option>
              <option value="published">Published</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary w-100">Save Post</button>
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
                <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-0">
            <label class="form-label">Tags (comma separated)</label>
            <input type="text" name="tags" class="form-control" placeholder="php, mysql, tips" value="<?= e($_POST['tags'] ?? '') ?>">
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>Featured Image</strong></div>
        <div class="card-body">
          <input type="file" name="featured_image" class="form-control" accept="image/*">
          <small class="text-muted">JPG, PNG, GIF, WEBP. Max 3MB.</small>
        </div>
      </div>
    </div>
  </div>
</form>

<script>tinymce.init({ selector: '#editor', height: 400, menubar: false, plugins: 'link image lists code table', toolbar: 'undo redo | bold italic underline | bullist numlist | link image table | code' });</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
