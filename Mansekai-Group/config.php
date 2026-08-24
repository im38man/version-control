<?php
/**
 * Konfigurasi utama Mansekai Group Dashboard
 * -------------------------------------------
 * Isi bagian DB_* sesuai data dari InfinityFree Control Panel:
 * Control Panel > MySQL Databases
 *
 * Host biasanya berformat: sqlXXX.infinityfree.com
 * Nama DB & user biasanya berformat: if0_XXXXXXXX_namadb
 */

// ==================== DATABASE ====================
define('DB_HOST', 'sql204.infinityfree.com');           // ganti dg host MySQL InfinityFree, mis: sql123.infinityfree.com
define('DB_NAME', 'if0_42661735_mansekai_group'); // ganti dg nama database kamu
define('DB_USER', 'if0_42661735');          // ganti dg username database kamu
define('DB_PASS', '6TIHEaqWwX8xL');                     // ganti dg password database kamu

// ==================== APLIKASI ====================
date_default_timezone_set('Asia/Jakarta');
define('APP_NAME', 'Mansekai Group Dashboard');
define('APP_URL', 'https://namadomainkamu.infinityfreeapp.com'); // ganti sesuai domain aktifmu

// ==================== EMAIL RESET PASSWORD (BREVO) ====================
// Buat API Key gratis di https://app.brevo.com/settings/keys/api lalu isi di bawah ini.
// Jika dikosongkan, sistem akan tetap membuat link reset tapi tidak mengirim email
// (link akan tetap tampil di layar sebagai fallback supaya tidak buntu saat testing).
define('BREVO_API_KEY', '');
define('BREVO_SENDER_EMAIL', 'no-reply@namadomainkamu.com');
define('BREVO_SENDER_NAME', 'Mansekai Group Dashboard');

// ==================== UPLOAD ====================
define('UPLOAD_DIR', __DIR__ . '/upload');
define('UPLOAD_URL', 'upload');
define('UPLOAD_MAX_SIZE', 3 * 1024 * 1024); // 3MB
define('UPLOAD_ALLOWED_EXT', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

// ==================== SESSION ====================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify($token) {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function require_login_page() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_login_api() {
    if (!is_logged_in()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Belum login / sesi berakhir.']);
        exit;
    }
}
