<?php
// Bootstrap kecil yang dipakai oleh semua file di folder api/.
// Beda dengan requireLogin() di includes/auth.php: kalau belum login,
// endpoint ini balikin JSON 401 (bukan redirect ke login.php),
// karena file-file ini dipanggil lewat fetch()/AJAX dari JavaScript.

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/koneksi.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesi login habis, silakan login ulang.']);
    exit;
}

$USER_ID = (int) $_SESSION['user_id'];

// Ambil input JSON (dipakai untuk request POST dari fetch dengan body JSON)
function ambilInputJSON() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function kirimJSON($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Bungkus mysqli_prepare(): kalau gagal (misal tabel belum ada karena
// migrasi SQL belum di-import), balikin pesan error yang jelas lewat JSON
// alih-alih PHP fatal error yang bikin halaman blank / respons rusak.
function siapkanQuery($koneksi, $sql) {
    $stmt = mysqli_prepare($koneksi, $sql);
    if ($stmt === false) {
        kirimJSON([
            'success' => false,
            'message' => 'Query database gagal disiapkan: ' . mysqli_error($koneksi) .
                ' (pastikan semua file di folder sql/ sudah di-import ke database).',
        ], 500);
    }
    return $stmt;
}
