<?php require_once __DIR__ . '/includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian - Nusantara Beans</title>
    
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

        /* ================= HALAMAN SEARCH CONTENT ================= */
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

        /* Grid Hasil Pencarian */
        .search-results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .no-results {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px;
            font-size: 16px;
            color: #666;
            background: var(--color-white);
            border-radius: 8px;
            border: 1px solid rgba(197, 160, 89, 0.2);
        }

        /* Card Style */
        .product-card {
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
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        
        .product-img-wrapper {
            position: relative;
            width: 100%;
            height: 200px;
            background-color: var(--color-white);
            overflow: hidden;
        }
        .product-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 15px;
            transition: transform 0.3s ease;
        }
        .product-img-wrapper:hover .product-img { transform: scale(1.05); }

        .product-badge-top {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #d9534f;
            color: var(--color-white);
            font-size: 10px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            z-index: 3;
        }
        .product-label-corner {
            position: absolute;
            top: 22px;
            right: -35px;
            background: var(--color-coffee);
            color: var(--color-gold);
            font-size: 9px;
            font-weight: 600;
            width: 130px;
            text-align: center;
            padding: 4px 0;
            text-transform: uppercase;
            transform: rotate(45deg);
            z-index: 2;
        }

        .product-info { padding: 18px; display: flex; flex-direction: column; flex-grow: 1; }
        .product-title {
            font-family: var(--font-heading);
            font-size: 19px;
            color: var(--color-coffee);
            margin-bottom: 5px;
        }
        .product-price {
            font-size: 15px;
            font-weight: 600;
            color: var(--color-gold);
            margin-bottom: 10px;
        }
        .product-bio {
            font-size: 13px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
            flex-grow: 1;
        }
        .product-actions { 
            display: flex; 
            gap: 8px; 
            margin-top: auto; 
        }
        .btn {
            flex: 1;
            padding: 8px 6px;
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
        .btn-solid {
            background: var(--color-gold);
            color: var(--color-white);
        }
        .btn-solid:hover { background: var(--color-coffee); }

        /* ================= PAGINATION STYLES ================= */
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
    <!-- KONTEN HASIL PENCARIAN -->
    <main class="search-page-container">
        <!-- Kembali ke Beranda -->
        <a href="index.php" class="back-to-home">
            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
        </a>

        <h1 class="search-page-title">Hasil Pencarian: <span id="queryDisplay">...</span></h1>
        <p class="search-meta" id="searchMeta">Menampilkan hasil yang sesuai dengan kueri Anda.</p>

        <div class="search-results-grid" id="searchResultsGrid">
            <!-- Data hasil pencarian akan dimasukkan melalui JavaScript -->
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

        // === DATA DUMMY DENGAN TAMBAHAN PROPERTI 'tags' ===
        const dummyData = [
            {
                title: "Arabica Premium",
                price: "Rp 120.000 /kg",
                bio: "Kopi Arabica Gayo low acidity & full body. Memiliki sentuhan khas rempah, cokelat pekat, dan hint karamel.",
                img: "assets/img/arabika-1536x1536.webp",
                badge: "Best Seller",
                corner: "Single Origin",
                tags: "paket termurah dan terjangkau ramah dikantong"
            },
            {
                title: "Arabica Full Wash",
                price: "Rp 135.000 /kg",
                bio: "Proses Full Wash menghasilkan cita rasa kopi yang sangat bersih dengan tingkat keasaman cerah dan segar.",
                img: "assets/img/fullwash-1122×1402.webp",
                badge: "",
                corner: "Full Wash",
                tags: "bersih asam cerah segar"
            },
            {
                title: "Arabica Natural",
                price: "Rp 140.000 /kg",
                bio: "Penjemuran utuh bersama ceri memunculkan notes buah-buahan tropis serta rasa manis alami yang sangat kuat.",
                img: "assets/img/natural-1122x1402.webp",
                badge: "Favorit",
                corner: "Natural",
                tags: "fruity tropis manis ceri"
            },
            {
                title: "Yellow Honey",
                price: "Rp 145.000 /kg",
                bio: "Keseimbangan sempurna antara keasaman dan kemanisan, dilengkapi aroma floral dengan sentuhan madu ringan.",
                img: "assets/img/yellowhoney-1149x1369.webp",
                badge: "",
                corner: "Honey Process",
                tags: "floral madu seimbang"
            },
            {
                title: "Red Honey",
                price: "Rp 150.000 /kg",
                bio: "Tingkat mucilage yang lebih tebal menghasilkan rasa yang jauh lebih manis dari Yellow Honey dengan karakter sirup.",
                img: "assets/img/redhoney-1149x1369.webp",
                badge: "",
                corner: "Red Honey",
                tags: "sirup mucilage tebal"
            },
            {
                title: "Black Honey",
                price: "Rp 160.000 /kg",
                bio: "Menghasilkan profil kopi kental dengan tingkat kemanisan tertinggi serta kekayaan rasa cokelat yang sangat pekat.",
                img: "assets/img/blackhoney-1149×1369.webp",
                badge: "Limited",
                corner: "Black Honey",
                tags: "kental cokelat manis tinggi"
            },
            {
                title: "Arabica Wine",
                price: "Rp 185.000 /kg",
                bio: "Difermentasi secara khusus (non-alkohol) untuk memunculkan aroma dan cita rasa anggur yang sangat eksotis.",
                img: "assets/img/wine-1197x1315.webp",
                badge: "Exclusive",
                corner: "Wine Process",
                tags: "fermentasi anggur eksotis non-alkohol"
            },
            {
                title: "GB Gayo Full Wash",
                price: "Rp 95.000 /kg",
                bio: "Biji mentah Gayo Full Wash dengan kadar air ideal, siap masuk mesin roasting untuk menemani kreasi profil Anda.",
                img: "https://images.unsplash.com/photo-1559525839-b184a4d698c7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80",
                badge: "Best Seller",
                corner: "Full Wash",
                tags: "biji mentah green beans roasting aceh"
            },
            {
                title: "GB Gayo Natural",
                price: "Rp 105.000 /kg",
                bio: "Pilihan tepat untuk para roaster yang ingin memunculkan notes fruity dan acidity kompleks dalam racikan kopinya.",
                img: "https://images.unsplash.com/photo-1511920170033-f8396924c348?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80",
                badge: "",
                corner: "Natural",
                tags: "roaster biji mentah green beans fruity"
            },
            {
                title: "GB Gayo Honey",
                price: "Rp 110.000 /kg",
                bio: "Hasil sortasi terbaik tanpa cacat (defect). Menjanjikan rasa sweet dan balance yang pas saat diseduh nanti.",
                img: "https://images.unsplash.com/photo-1611162458324-aae1eb4129a4?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80",
                badge: "",
                corner: "Honey Process",
                tags: "defect sortasi green beans biji mentah"
            },
            {
                title: "GB Luwak Liar",
                price: "Rp 450.000 /kg",
                bio: "Green beans kopi luwak liar bersertifikat. Sudah dibersihkan secara higienis, tinggal roasting untuk kemewahan hakiki.",
                img: "https://images.unsplash.com/photo-1497935586351-b67a49e012bf?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80",
                badge: "Rare",
                corner: "Wild Luwak",
                tags: "luwak liar bersertifikat green beans mahal"
            }
        ];

        // === LOGIKA PENCARIAN & PAGINATION ===
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('q') ? urlParams.get('q').trim() : '';

        document.getElementById('queryDisplay').innerText = searchQuery ? `"${searchQuery}"` : 'Semua Produk';
        
        const resultsGrid = document.getElementById('searchResultsGrid');
        const paginationContainer = document.getElementById('paginationContainer');
        
        const filteredData = dummyData.filter(item => {
            const query = searchQuery.toLowerCase();
            return item.title.toLowerCase().includes(query) || 
                   item.price.toLowerCase().includes(query) || 
                   item.bio.toLowerCase().includes(query) || 
                   item.badge.toLowerCase().includes(query) || 
                   item.corner.toLowerCase().includes(query) || 
                   item.tags.toLowerCase().includes(query);
        });

        document.getElementById('searchMeta').innerText = `Ditemukan ${filteredData.length} hasil yang cocok dengan kata kunci Anda.`;

        // Konfigurasi Pagination (Maksimal 6 item per halaman)
        const itemsPerPage = 6;
        let currentPage = 1;

        function displayPage(page) {
            currentPage = page;
            const start = (page - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const paginatedItems = filteredData.slice(start, end);

            if (filteredData.length === 0) {
                resultsGrid.innerHTML = `<div class="no-results">Maaf, produk atau informasi yang Anda cari tidak ditemukan. Coba gunakan kata kunci lain.</div>`;
                paginationContainer.innerHTML = '';
            } else {
                resultsGrid.innerHTML = paginatedItems.map(item => `
                    <div class="product-card">
                        <div class="product-img-wrapper">
                            ${item.badge ? `<span class="product-badge-top">${item.badge}</span>` : ''}
                            <span class="product-label-corner">${item.corner}</span>
                            <img src="${item.img}" alt="${item.title}" class="product-img">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">${item.title}</h3>
                            <div class="product-price">${item.price}</div>
                            <p class="product-bio">${item.bio}</p>
                            <div class="product-actions">
                                <button class="btn btn-outline"><i class="fas fa-external-link-alt"></i> Detail</button>
                                <button class="btn btn-solid"><i class="fas fa-cart-plus"></i> Checkout</button>
                            </div>
                        </div>
                    </div>
                `).join('');

                renderPaginationControls();
            }

            // Scroll otomatis ke atas bagian hasil pencarian saat ganti halaman
            window.scrollTo({ top: 100, behavior: 'smooth' });
        }

        function renderPaginationControls() {
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            
            // Jika halaman hanya 1 atau kurang, sembunyikan pagination
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
            displayPage(page);
        }

        // Inisialisasi tampilan halaman pertama
        displayPage(1);
    </script>
    <script src="assets/js/cart.js?v=<?php echo @filemtime(__DIR__ . '/assets/js/cart.js') ?: time(); ?>"></script>
</body>
</html>