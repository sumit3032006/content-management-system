<?php
/**
 * admin/page-add.php
 * Create a new page with SEO fields, rich text content, and optional featured image.
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $title = clean($_POST['title'] ?? '');
    $content = $_POST['content'] ?? ''; // rich text, not stripped
    $metaTitle = clean($_POST['meta_title'] ?? '');
    $metaDesc = clean($_POST['meta_description'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['published','unpublished','draft']) ? $_POST['status'] : 'draft';
    $showInMenu = isset($_POST['show_in_menu']) ? 1 : 0;
    $menuOrder = (int)($_POST['menu_order'] ?? 0);

    if ($title === '') {
        $error = 'Title is required.';
    } else {
        $slugInput = clean($_POST['slug'] ?? '') ?: $title;
        $slug = unique_slug($pdo, 'pages', slugify($slugInput));

        $featuredImage = null;
        if (!empty($_FILES['featured_image']['name'])) {
            $upload = handle_image_upload('featured_image', 'pages');
            if ($upload['success']) {
                $featuredImage = $upload['path'];
            } else {
                $error = $upload['message'];
            }
        }

        if (!$error) {
            $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, meta_title, meta_description, featured_image, status, show_in_menu, menu_order, created_by) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$title, $slug, $content, $metaTitle, $metaDesc, $featuredImage, $status, $showInMenu, $menuOrder, $_SESSION['user_id']]);
            log_activity($pdo, "Created page: $title", 'pages');
            header('Location: pages.php');
            exit;
        }
    }
}

$pageTitle = 'Add New Page';
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
            <label class="form-label">Page Title *</label>
            <input type="text" name="title" class="form-control" required value="<?= e($_POST['title'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Slug (URL) <small class="text-muted">(leave blank to auto-generate)</small></label>
            <input type="text" name="slug" class="form-control" value="<?= e($_POST['slug'] ?? '') ?>">
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
              <option value="unpublished">Unpublished</option>
            </select>
          </div>
          <div class="form-check mb-3">
            <input type="checkbox" name="show_in_menu" class="form-check-input" id="showInMenu">
            <label class="form-check-label" for="showInMenu">Show in navigation menu</label>
          </div>
          <div class="mb-3">
            <label class="form-label">Menu Order</label>
            <input type="number" name="menu_order" class="form-control" value="0">
          </div>
          <button type="submit" class="btn btn-primary w-100">Save Page</button>
          <a href="pages.php" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
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
