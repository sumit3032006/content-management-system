<?php
/**
 * admin/profile.php
 * Logged-in user can view/update their own name & email (not role).
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name = clean($_POST['name'] ?? '');
    $email = clean($_POST['email'] ?? '');

    if (!$name || !$email) {
        $error = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        $pdo->prepare("UPDATE users SET name=?, email=? WHERE id=?")->execute([$name, $email, $_SESSION['user_id']]);
        $_SESSION['user_name'] = $name;
        $success = 'Profile updated successfully.';
        $user['name'] = $name; $user['email'] = $email;
    }
}

$pageTitle = 'My Profile';
include __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-center mb-3">
          <i class="bi bi-person-circle" style="font-size:64px;"></i>
          <h5 class="mt-2 mb-0"><?= e($user['name']) ?></h5>
          <span class="badge bg-secondary"><?= ucfirst($user['role']) ?></span>
        </div>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
        <form method="POST">
          <?= csrf_field() ?>
          <div class="mb-3"><label class="form-label">Username</label><input type="text" class="form-control" value="<?= e($user['username']) ?>" disabled></div>
          <div class="mb-3"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" required value="<?= e($user['name']) ?>"></div>
          <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required value="<?= e($user['email']) ?>"></div>
          <button class="btn btn-primary w-100">Update Profile</button>
        </form>
        <a href="change-password.php" class="btn btn-outline-secondary w-100 mt-2">Change Password</a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
