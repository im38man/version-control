<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_mentor_or_admin(); // admin & mentor yang sudah di-approve boleh posting ke community

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: community.php');
    exit;
}
csrf_verify();

$user = current_user();
$caption = trim($_POST['caption'] ?? '');

if ($caption === '' || empty($_FILES['photo']['name'])) {
    flash_set('Foto dan bio/caption wajib diisi ya bro!', 'error');
    header('Location: community.php');
    exit;
}

$file = $_FILES['photo'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    flash_set('Upload foto gagal, coba lagi.', 'error');
    header('Location: community.php');
    exit;
}

if ($file['size'] > MAX_UPLOAD_BYTES) {
    flash_set('Ukuran foto maksimal 3MB.', 'error');
    header('Location: community.php');
    exit;
}

$allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!isset($allowedMime[$mime])) {
    flash_set('Format foto harus JPG, PNG, atau WEBP.', 'error');
    header('Location: community.php');
    exit;
}

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

$ext = $allowedMime[$mime];
$filename = 'post_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
$destPath = UPLOAD_DIR . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    flash_set('Gagal menyimpan foto ke server.', 'error');
    header('Location: community.php');
    exit;
}

$imagePath = $filename; // simpan nama file saja, ditampilkan lewat serve-image.php

$stmt = $conn->prepare('INSERT INTO community_posts (user_id, caption, image_path) VALUES (?, ?, ?)');
$stmt->bind_param('iss', $user['id'], $caption, $imagePath);
$stmt->execute();
$stmt->close();

flash_set('Postingan berhasil dipublikasikan.', 'success');
header('Location: community.php');
exit;
