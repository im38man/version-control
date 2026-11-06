<?php
require_once __DIR__ . '/includes/session.php';

if (is_logged_in()) {
    header('Location: ' . (is_admin() ? 'admin/index.php' : 'index.php'));
    exit;
}

$error = '';
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'index.php';
// Cegah open-redirect: hanya izinkan nama file lokal, bukan URL luar.
if (!preg_match('/^(admin\/)?[a-zA-Z0-9_\-]+\.php(\?[a-zA-Z0-9_=&%.\-]*)?$/', $redirect)) {
    $redirect = 'index.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = mysqli_prepare($koneksi, 'SELECT id, name, email, password, role, is_master FROM users WHERE email = ?');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['is_master'] = (int)$user['is_master'];

            if ($user['role'] === 'admin') {
                header('Location: admin/index.php');
            } else {
                header('Location: ' . $redirect);
            }
            exit;
        } else {
            $error = 'Email atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk - Zenith Tour & Travel</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Poppins',sans-serif; background:#fcfbf7; color:#333; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:20px; }
.auth-box { background:#fff; max-width:400px; width:100%; padding:40px 35px; border-radius:12px; box-shadow:0 10px 40px rgba(0,0,0,0.08); }
.auth-box h1 { font-family:'Playfair Display',serif; color:#1a2f27; font-size:26px; margin-bottom:6px; text-align:center; }
.auth-box p.sub { text-align:center; color:#777; font-size:14px; margin-bottom:25px; }
.form-group { margin-bottom:16px; }
.form-group label { display:block; font-size:13px; font-weight:500; margin-bottom:6px; color:#444; }
.form-group input { width:100%; padding:12px 14px; border:1px solid #e5e0d8; border-radius:8px; font-size:14px; font-family:inherit; }
.form-group input:focus { outline:none; border-color:#c5a880; }
button[type=submit] { width:100%; padding:13px; background:#1a2f27; color:#fff; border:none; border-radius:8px; font-size:15px; font-weight:500; cursor:pointer; margin-top:8px; transition:background 0.3s; }
button[type=submit]:hover { background:#c5a880; }
.alert { padding:12px 14px; border-radius:8px; font-size:13px; margin-bottom:18px; background:#fdeaea; color:#a33; }
.bottom-link { text-align:center; margin-top:20px; font-size:13px; color:#666; }
.bottom-link a { color:#c5a880; font-weight:600; }
.back-home { display:block; text-align:center; margin-top:14px; font-size:13px; color:#999; }
</style>
</head>
<body>
<div class="auth-box">
    <h1>Masuk</h1>
    <p class="sub">Login untuk favoritkan destinasi, kirim pesan ke admin, dan konfirmasi pembayaran.</p>
    <?php if ($error): ?><div class="alert"><?= h($error) ?></div><?php endif; ?>
    <form method="POST" action="login.php">
        <input type="hidden" name="redirect" value="<?= h($redirect) ?>">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
            <div style="text-align:right;margin-top:6px;"><a href="lupa-password.php" style="font-size:12px;color:#c5a880;">Lupa password?</a></div>
        </div>
        <button type="submit">Masuk</button>
    </form>
    <div class="bottom-link">Belum punya akun? <a href="register.php">Daftar di sini</a></div>
    <a href="index.php" class="back-home"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
</div>
</body>
</html>
