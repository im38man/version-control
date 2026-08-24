<?php
// Gerbang akses materi berbasis file .html.
// File fisiknya disimpan di uploads/materi/ yang diblokir lewat .htaccess,
// jadi SATU-SATUNYA jalan buka file itu ya lewat endpoint ini.
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../config/koneksi.php';

$userId = (int) $_SESSION['user_id'];
$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    exit('ID materi tidak valid.');
}

$stmt = mysqli_prepare($koneksi, "SELECT judul, status, file_materi FROM materi WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$materi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$materi || !$materi['file_materi']) {
    http_response_code(404);
    exit('Materi tidak ditemukan.');
}

// ================= CEK IZIN AKSES =================
// Admin selalu boleh. User biasa wajib punya pengajuan berstatus 'approved'
// DAN materinya tidak sedang dikunci admin.
$punyaAkses = isAdmin();
if (!$punyaAkses) {
    $cekAkses = mysqli_prepare($koneksi, "SELECT status FROM pengajuan_materi WHERE materi_id=? AND user_id=?");
    mysqli_stmt_bind_param($cekAkses, "ii", $id, $userId);
    mysqli_stmt_execute($cekAkses);
    $akses = mysqli_fetch_assoc(mysqli_stmt_get_result($cekAkses));
    $punyaAkses = $akses && $akses['status'] === 'approved';
}

if ($materi['status'] === 'Locked' && !isAdmin()) {
    $punyaAkses = false;
}

if (!$punyaAkses) {
    http_response_code(403);
    exit('Kamu belum punya izin admin untuk membuka materi ini.');
}

$pathFile = __DIR__ . '/../uploads/materi/' . $materi['file_materi'];

if (!is_file($pathFile)) {
    http_response_code(404);
    exit('File materi tidak ditemukan di server.');
}

$html = file_get_contents($pathFile);

// Suntik tombol "Kembali ke Daftar Materi" yang mengambang, supaya user
// tetap gampang balik ke aplikasi walau file HTML-nya dibuka full page.
$tombolBack = <<<HTML
<div style="position:fixed;top:14px;left:14px;z-index:2147483647;font-family:sans-serif;">
    <a href="../materi.php" style="display:inline-flex;align-items:center;gap:8px;background:#0f111a;color:#fff;text-decoration:none;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;box-shadow:0 4px 14px rgba(0,0,0,0.25);">
        &#8592; Kembali ke Daftar Materi
    </a>
</div>
HTML;

if (preg_match('/<body[^>]*>/i', $html)) {
    $html = preg_replace('/(<body[^>]*>)/i', '$1' . $tombolBack, $html, 1);
} else {
    $html = $tombolBack . $html;
}

// Tampil sebagai halaman HTML biasa di dalam sesi yang sudah tervalidasi.
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
echo $html;
exit;
