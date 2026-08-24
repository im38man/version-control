<?php
require_once __DIR__ . '/../lib.php';
require_login_api();

$ownerId = (int) current_user_id();
$key = $_GET['key'] ?? '';

if (!in_array($key, ud_allowed_keys(), true)) {
    json_out(['ok' => false, 'error' => 'Data key tidak dikenal.'], 400);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    json_out(['ok' => true, 'data' => ud_get($conn, $ownerId, $key, [])]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_input();
    if (!csrf_verify($body['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''))) {
        json_out(['ok' => false, 'error' => 'Sesi tidak valid, silakan muat ulang halaman.'], 419);
    }
    $value = $body['data'] ?? null;
    if ($value === null) {
        json_out(['ok' => false, 'error' => 'Data kosong.'], 400);
    }
    // Batas ukuran sederhana biar aman di hosting gratis (± 2MB per modul data)
    if (strlen(json_encode($value)) > 2 * 1024 * 1024) {
        json_out(['ok' => false, 'error' => 'Data terlalu besar untuk disimpan.'], 413);
    }
    $ok = ud_set($conn, $ownerId, $key, $value);
    json_out(['ok' => $ok]);
}

json_out(['ok' => false, 'error' => 'Method tidak didukung.'], 405);
