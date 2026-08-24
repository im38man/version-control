<?php require_once __DIR__ . "/includes/session.php"; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Destinasi - Zenith Tour & Travel</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%231a2f27'/><text x='50' y='70' font-size='60' font-family='Playfair Display, serif' font-weight='bold' fill='%23c5a880' text-anchor='middle'>Z</text></svg>">
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* CORE STYLES & NAVBAR */
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

        /* PAGE HEADER / HERO */
        .page-header { background: linear-gradient(rgba(26, 47, 39, 0.7), rgba(26, 47, 39, 0.5)), url('img/bali-01.webp'); background-size: cover; background-position: center; height: 40vh; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: #fff; padding: 0 20px; }
        .page-header h1 { font-family: 'Playfair Display', serif; font-size: 42px; margin-bottom: 10px; font-weight: 700; }
        .page-header p { font-size: 16px; font-weight: 300; max-width: 600px; color: rgba(255,255,255,0.9); }

        /* DESTINATIONS GRID LAYOUT (TANPA SLIDER) */
        .destinations-section { padding: 80px 8%; background-color: #fcfbf7; text-align: center; flex: 1; }
        .section-title { font-family: 'Playfair Display', serif; font-size: 38px; color: #1a2f27; margin-bottom: 15px; }
        .section-subtitle { color: #c5a880; margin-bottom: 5px; font-size: 13px; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; }
        
        .destinations-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; max-width: 1200px; margin: 50px auto 0 auto; text-align: left; }

        /* CARD STYLE (Sama dengan desain asli) */
        .card { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.03); transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.4s; height: 100%; display: flex; flex-direction: column; border: 1px solid #e5e0d8; }
        .card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); border-color: #c5a880; }
        .card-img { height: 240px; background-size: cover; background-position: center; position: relative; }
        .tag-promo { position: absolute; top: 15px; left: 15px; background: rgba(197, 168, 128, 0.95); color: #fff; font-size: 11px; padding: 4px 10px; font-weight: 500; border-radius: 4px; letter-spacing: 1px; text-transform: uppercase; }
        .card-content { padding: 30px; display: flex; flex-direction: column; flex-grow: 1; }
        .card-content h3 { font-family: 'Playfair Display', serif; font-size: 22px; margin-bottom: 10px; color: #1a2f27; }
        .card-content p { font-size: 14px; color: #666; margin-bottom: 25px; font-weight: 300; flex-grow: 1; }
        .card-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f3eee7; padding-top: 20px; margin-top: auto; }
        .price { font-weight: 600; color: #c5a880; font-size: 18px; }
        .btn-secondary { border: 1px solid #c5a880; color: #c5a880; padding: 8px 18px; border-radius: 4px; font-size: 13px; font-weight: 500; transition: all 0.3s; background: transparent; cursor: pointer; }
        .btn-secondary:hover { background-color: #c5a880; color: #fff; box-shadow: 0 4px 10px rgba(197, 168, 128, 0.15); }
        .btn-detail { background: transparent; border: none; color: #555; font-family: 'Poppins', sans-serif; font-size: 13px; text-decoration: underline; cursor: pointer; transition: color 0.3s; margin-right: 12px; }
        .btn-detail:hover { color: #c5a880; }

        /* MODAL STYLES */
        .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(26, 47, 39, 0.96); backdrop-filter: blur(8px); display: none; justify-content: center; align-items: center; z-index: 2000; padding: 40px 20px; opacity: 0; transition: opacity 0.3s ease; }
        .modal.active { display: flex; opacity: 1; }
        .modal-content { background-color: #fff; max-width: 1000px; width: 100%; max-height: 85vh; border-radius: 12px; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.3); position: relative; transform: scale(0.95); transition: transform 0.3s cubic-bezier(0.165, 0.84, 0.44, 1); }
        .modal.active .modal-content { transform: scale(1); }
        .modal-body { display: flex; height: 85vh; max-height: 85vh; }
        .modal-image-side { flex: 1.1; background-color: #1a2f27; position: relative; }
        .modal-image-side img { width: 100%; height: 100%; object-fit: cover; }
        .modal-info-side { flex: 1.2; padding: 40px; display: flex; flex-direction: column; overflow-y: auto; }
        .modal-close { position: absolute; top: 20px; right: 25px; font-size: 32px; color: #1a2f27; cursor: pointer; transition: color 0.3s; z-index: 10; }
        .modal-close:hover { color: #c5a880; }
        .modal-tag { color: #c5a880; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; display: inline-block; margin-bottom: 5px; }
        .modal-title { font-family: 'Playfair Display', serif; font-size: 32px; color: #1a2f27; line-height: 1.2; margin-bottom: 10px; }
        .modal-meta { display: flex; gap: 20px; align-items: center; border-bottom: 1px solid #f3eee7; padding-bottom: 15px; margin-bottom: 20px; }
        .modal-price { font-size: 22px; font-weight: 600; color: #c5a880; }
        .modal-duration { font-size: 14px; color: #777; }
        .modal-tabs { display: flex; border-bottom: 1px solid #f3eee7; margin-bottom: 20px; gap: 20px; }
        .tab-btn { background: none; border: none; padding: 10px 0; font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 500; color: #777; cursor: pointer; position: relative; transition: color 0.3s; outline: none; }
        .tab-btn:hover, .tab-btn.active { color: #1a2f27; }
        .tab-btn.active::after { content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 2px; background-color: #c5a880; }
        .tab-content { display: none; font-size: 14px; color: #555; line-height: 1.6; flex-grow: 1; margin-bottom: 25px; }
        .tab-content.active { display: block; }
        .modal-list { list-style: none; padding-left: 0; margin-top: 10px; }
        .modal-list li { margin-bottom: 8px; position: relative; padding-left: 20px; color: #555; }
        .modal-list li::before { content: "•"; color: #c5a880; font-size: 18px; position: absolute; left: 0; top: -2px; }
        .itinerary-timeline { margin-top: 10px; }
        .itinerary-step { position: relative; padding-left: 25px; border-left: 1px solid #e5e0d8; padding-bottom: 15px; }
        .itinerary-step:last-child { border-left: none; padding-bottom: 0; }
        .itinerary-step::before { content: ''; position: absolute; left: -5px; top: 5px; width: 9px; height: 9px; border-radius: 50%; background-color: #c5a880; }
        .itinerary-day { font-weight: 600; color: #1a2f27; font-size: 14px; margin-bottom: 2px; }
        .itinerary-desc { font-size: 13px; color: #666; }
        .btn-modal-book { background-color: #1a2f27; color: #fff; border: none; width: 100%; padding: 14px; border-radius: 4px; font-weight: 500; font-size: 15px; cursor: pointer; transition: background 0.3s; box-shadow: 0 4px 10px rgba(26, 47, 39, 0.15); font-family: 'Poppins', sans-serif; }
        .btn-modal-book:hover { background-color: #c5a880; }
        @media(max-width: 768px) { .modal { padding: 10px; } .modal-body { flex-direction: column; height: 90vh; max-height: 90vh; } .modal-image-side { height: 180px; flex: none; } .modal-info-side { padding: 25px 20px; } .modal-title { font-size: 24px; } }

        /* FOOTER */
        footer { background-color: #111e19; color: rgba(255,255,255,0.5); padding: 60px 8% 30px 8%; font-size: 13px; margin-top: auto; }
        .footer-grid-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 40px; margin-bottom: 30px; }
        .footer-info-block h3 { color: #fff; font-family: 'Playfair Display', serif; font-size: 18px; margin-bottom: 15px; }
        .footer-info-block p, .footer-info-block a { color: rgba(255, 255, 255, 0.6); line-height: 1.8; margin-bottom: 8px; display: block; }
        .footer-info-block a:hover { color: #c5a880; }
        .footer-info-block i { margin-right: 8px; color: #c5a880; width: 16px; }
        .footer-logo { font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 700; color: #fff; margin-bottom: 15px; }
        .footer-socials { display: flex; gap: 20px; margin-top: 15px; }
        .footer-socials a { color: rgba(255,255,255,0.6); font-size: 20px; transition: color 0.3s; display: inline-block; }
        .footer-socials a:hover { color: #c5a880; }
        .copyright { text-align: center; font-size: 12px; }

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
                <li><a href="semua-destinasi.php" class="active">Destinasi</a></li>
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

    <header class="page-header">
        <h1>Semua Destinasi Pilihan</h1>
        <p>Temukan keindahan alam dan kekayaan budaya Nusantara. Pilih paket wisata impian Anda dan biarkan kami mengatur segala perlengkapannya untuk Anda.</p>
    </header>

    <section class="destinations-section">
        <p class="section-subtitle">Jelajahi Indonesia</p>
        <h2 class="section-title">Paket Liburan Kami</h2>
        
        <div class="destinations-grid">
            <div class="card">
                <div class="card-img" style="background-image: url('img/kawah-putih-02.webp');">
                    <span class="tag-promo">Udara Sejuk</span>
                </div>
                <div class="card-content">
                    <h3>Kawah Putih Bandung</h3>
                    <p>Nikmati pesona magis kawah vulkanik berkabut eksotis, hamparan kebun teh Ciwidey, dan glamping mewah di alam terbuka.</p>
                    <div class="card-footer">
                        <span class="price">Rp 2.200.000</span>
                        <div style="display: flex; align-items: center;">
                            <button class="btn-detail" onclick="openDetailModal('bandung')">Selengkapnya</button>
                            <button class="btn-secondary" onclick="pesanLangsung('Kawah Putih Bandung')">Pesan</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-img" style="background-image: url('img/yogyakarta-02.webp');">
                    <span class="tag-promo">Kaya Budaya</span>
                </div>
                <div class="card-content">
                    <h3>Klasik Yogyakarta</h3>
                    <p>Telusuri keagungan Candi Borobudur, kemegahan Keraton, kehangatan jalan Malioboro, serta tradisi membatik yang adiluhung.</p>
                    <div class="card-footer">
                        <span class="price">Rp 3.100.000</span>
                        <div style="display: flex; align-items: center;">
                            <button class="btn-detail" onclick="openDetailModal('yogyakarta')">Selengkapnya</button>
                            <button class="btn-secondary" onclick="pesanLangsung('Klasik Yogyakarta')">Pesan</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-img" style="background-image: url('img/bali-02.webp');">
                    <span class="tag-promo">Terpopuler</span>
                </div>
                <div class="card-content">
                    <h3>Eksotika Bali</h3>
                    <p>Rasakan keindahan murni pantai berpasir putih, ritual tradisi budaya Bali yang sakral, dan ketenangan Ubud.</p>
                    <div class="card-footer">
                        <span class="price">Rp 4.500.000</span>
                        <div style="display: flex; align-items: center;">
                            <button class="btn-detail" onclick="openDetailModal('bali')">Selengkapnya</button>
                            <button class="btn-secondary" onclick="pesanLangsung('Eksotika Bali')">Pesan</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-img" style="background-image: url('img/malang-02.webp');">
                    <span class="tag-promo">Eksplorasi Alam</span>
                </div>
                <div class="card-content">
                    <h3>Pesona Malang & Bromo</h3>
                    <p>Saksikan matahari terbit legendaris di Gunung Bromo, nikmati petik apel Kota Batu, dan kesejukan udara pegunungan Jawa Timur.</p>
                    <div class="card-footer">
                        <span class="price">Rp 3.800.000</span>
                        <div style="display: flex; align-items: center;">
                            <button class="btn-detail" onclick="openDetailModal('malang')">Selengkapnya</button>
                            <button class="btn-secondary" onclick="pesanLangsung('Pesona Malang & Bromo')">Pesan</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-img" style="background-image: url('img/jakarta-02.webp');">
                    <span class="tag-promo">Metropolitan</span>
                </div>
                <div class="card-content">
                    <h3>Metropolitan Jakarta</h3>
                    <p>Rasakan kemewahan staycation hotel bintang 5, wisata sejarah Kota Tua, pusat belanja premium, dan gemerlap kota modern.</p>
                    <div class="card-footer">
                        <span class="price">Rp 2.900.000</span>
                        <div style="display: flex; align-items: center;">
                            <button class="btn-detail" onclick="openDetailModal('jakarta')">Selengkapnya</button>
                            <button class="btn-secondary" onclick="pesanLangsung('Metropolitan Jakarta')">Pesan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MODAL DETAIL DESTINASI -->
    <div class="modal" id="detailModal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeDetailModal()">&times;</span>
            <div class="modal-body">
                <div class="modal-image-side">
                    <img id="modalImg" src="" alt="Detail Destinasi">
                </div>
                <div class="modal-info-side">
                    <div>
                        <span class="modal-tag" id="modalTag">Kategori</span>
                        <h2 class="modal-title" id="modalTitle">Title</h2>
                        <div class="modal-meta">
                            <span class="modal-price" id="modalPrice">Rp 0</span>
                            <span class="modal-duration" id="modalDuration"><i class="fa-regular fa-clock"></i> 3 Hari 2 Malam</span>
                        </div>

                        <div class="modal-tabs">
                            <button class="tab-btn active" onclick="switchTab(event, 'tab-overview')">Ikhtisar</button>
                            <button class="tab-btn" onclick="switchTab(event, 'tab-itinerary')">Rencana Perjalanan</button>
                            <button class="tab-btn" onclick="switchTab(event, 'tab-inclusion')">Fasilitas</button>
                        </div>

                        <div class="tab-content active" id="tab-overview">
                            <p id="modalDesc">Deskripsi lengkap destinasi.</p>
                            <h4 style="margin-top: 20px; color: #1a2f27; font-family: 'Playfair Display', serif; font-size: 16px;">Sorotan Utama Wisata:</h4>
                            <ul id="modalHighlights" class="modal-list"></ul>
                        </div>

                        <div class="tab-content" id="tab-itinerary">
                            <div class="itinerary-timeline" id="modalItinerary"></div>
                        </div>

                        <div class="tab-content" id="tab-inclusion">
                            <div style="margin-bottom: 20px;">
                                <h4 style="color: #1a2f27; font-size: 15px; margin-bottom: 5px;"><i class="fa-solid fa-circle-check" style="color: #2e7d32; margin-right: 8px;"></i> Termasuk (Inclusions):</h4>
                                <ul id="modalInclusions" class="modal-list"></ul>
                            </div>
                            <div>
                                <h4 style="color: #1a2f27; font-size: 15px; margin-bottom: 5px;"><i class="fa-solid fa-circle-xmark" style="color: #c62828; margin-right: 8px;"></i> Tidak Termasuk (Exclusions):</h4>
                                <ul id="modalExclusions" class="modal-list"></ul>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: auto; padding-top: 25px;">
                        <button class="btn-modal-book" id="modalBookBtn" onclick="bookFromModal()">Pesan Paket Wisata Ini</button>
                    </div>
                </div>
            </div>
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

        // Fungsi langsung ke WhatsApp untuk booking
        function pesanLangsung(namaPaket) {
            const pesan = `Halo Zenith Tour & Travel,\n\nSaya tertarik dengan paket wisata *${namaPaket}*. Mohon informasi ketersediaan jadwal dan detail lengkapnya. Terima kasih.`;
            window.open(`https://wa.me/62895333841200?text=${encodeURIComponent(pesan)}`, '_blank');
        }

        // Data Modal Destinasi
        const destinasiData = {
            bandung: {
                title: "Kawah Putih Bandung", tag: "Bandung, Indonesia", price: "Rp 2.200.000", duration: "3 Hari 2 Malam", image: "img/kawah-putih-03.webp",
                desc: "Jelajahi keindahan alam Ciwidey yang menyejukkan jiwa. Saksikan pesona magis Kawah Putih yang dikelilingi kabut belerang eksotis, nikmati petualangan di Jembatan Pinisi, serta kenyamanan menginap di glamping premium tepi danau.",
                highlights: [ "Menjelajahi kawasan kawah vulkanik Kawah Putih Ciwidey", "Menikmati sore romantis di Situ Patenggang", "Staycation mewah ala Glamping bintang 5", "Wisata kuliner dan belanja khas Paris van Java" ],
                itinerary: [ { day: "Hari 1", desc: "Tiba di Bandung, penjemputan oleh tim VIP. Mengunjungi area bersejarah Jalan Asia Afrika dan Braga, dilanjutkan makan malam kuliner khas Sunda premium." }, { day: "Hari 2", desc: "Perjalanan menuju Bandung Selatan. Menjelajahi Kawah Putih, berjalan di atas jembatan kayu Ranca Upas, dan bersantai di Kebun Teh Rancabali." }, { day: "Hari 3", desc: "Sarapan pagi dengan pemandangan alam, berburu oleh-oleh premium Kartika Sari/Amanda, lalu diantar kembali ke stasiun/bandara." } ],
                inclusions: [ "Akomodasi Glamping/Hotel Bintang 4 selama 2 malam", "Transportasi private ber-AC", "Tiket masuk semua objek wisata", "Makan sesuai jadwal" ],
                exclusions: [ "Tiket perjalanan PP kota asal", "Pengeluaran pribadi", "Tips guide & driver" ], bookingValue: "Kawah Putih Bandung"
            },
            yogyakarta: {
                title: "Klasik Yogyakarta", tag: "Yogyakarta, Indonesia", price: "Rp 3.100.000", duration: "3 Hari 2 Malam", image: "img/yogyakarta-03.webp",
                desc: "Kembali ke pusat kebudayaan Jawa yang penuh kehangatan. Nikmati tur eksklusif menyaksikan kemegahan Candi Borobudur saat fajar, napak tilas sejarah di Keraton Yogyakarta, serta petualangan Lava Tour Merapi yang memicu adrenalin.",
                highlights: [ "Berburu matahari terbit eksklusif di Borobudur", "Wisata budaya di Keraton dan Istana Air Taman Sari", "Petualangan Jeep Lava Tour Merapi", "Makan malam romantis dengan tarian tradisional Jawa" ],
                itinerary: [ { day: "Hari 1", desc: "Tiba di Yogyakarta, check-in hotel butik premium. Mengunjungi Keraton Yogyakarta dan Taman Sari, sore hari menikmati suasana hangat Malioboro." }, { day: "Hari 2", desc: "Dini hari menuju Borobudur untuk melihat sunrise. Dilanjutkan petualangan seru naik Jeep terbuka menyusuri sisa aliran lava Merapi." }, { day: "Hari 3", desc: "Mengunjungi pusat kerajinan perak dan workshop batik tulis, dilanjutkan transfer menuju bandara/stasiun." } ],
                inclusions: [ "Hotel butik bintang 4/5 selama 2 malam", "Sewa Jeep private Merapi", "Semua tiket masuk VIP", "Layanan antar-jemput" ],
                exclusions: [ "Tiket transportasi ke Yogyakarta", "Pengeluaran pribadi" ], bookingValue: "Klasik Yogyakarta"
            },
            bali: {
                title: "Eksotika Bali", tag: "Bali, Indonesia", price: "Rp 4.500.000", duration: "4 Hari 3 Malam", image: "img/bali-03.webp",
                desc: "Rasakan harmoni alam dan budaya spiritual di pulau Dewata. Paket premium ini dirancang khusus untuk membawa Anda menikmati kedamaian sejati Ubud, pemandangan dramatis pura Uluwatu di atas tebing, serta petualangan eksotis pulau Nusa Penida.",
                highlights: [ "Menyaksikan matahari terbenam magis di Pura Uluwatu", "Menyusuri sawah Tegalalang", "Sesi spa aromaterapi Bali tradisional", "Eksplorasi pantai ikonik Kelingking Beach" ],
                itinerary: [ { day: "Hari 1", desc: "Tiba di Bali, disambut pemandu lokal. Menonton Tari Kecak berlatar sunset di Uluwatu." }, { day: "Hari 2", desc: "Berjalan di Campuhan Ridge Walk, Tegalalang, dan makan malam romantis di Ubud." }, { day: "Hari 3", desc: "Speed boat ke Nusa Penida. Menjelajahi Kelingking Beach, Broken Beach, dan Angels Billabong." }, { day: "Hari 4", desc: "Menikmati spa khas Bali sebelum berbelanja cinderamata dan pulang." } ],
                inclusions: [ "Hotel bintang 5 (3 Malam)", "Transportasi private ber-AC", "Tiket fast boat Nusa Penida", "Semua tiket masuk wisata" ],
                exclusions: [ "Tiket pesawat PP", "Pengeluaran pribadi" ], bookingValue: "Eksotika Bali"
            },
            malang: {
                title: "Pesona Malang & Bromo", tag: "Malang, Indonesia", price: "Rp 3.800.000", duration: "3 Hari 2 Malam", image: "img/malang-03.webp",
                desc: "Saksikan pemandangan matahari terbit terindah di Gunung Bromo. Padukan petualangan tersebut dengan kesejukan Kota Wisata Batu yang ramah keluarga serta perkebunan apel organik yang asri.",
                highlights: [ "Golden sunrise Bromo dari Penanjakan", "Menjelajahi lautan pasir Bromo menggunakan Jeep 4x4", "Eksplorasi wahana premium di Kota Batu", "Memetik buah apel segar langsung di kebun" ],
                itinerary: [ { day: "Hari 1", desc: "Penjemputan di Malang. Menuju Batu, mengunjungi Museum Angkut, check-in resort mewah." }, { day: "Hari 2", desc: "Pukul 00.30 menuju Bromo dengan Jeep 4x4. Sunrise, kawah Bromo, Pasir Berbisik dan Bukit Teletubbies." }, { day: "Hari 3", desc: "Agrowisata memetik apel, lalu transfer kembali ke bandara/stasiun." } ],
                inclusions: [ "Resort bintang 4 (2 malam)", "Sewa Jeep eksklusif 4x4", "Tiket masuk TN Bromo", "Makan selama tour" ],
                exclusions: [ "Tiket akomodasi dari kota asal", "Pengeluaran pribadi" ], bookingValue: "Pesona Malang & Bromo"
            },
            jakarta: {
                title: "Metropolitan Jakarta", tag: "Jakarta, Indonesia", price: "Rp 2.900.000", duration: "3 Hari 2 Malam", image: "img/jakarta-03.webp",
                desc: "Nikmati sisi mewah dari ibu kota Indonesia. Rasakan sensasi menginap di hotel pencakar langit bintang 5, makan malam romantis di cruise Jakarta Bay, serta tur sejarah eksklusif di Batavia lama (Kota Tua).",
                highlights: [ "Staycation premium di luxury hotel bintang 5", "Sunset dinner cruise romantis", "Private tour sejarah Batavia Lama dan PIK", "Akses belanja eksklusif" ],
                itinerary: [ { day: "Hari 1", desc: "Penjemputan mobil premium. Check-in hotel bintang 5. Dinner cruise romantis di atas yacht/cruise kecil." }, { day: "Hari 2", desc: "Private tour Kota Tua dengan sepeda ontel, sorenya ke Pantai Indah Kapuk (PIK) 2." }, { day: "Hari 3", desc: "Fasilitas spa hotel dan berbelanja di Plaza Indonesia sebelum ke bandara." } ],
                inclusions: [ "Hotel bintang 5 (2 malam)", "Transportasi private premium", "Tiket Exclusive Dinner Cruise", "Semua tiket masuk" ],
                exclusions: [ "Tiket transportasi PP ke Jakarta", "Pengeluaran pribadi" ], bookingValue: "Metropolitan Jakarta"
            }
        };

        let activeDestinasiKey = '';

        function openDetailModal(key) {
            const data = destinasiData[key];
            if (!data) return;

            activeDestinasiKey = key;

            document.getElementById('modalImg').src = data.image;
            document.getElementById('modalTag').innerText = data.tag;
            document.getElementById('modalTitle').innerText = data.title;
            document.getElementById('modalPrice').innerText = data.price;
            document.getElementById('modalDuration').innerHTML = `<i class="fa-regular fa-clock" style="margin-right: 5px;"></i> ${data.duration}`;
            document.getElementById('modalDesc').innerText = data.desc;

            const highlightsList = document.getElementById('modalHighlights');
            highlightsList.innerHTML = '';
            data.highlights.forEach(item => {
                const li = document.createElement('li'); li.innerText = item; highlightsList.appendChild(li);
            });

            const itineraryContainer = document.getElementById('modalItinerary');
            itineraryContainer.innerHTML = '';
            data.itinerary.forEach(step => {
                const stepDiv = document.createElement('div');
                stepDiv.className = 'itinerary-step';
                stepDiv.innerHTML = `<div class="itinerary-day">${step.day}</div><div class="itinerary-desc">${step.desc}</div>`;
                itineraryContainer.appendChild(stepDiv);
            });

            const inclusionList = document.getElementById('modalInclusions');
            inclusionList.innerHTML = '';
            data.inclusions.forEach(item => {
                const li = document.createElement('li'); li.innerText = item; inclusionList.appendChild(li);
            });

            const exclusionList = document.getElementById('modalExclusions');
            exclusionList.innerHTML = '';
            data.exclusions.forEach(item => {
                const li = document.createElement('li'); li.innerText = item; exclusionList.appendChild(li);
            });

            resetTabs();

            const modal = document.getElementById('detailModal');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
            document.body.style.overflow = 'hidden'; 
        }

        function closeDetailModal() {
            const modal = document.getElementById('detailModal');
            modal.classList.remove('active');
            setTimeout(() => modal.style.display = 'none', 300);
            document.body.style.overflow = 'auto'; 
        }

        function switchTab(event, tabId) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            event.currentTarget.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        }

        function resetTabs() {
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(btn => btn.classList.remove('active'));
            if (tabButtons[0]) tabButtons[0].classList.add('active');

            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            if (tabContents[0]) tabContents[0].classList.add('active');
        }

        function bookFromModal() {
            if (!activeDestinasiKey) return;
            const data = destinasiData[activeDestinasiKey];
            pesanLangsung(data.bookingValue);
        }

        window.addEventListener('click', function(event) {
            const modal = document.getElementById('detailModal');
            const searchModal = document.getElementById('searchModal');
            if (event.target === modal) closeDetailModal();
            if (event.target === searchModal) closeSearchModal();
        });

        window.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDetailModal();
                closeSearchModal();
            }
        });
    </script>
</body>
</html>