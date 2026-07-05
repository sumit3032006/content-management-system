<?php
/**
 * admin/pages.php
 * List all pages with search + pagination. Publish/unpublish & delete via AJAX.
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$search = clean($_GET['q'] ?? '');
$perPage = 10;
$currentPage = max(1, (int)($_GET['page'] ?? 1));

$where = '';
$params = [];
if ($search !== '') {
    $where = "WHERE title LIKE ? OR slug LIKE ?";
    $params = ["%$search%", "%$search%"];
}

$total = $pdo->prepare("SELECT COUNT(*) FROM pages $where");
$total->execute($params);
$totalRows = (int)$total->fetchColumn();
$p = paginate($totalRows, $perPage, $currentPage);

$stmt = $pdo->prepare("SELECT * FROM pages $where ORDER BY menu_order ASC, created_at DESC LIMIT $perPage OFFSET {$p['offset']}");
$stmt->execute($params);
$pages = $stmt->fetchAll();

$pageTitle = 'Pages';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <form class="d-flex" method="GET">
    <input type="text" name="q" class="form-control form-control-sm me-2" placeholder="Search pages..." value="<?= e($search) ?>">
    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
  </form>
  <a href="page-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> New Page</a>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead class="table-light">
        <tr><th>Title</th><th>Slug</th><th>Status</th><th>In Menu</th><th>Updated</th><th class="text-end">Actions</th></tr>
      </thead>
      <tbody id="pagesTable">
        <?php if (!$pages): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No pages found.</td></tr>
        <?php endif; ?>
        <?php foreach ($pages as $row): ?>
        <tr id="page-row-<?= $row['id'] ?>">
          <td><?= e($row['title']) ?></td>
          <td><code>/<?= e($row['slug']) ?></code></td>
          <td>
            <span class="badge status-badge <?= $row['status'] === 'published' ? 'bg-success' : 'bg-secondary' ?>" data-id="<?= $row['id'] ?>" style="cursor:pointer" title="Click to toggle">
              <?= ucfirst($row['status']) ?>
            </span>
          </td>
          <td><?= $row['show_in_menu'] ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-dash-circle text-muted"></i>' ?></td>
          <td><?= format_date($row['updated_at'], 'd M Y') ?></td>
          <td class="text-end">
            <a href="page-edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
            <button class="btn btn-sm btn-outline-danger btn-delete-page" data-id="<?= $row['id'] ?>"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3"><?= render_pagination($p['totalPages'], $p['currentPage'], 'pages.php?q=' . urlencode($search)) ?></div>

<script>
$(function(){
  // Toggle publish status via AJAX
  $('.status-badge').on('click', function(){
    const badge = $(this);
    const id = badge.data('id');
    $.post('ajax/toggle_page_status.php', {id: id, csrf_token: '<?= csrf_token() ?>'}, function(res){
      if (res.success) {
        badge.text(res.status.charAt(0).toUpperCase() + res.status.slice(1));
        badge.toggleClass('bg-success', res.status === 'published');
        badge.toggleClass('bg-secondary', res.status !== 'published');
      } else {
        alert(res.message || 'Failed to update status.');
      }
    }, 'json');
  });

  // Delete page via AJAX
  $('.btn-delete-page').on('click', function(){
    if (!confirm('Delete this page permanently?')) return;
    const id = $(this).data('id');
    $.post('ajax/delete_page.php', {id: id, csrf_token: '<?= csrf_token() ?>'}, function(res){
      if (res.success) { $('#page-row-'+id).fadeOut(200, function(){ $(this).remove(); }); }
      else { alert(res.message || 'Failed to delete.'); }
    }, 'json');
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
