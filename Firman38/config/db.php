<?php
// config/db.php — Koneksi database (PDO MySQL) — disetel untuk hosting InfinityFree
//
// Cara isi nilai di bawah (lihat di panel InfinityFree > MySQL Databases):
// - $db_host  : biasanya seperti "sql200.infinityfree.com" (BUKAN "localhost")
// - $db_name  : diawali prefix akun kamu, contoh "epiz_12345678_firman"
// - $db_user  : sama persis dengan nama database (InfinityFree mewajibkan ini)
// - $db_pass  : password database yang kamu buat di panel
$db_host = 'sql312.infinityfree.com';
$db_name = 'if0_42607038_firman';
$db_user = 'if0_42607038';
$db_pass = 'nIYR8t7CjRLB6qW';

try {
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . $e->getMessage());
}

// Session harus dimulai sebelum output apa pun.
// InfinityFree kadang butuh path session eksplisit karena shared hosting —
// kalau login/session "kepencet balik" terus, aktifkan 2 baris di bawah ini:
// session_save_path(__DIR__ . '/../sessions');
// if (!is_dir(__DIR__ . '/../sessions')) mkdir(__DIR__ . '/../sessions', 0700);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper: cek apakah user sedang login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper: cek apakah yang login adalah admin
function isAdmin() {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

// Helper: wajib login, kalau belum redirect ke login.php
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Helper: wajib admin, kalau bukan tolak akses
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

// Helper: escape output biar aman dari XSS
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Helper: hitung pesan belum dibaca UNTUK ADMIN (semua pesan dari user yang belum dibaca)
function unreadMessageCount($pdo) {
    static $count = null;
    if ($count === null) {
        $count = (int) $pdo->query(
            "SELECT COUNT(*) FROM messages WHERE sender_role = 'user' AND is_read = 0"
        )->fetchColumn();
    }
    return $count;
}

// Helper: hitung balasan admin yang belum dibaca UNTUK USER tertentu
function unreadReplyCount($pdo, $userId) {
    static $cache = [];
    if (!isset($cache[$userId])) {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM messages WHERE user_id = ? AND sender_role = 'admin' AND is_read = 0"
        );
        $stmt->execute([$userId]);
        $cache[$userId] = (int) $stmt->fetchColumn();
    }
    return $cache[$userId];
}

// Helper: badge navbar "Message Me" — beda hitungan tergantung admin/user
function navMessageBadge($pdo) {
    if (!isLoggedIn()) return 0;
    return isAdmin() ? unreadMessageCount($pdo) : unreadReplyCount($pdo, $_SESSION['user_id']);
}

// Helper: hitung total thread forum (dipakai kalau perlu badge di navbar)
function totalThreadCount($pdo) {
    static $count = null;
    if ($count === null) {
        $count = (int) $pdo->query("SELECT COUNT(*) FROM forum_threads")->fetchColumn();
    }
    return $count;
}
