<?php
require_once __DIR__ . '/../includes/session.php';
header('Content-Type: application/json');

if (!is_logged_in() || !is_admin()) {
    echo json_encode(['success' => false, 'need_login' => true]);
    exit;
}

$user_id = (int)($_POST['user_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if (!$user_id || $message === '') {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit;
}
if (mb_strlen($message) > 1000) {
    $message = mb_substr($message, 0, 1000);
}

// Pastikan target adalah user biasa yang valid
$check = mysqli_prepare($koneksi, 'SELECT id FROM users WHERE id = ? AND role = "user"');
mysqli_stmt_bind_param($check, 'i', $user_id);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);
if (mysqli_stmt_num_rows($check) === 0) {
    mysqli_stmt_close($check);
    echo json_encode(['success' => false, 'message' => 'Pengguna tidak ditemukan.']);
    exit;
}
mysqli_stmt_close($check);

$stmt = mysqli_prepare($koneksi, 'INSERT INTO pesan_messages (user_id, sender, message, is_read) VALUES (?, "admin", ?, 1)');
mysqli_stmt_bind_param($stmt, 'is', $user_id, $message);
mysqli_stmt_execute($stmt);
$id = mysqli_insert_id($koneksi);
mysqli_stmt_close($stmt);

echo json_encode(['success' => true, 'id' => $id, 'time' => date('H:i')]);
