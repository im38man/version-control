<?php
require_once __DIR__ . '/_bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    kirimJSON(['success' => false, 'message' => 'Method tidak didukung'], 405);
}

$EKSTENSI_DIIZINKAN = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$MAX_UKURAN = 3 * 1024 * 1024; // 3MB
$FOLDER_AVATAR = __DIR__ . '/../uploads/avatars/';

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    kirimJSON(['success' => false, 'message' => 'Tidak ada foto yang diunggah atau upload gagal.'], 400);
}

$file = $_FILES['avatar'];
$ukuran = $file['size'];
$ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ekstensi, $EKSTENSI_DIIZINKAN)) {
    kirimJSON(['success' => false, 'message' => 'Format foto tidak didukung. Hanya JPG, PNG, GIF, WEBP.'], 400);
}
if ($ukuran > $MAX_UKURAN) {
    kirimJSON(['success' => false, 'message' => 'Ukuran foto maksimal 3MB.'], 400);
}

// Pastikan file yang diupload benar-benar gambar (bukan cuma nama ekstensi yang direkayasa)
$infoGambar = @getimagesize($file['tmp_name']);
if ($infoGambar === false) {
    kirimJSON(['success' => false, 'message' => 'File yang diunggah bukan gambar yang valid.'], 400);
}

if (!is_dir($FOLDER_AVATAR)) {
    mkdir($FOLDER_AVATAR, 0755, true);
}

// Hapus avatar lama milik user ini (ekstensi apapun) sebelum simpan yang baru
foreach ($EKSTENSI_DIIZINKAN as $ext) {
    $lama = $FOLDER_AVATAR . $USER_ID . '.' . $ext;
    if (is_file($lama)) unlink($lama);
}

$namaFile = $USER_ID . '.' . $ekstensi;
$pathTujuan = $FOLDER_AVATAR . $namaFile;

if (!move_uploaded_file($file['tmp_name'], $pathTujuan)) {
    kirimJSON(['success' => false, 'message' => 'Gagal menyimpan foto di server.'], 500);
}

// path relatif ini yang disimpan & dipakai sebagai src="" di halaman
$pathRelatif = 'uploads/avatars/' . $namaFile . '?v=' . time(); // ?v= biar browser tidak pakai cache lama

$stmt = siapkanQuery($koneksi, "
    INSERT INTO profil (user_id, avatar) VALUES (?, ?)
    ON DUPLICATE KEY UPDATE avatar = VALUES(avatar)
");
mysqli_stmt_bind_param($stmt, "is", $USER_ID, $pathRelatif);
mysqli_stmt_execute($stmt);

kirimJSON(['success' => true, 'data' => ['avatar' => $pathRelatif]]);
