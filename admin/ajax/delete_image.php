<?php
require_once __DIR__ . '/_bootstrap.php';

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT file_path FROM media WHERE id = ?");
$stmt->execute([$id]);
$img = $stmt->fetch();

if (!$img) {
    echo json_encode(['success' => false, 'message' => 'Image not found.']);
    exit;
}

$fullPath = ROOT_PATH . '/' . $img['file_path'];
if (is_file($fullPath)) {
    @unlink($fullPath);
}

$pdo->prepare("DELETE FROM media WHERE id = ?")->execute([$id]);
log_activity($pdo, "Deleted image #$id", 'media');

echo json_encode(['success' => true]);
