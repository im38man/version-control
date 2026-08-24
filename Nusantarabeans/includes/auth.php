<?php
// includes/auth.php - Helper session & hak akses
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_user() {
    if (!is_logged_in()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role'  => $_SESSION['user_role'],
    ];
}

function is_admin() {
    return is_logged_in() && in_array($_SESSION['user_role'], ['admin', 'admin_master']);
}

function is_admin_master() {
    return is_logged_in() && $_SESSION['user_role'] === 'admin_master';
}

// Panggil di awal halaman yang wajib login
function require_login($redirect = 'login.php') {
    if (!is_logged_in()) {
        header('Location: ' . $redirect);
        exit;
    }
}

// Panggil di awal halaman khusus admin/admin master
function require_admin($redirect = 'index.php') {
    if (!is_admin()) {
        header('Location: ' . $redirect);
        exit;
    }
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
}

function require_admin_master($redirect = 'index.php') {
    if (!is_admin_master()) {
        header('Location: ' . $redirect);
        exit;
    }
}
