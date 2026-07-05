<?php
require_once __DIR__ . '/_bootstrap.php';

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Only admins can delete users.']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot delete your own account.']);
    exit;
}

$pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
log_activity($pdo, "Deleted user #$id", 'users');

echo json_encode(['success' => true]);
