<?php require_once __DIR__ . "/includes/session.php"; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Eksplorasi Malang & Bromo | Zenith Tour & Travel</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%231a2f27'/><text x='50' y='70' font-size='60' font-family='Playfair Display, serif' font-weight='bold' fill='%23c5a880' text-anchor='middle'>Z</text></svg>">
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            scroll-behavior: smooth;
        }
        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
            line-height: 1.8;
            background-color: #fcfbf7;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        a {
            text-decoration: none;
            color: inherit;
        }
        
        /* Navbar Konsisten dengan Index & Search */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 8%;
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 700;
            color: #1a2f27;
            letter-spacing: 1px;
        }
        .nav-right-container {
            display: flex;
            align-items: center;
            gap: 40px;
        }
        .nav-links {
            display: flex;
            list-style: none;
            gap: 30px;
            align-items: center;
        }
        .nav-links a {
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
            color: #555;
        }
        .nav-links a:hover, .nav-links a.active {
            color: #c5a880;
        }
        .nav-socials {
            display: flex;
            gap: 15px;
            align-items: center;
            border-left: 1px solid #e5e0d8;
            padding-left: 20px;
        }
        .nav-socials a, .nav-search-btn {
            color: #1a2f27;
            font-size: 18px;
            transition: color 0.3s, transform 0.3s;
            background: none;
            border: none;
            cursor: pointer;
        }
        .nav-socials a:hover, .nav-search-btn:hover {
            color: #c5a880;
            transform: scale(1.15);
        }
        .btn-nav-book {
            background-color: #1a2f27;
            color: #fff !important;
            padding: 8px 20px;
            border-radius: 4px;
            font-size: 13px;
            transition: background 0.3s !important;
        }
        .btn-nav-book:hover {
            background-color: #c5a880;
        }
        .menu-toggle {
            display: none;
            font-size: 22px;
            color: #1a2f27;
            cursor: pointer;
        }
        @media(max-width: 991px) {
            .nav-right-container {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: #fff;
                padding: 30px;
                box-shadow: 0 10px 15px rgba(0,0,0,0.05);
                gap: 20px;
                border-top: 1px solid #f3eee7;
            }
            .nav-right-container.active {
                display: flex;
            }
            .nav-links {
                flex-direction: column;
                gap: 20px;
                width: 100%;
                text-align: center;
            }
            .nav-socials {
                border-left: none;
                padding-left: 0;
                border-top: 1px solid #f3eee7;
                padding-top: 20px;
                width: 100%;
                justify-content: center;
            }
            .menu-toggle {
                display: block;
            }
        }

        /* Blog Header */
        .blog-header {
            position: relative;
            height: 60vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: #fff;
            padding: 0 20px;
            background: url('https://images.unsplash.com/photo-1571587847935-719114dce1f4?q=80&w=2070') center/cover no-repeat;
        }
        .blog-header-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(26, 47, 39, 0.6), rgba(26, 47, 39, 0.4));
            z-index: 1;
        }
        .blog-header-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
        }
        .blog-category {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #c5a880;
            font-weight: 600;
            margin-bottom: 15px;
            display: inline-block;
            background: rgba(255,255,255,0.1);
            padding: 5px 15px;
            border-radius: 20px;
        }
        .blog-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        .blog-meta {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        .blog-meta i {
            color: #c5a880;
            margin-right: 5px;
        }

        /* Blog Content */
        .blog-container {
            max-width: 900px;
            margin: -50px auto 100px auto;
            background: #fff;
            padding: 50px 8%;
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.05);
            position: relative;
            z-index: 3;
        }
        .blog-content p {
            margin-bottom: 25px;
            font-size: 16px;
            color: #555;
        }
        .blog-content h2 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: #1a2f27;
            margin: 40px 0 20px 0;
        }
        .blog-content img {
            width: 100%;
            border-radius: 8px;
            margin: 30px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .blog-quote {
            background-color: #fcfbf7;
            border-left: 4px solid #c5a880;
            padding: 25px;
            margin: 30px 0;
            font-style: italic;
            font-size: 18px;
            color: #1a2f27;
            font-family: 'Playfair Display', serif;
        }
        
        /* Author Info */
        .author-box {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid #e5e0d8;
        }
        .author-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #c5a880;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-family: 'Playfair Display', serif;
            font-weight: bold;
        }
        .author-info h4 {
            color: #1a2f27;
            font-size: 18px;
            margin-bottom: 5px;
        }
        .author-info p {
            font-size: 14px;
            color: #777;
            margin: 0;
        }

        /* Footer */
        footer {
            background-color: #111e19;
            color: rgba(255,255,255,0.5);
            padding: 60px 8% 30px 8%;
            font-size: 13px;
            margin-top: auto;
        }
        .footer-grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 40px;
            margin-bottom: 30px;
        }
        .footer-info-block h3 {
            color: #fff;
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .footer-info-block p, .footer-info-block a {
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.8;
            margin-bottom: 8px;
            display: block;
        }
        .footer-info-block a:hover {
            color: #c5a880;
        }
        .footer-info-block i {
            margin-right: 8px;
            color: #c5a880;
            width: 16px;
        }
        .footer-logo {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 15px;
        }
        .footer-socials {
            display: flex;
            gap: 20px;
            margin-top: 15px;
        }
        .footer-socials a {
            color: rgba(255,255,255,0.6);
            font-size: 20px;
            transition: color 0.3s;
            display: inline-block;
        }
        .footer-socials a:hover {
            color: #c5a880;
        }
        .copyright {
            text-align: center;
            font-size: 12px;
        }

        /* Modal Search */
        .search-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(26, 47, 39, 0.92);
            backdrop-filter: blur(8px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 3000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .search-modal.active {
            display: flex;
            opacity: 1;
        }
        .search-modal-content {
            width: 90%;
            max-width: 600px;
            text-align: center;
            position: relative;
            transform: translateY(-20px);
            transition: transform 0.3s ease;
        }
        .search-modal.active .search-modal-content {
            transform: translateY(0);
        }
        .search-input-wrapper {
            position: relative;
            width: 100%;
        }
        .search-modal-input {
            width: 100%;
            padding: 20px 60px 20px 25px;
            font-size: 20px;
            font-family: 'Poppins', sans-serif;
            background: #ffffff;
            border: none;
            border-radius: 50px;
            outline: none;
            color: #1a2f27;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .search-modal-input::placeholder {
            color: #aaa;
        }
        .search-modal-submit {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: #1a2f27;
            color: #fff;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: background 0.3s;
        }
        .search-modal-submit:hover {
            background: #c5a880;
        }
        .search-modal-close {
            position: absolute;
            top: -50px;
            right: 0;
            color: #fff;
            font-size: 28px;
            cursor: pointer;
            transition: color 0.3s;
        }
        .search-modal-close:hover {
            color: #c5a880;
        }
        .search-hint {
            color: rgba(255,255,255,0.7);
            font-size: 13px;
            margin-top: 15px;
            letter-spacing: 0.5px;
        }
        
        @media(max-width: 768px) {
            .blog-header h1 {
                font-size: 32px;
            }
            .blog-container {
                margin: 0;
                border-radius: 0;
                padding: 40px 5%;
            }
            nav {
                padding: 15px 5%;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav>
        <div style="display: flex; flex-direction: column;">
            <div class="logo" style="line-height: 1.2;">Zenit Tour & Travel</div>
            <a href="https://www.zenith-adventour.com" target="_blank" style="font-size: 11px; color: #c5a880; letter-spacing: 0.5px; font-weight: 500; margin-top: -2px;">www.zenith-adventour.com</a>
        </div>
        <div class="menu-toggle" onclick="toggleMenu()"><i class="fa-solid fa-bars"></i></div>
        <div class="nav-right-container" id="navContainer">
            <ul class="nav-links">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="index.php#destinasi">Destinasi</a></li>
                <li><a href="gallery.php">Galeri</a></li>
                <li><a href="#" class="btn-nav-book" onclick="openSearchModal()"><i class="fa-solid fa-magnifying-glass"></i> Cari Sekarang</a></li>
            </ul>
            <div class="nav-socials">
                <a href="https://instagram.com" target="_blank" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="https://tiktok.com" target="_blank" title="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                <a href="https://facebook.com" target="_blank" title="Facebook"><i class="fa-brands fa-facebook"></i></a>
                <a href="https://wa.me/62895333841200?text=Hallo%20admin%20saya%20mau%20bertanya%20tentang%20detail%20perjalanan" target="_blank" title="WhatsApp Admin"><i class="fa-brands fa-whatsapp"></i></a>
            </div>
            <?php include __DIR__ . "/includes/auth_nav.php"; ?>
        </div>
    </nav>

    <!-- Blog Header -->
    <header class="blog-header">
        <div class="blog-header-overlay"></div>
        <div class="blog-header-content">
            <span class="blog-category">Inspirasi Perjalanan</span>
            <h1>Eksplorasi Malang & Bromo: Perpaduan Kesejukan Alam dan Petualangan Seru</h1>
            <div class="blog-meta">
                <span><i class="fa-regular fa-calendar"></i> 22 Agustus 2026</span>
                <span><i class="fa-regular fa-user"></i> Zenith Editorial</span>
                <span><i class="fa-regular fa-clock"></i> 5 Min Read</span>
            </div>
        </div>
    </header>

    <!-- Blog Content -->
    <main class="blog-container">
        <div class="blog-content">
            <p>Terletak di dataran tinggi, Malang dan sekitarnya (termasuk Kota Batu) merupakan salah satu oase dengan udara paling sejuk di Jawa Timur. Kota ini menjadi destinasi idaman bagi mereka yang mendambakan pelarian dari panasnya perkotaan serta mencari keseimbangan antara rekreasi keluarga dan petualangan alam yang menantang.</p>
            <p>Dari keindahan pegunungan hingga wisata buatan berkelas internasional, Malang memberikan kebebasan eksplorasi bagi setiap pelancong.</p>

            <h2>Mengejar Matahari Terbit di Gunung Bromo</h2>
            <img src="https://images.unsplash.com/photo-1602073998595-5d55b0a34bba?q=80&w=2070&auto=format&fit=crop" alt="Pemandangan Gunung Bromo">
            <p>Highlight utama perjalanan ke wilayah ini tentu saja adalah <strong>Taman Nasional Bromo Tengger Semeru</strong>. Memulai perjalanan di dini hari menggunakan Jeep eksklusif, Anda akan dibawa menuju titik pandang Penanjakan. Saat semburat oranye pertama muncul di ufuk timur, menampilkan kemegahan lanskap Bromo dan Semeru, segala rasa lelah akan terbayar lunas.</p>

            <div class="blog-quote">
                "Berdiri di tepi lautan pasir Bromo memberikan kita perspektif tentang betapa kecilnya kita, namun sekaligus betapa besarnya keagungan alam."
            </div>

            <h2>Rekreasi Premium di Kota Batu</h2>
            <p>Turun dari pegunungan, Kota Batu menawarkan beragam aktivitas yang lebih santai. Anda dapat mengunjungi Museum Angkut untuk melihat koleksi mobil klasik dari seluruh dunia, atau berjalan-jalan di kebun apel untuk memetik buah segar langsung dari pohonnya.</p>
            <p>Paket perjalanan <em>Eksplorasi Malang & Bromo</em> dari <strong>Zenith Tour & Travel</strong> memastikan bahwa petualangan Anda tetap diselimuti oleh kenyamanan. Menginaplah di resort mewah di perbukitan Batu, di mana kami akan menyajikan sarapan hangat dengan pemandangan lembah yang hijau, dan menyiapkan segala kebutuhan tur Anda tanpa hambatan.</p>
        </div>

        <div class="author-box">
            <div class="author-avatar">Z</div>
            <div class="author-info">
                <h4>Zenith Editorial Team</h4>
                <p>Membawa Anda menjelajahi keindahan Nusantara dengan kenyamanan, kemewahan, dan pengalaman autentik yang tak terlupakan.</p>
            </div>
        </div>
    </main>

    <!-- MODAL SEARCH -->
    <div class="search-modal" id="searchModal">
        <div class="search-modal-content">
            <span class="search-modal-close" onclick="closeSearchModal()">&times;</span>
            <div class="search-input-wrapper">
                <input type="text" id="searchInput" class="search-modal-input" placeholder="Cari destinasi, hotel, atau aktivitas..." onkeypress="handleSearchEnter(event)">
                <button class="search-modal-submit" onclick="executeSearch()"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
            <p class="search-hint">Tekan <b>Enter</b> atau klik ikon kaca pembesar untuk mulai mencari.</p>
        </div>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . "/includes/footer.php"; ?>

    <script>
        // Responsive Menu Toggle
        function toggleMenu() {
            const navContainer = document.getElementById('navContainer');
            navContainer.classList.toggle('active');
        }

        function openSearchModal() {
            const modal = document.getElementById('searchModal');
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('active');
                document.getElementById('searchInput').focus();
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        function closeSearchModal() {
            const modal = document.getElementById('searchModal');
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
            document.body.style.overflow = 'auto';
        }

        function executeSearch() {
            const keyword = document.getElementById('searchInput').value.trim();
            if (keyword !== "") {
                window.location.href = `search.php?q=${encodeURIComponent(keyword)}`;
            }
        }

        function handleSearchEnter(event) {
            if (event.key === 'Enter') {
                executeSearch();
            }
        }

        window.addEventListener('click', function(event) {
            const modal = document.getElementById('searchModal');
            if (event.target === modal) {
                closeSearchModal();
            }
        });

        window.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeSearchModal();
            }
        });
    </script>
</body>
</html>