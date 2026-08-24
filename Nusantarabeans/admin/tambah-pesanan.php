<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin('../login.php');
$__user = current_user();

$allowedChannel = ['whatsapp', 'tiktok', 'instagram', 'facebook', 'shopee', 'website'];
$allowedStatus = ['menunggu_pembayaran', 'menunggu_konfirmasi', 'dikonfirmasi', 'dikirim', 'selesai', 'dibatalkan'];

$errors = [];
$old = [
    'full_name' => '',
    'phone' => '',
    'channel' => 'whatsapp',
    'shipping_address' => '',
    'status' => 'dikonfirmasi',
];
$oldItems = [['name' => '', 'price' => '', 'qty' => 1]];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['phone'] = trim($_POST['phone'] ?? '');
    $old['channel'] = $_POST['channel'] ?? 'whatsapp';
    $old['shipping_address'] = trim($_POST['shipping_address'] ?? '');
    $old['status'] = $_POST['status'] ?? 'dikonfirmasi';

    $itemNames = $_POST['item_name'] ?? [];
    $itemPrices = $_POST['item_price'] ?? [];
    $itemQtys = $_POST['item_qty'] ?? [];

    $oldItems = [];
    $items = [];
    for ($i = 0; $i < count($itemNames); $i++) {
        $name = trim($itemNames[$i]);
        $price = (float) str_replace(['.', ','], '', $itemPrices[$i] ?? 0);
        $qty = (int) ($itemQtys[$i] ?? 0);
        $oldItems[] = ['name' => $name, 'price' => $itemPrices[$i] ?? '', 'qty' => $qty ?: 1];
        if ($name !== '' && $price > 0 && $qty > 0) {
            $items[] = ['product_name' => $name, 'price' => $price, 'qty' => $qty, 'subtotal' => $price * $qty];
        }
    }
    if (empty($oldItems)) {
        $oldItems = [['name' => '', 'price' => '', 'qty' => 1]];
    }

    if ($old['full_name'] === '') $errors[] = 'Nama pelanggan wajib diisi.';
    if ($old['phone'] === '') $errors[] = 'Nomor HP / username kontak wajib diisi.';
    if (!in_array($old['channel'], $allowedChannel)) $errors[] = 'Channel tidak valid.';
    if (!in_array($old['status'], $allowedStatus)) $errors[] = 'Status tidak valid.';
    if (empty($items)) $errors[] = 'Minimal 1 produk dengan nama, harga, dan qty yang valid.';

    if (empty($errors)) {
        $total = array_sum(array_column($items, 'subtotal'));
        $order_code = 'NB-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('INSERT INTO orders (order_code, user_id, channel, full_name, phone, shipping_address, payment_method, total_amount, status)
                VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $order_code,
                $old['channel'],
                $old['full_name'],
                $old['phone'],
                $old['shipping_address'] !== '' ? $old['shipping_address'] : '-',
                'manual_' . $old['channel'],
                $total,
                $old['status'],
            ]);
            $order_id = $pdo->lastInsertId();

            $stmtItem = $pdo->prepare('INSERT INTO order_items (order_id, product_name, price, qty, subtotal) VALUES (?, ?, ?, ?, ?)');
            foreach ($items as $it) {
                $stmtItem->execute([$order_id, $it['product_name'], $it['price'], $it['qty'], $it['subtotal']]);
            }

            $pdo->commit();
            header('Location: kelola-pesanan.php?manual=1');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Terjadi kesalahan saat menyimpan pesanan. Coba lagi.';
        }
    }
}

$channelLabels = [
    'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'fab fa-whatsapp'],
    'tiktok' => ['label' => 'TikTok', 'icon' => 'fab fa-tiktok'],
    'instagram' => ['label' => 'Instagram', 'icon' => 'fab fa-instagram'],
    'facebook' => ['label' => 'Facebook', 'icon' => 'fab fa-facebook-f'],
    'shopee' => ['label' => 'Shopee', 'icon' => 'fas fa-shopping-bag'],
    'website' => ['label' => 'Website', 'icon' => 'fas fa-globe'],
];
$statusLabels = [
    'menunggu_pembayaran' => 'Menunggu Pembayaran',
    'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
    'dikonfirmasi' => 'Dikonfirmasi',
    'dikirim' => 'Dikirim',
    'selesai' => 'Selesai',
    'dibatalkan' => 'Dibatalkan',
];
?>
<?php $__admin_page = 'kelola-pesanan.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pesanan Manual - Nusantara Beans</title>
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
            <a href="index.php">Dashboard</a>
            <a href="pesan.php">Pesan</a>
            <a href="kelola-pesanan.php" class="active-link">Kelola Pesanan</a>
            <a href="kelola-user.php">Kelola User</a>
            <?php if (is_admin_master()): ?>
                <a href="kelola-admin.php">Kelola Admin</a>
            <?php endif; ?>
            <a href="../index.php"><i class="fas fa-arrow-left" style="margin-right:5px;"></i>Kembali ke Situs</a>
            <a href="../logout.php" class="link-logout"><i class="fas fa-sign-out-alt" style="margin-right:5px;"></i>Keluar</a>
        </div>
    </div>

    <div class="user-page-container">
        <h2 class="page-header-title">Tambah <span>Pesanan Manual</span></h2>
        <p style="margin-top:-10px; margin-bottom:20px; color:#7a7268;">Untuk pesanan yang masuk lewat WhatsApp, TikTok, Instagram, Facebook, atau Shopee.</p>

        <?php if ($errors): ?>
            <div class="admin-alert admin-alert-error">
                <?php foreach ($errors as $e): ?><div><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="profile-card">
            <form method="POST" id="manualOrderForm">
                <div class="form-group">
                    <label class="form-label">Channel / Sumber Pesanan</label>
                    <select name="channel" class="form-input">
                        <?php foreach ($channelLabels as $key => $ch): if ($key === 'website') continue; ?>
                            <option value="<?php echo $key; ?>" <?php echo $old['channel'] === $key ? 'selected' : ''; ?>><?php echo $ch['label']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Pelanggan</label>
                    <input type="text" name="full_name" class="form-input" value="<?php echo htmlspecialchars($old['full_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">No. HP / Username Kontak</label>
                    <input type="text" name="phone" class="form-input" value="<?php echo htmlspecialchars($old['phone']); ?>" placeholder="08xxxxxxxxxx atau @username" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat Pengiriman / Catatan (opsional)</label>
                    <textarea name="shipping_address" class="form-input" rows="3" placeholder="Boleh dikosongkan jika COD/ambil di toko/diambil dari chat"><?php echo htmlspecialchars($old['shipping_address']); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Status Pesanan</label>
                    <select name="status" class="form-input">
                        <?php foreach ($statusLabels as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php echo $old['status'] === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <hr style="border:none; border-top:1px solid #efe8db; margin:22px 0;">

                <label class="form-label">Produk Dipesan</label>
                <div id="itemRows">
                    <?php foreach ($oldItems as $it): ?>
                    <div class="item-row">
                        <div class="form-group">
                            <input type="text" name="item_name[]" class="form-input" placeholder="Nama produk" value="<?php echo htmlspecialchars($it['name']); ?>">
                        </div>
                        <div class="form-group">
                            <input type="number" name="item_price[]" class="form-input" placeholder="Harga satuan" value="<?php echo htmlspecialchars($it['price']); ?>" min="0">
                        </div>
                        <div class="form-group">
                            <input type="number" name="item_qty[]" class="form-input" placeholder="Qty" value="<?php echo (int) $it['qty']; ?>" min="1">
                        </div>
                        <button type="button" class="btn-remove-row" onclick="removeItemRow(this)"><i class="fas fa-trash"></i></button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn-outline-gold" onclick="addItemRow()" style="margin-bottom:22px;"><i class="fas fa-plus"></i> Tambah Produk</button>

                <div style="display:flex; gap:12px; flex-wrap:wrap;">
                    <button type="submit" class="btn-save"><i class="fas fa-save" style="margin-right:6px;"></i>Simpan Pesanan</button>
                    <a href="kelola-pesanan.php" class="btn-outline-gold">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <template id="itemRowTemplate">
        <div class="item-row">
            <div class="form-group">
                <input type="text" name="item_name[]" class="form-input" placeholder="Nama produk">
            </div>
            <div class="form-group">
                <input type="number" name="item_price[]" class="form-input" placeholder="Harga satuan" min="0">
            </div>
            <div class="form-group">
                <input type="number" name="item_qty[]" class="form-input" placeholder="Qty" min="1" value="1">
            </div>
            <button type="button" class="btn-remove-row" onclick="removeItemRow(this)"><i class="fas fa-trash"></i></button>
        </div>
    </template>

    <script>
        function addItemRow() {
            const tpl = document.getElementById('itemRowTemplate');
            document.getElementById('itemRows').appendChild(tpl.content.cloneNode(true));
        }
        function removeItemRow(btn) {
            const rows = document.querySelectorAll('#itemRows .item-row');
            if (rows.length <= 1) return;
            btn.closest('.item-row').remove();
        }

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
