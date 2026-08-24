<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = (int) ($_POST['item_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    // pastikan item ini memang milik user yang sedang login
    $stmt = $pdo->prepare('SELECT * FROM cart_items WHERE id = ? AND user_id = ?');
    $stmt->execute([$item_id, $user_id]);
    $item = $stmt->fetch();

    if ($item) {
        if ($action === 'plus') {
            $stmt = $pdo->prepare('UPDATE cart_items SET qty = qty + 1 WHERE id = ?');
            $stmt->execute([$item_id]);
        } elseif ($action === 'minus') {
            if ($item['qty'] > 1) {
                $stmt = $pdo->prepare('UPDATE cart_items SET qty = qty - 1 WHERE id = ?');
                $stmt->execute([$item_id]);
            }
        } elseif ($action === 'remove') {
            $stmt = $pdo->prepare('DELETE FROM cart_items WHERE id = ?');
            $stmt->execute([$item_id]);
        }
    }
}

header('Location: checkout.php');
exit;
