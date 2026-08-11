<?php
/** Dibuat otomatis saat user baru register: 1 rekening "Tunai" default */
function seed_default_account($pdo, $userId) {
    $stmt = $pdo->prepare(
        'INSERT INTO accounts (user_id, name, number, color, is_default) VALUES (?, ?, ?, ?, 1)'
    );
    $stmt->execute([$userId, 'Tunai', 'Wallet', '#475569']);
}
