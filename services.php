<?php
/**
 * services.php
 * Convenience wrapper so /services.php works directly; renders the
 * 'services' page content that is fully editable from the admin panel.
 */
$_GET['slug'] = 'services';
require __DIR__ . '/page.php';
