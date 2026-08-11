<?php
require_once __DIR__ . '/../includes/auth.php';
$user = require_login_api();

$input = json_decode(file_get_contents('php://input'), true) ?? [];
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf(csrf_from_request($input))) {
    json_response(false, null, 'Permintaan tidak valid.', 403);
}

$income = (float)($input['income'] ?? 0);
$stmt = $pdo->prepare('UPDATE users SET income = ? WHERE id = ?');
$stmt->execute([$income, $user['id']]);
json_response(true, null, 'Penghasilan diperbarui.');
