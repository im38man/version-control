<?php require_once __DIR__ . '/includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perjalanan Biji Kopi dari Gayo ke Cangkir Anda - Nusantara Beans</title>
    
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
        }
        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }

        /* ================= NAVBAR ================= */
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
        .navbar-icons i:hover { color: var(--color-white); }

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

        /* ================= KONTEN HALAMAN ARTIKEL / BLOG ================= */
        .article-container {
            max-width: 850px;
            margin: 120px auto 60px auto; 
            padding: 0 5%;
        }

        .article-breadcrumb {
            font-size: 13px;
            color: #777;
            margin-bottom: 20px;
        }
        .article-breadcrumb a {
            color: var(--color-gold);
        }
        .article-breadcrumb a:hover {
            text-decoration: underline;
        }

        .article-badge {
            display: inline-block;
            background: var(--color-gold);
            color: var(--color-white);
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 4px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .article-title {
            font-family: var(--font-heading);
            font-size: clamp(28px, 4.5vw, 40px);
            color: var(--color-coffee);
            line-height: 1.25;
            margin-bottom: 15px;
        }

        .article-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 13px;
            color: #666;
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(197, 160, 89, 0.3);
            padding-bottom: 20px;
        }
        .article-meta span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .article-meta i {
            color: var(--color-gold);
        }

        .article-featured-img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 35px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: 1px solid rgba(197, 160, 89, 0.2);
        }

        .article-body {
            font-size: 15px;
            line-height: 1.8;
            color: #444;
        }
        .article-body h2 {
            font-family: var(--font-heading);
            color: var(--color-coffee);
            font-size: 24px;
            margin-top: 35px;
            margin-bottom: 15px;
        }
        .article-body p {
            margin-bottom: 20px;
        }
        .article-body ul, .article-body ol {
            margin-left: 20px;
            margin-bottom: 20px;
        }
        .article-body li {
            margin-bottom: 8px;
        }
        .article-body blockquote {
            border-left: 4px solid var(--color-gold);
            padding: 15px 20px;
            background: var(--color-white);
            font-style: italic;
            color: var(--color-coffee);
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }

        /* Tombol Kembali / Navigasi Bawah Artikel */
        .article-footer-nav {
            margin-top: 50px;
            padding-top: 25px;
            border-top: 1px solid rgba(197, 160, 89, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: transparent;
            border: 2px solid var(--color-gold);
            color: var(--color-coffee);
            font-weight: 600;
            font-size: 13px;
            border-radius: 30px;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            background: var(--color-gold);
            color: var(--color-white);
        }

        /* ================= FOOTER ================= */
        .site-footer {
            background: #1A120D;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            padding: 50px 5% 20px;
            margin-top: 60px;
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
            background: var(--color-gold);
            color: var(--color-white);
            cursor: pointer;
            height: 100%;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .footer-bottom span { color: var(--color-gold); }

        @media (max-width: 768px) {
            .article-featured-img {
                height: 250px;
            }
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>
    <!-- KONTEN UTAMA ARTIKEL BLOG -->
    <main class="article-container">
        <div class="article-breadcrumb">
            <a href="index.php">Beranda</a> &gt; <a href="blogspot.php">Wawasan Cerita Kopi</a> &gt; <span>Perjalanan Biji Kopi dari Gayo ke Cangkir Anda</span>
        </div>

        <span class="article-badge">Petani</span>
        <h1 class="article-title">Perjalanan Biji Kopi dari Gayo ke Cangkir Anda</h1>
        
        <div class="article-meta">
            <span><i class="far fa-calendar-alt"></i> 08 Juni 2026</span>
            <span><i class="far fa-user"></i> Tim Nusantara Beans</span>
            <span><i class="far fa-clock"></i> 5 Menit Baca</span>
        </div>

        <img src="https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" alt="Petani Gayo" class="article-featured-img">

        <div class="article-body">
            <p>Di balik secangkir kopi hitam pekat yang Anda nikmati setiap pagi di meja kerja, tersimpan sebuah perjalanan panjang yang penuh dedikasi. Perjalanan tersebut bermula dari tanah vulkanik yang subur di dataran tinggi Gayo, Aceh Tengah, hingga akhirnya diseduh dengan sempurna di cangkir Anda.</p>

            <h2>1. Perawatan di Tanah Vulkanik Gayo</h2>
            <p>Kualitas kopi terbaik tidak lahir begitu saja. Di pegunungan Aceh Tengah dengan ketinggian 1.200 hingga 1.500 mdpl, para petani lokal merawat pohon-pohon kopi Arabika dengan penuh ketelatenan. Iklim sejuk, curah hujan yang stabil, serta naungan pohon pelindung membuat biji kopi Gayo memiliki karakteristik cita rasa yang kaya, rendah tingkat keasamannya, serta memiliki body yang tebal.</p>

            <h2>2. Panen Selektif dan Pascapanen</h2>
            <p>Memasuki musim panen, para petani memetik buah kopi (ceri kopi) yang benar-benar berwarna merah matang secara manual dengan tangan (hand-picking). Setelah itu, ceri kopi melalui proses pascapanen yang beragam—mulai dari proses *Full Wash*, *Natural*, hingga *Honey Process*—yang masing-masing memberikan sentuhan notes rasa unik tersendiri.</p>

            <blockquote>
                "Dedikasi petani lokal dalam menjaga kualitas pascapanen adalah fondasi utama yang menentukan keunggulan cita rasa kopi Nusantara di kancah global."
            </blockquote>

            <h2>3. Proses Roasting oleh Artisan Profesional</h2>
            <p>Setelah melewati tahap pengeringan dan penyortiran ketat menjadi *green beans* (biji mentah), biji kopi dikirimkan ke roastery kami. Di tangan para Artisan Roaster profesional, biji kopi dipanggang menggunakan profil suhu dan waktu yang presisi untuk mengeluarkan aroma serta karakter rasa terbaiknya.</p>

            <h2>4. Tersaji Sempurna di Cangkir Anda</h2>
            <p>Tahap akhir dari perjalanan panjang ini berada di tangan Anda. Melalui proses penggilingan yang tepat dan teknik penyeduhan yang penuh penghayatan, biji kopi Gayo pilihan siap mempersembahkan kehangatan, aroma khas rempah, serta kemewahan rasa Nusantara di setiap tegukannya.</p>
        </div>

        <div class="article-footer-nav">
            <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
        </div>
    </main>

    <!-- FOOTER -->
<?php include 'includes/footer.php'; ?>
    <!-- ================= JAVASCRIPT NAVBAR & SEARCH ================= -->
    <script>
        const burgerBtn = document.getElementById('burgerBtn');
        const socialDropdown = document.getElementById('socialDropdown');
        
        burgerBtn.addEventListener('click', (e) => {
            socialDropdown.classList.toggle('active');
            e.stopPropagation();
        });

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
    </script>
</body>
</html>