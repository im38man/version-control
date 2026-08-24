<?php
require_once __DIR__ . '/_bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET -> ambil data profil user yang login (nama dari tabel users, sisanya dari tabel profil)
if ($method === 'GET') {
    $stmt = siapkanQuery($koneksi, "SELECT nama FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $USER_ID);
    mysqli_stmt_execute($stmt);
    $userRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    $stmt2 = siapkanQuery($koneksi, "SELECT avatar, bio, link_github, link_linkedin, link_instagram, link_tiktok, link_facebook, link_x, link_youtube FROM profil WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt2, "i", $USER_ID);
    mysqli_stmt_execute($stmt2);
    $profilRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));

    $default = [
        'avatar' => 'img.magnific.com/premium-vector/user-profile-icon-circle_1256048-12499.jpg?auto=format&fit=crop&w=200&q=80',
        'bio' => '', 'link_github' => '', 'link_linkedin' => '', 'link_instagram' => '',
        'link_tiktok' => '', 'link_facebook' => '', 'link_x' => '', 'link_youtube' => '',
    ];
    $profilRow = $profilRow ?: $default;
    foreach ($default as $k => $v) {
        if (!isset($profilRow[$k]) || $profilRow[$k] === null) $profilRow[$k] = $v;
    }

    kirimJSON(['success' => true, 'data' => array_merge(['nama' => $userRow['nama'] ?? ''], $profilRow)]);
}

// POST -> simpan (insert/update) data profil. Avatar TIDAK ditangani di sini
// lagi -- itu urusan api/avatar.php, supaya menyimpan bio/link sosmed tidak
// menimpa foto profil yang sudah diupload user.
if ($method === 'POST') {
    $input = ambilInputJSON();

    $nama    = trim($input['nama'] ?? '');
    $bio     = trim($input['bio'] ?? '');
    $github  = trim($input['link_github'] ?? '');
    $linkedin= trim($input['link_linkedin'] ?? '');
    $ig      = trim($input['link_instagram'] ?? '');
    $tiktok  = trim($input['link_tiktok'] ?? '');
    $fb      = trim($input['link_facebook'] ?? '');
    $x       = trim($input['link_x'] ?? '');
    $yt      = trim($input['link_youtube'] ?? '');

    if ($nama !== '') {
        $stmtNama = siapkanQuery($koneksi, "UPDATE users SET nama = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmtNama, "si", $nama, $USER_ID);
        mysqli_stmt_execute($stmtNama);
    }

    $stmt = siapkanQuery($koneksi, "
        INSERT INTO profil (user_id, bio, link_github, link_linkedin, link_instagram, link_tiktok, link_facebook, link_x, link_youtube)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            bio = VALUES(bio),
            link_github = VALUES(link_github), link_linkedin = VALUES(link_linkedin),
            link_instagram = VALUES(link_instagram), link_tiktok = VALUES(link_tiktok),
            link_facebook = VALUES(link_facebook), link_x = VALUES(link_x), link_youtube = VALUES(link_youtube)
    ");
    mysqli_stmt_bind_param($stmt, "issssssss", $USER_ID, $bio, $github, $linkedin, $ig, $tiktok, $fb, $x, $yt);
    mysqli_stmt_execute($stmt);

    kirimJSON(['success' => true]);
}

kirimJSON(['success' => false, 'message' => 'Method tidak didukung'], 405);
