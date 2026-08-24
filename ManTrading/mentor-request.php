<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: community.php');
    exit;
}
csrf_verify();

$user = current_user();
$message = trim($_POST['message'] ?? '');

$stmt = $conn->prepare('SELECT mentor_status FROM users WHERE id = ?');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$stmt->bind_result($status);
$stmt->fetch();
$stmt->close();

if ($status === 'approved') {
    flash_set('Lu udah jadi Mentor bro, langsung aja posting di Community.', 'info');
    header('Location: community.php');
    exit;
}

if ($status === 'pending') {
    flash_set('Pengajuan Mentor lu masih menunggu diproses admin.', 'error');
    header('Location: community.php');
    exit;
}

if (is_admin()) {
    flash_set('Lu udah admin, otomatis bisa posting di Community.', 'info');
    header('Location: community.php');
    exit;
}

// Update status user jadi pending
$stmt = $conn->prepare("UPDATE users SET mentor_status = 'pending' WHERE id = ?");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$stmt->close();

// Catat log pengajuan (simpan pesan alasan/portofolio singkat dari user)
$stmt = $conn->prepare("INSERT INTO mentor_requests (user_id, status, message) VALUES (?, 'pending', ?)");
$stmt->bind_param('is', $user['id'], $message);
$stmt->execute();
$stmt->close();

$_SESSION['mentor_status'] = 'pending';
flash_set('Pengajuan jadi Mentor berhasil dikirim ke admin. Mohon tunggu persetujuan.', 'success');
header('Location: community.php');
exit;
