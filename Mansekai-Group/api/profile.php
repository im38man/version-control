<?php
require_once __DIR__ . '/../lib.php';
require_login_api();

$ownerId = (int) current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("SELECT name, username, email, role_title, avatar, recovery_email, recovery_phone, socials FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $ownerId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) json_out(['ok' => false, 'error' => 'User tidak ditemukan.'], 404);

    json_out(['ok' => true, 'profile' => [
        'name' => $row['name'],
        'username' => $row['username'],
        'role' => $row['role_title'],
        'avatar' => $row['avatar'],
        'recoveryEmail' => $row['recovery_email'] ?: $row['email'],
        'recoveryPhone' => $row['recovery_phone'],
        'socials' => $row['socials'] ? json_decode($row['socials'], true) : new stdClass(),
    ]]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_input();
    if (!csrf_verify($body['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''))) {
        json_out(['ok' => false, 'error' => 'Sesi tidak valid, silakan muat ulang halaman.'], 419);
    }

    $name = trim($body['name'] ?? '');
    $role = trim($body['role'] ?? '');
    $avatar = trim($body['avatar'] ?? '');
    $recoveryEmail = trim($body['recoveryEmail'] ?? '');
    $recoveryPhone = trim($body['recoveryPhone'] ?? '');
    $socials = $body['socials'] ?? [];
    $newPassword = (string)($body['newPassword'] ?? '');

    if ($name === '') json_out(['ok' => false, 'error' => 'Nama tidak boleh kosong.']);
    if (!$avatar) $avatar = avatar_url($name);
    $socialsJson = json_encode($socials, JSON_UNESCAPED_UNICODE);

    if ($newPassword !== '') {
        if (strlen($newPassword) < 6) json_out(['ok' => false, 'error' => 'Password baru minimal 6 karakter.']);
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name=?, role_title=?, avatar=?, recovery_email=?, recovery_phone=?, socials=?, password_hash=? WHERE id=?");
        $stmt->bind_param('sssssssi', $name, $role, $avatar, $recoveryEmail, $recoveryPhone, $socialsJson, $hash, $ownerId);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, role_title=?, avatar=?, recovery_email=?, recovery_phone=?, socials=? WHERE id=?");
        $stmt->bind_param('ssssssi', $name, $role, $avatar, $recoveryEmail, $recoveryPhone, $socialsJson, $ownerId);
    }

    $ok = $stmt->execute();
    json_out(['ok' => $ok, 'avatar' => $avatar]);
}

json_out(['ok' => false, 'error' => 'Method tidak didukung.'], 405);
