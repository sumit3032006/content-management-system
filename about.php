<?php
/**
 * about.php
 * Convenience wrapper so /about.php works directly; renders the
 * 'about' page content that is fully editable from the admin panel.
 */
$_GET['slug'] = 'about';
require __DIR__ . '/page.php';
