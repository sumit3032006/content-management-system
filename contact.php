<?php
/**
 * contact.php
 * Public contact form: Name, Email, Phone, Subject, Message.
 * Validates input server-side, protects with CSRF token, stores in DB.
 */
require_once __DIR__ . '/includes/functions.php';

$success = '';
$error = '';
$old = ['name'=>'','email'=>'','phone'=>'','subject'=>'','message'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $old['name'] = clean($_POST['name'] ?? '');
    $old['email'] = clean($_POST['email'] ?? '');
    $old['phone'] = clean($_POST['phone'] ?? '');
    $old['subject'] = clean($_POST['subject'] ?? '');
    $old['message'] = clean($_POST['message'] ?? '');

    if (!$old['name'] || !$old['email'] || !$old['message']) {
        $error = 'Name, email, and message are required.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?,?,?,?,?)");
        $stmt->execute([$old['name'], $old['email'], $old['phone'], $old['subject'], $old['message']]);
        $success = 'Thank you! Your message has been sent successfully. We will get back to you soon.';
        $old = ['name'=>'','email'=>'','phone'=>'','subject'=>'','message'=>'']; // clear form
    }
}

$pageTitle = 'Contact Us - ' . setting('site_name', SITE_NAME_DEFAULT);
include __DIR__ . '/includes/site-header.php';
?>

<header class="py-5 bg-light text-center border-bottom">
  <div class="container"><h1 class="fw-bold">Contact Us</h1></div>
</header>

<main class="container py-5">
  <div class="row g-4">
    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-body">
          <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
          <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

          <form method="POST" novalidate>
            <?= csrf_field() ?>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-control" required value="<?= e($old['name']) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" required value="<?= e($old['email']) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= e($old['phone']) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-control" value="<?= e($old['subject']) ?>">
              </div>
              <div class="col-12">
                <label class="form-label">Message *</label>
                <textarea name="message" class="form-control" rows="5" required><?= e($old['message']) ?></textarea>
              </div>
              <div class="col-12">
                <button class="btn btn-primary px-4">Send Message</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5 class="mb-3">Get in Touch</h5>
          <p><i class="bi bi-envelope me-2 text-primary"></i><?= e(setting('contact_email')) ?></p>
          <p><i class="bi bi-telephone me-2 text-primary"></i><?= e(setting('contact_phone')) ?></p>
          <p><i class="bi bi-geo-alt me-2 text-primary"></i><?= e(setting('contact_address')) ?></p>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/site-footer.php'; ?>
