<?php
require_once __DIR__ . '/_bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET -> ambil semua catatan milik user yang login
if ($method === 'GET') {
    $stmt = siapkanQuery($koneksi, "SELECT id, judul, isi FROM notes WHERE user_id = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, "i", $USER_ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $notes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $notes[] = $row;
    }
    kirimJSON(['success' => true, 'data' => $notes]);
}

// POST -> tambah catatan baru
if ($method === 'POST') {
    $stmt = siapkanQuery($koneksi, "INSERT INTO notes (user_id, judul, isi) VALUES (?, 'Catatan Baru', '')");
    mysqli_stmt_bind_param($stmt, "i", $USER_ID);
    mysqli_stmt_execute($stmt);
    $newId = mysqli_insert_id($koneksi);
    kirimJSON(['success' => true, 'data' => ['id' => $newId, 'judul' => 'Catatan Baru', 'isi' => '']]);
}

// PUT -> update judul/isi catatan (harus milik user yang login)
if ($method === 'PUT') {
    $input = ambilInputJSON();
    $id    = (int) ($input['id'] ?? 0);
    $judul = $input['judul'] ?? null;
    $isi   = $input['isi'] ?? null;

    if ($id <= 0) kirimJSON(['success' => false, 'message' => 'ID tidak valid'], 400);

    if ($judul !== null && $isi !== null) {
        $stmt = siapkanQuery($koneksi, "UPDATE notes SET judul = ?, isi = ? WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "ssii", $judul, $isi, $id, $USER_ID);
    } elseif ($judul !== null) {
        $stmt = siapkanQuery($koneksi, "UPDATE notes SET judul = ? WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "sii", $judul, $id, $USER_ID);
    } elseif ($isi !== null) {
        $stmt = siapkanQuery($koneksi, "UPDATE notes SET isi = ? WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "sii", $isi, $id, $USER_ID);
    } else {
        kirimJSON(['success' => false, 'message' => 'Tidak ada data untuk diupdate'], 400);
    }
    mysqli_stmt_execute($stmt);
    kirimJSON(['success' => true]);
}

// DELETE -> hapus catatan (harus milik user yang login)
if ($method === 'DELETE') {
    $input = ambilInputJSON();
    $id = (int) ($input['id'] ?? 0);
    if ($id <= 0) kirimJSON(['success' => false, 'message' => 'ID tidak valid'], 400);

    $stmt = siapkanQuery($koneksi, "DELETE FROM notes WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $USER_ID);
    mysqli_stmt_execute($stmt);
    kirimJSON(['success' => true]);
}

kirimJSON(['success' => false, 'message' => 'Method tidak didukung'], 405);
