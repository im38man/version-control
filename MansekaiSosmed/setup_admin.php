<?php
// =====================================================================
// JALANKAN FILE INI SEKALI SAJA setelah import database (mansekai.sql)
// untuk membuat akun admin pertama. Setelah berhasil, HAPUS file ini
// dari server supaya tidak disalahgunakan orang lain.
// =====================================================================
require 'config/koneksi.php';

$pesan = "";
$sukses = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nama === '' || $username === '' || $password === '') {
        $pesan = "Semua field wajib diisi.";
    } elseif (strlen($password) < 6) {
        $pesan = "Password minimal 6 karakter.";
    } else {
        $cek = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($cek, "s", $username);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $pesan = "Username itu sudah dipakai.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = mysqli_prepare($koneksi, "INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, 'admin')");
            mysqli_stmt_bind_param($insert, "sss", $nama, $username, $hash);
            mysqli_stmt_execute($insert);
            $sukses = true;
            $pesan = "Akun admin '$username' berhasil dibuat! Silakan login, lalu HAPUS file setup_admin.php dari server.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin - Mansekai Study</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-box">
            <div class="brand"><i class="fa-solid fa-user-shield"></i> Setup Akun Admin</div>
            <p class="subtitle">Buat akun admin pertama untuk Mansekai Study</p>

            <?php if ($pesan): ?>
                <div class="alert <?= $sukses ? 'alert-success' : 'alert-error' ?>"><?= htmlspecialchars($pesan) ?></div>
            <?php endif; ?>

            <?php if (!$sukses): ?>
            <form method="POST">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" required autofocus>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-check"></i> Buat Akun Admin</button>
            </form>
            <?php else: ?>
                <a href="login.php" class="btn-primary" style="width:100%; justify-content:center; text-decoration:none;">
                    <i class="fa-solid fa-right-to-bracket"></i> Ke Halaman Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
