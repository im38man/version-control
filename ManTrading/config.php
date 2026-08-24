<?php
/**
 * ManTrading - Konfigurasi Aplikasi
 * Jangan edit file ini untuk isi kredensial DB — itu ada di config.db.php
 * (otomatis ditulis oleh setup-admin.php).
 */

require_once __DIR__ . '/config.db.php';

// Zona waktu aplikasi: Jakarta (WIB, UTC+7)
date_default_timezone_set('Asia/Jakarta');

// Folder upload komunitas (relatif dari root project)
define('UPLOAD_DIR', __DIR__ . '/uploads/community/');
define('MAX_UPLOAD_BYTES', 3 * 1024 * 1024); // 3MB

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$isSetupPage = basename($scriptName) === 'setup-admin.php';
$inAdminFolder = basename(dirname($scriptName)) === 'admin';
$setupUrl = $inAdminFolder ? '../setup-admin.php' : 'setup-admin.php';

// Kalau aplikasi belum pernah di-setup (belum ada file .installed) dan bukan
// sedang membuka setup-admin.php, lempar ke wizard instalasi dulu.
if (!$isSetupPage && !file_exists(__DIR__ . '/.installed')) {
    header('Location: ' . $setupUrl);
    exit;
}

if (!$isSetupPage) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset('utf8mb4');
        $conn->query("SET time_zone = '+07:00'"); // samakan waktu DB (NOW(), CURRENT_TIMESTAMP) dengan WIB
    } catch (mysqli_sql_exception $e) {
        http_response_code(500);
        die('Koneksi database gagal. Cek pengaturan di config.db.php, atau hapus file .installed lalu buka ' . $setupUrl . ' lagi untuk instal ulang. (' . htmlspecialchars($e->getMessage()) . ')');
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
