<?php
require_once __DIR__ . '/_bootstrap.php';

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
$stmt->execute([$id]);
log_activity($pdo, "Deleted page #$id", 'pages');

echo json_encode(['success' => true]);
