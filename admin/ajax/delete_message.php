<?php
require_once __DIR__ . '/_bootstrap.php';

$id = (int)($_POST['id'] ?? 0);
$pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$id]);
log_activity($pdo, "Deleted contact message #$id", 'messages');

echo json_encode(['success' => true]);
