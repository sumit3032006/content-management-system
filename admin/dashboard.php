<?php
/**
 * admin/dashboard.php
 * Overview stats + quick actions + recent activity feed.
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$counts = [
    'pages'    => $pdo->query("SELECT COUNT(*) FROM pages")->fetchColumn(),
    'blogs'    => $pdo->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn(),
    'images'   => $pdo->query("SELECT COUNT(*) FROM media")->fetchColumn(),
    'messages' => $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn(),
    'users'    => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'unread'   => $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn(),
];

$recentActivity = $pdo->query("
    SELECT a.action, a.module, a.created_at, u.name AS user_name
    FROM activity_logs a
    LEFT JOIN users u ON u.id = a.user_id
    ORDER BY a.created_at DESC LIMIT 10
")->fetchAll();

$pageTitle = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-4 col-xl-2">
    <a href="pages.php" class="stat-card bg-primary">
      <i class="bi bi-file-earmark-text"></i>
      <h3><?= (int)$counts['pages'] ?></h3><p>Pages</p>
    </a>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <a href="blogs.php" class="stat-card bg-success">
      <i class="bi bi-journal-text"></i>
      <h3><?= (int)$counts['blogs'] ?></h3><p>Blog Posts</p>
    </a>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <a href="images.php" class="stat-card bg-info">
      <i class="bi bi-images"></i>
      <h3><?= (int)$counts['images'] ?></h3><p>Images</p>
    </a>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <a href="messages.php" class="stat-card bg-warning">
      <i class="bi bi-envelope"></i>
      <h3><?= (int)$counts['messages'] ?></h3><p>Messages <?php if($counts['unread']>0):?><span class="badge bg-danger"><?=$counts['unread']?> new</span><?php endif;?></p>
    </a>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <a href="<?= is_admin() ? 'users.php' : '#' ?>" class="stat-card bg-danger">
      <i class="bi bi-people"></i>
      <h3><?= (int)$counts['users'] ?></h3><p>Users</p>
    </a>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <a href="<?= BASE_URL ?>/index.php" target="_blank" class="stat-card bg-secondary">
      <i class="bi bi-globe"></i>
      <h3>View</h3><p>Live Site</p>
    </a>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-12">
    <h6 class="text-muted mb-2">Quick Actions</h6>
    <div class="d-flex flex-wrap gap-2">
      <a href="page-add.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-plus-lg"></i> New Page</a>
      <a href="blog-add.php" class="btn btn-outline-success btn-sm"><i class="bi bi-plus-lg"></i> New Blog Post</a>
      <a href="images.php" class="btn btn-outline-info btn-sm"><i class="bi bi-upload"></i> Upload Image</a>
      <?php if (is_admin()): ?>
      <a href="user-add.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-person-plus"></i> Add User</a>
      <a href="settings.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-gear"></i> Settings</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header bg-white"><strong>Recent Activity</strong></div>
  <div class="table-responsive">
    <table class="table mb-0 align-middle">
      <thead class="table-light"><tr><th>User</th><th>Action</th><th>Module</th><th>When</th></tr></thead>
      <tbody>
        <?php if (!$recentActivity): ?>
          <tr><td colspan="4" class="text-center text-muted py-3">No activity recorded yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($recentActivity as $log): ?>
        <tr>
          <td><?= e($log['user_name'] ?? 'System') ?></td>
          <td><?= e($log['action']) ?></td>
          <td><span class="badge bg-light text-dark"><?= e($log['module'] ?? '-') ?></span></td>
          <td><?= format_date($log['created_at'], 'd M Y, h:i A') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
