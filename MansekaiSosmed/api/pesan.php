<?php
require_once __DIR__ . '/_bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($method === 'POST' ? (ambilInputJSON()['action'] ?? '') : '');

// Kata 'CHAT' diubah menjadi 'PESAN'
const AVATAR_DEFAULT_PESAN = 'img.magnific.com/premium-vector/user-profile-icon-circle_1256048-12499.jpg?auto=format&fit=crop&w=200&q=80';

// ==== Helper: cek apakah 2 user saling follow (= "berteman") ====
function salingFollow($koneksi, $a, $b) {
    $stmt = siapkanQuery($koneksi, "
        SELECT
            (SELECT COUNT(*) FROM follows WHERE follower_id = ? AND following_id = ?) AS a_follow_b,
            (SELECT COUNT(*) FROM follows WHERE follower_id = ? AND following_id = ?) AS b_follow_a
    ");
    mysqli_stmt_bind_param($stmt, "iiii", $a, $b, $b, $a);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return ((int) $row['a_follow_b'] > 0) && ((int) $row['b_follow_a'] > 0);
}

// ==== Helper: ambil status permintaan pesan antara 2 user (kedua arah) ====
function ambilPermintaan($koneksi, $a, $b) {
    $stmt = siapkanQuery($koneksi, "
        SELECT id, pengirim_id, penerima_id, status FROM permintaan_pesan
        WHERE (pengirim_id = ? AND penerima_id = ?) OR (pengirim_id = ? AND penerima_id = ?)
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "iiii", $a, $b, $b, $a);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
}

// ==== Helper: apakah $a boleh langsung kirim pesan ke $b tanpa permintaan? ====
// Boleh langsung kalau: saling follow, ATAU sudah ada permintaan_pesan berstatus 'diterima'.
function bisaChatLangsung($koneksi, $a, $b, &$permintaanOut = null) {
    if (salingFollow($koneksi, $a, $b)) {
        $permintaanOut = null;
        return true;
    }
    $permintaan = ambilPermintaan($koneksi, $a, $b);
    $permintaanOut = $permintaan;
    return $permintaan && $permintaan['status'] === 'diterima';
}

// ==== GET: jumlah pesan belum dibaca (buat badge notif di sidebar) ====
if ($method === 'GET' && $action === 'unread_total') {
    $stmt = siapkanQuery($koneksi, "SELECT COUNT(*) AS jumlah FROM messages WHERE receiver_id = ? AND dibaca = 0 AND deleted_by_receiver = 0");
    mysqli_stmt_bind_param($stmt, "i", $USER_ID);
    mysqli_stmt_execute($stmt);
    $jumlahPesan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['jumlah'];

    $stmtReq = siapkanQuery($koneksi, "SELECT COUNT(*) AS jumlah FROM permintaan_pesan WHERE penerima_id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($stmtReq, "i", $USER_ID);
    mysqli_stmt_execute($stmtReq);
    $jumlahPermintaan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtReq))['jumlah'];

    kirimJSON(['success' => true, 'data' => [
        'total'      => (int) $jumlahPesan,
        'permintaan' => (int) $jumlahPermintaan,
    ]]);
}

// ==== GET: daftar permintaan pesan masuk (menunggu diterima/ditolak) ====
if ($method === 'GET' && $action === 'permintaan_masuk') {
    $stmt = siapkanQuery($koneksi, "
        SELECT pp.id, u.id AS user_id, u.nama, u.username, p.avatar, pp.created_at
        FROM permintaan_pesan pp
        JOIN users u ON u.id = pp.pengirim_id
        LEFT JOIN profil p ON p.user_id = u.id
        WHERE pp.penerima_id = ? AND pp.status = 'pending'
        ORDER BY pp.created_at DESC
    ");
    mysqli_stmt_bind_param($stmt, "i", $USER_ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'permintaan_id' => (int) $row['id'],
            'id'            => (int) $row['user_id'],
            'nama'          => $row['nama'],
            'username'      => $row['username'],
            'avatar'        => $row['avatar'] ?: AVATAR_DEFAULT_PESAN,
            'waktu'         => $row['created_at'],
        ];
    }

    kirimJSON(['success' => true, 'data' => $data]);
}

// ==== GET: daftar percakapan (kotak masuk) ====
// Hanya nampilin percakapan yang beneran udah punya pesan (bukan sekadar permintaan yang belum dibalas).
if ($method === 'GET' && $action === 'percakapan') {
    $stmtPartner = siapkanQuery($koneksi, "
        SELECT DISTINCT CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS partner_id
        FROM messages
        WHERE (sender_id = ? AND deleted_by_sender = 0) OR (receiver_id = ? AND deleted_by_receiver = 0)
    ");
    mysqli_stmt_bind_param($stmtPartner, "iii", $USER_ID, $USER_ID, $USER_ID);
    mysqli_stmt_execute($stmtPartner);
    $result = mysqli_stmt_get_result($stmtPartner);

    $daftar = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $partnerId = (int) $row['partner_id'];

        $stmtUser = siapkanQuery($koneksi, "SELECT u.id, u.nama, u.username, p.avatar FROM users u LEFT JOIN profil p ON p.user_id = u.id WHERE u.id = ?");
        mysqli_stmt_bind_param($stmtUser, "i", $partnerId);
        mysqli_stmt_execute($stmtUser);
        $userInfo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtUser));
        if (!$userInfo) continue; // Jaga-jaga kalau user sudah dihapus

        $stmtLast = siapkanQuery($koneksi, "
            SELECT pesan, sender_id, created_at FROM messages
            WHERE ((sender_id = ? AND receiver_id = ? AND deleted_by_sender = 0) OR (sender_id = ? AND receiver_id = ? AND deleted_by_receiver = 0))
            ORDER BY created_at DESC LIMIT 1
        ");
        mysqli_stmt_bind_param($stmtLast, "iiii", $USER_ID, $partnerId, $partnerId, $USER_ID);
        mysqli_stmt_execute($stmtLast);
        $pesanTerakhir = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtLast));
        if (!$pesanTerakhir) continue; // semua pesan di percakapan ini udah dihapus buat saya

        $stmtUnread = siapkanQuery($koneksi, "SELECT COUNT(*) AS jumlah FROM messages WHERE sender_id = ? AND receiver_id = ? AND dibaca = 0 AND deleted_by_receiver = 0");
        mysqli_stmt_bind_param($stmtUnread, "ii", $partnerId, $USER_ID);
        mysqli_stmt_execute($stmtUnread);
        $jumlahUnread = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtUnread))['jumlah'];

        $daftar[] = [
            'id'              => $partnerId,
            'nama'            => $userInfo['nama'],
            'username'        => $userInfo['username'],
            'avatar'          => $userInfo['avatar'] ?: AVATAR_DEFAULT_PESAN,
            'pesan_terakhir'  => $pesanTerakhir['pesan'] ?? '',
            'waktu_terakhir'  => $pesanTerakhir['created_at'] ?? null,
            'dari_saya'       => $pesanTerakhir && (int) $pesanTerakhir['sender_id'] === $USER_ID,
            'unread'          => (int) $jumlahUnread,
        ];
    }

    usort($daftar, fn($a, $b) => strtotime($b['waktu_terakhir'] ?? '1970-01-01') <=> strtotime($a['waktu_terakhir'] ?? '1970-01-01'));

    kirimJSON(['success' => true, 'data' => $daftar]);
}

// ==== GET: cek status "boleh chat" ke satu user (dipakai sebelum buka ruang obrolan) ====
if ($method === 'GET' && $action === 'status') {
    $partnerId = (int) ($_GET['user_id'] ?? 0);
    if ($partnerId <= 0 || $partnerId === $USER_ID) {
        kirimJSON(['success' => false, 'message' => 'User tidak valid.'], 400);
    }

    $permintaan = null;
    $bisaChat = bisaChatLangsung($koneksi, $USER_ID, $partnerId, $permintaan);

    $statusPermintaan = null; // null | 'menunggu_saya' | 'menunggu_dia' | 'ditolak'
    if (!$bisaChat && $permintaan) {
        if ($permintaan['status'] === 'pending') {
            $statusPermintaan = ((int) $permintaan['pengirim_id'] === $USER_ID) ? 'menunggu_dia' : 'menunggu_saya';
        } elseif ($permintaan['status'] === 'ditolak') {
            $statusPermintaan = 'ditolak';
        }
    }

    kirimJSON(['success' => true, 'data' => [
        'bisa_chat'         => $bisaChat,
        'status_permintaan' => $statusPermintaan,
        'permintaan_id'     => $permintaan['id'] ?? null,
    ]]);
}

// ==== GET: ambil isi percakapan dengan 1 user, sekaligus tandai dibaca ====
if ($method === 'GET' && $action === 'pesan') {
    $partnerId = (int) ($_GET['user_id'] ?? 0);

    if ($partnerId <= 0) {
        kirimJSON(['success' => false, 'message' => 'User tidak valid.'], 400);
    }

    $cekUser = siapkanQuery($koneksi, "SELECT u.id, u.nama, u.username, p.avatar FROM users u LEFT JOIN profil p ON p.user_id = u.id WHERE u.id = ?");
    mysqli_stmt_bind_param($cekUser, "i", $partnerId);
    mysqli_stmt_execute($cekUser);
    $partner = mysqli_fetch_assoc(mysqli_stmt_get_result($cekUser));
    if (!$partner) {
        kirimJSON(['success' => false, 'message' => 'User tidak ditemukan.'], 404);
    }

    // Tandai semua pesan dari partner ke saya sebagai sudah dibaca
    $stmtBaca = siapkanQuery($koneksi, "UPDATE messages SET dibaca = 1 WHERE sender_id = ? AND receiver_id = ? AND dibaca = 0");
    mysqli_stmt_bind_param($stmtBaca, "ii", $partnerId, $USER_ID);
    mysqli_stmt_execute($stmtBaca);

    $stmt = siapkanQuery($koneksi, "
        SELECT id, sender_id, pesan, created_at FROM messages
        WHERE ((sender_id = ? AND receiver_id = ? AND deleted_by_sender = 0) OR (sender_id = ? AND receiver_id = ? AND deleted_by_receiver = 0))
        ORDER BY created_at ASC
        LIMIT 200
    ");
    mysqli_stmt_bind_param($stmt, "iiii", $USER_ID, $partnerId, $partnerId, $USER_ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $pesanList = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $pesanList[] = [
            'id'         => (int) $row['id'],
            'pesan'      => $row['pesan'],
            'dari_saya'  => (int) $row['sender_id'] === $USER_ID,
            'waktu'      => $row['created_at'],
        ];
    }

    $permintaan = null;
    $bisaChat = bisaChatLangsung($koneksi, $USER_ID, $partnerId, $permintaan);
    $statusPermintaan = null;
    if (!$bisaChat && $permintaan) {
        if ($permintaan['status'] === 'pending') {
            $statusPermintaan = ((int) $permintaan['pengirim_id'] === $USER_ID) ? 'menunggu_dia' : 'menunggu_saya';
        } elseif ($permintaan['status'] === 'ditolak') {
            $statusPermintaan = 'ditolak';
        }
    }

    kirimJSON(['success' => true, 'data' => [
        'partner'  => [
            'id' => (int) $partner['id'], 'nama' => $partner['nama'], 'username' => $partner['username'],
            'avatar' => $partner['avatar'] ?: AVATAR_DEFAULT_PESAN,
        ],
        'messages'          => $pesanList,
        'bisa_chat'         => $bisaChat,
        'status_permintaan' => $statusPermintaan,
        'permintaan_id'     => $permintaan['id'] ?? null,
    ]]);
}

// ==== POST: kirim permintaan pesan (dipakai kalau belum saling follow) ====
if ($method === 'POST' && $action === 'kirim_permintaan') {
    $input = ambilInputJSON();
    $partnerId = (int) ($input['user_id'] ?? 0);

    if ($partnerId <= 0 || $partnerId === $USER_ID) {
        kirimJSON(['success' => false, 'message' => 'User tidak valid.'], 400);
    }

    $cekUser = siapkanQuery($koneksi, "SELECT id FROM users WHERE id = ?");
    mysqli_stmt_bind_param($cekUser, "i", $partnerId);
    mysqli_stmt_execute($cekUser);
    if (!mysqli_fetch_assoc(mysqli_stmt_get_result($cekUser))) {
        kirimJSON(['success' => false, 'message' => 'User tujuan tidak ditemukan.'], 404);
    }

    if (salingFollow($koneksi, $USER_ID, $partnerId)) {
        kirimJSON(['success' => false, 'message' => 'Kamu sudah bisa langsung kirim pesan ke user ini.'], 400);
    }

    $permintaan = ambilPermintaan($koneksi, $USER_ID, $partnerId);
    if ($permintaan) {
        if ($permintaan['status'] === 'diterima') {
            kirimJSON(['success' => false, 'message' => 'Permintaan sudah diterima, langsung kirim pesan aja.'], 400);
        }
        if ($permintaan['status'] === 'pending') {
            kirimJSON(['success' => false, 'message' => 'Permintaan pesan masih menunggu jawaban.'], 400);
        }
        // status 'ditolak' -> boleh kirim ulang, update jadi pending lagi (arah dari $USER_ID)
        $stmtUpdate = siapkanQuery($koneksi, "UPDATE permintaan_pesan SET pengirim_id = ?, penerima_id = ?, status = 'pending' WHERE id = ?");
        mysqli_stmt_bind_param($stmtUpdate, "iii", $USER_ID, $partnerId, $permintaan['id']);
        mysqli_stmt_execute($stmtUpdate);
        kirimJSON(['success' => true, 'message' => 'Permintaan pesan terkirim.']);
    }

    $stmt = siapkanQuery($koneksi, "INSERT INTO permintaan_pesan (pengirim_id, penerima_id, status) VALUES (?, ?, 'pending')");
    mysqli_stmt_bind_param($stmt, "ii", $USER_ID, $partnerId);
    mysqli_stmt_execute($stmt);

    kirimJSON(['success' => true, 'message' => 'Permintaan pesan terkirim.']);
}

// ==== POST: terima permintaan pesan masuk ====
if ($method === 'POST' && $action === 'terima_permintaan') {
    $input = ambilInputJSON();
    $partnerId = (int) ($input['user_id'] ?? 0);

    if ($partnerId <= 0) {
        kirimJSON(['success' => false, 'message' => 'User tidak valid.'], 400);
    }

    $stmt = siapkanQuery($koneksi, "UPDATE permintaan_pesan SET status = 'diterima' WHERE pengirim_id = ? AND penerima_id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($stmt, "ii", $partnerId, $USER_ID);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) === 0) {
        kirimJSON(['success' => false, 'message' => 'Permintaan tidak ditemukan.'], 404);
    }

    kirimJSON(['success' => true, 'message' => 'Permintaan pesan diterima.']);
}

// ==== POST: tolak permintaan pesan masuk ====
if ($method === 'POST' && $action === 'tolak_permintaan') {
    $input = ambilInputJSON();
    $partnerId = (int) ($input['user_id'] ?? 0);

    if ($partnerId <= 0) {
        kirimJSON(['success' => false, 'message' => 'User tidak valid.'], 400);
    }

    $stmt = siapkanQuery($koneksi, "UPDATE permintaan_pesan SET status = 'ditolak' WHERE pengirim_id = ? AND penerima_id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($stmt, "ii", $partnerId, $USER_ID);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) === 0) {
        kirimJSON(['success' => false, 'message' => 'Permintaan tidak ditemukan.'], 404);
    }

    kirimJSON(['success' => true, 'message' => 'Permintaan pesan ditolak.']);
}

// ==== POST: kirim pesan baru ====
if ($method === 'POST' && $action === 'kirim') {
    $input = ambilInputJSON();
    $partnerId = (int) ($input['user_id'] ?? 0);
    $pesan     = trim($input['pesan'] ?? '');

    if ($partnerId <= 0) {
        kirimJSON(['success' => false, 'message' => 'User tujuan tidak valid.'], 400);
    }
    if ($partnerId === $USER_ID) {
        kirimJSON(['success' => false, 'message' => 'Tidak bisa kirim pesan ke diri sendiri.'], 400);
    }
    if ($pesan === '') {
        kirimJSON(['success' => false, 'message' => 'Pesan tidak boleh kosong.'], 400);
    }
    if (mb_strlen($pesan) > 2000) {
        kirimJSON(['success' => false, 'message' => 'Pesan terlalu panjang (maksimal 2000 karakter).'], 400);
    }

    $cekUser = siapkanQuery($koneksi, "SELECT id FROM users WHERE id = ?");
    mysqli_stmt_bind_param($cekUser, "i", $partnerId);
    mysqli_stmt_execute($cekUser);
    if (!mysqli_fetch_assoc(mysqli_stmt_get_result($cekUser))) {
        kirimJSON(['success' => false, 'message' => 'User tujuan tidak ditemukan.'], 404);
    }

    // ==== Aturan: harus saling follow ATAU permintaan pesan sudah diterima ====
    $permintaan = null;
    if (!bisaChatLangsung($koneksi, $USER_ID, $partnerId, $permintaan)) {
        if ($permintaan && $permintaan['status'] === 'pending') {
            $pesanError = ((int) $permintaan['pengirim_id'] === $USER_ID)
                ? 'Permintaan pesan kamu masih menunggu diterima.'
                : 'User ini sudah mengirim permintaan pesan ke kamu, terima dulu sebelum bisa membalas.';
        } else {
            $pesanError = 'Kalian belum saling follow. Kirim permintaan pesan dulu sebelum bisa chat.';
        }
        kirimJSON(['success' => false, 'message' => $pesanError, 'butuh_permintaan' => true], 403);
    }

    $stmt = siapkanQuery($koneksi, "INSERT INTO messages (sender_id, receiver_id, pesan) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iis", $USER_ID, $partnerId, $pesan);
    mysqli_stmt_execute($stmt);

    kirimJSON(['success' => true, 'data' => [
        'id'        => mysqli_insert_id($koneksi),
        'pesan'     => $pesan,
        'dari_saya' => true,
        'waktu'     => date('Y-m-d H:i:s'),
    ]]);
}

// ==== POST: hapus pesan (hapus untuk saya; kalau kedua sisi udah hapus, baris beneran dibuang) ====
if ($method === 'POST' && $action === 'hapus') {
    $input = ambilInputJSON();
    $pesanId = (int) ($input['pesan_id'] ?? 0);

    if ($pesanId <= 0) {
        kirimJSON(['success' => false, 'message' => 'Pesan tidak valid.'], 400);
    }

    $cek = siapkanQuery($koneksi, "SELECT id, sender_id, receiver_id FROM messages WHERE id = ?");
    mysqli_stmt_bind_param($cek, "i", $pesanId);
    mysqli_stmt_execute($cek);
    $pesan = mysqli_fetch_assoc(mysqli_stmt_get_result($cek));

    if (!$pesan) {
        kirimJSON(['success' => false, 'message' => 'Pesan tidak ditemukan.'], 404);
    }
    if ((int) $pesan['sender_id'] !== $USER_ID && (int) $pesan['receiver_id'] !== $USER_ID) {
        kirimJSON(['success' => false, 'message' => 'Kamu tidak punya akses ke pesan ini.'], 403);
    }

    if ((int) $pesan['sender_id'] === $USER_ID) {
        $stmt = siapkanQuery($koneksi, "UPDATE messages SET deleted_by_sender = 1 WHERE id = ?");
    } else {
        $stmt = siapkanQuery($koneksi, "UPDATE messages SET deleted_by_receiver = 1 WHERE id = ?");
    }
    mysqli_stmt_bind_param($stmt, "i", $pesanId);
    mysqli_stmt_execute($stmt);

    // Kalau kedua sisi udah sama-sama hapus, buang aja barisnya biar tabel nggak numpuk sampah.
    $stmtBersih = siapkanQuery($koneksi, "DELETE FROM messages WHERE id = ? AND deleted_by_sender = 1 AND deleted_by_receiver = 1");
    mysqli_stmt_bind_param($stmtBersih, "i", $pesanId);
    mysqli_stmt_execute($stmtBersih);

    kirimJSON(['success' => true, 'message' => 'Pesan dihapus.']);
}

kirimJSON(['success' => false, 'message' => 'Aksi tidak dikenali.'], 400);
?>
