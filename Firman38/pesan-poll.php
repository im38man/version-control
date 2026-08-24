<?php
require __DIR__ . '/config/db.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['messages' => []]);
    exit;
}

$since = (int) ($_GET['since'] ?? 0);

if (isAdmin()) {
    $targetUserId = (int) ($_GET['user'] ?? 0);
    if (!$targetUserId) {
        echo json_encode(['messages' => []]);
        exit;
    }
    $stmt = $pdo->prepare('SELECT * FROM messages WHERE user_id = ? AND id > ? ORDER BY id ASC');
    $stmt->execute([$targetUserId, $since]);
    // pesan baru dari user langsung ditandai terbaca karena admin sedang membuka chat ini
    $pdo->prepare('UPDATE messages SET is_read = 1 WHERE user_id = ? AND sender_role = "user" AND id > ?')
        ->execute([$targetUserId, $since]);
} else {
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare('SELECT * FROM messages WHERE user_id = ? AND id > ? ORDER BY id ASC');
    $stmt->execute([$userId, $since]);
    $pdo->prepare('UPDATE messages SET is_read = 1 WHERE user_id = ? AND sender_role = "admin" AND id > ?')
        ->execute([$userId, $since]);
}

$rows = $stmt->fetchAll();
$out = array_map(function ($m) {
    return [
        'id'           => (int) $m['id'],
        'sender_role'  => $m['sender_role'],
        'message_html' => nl2br(htmlspecialchars($m['message'], ENT_QUOTES, 'UTF-8')),
        'time'         => date('H:i', strtotime($m['created_at'])),
    ];
}, $rows);

echo json_encode(['messages' => $out]);
