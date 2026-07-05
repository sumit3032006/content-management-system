<?php
/**
 * admin/user-edit.php
 * Admin edits a user's details, role, status, or resets their password.
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) { header('Location: users.php'); exit; }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $name = clean($_POST['name'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $role = in_array($_POST['role'] ?? '', ['admin','editor']) ? $_POST['role'] : 'editor';
    $status = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';
    $newPassword = $_POST['new_password'] ?? '';

    if (!$name || !$email) {
        $error = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Prevent admin locking themselves out
        if ($id == $_SESSION['user_id'] && $role !== 'admin') {
            $error = "You can't remove your own admin role.";
        } else {
            if ($newPassword !== '') {
                if (strlen($newPassword) < 8) {
                    $error = 'New password must be at least 8 characters.';
                } else {
                    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $id]);
                }
            }
            if (!$error) {
                $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=?, status=? WHERE id=?");
                $stmt->execute([$name, $email, $role, $status, $id]);
                log_activity($pdo, "Updated user: {$user['username']}", 'users');
                $success = 'User updated successfully.';
                $user = array_merge($user, compact('name','email','role','status'));
            }
        }
    }
}

$pageTitle = 'Edit User';
include __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
        <form method="POST">
          <?= csrf_field() ?>
          <div class="mb-3"><label class="form-label">Username</label><input type="text" class="form-control" value="<?= e($user['username']) ?>" disabled></div>
          <div class="mb-3"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required value="<?= e($user['name']) ?>"></div>
          <div class="mb-3"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required value="<?= e($user['email']) ?>"></div>
          <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
              <option value="editor" <?= $user['role']==='editor'?'selected':'' ?>>Editor</option>
              <option value="admin" <?= $user['role']==='admin'?'selected':'' ?>>Admin</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="active" <?= $user['status']==='active'?'selected':'' ?>>Active</option>
              <option value="inactive" <?= $user['status']==='inactive'?'selected':'' ?>>Inactive</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Reset Password <small class="text-muted">(leave blank to keep current)</small></label>
            <input type="password" name="new_password" class="form-control" minlength="8">
          </div>
          <button class="btn btn-primary w-100">Save Changes</button>
          <a href="users.php" class="btn btn-outline-secondary w-100 mt-2">Back to Users</a>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
