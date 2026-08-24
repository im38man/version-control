<?php
require_once __DIR__ . '/includes/auth.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'need_login']);
    exit;
}

$name = trim($_POST['product_name'] ?? '');
$img = trim($_POST['product_image'] ?? '');
$price = (float) ($_POST['price'] ?? 0);
$qty = max(1, (int) ($_POST['qty'] ?? 1));

if ($name === '' || $price <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Data produk tidak valid.']);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT id, qty FROM cart_items WHERE user_id = ? AND product_name = ?');
$stmt->execute([$user_id, $name]);
$existing = $stmt->fetch();

if ($existing) {
    $stmt = $pdo->prepare('UPDATE cart_items SET qty = qty + ? WHERE id = ?');
    $stmt->execute([$qty, $existing['id']]);
} else {
    $stmt = $pdo->prepare('INSERT INTO cart_items (user_id, product_name, product_image, price, qty) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$user_id, $name, $img, $price, $qty]);
}

echo json_encode(['status' => 'ok']);
