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
$postId = (int)($_POST['post_id'] ?? 0);

$stmt = $conn->prepare('SELECT id FROM community_likes WHERE post_id = ? AND user_id = ?');
$stmt->bind_param('ii', $postId, $user['id']);
$stmt->execute();
$stmt->store_result();
$alreadyLiked = $stmt->num_rows > 0;
$stmt->close();

if ($alreadyLiked) {
    $stmt = $conn->prepare('DELETE FROM community_likes WHERE post_id = ? AND user_id = ?');
    $stmt->bind_param('ii', $postId, $user['id']);
} else {
    $stmt = $conn->prepare('INSERT INTO community_likes (post_id, user_id) VALUES (?, ?)');
    $stmt->bind_param('ii', $postId, $user['id']);
}
$stmt->execute();
$stmt->close();

header('Location: community.php');
exit;
