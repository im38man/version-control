<?php
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/includes/pagination.php';

$msg = '';
$error = '';
$new_password_info = null; // ['name', 'phone', 'password', 'wa_link'] jika reset password baru saja terjadi

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_id = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $target = null;
    if ($target_id) {
        $stmt = mysqli_prepare($koneksi, 'SELECT id, name, phone, role, is_master FROM users WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $target_id);
        mysqli_stmt_execute($stmt);
        $target = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
    }

    if (!$target) {
        $error = 'Pengguna tidak ditemukan.';
    } elseif ($action === 'reset_password') {
        // Reset password boleh dilakukan admin biasa maupun Admin Master (kebutuhan layanan pelanggan sehari-hari)
        if ((int)$target['is_master'] === 1 && $target_id !== (int)$_SESSION['user_id']) {
            $error = 'Password Admin Master tidak bisa direset dari sini.';
        } else {
            $password_baru = substr(bin2hex(random_bytes(4)), 0, 8);
            $hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($koneksi, 'UPDATE users SET password = ? WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'si', $hash_baru, $target_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $pesan_wa = "Halo {$target['name']}, ini password baru akun Zenith Tour & Travel Anda:\n\n{$password_baru}\n\nSilakan login lalu segera ganti password ini di menu Pengaturan Akun. Terima kasih.";
            $wa_link = $target['phone'] ? ('https://wa.me/' . preg_replace('/[^0-9]/', '', $target['phone']) . '?text=' . rawurlencode($pesan_wa)) : '';

            $new_password_info = [
                'name' => $target['name'],
                'phone' => $target['phone'],
                'password' => $password_baru,
                'wa_link' => $wa_link,
            ];
            $msg = 'Password baru untuk ' . h($target['name']) . ' berhasil dibuat.';
        }
    } elseif (!is_master_admin()) {
        $error = 'Hanya Admin Master yang boleh mengelola peran & menghapus pengguna.';
    } elseif ($target_id === (int)$_SESSION['user_id']) {
        $error = 'Anda tidak bisa melakukan aksi ini terhadap akun Anda sendiri.';
    } elseif ((int)$target['is_master'] === 1) {
        $error = 'Akun Admin Master tidak bisa diubah atau dihapus dari sini.';
    } elseif ($action === 'promote') {
        if ($target['role'] === 'admin') {
            $error = 'Pengguna ini sudah menjadi admin.';
        } else {
            $stmt = mysqli_prepare($koneksi, "UPDATE users SET role = 'admin' WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $target_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $msg = h($target['name']) . ' berhasil dijadikan admin.';
        }
    } elseif ($action === 'demote') {
        if ($target['role'] !== 'admin') {
            $error = 'Pengguna ini bukan admin.';
        } else {
            $stmt = mysqli_prepare($koneksi, "UPDATE users SET role = 'user' WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $target_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $msg = 'Akses admin ' . h($target['name']) . ' berhasil dicabut.';
        }
    } elseif ($action === 'delete') {
        $stmt = mysqli_prepare($koneksi, 'DELETE FROM users WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $target_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $msg = 'Pengguna ' . h($target['name']) . ' berhasil dihapus.';
    } else {
        $error = 'Aksi tidak dikenali.';
    }
}

$users = mysqli_query($koneksi, "SELECT id, name, email, phone, role, is_master, created_at FROM users ORDER BY is_master DESC, role DESC, created_at DESC LIMIT " . ADMIN_PER_PAGE . " OFFSET " . get_page_offset());
$total_rows = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM users"))['c'];

$page_title = 'Pengguna';
$active_menu = 'users';
require __DIR__ . '/includes/admin_header.php';
?>
<h1>Pengguna Terdaftar</h1>
<p class="sub">Semua akun yang terdaftar di situs.</p>

<?php if ($msg): ?><div class="alert alert-success"><?= h($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>

<?php if ($new_password_info): ?>
<div class="card" style="border:1.5px solid #25D366;">
    <h3 style="font-family:'Playfair Display',serif;color:#1a2f27;margin-bottom:10px;">
        <i class="fa-brands fa-whatsapp" style="color:#25D366;"></i> Password Baru untuk <?= h($new_password_info['name']) ?>
    </h3>
    <p style="font-size:13px;color:#555;margin-bottom:12px;">Password baru ini hanya ditampilkan sekali. Segera kirimkan ke pelanggan lewat WhatsApp:</p>
    <div style="background:#f7f5f0;padding:14px 18px;border-radius:8px;font-family:monospace;font-size:18px;letter-spacing:1px;margin-bottom:14px;display:inline-block;">
        <?= h($new_password_info['password']) ?>
    </div><br>
    <?php if ($new_password_info['wa_link']): ?>
        <a href="<?= h($new_password_info['wa_link']) ?>" target="_blank" class="btn" style="background:#25D366;color:#fff;padding:10px 20px;">
            <i class="fa-brands fa-whatsapp"></i> Kirim ke WhatsApp Pelanggan
        </a>
    <?php else: ?>
        <p style="font-size:12px;color:#a33;">Pelanggan ini belum punya nomor HP tersimpan — sampaikan password secara manual.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if (!is_master_admin()): ?>
<div class="alert" style="background:#fdf3e2;color:#8a6100;">
    <i class="fa-solid fa-lock"></i> Anda login sebagai admin biasa (bukan Admin Master), jadi menghapus pengguna dan menjadikan pengguna sebagai admin tidak bisa dilakukan dari sini. Reset password tetap bisa Anda lakukan. Hubungi Admin Master untuk aksi lainnya.
</div>
<?php endif; ?>

<div class="card">
<div class="table-scroll">
<table>
    <tr><th>Nama</th><th>Email</th><th>Telepon</th><th>Peran</th><th>Bergabung</th><th>Aksi</th></tr>
    <?php while ($u = mysqli_fetch_assoc($users)): ?>
    <tr>
        <td><?= h($u['name']) ?></td>
        <td><?= h($u['email']) ?></td>
        <td><?= h($u['phone'] ?: '-') ?></td>
        <td>
            <?php if ((int)$u['is_master'] === 1): ?>
                <span class="badge" style="color:#8a5a00;background:#fdf0d8;"><i class="fa-solid fa-crown"></i> Admin Master</span>
            <?php elseif ($u['role'] === 'admin'): ?>
                <span class="badge" style="color:#1a2f27;background:#eee7d8;">Admin</span>
            <?php else: ?>
                <span class="badge" style="color:#237040;background:#e7f5ec;">User</span>
            <?php endif; ?>
        </td>
        <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
        <?php if (is_master_admin()): ?>
        <td>
            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <?php if ((int)$u['is_master'] !== 1): ?>
                    <form method="POST" onsubmit="return confirm('Buat password baru untuk <?= h($u['name']) ?>? Password lamanya akan langsung tidak berlaku.');">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="action" value="reset_password">
                        <button type="submit" class="btn" style="background:#1f6fc9;color:#fff;">Reset Password</button>
                    </form>
                <?php endif; ?>
                <?php if ((int)$u['is_master'] === 1 || (int)$u['id'] === (int)$_SESSION['user_id']): ?>
                    <span style="color:#ccc;font-size:12px;align-self:center;">-</span>
                <?php else: ?>
                    <?php if ($u['role'] === 'user'): ?>
                        <form method="POST" onsubmit="return confirm('Jadikan <?= h($u['name']) ?> sebagai admin?');">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="action" value="promote">
                            <button type="submit" class="btn btn-approve">Jadikan Admin</button>
                        </form>
                    <?php else: ?>
                        <form method="POST" onsubmit="return confirm('Cabut akses admin dari <?= h($u['name']) ?>?');">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="action" value="demote">
                            <button type="submit" class="btn" style="background:#c98a1f;color:#fff;">Cabut Admin</button>
                        </form>
                    <?php endif; ?>
                    <form method="POST" onsubmit="return confirm('Hapus pengguna <?= h($u['name']) ?>? Semua data booking, favorit, dan pesan miliknya akan ikut terhapus.');">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-reject">Hapus</button>
                    </form>
                <?php endif; ?>
            </div>
        </td>
        <?php else: ?>
        <td>
            <?php if ((int)$u['is_master'] !== 1): ?>
            <form method="POST" onsubmit="return confirm('Buat password baru untuk <?= h($u['name']) ?>? Password lamanya akan langsung tidak berlaku.');">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <input type="hidden" name="action" value="reset_password">
                <button type="submit" class="btn" style="background:#1f6fc9;color:#fff;">Reset Password</button>
            </form>
            <?php else: ?>
                <span style="color:#ccc;font-size:12px;">-</span>
            <?php endif; ?>
        </td>
        <?php endif; ?>
    </tr>
    <?php endwhile; ?>
</table>
</div>
<?php render_pagination($total_rows); ?>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
