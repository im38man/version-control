<?php
require_once __DIR__ . '/includes/session.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$submitted = false;
$wa_link = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Masukkan email yang valid.';
    } else {
        // Demi keamanan, kita tidak memberi tahu apakah email terdaftar atau tidak.
        // Pesan WA selalu ditampilkan agar tidak bisa dipakai menebak akun mana yang ada.
        $pesan = "Halo Admin Zenith Tour & Travel, saya lupa password akun saya.\n\nEmail akun: {$email}\n\nMohon bantu reset password saya. Terima kasih.";
        $wa_link = 'https://wa.me/62895333841200?text=' . rawurlencode($pesan);
        $submitted = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lupa Password - Zenith Tour & Travel</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Poppins',sans-serif; background:#fcfbf7; color:#333; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:20px; }
.auth-box { background:#fff; max-width:420px; width:100%; padding:40px 35px; border-radius:12px; box-shadow:0 10px 40px rgba(0,0,0,0.08); }
.auth-box h1 { font-family:'Playfair Display',serif; color:#1a2f27; font-size:24px; margin-bottom:6px; text-align:center; }
.auth-box p.sub { text-align:center; color:#777; font-size:13px; margin-bottom:25px; line-height:1.6; }
.form-group { margin-bottom:16px; }
.form-group label { display:block; font-size:13px; font-weight:500; margin-bottom:6px; color:#444; }
.form-group input { width:100%; padding:12px 14px; border:1px solid #e5e0d8; border-radius:8px; font-size:14px; font-family:inherit; }
.form-group input:focus { outline:none; border-color:#c5a880; }
button[type=submit] { width:100%; padding:13px; background:#1a2f27; color:#fff; border:none; border-radius:8px; font-size:15px; font-weight:500; cursor:pointer; margin-top:8px; transition:background 0.3s; }
button[type=submit]:hover { background:#c5a880; }
.alert { padding:12px 14px; border-radius:8px; font-size:13px; margin-bottom:18px; background:#fdeaea; color:#a33; }
.wa-box { text-align:center; padding:10px 0; }
.wa-box i { font-size:36px; color:#25D366; margin-bottom:12px; display:block; }
.wa-box p { font-size:13px; color:#555; margin-bottom:20px; line-height:1.7; }
.btn-wa { display:inline-flex; align-items:center; gap:10px; background:#25D366; color:#fff; padding:13px 26px; border-radius:30px; font-size:14px; font-weight:600; text-decoration:none; }
.btn-wa:hover { background:#1ebe5a; }
.bottom-link { text-align:center; margin-top:20px; font-size:13px; color:#666; }
.bottom-link a { color:#c5a880; font-weight:600; }
.back-home { display:block; text-align:center; margin-top:14px; font-size:13px; color:#999; }
</style>
</head>
<body>
<div class="auth-box">
    <?php if (!$submitted): ?>
        <h1>Lupa Password?</h1>
        <p class="sub">Masukkan email akun Anda. Kami akan siapkan pesan WhatsApp ke admin untuk verifikasi & reset password.</p>
        <?php if ($error): ?><div class="alert"><?= h($error) ?></div><?php endif; ?>
        <form method="POST" action="lupa-password.php">
            <div class="form-group">
                <label>Email Akun</label>
                <input type="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>">
            </div>
            <button type="submit">Lanjutkan</button>
        </form>
        <div class="bottom-link">Ingat password Anda? <a href="login.php">Masuk di sini</a></div>
    <?php else: ?>
        <div class="wa-box">
            <i class="fa-brands fa-whatsapp"></i>
            <h1 style="margin-bottom:14px;">Verifikasi via WhatsApp</h1>
            <p>Klik tombol di bawah untuk mengirim pesan ke <strong>Admin Zenith Tour & Travel</strong> lewat WhatsApp. Setelah identitas Anda terverifikasi, admin akan membuatkan password baru dan mengirimkannya langsung ke chat WhatsApp Anda.</p>
            <a href="<?= h($wa_link) ?>" target="_blank" class="btn-wa"><i class="fa-brands fa-whatsapp"></i> Hubungi Admin via WhatsApp</a>
        </div>
    <?php endif; ?>
    <a href="index.php" class="back-home"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
</div>
</body>
</html>
