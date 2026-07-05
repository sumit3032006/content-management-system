<?php
/**
 * admin/user-add.php
 * Admin creates a new user account (admin or editor role).
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $name = clean($_POST['name'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = in_array($_POST['role'] ?? '', ['admin','editor']) ? $_POST['role'] : 'editor';

    if (!$name || !$email || !$username || !$password) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $check->execute([$email, $username]);
        if ($check->fetch()) {
            $error = 'A user with that email or username already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, username, password, role) VALUES (?,?,?,?,?)");
            $stmt->execute([$name, $email, $username, $hash, $role]);
            log_activity($pdo, "Created user: $username ($role)", 'users');
            header('Location: users.php');
            exit;
        }
    }
}

$pageTitle = 'Add User';
include __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="POST">
          <?= csrf_field() ?>
          <div class="mb-3"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required value="<?= e($_POST['name'] ?? '') ?>"></div>
          <div class="mb-3"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>"></div>
          <div class="mb-3"><label class="form-label">Username *</label><input type="text" name="username" class="form-control" required value="<?= e($_POST['username'] ?? '') ?>"></div>
          <div class="mb-3"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" minlength="8" required></div>
          <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
              <option value="editor">Editor</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <button class="btn btn-primary w-100">Create User</button>
          <a href="users.php" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
