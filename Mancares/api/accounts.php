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
        $stmt = $pdo->prepare('SELECT id, name, number, color, img_network AS imgNetwork, img_bank AS imgBank, img_local AS imgLocal, is_default AS isDefault FROM accounts WHERE user_id = ? ORDER BY id ASC');
        $stmt->execute([$userId]);
        json_response(true, $stmt->fetchAll());
        break;

    case 'POST':
        $name = trim($input['name'] ?? '');
        if ($name === '') json_response(false, null, 'Nama akun wajib diisi.', 422);

        $stmt = $pdo->prepare('INSERT INTO accounts (user_id, name, number, color, img_network, img_bank, img_local) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $userId,
            $name,
            trim($input['number'] ?? ''),
            $input['color'] ?? '#0f172a',
            $input['imgNetwork'] ?? '',
            $input['imgBank'] ?? '',
            $input['imgLocal'] ?? '',
        ]);
        json_response(true, ['id' => (int)$pdo->lastInsertId()], 'Rekening ditambahkan.');
        break;

    case 'PUT':
        $id = (int)($input['id'] ?? 0);
        if (!$id) json_response(false, null, 'ID tidak valid.', 422);
        // hanya field warna yang boleh diubah lewat quick-edit di kartu
        $stmt = $pdo->prepare('UPDATE accounts SET color = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$input['color'] ?? '#0f172a', $id, $userId]);
        json_response(true, null, 'Warna diperbarui.');
        break;

    case 'DELETE':
        $id = (int)($input['id'] ?? 0);
        if (!$id) json_response(false, null, 'ID tidak valid.', 422);

        $count = $pdo->prepare('SELECT COUNT(*) c FROM accounts WHERE user_id = ?');
        $count->execute([$userId]);
        if ((int)$count->fetch()['c'] <= 1) {
            json_response(false, null, 'Minimal harus ada 1 rekening aktif di sistem!', 422);
        }

        $stmt = $pdo->prepare('DELETE FROM accounts WHERE id = ? AND user_id = ? AND is_default = 0');
        $stmt->execute([$id, $userId]);
        if ($stmt->rowCount() === 0) {
            json_response(false, null, 'Rekening tidak ditemukan atau tidak bisa dihapus.', 404);
        }
        json_response(true, null, 'Rekening dihapus.');
        break;

    default:
        json_response(false, null, 'Metode tidak didukung.', 405);
}
