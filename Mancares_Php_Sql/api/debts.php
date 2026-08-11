<?php
require_once __DIR__ . '/../includes/auth.php';
$user = require_login_api();
$userId = $user['id'];

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? [];

if (in_array($method, ['POST', 'PUT', 'DELETE']) && !verify_csrf(csrf_from_request($input))) {
    json_response(false, null, 'Token keamanan tidak valid.', 403);
}

switch ($method) {
    case 'GET':
        $stmt = $pdo->prepare('SELECT id, type, person_name AS personName, description AS `desc`, amount, account_id AS accountId, status, tx_date AS date FROM debts WHERE user_id = ? ORDER BY status ASC, id DESC');
        $stmt->execute([$userId]);
        json_response(true, $stmt->fetchAll());
        break;

    case 'POST':
        $type = ($input['type'] ?? '') === 'hutang' ? 'hutang' : 'piutang';
        $personName = trim($input['personName'] ?? '');
        $amount = (float)($input['amount'] ?? 0);
        $accountId = (int)($input['accountId'] ?? 0);
        $desc = trim($input['desc'] ?? '');

        if ($personName === '' || $amount <= 0 || !$accountId) {
            json_response(false, null, 'Data hutang/piutang tidak lengkap.', 422);
        }

        $chk = $pdo->prepare('SELECT id, name FROM accounts WHERE id = ? AND user_id = ?');
        $chk->execute([$accountId, $userId]);
        $acc = $chk->fetch();
        if (!$acc) json_response(false, null, 'Rekening tidak valid.', 422);

        $date = $input['date'] ?? null;
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = date('Y-m-d');

        // Piutang = kita kasih uang ke orang -> dana KELUAR dari rekening kita
        // Hutang  = kita pinjam uang dari orang -> dana MASUK ke rekening kita
        $txType = $type === 'piutang' ? 'out' : 'in';
        $label = $type === 'piutang' ? 'Piutang' : 'Hutang';
        $txDesc = "$label - $personName" . ($desc !== '' ? " ($desc)" : '');

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('INSERT INTO debts (user_id, type, person_name, description, amount, account_id, status, tx_date) VALUES (?, ?, ?, ?, ?, ?, "belum_lunas", ?)');
            $stmt->execute([$userId, $type, $personName, $desc, $amount, $accountId, $date]);
            $debtId = (int)$pdo->lastInsertId();

            $stmt = $pdo->prepare('INSERT INTO transactions (user_id, account_id, type, description, amount, tx_date, debt_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$userId, $accountId, $txType, $txDesc, $amount, $date, $debtId]);
            $txId = (int)$pdo->lastInsertId();

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            json_response(false, null, 'Gagal mencatat hutang/piutang.', 500);
        }

        json_response(true, [
            'id' => $debtId,
            'transaction' => ['id' => $txId, 'accountId' => $accountId, 'type' => $txType, 'desc' => $txDesc, 'amount' => $amount, 'date' => $date],
        ], 'Dana berhasil dicatat.');
        break;

    case 'PUT':
        $id = (int)($input['id'] ?? 0);
        $status = ($input['status'] ?? '') === 'lunas' ? 'lunas' : 'belum_lunas';
        if (!$id) json_response(false, null, 'ID tidak valid.', 422);

        $stmt = $pdo->prepare('UPDATE debts SET status = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$status, $id, $userId]);
        json_response(true, null, 'Status diperbarui.');
        break;

    case 'DELETE':
        $id = (int)($input['id'] ?? 0);
        if (!$id) json_response(false, null, 'ID tidak valid.', 422);

        $chk = $pdo->prepare('SELECT id FROM debts WHERE id = ? AND user_id = ?');
        $chk->execute([$id, $userId]);
        if (!$chk->fetch()) json_response(false, null, 'Data tidak ditemukan.', 404);

        $stmt = $pdo->prepare('SELECT id FROM transactions WHERE debt_id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        $deletedTxIds = array_column($stmt->fetchAll(), 'id');

        $pdo->prepare('DELETE FROM transactions WHERE debt_id = ? AND user_id = ?')->execute([$id, $userId]);
        $pdo->prepare('DELETE FROM debts WHERE id = ? AND user_id = ?')->execute([$id, $userId]);

        json_response(true, ['deletedTransactionIds' => $deletedTxIds], 'Data hutang/piutang & transaksi terkait dihapus.');
        break;

    default:
        json_response(false, null, 'Metode tidak didukung.', 405);
}
