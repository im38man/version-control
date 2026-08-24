<?php require_once __DIR__ . "/includes/session.php"; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesona Dataran Tinggi Bandung - Zenith Tour & Travel</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%231a2f27'/><text x='50' y='70' font-size='60' font-family='Playfair Display, serif' font-weight='bold' fill='%23c5a880' text-anchor='middle'>Z</text></svg>">
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; scroll-behavior: smooth; }
        body { font-family: 'Poppins', sans-serif; color: #333; line-height: 1.6; background-color: #fcfbf7; overflow-x: hidden; display: flex; flex-direction: column; min-height: 100vh; }
        a { text-decoration: none; color: inherit; }
        
        nav { display: flex; justify-content: space-between; align-items: center; padding: 15px 5%; background-color: #ffffff; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1000; }
        .logo { font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 700; color: #1a2f27; letter-spacing: 1px; }
        .nav-right-container { display: flex; align-items: center; gap: 40px; }
        .nav-links { display: flex; list-style: none; gap: 30px; align-items: center; }
        .nav-links a { font-size: 14px; font-weight: 500; transition: color 0.3s; color: #555; }
        .nav-links a:hover, .nav-links a.active { color: #c5a880; }
        .nav-socials { display: flex; gap: 15px; align-items: center; border-left: 1px solid #e5e0d8; padding-left: 20px; }
        .nav-socials a, .nav-search-btn { color: #1a2f27; font-size: 18px; transition: color 0.3s, transform 0.3s; background: none; border: none; cursor: pointer; }
        .nav-socials a:hover, .nav-search-btn:hover { color: #c5a880; transform: scale(1.15); }
        .btn-nav-book { background-color: #1a2f27; color: #fff !important; padding: 8px 20px; border-radius: 4px; font-size: 13px; transition: background 0.3s !important; }
        .btn-nav-book:hover { background-color: #c5a880; }
        .menu-toggle { display: none; font-size: 22px; color: #1a2f27; cursor: pointer; }
        
        @media(max-width: 991px) {
            nav { padding: 15px 5%; position: sticky; }
            .nav-right-container { display: none; flex-direction: column; position: absolute; top: 100%; left: 0; width: 100%; background: #fff; padding: 20px 5%; box-shadow: 0 10px 15px rgba(0,0,0,0.05); gap: 15px; border-top: 1px solid #f3eee7; }
            .nav-right-container.active { display: flex; }
            .nav-links { flex-direction: column; gap: 15px; width: 100%; text-align: center; }
            .nav-socials { border-left: none; padding-left: 0; border-top: 1px solid #f3eee7; padding-top: 15px; width: 100%; justify-content: center; }
            .menu-toggle { display: block; }
        }

        .package-hero { position: relative; height: 60vh; display: flex; align-items: center; justify-content: center; text-align: center; color: #fff; background-image: url('https://images.unsplash.com/photo-1584646098378-0874589d76b1?auto=format&fit=crop&q=80&w=1200'); background-size: cover; background-position: center; }
        .package-hero::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(rgba(26, 47, 39, 0.6), rgba(26, 47, 39, 0.3)); z-index: 1; }
        .hero-content { position: relative; z-index: 2; padding: 0 20px; }
        .hero-tag { background: #c5a880; color: #fff; font-size: 12px; padding: 6px 15px; border-radius: 20px; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; display: inline-block; margin-bottom: 20px; }
        .package-hero h1 { font-family: 'Playfair Display', serif; font-size: 32px; font-weight: 700; margin-bottom: 10px; line-height: 1.2; }
        .package-hero p { font-size: 16px; font-weight: 300; opacity: 0.9; }

        @media(min-width: 992px) {
            .package-hero h1 { font-size: 48px; }
            .package-hero p { font-size: 18px; }
        }

        .detail-wrapper { display: grid; grid-template-columns: 2fr 1.1fr; gap: 30px; max-width: 1200px; margin: 40px auto; padding: 0 5%; flex: 1; }
        
        @media(max-width: 991px) {
            .detail-wrapper { grid-template-columns: 1fr; padding: 0 4%; gap: 20px; }
            .detail-content section { padding: 20px !important; }
            .sidebar-booking { padding: 25px 20px !important; }
        }

        .detail-content section { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.03); border: 1px solid #e5e0d8; margin-bottom: 30px; }
        .detail-content h3 { font-family: 'Playfair Display', serif; font-size: 24px; color: #1a2f27; margin-bottom: 20px; border-bottom: 1px solid #f3eee7; padding-bottom: 15px; }
        .detail-content p { color: #555; margin-bottom: 20px; font-size: 15px; }
        
        .highlight-list, .inclusion-list { list-style: none; margin-bottom: 20px; }
        .highlight-list li, .inclusion-list li { margin-bottom: 12px; position: relative; padding-left: 28px; color: #555; font-size: 15px; }
        .highlight-list li::before, .inclusion-list li::before { content: "\f058"; font-family: "Font Awesome 6 Free"; font-weight: 900; color: #c5a880; position: absolute; left: 0; top: 2px; font-size: 18px; }
        .inc-yes li::before { content: "\f058"; color: #2e7d32; }
        .inc-no li::before { content: "\f057"; color: #c62828; }

        .itinerary-timeline { margin-top: 20px; }
        .itinerary-step { position: relative; padding-left: 35px; border-left: 2px solid #e5e0d8; padding-bottom: 30px; }
        .itinerary-step:last-child { border-left-color: transparent; padding-bottom: 0; }
        .itinerary-step::before { content: ''; position: absolute; left: -9px; top: 0; width: 16px; height: 16px; border-radius: 50%; background-color: #c5a880; border: 3px solid #fff; box-shadow: 0 0 0 1px #e5e0d8; }
        .itinerary-day { font-family: 'Playfair Display', serif; font-weight: 700; color: #1a2f27; font-size: 18px; margin-bottom: 8px; }
        .itinerary-desc { font-size: 14px; color: #666; line-height: 1.7; }

        .package-gallery-section { max-width: 1200px; margin: 0 auto 60px auto; padding: 0 5%; width: 100%; }
        .gallery-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 20px; }
        .gallery-item { height: 200px; border-radius: 8px; overflow: hidden; background-size: cover; background-position: center; cursor: zoom-in; position: relative; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.3s; display: none; }
        .gallery-item.visible { display: block; }
        .gallery-item:hover { transform: scale(1.03); }
        .gallery-item::after { content: '\f00e'; font-family: "Font Awesome 6 Free"; font-weight: 900; position: absolute; bottom: 12px; right: 12px; background: rgba(26, 47, 39, 0.8); color: #fff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; opacity: 0; transition: opacity 0.3s; }
        .gallery-item:hover::after { opacity: 1; }

        .gallery-controls { display: flex; justify-content: space-between; align-items: center; background: #ffffff; padding: 15px 20px; border-radius: 8px; border: 1px solid #e5e0d8; box-shadow: 0 4px 15px rgba(0,0,0,0.02); gap: 10px; }
        .gallery-btn { background: #1a2f27; color: #fff; border: none; padding: 10px 16px; border-radius: 4px; cursor: pointer; font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 6px; transition: background 0.3s; white-space: nowrap; }
        .gallery-btn:hover:not(:disabled) { background: #c5a880; }
        .gallery-btn:disabled { background: #ccc; cursor: not-allowed; opacity: 0.7; }
        .gallery-page-indicator { font-size: 13px; color: #555; font-weight: 500; font-family: 'Playfair Display', serif; text-align: center; white-space: nowrap; }

        @media(max-width: 768px) { 
            .gallery-grid { grid-template-columns: 1fr; } 
            .gallery-item { height: 260px; }
            .gallery-controls { flex-wrap: wrap; justify-content: center; gap: 12px; padding: 12px; }
            .gallery-page-indicator { width: 100%; order: -1; margin-bottom: 2px; }
            .gallery-btn { flex: 1; justify-content: center; }
        }

        .zoom-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.9); display: none; justify-content: center; align-items: center; z-index: 4000; opacity: 0; transition: opacity 0.3s ease; }
        .zoom-modal.active { display: flex; opacity: 1; }
        .zoom-modal img { max-width: 90%; max-height: 85vh; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); object-fit: contain; }
        .zoom-close { position: absolute; top: 25px; right: 35px; color: #fff; font-size: 36px; cursor: pointer; transition: color 0.3s; }
        .zoom-close:hover { color: #c5a880; }

        .marquee-section { background-color: #1a2f27; padding: 40px 0; overflow: hidden; margin-bottom: 60px; position: relative; }
        .marquee-title { text-align: center; color: #c5a880; font-family: 'Playfair Display', serif; font-size: 20px; margin-bottom: 25px; letter-spacing: 2px; text-transform: uppercase; }
        .marquee-container { display: flex; width: 100%; overflow: hidden; white-space: nowrap; position: relative; }
        .marquee-track { display: flex; gap: 25px; width: max-content; animation: scrollMarquee 25s linear infinite; }
        .marquee-track:hover { animation-play-state: paused; }
        .poster-card { width: 280px; height: 180px; border-radius: 8px; background-size: cover; background-position: center; position: relative; box-shadow: 0 8px 20px rgba(0,0,0,0.3); border: 1px solid rgba(197, 168, 128, 0.4); flex-shrink: 0; display: flex; align-items: flex-end; padding: 15px; }
        .poster-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to top, rgba(26,47,39,0.8), transparent); border-radius: 8px; z-index: 1; }
        .poster-name { position: relative; z-index: 2; color: #fff; font-family: 'Playfair Display', serif; font-size: 16px; font-weight: 600; text-shadow: 0 2px 4px rgba(0,0,0,0.5); }

        @keyframes scrollMarquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(calc(-280px * 5 - 25px * 5)); }
        }

        .sidebar-booking { background: #1a2f27; color: #fff; padding: 40px; border-radius: 12px; position: sticky; top: 100px; box-shadow: 0 15px 35px rgba(0,0,0,0.15); }
        .price-tag { font-family: 'Playfair Display', serif; font-size: 36px; color: #c5a880; font-weight: 700; margin-bottom: 5px; line-height: 1.1; }
        .price-sub { font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 25px; display: block; }
        
        .sidebar-info { display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; margin-bottom: 15px; font-size: 14px; }
        .sidebar-info span:first-child { color: rgba(255,255,255,0.7); }
        .sidebar-info span:last-child { font-weight: 500; color: #fff; text-align: right; }

        .booking-form-sidebar { margin-top: 30px; }
        .form-group { margin-bottom: 20px; display: flex; flex-direction: column; }
        .form-group label { font-size: 12px; color: #c5a880; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 8px; font-weight: 500; }
        .form-group input, .form-group select { padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); border-radius: 4px; color: #fff; font-family: 'Poppins', sans-serif; font-size: 14px; outline: none; transition: border-color 0.3s; }
        .form-group input:focus, .form-group select:focus { border-color: #c5a880; }

        .btn-book-sidebar { width: 100%; padding: 15px; background-color: #c5a880; border: none; color: #fff; font-weight: 600; font-size: 15px; border-radius: 4px; cursor: pointer; transition: background 0.3s, transform 0.2s; font-family: 'Poppins', sans-serif; display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 10px; }
        .btn-book-sidebar:hover { background-color: #b0936d; transform: translateY(-2px); }
        .btn-book-web { background-color: transparent; border: 1.5px solid rgba(255,255,255,0.6); color: #fff; margin-top: 12px; }
        .btn-book-web:hover { background-color: rgba(255,255,255,0.08); border-color: #c5a880; color: #c5a880; transform: translateY(-2px); }

        .search-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(26, 47, 39, 0.92); backdrop-filter: blur(8px); display: none; justify-content: center; align-items: center; z-index: 3000; opacity: 0; transition: opacity 0.3s ease; }
        .search-modal.active { display: flex; opacity: 1; }
        .search-modal-content { width: 90%; max-width: 600px; text-align: center; position: relative; transform: translateY(-20px); transition: transform 0.3s ease; }
        .search-modal.active .search-modal-content { transform: translateY(0); }
        .search-input-wrapper { position: relative; width: 100%; }
        .search-modal-input { width: 100%; padding: 20px 60px 20px 25px; font-size: 20px; font-family: 'Poppins', sans-serif; background: #ffffff; border: none; border-radius: 50px; outline: none; color: #1a2f27; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .search-modal-input::placeholder { color: #aaa; }
        .search-modal-submit { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: #1a2f27; color: #fff; border: none; width: 45px; height: 45px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px; transition: background 0.3s; }
        .search-modal-submit:hover { background: #c5a880; }
        .search-modal-close { position: absolute; top: -50px; right: 0; color: #fff; font-size: 28px; cursor: pointer; transition: color 0.3s; }
        .search-modal-close:hover { color: #c5a880; }
        .search-hint { color: rgba(255,255,255,0.7); font-size: 13px; margin-top: 15px; letter-spacing: 0.5px; }

        footer { background-color: #111e19; color: rgba(255,255,255,0.5); padding: 60px 5% 30px 5%; font-size: 13px; margin-top: auto; }
        .footer-grid-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 40px; margin-bottom: 30px; }
        .footer-info-block h3 { color: #fff; font-family: 'Playfair Display', serif; font-size: 18px; margin-bottom: 15px; }
        .footer-info-block p, .footer-info-block a { color: rgba(255, 255, 255, 0.6); line-height: 1.8; margin-bottom: 8px; display: block; transition: color 0.3s; }
        .footer-info-block a:hover { color: #c5a880; }
        .footer-info-block i { margin-right: 8px; color: #c5a880; width: 16px; }
        .footer-logo { font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 700; color: #fff; margin-bottom: 15px; }
        .footer-socials { display: flex; gap: 20px; margin-top: 15px; }
        .footer-socials a { color: rgba(255,255,255,0.6); font-size: 20px; transition: color 0.3s; display: inline-block; }
        .footer-socials a:hover { color: #c5a880; }
        .copyright { text-align: center; font-size: 12px; }
    </style>
</head>
<body>

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

    <header class="package-hero">
        <div class="hero-content">
            <span class="hero-tag">Dataran Tinggi Sejuk</span>
            <h1>Pesona Dataran Tinggi Bandung</h1>
            <p><i class="fa-solid fa-location-dot" style="color: #c5a880; margin-right: 8px;"></i> Lembang - Kawah Putih, Bandung, Indonesia</p>
        </div>
    </header>
    <?php require_once __DIR__ . '/includes/favorite_button.php'; ?>
    <div style="max-width:1000px;margin:20px auto 0;padding:0 20px;text-align:right;">
        <?php render_favorite_button('bandung', 'Pesona Dataran Tinggi Bandung'); ?>
    </div>


    <div class="detail-wrapper">
        <div class="detail-content">
            <section id="ikhtisar">
                <h3>Ikhtisar Perjalanan</h3>
                <p>Nikmati kesejukan udara pegunungan parahyangan dalam paket wisata eksklusif Bandung. Dari keindahan vulkanik Kawah Putih yang memukau hingga hamparan kebun teh Lembang yang asri, dipadukan dengan kuliner khas Sunda kelas premium.</p>
                
                <h4 style="font-size: 16px; color: #1a2f27; margin: 25px 0 15px 0; font-family: 'Playfair Display', serif;">Sorotan Utama Wisata:</h4>
                <ul class="highlight-list">
                    <li>Menyaksikan keindahan kawah vulkanik putih kehijauan di Ciwidey.</li><li>Menjelajahi kebun teh Rancabali dan spot foto Instagramable di Lembang.</li><li>Wisata kuliner lezat khas Sunda autentik di restoran bernuansa alam.</li><li>Belanja produk fashion dan oleh-oleh premium Factory Outlet Bandung.</li>
                </ul>
            </section>

            <section id="itinerary">
                <h3>Rencana Perjalanan (Itinerary)</h3>
                <div class="itinerary-timeline">
                    
                    <div class="itinerary-step">
                        <div class="itinerary-day">Hari 1: Eksplorasi Ciwidey & Kawah Putih</div>
                        <div class="itinerary-desc">Penjemputan di Stasiun/Bandara Bandung, langsung menuju kawasan Ciwidey untuk mengunjungi Kawah Putih yang eksotis. Dilanjutkan bersantai di kebun teh Rancabali dan menikmati makan malam hangat khas Sunda.</div>
                    </div>
                    <div class="itinerary-step">
                        <div class="itinerary-day">Hari 2: Pesona Alam & Kuliner Lembang</div>
                        <div class="itinerary-desc">Menuju dataran tinggi Lembang mengunjungi Farm House dan Floating Market. Menikmati udara sejuk pegunungan serta berbelanja suvenir khas lokal berkualitas tinggi.</div>
                    </div>
                    <div class="itinerary-step">
                        <div class="itinerary-day">Hari 3: City Tour & Kepulangan</div>
                        <div class="itinerary-desc">Mengunjungi ikon kota Bandung Gedung Sate dan berbelanja fashion di Jalan Riau (Factory Outlet). Diantar kembali menuju titik kepulangan (Stasiun/Bandara/Hotel).</div>
                    </div>
                </div>
            </section>

            <section id="fasilitas">
                <h3>Fasilitas Paket</h3>
                <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
                    <div>
                        <h4 style="font-size: 15px; color: #1a2f27; margin-bottom: 15px;">Termasuk (Inclusions):</h4>
                        <ul class="inclusion-list inc-yes">
                            <li>Akomodasi Hotel Bintang 4 pilihan di pusat kota Bandung (2 Malam)</li><li>Transportasi private ber-AC dengan supir berpengalaman</li><li>Tiket masuk seluruh objek wisata (Kawah Putih, Lembang, dll)</li><li>Makan gourmet sesuai program termasuk kuliner Sunda autentik</li><li>Pemandu wisata profesional (Tour Guide) resmi</li>
                        </ul>
                    </div>
                    <div style="margin-top: 10px;">
                        <h4 style="font-size: 15px; color: #1a2f27; margin-bottom: 15px;">Tidak Termasuk (Exclusions):</h4>
                        <ul class="inclusion-list inc-no">
                            <li>Tiket kereta/pesawat dari kota asal ke Bandung</li><li>Pengeluaran belanja pribadi dan tips guide/driver</li><li>Wahana opsional di luar program</li>
                        </ul>
                    </div>
                </div>
            </section>
        </div>

        <div>
            <div class="sidebar-booking" id="booking">
                <div class="price-tag">Rp 2.750.000</div>
                <span class="price-sub">per orang (Min. keberangkatan 2 orang)</span>
                
                <div class="sidebar-info">
                    <span><i class="fa-regular fa-clock" style="color: #c5a880; margin-right: 5px;"></i> Durasi</span>
                    <span>3 Hari 2 Malam</span>
                </div>
                <div class="sidebar-info">
                    <span><i class="fa-solid fa-car" style="color: #c5a880; margin-right: 5px;"></i> Transportasi</span>
                    <span>Private Car AC</span>
                </div>
                <div class="sidebar-info" style="border-bottom: none;">
                    <span><i class="fa-solid fa-bed" style="color: #c5a880; margin-right: 5px;"></i> Penginapan</span>
                    <span>Hotel Bintang 4</span>
                </div>

                <form class="booking-form-sidebar" onsubmit="prosesBooking(event)">
                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" required placeholder="Nama Anda">
                    </div>
                    <div class="form-group">
                        <label for="jumlah">Jumlah Peserta</label>
                        <input type="number" id="jumlah" min="2" value="2" required>
                    </div>
                    <div class="form-group">
                        <label for="tanggal">Rencana Keberangkatan</label>
                        <input type="date" id="tanggal" required>
                    </div>
                    
                    <input type="hidden" id="paket" value="Pesona Dataran Tinggi Bandung">

                    <button type="submit" class="btn-book-sidebar">
                        <i class="fa-brands fa-whatsapp" style="font-size: 18px;"></i> Pesan via WhatsApp
                    </button>
                    <button type="button" class="btn-book-sidebar btn-book-web" onclick="pesanViaWeb(event)">
                        <i class="fa-solid fa-globe" style="font-size: 16px;"></i> Pesan Langsung di Web
                    </button>
                    <p style="text-align: center; font-size: 11px; color: rgba(255,255,255,0.5); margin-top: 15px;">
                        Konsultasi gratis tanpa biaya komitmen.
                    </p>
                </form>
            </div>
        </div>
    </div>

    <div class="package-gallery-section">
        <h3 style="font-family: 'Playfair Display', serif; font-size: 26px; color: #1a2f27; margin-bottom: 20px; text-align: center;">Galeri Dokumentasi Wisata</h3>
        
        <div class="gallery-grid" id="galleryGrid">
            <div class="gallery-item" style="background-image: url('https://images.unsplash.com/photo-1584646098378-0874589d76b1?auto=format&fit=crop&q=80&w=800');" onclick="openZoomModal('https://images.unsplash.com/photo-1584646098378-0874589d76b1?auto=format&fit=crop&q=80&w=800')"></div><div class="gallery-item" style="background-image: url('https://images.unsplash.com/photo-1596401348083-06a4b1602a63?auto=format&fit=crop&q=80&w=800');" onclick="openZoomModal('https://images.unsplash.com/photo-1596401348083-06a4b1602a63?auto=format&fit=crop&q=80&w=800')"></div><div class="gallery-item" style="background-image: url('https://images.unsplash.com/photo-1609137144813-7728684d6ddf?auto=format&fit=crop&q=80&w=800');" onclick="openZoomModal('https://images.unsplash.com/photo-1609137144813-7728684d6ddf?auto=format&fit=crop&q=80&w=800')"></div><div class="gallery-item" style="background-image: url('https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&q=80&w=800');" onclick="openZoomModal('https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&q=80&w=800')"></div><div class="gallery-item" style="background-image: url('https://images.unsplash.com/photo-1512100356356-de1b84283e18?auto=format&fit=crop&q=80&w=800');" onclick="openZoomModal('https://images.unsplash.com/photo-1512100356356-de1b84283e18?auto=format&fit=crop&q=80&w=800')"></div><div class="gallery-item" style="background-image: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&q=80&w=800');" onclick="openZoomModal('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&q=80&w=800')"></div><div class="gallery-item" style="background-image: url('https://images.unsplash.com/photo-1578593147286-631e054668c2?auto=format&fit=crop&q=80&w=800');" onclick="openZoomModal('https://images.unsplash.com/photo-1578593147286-631e054668c2?auto=format&fit=crop&q=80&w=800')"></div><div class="gallery-item" style="background-image: url('https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&q=80&w=800');" onclick="openZoomModal('https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&q=80&w=800')"></div><div class="gallery-item" style="background-image: url('https://images.unsplash.com/photo-1570789210967-2cac24afeb00?auto=format&fit=crop&q=80&w=800');" onclick="openZoomModal('https://images.unsplash.com/photo-1570789210967-2cac24afeb00?auto=format&fit=crop&q=80&w=800')"></div><div class="gallery-item" style="background-image: url('https://images.unsplash.com/photo-1558005530-d3c16f2c366e?auto=format&fit=crop&q=80&w=800');" onclick="openZoomModal('https://images.unsplash.com/photo-1558005530-d3c16f2c366e?auto=format&fit=crop&q=80&w=800')"></div><div class="gallery-item" style="background-image: url('https://images.unsplash.com/photo-1559628376-f365d1a76c59?auto=format&fit=crop&q=80&w=800');" onclick="openZoomModal('https://images.unsplash.com/photo-1559628376-f365d1a76c59?auto=format&fit=crop&q=80&w=800')"></div>
        </div>

        <div class="gallery-controls">
            <button class="gallery-btn" id="prevGalleryBtn" onclick="changeGalleryPage(-1)" disabled><i class="fa-solid fa-arrow-left"></i> Sebelumnya</button>
            <div class="gallery-page-indicator" id="galleryPageIndicator">Halaman 1 dari 3</div>
            <button class="gallery-btn" id="nextGalleryBtn" onclick="changeGalleryPage(1)">Selanjutnya <i class="fa-solid fa-arrow-right"></i></button>
        </div>
    </div>

    <div class="zoom-modal" id="zoomModal" onclick="closeZoomModal()">
        <span class="zoom-close">&times;</span>
        <img id="zoomModalImg" src="" alt="Zoomed Image">
    </div>

    <div class="marquee-section">
        <div class="marquee-title">Jelajahi Koleksi Destinasi Nusantara Lainnya</div>
        <div class="marquee-container">
            <div class="marquee-track">
                <a href="paket-bandung.php" class="poster-card" style="background-image: url('https://images.unsplash.com/photo-1584646098378-0874589d76b1?auto=format&fit=crop&q=80&w=600');"><div class="poster-name">Dataran Tinggi Bandung</div></a><a href="paket-yogyakarta.php" class="poster-card" style="background-image: url('https://images.unsplash.com/photo-1584551246679-0daf3d275d0f?auto=format&fit=crop&q=80&w=600');"><div class="poster-name">Klasik Yogyakarta</div></a><a href="paket-bali.php" class="poster-card" style="background-image: url('https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&q=80&w=600');"><div class="poster-name">Eksotika Bali</div></a><a href="paket-malang.php" class="poster-card" style="background-image: url('https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?auto=format&fit=crop&q=80&w=600');"><div class="poster-name">Pesona Malang & Bromo</div></a><a href="paket-jakarta.php" class="poster-card" style="background-image: url('https://images.unsplash.com/photo-1555899434-9461158aa79c?auto=format&fit=crop&q=80&w=600');"><div class="poster-name">Metropolitan Jakarta</div></a><a href="paket-bandung.php" class="poster-card" style="background-image: url('https://images.unsplash.com/photo-1584646098378-0874589d76b1?auto=format&fit=crop&q=80&w=600');"><div class="poster-name">Dataran Tinggi Bandung</div></a><a href="paket-yogyakarta.php" class="poster-card" style="background-image: url('https://images.unsplash.com/photo-1584551246679-0daf3d275d0f?auto=format&fit=crop&q=80&w=600');"><div class="poster-name">Klasik Yogyakarta</div></a><a href="paket-bali.php" class="poster-card" style="background-image: url('https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&q=80&w=600');"><div class="poster-name">Eksotika Bali</div></a><a href="paket-malang.php" class="poster-card" style="background-image: url('https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?auto=format&fit=crop&q=80&w=600');"><div class="poster-name">Pesona Malang & Bromo</div></a><a href="paket-jakarta.php" class="poster-card" style="background-image: url('https://images.unsplash.com/photo-1555899434-9461158aa79c?auto=format&fit=crop&q=80&w=600');"><div class="poster-name">Metropolitan Jakarta</div></a>
            </div>
        </div>
    </div>

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

        let currentGalleryPage = 1;
        const galleryItems = document.querySelectorAll('.gallery-item');

        function getItemsPerPage() {
            return window.innerWidth <= 768 ? 1 : 4;
        }

        function renderGalleryPage() {
            const itemsPerPage = getItemsPerPage();
            const totalGalleryPages = Math.ceil(galleryItems.length / itemsPerPage);

            if (currentGalleryPage > totalGalleryPages) {
                currentGalleryPage = totalGalleryPages > 0 ? totalGalleryPages : 1;
            }

            const startIndex = (currentGalleryPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;

            galleryItems.forEach((item, index) => {
                if (index >= startIndex && index < endIndex) {
                    item.classList.add('visible');
                } else {
                    item.classList.remove('visible');
                }
            });

            document.getElementById('galleryPageIndicator').innerText = `Halaman ${currentGalleryPage} dari ${totalGalleryPages}`;
            document.getElementById('prevGalleryBtn').disabled = currentGalleryPage === 1;
            document.getElementById('nextGalleryBtn').disabled = currentGalleryPage === totalGalleryPages;
        }

        function changeGalleryPage(direction) {
            const itemsPerPage = getItemsPerPage();
            const totalGalleryPages = Math.ceil(galleryItems.length / itemsPerPage);

            currentGalleryPage += direction;
            if (currentGalleryPage < 1) currentGalleryPage = 1;
            if (currentGalleryPage > totalGalleryPages) currentGalleryPage = totalGalleryPages;
            renderGalleryPage();
        }

        window.addEventListener('resize', function() {
            renderGalleryPage();
        });

        document.addEventListener("DOMContentLoaded", function() {
            renderGalleryPage();
        });

        function openZoomModal(imgSrc) {
            const modal = document.getElementById('zoomModal');
            const modalImg = document.getElementById('zoomModalImg');
            modalImg.src = imgSrc;
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
            document.body.style.overflow = 'hidden';
        }

        function closeZoomModal() {
            const modal = document.getElementById('zoomModal');
            modal.classList.remove('active');
            setTimeout(() => modal.style.display = 'none', 300);
            document.body.style.overflow = 'auto';
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
                closeZoomModal();
            }
        });

        function prosesBooking(event) {
            event.preventDefault(); 
            
            const nama = document.getElementById('nama').value;
            const jumlah = document.getElementById('jumlah').value;
            const tanggal = document.getElementById('tanggal').value;
            const paket = document.getElementById('paket').value; 

            const templatePesan = `Halo Zenith Tour & Travel,\n\nSaya tertarik dan ingin menanyakan detail pemesanan tur berikut:\n*Nama Lengkap:* ${nama}\n*Paket Destinasi:* ${paket}\n*Jumlah Peserta:* ${jumlah} Orang\n*Rencana Tanggal:* ${tanggal}\n\nMohon informasi ketersediaan dan langkah selanjutnya. Terima kasih!`;

            const pesanValidURL = encodeURIComponent(templatePesan);
            const nomorAdmin = "62895333841200"; 
            
            window.open(`https://wa.me/${nomorAdmin}?text=${pesanValidURL}`, '_blank');
        }

        const isLoggedInWeb = <?= is_logged_in() ? 'true' : 'false' ?>;
        function pesanViaWeb(event) {
            event.preventDefault();
            const jumlah = document.getElementById('jumlah').value || 2;
            const paket = document.getElementById('paket').value;
            if (!isLoggedInWeb) {
                window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname.split('/').pop());
                return;
            }
            window.location.href = 'payment-confirm.php?destinasi=' + encodeURIComponent(paket) + '&jumlah=' + encodeURIComponent(jumlah);
        }
    </script>
</body>
</html>
