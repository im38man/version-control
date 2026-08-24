<?php require_once __DIR__ . "/includes/session.php"; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian - Zenith Tour & Travel</title>
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
            line-height: 1.6;
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
        
        /* Navbar Konsisten dengan Index */
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

        /* Konten Utama Hasil Pencarian */
        .main-content {
            flex: 1;
            padding: 80px 8%;
        }
        .search-header {
            margin-bottom: 40px;
            border-bottom: 1px solid #e5e0d8;
            padding-bottom: 20px;
        }
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 38px;
            color: #1a2f27;
            margin-bottom: 15px;
        }
        .search-header p {
            color: #666;
            font-size: 15px;
        }
        .search-header span {
            color: #c5a880;
            font-weight: 600;
        }

        /* Ukuran Grid Konsisten (3 kolom untuk desktop, menyusuaikan responsif) */
        .results-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }
        @media(max-width: 1024px) {
            .results-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media(max-width: 768px) {
            .results-grid {
                grid-template-columns: 1fr;
            }
        }

        .result-card {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            border: 1px solid #e5e0d8;
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.4s;
            cursor: pointer;
        }
        .result-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.08);
            border-color: #c5a880;
        }
        .result-img {
            height: 240px;
            background-size: cover;
            background-position: center;
        }
        .result-content {
            padding: 30px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .result-content h3 {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            color: #1a2f27;
            margin-bottom: 10px;
        }
        .result-content p {
            font-size: 14px;
            color: #666;
            margin-bottom: 25px;
            font-weight: 300;
            flex-grow: 1;
        }
        .result-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #f3eee7;
            padding-top: 20px;
            margin-top: auto;
        }
        .price {
            font-weight: 600;
            color: #c5a880;
            font-size: 18px;
        }
        .btn-secondary {
            border: 1px solid #c5a880;
            color: #c5a880;
            padding: 8px 18px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-block;
        }
        .result-card:hover .btn-secondary {
            background-color: #c5a880;
            color: #fff;
            box-shadow: 0 4px 10px rgba(197, 168, 128, 0.15);
        }
        .no-results {
            text-align: center;
            padding: 60px 0;
            color: #777;
            font-size: 16px;
            grid-column: 1 / -1;
        }
        .btn-primary {
            background-color: #c5a880;
            color: #fff;
            padding: 12px 28px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 14px;
            display: inline-block;
            margin-top: 20px;
            transition: background 0.3s;
        }
        .btn-primary:hover {
            background-color: #b0936d;
        }

        /* Footer Konsisten dengan Index */
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
    </style>
</head>
<body>

    <!-- NAVBAR -->
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

    <!-- KONTEN HASIL PENCARIAN -->
    <div class="main-content">
        <div class="search-header">
            <h1 class="section-title" id="searchTitle">Hasil Pencarian</h1>
            <p id="searchCountInfo">Memuat hasil pencarian...</p>
        </div>

        <div class="results-grid" id="resultsContainer">
            <!-- Data Dummy Hasil Pencarian Dinamis -->
        </div>
    </div>

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

    <!-- FOOTER -->
    <?php include __DIR__ . "/includes/footer.php"; ?>

    <script>
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

        /* Data Dummy dengan hidden tags dan link */
        const dummyData = [
            {
                title: "Kawah Putih Bandung",
                category: "Bandung",
                desc: "Nikmati pesona magis kawah vulkanik berkabut eksotis, hamparan kebun teh Ciwidey, dan glamping mewah di alam terbuka.",
                price: "Rp 2.200.000",
                image: "img/kawah-putih-02.webp",
                link: "index.php#destinasi",
                tags: ["bandung", "ciwidey", "kawah", "alam", "glamping", "gunung", "murah", "hemat" , "paket murah", "paket hemat"]
            },
            {
                title: "Klasik Yogyakarta",
                category: "Yogyakarta",
                desc: "Telusuri keagungan Candi Borobudur, kemegahan Keraton, kehangatan jalan Malioboro, serta tradisi membatik yang adiluhung.",
                price: "Rp 3.100.000",
                image: "img/yogyakarta-02.webp",
                link: "index.php#destinasi",
                tags: ["jogja", "yogyakarta", "borobudur", "malioboro", "budaya", "sejarah"]
            },
            {
                title: "Eksotika Bali",
                category: "Bali",
                desc: "Rasakan keindahan murni pantai berpasir putih, ritual tradisi budaya Bali yang sakral, dan ketenangan Ubud.",
                price: "Rp 4.500.000",
                image: "img/bali-02.webp",
                link: "index.php#destinasi",
                tags: ["bali", "ubud", "pantai", "uluwatu", "pura", "nusa penida"]
            },
            {
                title: "Pesona Malang & Bromo",
                category: "Malang",
                desc: "Saksikan matahari terbit legendaris di Gunung Bromo, nikmati petik apel Kota Batu, dan kesejukan udara pegunungan Jawa Timur.",
                price: "Rp 3.800.000",
                image: "img/malang-02.webp",
                link: "index.php#destinasi",
                tags: ["malang", "bromo", "batu", "sunrise", "gunung", "apel"]
            },
            {
                title: "Metropolitan Jakarta",
                category: "Jakarta",
                desc: "Rasakan kemewahan staycation hotel bintang 5, wisata sejarah Kota Tua, pusat belanja premium, dan gemerlap kota modern.",
                price: "Rp 2.900.000",
                image: "img/jakarta-02.webp",
                link: "index.php#destinasi",
                tags: ["jakarta", "ibukota", "kota tua", "hotel", "mall", "staycation"]
            }
        ];

        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('q') ? urlParams.get('q').trim().toLowerCase() : '';

        if (urlParams.get('q')) {
            document.getElementById('searchTitle').innerText = `Hasil Pencarian: "${urlParams.get('q')}"`;
        }

        const filteredResults = dummyData.filter(item => {
            const matchTitle = item.title.toLowerCase().includes(searchQuery);
            const matchDesc = item.desc.toLowerCase().includes(searchQuery);
            const matchCat = item.category.toLowerCase().includes(searchQuery);
            const matchTags = item.tags.some(tag => tag.toLowerCase().includes(searchQuery));
            
            return matchTitle || matchDesc || matchCat || matchTags;
        });

        const countInfoEl = document.getElementById('searchCountInfo');
        countInfoEl.innerHTML = `Ditemukan <span>${filteredResults.length}</span> hasil yang sesuai dengan pencarian Anda.`;

        const resultsContainer = document.getElementById('resultsContainer');
        resultsContainer.innerHTML = '';

        if (filteredResults.length > 0) {
            filteredResults.forEach(item => {
                const card = document.createElement('div');
                card.className = 'result-card';
                card.onclick = function() {
                    window.location.href = item.link;
                };
                
                card.innerHTML = `
                    <div class="result-img" style="background-image: url('${item.image}');"></div>
                    <div class="result-content">
                        <h3>${item.title}</h3>
                        <p>${item.desc}</p>
                        <div class="result-footer">
                            <div class="price">${item.price}</div>
                            <span class="btn-secondary">Lihat Detail</span>
                        </div>
                    </div>
                `;
                resultsContainer.appendChild(card);
            });
        } else {
            resultsContainer.innerHTML = `
                <div class="no-results">
                    <i class="fa-solid fa-face-frown" style="font-size: 48px; color: #c5a880; margin-bottom: 15px;"></i>
                    <p>Maaf, destinasi atau paket wisata yang Anda cari tidak ditemukan.</p>
                    <a href="index.php" class="btn-primary">Kembali ke Beranda</a>
                </div>
            `;
        }
    </script>
</body>
</html>