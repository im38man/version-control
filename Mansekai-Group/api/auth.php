<?php
require_once __DIR__ . '/../lib.php';

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
$body = json_input();

// Semua aksi POST wajib bawa CSRF token yang valid
if (!csrf_verify($body['csrf'] ?? '')) {
    json_out(['ok' => false, 'error' => 'Sesi tidak valid, silakan muat ulang halaman.'], 419);
}

if ($action === 'login') {
    $identity = trim($body['identity'] ?? ''); // email atau username
    $password = (string)($body['password'] ?? '');

    if ($identity === '' || $password === '') {
        json_out(['ok' => false, 'error' => 'Email/Username dan password wajib diisi.']);
    }

    $stmt = $conn->prepare("SELECT id, name, password_hash FROM users WHERE email = ? OR username = ? LIMIT 1");
    $stmt->bind_param('ss', $identity, $identity);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        json_out(['ok' => false, 'error' => 'Email/Username atau password salah.']);
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    json_out(['ok' => true, 'name' => $user['name']]);
}

elseif ($action === 'register') {
    $name = trim($body['name'] ?? '');
    $identity = trim($body['email'] ?? ''); // dipakai sebagai email + basis username
    $password = (string)($body['password'] ?? '');
    $confirm = (string)($body['confirm'] ?? '');

    if ($name === '' || $identity === '' || $password === '') {
        json_out(['ok' => false, 'error' => 'Semua field wajib diisi.']);
    }
    if ($password !== $confirm) {
        json_out(['ok' => false, 'error' => 'Password dan konfirmasi password tidak cocok!']);
    }
    if (strlen($password) < 6) {
        json_out(['ok' => false, 'error' => 'Password minimal 6 karakter.']);
    }

    // Terima input berupa email ATAU username sebagai "Email/Username" (mengikuti form asli)
    $isEmail = filter_var($identity, FILTER_VALIDATE_EMAIL) !== false;
    $email = $isEmail ? $identity : ($identity . '@local.mansekai');
    $username = $isEmail ? strtolower(explode('@', $identity)[0]) . '_' . substr(md5(uniqid('', true)), 0, 4) : $identity;

    $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
    $check->bind_param('ss', $email, $username);
    $check->execute();
    if ($check->get_result()->fetch_assoc()) {
        json_out(['ok' => false, 'error' => 'Email/Username sudah terdaftar, silakan gunakan yang lain.']);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $avatar = avatar_url($name);

    $ins = $conn->prepare("INSERT INTO users (name, username, email, password_hash, role_title, avatar) VALUES (?, ?, ?, ?, 'Lead Developer', ?)");
    $ins->bind_param('sssss', $name, $username, $email, $hash, $avatar);

    if (!$ins->execute()) {
        json_out(['ok' => false, 'error' => 'Registrasi gagal, silakan coba lagi.']);
    }

    $newId = $conn->insert_id;
    seed_default_data($conn, $newId, $name);

    json_out(['ok' => true]);
}

elseif ($action === 'request_reset') {
    $email = trim($body['email'] ?? '');
    if ($email === '') json_out(['ok' => false, 'error' => 'Email wajib diisi.']);

    $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ? OR recovery_email = ? LIMIT 1");
    $stmt->bind_param('ss', $email, $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    // Selalu balas sukses (walau email tidak ditemukan) supaya tidak bocor data akun mana yang terdaftar
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $ins = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $ins->bind_param('iss', $user['id'], $token, $expires);
        $ins->execute();

        $resetLink = rtrim(APP_URL, '/') . '/reset-password.php?token=' . $token;
        $sent = send_email_brevo($email, $user['name'], 'Reset Password - ' . APP_NAME,
            "<p>Halo {$user['name']},</p><p>Klik link berikut untuk reset password (berlaku 1 jam):</p><p><a href='{$resetLink}'>{$resetLink}</a></p><p>Abaikan email ini jika kamu tidak meminta reset password.</p>");

        if (!$sent) {
            // fallback dev: kalau Brevo belum dikonfigurasi, kembalikan link langsung
            json_out(['ok' => true, 'dev_link' => $resetLink]);
        }
    }

    json_out(['ok' => true]);
}

elseif ($action === 'do_reset') {
    $token = trim($body['token'] ?? '');
    $password = (string)($body['password'] ?? '');
    if ($token === '' || $password === '') json_out(['ok' => false, 'error' => 'Data tidak lengkap.']);
    if (strlen($password) < 6) json_out(['ok' => false, 'error' => 'Password minimal 6 karakter.']);

    $stmt = $conn->prepare("SELECT id, user_id, expires_at, used FROM password_resets WHERE token = ? LIMIT 1");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row || $row['used'] || strtotime($row['expires_at']) < time()) {
        json_out(['ok' => false, 'error' => 'Link reset tidak valid atau sudah kedaluwarsa.']);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $upd = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $upd->bind_param('si', $hash, $row['user_id']);
    $upd->execute();

    $mark = $conn->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
    $mark->bind_param('i', $row['id']);
    $mark->execute();

    json_out(['ok' => true]);
}

else {
    json_out(['ok' => false, 'error' => 'Aksi tidak dikenal.'], 400);
}
