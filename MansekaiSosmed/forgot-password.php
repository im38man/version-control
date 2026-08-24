<?php
require 'includes/auth.php';
require 'config/koneksi.php';
require 'config/mail.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error   = "";
$sukses  = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Masukkan alamat email yang valid.";
    } else {
        $stmt = mysqli_prepare($koneksi, "SELECT id, nama, email FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        // Pesan sukses selalu sama walau email tidak ditemukan,
        // supaya orang lain tidak bisa menebak-nebak email mana yang terdaftar.
        $sukses = "Jika email tersebut terdaftar, link reset password sudah kami kirim. Silakan cek inbox (atau folder spam) email kamu.";

        if ($user) {
            $token      = bin2hex(random_bytes(32));
            $expiredAt  = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $update = mysqli_prepare($koneksi, "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
            mysqli_stmt_bind_param($update, "ssi", $token, $expiredAt, $user['id']);
            mysqli_stmt_execute($update);

            $baseUrl   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
            $resetLink = $baseUrl . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/reset-password.php?token=' . $token;

            kirimEmailResetPassword($user['email'], $user['nama'], $resetLink);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Mansekai Study</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-box">
            <div class="brand"><i class="fa-solid fa-key"></i> Lupa Password</div>
            <p class="subtitle">Masukkan email akun kamu, kami kirimkan link reset password</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($sukses): ?>
                <div class="alert alert-success"><?= htmlspecialchars($sukses) ?></div>
            <?php endif; ?>

            <?php if (!$sukses): ?>
            <form method="POST">
                <div class="form-group">
                    <label>Email Akun</label>
                    <input type="email" name="email" placeholder="email@contoh.com" required autofocus>
                </div>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-paper-plane"></i> Kirim Link Reset</button>
            </form>
            <?php endif; ?>

            <div class="auth-switch"><a href="login.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Login</a></div>
        </div>
    </div>
</body>
</html>
