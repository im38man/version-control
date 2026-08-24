<?php require_once __DIR__ . '/includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wawasan & Cerita Kopi - Nusantara Beans</title>
    
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

        /* ================= HALAMAN BLOG CONTAINER ================= */
        .search-page-container {
            padding: 120px 5% 60px;
            max-width: 1300px;
            margin: 0 auto;
            min-height: 80vh;
        }
        
        /* Tombol Kembali ke Beranda */
        .back-to-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--color-coffee);
            margin-bottom: 20px;
            transition: color 0.3s;
        }
        .back-to-home:hover {
            color: var(--color-gold);
        }

        .search-page-title {
            font-family: var(--font-heading);
            font-size: clamp(24px, 4vw, 32px);
            color: var(--color-coffee);
            margin-bottom: 8px;
        }
        .search-page-title span { color: var(--color-gold); }
        .search-meta {
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
        }

        /* Grid Hasil Blog */
        .search-results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        /* Blog Card Style (Disesuaikan dengan struktur card indeks) */
        .blog-card {
            background: var(--color-white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            text-align: left;
            transition: transform 0.3s ease;
            border: 1px solid rgba(197, 160, 89, 0.2);
            display: flex;
            flex-direction: column;
        }
        .blog-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }

        .blog-img-wrapper {
            position: relative;
            width: 100%;
            height: 180px;
            overflow: hidden;
        }
        .blog-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .blog-card:hover .blog-img { transform: scale(1.05); }
        .blog-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: var(--color-gold);
            color: var(--color-white);
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 500;
            border-radius: 4px;
            z-index: 2;
        }
        .blog-info {
            padding: 18px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .blog-date {
            font-size: 11px;
            color: #888;
            margin-bottom: 6px;
        }
        .blog-title {
            font-family: var(--font-heading);
            font-size: 18px;
            color: var(--color-coffee);
            margin-bottom: 8px;
            line-height: 1.3;
        }
        .blog-desc {
            font-size: 13px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
            flex-grow: 1;
        }
        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            font-family: var(--font-body);
            font-weight: 500;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--color-gold);
            color: var(--color-coffee);
        }
        .btn-outline:hover { background: var(--color-gold); color: var(--color-white); }

        /* ================= PAGINATION STYLES (Sama Persis dengan Search) ================= */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 40px;
        }
        .page-btn {
            background: var(--color-white);
            border: 1px solid rgba(197, 160, 89, 0.4);
            color: var(--color-coffee);
            padding: 8px 14px;
            border-radius: 4px;
            font-family: var(--font-body);
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .page-btn:hover:not(:disabled) {
            background: var(--color-gold);
            color: var(--color-white);
            border-color: var(--color-gold);
        }
        .page-btn.active {
            background: var(--color-coffee);
            color: var(--color-gold);
            border-color: var(--color-coffee);
            font-weight: 600;
        }
        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* ================= FOOTER ================= */
        .site-footer {
            background: #1A120D;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            padding: 50px 5% 20px;
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
        }
        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .footer-bottom span { color: var(--color-gold); }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>
    <!-- KONTEN HALAMAN BLOG -->
    <main class="search-page-container">
        <!-- Kembali ke Beranda -->
        <a href="index.php" class="back-to-home">
            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
        </a>

        <h1 class="search-page-title">Wawasan <span>Cerita Kopi</span></h1>
        <p class="search-meta" id="searchMeta">Menampilkan seluruh artikel dan wawasan menarik seputar kopi Nusantara.</p>

        <!-- Grid Artikel Blog -->
        <div class="search-results-grid" id="blogResultsGrid">
            <!-- Data blog akan dimuat otomatis via JavaScript -->
        </div>

        <!-- Container Pagination -->
        <div class="pagination-container" id="paginationContainer"></div>
    </main>

    <!-- FOOTER -->
<?php include 'includes/footer.php'; ?>
    <!-- ================= JAVASCRIPT ================= -->
    <script>
        // Toggle Burger Menu
        const burgerBtn = document.getElementById('burgerBtn');
        const socialDropdown = document.getElementById('socialDropdown');
        
        burgerBtn.addEventListener('click', (e) => {
            socialDropdown.classList.toggle('active');
            e.stopPropagation();
        });

        // Toggle Search Bar
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

        // === DATA DUMMY ARTIKEL BLOG (Total 12 Artikel) ===
        const blogData = [
            {
                title: "Mengenal Metode Seduh Manual Brew V60",
                date: "12 Juni 2026",
                desc: "Pelajari bagaimana teknik tuang dan suhu air dapat memengaruhi kejernihan rasa serta keasaman kopi Anda di rumah.",
                img: "https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80",
                badge: "Edukasi",
                link: "blog-wawasan-cerita-kopi-mengenal-metode-seduh-manual-brew-v60.php"
            },
            {
                title: "Perjalanan Biji Kopi dari Gayo ke Cangkir Anda",
                date: "08 Juni 2026",
                desc: "Mengintip dedikasi petani dataran tinggi Aceh Tengah dalam merawat pohon kopi hingga proses pascapanen terbaik.",
                img: "https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80",
                badge: "Petani",
                link: "#"
            },
            {
                title: "Cara Menyimpan Biji Kopi agar Aroma Tetap Awet",
                date: "01 Juni 2026",
                desc: "Jangan salah langkah! Ketahui wadah kedap udara dan suhu ideal yang tepat agar kesegaran biji kopi bertahan lama.",
                img: "https://images.unsplash.com/photo-1507133750043-4a8f6beae2f7?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80",
                badge: "Tips",
                link: "#"
            },
            {
                title: "Perbedaan Light, Medium, dan Dark Roast",
                date: "25 Mei 2026",
                desc: "Kenali karakteristik profil tingkat pemanggangan dan temukan mana tingkat kematangan yang paling cocok dengan selera lidah Anda.",
                img: "https://images.unsplash.com/photo-1511920170033-f8396924c348?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80",
                badge: "Roasting",
                link: "#"
            },
            {
                title: "Membangun Sudut Kopi Minimalis di Rumah",
                date: "18 Mei 2026",
                desc: "Ide dan inspirasi penataan sudut kopi estetik di rumah agar pengalaman ngopi Anda terasa seperti di cafe profesional.",
                img: "https://images.unsplash.com/photo-1442512595331-e89e73853f31?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80",
                badge: "Inspirasi",
                link: "#"
            },
            {
                title: "Sejarah Panjang Kejayaan Kopi Nusantara",
                date: "10 Mei 2026",
                desc: "Menelusuri jejak masuknya tanaman kopi ke tanah air hingga menjadi salah satu komoditas ekspor terbaik di dunia.",
                img: "https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80",
                badge: "Sejarah",
                link: "#"
            },
            {
                title: "Mengenal Rasio Ekstraksi Espresso yang Sempurna",
                date: "02 Mei 2026",
                desc: "Panduan praktis bagi barista rumahan untuk menghasilkan shot espresso dengan crema tebal dan rasa seimbang.",
                img: "https://images.unsplash.com/photo-1517256064527-09c73fc73e38?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80",
                badge: "Edukasi",
                link: "#"
            },
            {
                title: "Manfaat Minum Kopi Hitam Tanpa Gula",
                date: "25 April 2026",
                desc: "Ketahui berbagai kebaikan kesehatan mulai dari peningkatan metabolisme hingga antioksidan tinggi dalam secangkir kopi hitam.",
                img: "https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80",
                badge: "Kesehatan",
                link: "#"
            },
            {
                title: "Pengaruh Ukuran Gilingan terhadap Rasa Seduhan",
                date: "15 April 2026",
                desc: "Dari halus seperti tepung hingga kasar seperti garam laut, ketahui ukuran gilingan yang tepat untuk alat seduh Anda.",
                img: "https://images.unsplash.com/photo-1541167760496-1628856ab772?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80",
                badge: "Tips",
                link: "#"
            },
            {
                title: "Misteri Kelezatan Kopi Luwak Liar Sumatera",
                date: "05 April 2026",
                desc: "Mengapa kopi luwak liar asal Sumatera memiliki nilai eksklusif dan cita rasa halus yang dicari kolektor kopi dunia?",
                img: "https://images.unsplash.com/photo-1497935586351-b67a49e012bf?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80",
                badge: "Eksplorasi",
                link: "#"
            },
            {
                title: "Resep Es Kopi Susu Gula Aren ala Cafe",
                date: "28 Maret 2026",
                desc: "Cara mudah meracik minuman es kopi susu kekinian yang creamy, manis, dan pas di lidah menggunakan bahan rumahan.",
                img: "https://images.unsplash.com/photo-1534482421326-d18b53db7df2?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80",
                badge: "Kreatif",
                link: "#"
            },
            {
                title: "Sensasi Kesegaran Cold Brew untuk Hari Panas",
                date: "14 Maret 2026",
                desc: "Teknik rendam dingin berjam-jam yang menghasilkan kopi rendah asam dengan rasa manis tersembunyi yang menyegarkan.",
                img: "https://images.unsplash.com/photo-1511920170033-f8396924c348?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80",
                badge: "Inovasi",
                link: "#"
            }
        ];

        const blogResultsGrid = document.getElementById('blogResultsGrid');
        const paginationContainer = document.getElementById('paginationContainer');

        const itemsPerPage = 6;
        let currentPage = 1;

        function displayBlogPage(page) {
            currentPage = page;
            const start = (page - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const paginatedItems = blogData.slice(start, end);

            blogResultsGrid.innerHTML = paginatedItems.map(item => `
                <div class="blog-card">
                    <div class="blog-img-wrapper">
                        <span class="blog-badge">${item.badge}</span>
                        <img src="${item.img}" alt="${item.title}" class="blog-img">
                    </div>
                    <div class="blog-info">
                        <span class="blog-date"><i class="far fa-calendar-alt"></i> ${item.date}</span>
                        <h3 class="blog-title">${item.title}</h3>
                        <p class="blog-desc">${item.desc}</p>
                        <a href="${item.link}" class="btn btn-outline"><i class="fas fa-external-link-alt"></i> Baca Selengkapnya</a>
                    </div>
                </div>
            `).join('');

            renderPaginationControls();
            window.scrollTo({ top: 100, behavior: 'smooth' });
        }

        function renderPaginationControls() {
            const totalPages = Math.ceil(blogData.length / itemsPerPage);
            
            if (totalPages <= 1) {
                paginationContainer.innerHTML = '';
                return;
            }

            let paginationHTML = '';

            // Tombol Previous
            paginationHTML += `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})"><i class="fas fa-chevron-left"></i></button>`;

            // Nomor Halaman
            for (let i = 1; i <= totalPages; i++) {
                paginationHTML += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
            }

            // Tombol Next
            paginationHTML += `<button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="changePage(${currentPage + 1})"><i class="fas fa-chevron-right"></i></button>`;

            paginationContainer.innerHTML = paginationHTML;
        }

        window.changePage = function(page) {
            displayBlogPage(page);
        }

        // Inisialisasi tampilan halaman pertama saat pertama kali dimuat
        displayBlogPage(1);
    </script>
</body>
</html>