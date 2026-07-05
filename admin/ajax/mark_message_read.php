<?php
require_once __DIR__ . '/_bootstrap.php';

$id = (int)($_POST['id'] ?? 0);
$pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?")->execute([$id]);

echo json_encode(['success' => true]);
