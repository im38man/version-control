<?php
require_once __DIR__ . '/includes/session.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($name === '' || $email === '' || $phone === '' || $password === '') {
        $error = 'Nama, email, nomor HP, dan password wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (!preg_match('/^[0-9+\-\s]{9,20}$/', $phone)) {
        $error = 'Format nomor HP tidak valid. Gunakan angka saja, contoh: 08123456789.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $password2) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $stmt = mysqli_prepare($koneksi, 'SELECT id FROM users WHERE email = ?');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = 'Email sudah terdaftar. Silakan login.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = mysqli_prepare($koneksi, 'INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, "user")');
            mysqli_stmt_bind_param($insert, 'ssss', $name, $email, $phone, $hash);
            if (mysqli_stmt_execute($insert)) {
                $success = true;
            } else {
                $error = 'Terjadi kesalahan, silakan coba lagi.';
            }
            mysqli_stmt_close($insert);
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Akun - Zenith Tour & Travel</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Poppins',sans-serif; background:#fcfbf7; color:#333; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:20px; }
.auth-box { background:#fff; max-width:420px; width:100%; padding:40px 35px; border-radius:12px; box-shadow:0 10px 40px rgba(0,0,0,0.08); }
.auth-box h1 { font-family:'Playfair Display',serif; color:#1a2f27; font-size:26px; margin-bottom:6px; text-align:center; }
.auth-box p.sub { text-align:center; color:#777; font-size:14px; margin-bottom:25px; }
.form-group { margin-bottom:16px; }
.form-group label { display:block; font-size:13px; font-weight:500; margin-bottom:6px; color:#444; }
.form-group input { width:100%; padding:12px 14px; border:1px solid #e5e0d8; border-radius:8px; font-size:14px; font-family:inherit; }
.form-group input:focus { outline:none; border-color:#c5a880; }
button[type=submit] { width:100%; padding:13px; background:#1a2f27; color:#fff; border:none; border-radius:8px; font-size:15px; font-weight:500; cursor:pointer; margin-top:8px; transition:background 0.3s; }
button[type=submit]:hover { background:#c5a880; }
.alert { padding:12px 14px; border-radius:8px; font-size:13px; margin-bottom:18px; }
.alert-error { background:#fdeaea; color:#a33; }
.alert-success { background:#e7f5ec; color:#237040; }
.bottom-link { text-align:center; margin-top:20px; font-size:13px; color:#666; }
.bottom-link a { color:#c5a880; font-weight:600; }
.back-home { display:block; text-align:center; margin-top:14px; font-size:13px; color:#999; }
</style>
</head>
<body>
<div class="auth-box">
    <h1>Buat Akun</h1>
    <p class="sub">Daftar untuk favoritkan destinasi, kirim pesan ke admin, dan konfirmasi pembayaran.</p>

    <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success">Pendaftaran berhasil! Silakan <a href="login.php">login di sini</a>.</div>
    <?php else: ?>
    <form method="POST" action="register.php">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="name" required value="<?= h($_POST['name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Nomor WhatsApp <span style="color:#c5423b;">*</span></label>
            <input type="tel" name="phone" placeholder="08123456xxx" required value="<?= h($_POST['phone'] ?? '') ?>">
            <small style="color:#999;font-size:11px;">Wajib diisi & aktif — dipakai untuk pemulihan akun via WhatsApp.</small>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required minlength="6">
        </div>
        <div class="form-group">
            <label>Konfirmasi Password</label>
            <input type="password" name="password2" required minlength="6">
        </div>
        <button type="submit">Daftar Sekarang</button>
    </form>
    <div class="bottom-link">Sudah punya akun? <a href="login.php">Masuk di sini</a></div>
    <?php endif; ?>
    <a href="index.php" class="back-home"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
</div>
</body>
</html>
