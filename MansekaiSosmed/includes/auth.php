<?php
// Selalu mulai session di awal setiap file yang butuh login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Wajib login (admin atau user) buat akses halaman ini
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

// Wajib login SEBAGAI ADMIN
function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header("Location: index.php?error=akses_ditolak");
        exit;
    }
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
