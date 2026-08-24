<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Belum login.');
}

$userId = (int) $_SESSION['user_id'];
$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    exit('ID tidak valid.');
}

$stmt = mysqli_prepare($koneksi, "SELECT nama_asli, nama_file, ekstensi FROM dokumen WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $id, $userId);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$row) {
    http_response_code(404);
    exit('Dokumen tidak ditemukan atau bukan milik Anda.');
}

$pathFile = __DIR__ . '/../uploads/documents/' . $userId . '/' . $row['nama_file'];

if (!is_file($pathFile)) {
    http_response_code(404);
    exit('File fisik tidak ditemukan di server.');
}

$mimeMap = [
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls'  => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
];
$mime = $mimeMap[$row['ekstensi']] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . basename($row['nama_asli']) . '"');
header('Content-Length: ' . filesize($pathFile));
header('X-Content-Type-Options: nosniff');
readfile($pathFile);
exit;
