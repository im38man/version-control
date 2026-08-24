<?php
require_once __DIR__ . '/includes/session.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'need_login' => true]);
    exit;
}

$message = trim($_POST['message'] ?? '');
if ($message === '') {
    echo json_encode(['success' => false, 'message' => 'Pesan tidak boleh kosong.']);
    exit;
}
if (mb_strlen($message) > 1000) {
    $message = mb_substr($message, 0, 1000);
}

$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare($koneksi, 'INSERT INTO pesan_messages (user_id, sender, message) VALUES (?, "user", ?)');
mysqli_stmt_bind_param($stmt, 'is', $user_id, $message);
mysqli_stmt_execute($stmt);
$id = mysqli_insert_id($koneksi);
mysqli_stmt_close($stmt);

echo json_encode([
    'success' => true,
    'id' => $id,
    'time' => date('H:i'),
]);
