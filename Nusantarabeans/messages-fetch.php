<?php
require_once __DIR__ . '/includes/auth.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['status' => 'need_login']);
    exit;
}

$after_id = (int) ($_GET['after_id'] ?? 0);

if (is_admin()) {
    // Admin bisa memilih percakapan user mana yang mau dilihat
    $target_user_id = (int) ($_GET['user_id'] ?? 0);
    if ($target_user_id <= 0) {
        echo json_encode(['status' => 'ok', 'messages' => []]);
        exit;
    }
    $stmt = $pdo->prepare('SELECT * FROM messages WHERE user_id = ? AND id > ? ORDER BY id ASC');
    $stmt->execute([$target_user_id, $after_id]);
    $messages = $stmt->fetchAll();

    // Tandai pesan dari user sebagai sudah dibaca admin
    $pdo->prepare("UPDATE messages SET is_read = 1 WHERE user_id = ? AND sender_role = 'user' AND is_read = 0")
        ->execute([$target_user_id]);
} else {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare('SELECT * FROM messages WHERE user_id = ? AND id > ? ORDER BY id ASC');
    $stmt->execute([$user_id, $after_id]);
    $messages = $stmt->fetchAll();

    // Tandai pesan dari admin sebagai sudah dibaca user
    $pdo->prepare("UPDATE messages SET is_read = 1 WHERE user_id = ? AND sender_role = 'admin' AND is_read = 0")
        ->execute([$user_id]);
}

echo json_encode(['status' => 'ok', 'messages' => $messages]);
