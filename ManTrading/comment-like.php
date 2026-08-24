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
$commentId = (int)($_POST['comment_id'] ?? 0);
$postId = (int)($_POST['post_id'] ?? 0);

if ($commentId > 0) {
    $stmt = $conn->prepare('SELECT id FROM community_comment_likes WHERE comment_id = ? AND user_id = ?');
    $stmt->bind_param('ii', $commentId, $user['id']);
    $stmt->execute();
    $stmt->store_result();
    $alreadyLiked = $stmt->num_rows > 0;
    $stmt->close();

    if ($alreadyLiked) {
        $stmt = $conn->prepare('DELETE FROM community_comment_likes WHERE comment_id = ? AND user_id = ?');
        $stmt->bind_param('ii', $commentId, $user['id']);
    } else {
        $stmt = $conn->prepare('INSERT INTO community_comment_likes (comment_id, user_id) VALUES (?, ?)');
        $stmt->bind_param('ii', $commentId, $user['id']);
    }
    $stmt->execute();
    $stmt->close();
}

header('Location: community.php#comments-' . $postId);
exit;
