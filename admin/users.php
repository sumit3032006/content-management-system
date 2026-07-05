<?php
/**
 * admin/users.php
 * Admin-only user management: list all users, quick delete/status actions.
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin(); // only 'admin' role can access

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'User Management';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h6 class="mb-0 text-muted">Manage admin and editor accounts</h6>
  <a href="user-add.php" class="btn btn-primary btn-sm"><i class="bi bi-person-plus"></i> Add User</a>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead class="table-light">
        <tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th class="text-end">Actions</th></tr>
      </thead>
      <tbody id="usersTable">
        <?php foreach ($users as $u): ?>
        <tr id="user-row-<?= $u['id'] ?>">
          <td><?= e($u['name']) ?></td>
          <td><?= e($u['username']) ?></td>
          <td><?= e($u['email']) ?></td>
          <td><span class="badge <?= $u['role']==='admin' ? 'bg-danger':'bg-info' ?>"><?= ucfirst($u['role']) ?></span></td>
          <td><span class="badge <?= $u['status']==='active' ? 'bg-success':'bg-secondary' ?>"><?= ucfirst($u['status']) ?></span></td>
          <td class="text-end">
            <a href="user-edit.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
            <?php if ($u['id'] != $_SESSION['user_id']): ?>
            <button class="btn btn-sm btn-outline-danger btn-delete-user" data-id="<?= $u['id'] ?>"><i class="bi bi-trash"></i></button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
$(function(){
  $('.btn-delete-user').on('click', function(){
    if (!confirm('Delete this user account?')) return;
    const id = $(this).data('id');
    $.post('ajax/delete_user.php', {id:id, csrf_token:'<?= csrf_token() ?>'}, function(res){
      if (res.success) { $('#user-row-'+id).fadeOut(200, function(){ $(this).remove(); }); }
      else { alert(res.message || 'Failed to delete.'); }
    }, 'json');
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
