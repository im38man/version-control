<?php
/**
 * Konfigurasi koneksi database - Zenith Tour & Travel
 * =====================================================
 * KHUSUS UNTUK HOSTING INFINITYFREE:
 * 1. Login ke Client Area InfinityFree -> pilih akun hosting -> "MySQL Databases".
 * 2. Buat database baru, catat nama DB, username, dan password yang diberikan.
 * 3. Host database di InfinityFree BUKAN "localhost", biasanya berbentuk:
 *    sqlXXX.infinityfree.com (lihat di panel vPanel bagian "MySQL Databases").
 * 4. Isi 4 variabel di bawah ini sesuai data dari vPanel InfinityFree Anda.
 * 5. Import file /sql/schema.sql lewat phpMyAdmin (tombol "Import") di vPanel.
 */

// ==== GANTI 4 BARIS DI BAWAH INI SESUAI DATA DARI VPANEL INFINITYFREE ANDA ====
define('DB_HOST', 'sql212.infinityfree.com');   // Host database dari vPanel
define('DB_USER', 'if0_42558593');              // Username database dari vPanel
define('DB_PASS', '58Ew6sjDiyt');    // Password database dari vPanel
define('DB_NAME', 'if0_42558593_zenith_adventour');       // Nama database dari vPanel
// ================================================================================

// Koneksi mysqli (ekstensi mysqli sudah aktif secara default di InfinityFree)
$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$koneksi) {
    // Jangan tampilkan detail error database ke publik saat production.
    http_response_code(500);
    die('Koneksi database gagal. Silakan cek kembali konfigurasi di config/db.php.');
}

mysqli_set_charset($koneksi, 'utf8mb4');
