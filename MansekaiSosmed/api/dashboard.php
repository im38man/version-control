<?php
require_once __DIR__ . '/_bootstrap.php';

$method   = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? '';

// ============ TODO LIST (dash_tasks) ============
if ($resource === 'tasks') {
    if ($method === 'GET') {
        $stmt = siapkanQuery($koneksi, "SELECT id, teks, selesai FROM dash_tasks WHERE user_id = ? ORDER BY id ASC");
        mysqli_stmt_bind_param($stmt, "i", $USER_ID);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $items = [];
        while ($row = mysqli_fetch_assoc($res)) { $row['selesai'] = (bool)$row['selesai']; $items[] = $row; }
        kirimJSON(['success' => true, 'data' => $items]);
    }
    if ($method === 'POST') {
        $input = ambilInputJSON();
        $teks = trim($input['teks'] ?? '');
        if ($teks === '') kirimJSON(['success' => false, 'message' => 'Teks tugas wajib diisi'], 400);
        $stmt = siapkanQuery($koneksi, "INSERT INTO dash_tasks (user_id, teks) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "is", $USER_ID, $teks);
        mysqli_stmt_execute($stmt);
        kirimJSON(['success' => true, 'data' => ['id' => mysqli_insert_id($koneksi), 'teks' => $teks, 'selesai' => false]]);
    }
    if ($method === 'PUT') {
        $input = ambilInputJSON();
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) kirimJSON(['success' => false, 'message' => 'ID tidak valid'], 400);
        if (array_key_exists('teks', $input)) {
            $teks = trim($input['teks']);
            $stmt = siapkanQuery($koneksi, "UPDATE dash_tasks SET teks = ? WHERE id = ? AND user_id = ?");
            mysqli_stmt_bind_param($stmt, "sii", $teks, $id, $USER_ID);
            mysqli_stmt_execute($stmt);
        }
        if (array_key_exists('selesai', $input)) {
            $selesai = $input['selesai'] ? 1 : 0;
            $stmt = siapkanQuery($koneksi, "UPDATE dash_tasks SET selesai = ? WHERE id = ? AND user_id = ?");
            mysqli_stmt_bind_param($stmt, "iii", $selesai, $id, $USER_ID);
            mysqli_stmt_execute($stmt);
        }
        kirimJSON(['success' => true]);
    }
    if ($method === 'DELETE') {
        $input = ambilInputJSON();
        $id = (int)($input['id'] ?? 0);
        $stmt = siapkanQuery($koneksi, "DELETE FROM dash_tasks WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $id, $USER_ID);
        mysqli_stmt_execute($stmt);
        kirimJSON(['success' => true]);
    }
}

// ============ MODUL TERBARU (dash_modules) ============
if ($resource === 'modules') {
    if ($method === 'GET') {
        $stmt = siapkanQuery($koneksi, "SELECT id, nama, status FROM dash_modules WHERE user_id = ? ORDER BY id DESC");
        mysqli_stmt_bind_param($stmt, "i", $USER_ID);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $items = [];
        while ($row = mysqli_fetch_assoc($res)) { $items[] = $row; }
        kirimJSON(['success' => true, 'data' => $items]);
    }
    if ($method === 'POST') {
        $input = ambilInputJSON();
        $nama = trim($input['nama'] ?? '');
        $status = in_array($input['status'] ?? '', ['Selesai','Proses','Locked']) ? $input['status'] : 'Proses';
        if ($nama === '') kirimJSON(['success' => false, 'message' => 'Nama modul wajib diisi'], 400);
        $stmt = siapkanQuery($koneksi, "INSERT INTO dash_modules (user_id, nama, status) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iss", $USER_ID, $nama, $status);
        mysqli_stmt_execute($stmt);
        kirimJSON(['success' => true, 'data' => ['id' => mysqli_insert_id($koneksi), 'nama' => $nama, 'status' => $status]]);
    }
    if ($method === 'PUT') {
        $input = ambilInputJSON();
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) kirimJSON(['success' => false, 'message' => 'ID tidak valid'], 400);
        if (array_key_exists('nama', $input)) {
            $nama = trim($input['nama']);
            $stmt = siapkanQuery($koneksi, "UPDATE dash_modules SET nama = ? WHERE id = ? AND user_id = ?");
            mysqli_stmt_bind_param($stmt, "sii", $nama, $id, $USER_ID);
            mysqli_stmt_execute($stmt);
        }
        if (array_key_exists('status', $input) && in_array($input['status'], ['Selesai','Proses','Locked'])) {
            $status = $input['status'];
            $stmt = siapkanQuery($koneksi, "UPDATE dash_modules SET status = ? WHERE id = ? AND user_id = ?");
            mysqli_stmt_bind_param($stmt, "sii", $status, $id, $USER_ID);
            mysqli_stmt_execute($stmt);
        }
        kirimJSON(['success' => true]);
    }
    if ($method === 'DELETE') {
        $input = ambilInputJSON();
        $id = (int)($input['id'] ?? 0);
        $stmt = siapkanQuery($koneksi, "DELETE FROM dash_modules WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $id, $USER_ID);
        mysqli_stmt_execute($stmt);
        kirimJSON(['success' => true]);
    }
}

// ============ JADWAL BELAJAR HARIAN (dash_jadwal) ============
if ($resource === 'jadwal') {
    if ($method === 'GET') {
        $stmt = siapkanQuery($koneksi, "SELECT id, nama, jam FROM dash_jadwal WHERE user_id = ? ORDER BY id ASC");
        mysqli_stmt_bind_param($stmt, "i", $USER_ID);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $items = [];
        while ($row = mysqli_fetch_assoc($res)) { $items[] = $row; }
        kirimJSON(['success' => true, 'data' => $items]);
    }
    if ($method === 'POST') {
        $input = ambilInputJSON();
        $nama = trim($input['nama'] ?? '');
        $jam  = trim($input['jam'] ?? '');
        if ($nama === '' || $jam === '') kirimJSON(['success' => false, 'message' => 'Nama dan jam wajib diisi'], 400);
        $stmt = siapkanQuery($koneksi, "INSERT INTO dash_jadwal (user_id, nama, jam) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iss", $USER_ID, $nama, $jam);
        mysqli_stmt_execute($stmt);
        kirimJSON(['success' => true, 'data' => ['id' => mysqli_insert_id($koneksi), 'nama' => $nama, 'jam' => $jam]]);
    }
    if ($method === 'PUT') {
        $input = ambilInputJSON();
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) kirimJSON(['success' => false, 'message' => 'ID tidak valid'], 400);
        $nama = trim($input['nama'] ?? '');
        $jam  = trim($input['jam'] ?? '');
        $stmt = siapkanQuery($koneksi, "UPDATE dash_jadwal SET nama = ?, jam = ? WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "ssii", $nama, $jam, $id, $USER_ID);
        mysqli_stmt_execute($stmt);
        kirimJSON(['success' => true]);
    }
    if ($method === 'DELETE') {
        $input = ambilInputJSON();
        $id = (int)($input['id'] ?? 0);
        $stmt = siapkanQuery($koneksi, "DELETE FROM dash_jadwal WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $id, $USER_ID);
        mysqli_stmt_execute($stmt);
        kirimJSON(['success' => true]);
    }
}

// ============ TARGET PEMBELAJARAN (dash_target) ============
if ($resource === 'target') {
    if ($method === 'GET') {
        $stmt = siapkanQuery($koneksi, "SELECT id, nama, persen FROM dash_target WHERE user_id = ? ORDER BY id ASC");
        mysqli_stmt_bind_param($stmt, "i", $USER_ID);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $items = [];
        while ($row = mysqli_fetch_assoc($res)) { $row['persen'] = (int)$row['persen']; $items[] = $row; }
        kirimJSON(['success' => true, 'data' => $items]);
    }
    if ($method === 'POST') {
        $input = ambilInputJSON();
        $nama = trim($input['nama'] ?? '');
        $persen = max(0, min(100, (int)($input['persen'] ?? 0)));
        if ($nama === '') kirimJSON(['success' => false, 'message' => 'Nama target wajib diisi'], 400);
        $stmt = siapkanQuery($koneksi, "INSERT INTO dash_target (user_id, nama, persen) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isi", $USER_ID, $nama, $persen);
        mysqli_stmt_execute($stmt);
        kirimJSON(['success' => true, 'data' => ['id' => mysqli_insert_id($koneksi), 'nama' => $nama, 'persen' => $persen]]);
    }
    if ($method === 'PUT') {
        $input = ambilInputJSON();
        $id = (int)($input['id'] ?? 0);
        if ($id <= 0) kirimJSON(['success' => false, 'message' => 'ID tidak valid'], 400);
        $nama = trim($input['nama'] ?? '');
        $persen = max(0, min(100, (int)($input['persen'] ?? 0)));
        $stmt = siapkanQuery($koneksi, "UPDATE dash_target SET nama = ?, persen = ? WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "siii", $nama, $persen, $id, $USER_ID);
        mysqli_stmt_execute($stmt);
        kirimJSON(['success' => true]);
    }
    if ($method === 'DELETE') {
        $input = ambilInputJSON();
        $id = (int)($input['id'] ?? 0);
        $stmt = siapkanQuery($koneksi, "DELETE FROM dash_target WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $id, $USER_ID);
        mysqli_stmt_execute($stmt);
        kirimJSON(['success' => true]);
    }
}

// ============ WINSTREAK BELAJAR (dash_streak) ============
if ($resource === 'streak') {
    if ($method === 'GET') {
        $stmt = siapkanQuery($koneksi, "SELECT streak_count, last_claim_date FROM dash_streak WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $USER_ID);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        kirimJSON(['success' => true, 'data' => [
            'streak_count' => $row['streak_count'] ?? 0,
            'last_claim_date' => $row['last_claim_date'] ?? null,
        ]]);
    }
    if ($method === 'POST') {
        // Klaim winstreak hari ini
        $stmt = siapkanQuery($koneksi, "SELECT streak_count, last_claim_date FROM dash_streak WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $USER_ID);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        $today = date('Y-m-d');
        $streak = $row['streak_count'] ?? 0;
        $lastClaim = $row['last_claim_date'] ?? null;

        if ($lastClaim === $today) {
            kirimJSON(['success' => false, 'message' => 'Winstreak hari ini sudah diklaim', 'data' => ['streak_count' => $streak, 'last_claim_date' => $lastClaim]]);
        }

        if ($lastClaim) {
            $diffDays = (strtotime($today) - strtotime($lastClaim)) / 86400;
            if ($diffDays > 1) $streak = 0;
        }
        $streak += 1;

        $stmt2 = siapkanQuery($koneksi, "
            INSERT INTO dash_streak (user_id, streak_count, last_claim_date) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE streak_count = VALUES(streak_count), last_claim_date = VALUES(last_claim_date)
        ");
        mysqli_stmt_bind_param($stmt2, "iis", $USER_ID, $streak, $today);
        mysqli_stmt_execute($stmt2);

        kirimJSON(['success' => true, 'data' => ['streak_count' => $streak, 'last_claim_date' => $today]]);
    }
}

kirimJSON(['success' => false, 'message' => 'Resource tidak dikenali'], 404);
