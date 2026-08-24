<?php
require 'includes/auth.php';
requireAdmin();
require 'config/koneksi.php';

$pageTitle  = "Kelola User - Admin Mansekai";
$activePage = "admin-user.php";

// ================= HANDLE HAPUS USER =================
if (isset($_GET['hapus_user'])) {
    $userIdHapus = (int) $_GET['hapus_user'];

    if ($userIdHapus === (int) $_SESSION['user_id']) {
        header("Location: admin-user.php?user_error=" . urlencode("Tidak bisa menghapus akun sendiri."));
        exit;
    }

    // Hapus dulu file fisik dokumen & foto profil milik user itu, sebelum
    // row-nya kehapus dari database (biar gak ninggalin sampah file di server).
    // Data di tabel lain (notepad, arus kas, pengingat, profil, dashboard,
    // pengajuan materi) otomatis ikut kehapus lewat ON DELETE CASCADE.
    $folderDokumen = __DIR__ . '/uploads/documents/' . $userIdHapus . '/';
    if (is_dir($folderDokumen)) {
        foreach (glob($folderDokumen . '*') as $file) {
            if (is_file($file)) unlink($file);
        }
        @rmdir($folderDokumen);
    }
    foreach (['jpg', 'jpeg', 'png', 'gif', 'webp'] as $ext) {
        $avatarFile = __DIR__ . '/uploads/avatars/' . $userIdHapus . '.' . $ext;
        if (is_file($avatarFile)) unlink($avatarFile);
    }

    // Cuma boleh hapus akun role 'user' lewat sini (bukan sesama admin),
    // biar gak ada resiko semua akses admin ikut kehapus.
    $stmt = mysqli_prepare($koneksi, "DELETE FROM users WHERE id = ? AND role = 'user'");
    mysqli_stmt_bind_param($stmt, "i", $userIdHapus);
    mysqli_stmt_execute($stmt);

    header("Location: admin-user.php?user_deleted=1");
    exit;
}

// ================= HANDLE UBAH ROLE USER =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'ubah_role') {
    $userId    = (int) ($_POST['user_id'] ?? 0);
    $roleBaru  = ($_POST['role'] ?? '') === 'admin' ? 'admin' : 'user';

    if ($userId === (int) $_SESSION['user_id']) {
        header("Location: admin-user.php?user_error=" . urlencode("Tidak bisa mengubah role akun sendiri."));
        exit;
    }

    $stmt = mysqli_prepare($koneksi, "UPDATE users SET role=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "si", $roleBaru, $userId);
    mysqli_stmt_execute($stmt);

    header("Location: admin-user.php?role_updated=1");
    exit;
}

// Pencarian sederhana berdasarkan nama/username
$cari = trim($_GET['cari'] ?? '');
if ($cari !== '') {
    $stmt = mysqli_prepare($koneksi, "SELECT id, nama, username, role, created_at FROM users WHERE nama LIKE ? OR username LIKE ? ORDER BY created_at DESC");
    $like = "%$cari%";
    mysqli_stmt_bind_param($stmt, "ss", $like, $like);
    mysqli_stmt_execute($stmt);
    $semuaUser = mysqli_stmt_get_result($stmt);
} else {
    $semuaUser = mysqli_query($koneksi, "SELECT id, nama, username, role, created_at FROM users ORDER BY created_at DESC");
}

$totalUser = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users"))['total'];
$totalAdmin = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users WHERE role='admin'"))['total'];

include 'includes/header.php';
?>
<div class="header-title">
    <h1>Kelola User (Admin)</h1>
    <p>Lihat, cari, ubah role, atau hapus akun user yang terdaftar.</p>
</div>

<?php if (isset($_GET['user_deleted'])): ?><div class="alert alert-success">Akun user berhasil dihapus beserta semua datanya.</div><?php endif; ?>
<?php if (isset($_GET['role_updated'])): ?><div class="alert alert-success">Role user berhasil diperbarui.</div><?php endif; ?>
<?php if (isset($_GET['user_error'])): ?><div class="alert alert-error"><?= htmlspecialchars($_GET['user_error']) ?></div><?php endif; ?>

<div class="grid-2" style="margin-bottom: 20px;">
    <div class="card">
        <h3>Total User</h3>
        <p style="font-size: 1.8rem; font-weight: 700; margin-top: 8px;"><?= (int) $totalUser ?></p>
    </div>
    <div class="card">
        <h3>Total Admin</h3>
        <p style="font-size: 1.8rem; font-weight: 700; margin-top: 8px;"><?= (int) $totalAdmin ?></p>
    </div>
</div>

<div class="card">
    <h3>Daftar User</h3>
    <form method="GET" style="margin: 15px 0; display: flex; gap: 8px;">
        <input type="text" name="cari" placeholder="Cari nama atau username..." value="<?= htmlspecialchars($cari) ?>" style="flex-grow: 1;">
        <button type="submit" class="btn-secondary"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
        <?php if ($cari !== ''): ?>
            <a href="admin-user.php" class="btn-secondary">Reset</a>
        <?php endif; ?>
    </form>

    <table class="admin-table">
        <thead>
            <tr><th>Nama</th><th>Username</th><th>Role</th><th>Terdaftar</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($semuaUser) === 0): ?>
                <tr><td colspan="5">Tidak ada user yang cocok.</td></tr>
            <?php endif; ?>
            <?php while ($u = mysqli_fetch_assoc($semuaUser)): ?>
                <?php $isSelf = (int)$u['id'] === (int)$_SESSION['user_id']; ?>
                <tr>
                    <td><?= htmlspecialchars($u['nama']) ?><?= $isSelf ? ' <span style="color:var(--text-muted); font-size:0.8em;">(kamu)</span>' : '' ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><span class="badge <?= $u['role'] === 'admin' ? 'selesai' : 'proses' ?>"><?= htmlspecialchars(ucfirst($u['role'])) ?></span></td>
                    <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td class="aksi" style="display:flex; gap:6px; flex-wrap:wrap;">
                        <a href="lihat-profil.php?id=<?= (int)$u['id'] ?>" class="btn-secondary"><i class="fa-solid fa-eye"></i></a>
                        <?php if (!$isSelf): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Ubah role <?= htmlspecialchars(addslashes($u['nama'])) ?> jadi <?= $u['role'] === 'admin' ? 'user' : 'admin' ?>?');">
                                <input type="hidden" name="aksi" value="ubah_role">
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <input type="hidden" name="role" value="<?= $u['role'] === 'admin' ? 'user' : 'admin' ?>">
                                <button type="submit" class="btn-secondary" title="Jadikan <?= $u['role'] === 'admin' ? 'User' : 'Admin' ?>">
                                    <i class="fa-solid fa-user-gear"></i>
                                </button>
                            </form>
                            <?php if ($u['role'] === 'user'): ?>
                                <a href="admin-user.php?hapus_user=<?= (int)$u['id'] ?>" class="btn-danger" onclick="return confirm('Yakin mau hapus akun <?= htmlspecialchars(addslashes($u['nama'])) ?>? Semua data (notepad, arus kas, pengingat, profil, dashboard, dokumen) ikut terhapus permanen dan TIDAK BISA dikembalikan.')"><i class="fa-solid fa-trash"></i></a>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:var(--text-muted); font-size:0.85rem;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
