<?php
/**
 * admin/images.php
 * Central media library: upload, preview and delete images.
 * Validates type and size server-side (see handle_image_upload()).
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $upload = handle_image_upload('image_file', 'media');
    if ($upload['success']) {
        $stmt = $pdo->prepare("INSERT INTO media (file_name, file_path, file_type, file_size, uploaded_by) VALUES (?,?,?,?,?)");
        $stmt->execute([$upload['file_name'], $upload['path'], $upload['type'], $upload['size'], $_SESSION['user_id']]);
        log_activity($pdo, "Uploaded image: {$upload['file_name']}", 'media');
        $success = 'Image uploaded successfully.';
    } else {
        $error = $upload['message'];
    }
}

$perPage = 12;
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$totalRows = (int)$pdo->query("SELECT COUNT(*) FROM media")->fetchColumn();
$p = paginate($totalRows, $perPage, $currentPage);
$stmt = $pdo->prepare("SELECT m.*, u.name AS uploader FROM media m LEFT JOIN users u ON u.id = m.uploaded_by ORDER BY m.created_at DESC LIMIT $perPage OFFSET {$p['offset']}");
$stmt->execute();
$images = $stmt->fetchAll();

$pageTitle = 'Image Manager';
include __DIR__ . '/includes/header.php';
?>

<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <form method="POST" enctype="multipart/form-data" class="row g-2 align-items-center">
      <?= csrf_field() ?>
      <div class="col-sm-8">
        <input type="file" name="image_file" class="form-control" accept="image/*" required>
        <small class="text-muted">Allowed: JPG, PNG, GIF, WEBP. Max size 3MB.</small>
      </div>
      <div class="col-sm-4">
        <button class="btn btn-primary w-100"><i class="bi bi-upload"></i> Upload</button>
      </div>
    </form>
  </div>
</div>

<div class="row g-3" id="mediaGrid">
  <?php if (!$images): ?><p class="text-muted">No images uploaded yet.</p><?php endif; ?>
  <?php foreach ($images as $img): ?>
  <div class="col-6 col-md-4 col-lg-2" id="media-<?= $img['id'] ?>">
    <div class="card h-100">
      <img src="<?= BASE_URL . '/' . e($img['file_path']) ?>" class="card-img-top" style="height:120px;object-fit:cover;" onclick="window.open(this.src)">
      <div class="card-body p-2">
        <small class="text-muted d-block text-truncate"><?= e($img['file_name']) ?></small>
        <small class="text-muted"><?= round($img['file_size']/1024) ?> KB</small>
        <div class="d-flex justify-content-between mt-2">
          <button class="btn btn-sm btn-outline-secondary" onclick="copyPath('<?= e(BASE_URL . '/' . $img['file_path']) ?>')" title="Copy URL"><i class="bi bi-clipboard"></i></button>
          <button class="btn btn-sm btn-outline-danger btn-delete-image" data-id="<?= $img['id'] ?>"><i class="bi bi-trash"></i></button>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="mt-3"><?= render_pagination($p['totalPages'], $p['currentPage'], 'images.php') ?></div>

<script>
function copyPath(path) {
  navigator.clipboard.writeText(path).then(()=> alert('Image URL copied!'));
}
$(function(){
  $('.btn-delete-image').on('click', function(){
    if (!confirm('Delete this image permanently? This cannot be undone.')) return;
    const id = $(this).data('id');
    $.post('ajax/delete_image.php', {id:id, csrf_token:'<?= csrf_token() ?>'}, function(res){
      if (res.success) { $('#media-'+id).fadeOut(200, function(){ $(this).remove(); }); }
      else { alert(res.message || 'Failed to delete.'); }
    }, 'json');
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
