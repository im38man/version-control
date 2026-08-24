<?php
require_once __DIR__ . '/includes/session.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'need_login' => true]);
    exit;
}

$slug = trim($_POST['slug'] ?? '');
$title = trim($_POST['title'] ?? '');

if ($slug === '' || $title === '') {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare($koneksi, 'SELECT id FROM favorites WHERE user_id = ? AND destination_slug = ?');
mysqli_stmt_bind_param($stmt, 'is', $user_id, $slug);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
$exists = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if ($exists) {
    $del = mysqli_prepare($koneksi, 'DELETE FROM favorites WHERE user_id = ? AND destination_slug = ?');
    mysqli_stmt_bind_param($del, 'is', $user_id, $slug);
    mysqli_stmt_execute($del);
    mysqli_stmt_close($del);
    echo json_encode(['success' => true, 'favorited' => false]);
} else {
    $ins = mysqli_prepare($koneksi, 'INSERT INTO favorites (user_id, destination_slug, destination_title) VALUES (?, ?, ?)');
    mysqli_stmt_bind_param($ins, 'iss', $user_id, $slug, $title);
    mysqli_stmt_execute($ins);
    mysqli_stmt_close($ins);
    echo json_encode(['success' => true, 'favorited' => true]);
}
