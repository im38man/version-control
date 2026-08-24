<?php
// config/db.php - Koneksi database (PDO MySQL)
// Ganti kredensial ini sesuai server hosting Anda (contoh: InfinityFree)

// FIX: jam pesan tidak sesuai — server hosting default pakai UTC, sedangkan
// Indonesia WIB = UTC+7. Disamakan di sini supaya semua tanggal/jam konsisten.
date_default_timezone_set('Asia/Jakarta');

$DB_HOST = 'sql207.infinityfree.com';
$DB_NAME = 'if0_42585657_nusantara_beans';
$DB_USER = 'if0_42585657';
$DB_PASS = 'KOEsQZSaFnG';

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    // Samakan zona waktu sesi MySQL ke WIB, supaya CURRENT_TIMESTAMP di kolom
    // created_at tersimpan sesuai jam Indonesia, bukan UTC server.
    $pdo->exec("SET time_zone = '+07:00'");
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . $e->getMessage());
}
