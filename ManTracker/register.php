<?php
require 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan Password wajib diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Username sudah digunakan, pilih yang lain.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            if ($stmt->execute([$username, $hashedPassword])) {
                $success = 'Pendaftaran berhasil! Silakan login.';
            } else {
                $error = 'Terjadi kesalahan sistem.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register | Man-Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { background-color: #0f172a; color: #cbd5e1; }</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-slate-800 p-8 rounded-2xl shadow-2xl w-full max-w-md border border-slate-700">
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto bg-gradient-to-br from-slate-700 to-slate-900 border border-slate-600 rounded-xl flex items-center justify-center shadow-lg mb-4">
                <span class="font-extrabold text-2xl text-amber-500 italic">MT</span>
            </div>
            <h1 class="text-2xl font-extrabold text-white">BERGABUNG SEKARANG</h1>
            <p class="text-sm text-slate-400 mt-1">Mulai lacak kedisiplinan Anda hari ini.</p>
        </div>

        <?php if($error): ?>
            <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 p-3 rounded-lg mb-4 text-sm text-center"><i class="fa-solid fa-triangle-exclamation"></i> <?= $error ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 p-3 rounded-lg mb-4 text-sm text-center"><i class="fa-solid fa-check"></i> <?= $success ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Username</label>
                <input type="text" name="username" required class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Password</label>
                <input type="password" name="password" required class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition">
            </div>
            <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-3.5 rounded-xl shadow-lg transition">
                DAFTAR AKUN
            </button>
        </form>
        <div class="text-center mt-6 text-sm">
            <span class="text-slate-500">Sudah punya akun?</span> <a href="login.php" class="text-amber-500 hover:text-amber-400 font-bold">Login di sini</a>
        </div>
    </div>
</body>
</html>