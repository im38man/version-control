<?php
require_once __DIR__ . '/_bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET -> ambil semua pengingat milik user yang login
if ($method === 'GET') {
    $stmt = siapkanQuery($koneksi, "SELECT id, judul, waktu, notif_terkirim FROM reminders WHERE user_id = ? ORDER BY waktu ASC");
    mysqli_stmt_bind_param($stmt, "i", $USER_ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['notif_terkirim'] = (bool) $row['notif_terkirim'];
        $items[] = $row;
    }
    kirimJSON(['success' => true, 'data' => $items]);
}

// POST -> tambah pengingat baru
if ($method === 'POST') {
    $input = ambilInputJSON();
    $judul = trim($input['judul'] ?? '');
    $waktu = $input['waktu'] ?? '';

    if ($judul === '' || $waktu === '') {
        kirimJSON(['success' => false, 'message' => 'Judul dan waktu wajib diisi'], 400);
    }

    // input datetime-local formatnya "YYYY-MM-DDTHH:MM" -> ubah ke format MySQL
    $waktuMysql = str_replace('T', ' ', $waktu) . ':00';

    $stmt = siapkanQuery($koneksi, "INSERT INTO reminders (user_id, judul, waktu) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iss", $USER_ID, $judul, $waktuMysql);
    mysqli_stmt_execute($stmt);
    $newId = mysqli_insert_id($koneksi);

    kirimJSON(['success' => true, 'data' => ['id' => $newId, 'judul' => $judul, 'waktu' => $waktuMysql, 'notif_terkirim' => false]]);
}

// PUT -> tandai pengingat sudah bunyi (notif_terkirim = 1)
if ($method === 'PUT') {
    $input = ambilInputJSON();
    $id = (int) ($input['id'] ?? 0);
    if ($id <= 0) kirimJSON(['success' => false, 'message' => 'ID tidak valid'], 400);

    $stmt = siapkanQuery($koneksi, "UPDATE reminders SET notif_terkirim = 1 WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $USER_ID);
    mysqli_stmt_execute($stmt);
    kirimJSON(['success' => true]);
}

// DELETE -> hapus pengingat (harus milik user yang login)
if ($method === 'DELETE') {
    $input = ambilInputJSON();
    $id = (int) ($input['id'] ?? 0);
    if ($id <= 0) kirimJSON(['success' => false, 'message' => 'ID tidak valid'], 400);

    $stmt = siapkanQuery($koneksi, "DELETE FROM reminders WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $USER_ID);
    mysqli_stmt_execute($stmt);
    kirimJSON(['success' => true]);
}

kirimJSON(['success' => false, 'message' => 'Method tidak didukung'], 405);
