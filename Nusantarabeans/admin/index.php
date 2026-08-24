<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin('../login.php');
$__user = current_user();

// Statistik ringkas untuk dashboard
$__stat_users = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$__stat_orders = (int) $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$__stat_pending_orders = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('menunggu_pembayaran','menunggu_konfirmasi')")->fetchColumn();
$__stat_unread_msgs = (int) $pdo->query("SELECT COUNT(*) FROM messages WHERE sender_role = 'user' AND is_read = 0")->fetchColumn();
?>
<?php $__admin_page = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Nusantara Beans</title>
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
        <h2 class="page-header-title">Dashboard <span>Admin</span></h2>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div>
                    <div class="stat-value"><?php echo number_format($__stat_users, 0, ',', '.'); ?></div>
                    <div class="stat-label">Total User</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-receipt"></i></div>
                <div>
                    <div class="stat-value"><?php echo number_format($__stat_orders, 0, ',', '.'); ?></div>
                    <div class="stat-label">Total Pesanan</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                <div>
                    <div class="stat-value"><?php echo number_format($__stat_pending_orders, 0, ',', '.'); ?></div>
                    <div class="stat-label">Menunggu Konfirmasi</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-comment-dots"></i></div>
                <div>
                    <div class="stat-value"><?php echo number_format($__stat_unread_msgs, 0, ',', '.'); ?></div>
                    <div class="stat-label">Pesan Belum Dibaca</div>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <h3 class="profile-card-title"><i class="fas fa-user-shield"></i> Selamat Datang, <?php echo htmlspecialchars($__user['name']); ?></h3>
            <p>Anda login sebagai
                <span class="admin-badge"><?php echo $__user['role'] === 'admin_master' ? 'Admin Master' : 'Admin'; ?></span>
            </p>
            <p style="margin-top:15px;">Gunakan menu di atas untuk mengelola akun user<?php echo is_admin_master() ? ' dan akun admin.' : '.'; ?></p>
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
