<?php
require_once __DIR__ . '/_bootstrap.php';

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT status FROM blog_posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    echo json_encode(['success' => false, 'message' => 'Post not found.']);
    exit;
}

$newStatus = $post['status'] === 'published' ? 'draft' : 'published';
$stmt = $pdo->prepare("UPDATE blog_posts SET status = ? WHERE id = ?");
$stmt->execute([$newStatus, $id]);
log_activity($pdo, "Set blog post #$id status to $newStatus", 'blogs');

echo json_encode(['success' => true, 'status' => $newStatus]);
