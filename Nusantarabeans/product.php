<?php
require_once __DIR__ . '/includes/auth.php';

// Situs ini tidak punya tabel "products" di database — katalog produk memang
// statis di index.php/all-product.php (keranjang pun berbasis nama produk,
// bukan ID, lihat add-to-cart.php). Jadi halaman detail ini menerima info
// produk lewat parameter URL yang dikirim dari tombol "Detail" di kartu produk.
$title = trim($_GET['title'] ?? '');
$price = (float) ($_GET['price'] ?? 0);
$desc  = trim($_GET['desc'] ?? '');
$img   = trim($_GET['img'] ?? '');
$badge = trim($_GET['badge'] ?? '');
$label = trim($_GET['label'] ?? '');

if ($title === '' || $price <= 0) {
    header('Location: index.php');
    exit;
}

$page_title = $title;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?> - Nusantara Beans</title>
    
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

        /* ================= NAVBAR (Sesuai pesan.php) ================= */
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

        /* ================= SEARCH DROPDOWN BAR ================= */
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

        /* ================= PRODUCT SECTION CONTAINER (DITENGAHKAN) ================= */
        .product-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 90px 20px 40px 20px;
            width: 100%;
        }

        .product-card-container {
            width: 100%;
            max-width: 900px;
            background: var(--color-white);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(44, 30, 22, 0.08);
            border: 1px solid rgba(197, 160, 89, 0.25);
            padding: 35px;
            display: flex;
            flex-direction: column;
        }

        .back-link {
            font-size: 0.85rem;
            color: var(--color-gold);
            margin-bottom: 20px;
            display: inline-block;
            transition: color 0.3s;
        }
        .back-link:hover {
            color: var(--color-coffee);
        }

        .product-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
        }

        .product-thumb {
            aspect-ratio: 1/1;
            border-radius: 8px;
            overflow: hidden;
            background: var(--color-cream);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(197, 160, 89, 0.2);
            font-family: var(--font-heading);
            color: var(--color-coffee);
            font-size: 1.2rem;
        }

        .product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-cat {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--color-gold);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .product-card-container h1 {
            font-family: var(--font-heading);
            font-size: clamp(20px, 2.5vw, 28px);
            color: var(--color-coffee);
            margin-bottom: 12px;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-gold);
            margin-bottom: 15px;
        }

        .product-desc {
            color: #555;
            font-size: 13.5px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .product-stock {
            font-size: 13px;
            color: #666;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .qty-input {
            width: 65px;
            padding: 9px 12px;
            border: 1px solid rgba(197, 160, 89, 0.4);
            border-radius: 25px;
            outline: none;
            text-align: center;
            font-family: var(--font-body);
            background: var(--color-cream);
            font-size: 14px;
        }

        .btn-add-cart {
            background-color: var(--color-gold);
            color: var(--color-white);
            border: none;
            padding: 10px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-family: var(--font-body);
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .btn-add-cart:hover {
            background-color: var(--color-coffee);
            color: var(--color-gold);
            transform: scale(1.02);
        }

        /* ================= FOOTER ================= */
        .site-footer {
            background: #1A120D;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            padding: 40px 5% 20px;
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

        /* ================= RESPONSIVE ================= */
        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .product-card-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

    <div class="product-wrapper">
        <div class="product-card-container">
            <a href="index.php" class="back-link">&larr; Kembali ke belanja</a>

            <div class="product-grid">
                <div class="product-thumb">
                    <?php if ($img !== ''): ?>
                        <img src="<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php else: ?>
                        Nusantara Beans
                    <?php endif; ?>
                </div>
                <div>
                    <?php if ($label !== ''): ?>
                        <div class="product-cat"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <h1><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
                    <div class="product-price">Rp <?php echo number_format($price, 0, ',', '.'); ?> /kg</div>
                    <?php if ($desc !== ''): ?>
                        <p class="product-desc"><?php echo nl2br(htmlspecialchars($desc, ENT_QUOTES, 'UTF-8')); ?></p>
                    <?php endif; ?>

                    <form id="detailAddCartForm" style="display:flex; gap:12px; align-items:center;">
                        <input type="number" name="qty" id="detailQty" value="1" min="1" class="qty-input">
                        <button type="submit" class="btn-add-cart" id="detailAddCartBtn"><i class="fas fa-cart-plus"></i> Tambah ke Keranjang</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>

    <script src="assets/js/cart.js?v=<?php echo @filemtime(__DIR__ . '/assets/js/cart.js') ?: time(); ?>"></script>
    <script>
        // Tombol "Tambah ke Keranjang" di halaman detail produk ini terpisah dari
        // cart.js (yang bekerja di atas markup .product-card kartu produk),
        // karena halaman detail punya layout sendiri. Endpoint & field POST-nya
        // sama persis dengan add-to-cart.php supaya konsisten dengan kartu produk.
        const detailAddCartForm = document.getElementById('detailAddCartForm');
        const detailAddCartBtn = document.getElementById('detailAddCartBtn');
        const detailProductName = <?php echo json_encode($title); ?>;
        const detailProductImage = <?php echo json_encode($img); ?>;
        const detailProductPrice = <?php echo json_encode($price); ?>;

        detailAddCartForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const qty = Math.max(1, parseInt(document.getElementById('detailQty').value, 10) || 1);

            const originalHtml = detailAddCartBtn.innerHTML;
            detailAddCartBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menambahkan...';
            detailAddCartBtn.disabled = true;

            const params = new URLSearchParams();
            params.set('product_name', detailProductName);
            params.set('product_image', detailProductImage);
            params.set('price', detailProductPrice);
            params.set('qty', qty);

            fetch('add-to-cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            })
                .then((r) => r.json())
                .then((data) => {
                    detailAddCartBtn.disabled = false;
                    if (data.status === 'need_login') {
                        window.location.href = 'login.php';
                    } else if (data.status === 'ok') {
                        detailAddCartBtn.innerHTML = '<i class="fas fa-check"></i> Ditambahkan';
                        setTimeout(() => { detailAddCartBtn.innerHTML = originalHtml; }, 1200);
                        const badge = document.getElementById('navCartBadge');
                        if (badge) {
                            const next = (parseInt(badge.textContent, 10) || 0) + qty;
                            badge.textContent = next > 99 ? '99+' : next;
                            badge.style.display = '';
                        }
                    } else {
                        detailAddCartBtn.innerHTML = originalHtml;
                        alert(data.message || 'Gagal menambahkan produk ke keranjang.');
                    }
                })
                .catch(() => {
                    detailAddCartBtn.innerHTML = originalHtml;
                    detailAddCartBtn.disabled = false;
                    alert('Terjadi kesalahan jaringan. Coba lagi.');
                });
        });
    </script>

    <script>
        // Toggle Burger Menu & Social Dropdown (sama seperti halaman lain)
        const burgerBtn = document.getElementById('burgerBtn');
        const socialDropdown = document.getElementById('socialDropdown');

        if (burgerBtn && socialDropdown) {
            burgerBtn.addEventListener('click', (e) => {
                socialDropdown.classList.toggle('active');
                e.stopPropagation();
            });
        }

        const searchBtn = document.getElementById('searchBtn');
        const searchDropdownBar = document.getElementById('searchDropdownBar');
        const searchBarInput = document.getElementById('searchBarInput');

        if (searchBtn && searchDropdownBar) {
            searchBtn.addEventListener('click', (e) => {
                searchDropdownBar.classList.toggle('active');
                if (searchDropdownBar.classList.contains('active')) {
                    searchBtn.classList.remove('fa-search');
                    searchBtn.classList.add('fa-xmark');
                    if (searchBarInput) searchBarInput.focus();
                } else {
                    searchBtn.classList.remove('fa-xmark');
                    searchBtn.classList.add('fa-search');
                }
                e.stopPropagation();
            });
        }

        window.addEventListener('click', (e) => {
            if (burgerBtn && socialDropdown && !burgerBtn.contains(e.target) && !socialDropdown.contains(e.target)) {
                socialDropdown.classList.remove('active');
            }
            if (searchBtn && searchDropdownBar && !searchDropdownBar.contains(e.target) && e.target !== searchBtn) {
                searchDropdownBar.classList.remove('active');
                searchBtn.classList.remove('fa-xmark');
                searchBtn.classList.add('fa-search');
            }
        });
    </script>
</body>
</html>