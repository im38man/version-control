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
$parentId = (int)($_POST['parent_id'] ?? 0);
$text = trim($_POST['comment_text'] ?? '');

if ($text !== '' && $postId > 0) {
    $parentIdParam = null;

    // Kalau ini reply, pastikan parent-nya bener-bener komentar milik post yang sama
    if ($parentId > 0) {
        $stmt = $conn->prepare('SELECT id FROM community_comments WHERE id = ? AND post_id = ?');
        $stmt->bind_param('ii', $parentId, $postId);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $parentIdParam = $parentId;
        }
        $stmt->close();
    }

    $stmt = $conn->prepare('INSERT INTO community_comments (post_id, parent_id, user_id, comment_text) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('iiis', $postId, $parentIdParam, $user['id'], $text);
    $stmt->execute();
    $stmt->close();
}

header('Location: community.php#comments-' . $postId);
exit;
