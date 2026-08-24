<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
csrf_verify();

$user = current_user();
$id = (int)($_POST['id'] ?? 0);
$date = $_POST['date'] ?? '';
$pair = strtoupper(trim($_POST['pair'] ?? ''));
$entry = $_POST['entry'] ?? '';
$sl = $_POST['sl'] !== '' ? $_POST['sl'] : null; // SL awal (referensi/display doang, bukan sumber hit/pips/usd)
$tp = null; // field TP tunggal sudah digantikan tabel Risk:Reward (TP1-5)
$lot = $_POST['lot'] ?? '';
$linkBefore = trim($_POST['link_before'] ?? '') ?: null;
$linkAfter = trim($_POST['link_after'] ?? '') ?: null;

if ($date === '' || $pair === '' || $entry === '' || $lot === '') {
    flash_set('Tanggal, Pair, Entry, dan Lot wajib diisi ya bro!', 'error');
    header('Location: index.php');
    exit;
}

/** Ambil nilai numerik dari $_POST, null kalau kosong */
function post_num(string $key): ?float {
    $v = $_POST[$key] ?? '';
    return $v !== '' ? (float)$v : null;
}

// ---------- Baris SL1-SL5 (trailing SL per fase Risk:Reward) ----------
$slHit = [];
$slPrice = [];
$slPips = [];
$slUsd = [];
for ($i = 1; $i <= 5; $i++) {
    $hit = isset($_POST["sl{$i}_hit"]) ? 1 : 0;
    $price = post_num("sl{$i}_price");
    $pips = post_num("sl{$i}_pips");
    $usd = post_num("sl{$i}_usd");
    // SL selalu diitung MINUS otomatis
    if ($hit) {
        $pips = $pips !== null ? -abs($pips) : null;
        $usd = $usd !== null ? -abs($usd) : null;
    } else {
        $pips = null;
        $usd = null;
    }
    $slHit[$i] = $hit;
    $slPrice[$i] = $price;
    $slPips[$i] = $pips;
    $slUsd[$i] = $usd;
}

// ---------- Baris TP1-TP5 ----------
$tpHit = [];
$tpPrice = [];
$tpPips = [];
$tpUsd = [];
for ($i = 1; $i <= 5; $i++) {
    $hit = isset($_POST["tp{$i}_hit"]) ? 1 : 0;
    $price = post_num("tp{$i}_price");
    $pips = post_num("tp{$i}_pips");
    $usd = post_num("tp{$i}_usd");
    // TP selalu diitung PLUS otomatis
    if ($hit) {
        $pips = $pips !== null ? abs($pips) : null;
        $usd = $usd !== null ? abs($usd) : null;
    } else {
        $pips = null;
        $usd = null;
    }
    $tpHit[$i] = $hit;
    $tpPrice[$i] = $price;
    $tpPips[$i] = $pips;
    $tpUsd[$i] = $usd;
}

// ---------- Hitung total pips/usd & status otomatis dari semua baris yang KENA ----------
$anyTpHit = false;
$anySlHit = false;
$totalPips = 0;
$totalUsd = 0;
$hasAnyPipsValue = false;
$hasAnyUsdValue = false;

for ($i = 1; $i <= 5; $i++) {
    if ($slHit[$i]) {
        $anySlHit = true;
        if ($slPips[$i] !== null) { $totalPips += $slPips[$i]; $hasAnyPipsValue = true; }
        if ($slUsd[$i] !== null) { $totalUsd += $slUsd[$i]; $hasAnyUsdValue = true; }
    }
    if ($tpHit[$i]) {
        $anyTpHit = true;
        if ($tpPips[$i] !== null) { $totalPips += $tpPips[$i]; $hasAnyPipsValue = true; }
        if ($tpUsd[$i] !== null) { $totalUsd += $tpUsd[$i]; $hasAnyUsdValue = true; }
    }
}

if (!$anySlHit && !$anyTpHit) {
    $pnlStatus = 'Pending';
} elseif ($anySlHit && !$anyTpHit) {
    $pnlStatus = 'Loss';
} elseif (!$anySlHit && $anyTpHit) {
    $pnlStatus = 'Profit';
} else {
    // Partial close: SL kena setelah sebagian TP kena duluan (trailing) — tentukan dari total USD (fallback pips)
    $sign = $hasAnyUsdValue ? $totalUsd : ($hasAnyPipsValue ? $totalPips : 0);
    $pnlStatus = $sign > 0 ? 'Profit' : ($sign < 0 ? 'Loss' : 'Breakeven');
}

$finalPips = $hasAnyPipsValue ? $totalPips : null;
$finalUsd = $hasAnyUsdValue ? $totalUsd : null;

if ($id > 0) {
    // Pastikan trade ini milik user yang login
    $stmt = $conn->prepare('SELECT id FROM trades WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $id, $user['id']);
    $stmt->execute();
    $stmt->store_result();
    $owns = $stmt->num_rows > 0;
    $stmt->close();

    if (!$owns) {
        flash_set('Data tidak ditemukan.', 'error');
        header('Location: index.php');
        exit;
    }

    $stmt = $conn->prepare('UPDATE trades SET trade_date=?, pair=?, entry=?, sl=?, tp=?, lot=?, pnl_status=?, pips=?, usd=?, image_before=?, image_after=?,
        sl1_hit=?, sl1_price=?, sl1_pips=?, sl1_usd=?,
        sl2_hit=?, sl2_price=?, sl2_pips=?, sl2_usd=?,
        sl3_hit=?, sl3_price=?, sl3_pips=?, sl3_usd=?,
        sl4_hit=?, sl4_price=?, sl4_pips=?, sl4_usd=?,
        sl5_hit=?, sl5_price=?, sl5_pips=?, sl5_usd=?,
        tp1_hit=?, tp1_price=?, tp1_pips=?, tp1_usd=?,
        tp2_hit=?, tp2_price=?, tp2_pips=?, tp2_usd=?,
        tp3_hit=?, tp3_price=?, tp3_pips=?, tp3_usd=?,
        tp4_hit=?, tp4_price=?, tp4_pips=?, tp4_usd=?,
        tp5_hit=?, tp5_price=?, tp5_pips=?, tp5_usd=?
        WHERE id=? AND user_id=?');
    $stmt->bind_param(
        'ssddddsddss' . 'idddidddidddidddiddd' . 'idddidddidddidddiddd' . 'ii',
        $date, $pair, $entry, $sl, $tp, $lot, $pnlStatus, $finalPips, $finalUsd, $linkBefore, $linkAfter,
        $slHit[1], $slPrice[1], $slPips[1], $slUsd[1],
        $slHit[2], $slPrice[2], $slPips[2], $slUsd[2],
        $slHit[3], $slPrice[3], $slPips[3], $slUsd[3],
        $slHit[4], $slPrice[4], $slPips[4], $slUsd[4],
        $slHit[5], $slPrice[5], $slPips[5], $slUsd[5],
        $tpHit[1], $tpPrice[1], $tpPips[1], $tpUsd[1],
        $tpHit[2], $tpPrice[2], $tpPips[2], $tpUsd[2],
        $tpHit[3], $tpPrice[3], $tpPips[3], $tpUsd[3],
        $tpHit[4], $tpPrice[4], $tpPips[4], $tpUsd[4],
        $tpHit[5], $tpPrice[5], $tpPips[5], $tpUsd[5],
        $id, $user['id']
    );
    $stmt->execute();
    $stmt->close();
    flash_set('Journal berhasil diupdate.', 'success');
} else {
    $stmt = $conn->prepare('INSERT INTO trades (user_id, trade_date, pair, entry, sl, tp, lot, pnl_status, pips, usd, image_before, image_after,
        sl1_hit, sl1_price, sl1_pips, sl1_usd,
        sl2_hit, sl2_price, sl2_pips, sl2_usd,
        sl3_hit, sl3_price, sl3_pips, sl3_usd,
        sl4_hit, sl4_price, sl4_pips, sl4_usd,
        sl5_hit, sl5_price, sl5_pips, sl5_usd,
        tp1_hit, tp1_price, tp1_pips, tp1_usd,
        tp2_hit, tp2_price, tp2_pips, tp2_usd,
        tp3_hit, tp3_price, tp3_pips, tp3_usd,
        tp4_hit, tp4_price, tp4_pips, tp4_usd,
        tp5_hit, tp5_price, tp5_pips, tp5_usd)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?, ?,?,?,?, ?,?,?,?, ?,?,?,?, ?,?,?,?, ?,?,?,?, ?,?,?,?, ?,?,?,?, ?,?,?,?, ?,?,?,?, ?,?,?,?)');
    $stmt->bind_param(
        'issddddsddss' . 'idddidddidddidddiddd' . 'idddidddidddidddiddd',
        $user['id'], $date, $pair, $entry, $sl, $tp, $lot, $pnlStatus, $finalPips, $finalUsd, $linkBefore, $linkAfter,
        $slHit[1], $slPrice[1], $slPips[1], $slUsd[1],
        $slHit[2], $slPrice[2], $slPips[2], $slUsd[2],
        $slHit[3], $slPrice[3], $slPips[3], $slUsd[3],
        $slHit[4], $slPrice[4], $slPips[4], $slUsd[4],
        $slHit[5], $slPrice[5], $slPips[5], $slUsd[5],
        $tpHit[1], $tpPrice[1], $tpPips[1], $tpUsd[1],
        $tpHit[2], $tpPrice[2], $tpPips[2], $tpUsd[2],
        $tpHit[3], $tpPrice[3], $tpPips[3], $tpUsd[3],
        $tpHit[4], $tpPrice[4], $tpPips[4], $tpUsd[4],
        $tpHit[5], $tpPrice[5], $tpPips[5], $tpUsd[5]
    );
    $stmt->execute();
    $stmt->close();
    flash_set('Journal baru berhasil disimpan.', 'success');
}

header('Location: index.php');
exit;
