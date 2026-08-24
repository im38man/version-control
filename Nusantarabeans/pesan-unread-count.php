<?php
require_once __DIR__ . '/includes/auth.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'ok', 'count' => 0]);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE user_id = ? AND sender_role = 'admin' AND is_read = 0");
$stmt->execute([$user_id]);
$count = (int) $stmt->fetchColumn();

echo json_encode(['status' => 'ok', 'count' => $count]);
