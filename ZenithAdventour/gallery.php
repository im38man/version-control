<?php require_once __DIR__ . "/includes/session.php"; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri - Zenith Tour & Travel</title>
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
        }
        a {
            text-decoration: none;
            color: inherit;
        }
        
        /* NAVIGATION (Persis Index) */
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

        /* HEADER / HERO MINI */
        .gallery-header {
            background: linear-gradient(rgba(26, 47, 39, 0.75), rgba(26, 47, 39, 0.85)), url('img/bali-01.webp') no-repeat center center/cover;
            padding: 80px 20px;
            text-align: center;
            color: #fff;
        }
        .gallery-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        .gallery-header p {
            font-size: 15px;
            color: rgba(255,255,255,0.8);
            font-weight: 300;
            max-width: 600px;
            margin: 0 auto;
        }

        /* GALLERY SECTION */
        .gallery-section {
            padding: 60px 8% 100px 8%;
        }
        
        /* Filter Kategori */
        .gallery-filter {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 40px;
        }
        .filter-btn {
            background-color: #fff;
            border: 1px solid #e5e0d8;
            color: #555;
            padding: 10px 24px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        .filter-btn:hover, .filter-btn.active {
            background-color: #1a2f27;
            color: #fff;
            border-color: #1a2f27;
            box-shadow: 0 4px 12px rgba(26, 47, 39, 0.1);
        }

        /* Grid Gambar */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        .gallery-item {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            border: 1px solid #f3eee7;
            cursor: pointer;
            transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.4s;
        }
        .gallery-item:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 25px rgba(26, 47, 39, 0.08);
        }
        .gallery-img-wrapper {
            position: relative;
            height: 220px;
            overflow: hidden;
            background-color: #1a2f27;
        }
        .gallery-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .gallery-item:hover .gallery-img-wrapper img {
            transform: scale(1.08);
        }
        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(26, 47, 39, 0.4);
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .gallery-overlay i {
            color: #fff;
            font-size: 24px;
            background: rgba(197, 168, 128, 0.9);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }
        .gallery-info {
            padding: 20px;
        }
        .gallery-info h3 {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            color: #1a2f27;
            margin-bottom: 5px;
        }
        .gallery-tag {
            font-size: 12px;
            color: #c5a880;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* SLIDER LIGHTBOX MODAL */
        .lightbox-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(21, 38, 31, 0.98);
            backdrop-filter: blur(5px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            padding: 20px;
        }
        .lightbox-modal.active {
            display: flex;
        }
        .lightbox-content {
            max-width: 850px;
            width: 100%;
            position: relative;
            text-align: center;
            animation: zoomIn 0.3s ease;
        }
        @keyframes zoomIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        .lightbox-slider-frame {
            position: relative;
            width: 100%;
            height: 65vh;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            background-color: #111e19;
        }
        .lightbox-slider-track {
            display: flex;
            width: 100%;
            height: 100%;
            transition: transform 0.4s ease-in-out;
        }
        .lightbox-slide {
            min-width: 100%;
            height: 100%;
        }
        .lightbox-slide img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .lightbox-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #fff;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
            z-index: 10;
        }
        .lightbox-arrow:hover {
            background: #c5a880;
            border-color: #c5a880;
            transform: translateY(-50%) scale(1.05);
        }
        .arrow-left { left: 15px; }
        .arrow-right { right: 15px; }

        .lightbox-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 15px;
        }
        .lightbox-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            cursor: pointer;
            transition: all 0.3s;
        }
        .lightbox-dot.active {
            background: #c5a880;
            width: 20px;
            border-radius: 4px;
        }

        .lightbox-caption {
            color: #fff;
            margin-top: 15px;
            font-family: 'Playfair Display', serif;
            font-size: 22px;
        }
        .lightbox-sub {
            color: #c5a880;
            font-size: 13px;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .lightbox-close {
            position: absolute;
            top: -45px;
            right: 0;
            color: #fff;
            font-size: 35px;
            cursor: pointer;
            transition: color 0.3s;
            z-index: 20;
        }
        .lightbox-close:hover {
            color: #c5a880;
        }

        /* MODAL SEARCH INTERAKTIF (Persis Index) */
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

        /* FOOTER (Persis Index) */
        footer {
            background-color: #111e19;
            color: rgba(255,255,255,0.5);
            padding: 60px 8% 30px 8%;
            font-size: 13px;
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
    </style>
</head>
<body>

    <!-- NAVBAR (Disamakan persis dengan Index) -->
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
                <li><a href="gallery.php" class="active">Galeri</a></li>
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

    <header class="gallery-header">
        <h1>Dokumentasi Perjalanan</h1>
        <p>Intip momen-momen indah, tawa, dan petualangan seru para travelers premium kami di berbagai destinasi Nusantara.</p>
    </header>

    <section class="gallery-section">
        <div class="gallery-filter">
            <button class="filter-btn active" onclick="filterGallery('all')">Semua</button>
            <button class="filter-btn" onclick="filterGallery('bali')">Bali</button>
            <button class="filter-btn" onclick="filterGallery('jogja')">Yogyakarta</button>
            <button class="filter-btn" onclick="filterGallery('malang')">Malang</button>
            <button class="filter-btn" onclick="filterGallery('bandung')">Bandung</button>
        </div>

        <div class="gallery-grid">
            <div class="gallery-item" data-category="bandung" onclick="openLightbox('bandung', 'Pesona Kawah Putih', 'Ciwidey, Bandung')">
                <div class="gallery-img-wrapper">
                    <img src="img/kawah-putih-01.webp" alt="Kawah Putih">
                    <div class="gallery-overlay"><i class="fa-solid fa-images"></i></div>
                </div>
                <div class="gallery-info">
                    <h3>Pesona Kawah Putih</h3>
                    <span class="gallery-tag">Bandung</span>
                </div>
            </div>

            <div class="gallery-item" data-category="jogja" onclick="openLightbox('jogja', 'Kemegahan Candi Prambanan', 'D.I. Yogyakarta')">
                <div class="gallery-img-wrapper">
                    <img src="img/yogyakarta-01.webp" alt="Yogyakarta">
                    <div class="gallery-overlay"><i class="fa-solid fa-images"></i></div>
                </div>
                <div class="gallery-info">
                    <h3>Kemegahan Candi Prambanan</h3>
                    <span class="gallery-tag">Yogyakarta</span>
                </div>
            </div>

            <div class="gallery-item" data-category="bali" onclick="openLightbox('bali', 'Sunset Eksotis Pura Tanah Lot', 'Tabanan, Bali')">
                <div class="gallery-img-wrapper">
                    <img src="img/bali-01.webp" alt="Bali">
                    <div class="gallery-overlay"><i class="fa-solid fa-images"></i></div>
                </div>
                <div class="gallery-info">
                    <h3>Sunset Eksotis Pura Tanah Lot</h3>
                    <span class="gallery-tag">Bali</span>
                </div>
            </div>

            <div class="gallery-item" data-category="malang" onclick="openLightbox('malang', 'Sunrise Golden Hour Bromo', 'Malang, Jawa Timur')">
                <div class="gallery-img-wrapper">
                    <img src="img/malang-01.webp" alt="Malang Bromo">
                    <div class="gallery-overlay"><i class="fa-solid fa-images"></i></div>
                </div>
                <div class="gallery-info">
                    <h3>Sunrise Golden Hour Bromo</h3>
                    <span class="gallery-tag">Malang</span>
                </div>
            </div>

            <div class="gallery-item" data-category="jakarta" onclick="openLightbox('jakarta', 'Cityscape Sudirman Malam Hari', 'DKI Jakarta')">
                <div class="gallery-img-wrapper">
                    <img src="img/jakarta-01.webp" alt="Jakarta">
                    <div class="gallery-overlay"><i class="fa-solid fa-images"></i></div>
                </div>
                <div class="gallery-info">
                    <h3>Cityscape Sudirman</h3>
                    <span class="gallery-tag">Jakarta</span>
                </div>
            </div>
        </div>
    </section>

    <!-- SLIDER LIGHTBOX MODAL -->
    <div class="lightbox-modal" id="lightboxModal" onclick="closeLightbox(event)">
        <div class="lightbox-content">
            <span class="lightbox-close" onclick="closeLightbox(event)">&times;</span>
            
            <div class="lightbox-slider-frame">
                <button class="lightbox-arrow arrow-left" onclick="moveSlider(-1)"><i class="fa-solid fa-chevron-left"></i></button>
                <button class="lightbox-arrow arrow-right" onclick="moveSlider(1)"><i class="fa-solid fa-chevron-right"></i></button>
                
                <div class="lightbox-slider-track" id="sliderTrack">
                    <div class="lightbox-slide"><img id="imgSlide0" src="" alt="Slide 1"></div>
                    <div class="lightbox-slide"><img id="imgSlide1" src="" alt="Slide 2"></div>
                    <div class="lightbox-slide"><img id="imgSlide2" src="" alt="Slide 3"></div>
                </div>
            </div>

            <div class="lightbox-dots">
                <div class="lightbox-dot" onclick="setSlide(0)"></div>
                <div class="lightbox-dot" onclick="setSlide(1)"></div>
                <div class="lightbox-dot" onclick="setSlide(2)"></div>
            </div>

            <div class="lightbox-caption" id="lightboxTitle">Judul Album</div>
            <div class="lightbox-sub" id="lightboxLocation">Lokasi</div>
        </div>
    </div>

    <!-- MODAL SEARCH INTERAKTIF -->
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

    <!-- FOOTER (Disamakan persis dengan Index) -->
    <?php include __DIR__ . "/includes/footer.php"; ?>

    <script>
        // Toggle Menu Responsive
        function toggleMenu() {
            const navContainer = document.getElementById('navContainer');
            navContainer.classList.toggle('active');
        }

        // Live Filter Kategori Galeri
        function filterGallery(category) {
            const buttons = document.querySelectorAll('.filter-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            const items = document.querySelectorAll('.gallery-item');
            items.forEach(item => {
                if (category === 'all' || item.getAttribute('data-category') === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Database Gambar Album Slider
        const galleryDatabase = {
            bandung: [
                'img/kawah-putih-01.webp',
                'img/kawah-putih-02.webp',
                'img/kawah-putih-03.webp'
            ],
            jogja: [
                'img/yogyakarta-01.webp',
                'img/yogyakarta-02.webp',
                'img/yogyakarta-03.webp'
            ],
            bali: [
                'img/bali-01.webp',
                'img/bali-02.webp',
                'img/bali-03.webp'
            ],
            malang: [
                'img/malang-01.webp',
                'img/malang-02.webp',
                'img/malang-03.webp'
            ],
            jakarta: [
                'img/jakarta-01.webp',
                'img/jakarta-02.webp',
                'img/jakarta-03.webp'
            ]
        };

        let currentSlideIndex = 0;

        function openLightbox(destKey, title, location) {
            currentSlideIndex = 0;
            const images = galleryDatabase[destKey] || [];
            
            if(images.length >= 3) {
                document.getElementById('imgSlide0').src = images[0];
                document.getElementById('imgSlide1').src = images[1];
                document.getElementById('imgSlide2').src = images[2];
            } else {
                const fallbackImg = document.querySelector(`[data-category="${destKey}"] img`).src;
                document.getElementById('imgSlide0').src = fallbackImg;
                document.getElementById('imgSlide1').src = fallbackImg;
                document.getElementById('imgSlide2').src = fallbackImg;
            }

            document.getElementById('lightboxTitle').innerText = title;
            document.getElementById('lightboxLocation').innerText = location;
            
            updateSliderPosition();
            document.getElementById('lightboxModal').classList.add('active');
        }

        function moveSlider(direction) {
            currentSlideIndex += direction;
            if (currentSlideIndex > 2) currentSlideIndex = 0;
            if (currentSlideIndex < 0) currentSlideIndex = 2;
            updateSliderPosition();
        }

        function setSlide(index) {
            currentSlideIndex = index;
            updateSliderPosition();
        }

        function updateSliderPosition() {
            const track = document.getElementById('sliderTrack');
            track.style.transform = `translateX(-${currentSlideIndex * 100}%)`;

            const dots = document.querySelectorAll('.lightbox-dot');
            dots.forEach((dot, idx) => {
                if(idx === currentSlideIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }

        function closeLightbox(event) {
            if (event.target.id === 'lightboxModal' || event.target.classList.contains('lightbox-close') || event.target.tagName === 'SPAN') {
                document.getElementById('lightboxModal').classList.remove('active');
            }
        }

        // Fungsi Handler untuk Modal Search (Persis Index)
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
            const searchModal = document.getElementById('searchModal');
            if (event.target === searchModal) {
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