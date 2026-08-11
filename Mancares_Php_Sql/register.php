<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$old = ['username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Sesi form kedaluwarsa, silakan coba lagi.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $old['username'] = $username;
        $old['email'] = $email;

        if ($username === '' || strlen($username) < 3) $errors[] = 'Username minimal 3 karakter.';
        if (!preg_match('/^[a-zA-Z0-9_.]+$/', $username)) $errors[] = 'Username hanya boleh huruf, angka, titik, dan underscore.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
        if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';
        if ($password !== $confirm) $errors[] = 'Konfirmasi password tidak cocok.';

        if (empty($errors)) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $errors[] = 'Username atau email sudah terdaftar.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role, status, income) VALUES (?, ?, ?, "user", "active", 0)');
                $stmt->execute([$username, $email, $hash]);
                $newUserId = $pdo->lastInsertId();
                seed_default_account($pdo, $newUserId);

                header('Location: login.php?registered=1');
                exit;
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
<title>Daftar Akun | MANCARE</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>body{font-family:'Plus Jakarta Sans',sans-serif;background-color:#0f172a;}</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-3xl p-6 lg:p-8 shadow-2xl space-y-6 text-slate-200">
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-white font-bold text-2xl mx-auto">M</div>
            <h3 class="text-2xl font-extrabold text-white">MANCARE<span class="text-slate-500">.</span></h3>
            <p class="text-xs text-slate-400">Buat akun baru untuk mulai kelola keuanganmu.</p>
        </div>

        <?php if ($errors): ?>
        <div class="bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs rounded-xl p-3 space-y-1">
            <?php foreach ($errors as $e): ?><p>&bull; <?= htmlspecialchars($e) ?></p><?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2">Username</label>
                <input type="text" name="username" required value="<?= htmlspecialchars($old['username']) ?>" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3.5 font-medium text-white focus:outline-none focus:ring-2 focus:ring-slate-500">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2">Email</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($old['email']) ?>" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3.5 font-medium text-white focus:outline-none focus:ring-2 focus:ring-slate-500">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2">Password</label>
                <input type="password" name="password" required class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3.5 font-medium text-white focus:outline-none focus:ring-2 focus:ring-slate-500">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2">Konfirmasi Password</label>
                <input type="password" name="confirm_password" required class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3.5 font-medium text-white focus:outline-none focus:ring-2 focus:ring-slate-500">
            </div>
            <button type="submit" class="w-full bg-slate-100 hover:bg-white text-slate-900 font-extrabold py-3.5 rounded-xl transition">
                Daftar Sekarang
            </button>
        </form>
        <p class="text-center text-xs text-slate-400">Sudah punya akun? <a href="login.php" class="text-white font-bold hover:underline">Masuk di sini</a></p>
    </div>
</body>
</html>
