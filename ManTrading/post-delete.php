<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_mentor_or_admin(); // admin bisa hapus postingan siapapun, mentor cuma postingan sendiri

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: community.php');
    exit;
}
csrf_verify();

$user = current_user();
$id = (int)($_POST['id'] ?? 0);

// Hapus file fisik foto sebelum hapus row
$stmt = $conn->prepare('SELECT user_id, image_path FROM community_posts WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    flash_set('Postingan tidak ditemukan.', 'error');
    header('Location: community.php');
    exit;
}

// Mentor cuma boleh hapus postingan sendiri, admin boleh hapus semua
if (!is_admin() && (int)$row['user_id'] !== $user['id']) {
    flash_set('Lu cuma bisa hapus postingan sendiri, bro.', 'error');
    header('Location: community.php');
    exit;
}

$filePath = UPLOAD_DIR . $row['image_path'];
if (is_file($filePath)) {
    @unlink($filePath);
}

$stmt = $conn->prepare('DELETE FROM community_posts WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

flash_set('Postingan berhasil dihapus.', 'success');
header('Location: community.php');
exit;
