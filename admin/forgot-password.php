<?php
/**
 * admin/forgot-password.php
 * Optional password recovery flow. Generates a reset token stored in DB.
 * NOTE: Actual email sending requires a configured mail server / SMTP
 * (e.g. PHPMailer) on your host. This file prepares the token + link;
 * wire up mail() or PHPMailer in the marked section for production use.
 */
require_once __DIR__ . '/../includes/functions.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email = clean($_POST['email'] ?? '');

    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Always show the same generic message (prevents email enumeration)
    $message = 'If an account with that email exists, a password reset link has been generated.';

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $stmt->execute([$token, $expires, $user['id']]);

        $resetLink = BASE_URL . '/admin/reset-password.php?token=' . $token;

        // ---- TODO (production): send $resetLink via email using PHPMailer/SMTP ----
        // mail($email, 'Password Reset', "Reset link: $resetLink");

        log_activity($pdo, 'Requested password reset', 'auth');

        // For local/dev convenience only, the link is shown on screen.
        // Remove this block once real email sending is configured.
        $message .= '<br><small class="text-muted">Dev preview link: <a href="' . e($resetLink) . '">' . e($resetLink) . '</a></small>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:420px;">
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="mb-3">Forgot Password</h5>
      <?php if ($message): ?><div class="alert alert-info"><?= $message ?></div><?php endif; ?>
      <form method="POST">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label">Registered Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Send Reset Link</button>
      </form>
      <p class="text-center mt-3"><a href="login.php">Back to Login</a></p>
    </div>
  </div>
</div>
</body>
</html>
