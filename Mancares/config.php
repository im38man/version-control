<?php
/**
 * KONFIGURASI DATABASE MANCARE
 * -----------------------------------------------------------
 * Ganti 4 nilai di bawah ini dengan data dari panel InfinityFree
 * kamu (Control Panel > MySQL Databases).
 *
 * Contoh nilai di InfinityFree biasanya seperti ini:
 *   DB_HOST = sql200.infinityfree.com
 *   DB_NAME = epiz_12345678_mancare
 *   DB_USER = epiz_12345678
 *   DB_PASS = (password yang kamu buat sendiri saat membuat DB)
 * -----------------------------------------------------------
 */
define('DB_HOST', 'sql201.infinityfree.com');
define('DB_NAME', 'if0_42619393_mancares');
define('DB_USER', 'if0_42619393');
define('DB_PASS', '8lKyVcv8vG');

// Nama aplikasi (dipakai di beberapa tempat)
define('APP_NAME', 'MANCARE');

// Set true kalau lagi development lokal (biar error PHP kelihatan)
define('APP_DEBUG', false);

if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('Koneksi database gagal. Pastikan config.php sudah diisi dengan data InfinityFree yang benar. (' . (APP_DEBUG ? $e->getMessage() : 'detail disembunyikan') . ')');
}
