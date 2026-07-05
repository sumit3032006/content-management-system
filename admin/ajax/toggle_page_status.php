<?php
require_once __DIR__ . '/_bootstrap.php';

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT status FROM pages WHERE id = ?");
$stmt->execute([$id]);
$page = $stmt->fetch();

if (!$page) {
    echo json_encode(['success' => false, 'message' => 'Page not found.']);
    exit;
}

$newStatus = $page['status'] === 'published' ? 'unpublished' : 'published';
$stmt = $pdo->prepare("UPDATE pages SET status = ? WHERE id = ?");
$stmt->execute([$newStatus, $id]);
log_activity($pdo, "Set page #$id status to $newStatus", 'pages');

echo json_encode(['success' => true, 'status' => $newStatus]);
