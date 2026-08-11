<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

/** Ambil data user yang sedang login langsung dari DB (biar status/role selalu fresh) */
function current_user() {
    global $pdo;
    if (!is_logged_in()) return null;
    $stmt = $pdo->prepare('SELECT id, username, email, role, status, income FROM users WHERE id = ?');
    $stmt->execute([current_user_id()]);
    $user = $stmt->fetch();
    if (!$user || $user['status'] !== 'active') {
        // Akun dihapus / dibanned di tengah sesi -> paksa logout
        session_unset();
        session_destroy();
        return null;
    }
    return $user;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
    $user = current_user();
    if (!$user) {
        header('Location: login.php?banned=1');
        exit;
    }
    return $user;
}

function require_login_api() {
    if (!is_logged_in()) {
        json_response(false, null, 'Sesi habis, silakan login ulang.', 401);
    }
    $user = current_user();
    if (!$user) {
        json_response(false, null, 'Akun tidak aktif.', 403);
    }
    return $user;
}

function require_admin() {
    $user = require_login();
    if ($user['role'] !== 'admin') {
        header('Location: index.php');
        exit;
    }
    return $user;
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

function json_response($success, $data = null, $message = '', $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

/** Ambil token CSRF dari header X-CSRF-Token atau dari body JSON */
function csrf_from_request($input) {
    if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) return $_SERVER['HTTP_X_CSRF_TOKEN'];
    return $input['csrf_token'] ?? '';
}
