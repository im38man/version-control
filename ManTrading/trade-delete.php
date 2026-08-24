<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
csrf_verify();

$user = current_user();
$id = (int)($_POST['id'] ?? 0);

$stmt = $conn->prepare('DELETE FROM trades WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $id, $user['id']);
$stmt->execute();
$stmt->close();

flash_set('Trade berhasil dihapus.', 'success');
header('Location: index.php');
exit;
