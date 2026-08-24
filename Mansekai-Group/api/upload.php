<?php
require_once __DIR__ . '/../lib.php';
require_login_api();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['ok' => false, 'error' => 'Method tidak didukung.'], 405);
}

if (!csrf_verify($_POST['csrf'] ?? '')) {
    json_out(['ok' => false, 'error' => 'Sesi tidak valid, silakan muat ulang halaman.'], 419);
}

if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    json_out(['ok' => false, 'error' => 'File gagal diupload.']);
}

$file = $_FILES['photo'];

if ($file['size'] > UPLOAD_MAX_SIZE) {
    json_out(['ok' => false, 'error' => 'Ukuran file maksimal 3MB.']);
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, UPLOAD_ALLOWED_EXT, true)) {
    json_out(['ok' => false, 'error' => 'Format file tidak didukung. Gunakan JPG, PNG, WEBP, atau GIF.']);
}

// Validasi tambahan: pastikan file benar-benar gambar (bukan sekadar ekstensi)
$imgInfo = @getimagesize($file['tmp_name']);
if ($imgInfo === false) {
    json_out(['ok' => false, 'error' => 'File yang diupload bukan gambar yang valid.']);
}

$ownerId = (int) current_user_id();
$ownerDir = UPLOAD_DIR . '/' . $ownerId;
if (!is_dir($ownerDir)) {
    mkdir($ownerDir, 0755, true);
}

$filename = bin2hex(random_bytes(12)) . '.' . $ext;
$destination = $ownerDir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    json_out(['ok' => false, 'error' => 'Gagal menyimpan file di server.']);
}

$url = UPLOAD_URL . '/' . $ownerId . '/' . $filename;
json_out(['ok' => true, 'url' => $url]);
