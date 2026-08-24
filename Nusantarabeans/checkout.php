<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM cart_items WHERE user_id = ? ORDER BY added_at DESC');
$stmt->execute([$user_id]);
$cart = $stmt->fetchAll();

$subtotalAll = 0;
$totalItemsCount = 0;
foreach ($cart as $item) {
    $subtotalAll += $item['price'] * $item['qty'];
    $totalItemsCount += $item['qty'];
}
$shippingFee = !empty($cart) ? 15000 : 0;
$pphAmount = round($subtotalAll * 0.02);
$grandTotal = !empty($cart) ? $subtotalAll + $shippingFee + $pphAmount : 0;

$checkout_errors = $_SESSION['checkout_errors'] ?? [];
unset($_SESSION['checkout_errors']);

function rp($n) { return 'Rp ' . number_format($n, 0, ',', '.'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Nusantara Beans</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 512 512%22><path fill=%22%23C5A059%22 d=%22M96 96c0-35.3 28.7-64 64-64H384c35.3 0 64 28.7 64 64v32H96V96zM48 224h416c26.5 0 48 21.5 48 48v32c0 53-43 96-96 96H160c-53 0-96-43-96-96V272c0-26.5 21.5-48 48-48zm352 160h32c35.3 0 64-28.7 64-64V288H432v96zM32 448H480c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32-14.3-32-32s14.3-32 32-32z%22/></svg>">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ================= VARIABEL TEMA ELEGAN ================= */
        :root {
            --color-coffee: #2C1E16; 
            --color-gold: #C5A059;   
            --color-gold-hover: #D4B572;
            --color-cream: #F8F5F0;  
            --color-text: #333333;
            --color-white: #FFFFFF;
            --font-heading: 'Playfair Display', serif;
            --font-body: 'Poppins', sans-serif;
        }

        /* ================= RESET & DASAR ================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html {
            scroll-behavior: smooth;
        }
        body {
            font-family: var(--font-body);
            background-color: var(--color-cream);
            color: var(--color-text);
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }

        /* ================= NAVBAR (Sesuai index.html) ================= */
        .navbar {
            background-color: var(--color-coffee);
            color: var(--color-gold);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 5%;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        .navbar-brand-wrapper {
            display: flex;
            flex-direction: column;
        }
        .navbar-brand {
            font-family: var(--font-heading);
            font-size: clamp(18px, 3.5vw, 22px);
            font-weight: 700;
            letter-spacing: 1px;
            color: var(--color-gold);
            line-height: 1.1;
        }
        .navbar-tagline {
            font-size: clamp(8px, 1.5vw, 10px);
            font-weight: 400;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.75);
            margin-top: 2px;
        }
        .navbar-right {
            display: flex;
            align-items: center;
            gap: clamp(10px, 3vw, 20px);
        }
        .burger-container {
            position: relative;
        }
        .burger-btn {
            font-size: clamp(18px, 3vw, 22px);
            cursor: pointer;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            border-right: 1px solid rgba(197, 160, 89, 0.3);
            padding-right: 15px;
        }
        .burger-btn:hover { color: var(--color-white); }
        
        .social-dropdown {
            position: absolute;
            top: 40px;
            right: 0;
            background: var(--color-coffee);
            border: 1px solid var(--color-gold);
            border-radius: 8px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 101;
        }
        .social-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .social-dropdown i {
            font-size: 18px;
            cursor: pointer;
            transition: color 0.3s;
        }
        .social-dropdown i:hover { color: var(--color-white); }

        .navbar-icons {
            display: flex;
            gap: clamp(12px, 2.5vw, 20px);
            align-items: center;
        }
        .navbar-icons i {
            font-size: clamp(16px, 2.5vw, 18px);
            cursor: pointer;
            transition: color 0.3s;
        }
        .navbar-icons i:hover, .navbar-icons i.active-icon { color: var(--color-white); }

        /* ================= SEARCH DROPDOWN BAWAH NAVBAR ================= */
        .search-dropdown-bar {
            position: fixed;
            top: 55px;
            left: 0;
            width: 100%;
            background: var(--color-coffee);
            border-bottom: 2px solid var(--color-gold);
            padding: 15px 5%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
            z-index: 99;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-15px);
            transition: all 0.3s ease-in-out;
        }
        .search-dropdown-bar.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .search-bar-inner {
            display: flex;
            width: 100%;
            max-width: 600px;
            background: var(--color-white);
            border-radius: 30px;
            overflow: hidden;
            border: 2px solid var(--color-gold);
        }
        .search-bar-input {
            flex: 1;
            padding: 10px 20px;
            border: none;
            outline: none;
            font-family: var(--font-body);
            font-size: 14px;
            color: var(--color-text);
        }
        .search-bar-btn {
            background: var(--color-gold);
            color: var(--color-white);
            border: none;
            padding: 0 20px;
            cursor: pointer;
            font-size: 15px;
            transition: background 0.3s;
        }
        .search-bar-btn:hover {
            background: var(--color-coffee);
            color: var(--color-gold);
        }

        /* ================= CART SECTION CONTAINER ================= */
        .cart-page-container {
            flex: 1;
            max-width: 1200px;
            width: 92%;
            margin: 95px auto 50px auto;
        }

        .page-header-title {
            font-family: var(--font-heading);
            font-size: clamp(24px, 4vw, 34px);
            color: var(--color-coffee);
            margin-bottom: 25px;
        }
        .page-header-title span { color: var(--color-gold); }

        .cart-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            align-items: flex-start;
        }

        /* Cart Items List */
        .cart-items-wrapper {
            background: var(--color-white);
            border-radius: 8px;
            border: 1px solid rgba(197, 160, 89, 0.2);
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
            overflow: hidden;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .cart-table th {
            background-color: var(--color-coffee);
            color: var(--color-gold);
            font-family: var(--font-body);
            font-weight: 600;
            font-size: 13.5px;
            padding: 14px 18px;
            letter-spacing: 0.5px;
        }

        .cart-table td {
            padding: 18px;
            border-bottom: 1px solid rgba(197, 160, 89, 0.15);
            vertical-align: middle;
            font-size: 13.5px;
        }

        .cart-item-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .item-select-checkbox {
            width: 18px;
            height: 18px;
            accent-color: var(--color-gold);
            cursor: pointer;
            flex-shrink: 0;
        }

        .select-all-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        .select-all-wrapper input {
            width: 16px;
            height: 16px;
            accent-color: var(--color-gold);
            cursor: pointer;
        }

        .cart-item-img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            background: var(--color-cream);
            border-radius: 6px;
            padding: 5px;
            border: 1px solid rgba(197, 160, 89, 0.2);
        }

        .cart-item-detail h4 {
            font-family: var(--font-heading);
            font-size: 16px;
            color: var(--color-coffee);
            margin-bottom: 3px;
        }

        .cart-item-detail span {
            font-size: 12px;
            color: #777;
        }

        /* Quantity Control */
        .qty-control {
            display: inline-flex;
            align-items: center;
            border: 1px solid rgba(197, 160, 89, 0.4);
            border-radius: 4px;
            overflow: hidden;
            background: var(--color-cream);
        }

        .qty-btn {
            background: transparent;
            border: none;
            padding: 6px 10px;
            cursor: pointer;
            color: var(--color-coffee);
            font-size: 12px;
            transition: background 0.3s;
        }
        .qty-btn:hover {
            background: var(--color-gold);
            color: var(--color-white);
        }

        .qty-input {
            width: 35px;
            text-align: center;
            border: none;
            background: transparent;
            font-family: var(--font-body);
            font-size: 13px;
            font-weight: 500;
            outline: none;
        }

        .item-subtotal {
            font-weight: 600;
            color: var(--color-coffee);
        }

        .remove-item-btn {
            background: transparent;
            border: none;
            color: #d9534f;
            cursor: pointer;
            font-size: 16px;
            transition: transform 0.2s;
        }
        .remove-item-btn:hover { transform: scale(1.15); }

        /* Cart Summary Card */
        .cart-summary-card {
            background: var(--color-white);
            border-radius: 8px;
            border: 1px solid rgba(197, 160, 89, 0.2);
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
            padding: 24px;
        }

        .summary-title {
            font-family: var(--font-heading);
            font-size: 20px;
            color: var(--color-coffee);
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(197, 160, 89, 0.2);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 14px;
            font-size: 13.5px;
            color: #555;
        }

        .summary-row.total {
            font-size: 16px;
            font-weight: 600;
            color: var(--color-coffee);
            border-top: 1px dashed rgba(197, 160, 89, 0.4);
            padding-top: 15px;
            margin-top: 15px;
        }

        .summary-row.total span:last-child {
            color: var(--color-gold);
        }

        .btn-checkout {
            display: block;
            width: 100%;
            padding: 12px;
            background: var(--color-gold);
            color: var(--color-white);
            border: none;
            border-radius: 30px;
            font-family: var(--font-body);
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .btn-checkout:hover {
            background: var(--color-coffee);
            color: var(--color-gold);
            transform: translateY(-2px);
        }

        .empty-cart-message {
            text-align: center;
            padding: 40px 20px;
            color: #777;
        }
        .empty-cart-message i {
            font-size: 48px;
            color: var(--color-gold);
            margin-bottom: 15px;
        }

        /* ================= FOOTER (Sesuai index.html) ================= */
        .site-footer {
            background: #1A120D;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            padding: 50px 5% 20px;
            margin-top: auto;
        }
        .footer-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 35px;
        }
        .footer-col h4 {
            font-family: var(--font-heading);
            color: var(--color-gold);
            font-size: 18px;
            margin-bottom: 15px;
        }
        .footer-brand {
            font-family: var(--font-heading);
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 1px;
            color: var(--color-white);
            margin-bottom: 12px;
        }
        .footer-brand span { color: var(--color-gold); }
        .footer-desc { margin-bottom: 15px; line-height: 1.6; }
        
        .footer-socials {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .footer-socials a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(197, 160, 89, 0.3);
            border-radius: 50%;
            color: var(--color-white);
            font-size: 15px;
            transition: all 0.3s ease;
        }
        .footer-socials a:hover {
            background: var(--color-gold);
            color: var(--color-coffee);
            transform: translateY(-3px);
        }

        .footer-contact li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
            line-height: 1.5;
        }
        .footer-contact i { color: var(--color-gold); font-size: 15px; margin-top: 3px; }

        .subscribe-form { display: flex; align-items: center; }
        .subscribe-form input {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid rgba(197, 160, 89, 0.4);
            background: rgba(255, 255, 255, 0.05);
            color: var(--color-white);
            font-family: var(--font-body);
            border-radius: 4px 0 0 4px;
            outline: none;
            font-size: 13px;
        }
        .subscribe-form button {
            padding: 10px 16px;
            border-radius: 0 4px 4px 0;
            border: 1px solid var(--color-gold);
            height: 100%;
            background: var(--color-gold);
            color: var(--color-white);
            cursor: pointer;
        }
        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .footer-bottom span { color: var(--color-gold); }

        /* Responsive Layout Umum */
        @media (max-width: 900px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }
            .cart-table th, .cart-table td {
                padding: 12px 10px;
            }
        }

        /* ================= RESPONSIVE TABLE MOBILE ================= */
        @media (max-width: 768px) {
            .cart-table, 
            .cart-table thead, 
            .cart-table tbody, 
            .cart-table th, 
            .cart-table td, 
            .cart-table tr {
                display: block;
            }

            /* Sembunyikan header tabel bawaan */
            .cart-table thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            .cart-table tr {
                border: 1px solid rgba(197, 160, 89, 0.3);
                margin-bottom: 15px;
                border-radius: 8px;
                background: var(--color-white);
                padding: 12px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.02);
                position: relative;
            }

            .cart-table td {
                border: none;
                position: relative;
                padding: 8px 0;
                text-align: right;
            }

            /* Penyesuaian khusus untuk kolom produk/gambar di mobile */
            .cart-table td:nth-child(1) {
                text-align: left;
                border-bottom: 1px solid rgba(197, 160, 89, 0.1);
                padding-bottom: 12px;
                margin-bottom: 8px;
            }

            /* Label otomatis menggunakan atribut data-label */
            .cart-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 50%;
                padding-right: 10px;
                font-weight: 600;
                text-align: left;
                color: var(--color-coffee);
                font-size: 13px;
            }

            .cart-table td:nth-child(1)::before {
                content: ""; /* Sembunyikan label untuk kolom pertama (produk) */
            }

            /* Posisi tombol hapus di pojok kanan atas card */
            .cart-table td:nth-child(5) {
                position: absolute;
                top: 10px;
                right: 10px;
                padding: 0;
            }
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>
    <!-- CART PAGE CONTAINER -->
    <div class="cart-page-container">
        <h2 class="page-header-title">Keranjang <span>Belanja Anda</span></h2>

        <div class="cart-layout">
            <!-- Daftar Produk dalam Keranjang -->
            <div class="cart-items-wrapper">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>
                                <label class="select-all-wrapper">
                                    <input type="checkbox" id="selectAllCheckbox" <?php echo !empty($cart) ? 'checked' : ''; ?>>
                                    Produk
                                </label>
                            </th>
                            <th>Harga</th>
                            <th>Kuantitas</th>
                            <th>Subtotal</th>
                            <th><i class="fas fa-trash-alt"></i></th>
                        </tr>
                    </thead>
                    <tbody id="cartTableBody">
                        <?php if (empty($cart)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-cart-message">
                                    <i class="fas fa-shopping-cart"></i>
                                    <p>Keranjang belanja Anda masih kosong.</p>
                                    <a href="index.php#produk" style="display:inline-block; margin-top:12px; color:var(--color-gold); font-weight:600;">Mulai Belanja Sekarang -></a>
                                </div>
                            </td>
                        </tr>
                        <?php else: foreach ($cart as $item): ?>
                        <tr>
                            <td>
                                <div class="cart-item-info">
                                    <input type="checkbox" class="item-select-checkbox" data-id="<?php echo (int)$item['id']; ?>" data-price="<?php echo (int)$item['price']; ?>" data-qty="<?php echo (int)$item['qty']; ?>" checked>
                                    <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="cart-item-img" onerror="this.src='https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80'">
                                    <div class="cart-item-detail">
                                        <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                        <span>Kemasan 1 Kg</span>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Harga: "><?php echo rp($item['price']); ?></td>
                            <td data-label="Kuantitas: ">
                                <div class="qty-control">
                                    <form method="POST" action="cart-action.php" style="display:inline;">
                                        <input type="hidden" name="item_id" value="<?php echo (int)$item['id']; ?>">
                                        <button type="submit" name="action" value="minus" class="qty-btn">-</button>
                                    </form>
                                    <input type="text" class="qty-input" value="<?php echo (int)$item['qty']; ?>" readonly>
                                    <form method="POST" action="cart-action.php" style="display:inline;">
                                        <input type="hidden" name="item_id" value="<?php echo (int)$item['id']; ?>">
                                        <button type="submit" name="action" value="plus" class="qty-btn">+</button>
                                    </form>
                                </div>
                            </td>
                            <td data-label="Subtotal: " class="item-subtotal"><?php echo rp($item['price'] * $item['qty']); ?></td>
                            <td>
                                <form method="POST" action="cart-action.php">
                                    <input type="hidden" name="item_id" value="<?php echo (int)$item['id']; ?>">
                                    <button type="submit" name="action" value="remove" class="remove-item-btn" title="Hapus Produk"><i class="fas fa-xmark"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Ringkasan Belanja / Checkout Card -->
            <div class="cart-summary-card">
                <h3 class="summary-title">Ringkasan Pesanan</h3>
                <div class="summary-row">
                    <span>Total Item</span>
                    <span id="summaryTotalItems"><?php echo $totalItemsCount; ?> Produk</span>
                </div>
                <div class="summary-row">
                    <span>Subtotal Harga</span>
                    <span id="summarySubtotal"><?php echo rp($subtotalAll); ?></span>
                </div>
                <div class="summary-row">
                    <span>Pph (2%)</span>
                    <span id="summaryPph"><?php echo rp($pphAmount); ?></span>
                </div>
                <div class="summary-row">
                    <span>Estimasi Ongkir (Bandung)</span>
                    <span id="summaryShipping"><?php echo rp($shippingFee); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total Pembayaran</span>
                    <span id="summaryGrandTotal"><?php echo rp($grandTotal); ?></span>
                </div>

                <?php if (!empty($cart)): ?>
                <button type="button" class="btn-checkout" id="btnOpenCheckoutForm">
                    <i class="fas fa-lock"></i> Lanjut Checkout
                </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($cart)): ?>
        <!-- Form Pengiriman & Pembayaran (tampil setelah klik Lanjut Checkout) -->
        <div class="cart-summary-card" id="checkoutFormCard" style="display:none; max-width:600px; margin:24px auto 0;">
            <h3 class="summary-title">Detail Pengiriman & Pembayaran</h3>

            <?php if (!empty($checkout_errors)): ?>
                <div style="background:#fbe9e9; color:#b00020; padding:12px; border-radius:8px; margin-bottom:15px;">
                    <?php foreach ($checkout_errors as $err): ?>
                        <p><?php echo htmlspecialchars($err); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="place-order.php" enctype="multipart/form-data" id="placeOrderForm">
                <div id="selectedItemsHiddenInputs"></div>
                <div style="margin-bottom:14px;">
                    <label style="display:block; margin-bottom:6px; font-weight:600; color:var(--color-coffee);">Nama Penerima</label>
                    <input type="text" name="full_name" required style="width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:8px; font-family:var(--font-body);" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>">
                </div>
                <div style="margin-bottom:14px;">
                    <label style="display:block; margin-bottom:6px; font-weight:600; color:var(--color-coffee);">Nomor HP / WhatsApp</label>
                    <input type="tel" name="phone" required style="width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:8px; font-family:var(--font-body);">
                </div>
                <div style="margin-bottom:14px;">
                    <label style="display:block; margin-bottom:6px; font-weight:600; color:var(--color-coffee);">Alamat Pengiriman Lengkap</label>
                    <textarea name="address" rows="3" required style="width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:8px; font-family:var(--font-body); resize:vertical;"></textarea>
                </div>
                <div style="margin-bottom:14px; background:var(--color-cream); padding:12px; border-radius:8px;">
                    <p style="margin-bottom:8px; font-weight:600; color:var(--color-coffee);">Transfer ke:</p>
                    <p>Bank BCA — 1234567890 a.n. Nusantara Beans</p>
                </div>
                <div style="margin-bottom:14px;">
                    <label style="display:block; margin-bottom:6px; font-weight:600; color:var(--color-coffee);">Unggah Bukti Transfer</label>
                    <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                    <small style="color:#777;">JPG, PNG, WEBP, atau PDF, maksimal 3MB.</small>
                </div>
                <button type="submit" class="btn-checkout"><i class="fas fa-paper-plane"></i> Kirim Pesanan</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- FOOTER (Sesuai index.html) -->
<?php include 'includes/footer.php'; ?>
    <!-- ================= JAVASCRIPT ================= -->
    <script>
        // Toggle Burger Menu & Social Dropdown
        const burgerBtn = document.getElementById('burgerBtn');
        const socialDropdown = document.getElementById('socialDropdown');
        
        burgerBtn.addEventListener('click', (e) => {
            socialDropdown.classList.toggle('active');
            e.stopPropagation();
        });

        // Search Dropdown Toggle Logic & Icon Switcher
        const searchBtn = document.getElementById('searchBtn');
        const searchDropdownBar = document.getElementById('searchDropdownBar');
        const searchBarInput = document.getElementById('searchBarInput');

        searchBtn.addEventListener('click', (e) => {
            searchDropdownBar.classList.toggle('active');
            if (searchDropdownBar.classList.contains('active')) {
                searchBtn.classList.remove('fa-search');
                searchBtn.classList.add('fa-xmark');
                searchBarInput.focus();
            } else {
                searchBtn.classList.remove('fa-xmark');
                searchBtn.classList.add('fa-search');
            }
            e.stopPropagation();
        });

        // Close dropdowns when clicking outside
        window.addEventListener('click', (e) => {
            if (!burgerBtn.contains(e.target) && !socialDropdown.contains(e.target)) {
                socialDropdown.classList.remove('active');
            }
            if (!searchDropdownBar.contains(e.target) && e.target !== searchBtn) {
                searchDropdownBar.classList.remove('active');
                searchBtn.classList.remove('fa-xmark');
                searchBtn.classList.add('fa-search');
            }
        });

        // Tampilkan form pengiriman & pembayaran saat klik "Lanjut Checkout"
        const btnOpenCheckoutForm = document.getElementById('btnOpenCheckoutForm');
        const checkoutFormCard = document.getElementById('checkoutFormCard');

        // ============ PILIH ITEM KERANJANG UNTUK CHECKOUT ============
        const itemCheckboxes = Array.from(document.querySelectorAll('.item-select-checkbox'));
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const placeOrderForm = document.getElementById('placeOrderForm');
        const selectedItemsHiddenInputs = document.getElementById('selectedItemsHiddenInputs');

        const SHIPPING_FEE = 15000;
        const PPH_RATE = 0.02;

        function formatRupiah(num) {
            return 'Rp ' + Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function getSelectedCheckboxes() {
            return itemCheckboxes.filter(cb => cb.checked);
        }

        function recalcSummary() {
            const selected = getSelectedCheckboxes();

            let subtotal = 0;
            let totalQty = 0;
            selected.forEach(cb => {
                const price = parseFloat(cb.dataset.price) || 0;
                const qty = parseInt(cb.dataset.qty, 10) || 0;
                subtotal += price * qty;
                totalQty += qty;
            });

            const shipping = selected.length > 0 ? SHIPPING_FEE : 0;
            const pph = Math.round(subtotal * PPH_RATE);
            const grandTotal = selected.length > 0 ? subtotal + shipping + pph : 0;

            const elTotalItems = document.getElementById('summaryTotalItems');
            const elSubtotal = document.getElementById('summarySubtotal');
            const elPph = document.getElementById('summaryPph');
            const elShipping = document.getElementById('summaryShipping');
            const elGrandTotal = document.getElementById('summaryGrandTotal');

            if (elTotalItems) elTotalItems.textContent = totalQty + ' Produk';
            if (elSubtotal) elSubtotal.textContent = formatRupiah(subtotal);
            if (elPph) elPph.textContent = formatRupiah(pph);
            if (elShipping) elShipping.textContent = formatRupiah(shipping);
            if (elGrandTotal) elGrandTotal.textContent = formatRupiah(grandTotal);

            if (btnOpenCheckoutForm) {
                btnOpenCheckoutForm.disabled = selected.length === 0;
                btnOpenCheckoutForm.style.opacity = selected.length === 0 ? '0.5' : '1';
                btnOpenCheckoutForm.style.cursor = selected.length === 0 ? 'not-allowed' : 'pointer';
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.checked = itemCheckboxes.length > 0 && selected.length === itemCheckboxes.length;
                selectAllCheckbox.indeterminate = selected.length > 0 && selected.length < itemCheckboxes.length;
            }

            // Sinkronkan hidden input item terpilih ke form pemesanan
            if (selectedItemsHiddenInputs) {
                selectedItemsHiddenInputs.innerHTML = '';
                selected.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_items[]';
                    input.value = cb.dataset.id;
                    selectedItemsHiddenInputs.appendChild(input);
                });
            }
        }

        itemCheckboxes.forEach(cb => cb.addEventListener('change', recalcSummary));

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                itemCheckboxes.forEach(cb => { cb.checked = selectAllCheckbox.checked; });
                recalcSummary();
            });
        }

        // Hitung ringkasan awal saat halaman dimuat
        recalcSummary();

        if (btnOpenCheckoutForm && checkoutFormCard) {
            btnOpenCheckoutForm.addEventListener('click', function () {
                if (getSelectedCheckboxes().length === 0) {
                    alert('Pilih minimal satu produk untuk melanjutkan checkout.');
                    return;
                }
                recalcSummary();
                checkoutFormCard.style.display = 'block';
                checkoutFormCard.scrollIntoView({ behavior: 'smooth' });
            });
        }

        if (placeOrderForm) {
            placeOrderForm.addEventListener('submit', function (e) {
                if (getSelectedCheckboxes().length === 0) {
                    e.preventDefault();
                    alert('Pilih minimal satu produk untuk melanjutkan checkout.');
                    return;
                }
                recalcSummary();
            });
        }
        <?php if (!empty($checkout_errors)): ?>
        // Buka otomatis form jika ada error validasi sebelumnya
        if (checkoutFormCard) { checkoutFormCard.style.display = 'block'; }
        <?php endif; ?>
    </script>
</body>
</html>
