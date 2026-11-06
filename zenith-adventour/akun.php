<?php
require_once __DIR__ . '/includes/session.php';
require_login('akun.php');

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Ambil data akun terbaru
$stmt = mysqli_prepare($koneksi, 'SELECT id, name, email, phone, password, created_at FROM users WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$akun = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form']) && $_POST['form'] === 'ganti_password') {
    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    $password_baru2 = $_POST['password_baru2'] ?? '';

    if ($password_lama === '' || $password_baru === '' || $password_baru2 === '') {
        $error = 'Semua kolom password wajib diisi.';
    } elseif (!password_verify($password_lama, $akun['password'])) {
        $error = 'Password lama yang Anda masukkan salah.';
    } elseif (strlen($password_baru) < 6) {
        $error = 'Password baru minimal 6 karakter.';
    } elseif ($password_baru !== $password_baru2) {
        $error = 'Konfirmasi password baru tidak cocok.';
    } elseif (password_verify($password_baru, $akun['password'])) {
        $error = 'Password baru tidak boleh sama dengan password lama.';
    } else {
        $hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($koneksi, 'UPDATE users SET password = ? WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'si', $hash_baru, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $success = 'Password berhasil diubah.';
        $akun['password'] = $hash_baru;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form']) && $_POST['form'] === 'update_profil') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '' || $phone === '') {
        $error = 'Nama dan nomor HP wajib diisi.';
    } elseif (!preg_match('/^[0-9+\-\s]{9,20}$/', $phone)) {
        $error = 'Format nomor HP tidak valid.';
    } else {
        $stmt = mysqli_prepare($koneksi, 'UPDATE users SET name = ?, phone = ? WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'ssi', $name, $phone, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['user_name'] = $name;
        $akun['name'] = $name;
        $akun['phone'] = $phone;
        $success = 'Profil berhasil diperbarui.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengaturan Akun - Zenith Tour & Travel</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
body { background:#fcfbf7; color:#333; }
.wrap { max-width:640px; margin:0 auto; padding:50px 20px 80px; }
.top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; }
.top-bar a { color:#1a2f27; font-size:14px; text-decoration:none; }
h1 { font-family:'Playfair Display',serif; color:#1a2f27; margin-bottom:6px; }
.sub { color:#888; font-size:14px; margin-bottom:28px; }
.card { background:#fff; padding:28px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.05); margin-bottom:25px; }
.card h3 { font-family:'Playfair Display',serif; color:#1a2f27; margin-bottom:18px; font-size:18px; }
.form-group { margin-bottom:16px; }
.form-group label { display:block; font-size:13px; font-weight:500; margin-bottom:6px; color:#444; }
.form-group input { width:100%; padding:11px 14px; border:1px solid #e5e0d8; border-radius:8px; font-size:14px; font-family:inherit; }
.form-group input:focus { outline:none; border-color:#c5a880; }
.form-group input[readonly] { background:#f7f5f0; color:#999; }
button[type=submit] { padding:11px 26px; background:#1a2f27; color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; }
button[type=submit]:hover { background:#c5a880; }
.alert { padding:12px 14px; border-radius:8px; font-size:13px; margin-bottom:18px; }
.alert-error { background:#fdeaea; color:#a33; }
.alert-success { background:#e7f5ec; color:#237040; }
.info-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f3eee7; font-size:13px; }
.info-row:last-child { border-bottom:none; }
.info-row span:first-child { color:#888; }
.info-row span:last-child { color:#1a2f27; font-weight:500; }
</style>
</head>
<body>
<div class="wrap">
    <div class="top-bar">
        <a href="index.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
    </div>
    <h1><i class="fa-solid fa-user-gear"></i> Pengaturan Akun</h1>
    <p class="sub">Kelola informasi profil dan keamanan akun Anda.</p>

    <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>

    <div class="card">
        <h3>Informasi Akun</h3>
        <div class="info-row"><span>Email</span><span><?= h($akun['email']) ?></span></div>
        <div class="info-row"><span>Bergabung sejak</span><span><?= date('d M Y', strtotime($akun['created_at'])) ?></span></div>
    </div>

    <div class="card">
        <h3>Ubah Nama & Nomor HP</h3>
        <form method="POST">
            <input type="hidden" name="form" value="update_profil">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" required value="<?= h($akun['name']) ?>">
            </div>
            <div class="form-group">
                <label>Nomor WhatsApp</label>
                <input type="tel" name="phone" required value="<?= h($akun['phone']) ?>">
                <small style="color:#999;font-size:11px;">Pastikan nomor ini aktif — dipakai untuk pemulihan akun.</small>
            </div>
            <button type="submit">Simpan Perubahan</button>
        </form>
    </div>

    <div class="card">
        <h3><i class="fa-solid fa-lock"></i> Ganti Password</h3>
        <form method="POST">
            <input type="hidden" name="form" value="ganti_password">
            <div class="form-group">
                <label>Password Lama</label>
                <input type="password" name="password_lama" required>
            </div>
            <div class="form-group">
                <label>Password Baru</label>
                <input type="password" name="password_baru" required minlength="6">
            </div>
            <div class="form-group">
                <label>Konfirmasi Password Baru</label>
                <input type="password" name="password_baru2" required minlength="6">
            </div>
            <button type="submit">Ubah Password</button>
        </form>
    </div>
</div>
</body>
</html>
