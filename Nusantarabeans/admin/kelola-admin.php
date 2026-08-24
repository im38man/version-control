<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin_master('../login.php');
$__user = current_user();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_admin'])) {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($full_name === '' || $email === '' || $password === '') {
            $error = 'Semua kolom wajib diisi.';
        } elseif (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email sudah terdaftar.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)');
                $stmt->execute([$full_name, $email, $hash, 'admin']);
                $success = 'Akun admin baru berhasil dibuat.';
            }
        }
    } elseif (isset($_POST['delete_admin'])) {
        $id = (int) $_POST['delete_admin'];
        // Tidak boleh menghapus akun admin_master lewat sini
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'");
        $stmt->execute([$id]);
        $success = 'Akun admin berhasil dihapus.';
    }
}

$admins = $pdo->query("SELECT * FROM users WHERE role IN ('admin','admin_master') ORDER BY role, full_name")->fetchAll();
?>
<?php $__admin_page = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Admin - Nusantara Beans</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-theme.css?v=<?php echo @filemtime(__DIR__ . '/../assets/css/admin-theme.css') ?: time(); ?>">
    <link rel="stylesheet" href="../assets/css/admin-extra.css?v=<?php echo @filemtime(__DIR__ . '/../assets/css/admin-extra.css') ?: time(); ?>">
</head>
<body>

    <div class="admin-topbar">
        <div class="navbar-brand">Nusantara<span>Beans</span> <span class="topbar-suffix">Panel Admin</span></div>
        <button type="button" class="admin-burger" id="adminBurger" aria-label="Buka menu"><i class="fas fa-bars"></i></button>
        <div class="admin-menu" id="adminMenu">
            <a href="index.php" class="<?php echo $__admin_page === 'index.php' ? 'active-link' : ''; ?>">Dashboard</a>
            <a href="pesan.php" class="<?php echo $__admin_page === 'pesan.php' ? 'active-link' : ''; ?>">Pesan</a>
            <a href="kelola-pesanan.php" class="<?php echo $__admin_page === 'kelola-pesanan.php' ? 'active-link' : ''; ?>">Kelola Pesanan</a>
            <a href="kelola-user.php" class="<?php echo $__admin_page === 'kelola-user.php' ? 'active-link' : ''; ?>">Kelola User</a>
            <a href="kelola-admin.php" class="<?php echo $__admin_page === 'kelola-admin.php' ? 'active-link' : ''; ?>">Kelola Admin</a>
            <a href="../index.php"><i class="fas fa-arrow-left" style="margin-right:5px;"></i>Kembali ke Situs</a>
            <a href="../logout.php" class="link-logout"><i class="fas fa-sign-out-alt" style="margin-right:5px;"></i>Keluar</a>
        </div>
    </div>

    <div class="user-page-container">
        <h2 class="page-header-title">Kelola <span>Admin</span></h2>

        <?php if ($error): ?><div class="admin-alert admin-alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="admin-alert admin-alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

        <div class="profile-card">
            <h3 class="profile-card-title"><i class="fas fa-user-plus"></i> Tambah Admin Baru</h3>
            <form method="POST">
                <input type="hidden" name="create_admin" value="1">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="full_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                <button type="submit" class="btn-save">Buat Admin</button>
            </form>
        </div>

        <div class="profile-card">
            <h3 class="profile-card-title"><i class="fas fa-users-cog"></i> Daftar Admin</h3>
            <div class="table-responsive">
            <table class="admin-table">
                <tr><th>Nama</th><th>Email</th><th>Role</th><th>Aksi</th></tr>
                <?php foreach ($admins as $a): ?>
                <tr>
                    <td><?php echo htmlspecialchars($a['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($a['email']); ?></td>
                    <td><span class="admin-badge<?php echo $a['role'] === 'admin_master' ? ' badge-master' : ''; ?>"><?php echo $a['role'] === 'admin_master' ? 'Admin Master' : 'Admin'; ?></span></td>
                    <td>
                        <?php if ($a['role'] === 'admin'): ?>
                        <form method="POST" onsubmit="return confirm('Hapus akun admin ini?');" style="display:inline;">
                            <button type="submit" name="delete_admin" value="<?php echo (int)$a['id']; ?>" class="btn-delete">Hapus</button>
                        </form>
                        <?php else: ?>
                            <em>—</em>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            </div>
        </div>
    </div>

    <script>
        const adminBurger = document.getElementById('adminBurger');
        const adminMenu = document.getElementById('adminMenu');
        if (adminBurger && adminMenu) {
            adminBurger.addEventListener('click', function (e) {
                adminMenu.classList.toggle('active');
                e.stopPropagation();
            });
            document.addEventListener('click', function (e) {
                if (!adminMenu.contains(e.target) && !adminBurger.contains(e.target)) {
                    adminMenu.classList.remove('active');
                }
            });
        }
    </script>
</body>
</html>
