<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin('../login.php');
$__user = current_user();

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    if (is_admin_master()) {
        $id = (int) $_POST['delete_user'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
        $stmt->execute([$id]);
        $success = 'Akun user berhasil dihapus.';
    }
}

$users = $pdo->query("SELECT * FROM users WHERE role = 'user' ORDER BY full_name")->fetchAll();
?>
<?php $__admin_page = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Nusantara Beans</title>
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
            <?php if (is_admin_master()): ?>
                <a href="kelola-admin.php" class="<?php echo $__admin_page === 'kelola-admin.php' ? 'active-link' : ''; ?>">Kelola Admin</a>
            <?php endif; ?>
            <a href="../index.php"><i class="fas fa-arrow-left" style="margin-right:5px;"></i>Kembali ke Situs</a>
            <a href="../logout.php" class="link-logout"><i class="fas fa-sign-out-alt" style="margin-right:5px;"></i>Keluar</a>
        </div>
    </div>

    <div class="user-page-container">
        <h2 class="page-header-title">Kelola <span>User</span></h2>

        <?php if ($success): ?><div class="admin-alert admin-alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

        <div class="profile-card">
            <h3 class="profile-card-title"><i class="fas fa-users"></i> Daftar User Terdaftar</h3>
            <div class="table-responsive">
            <table class="admin-table">
                <tr><th>Nama</th><th>Email</th><th>No. HP</th><th>Terdaftar</th><?php if (is_admin_master()): ?><th>Aksi</th><?php endif; ?></tr>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo htmlspecialchars($u['phone'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($u['created_at']))); ?></td>
                    <?php if (is_admin_master()): ?>
                    <td>
                        <form method="POST" onsubmit="return confirm('Hapus akun user ini?');" style="display:inline;">
                            <button type="submit" name="delete_user" value="<?php echo (int)$u['id']; ?>" class="btn-delete">Hapus</button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                <tr><td colspan="5">Belum ada user terdaftar.</td></tr>
                <?php endif; ?>
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
