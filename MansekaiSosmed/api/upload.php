<?php
require_once __DIR__ . '/_bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

$EKSTENSI_DIIZINKAN = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
$MAX_UKURAN = 10 * 1024 * 1024; // 10MB
$FOLDER_DOKUMEN = __DIR__ . '/../uploads/documents/' . $USER_ID . '/';

// GET -> daftar dokumen milik user yang login
if ($method === 'GET') {
    $stmt = siapkanQuery($koneksi, "SELECT id, nama_asli, ekstensi, ukuran, created_at FROM dokumen WHERE user_id = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, "i", $USER_ID);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $items = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $row['ukuran'] = (int) $row['ukuran'];
        $items[] = $row;
    }
    kirimJSON(['success' => true, 'data' => $items]);
}

// POST (multipart/form-data) -> upload file baru
if ($method === 'POST') {
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        kirimJSON(['success' => false, 'message' => 'Tidak ada file yang diunggah atau upload gagal.'], 400);
    }

    $file = $_FILES['file'];
    $namaAsli = $file['name'];
    $ukuran = $file['size'];
    $ekstensi = strtolower(pathinfo($namaAsli, PATHINFO_EXTENSION));

    if (!in_array($ekstensi, $EKSTENSI_DIIZINKAN)) {
        kirimJSON(['success' => false, 'message' => 'Format file tidak didukung. Hanya PDF, DOC, DOCX, XLS, XLSX.'], 400);
    }
    if ($ukuran > $MAX_UKURAN) {
        kirimJSON(['success' => false, 'message' => 'Ukuran file maksimal 10MB.'], 400);
    }

    if (!is_dir($FOLDER_DOKUMEN)) {
        mkdir($FOLDER_DOKUMEN, 0755, true);
    }

    // Nama file unik di server supaya tidak bentrok & tidak bisa ditebak orang lain
    $namaFile = bin2hex(random_bytes(16)) . '.' . $ekstensi;
    $pathTujuan = $FOLDER_DOKUMEN . $namaFile;

    if (!move_uploaded_file($file['tmp_name'], $pathTujuan)) {
        kirimJSON(['success' => false, 'message' => 'Gagal menyimpan file di server.'], 500);
    }

    $stmt = siapkanQuery($koneksi, "INSERT INTO dokumen (user_id, nama_asli, nama_file, ekstensi, ukuran) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isssi", $USER_ID, $namaAsli, $namaFile, $ekstensi, $ukuran);
    mysqli_stmt_execute($stmt);
    $newId = mysqli_insert_id($koneksi);

    kirimJSON(['success' => true, 'data' => [
        'id' => $newId, 'nama_asli' => $namaAsli, 'ekstensi' => $ekstensi,
        'ukuran' => $ukuran, 'created_at' => date('Y-m-d H:i:s'),
    ]]);
}

// DELETE -> hapus dokumen (harus milik user yang login)
if ($method === 'DELETE') {
    $input = ambilInputJSON();
    $id = (int) ($input['id'] ?? 0);
    if ($id <= 0) kirimJSON(['success' => false, 'message' => 'ID tidak valid'], 400);

    $stmt = siapkanQuery($koneksi, "SELECT nama_file FROM dokumen WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $USER_ID);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$row) kirimJSON(['success' => false, 'message' => 'Dokumen tidak ditemukan'], 404);

    $pathFile = $FOLDER_DOKUMEN . $row['nama_file'];
    if (is_file($pathFile)) unlink($pathFile);

    $stmtDel = siapkanQuery($koneksi, "DELETE FROM dokumen WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmtDel, "ii", $id, $USER_ID);
    mysqli_stmt_execute($stmtDel);

    kirimJSON(['success' => true]);
}

kirimJSON(['success' => false, 'message' => 'Method tidak didukung'], 405);
