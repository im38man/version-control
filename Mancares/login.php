<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$info = '';
if (isset($_GET['registered'])) $info = 'Akun berhasil dibuat. Silakan login.';
if (isset($_GET['banned'])) $error = 'Akun kamu tidak aktif / diblokir. Hubungi admin.';
if (isset($_GET['timeout'])) $error = 'Sesi habis, silakan login kembali.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Sesi form kedaluwarsa, silakan coba lagi.';
    } else {
        $identity = trim($_POST['identity'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$identity, $identity]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $error = 'Username/email atau password salah.';
        } elseif ($user['status'] !== 'active') {
            $error = 'Akun kamu tidak aktif / diblokir. Hubungi admin.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            header('Location: ' . ($user['role'] === 'admin' ? 'admin/index.php' : 'index.php'));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk | MANCARE</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>body{font-family:'Plus Jakarta Sans',sans-serif;background-color:#0f172a;}</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-3xl p-6 lg:p-8 shadow-2xl space-y-6 text-slate-200">
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-white font-bold text-2xl mx-auto">M</div>
            <h3 class="text-2xl font-extrabold text-white">MANCARE<span class="text-slate-500">.</span></h3>
            <p class="text-xs text-slate-400">Wealth System &mdash; masuk untuk lanjut kelola keuanganmu.</p>
        </div>

        <?php if ($error): ?>
        <div class="bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs rounded-xl p-3"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($info): ?>
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs rounded-xl p-3"><?= htmlspecialchars($info) ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2">Username atau Email</label>
                <input type="text" name="identity" required autofocus class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3.5 font-medium text-white focus:outline-none focus:ring-2 focus:ring-slate-500">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2">Password</label>
                <input type="password" name="password" required class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3.5 font-medium text-white focus:outline-none focus:ring-2 focus:ring-slate-500">
            </div>
            <button type="submit" class="w-full bg-slate-100 hover:bg-white text-slate-900 font-extrabold py-3.5 rounded-xl transition">
                Masuk ke Sistem
            </button>
        </form>
        <p class="text-center text-xs text-slate-400">Belum punya akun? <a href="register.php" class="text-white font-bold hover:underline">Daftar di sini</a></p>
    </div>
</body>
</html>
