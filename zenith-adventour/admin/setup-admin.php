<?php
/**
 * Jalankan file ini SEKALI lewat browser untuk membuat akun admin pertama Anda.
 * SETELAH BERHASIL, SEGERA HAPUS FILE INI DARI SERVER (lewat File Manager InfinityFree)
 * agar tidak disalahgunakan orang lain untuk membuat akun admin baru.
 */
require_once __DIR__ . '/../includes/session.php';

$error = '';
$success = false;

// Guard sederhana: jika sudah ada akun admin, minta konfirmasi eksplisit lewat ?force=1
$cek = mysqli_query($koneksi, "SELECT COUNT(*) as jml FROM users WHERE role = 'admin'");
$sudah_ada_admin = mysqli_fetch_assoc($cek)['jml'] > 0;
$force = isset($_GET['force']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || strlen($password) < 6) {
        $error = 'Lengkapi semua data. Password minimal 6 karakter.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        $cekEmail = mysqli_prepare($koneksi, 'SELECT id FROM users WHERE email = ?');
        mysqli_stmt_bind_param($cekEmail, 's', $email);
        mysqli_stmt_execute($cekEmail);
        mysqli_stmt_store_result($cekEmail);

        if (mysqli_stmt_num_rows($cekEmail) > 0) {
            $error = 'Email sudah terdaftar. Gunakan email lain.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $jadi_master = $sudah_ada_admin ? 0 : 1; // hanya admin PERTAMA yang otomatis jadi master
            $ins = mysqli_prepare($koneksi, 'INSERT INTO users (name, email, password, role, is_master) VALUES (?, ?, ?, "admin", ?)');
            mysqli_stmt_bind_param($ins, 'sssi', $name, $email, $hash, $jadi_master);
            mysqli_stmt_execute($ins);
            mysqli_stmt_close($ins);
            $success = true;
        }
        mysqli_stmt_close($cekEmail);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Setup Admin Pertama</title>
<style>
body{font-family:sans-serif;background:#1a2f27;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;}
.box{background:#fff;max-width:420px;width:100%;padding:35px;border-radius:10px;}
input{width:100%;padding:10px;margin-bottom:12px;border:1px solid #ddd;border-radius:6px;box-sizing:border-box;}
button{width:100%;padding:12px;background:#1a2f27;color:#fff;border:none;border-radius:6px;cursor:pointer;}
.warn{background:#fdf3e2;color:#8a6100;padding:12px;border-radius:6px;margin-bottom:16px;font-size:13px;}
.err{background:#fdeaea;color:#a33;padding:12px;border-radius:6px;margin-bottom:16px;font-size:13px;}
.ok{background:#e7f5ec;color:#237040;padding:12px;border-radius:6px;font-size:13px;}
</style>
</head>
<body>
<div class="box">
<h2>Setup Akun Admin Pertama</h2><br>

<?php if ($success): ?>
    <div class="ok">
        Akun admin berhasil dibuat! Silakan <a href="login.php">login di sini</a>.<br><br>
        <strong>PENTING:</strong> Segera hapus file <code>admin/setup-admin.php</code> dari server Anda sekarang.
    </div>
<?php elseif ($sudah_ada_admin && !$force): ?>
    <div class="warn">
        Sudah ada akun admin terdaftar di sistem. Jika Anda tetap ingin membuat admin baru,
        buka tautan ini: <a href="setup-admin.php?force=1">setup-admin.php?force=1</a>.<br><br>
        Jika ini bukan Anda yang mencoba membuat akun admin, segera hapus file ini dari server.
    </div>
<?php else: ?>
    <?php if ($error): ?><div class="err"><?= h($error) ?></div><?php endif; ?>
    <form method="POST">
        <input type="text" name="name" placeholder="Nama Lengkap" required>
        <input type="email" name="email" placeholder="Email Admin" required>
        <input type="password" name="password" placeholder="Password (min 6 karakter)" required minlength="6">
        <button type="submit">Buat Akun Admin</button>
    </form>
<?php endif; ?>
</div>
</body>
</html>
