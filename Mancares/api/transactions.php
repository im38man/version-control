<?php
require_once __DIR__ . '/../includes/auth.php';
$user = require_login_api();
$userId = $user['id'];

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? [];

if (in_array($method, ['POST', 'DELETE']) && !verify_csrf(csrf_from_request($input))) {
    json_response(false, null, 'Token keamanan tidak valid.', 403);
}

switch ($method) {
    case 'GET':
        $stmt = $pdo->prepare('SELECT t.id, t.account_id AS accountId, t.type, t.description AS `desc`, t.amount, t.tx_date AS date, t.transfer_id AS transferId, t.debt_id AS debtId, d.type AS debtType FROM transactions t LEFT JOIN debts d ON d.id = t.debt_id WHERE t.user_id = ? ORDER BY t.tx_date DESC, t.id DESC');
        $stmt->execute([$userId]);
        json_response(true, $stmt->fetchAll());
        break;

    case 'POST':
        $desc = trim($input['desc'] ?? '');
        $amount = (float)($input['amount'] ?? 0);
        $accountId = (int)($input['accountId'] ?? 0);
        $type = ($input['type'] ?? 'out') === 'in' ? 'in' : 'out';

        if ($desc === '' || $amount <= 0 || !$accountId) {
            json_response(false, null, 'Data transaksi tidak lengkap.', 422);
        }

        $chk = $pdo->prepare('SELECT id FROM accounts WHERE id = ? AND user_id = ?');
        $chk->execute([$accountId, $userId]);
        if (!$chk->fetch()) json_response(false, null, 'Rekening tidak valid.', 422);

        // Tanggal dikirim dari client (zona Asia/Jakarta) supaya akurat walau server beda zona waktu
        $date = $input['date'] ?? null;
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        $stmt = $pdo->prepare('INSERT INTO transactions (user_id, account_id, type, description, amount, tx_date) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $accountId, $type, $desc, $amount, $date]);
        json_response(true, ['id' => (int)$pdo->lastInsertId(), 'date' => $date], 'Transaksi dicatat.');
        break;

    case 'DELETE':
        $id = (int)($input['id'] ?? 0);
        if (!$id) json_response(false, null, 'ID tidak valid.', 422);

        $chk = $pdo->prepare('SELECT transfer_id, debt_id FROM transactions WHERE id = ? AND user_id = ?');
        $chk->execute([$id, $userId]);
        $row = $chk->fetch();
        if (!$row) json_response(false, null, 'Transaksi tidak ditemukan.', 404);

        if (!empty($row['transfer_id'])) {
            // Transaksi bagian dari transfer -> hapus kedua sisinya sekaligus
            $stmt = $pdo->prepare('SELECT id FROM transactions WHERE transfer_id = ? AND user_id = ?');
            $stmt->execute([$row['transfer_id'], $userId]);
            $ids = array_column($stmt->fetchAll(), 'id');

            $del = $pdo->prepare('DELETE FROM transactions WHERE transfer_id = ? AND user_id = ?');
            $del->execute([$row['transfer_id'], $userId]);
            json_response(true, ['deletedIds' => $ids], 'Transaksi transfer (kedua sisi) dihapus.');
        } elseif (!empty($row['debt_id'])) {
            // Transaksi bagian dari hutang/piutang -> hapus juga data hutang/piutangnya
            $stmt = $pdo->prepare('SELECT id FROM transactions WHERE debt_id = ? AND user_id = ?');
            $stmt->execute([$row['debt_id'], $userId]);
            $ids = array_column($stmt->fetchAll(), 'id');

            $pdo->prepare('DELETE FROM transactions WHERE debt_id = ? AND user_id = ?')->execute([$row['debt_id'], $userId]);
            $pdo->prepare('DELETE FROM debts WHERE id = ? AND user_id = ?')->execute([$row['debt_id'], $userId]);
            json_response(true, ['deletedIds' => $ids, 'deletedDebtId' => (int)$row['debt_id']], 'Transaksi & data hutang/piutang terkait dihapus.');
        } else {
            $stmt = $pdo->prepare('DELETE FROM transactions WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $userId]);
            json_response(true, ['deletedIds' => [$id]], 'Transaksi dihapus.');
        }
        break;

    default:
        json_response(false, null, 'Metode tidak didukung.', 405);
}
