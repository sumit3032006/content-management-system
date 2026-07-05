<?php
/**
 * admin/includes/sidebar.php
 * Professional collapsible sidebar. Highlights the active menu item
 * based on the current script name.
 */
$current = basename($_SERVER['PHP_SELF']);

function nav_active($files, $current) {
    $files = (array)$files;
    return in_array($current, $files) ? 'active' : '';
}
?>
<aside class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-brand">
    <i class="bi bi-layers-fill"></i>
    <span><?= e(setting('site_name', SITE_NAME_DEFAULT)) ?></span>
  </div>

  <ul class="sidebar-menu">
    <li class="<?= nav_active('dashboard.php', $current) ?>">
      <a href="dashboard.php"><i class="bi bi-speedometer2"></i> <span>Dashboard</span></a>
    </li>
    <li class="<?= nav_active(['pages.php','page-add.php','page-edit.php'], $current) ?>">
      <a href="pages.php"><i class="bi bi-file-earmark-text"></i> <span>Pages</span></a>
    </li>
    <li class="<?= nav_active(['blogs.php','blog-add.php','blog-edit.php'], $current) ?>">
      <a href="blogs.php"><i class="bi bi-journal-text"></i> <span>Blog Posts</span></a>
    </li>
    <li class="<?= nav_active('categories.php', $current) ?>">
      <a href="categories.php"><i class="bi bi-tags"></i> <span>Categories &amp; Tags</span></a>
    </li>
    <li class="<?= nav_active('images.php', $current) ?>">
      <a href="images.php"><i class="bi bi-images"></i> <span>Image Manager</span></a>
    </li>
    <li class="<?= nav_active('messages.php', $current) ?>">
      <a href="messages.php"><i class="bi bi-envelope"></i> <span>Messages</span></a>
    </li>
    <?php if (is_admin()): ?>
    <li class="<?= nav_active(['users.php','user-add.php','user-edit.php'], $current) ?>">
      <a href="users.php"><i class="bi bi-people"></i> <span>Users</span></a>
    </li>
    <li class="<?= nav_active('settings.php', $current) ?>">
      <a href="settings.php"><i class="bi bi-gear"></i> <span>Settings</span></a>
    </li>
    <?php endif; ?>
    <li class="<?= nav_active('profile.php', $current) ?>">
      <a href="profile.php"><i class="bi bi-person"></i> <span>Profile</span></a>
    </li>
    <li>
      <a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a>
    </li>
  </ul>
</aside>
