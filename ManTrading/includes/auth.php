<?php
/**
 * ManTrading - Helper Otentikasi & Proteksi Halaman
 * File ini WAJIB di-include setelah config.php
 */

function current_user(): ?array {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id'            => (int)$_SESSION['user_id'],
        'full_name'     => $_SESSION['full_name'],
        'email'         => $_SESSION['email'],
        'role'          => $_SESSION['role'],
        'vip_status'    => $_SESSION['vip_status'],
        'mentor_status' => $_SESSION['mentor_status'] ?? 'none',
    ];
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function is_admin(): bool {
    return is_logged_in() && $_SESSION['role'] === 'admin';
}

/** Mentor yang sudah di-approve admin (bukan admin, tapi boleh posting Community) */
function is_mentor(): bool {
    return is_logged_in() && ($_SESSION['mentor_status'] ?? 'none') === 'approved';
}

/** Admin ATAU mentor approved — dua-duanya boleh posting di Community */
function can_post_community(): bool {
    return is_admin() || is_mentor();
}

/** Panggil di paling atas halaman yang wajib login */
function require_login(): void {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/** Panggil di paling atas halaman khusus admin */
function require_admin(): void {
    require_login();
    if (!is_admin()) {
        header('Location: index.php');
        exit;
    }
}

/** Panggil di paling atas proses yang cuma boleh admin ATAU mentor approved (mis. posting community) */
function require_mentor_or_admin(): void {
    require_login();
    if (!can_post_community()) {
        header('Location: index.php');
        exit;
    }
}

/** Sinkronkan vip_status di session dengan data terbaru di DB (dipanggil tiap load halaman VIP) */
function refresh_vip_status(mysqli $conn, int $userId): string {
    $stmt = $conn->prepare('SELECT vip_status FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($status);
    $stmt->fetch();
    $stmt->close();
    $_SESSION['vip_status'] = $status;
    return $status;
}

/** Sinkronkan mentor_status di session dengan data terbaru di DB (dipanggil tiap load halaman Community) */
function refresh_mentor_status(mysqli $conn, int $userId): string {
    $stmt = $conn->prepare('SELECT mentor_status FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($status);
    $stmt->fetch();
    $stmt->close();
    $_SESSION['mentor_status'] = $status;
    return $status;
}

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/** Format angka desimal dari MySQL (mis. "4000.00000") jadi bersih (mis. "4000") tanpa nol di belakang */
function n($val): string {
    if ($val === null || $val === '') return '';
    $formatted = rtrim(rtrim(sprintf('%.8f', (float)$val), '0'), '.');
    return $formatted === '' || $formatted === '-' ? '0' : $formatted;
}

function flash_set(string $msg, string $type = 'info'): void {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function flash_get(): ?array {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

/** Ambil (atau buat baru) token CSRF untuk session ini */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Cetak hidden input CSRF, taruh di dalam setiap <form method="POST"> */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Panggil di paling atas proses POST (setelah require_login/require_admin) */
function csrf_verify(): void {
    $sent = $_POST['csrf_token'] ?? '';
    $valid = !empty($_SESSION['csrf_token']) && is_string($sent) && hash_equals($_SESSION['csrf_token'], $sent);
    if (!$valid) {
        http_response_code(403);
        die('Sesi form sudah kadaluarsa atau tidak valid (CSRF check gagal). Silakan kembali, refresh halaman, dan coba lagi.');
    }
}
