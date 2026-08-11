<?php
require_once __DIR__ . '/includes/auth.php';

// Kalau sudah ada admin, halaman ini otomatis terkunci.
$stmt = $pdo->query("SELECT COUNT(*) AS c FROM users WHERE role = 'admin'");
$adminExists = ((int)$stmt->fetch()['c']) > 0;

$errors = [];
$done = false;

if (!$adminExists && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Sesi form kedaluwarsa, silakan reload halaman.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (strlen($username) < 3) $errors[] = 'Username minimal 3 karakter.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
        if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';

        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role, status, income) VALUES (?, ?, ?, "admin", "active", 0)');
            $stmt->execute([$username, $email, $hash]);
            require_once __DIR__ . '/includes/functions.php';
            seed_default_account($pdo, $pdo->lastInsertId());
            $done = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Setup Admin | MANCARE</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>body{font-family:'Plus Jakarta Sans',sans-serif;background-color:#0f172a;}</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-3xl p-6 lg:p-8 shadow-2xl space-y-6 text-slate-200">
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-white font-bold text-2xl mx-auto">M</div>
            <h3 class="text-2xl font-extrabold text-white">Setup Admin</h3>
        </div>

        <?php if ($adminExists && !$done): ?>
            <div class="bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs rounded-xl p-3">
                Akun admin sudah pernah dibuat. Halaman ini terkunci demi keamanan.
                Hapus file <code class="text-white">setup.php</code> dari server, lalu login lewat <a href="login.php" class="underline font-bold">login.php</a>.
            </div>
        <?php elseif ($done): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs rounded-xl p-3 space-y-2">
                <p>Akun admin berhasil dibuat.</p>
                <p class="text-rose-300 font-bold">PENTING: hapus file setup.php dari server sekarang juga.</p>
            </div>
            <a href="login.php" class="block text-center w-full bg-slate-100 hover:bg-white text-slate-900 font-extrabold py-3.5 rounded-xl transition">Ke Halaman Login</a>
        <?php else: ?>
            <?php if ($errors): ?>
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs rounded-xl p-3 space-y-1">
                <?php foreach ($errors as $e): ?><p>&bull; <?= htmlspecialchars($e) ?></p><?php endforeach; ?>
            </div>
            <?php endif; ?>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Username Admin</label>
                    <input type="text" name="username" required class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3.5 font-medium text-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Email Admin</label>
                    <input type="email" name="email" required class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3.5 font-medium text-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Password</label>
                    <input type="password" name="password" required class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3.5 font-medium text-white">
                </div>
                <button type="submit" class="w-full bg-slate-100 hover:bg-white text-slate-900 font-extrabold py-3.5 rounded-xl transition">
                    Buat Akun Admin
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
