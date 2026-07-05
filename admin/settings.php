<?php
/**
 * admin/settings.php
 * Admin-only global site settings: name, logo, favicon, contact info, social links, footer text.
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $fields = [
        'site_name'        => clean($_POST['site_name'] ?? ''),
        'site_tagline'     => clean($_POST['site_tagline'] ?? ''),
        'contact_email'    => clean($_POST['contact_email'] ?? ''),
        'contact_phone'    => clean($_POST['contact_phone'] ?? ''),
        'contact_address'  => clean($_POST['contact_address'] ?? ''),
        'social_facebook'  => clean($_POST['social_facebook'] ?? ''),
        'social_twitter'   => clean($_POST['social_twitter'] ?? ''),
        'social_instagram' => clean($_POST['social_instagram'] ?? ''),
        'social_linkedin'  => clean($_POST['social_linkedin'] ?? ''),
        'footer_text'      => clean($_POST['footer_text'] ?? ''),
    ];

    foreach ($fields as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$key, $value]);
    }

    // Logo upload
    if (!empty($_FILES['site_logo']['name'])) {
        $upload = handle_image_upload('site_logo', 'logo');
        if ($upload['success']) {
            $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('site_logo', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)")->execute([$upload['path']]);
        } else { $error = $upload['message']; }
    }

    // Favicon upload
    if (!empty($_FILES['site_favicon']['name'])) {
        $upload = handle_image_upload('site_favicon', 'logo');
        if ($upload['success']) {
            $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('site_favicon', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)")->execute([$upload['path']]);
        } else { $error = $upload['message']; }
    }

    log_activity($pdo, 'Updated site settings', 'settings');
    $success = 'Settings saved successfully.';
    // Force settings cache refresh for this request
    $GLOBALS['pdo'] = $pdo;
}

// Reload settings fresh (bypass static cache)
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$s = [];
foreach ($stmt->fetchAll() as $row) { $s[$row['setting_key']] = $row['setting_value']; }

$pageTitle = 'Settings';
include __DIR__ . '/includes/header.php';
?>

<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card shadow-sm mb-3">
        <div class="card-header bg-white"><strong>General</strong></div>
        <div class="card-body">
          <div class="mb-3"><label class="form-label">Website Name</label><input type="text" name="site_name" class="form-control" value="<?= e($s['site_name'] ?? '') ?>"></div>
          <div class="mb-3"><label class="form-label">Tagline</label><input type="text" name="site_tagline" class="form-control" value="<?= e($s['site_tagline'] ?? '') ?>"></div>
          <div class="mb-3">
            <label class="form-label">Logo</label>
            <?php if (!empty($s['site_logo'])): ?><div><img src="<?= BASE_URL.'/'.e($s['site_logo']) ?>" style="height:40px;" class="mb-2"></div><?php endif; ?>
            <input type="file" name="site_logo" class="form-control" accept="image/*">
          </div>
          <div class="mb-0">
            <label class="form-label">Favicon</label>
            <?php if (!empty($s['site_favicon'])): ?><div><img src="<?= BASE_URL.'/'.e($s['site_favicon']) ?>" style="height:24px;" class="mb-2"></div><?php endif; ?>
            <input type="file" name="site_favicon" class="form-control" accept="image/*">
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>Contact Details</strong></div>
        <div class="card-body">
          <div class="mb-3"><label class="form-label">Email</label><input type="email" name="contact_email" class="form-control" value="<?= e($s['contact_email'] ?? '') ?>"></div>
          <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="contact_phone" class="form-control" value="<?= e($s['contact_phone'] ?? '') ?>"></div>
          <div class="mb-0"><label class="form-label">Address</label><textarea name="contact_address" class="form-control" rows="2"><?= e($s['contact_address'] ?? '') ?></textarea></div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card shadow-sm mb-3">
        <div class="card-header bg-white"><strong>Social Media Links</strong></div>
        <div class="card-body">
          <div class="mb-3"><label class="form-label"><i class="bi bi-facebook"></i> Facebook</label><input type="url" name="social_facebook" class="form-control" value="<?= e($s['social_facebook'] ?? '') ?>"></div>
          <div class="mb-3"><label class="form-label"><i class="bi bi-twitter-x"></i> Twitter / X</label><input type="url" name="social_twitter" class="form-control" value="<?= e($s['social_twitter'] ?? '') ?>"></div>
          <div class="mb-3"><label class="form-label"><i class="bi bi-instagram"></i> Instagram</label><input type="url" name="social_instagram" class="form-control" value="<?= e($s['social_instagram'] ?? '') ?>"></div>
          <div class="mb-0"><label class="form-label"><i class="bi bi-linkedin"></i> LinkedIn</label><input type="url" name="social_linkedin" class="form-control" value="<?= e($s['social_linkedin'] ?? '') ?>"></div>
        </div>
      </div>

      <div class="card shadow-sm mb-3">
        <div class="card-header bg-white"><strong>Footer</strong></div>
        <div class="card-body">
          <textarea name="footer_text" class="form-control" rows="2"><?= e($s['footer_text'] ?? '') ?></textarea>
        </div>
      </div>

      <button class="btn btn-primary w-100">Save Settings</button>
    </div>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
