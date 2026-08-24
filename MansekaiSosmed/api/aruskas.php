<?php
require_once __DIR__ . '/_bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET -> ambil semua transaksi milik user yang login
if ($method === 'GET') {
    $stmt = siapkanQuery($koneksi, "SELECT id, deskripsi, jumlah, tipe FROM cashflow WHERE user_id = ? ORDER BY id ASC");
    mysqli_stmt_bind_param($stmt, "i", $USER_ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['jumlah'] = (float) $row['jumlah'];
        $items[] = $row;
    }
    kirimJSON(['success' => true, 'data' => $items]);
}

// POST -> tambah transaksi baru
if ($method === 'POST') {
    $input = ambilInputJSON();
    $deskripsi = trim($input['deskripsi'] ?? '');
    $jumlah    = (float) ($input['jumlah'] ?? 0);
    $tipe      = ($input['tipe'] ?? '') === 'keluar' ? 'keluar' : 'masuk';

    if ($deskripsi === '' || $jumlah <= 0) {
        kirimJSON(['success' => false, 'message' => 'Keterangan dan nominal wajib diisi'], 400);
    }

    $stmt = siapkanQuery($koneksi, "INSERT INTO cashflow (user_id, deskripsi, jumlah, tipe) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isds", $USER_ID, $deskripsi, $jumlah, $tipe);
    mysqli_stmt_execute($stmt);
    $newId = mysqli_insert_id($koneksi);

    kirimJSON(['success' => true, 'data' => ['id' => $newId, 'deskripsi' => $deskripsi, 'jumlah' => $jumlah, 'tipe' => $tipe]]);
}

// DELETE -> hapus transaksi (harus milik user yang login)
if ($method === 'DELETE') {
    $input = ambilInputJSON();
    $id = (int) ($input['id'] ?? 0);
    if ($id <= 0) kirimJSON(['success' => false, 'message' => 'ID tidak valid'], 400);

    $stmt = siapkanQuery($koneksi, "DELETE FROM cashflow WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $USER_ID);
    mysqli_stmt_execute($stmt);
    kirimJSON(['success' => true]);
}

kirimJSON(['success' => false, 'message' => 'Method tidak didukung'], 405);
