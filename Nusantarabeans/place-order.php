<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');

// Ambil hanya item keranjang yang dicentang/dipilih user di halaman checkout
$selected_ids = $_POST['selected_items'] ?? [];
$selected_ids = array_filter(array_map('intval', (array)$selected_ids));

if (!empty($selected_ids)) {
    $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
    $params = array_merge([$user_id], $selected_ids);
    $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE user_id = ? AND id IN ($placeholders)");
    $stmt->execute($params);
    $cart = $stmt->fetchAll();
} else {
    $cart = [];
}

$errors = [];
if ($full_name === '' || $phone === '' || $address === '') {
    $errors[] = 'Nama, nomor HP, dan alamat pengiriman wajib diisi.';
}
if (empty($cart)) {
    $errors[] = 'Pilih minimal satu produk di keranjang untuk checkout.';
}

$proof_path = null;
if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] === UPLOAD_ERR_NO_FILE) {
    $errors[] = 'Bukti transfer wajib diunggah.';
} elseif ($_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'Gagal mengunggah bukti transfer.';
} else {
    $file = $_FILES['payment_proof'];
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
    $maxSize = 3 * 1024 * 1024; // 3MB

    if (!in_array($file['type'], $allowed)) {
        $errors[] = 'Format bukti transfer harus JPG, PNG, WEBP, atau PDF.';
    } elseif ($file['size'] > $maxSize) {
        $errors[] = 'Ukuran bukti transfer maksimal 3MB.';
    } else {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName = 'bukti_' . $user_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destDir = __DIR__ . '/uploads/bukti-transfer/';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        if (move_uploaded_file($file['tmp_name'], $destDir . $safeName)) {
            $proof_path = 'uploads/bukti-transfer/' . $safeName;
        } else {
            $errors[] = 'Gagal menyimpan file bukti transfer.';
        }
    }
}

if (!empty($errors)) {
    $_SESSION['checkout_errors'] = $errors;
    header('Location: checkout.php');
    exit;
}

$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['qty'];
}
$shipping = 15000;
$pph = round($total * 0.02);
$grandTotal = $total + $shipping + $pph;

$order_code = 'NB-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('INSERT INTO orders (order_code, user_id, full_name, phone, shipping_address, payment_method, payment_proof, total_amount, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$order_code, $user_id, $full_name, $phone, $address, 'transfer_manual', $proof_path, $grandTotal, 'menunggu_konfirmasi']);
    $order_id = $pdo->lastInsertId();

    $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, product_name, product_image, price, qty, subtotal) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($cart as $item) {
        $subtotal = $item['price'] * $item['qty'];
        $stmtItem->execute([$order_id, $item['product_name'], $item['product_image'], $item['price'], $item['qty'], $subtotal]);
    }

    // Hapus dari keranjang hanya item yang tadi dipilih & sudah masuk pesanan
    $cartIds = array_column($cart, 'id');
    $delPlaceholders = implode(',', array_fill(0, count($cartIds), '?'));
    $delParams = array_merge([$user_id], $cartIds);
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND id IN ($delPlaceholders)");
    $stmt->execute($delParams);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['checkout_errors'] = ['Terjadi kesalahan saat menyimpan pesanan. Coba lagi.'];
    header('Location: checkout.php');
    exit;
}

header('Location: pesanan-sukses.php?order=' . urlencode($order_code));
exit;
