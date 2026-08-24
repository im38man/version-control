<?php
require_once __DIR__ . '/includes/session.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'need_login' => true]);
    exit;
}

$user_id = $_SESSION['user_id'];
$after_id = (int)($_GET['after'] ?? 0);

$stmt = mysqli_prepare($koneksi, 'SELECT id, sender, message, created_at FROM pesan_messages WHERE user_id = ? AND id > ? ORDER BY id ASC');
mysqli_stmt_bind_param($stmt, 'ii', $user_id, $after_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = [
        'id' => (int)$row['id'],
        'sender' => $row['sender'],
        'message' => $row['message'],
        'time' => date('H:i', strtotime($row['created_at'])),
    ];
}
mysqli_stmt_close($stmt);

// Tandai pesan admin sebagai sudah dibaca oleh user
$mark = mysqli_prepare($koneksi, 'UPDATE pesan_messages SET is_read = 1 WHERE user_id = ? AND sender = "admin" AND is_read = 0');
mysqli_stmt_bind_param($mark, 'i', $user_id);
mysqli_stmt_execute($mark);
mysqli_stmt_close($mark);

echo json_encode(['success' => true, 'messages' => $messages]);
