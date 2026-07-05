<footer class="bg-dark text-light pt-5 pb-4 mt-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-4">
        <h5 class="fw-bold"><?= e(setting('site_name', SITE_NAME_DEFAULT)) ?></h5>
        <p class="text-secondary"><?= e(setting('site_tagline')) ?></p>
        <div class="d-flex gap-3 fs-5">
          <?php if (setting('social_facebook')): ?><a class="text-light" href="<?= e(setting('social_facebook')) ?>" target="_blank"><i class="bi bi-facebook"></i></a><?php endif; ?>
          <?php if (setting('social_twitter')): ?><a class="text-light" href="<?= e(setting('social_twitter')) ?>" target="_blank"><i class="bi bi-twitter-x"></i></a><?php endif; ?>
          <?php if (setting('social_instagram')): ?><a class="text-light" href="<?= e(setting('social_instagram')) ?>" target="_blank"><i class="bi bi-instagram"></i></a><?php endif; ?>
          <?php if (setting('social_linkedin')): ?><a class="text-light" href="<?= e(setting('social_linkedin')) ?>" target="_blank"><i class="bi bi-linkedin"></i></a><?php endif; ?>
        </div>
      </div>
      <div class="col-md-4">
        <h6 class="fw-bold">Quick Links</h6>
        <ul class="list-unstyled">
          <li><a class="text-secondary text-decoration-none" href="<?= BASE_URL ?>/index.php">Home</a></li>
          <li><a class="text-secondary text-decoration-none" href="<?= BASE_URL ?>/blog.php">Blog</a></li>
          <li><a class="text-secondary text-decoration-none" href="<?= BASE_URL ?>/contact.php">Contact</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h6 class="fw-bold">Contact</h6>
        <p class="text-secondary mb-1"><i class="bi bi-envelope me-2"></i><?= e(setting('contact_email')) ?></p>
        <p class="text-secondary mb-1"><i class="bi bi-telephone me-2"></i><?= e(setting('contact_phone')) ?></p>
        <p class="text-secondary"><i class="bi bi-geo-alt me-2"></i><?= e(setting('contact_address')) ?></p>
      </div>
    </div>
    <hr class="border-secondary">
    <p class="text-center text-secondary mb-0 small"><?= e(setting('footer_text')) ?></p>
  </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/site.js"></script>
</body>
</html>
