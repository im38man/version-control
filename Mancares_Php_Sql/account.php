<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_login();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Sesi form kedaluwarsa, silakan coba lagi.';
    } else {
        $newUsername = trim($_POST['username'] ?? '');
        $newEmail    = trim($_POST['email'] ?? '');
        $currentPass = $_POST['current_password'] ?? '';
        $newPass     = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if ($newUsername === '' || strlen($newUsername) < 3) $errors[] = 'Username minimal 3 karakter.';
        if (!preg_match('/^[a-zA-Z0-9_.]+$/', $newUsername)) $errors[] = 'Username hanya boleh huruf, angka, titik, underscore.';
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';

        if ($currentPass === '') {
            $errors[] = 'Masukkan password saat ini untuk konfirmasi perubahan.';
        } else {
            $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
            $stmt->execute([$user['id']]);
            $row = $stmt->fetch();
            if (!$row || !password_verify($currentPass, $row['password'])) {
                $errors[] = 'Password saat ini salah.';
            }
        }

        if ($newPass !== '' && strlen($newPass) < 6) $errors[] = 'Password baru minimal 6 karakter.';
        if ($newPass !== '' && $newPass !== $confirmPass) $errors[] = 'Konfirmasi password baru tidak cocok.';

        if (empty($errors)) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?');
            $stmt->execute([$newUsername, $newEmail, $user['id']]);
            if ($stmt->fetch()) {
                $errors[] = 'Username atau email sudah dipakai user lain.';
            } else {
                if ($newPass !== '') {
                    $hash = password_hash($newPass, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?');
                    $stmt->execute([$newUsername, $newEmail, $hash, $user['id']]);
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ? WHERE id = ?');
                    $stmt->execute([$newUsername, $newEmail, $user['id']]);
                }
                $_SESSION['username'] = $newUsername;
                $user['username'] = $newUsername;
                $user['email'] = $newEmail;
                $success = 'Akun berhasil diperbarui.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ganti Akun | MANCARE</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>body{font-family:'Plus Jakarta Sans',sans-serif;background-color:#f8fafc;}</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md space-y-4">
        <a href="index.php" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-slate-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Dashboard
        </a>
        <div class="bg-white w-full rounded-3xl p-6 lg:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.08)] border border-slate-100 space-y-6">
            <div>
                <h3 class="text-xl font-extrabold text-slate-800">Ganti Akun / Profil</h3>
                <p class="text-xs text-slate-500 mt-1">Perbarui username, email, atau password login kamu.</p>
            </div>

            <?php if ($errors): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-600 text-xs rounded-xl p-3 space-y-1">
                <?php foreach ($errors as $e): ?><p>&bull; <?= htmlspecialchars($e) ?></p><?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if ($success): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 text-xs rounded-xl p-3"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Username</label>
                    <input type="text" name="username" required value="<?= htmlspecialchars($user['username']) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-medium text-slate-800">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                    <input type="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-medium text-slate-800">
                </div>
                <hr class="border-slate-100">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Password Baru <span class="text-slate-400 font-normal">(kosongkan jika tidak ganti)</span></label>
                    <input type="password" name="new_password" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-medium text-slate-800">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-medium text-slate-800">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Password Saat Ini <span class="text-rose-500">*wajib</span></label>
                    <input type="password" name="current_password" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-medium text-slate-800">
                </div>
                <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-extrabold py-3.5 rounded-xl transition">
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
</body>
</html>
