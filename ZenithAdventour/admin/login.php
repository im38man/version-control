<?php
require_once __DIR__ . '/../includes/session.php';

if (is_logged_in() && is_admin()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($koneksi, 'SELECT id, name, email, password, role, is_master FROM users WHERE email = ? AND role = "admin"');
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['is_master'] = (int)$user['is_master'];
        header('Location: index.php');
        exit;
    }
    $error = 'Email/password salah atau akun ini bukan admin.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Admin - Zenith Tour & Travel</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Poppins',sans-serif; background:#1a2f27; display:flex; align-items:center; justify-content:center; min-height:100vh; }
.box { background:#fff; max-width:380px; width:100%; padding:40px 32px; border-radius:12px; }
h1 { font-family:'Playfair Display',serif; color:#1a2f27; text-align:center; margin-bottom:22px; }
.form-group { margin-bottom:16px; }
.form-group label { display:block; font-size:13px; margin-bottom:6px; color:#444; }
.form-group input { width:100%; padding:12px; border:1px solid #e5e0d8; border-radius:8px; font-size:14px; }
button { width:100%; padding:13px; background:#1a2f27; color:#fff; border:none; border-radius:8px; font-size:14px; cursor:pointer; margin-top:6px; }
button:hover { background:#c5a880; }
.alert { background:#fdeaea; color:#a33; padding:10px 14px; border-radius:8px; font-size:13px; margin-bottom:16px; }
</style>
</head>
<body>
<div class="box">
    <h1>Admin Login</h1>
    <?php if ($error): ?><div class="alert"><?= h($error) ?></div><?php endif; ?>
    <form method="POST">
        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
        <button type="submit">Masuk sebagai Admin</button>
    </form>
</div>
</body>
</html>
