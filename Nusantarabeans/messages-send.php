<?php
require_once __DIR__ . '/includes/auth.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['status' => 'need_login']);
    exit;
}

$message = trim($_POST['message'] ?? '');
$image_path = null;

// Lokasi/daerah pengirim (opsional, dikirim otomatis dari GPS browser)
$sender_location = trim($_POST['location'] ?? '');
if ($sender_location === '' || mb_strlen($sender_location) > 150) {
    $sender_location = null;
}

// ================= UPLOAD FOTO (opsional, maks 1MB) =================
if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['image'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxSize = 1 * 1024 * 1024; // 1MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengunggah foto.']);
        exit;
    } elseif ($file['size'] > $maxSize) {
        echo json_encode(['status' => 'error', 'message' => 'Ukuran foto maksimal 1MB.']);
        exit;
    } elseif (!in_array($file['type'], $allowedTypes) || @getimagesize($file['tmp_name']) === false) {
        echo json_encode(['status' => 'error', 'message' => 'Format foto harus JPG, PNG, WEBP, atau GIF.']);
        exit;
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $safeName = 'pesan_' . $_SESSION['user_id'] . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destDir = __DIR__ . '/uploads/pesan/';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        if (move_uploaded_file($file['tmp_name'], $destDir . $safeName)) {
            $image_path = 'uploads/pesan/' . $safeName;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan foto.']);
            exit;
        }
    }
}

if ($message === '' && $image_path === null) {
    echo json_encode(['status' => 'error', 'message' => 'Pesan atau foto tidak boleh kosong.']);
    exit;
}

if (is_admin()) {
    $target_user_id = (int) ($_POST['user_id'] ?? 0);
    if ($target_user_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'User tujuan tidak valid.']);
        exit;
    }
    $stmt = $pdo->prepare("INSERT INTO messages (user_id, sender_role, sender_id, message, image_path, sender_location) VALUES (?, 'admin', ?, ?, ?, ?)");
    $stmt->execute([$target_user_id, $_SESSION['user_id'], $message, $image_path, $sender_location]);
} else {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("INSERT INTO messages (user_id, sender_role, sender_id, message, image_path, sender_location) VALUES (?, 'user', ?, ?, ?, ?)");
    $stmt->execute([$user_id, $user_id, $message, $image_path, $sender_location]);
}

echo json_encode(['status' => 'ok', 'id' => $pdo->lastInsertId(), 'image_path' => $image_path, 'sender_location' => $sender_location]);
