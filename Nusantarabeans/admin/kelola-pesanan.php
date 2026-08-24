<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin('../login.php');
$__user = current_user();

$success = '';
if (isset($_GET['manual'])) {
    $success = 'Pesanan manual berhasil ditambahkan.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int) $_POST['order_id'];
    $status = $_POST['status'] ?? '';
    $allowed = ['menunggu_pembayaran','menunggu_konfirmasi','dikonfirmasi','dikirim','selesai','dibatalkan'];
    if (in_array($status, $allowed)) {
        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $order_id]);
        $success = 'Status pesanan berhasil diperbarui.';
    }
}

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $pdo->prepare('SELECT DISTINCT o.* FROM orders o
        LEFT JOIN order_items oi ON oi.order_id = o.id
        WHERE o.order_code LIKE :q
           OR o.full_name LIKE :q
           OR o.phone LIKE :q
           OR o.shipping_address LIKE :q
           OR o.payment_method LIKE :q
           OR o.channel LIKE :q
           OR o.status LIKE :q
           OR o.total_amount LIKE :q
           OR o.created_at LIKE :q
           OR DATE_FORMAT(o.created_at, "%d-%m-%Y") LIKE :q
           OR DATE_FORMAT(o.created_at, "%d/%m/%Y") LIKE :q
           OR DATE_FORMAT(o.created_at, "%d %m %Y") LIKE :q
           OR oi.product_name LIKE :q
        ORDER BY o.created_at DESC');
    $stmt->execute(['q' => $like]);
    $orders = $stmt->fetchAll();
} else {
    $orders = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC')->fetchAll();
}

$statusLabels = [
    'menunggu_pembayaran' => 'Menunggu Pembayaran',
    'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
    'dikonfirmasi' => 'Dikonfirmasi',
    'dikirim' => 'Dikirim',
    'selesai' => 'Selesai',
    'dibatalkan' => 'Dibatalkan',
];

$channelLabels = [
    'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'fab fa-whatsapp'],
    'tiktok' => ['label' => 'TikTok', 'icon' => 'fab fa-tiktok'],
    'instagram' => ['label' => 'Instagram', 'icon' => 'fab fa-instagram'],
    'facebook' => ['label' => 'Facebook', 'icon' => 'fab fa-facebook-f'],
    'shopee' => ['label' => 'Shopee', 'icon' => 'fas fa-shopping-bag'],
    'website' => ['label' => 'Website', 'icon' => 'fas fa-globe'],
];
?>
<?php $__admin_page = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Nusantara Beans</title>
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
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <h2 class="page-header-title" style="margin-bottom:0;">Kelola <span>Pesanan</span></h2>
            <a href="tambah-pesanan.php" class="btn-outline-gold"><i class="fas fa-plus"></i> Tambah Pesanan Manual</a>
        </div>
        <p style="margin-top:8px; margin-bottom:20px; color:#7a7268;">Pesanan dari WhatsApp, TikTok, Instagram, Facebook, atau Shopee bisa dicatat manual lewat tombol di atas.</p>

        <form method="GET" class="order-search-form">
            <input type="text" name="q" class="form-input" placeholder="Cari kode pesanan, nama, atau no. HP..." value="<?php echo htmlspecialchars($q); ?>">
            <button type="submit" class="btn-outline-gold"><i class="fas fa-search"></i> Cari</button>
            <?php if ($q !== ''): ?><a href="kelola-pesanan.php" class="btn-outline-gold">Reset</a><?php endif; ?>
        </form>

        <?php if ($success): ?><div class="admin-alert admin-alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

        <?php if (empty($orders) && $q !== ''): ?>
        <div class="profile-card"><p>Tidak ada pesanan yang cocok dengan pencarian "<?php echo htmlspecialchars($q); ?>".</p></div>
        <?php elseif (empty($orders)): ?>
        <div class="profile-card"><p>Belum ada pesanan masuk.</p></div>
        <?php endif; ?>

        <?php foreach ($orders as $order): ?>
        <?php
            $stmtItems = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
            $stmtItems->execute([$order['id']]);
            $orderItems = $stmtItems->fetchAll();
        ?>
        <div class="order-card">
            <div class="order-head">
                <div>
                    <strong><?php echo htmlspecialchars($order['order_code']); ?></strong>
                    — <?php echo htmlspecialchars($order['full_name']); ?> (<?php echo htmlspecialchars($order['phone']); ?>)
                    <br>
                    <?php $__ch = $channelLabels[$order['channel'] ?? 'website'] ?? $channelLabels['website']; ?>
                    <span class="channel-badge" data-channel="<?php echo htmlspecialchars($order['channel'] ?? 'website'); ?>"><i class="<?php echo $__ch['icon']; ?>"></i> <?php echo $__ch['label']; ?></span>
                    <small style="margin-left:8px;"><?php echo htmlspecialchars(date('d M Y H:i', strtotime($order['created_at']))); ?></small>
                </div>
                <span class="order-status-badge" data-status="<?php echo htmlspecialchars($order['status']); ?>"><?php echo $statusLabels[$order['status']] ?? $order['status']; ?></span>
            </div>

            <p><strong>Alamat:</strong> <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
            <p><strong>Total:</strong> Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></p>
            <?php if ($order['payment_proof']): ?>
                <p><strong>Bukti Transfer:</strong>
                    <a href="../<?php echo htmlspecialchars($order['payment_proof']); ?>" target="_blank" class="proof-link">Lihat File</a>
                </p>
            <?php endif; ?>

            <div class="table-responsive">
            <table class="order-table">
                <tr><th>Produk</th><th>Qty</th><th>Subtotal</th></tr>
                <?php foreach ($orderItems as $it): ?>
                <tr>
                    <td><?php echo htmlspecialchars($it['product_name']); ?></td>
                    <td><?php echo (int)$it['qty']; ?></td>
                    <td>Rp <?php echo number_format($it['subtotal'], 0, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            </div>

            <form method="POST" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                <select name="status" class="status-select">
                    <?php foreach ($statusLabels as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo $order['status'] === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="update_status" value="1" class="btn-save" style="width:auto; padding:8px 18px;">Update Status</button>
            </form>
        </div>
        <?php endforeach; ?>
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
