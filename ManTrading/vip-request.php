<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: vip-class.php');
    exit;
}
csrf_verify();

$user = current_user();

$stmt = $conn->prepare('SELECT vip_status FROM users WHERE id = ?');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$stmt->bind_result($status);
$stmt->fetch();
$stmt->close();

if ($status === 'approved') {
    header('Location: vip-class.php');
    exit;
}

if ($status === 'pending') {
    flash_set('Pengajuan lu masih menunggu diproses admin.', 'error');
    header('Location: vip-class.php');
    exit;
}

// Update status user jadi pending
$stmt = $conn->prepare("UPDATE users SET vip_status = 'pending' WHERE id = ?");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$stmt->close();

// Catat log pengajuan
$stmt = $conn->prepare("INSERT INTO vip_requests (user_id, status) VALUES (?, 'pending')");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$stmt->close();

$_SESSION['vip_status'] = 'pending';
flash_set('Pengajuan akses VIP Class berhasil dikirim ke admin. Mohon tunggu persetujuan.', 'success');
header('Location: vip-class.php');
exit;
