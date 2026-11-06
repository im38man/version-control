<?php
/**
 * Bootstrap session + koneksi DB.
 * File ini di-include di baris paling atas setiap halaman .php.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

/** Apakah pengunjung sedang login? */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/** Apakah pengguna yang login adalah admin? */
function is_admin() {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

/** Apakah pengguna yang login adalah Admin Master (super admin)?
 *  Admin Master adalah satu-satunya yang boleh hapus user & menjadikan user sebagai admin.
 *  Admin biasa TIDAK punya akses ini. */
function is_master_admin() {
    return is_admin() && (int)($_SESSION['is_master'] ?? 0) === 1;
}

/** Ambil data user yang sedang login (array) atau null. */
function current_user() {
    if (!is_logged_in()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role'  => $_SESSION['role'],
        'is_master' => (int)($_SESSION['is_master'] ?? 0),
    ];
}

/** Redirect ke halaman login jika belum login (dipakai di halaman yang butuh login). */
function require_login($redirect_to = null) {
    if (!is_logged_in()) {
        $target = $redirect_to ?: basename($_SERVER['PHP_SELF']);
        header('Location: login.php?redirect=' . urlencode($target));
        exit;
    }
}

/** Escape output HTML dengan singkat. */
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
