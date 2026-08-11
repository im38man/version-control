<?php
require_once __DIR__ . '/../includes/auth.php';
$user = require_login_api();
$userId = $user['id'];

$input = json_decode(file_get_contents('php://input'), true) ?? [];

if (!verify_csrf(csrf_from_request($input))) {
    json_response(false, null, 'Token keamanan tidak valid.', 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, null, 'Metode tidak didukung.', 405);
}

$fromId = (int)($input['fromAccountId'] ?? 0);
$toId   = (int)($input['toAccountId'] ?? 0);
$amount = (float)($input['amount'] ?? 0);
$desc   = trim($input['desc'] ?? '');

if (!$fromId || !$toId || $amount <= 0) {
    json_response(false, null, 'Data perpindahan dana tidak lengkap.', 422);
}
if ($fromId === $toId) {
    json_response(false, null, 'Rekening asal dan tujuan tidak boleh sama.', 422);
}

$stmt = $pdo->prepare('SELECT id, name FROM accounts WHERE id IN (?, ?) AND user_id = ?');
$stmt->execute([$fromId, $toId, $userId]);
$accounts = $stmt->fetchAll();
if (count($accounts) !== 2) {
    json_response(false, null, 'Rekening asal/tujuan tidak valid.', 422);
}
$accByid = [];
foreach ($accounts as $a) $accByid[$a['id']] = $a['name'];

$date = $input['date'] ?? null;
if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = date('Y-m-d');
}

$transferId = bin2hex(random_bytes(16));
$descOut = $desc !== '' ? $desc : ('Transfer ke ' . $accByid[$toId]);
$descIn  = $desc !== '' ? $desc : ('Transfer dari ' . $accByid[$fromId]);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('INSERT INTO transactions (user_id, account_id, type, description, amount, tx_date, transfer_id) VALUES (?, ?, "out", ?, ?, ?, ?)');
    $stmt->execute([$userId, $fromId, $descOut, $amount, $date, $transferId]);
    $outId = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare('INSERT INTO transactions (user_id, account_id, type, description, amount, tx_date, transfer_id) VALUES (?, ?, "in", ?, ?, ?, ?)');
    $stmt->execute([$userId, $toId, $descIn, $amount, $date, $transferId]);
    $inId = (int)$pdo->lastInsertId();

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    json_response(false, null, 'Gagal memproses perpindahan dana.', 500);
}

json_response(true, [
    'transferId' => $transferId,
    'out' => ['id' => $outId, 'accountId' => $fromId, 'desc' => $descOut, 'amount' => $amount, 'date' => $date],
    'in'  => ['id' => $inId, 'accountId' => $toId, 'desc' => $descIn, 'amount' => $amount, 'date' => $date],
], 'Dana berhasil dipindahkan.');
