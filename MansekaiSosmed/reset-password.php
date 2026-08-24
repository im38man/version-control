<?php
require 'includes/auth.php';
require 'config/koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error   = "";
$sukses  = "";
$token   = trim($_GET['token'] ?? $_POST['token'] ?? '');
$user    = null;

if ($token === '') {
    $error = "Link reset password tidak valid.";
} else {
    $stmt = mysqli_prepare($koneksi, "SELECT id, nama, reset_token_expiry FROM users WHERE reset_token = ?");
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$user) {
        $error = "Link reset password tidak valid atau sudah pernah dipakai.";
    } elseif (strtotime($user['reset_token_expiry']) < time()) {
        $error = "Link reset password sudah kedaluwarsa. Silakan minta link baru.";
        $user = null;
    }
}

if ($user && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $passwordBaru = $_POST['password_baru'] ?? '';
    $konfirmasi   = $_POST['password_konfirmasi'] ?? '';

    if (strlen($passwordBaru) < 6) {
        $error = "Password baru minimal 6 karakter.";
    } elseif ($passwordBaru !== $konfirmasi) {
        $error = "Konfirmasi password tidak cocok.";
    } else {
        $hash = password_hash($passwordBaru, PASSWORD_DEFAULT);
        $update = mysqli_prepare($koneksi, "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        mysqli_stmt_bind_param($update, "si", $hash, $user['id']);
        mysqli_stmt_execute($update);

        $sukses = "Password berhasil diubah. Silakan login dengan password baru kamu.";
        $user = null; // Sembunyikan form setelah berhasil
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Mansekai Study</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-box">
            <div class="brand"><i class="fa-solid fa-lock-open"></i> Reset Password</div>
            <p class="subtitle">Buat password baru untuk akun kamu</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($sukses): ?>
                <div class="alert alert-success"><?= htmlspecialchars($sukses) ?></div>
            <?php endif; ?>

            <?php if ($user): ?>
            <form method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="form-group">
                    <label>Password Baru</label>
                    <input type="password" name="password_baru" required autofocus>
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="password_konfirmasi" required>
                </div>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-check"></i> Simpan Password Baru</button>
            </form>
            <?php endif; ?>

            <?php if (!$user): ?>
            <div class="auth-switch">
                <a href="login.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Login</a>
                <?php if ($error): ?> &middot; <a href="forgot-password.php">Minta link baru</a><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
