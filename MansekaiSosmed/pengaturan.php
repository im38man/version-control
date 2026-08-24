<?php
require 'includes/auth.php';
requireLogin();

require 'config/koneksi.php';

$pageTitle  = "Pengaturan Akun - Mansekai";
$activePage = "pengaturan.php";

$errorPassword = "";
$suksesPassword = "";
$errorEmail = "";
$suksesEmail = "";
$errorUsername = "";
$suksesUsername = "";

// ==== Ganti Password ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ganti_password'])) {
    $passwordLama = $_POST['password_lama'] ?? '';
    $passwordBaru = $_POST['password_baru'] ?? '';
    $konfirmasi   = $_POST['password_konfirmasi'] ?? '';

    $stmt = mysqli_prepare($koneksi, "SELECT password FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$row || !password_verify($passwordLama, $row['password'])) {
        $errorPassword = "Password lama salah.";
    } elseif (strlen($passwordBaru) < 6) {
        $errorPassword = "Password baru minimal 6 karakter.";
    } elseif ($passwordBaru !== $konfirmasi) {
        $errorPassword = "Konfirmasi password tidak cocok.";
    } else {
        $hash = password_hash($passwordBaru, PASSWORD_DEFAULT);
        $update = mysqli_prepare($koneksi, "UPDATE users SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($update, "si", $hash, $_SESSION['user_id']);
        mysqli_stmt_execute($update);
        $suksesPassword = "Password berhasil diperbarui.";
    }
}

// ==== Ganti Username ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ganti_username'])) {
    $usernameBaru = trim($_POST['username'] ?? '');

    $stmt = mysqli_prepare($koneksi, "SELECT username, username_changed_at FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $userSekarang = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    $sisaHari = 0;
    if ($userSekarang && $userSekarang['username_changed_at']) {
        $kapanBolehGanti = strtotime($userSekarang['username_changed_at'] . ' +30 days');
        if (time() < $kapanBolehGanti) {
            $sisaHari = ceil(($kapanBolehGanti - time()) / 86400);
        }
    }

    if ($sisaHari > 0) {
        $errorUsername = "Kamu baru bisa ganti username lagi dalam $sisaHari hari.";
    } elseif ($usernameBaru === '' || strlen($usernameBaru) < 3 || strlen($usernameBaru) > 50) {
        $errorUsername = "Username harus 3-50 karakter.";
    } elseif (!preg_match('/^[a-zA-Z0-9_.]+$/', $usernameBaru)) {
        $errorUsername = "Username cuma boleh huruf, angka, underscore, dan titik.";
    } elseif ($usernameBaru === $userSekarang['username']) {
        $errorUsername = "Username baru sama dengan username sekarang.";
    } else {
        $cek = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ? AND id != ?");
        mysqli_stmt_bind_param($cek, "si", $usernameBaru, $_SESSION['user_id']);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $errorUsername = "Username tersebut sudah dipakai orang lain.";
        } else {
            $update = mysqli_prepare($koneksi, "UPDATE users SET username = ?, username_changed_at = NOW() WHERE id = ?");
            mysqli_stmt_bind_param($update, "si", $usernameBaru, $_SESSION['user_id']);
            mysqli_stmt_execute($update);
            $_SESSION['username'] = $usernameBaru;
            $suksesUsername = "Username berhasil diganti. Kamu bisa ganti lagi 30 hari dari sekarang.";
        }
    }
}

// ==== Update Email ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_email'])) {
    $emailBaru = trim($_POST['email'] ?? '');

    if ($emailBaru === '' || !filter_var($emailBaru, FILTER_VALIDATE_EMAIL)) {
        $errorEmail = "Format email tidak valid.";
    } else {
        $cek = mysqli_prepare($koneksi, "SELECT id FROM users WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($cek, "si", $emailBaru, $_SESSION['user_id']);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $errorEmail = "Email tersebut sudah dipakai akun lain.";
        } else {
            $update = mysqli_prepare($koneksi, "UPDATE users SET email = ? WHERE id = ?");
            mysqli_stmt_bind_param($update, "si", $emailBaru, $_SESSION['user_id']);
            mysqli_stmt_execute($update);
            $suksesEmail = "Email berhasil disimpan. Email ini akan dipakai kalau kamu lupa password.";
        }
    }
}

// Ambil data akun terbaru
$stmt = mysqli_prepare($koneksi, "SELECT nama, username, username_changed_at, email, role FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$akun = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$sisaHariTampil = 0;
if (!empty($akun['username_changed_at'])) {
    $kapanBolehGantiTampil = strtotime($akun['username_changed_at'] . ' +30 days');
    if (time() < $kapanBolehGantiTampil) {
        $sisaHariTampil = ceil(($kapanBolehGantiTampil - time()) / 86400);
    }
}

include 'includes/header.php';
?>

<div class="header-title">
    <h1>Pengaturan Akun</h1>
    <p>Kelola email dan password akun kamu di sini.</p>
</div>

<div class="grid-2">
    <div class="card">
        <h3>Informasi Akun</h3>
        <ul class="module-list">
            <li class="module-item"><span><i class="fa-solid fa-user"></i> Nama</span><b><?= htmlspecialchars($akun['nama']) ?></b></li>
            <li class="module-item"><span><i class="fa-solid fa-shield-halved"></i> Role</span><span class="badge selesai"><?= htmlspecialchars(ucfirst($akun['role'])) ?></span></li>
        </ul>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

        <h3>Username</h3>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 15px;">
            Username cuma bisa diganti 30 hari sekali.
        </p>
        <?php if ($errorUsername): ?><div class="alert alert-error"><?= htmlspecialchars($errorUsername) ?></div><?php endif; ?>
        <?php if ($suksesUsername): ?><div class="alert alert-success"><?= htmlspecialchars($suksesUsername) ?></div><?php endif; ?>
        <?php if ($sisaHariTampil > 0): ?>
            <div class="alert alert-error">
                Username saat ini: <b><?= htmlspecialchars($akun['username']) ?></b>.
                Kamu bisa ganti lagi dalam <?= (int)$sisaHariTampil ?> hari.
            </div>
        <?php else: ?>
            <form method="POST">
                <input type="hidden" name="ganti_username" value="1">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($akun['username']) ?>" minlength="3" maxlength="50" pattern="[a-zA-Z0-9_.]+" required>
                </div>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-at"></i> Simpan Username</button>
            </form>
        <?php endif; ?>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

        <h3>Email Akun</h3>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 15px;">
            Email ini dipakai untuk menerima link reset kalau kamu lupa password. Pastikan email masih aktif.
        </p>
        <?php if ($errorEmail): ?><div class="alert alert-error"><?= htmlspecialchars($errorEmail) ?></div><?php endif; ?>
        <?php if ($suksesEmail): ?><div class="alert alert-success"><?= htmlspecialchars($suksesEmail) ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="simpan_email" value="1">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($akun['email'] ?? '') ?>" placeholder="email@contoh.com" required>
            </div>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-envelope"></i> Simpan Email</button>
        </form>
    </div>

    <div class="card">
        <h3>Ganti Password</h3>
        <?php if ($errorPassword): ?><div class="alert alert-error"><?= htmlspecialchars($errorPassword) ?></div><?php endif; ?>
        <?php if ($suksesPassword): ?><div class="alert alert-success"><?= htmlspecialchars($suksesPassword) ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="ganti_password" value="1">
            <div class="form-group">
                <label>Password Lama</label>
                <input type="password" name="password_lama" required>
            </div>
            <div class="form-group">
                <label>Password Baru</label>
                <input type="password" name="password_baru" required>
            </div>
            <div class="form-group">
                <label>Konfirmasi Password Baru</label>
                <input type="password" name="password_konfirmasi" required>
            </div>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-key"></i> Update Password</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
