<?php
require_once __DIR__ . '/_bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($method === 'POST' ? (ambilInputJSON()['action'] ?? '') : '');

// Avatar default, disamain sama yang dipakai api/profil.php
const AVATAR_DEFAULT = 'img.magnific.com/premium-vector/user-profile-icon-circle_1256048-12499.jpg?auto=format&fit=crop&w=200&q=80';

function formatUserRow($row, $avatarDefault) {
    return [
        'id'       => (int) $row['id'],
        'nama'     => $row['nama'],
        'username' => $row['username'],
        'avatar'   => $row['avatar'] ?: $avatarDefault,
    ];
}

// ==== GET: cari user / lihat daftar following / lihat daftar followers ====
if ($method === 'GET' && $action === 'search') {
    $q = trim($_GET['q'] ?? '');

    if ($q === '') {
        kirimJSON(['success' => true, 'data' => []]);
    }

    $like = '%' . $q . '%';
    $idExact = ctype_digit($q) ? (int) $q : -1;
    $stmt = siapkanQuery($koneksi, "
        SELECT u.id, u.nama, u.username, p.avatar,
               EXISTS(SELECT 1 FROM follows f WHERE f.follower_id = ? AND f.following_id = u.id) AS is_following
        FROM users u
        LEFT JOIN profil p ON p.user_id = u.id
        WHERE u.id != ? AND (u.nama LIKE ? OR u.username LIKE ? OR u.id = ?)
        ORDER BY u.nama ASC
        LIMIT 30
    ");
    mysqli_stmt_bind_param($stmt, "iissi", $USER_ID, $USER_ID, $like, $like, $idExact);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $u = formatUserRow($row, AVATAR_DEFAULT);
        $u['is_following'] = (bool) $row['is_following'];
        $data[] = $u;
    }

    kirimJSON(['success' => true, 'data' => $data]);
}

if ($method === 'GET' && $action === 'following') {
    $stmt = siapkanQuery($koneksi, "
        SELECT u.id, u.nama, u.username, p.avatar
        FROM follows f
        JOIN users u ON u.id = f.following_id
        LEFT JOIN profil p ON p.user_id = u.id
        WHERE f.follower_id = ?
        ORDER BY f.created_at DESC
    ");
    mysqli_stmt_bind_param($stmt, "i", $USER_ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = formatUserRow($row, AVATAR_DEFAULT);
    }

    kirimJSON(['success' => true, 'data' => $data]);
}

if ($method === 'GET' && $action === 'followers') {
    $stmt = siapkanQuery($koneksi, "
        SELECT u.id, u.nama, u.username, p.avatar
        FROM follows f
        JOIN users u ON u.id = f.follower_id
        LEFT JOIN profil p ON p.user_id = u.id
        WHERE f.following_id = ?
        ORDER BY f.created_at DESC
    ");
    mysqli_stmt_bind_param($stmt, "i", $USER_ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = formatUserRow($row, AVATAR_DEFAULT);
    }

    kirimJSON(['success' => true, 'data' => $data]);
}

if ($method === 'GET' && $action === 'ringkasan') {
    $stmtA = siapkanQuery($koneksi, "SELECT COUNT(*) AS jumlah FROM follows WHERE follower_id = ?");
    mysqli_stmt_bind_param($stmtA, "i", $USER_ID);
    mysqli_stmt_execute($stmtA);
    $jumlahMengikuti = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtA))['jumlah'];

    $stmtB = siapkanQuery($koneksi, "SELECT COUNT(*) AS jumlah FROM follows WHERE following_id = ?");
    mysqli_stmt_bind_param($stmtB, "i", $USER_ID);
    mysqli_stmt_execute($stmtB);
    $jumlahPengikut = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtB))['jumlah'];

    kirimJSON(['success' => true, 'data' => [
        'mengikuti' => (int) $jumlahMengikuti,
        'pengikut'  => (int) $jumlahPengikut,
    ]]);
}

// ==== POST: follow / unfollow ====
if ($method === 'POST' && $action === 'follow') {
    $input = ambilInputJSON();
    $targetId = (int) ($input['user_id'] ?? 0);

    if ($targetId <= 0) {
        kirimJSON(['success' => false, 'message' => 'User tidak valid.'], 400);
    }
    if ($targetId === $USER_ID) {
        kirimJSON(['success' => false, 'message' => 'Tidak bisa follow diri sendiri.'], 400);
    }

    $cekUser = siapkanQuery($koneksi, "SELECT id FROM users WHERE id = ?");
    mysqli_stmt_bind_param($cekUser, "i", $targetId);
    mysqli_stmt_execute($cekUser);
    if (!mysqli_fetch_assoc(mysqli_stmt_get_result($cekUser))) {
        kirimJSON(['success' => false, 'message' => 'User tidak ditemukan.'], 404);
    }

    $stmt = siapkanQuery($koneksi, "INSERT IGNORE INTO follows (follower_id, following_id) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ii", $USER_ID, $targetId);
    mysqli_stmt_execute($stmt);

    kirimJSON(['success' => true]);
}

if ($method === 'POST' && $action === 'unfollow') {
    $input = ambilInputJSON();
    $targetId = (int) ($input['user_id'] ?? 0);

    if ($targetId <= 0) {
        kirimJSON(['success' => false, 'message' => 'User tidak valid.'], 400);
    }

    $stmt = siapkanQuery($koneksi, "DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $USER_ID, $targetId);
    mysqli_stmt_execute($stmt);

    kirimJSON(['success' => true]);
}

kirimJSON(['success' => false, 'message' => 'Aksi tidak dikenali.'], 400);
