<?php
/**
 * admin/blogs.php
 * List blog posts with search, category filter and pagination.
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$search = clean($_GET['q'] ?? '');
$perPage = 10;
$currentPage = max(1, (int)($_GET['page'] ?? 1));

$where = [];
$params = [];
if ($search !== '') {
    $where[] = "(b.title LIKE ? OR b.slug LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total = $pdo->prepare("SELECT COUNT(*) FROM blog_posts b $whereSql");
$total->execute($params);
$totalRows = (int)$total->fetchColumn();
$p = paginate($totalRows, $perPage, $currentPage);

$stmt = $pdo->prepare("
    SELECT b.*, c.name AS category_name
    FROM blog_posts b
    LEFT JOIN categories c ON c.id = b.category_id
    $whereSql
    ORDER BY b.created_at DESC LIMIT $perPage OFFSET {$p['offset']}
");
$stmt->execute($params);
$posts = $stmt->fetchAll();

$pageTitle = 'Blog Posts';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <form class="d-flex" method="GET">
    <input type="text" name="q" class="form-control form-control-sm me-2" placeholder="Search posts..." value="<?= e($search) ?>">
    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
  </form>
  <a href="blog-add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> New Post</a>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead class="table-light">
        <tr><th>Image</th><th>Title</th><th>Category</th><th>Status</th><th>Views</th><th>Date</th><th class="text-end">Actions</th></tr>
      </thead>
      <tbody id="blogsTable">
        <?php if (!$posts): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No blog posts found.</td></tr>
        <?php endif; ?>
        <?php foreach ($posts as $row): ?>
        <tr id="blog-row-<?= $row['id'] ?>">
          <td>
            <?php if ($row['featured_image']): ?>
              <img src="<?= BASE_URL . '/' . e($row['featured_image']) ?>" style="width:50px;height:50px;object-fit:cover;border-radius:6px;">
            <?php else: ?>
              <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:50px;height:50px;"><i class="bi bi-image text-muted"></i></div>
            <?php endif; ?>
          </td>
          <td><?= e($row['title']) ?></td>
          <td><?= e($row['category_name'] ?? '-') ?></td>
          <td>
            <span class="badge status-badge <?= $row['status'] === 'published' ? 'bg-success' : 'bg-secondary' ?>" data-id="<?= $row['id'] ?>" style="cursor:pointer" title="Click to toggle">
              <?= ucfirst($row['status']) ?>
            </span>
          </td>
          <td><?= (int)$row['views'] ?></td>
          <td><?= format_date($row['created_at']) ?></td>
          <td class="text-end">
            <a href="blog-edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
            <button class="btn btn-sm btn-outline-danger btn-delete-blog" data-id="<?= $row['id'] ?>"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3"><?= render_pagination($p['totalPages'], $p['currentPage'], 'blogs.php?q=' . urlencode($search)) ?></div>

<script>
$(function(){
  $('.status-badge').on('click', function(){
    const badge = $(this); const id = badge.data('id');
    $.post('ajax/toggle_blog_status.php', {id:id, csrf_token:'<?= csrf_token() ?>'}, function(res){
      if (res.success) {
        badge.text(res.status.charAt(0).toUpperCase() + res.status.slice(1));
        badge.toggleClass('bg-success', res.status === 'published');
        badge.toggleClass('bg-secondary', res.status !== 'published');
      } else { alert(res.message || 'Failed.'); }
    }, 'json');
  });

  $('.btn-delete-blog').on('click', function(){
    if (!confirm('Delete this blog post permanently?')) return;
    const id = $(this).data('id');
    $.post('ajax/delete_blog.php', {id:id, csrf_token:'<?= csrf_token() ?>'}, function(res){
      if (res.success) { $('#blog-row-'+id).fadeOut(200, function(){ $(this).remove(); }); }
      else { alert(res.message || 'Failed to delete.'); }
    }, 'json');
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
