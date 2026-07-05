<?php
/**
 * admin/login.php
 * Secure admin/editor login with password_verify, CSRF protection,
 * and basic brute-force throttling via session attempt counter.
 */
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    // Basic throttling: max 5 attempts per 5 minutes per session
    $_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
    $_SESSION['login_first_attempt'] = $_SESSION['login_first_attempt'] ?? time();

    if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['login_first_attempt']) < 300) {
        $error = 'Too many failed attempts. Please try again in a few minutes.';
    } else {
        $username = clean($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Please enter both username and password.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Success - regenerate session id to prevent fixation
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                unset($_SESSION['login_attempts'], $_SESSION['login_first_attempt']);

                log_activity($pdo, 'Logged in', 'auth');
                header('Location: dashboard.php');
                exit;
            } else {
                $_SESSION['login_attempts']++;
                $error = 'Invalid username or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | <?= e(setting('site_name', SITE_NAME_DEFAULT)) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
  body { background: linear-gradient(135deg,#1e3c72,#2a5298); min-height:100vh; display:flex; align-items:center; }
  .login-card { max-width:400px; margin:auto; border:none; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,.25); }
  .login-icon { width:60px; height:60px; border-radius:50%; background:#2a5298; color:#fff; display:flex; align-items:center; justify-content:center; font-size:28px; margin:-52px auto 15px; box-shadow:0 4px 12px rgba(0,0,0,.2); }
</style>
</head>
<body>
<div class="container">
  <div class="card login-card p-4">
    <div class="login-icon"><i class="bi bi-shield-lock"></i></div>
    <h4 class="text-center mb-1">Admin Panel</h4>
    <p class="text-center text-muted mb-4"><?= e(setting('site_name', SITE_NAME_DEFAULT)) ?></p>

    <?php if ($error): ?>
      <div class="alert alert-danger py-2"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <p class="text-center text-muted mt-3 mb-0" style="font-size:.85rem;">
      Default credentials: <code>admin</code> / <code>Admin@123</code>
    </p>
  </div>
</div>
</body>
</html>
