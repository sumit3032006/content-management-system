<?php
require_once __DIR__ . '/_bootstrap.php';

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
$stmt->execute([$id]);
log_activity($pdo, "Deleted blog post #$id", 'blogs');

echo json_encode(['success' => true]);
