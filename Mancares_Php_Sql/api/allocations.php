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
        $stmt = $pdo->prepare('SELECT id, name, pct FROM allocations WHERE user_id = ? ORDER BY id ASC');
        $stmt->execute([$userId]);
        json_response(true, $stmt->fetchAll());
        break;

    case 'POST':
        $stmt = $pdo->prepare('INSERT INTO allocations (user_id, name, pct) VALUES (?, "Pos Baru", 0)');
        $stmt->execute([$userId]);
        json_response(true, ['id' => (int)$pdo->lastInsertId(), 'name' => 'Pos Baru', 'pct' => 0], 'Pos alokasi ditambahkan.');
        break;

    case 'PUT':
        $id = (int)($input['id'] ?? 0);
        $field = $input['field'] ?? '';
        $value = $input['value'] ?? '';
        if (!$id || !in_array($field, ['name', 'pct'], true)) json_response(false, null, 'Data tidak valid.', 422);

        if ($field === 'pct') {
            $newPct = (float)$value;
            if ($newPct < 0) $newPct = 0;

            $sumStmt = $pdo->prepare('SELECT COALESCE(SUM(pct), 0) AS total FROM allocations WHERE user_id = ? AND id != ?');
            $sumStmt->execute([$userId, $id]);
            $othersTotal = (float)$sumStmt->fetch()['total'];

            $maxAllowed = round(100 - $othersTotal, 2);
            if ($newPct > $maxAllowed) {
                json_response(false, ['maxAllowed' => max(0, $maxAllowed)], "Total alokasi tidak boleh lebih dari 100%. Sisa slot: {$maxAllowed}%.", 422);
            }

            $stmt = $pdo->prepare('UPDATE allocations SET pct = ? WHERE id = ? AND user_id = ?');
            $stmt->execute([$newPct, $id, $userId]);
        } else {
            $stmt = $pdo->prepare('UPDATE allocations SET name = ? WHERE id = ? AND user_id = ?');
            $stmt->execute([trim((string)$value), $id, $userId]);
        }
        json_response(true, null, 'Alokasi diperbarui.');
        break;

    case 'DELETE':
        $id = (int)($input['id'] ?? 0);
        if (!$id) json_response(false, null, 'ID tidak valid.', 422);
        $stmt = $pdo->prepare('DELETE FROM allocations WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        json_response(true, null, 'Pos alokasi dihapus.');
        break;

    default:
        json_response(false, null, 'Metode tidak didukung.', 405);
}
