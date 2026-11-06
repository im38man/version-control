<?php require_once __DIR__ . "/includes/session.php"; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan ke Admin - Zenith Tour & Travel</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome untuk Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        /* Reset & Base Styles */
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
        }
        a {
            text-decoration: none;
            color: inherit;
        }

        /* Navbar */
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
        .nav-socials a {
            color: #1a2f27;
            font-size: 18px;
            transition: color 0.3s, transform 0.3s;
        }
        .nav-socials a:hover {
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

        /* Header Section */
        .page-header {
            text-align: center;
            padding: 60px 8% 30px 8%;
            background: linear-gradient(rgba(26, 47, 39, 0.03), rgba(26, 47, 39, 0.00));
        }
        .section-subtitle {
            color: #c5a880;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            color: #1a2f27;
            margin-bottom: 15px;
        }
        .section-desc {
            max-width: 650px;
            margin: 0 auto;
            color: #666;
            font-weight: 300;
            font-size: 15px;
        }

        /* Layout Container (satu kolom, fokus ke Pesan) */
        .main-container {
            max-width: 700px;
            margin: 0 auto;
            padding: 0 4% 80px 4%;
        }

        /* Kotak Pesan Styles */
        .pesan-section {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border: 1px solid #f3eee7;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 600px;
        }
        .pesan-header {
            background-color: #1a2f27;
            color: #fff;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 3px solid #c5a880;
        }
        .admin-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #c5a880;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 600;
            border: 2px solid #fff;
            color: #1a2f27;
        }
        .admin-status {
            display: flex;
            flex-direction: column;
        }
        .admin-name {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .status-tag {
            font-size: 11px;
            color: rgba(255,255,255,0.7);
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #4caf50;
            display: inline-block;
            animation: pulse-dot 1.5s infinite;
        }
        @keyframes pulse-dot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 5px rgba(76, 175, 80, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(76, 175, 80, 0); }
        }

        .pesan-messages {
            flex-grow: 1;
            padding: 25px;
            overflow-y: auto;
            background-color: #faf9f6;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .message-bubble {
            max-width: 80%;
            padding: 12px 18px;
            border-radius: 12px;
            font-size: 14px;
            line-height: 1.5;
            position: relative;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .message-admin {
            background-color: #ffffff;
            color: #333;
            align-self: flex-start;
            border-bottom-left-radius: 2px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
            border: 1px solid #f3eee7;
        }
        .message-user {
            background-color: #1a2f27;
            color: #ffffff;
            align-self: flex-end;
            border-bottom-right-radius: 2px;
        }
        .message-time {
            font-size: 10px;
            color: #999;
            margin-top: 5px;
            text-align: right;
            display: block;
        }
        .message-user .message-time {
            color: rgba(255,255,255,0.6);
        }

        .typing-indicator {
            align-self: flex-start;
            background-color: #ffffff;
            border: 1px solid #f3eee7;
            padding: 12px 18px;
            border-radius: 12px;
            border-bottom-left-radius: 2px;
            display: none;
            gap: 5px;
            align-items: center;
        }
        .typing-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: #c5a880;
            animation: bounce-dot 1.4s infinite ease-in-out both;
        }
        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }
        .typing-dot:nth-child(3) { animation-delay: 0s; }
        @keyframes bounce-dot {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1.0); }
        }

        .pesan-input-area {
            padding: 15px 20px;
            background: #ffffff;
            border-top: 1px solid #f3eee7;
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .pesan-input {
            flex-grow: 1;
            padding: 12px 18px;
            border: 1px solid #e5e0d8;
            border-radius: 30px;
            outline: none;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s;
            background-color: #fcfbf7;
        }
        .pesan-input:focus {
            border-color: #c5a880;
            background-color: #fff;
            box-shadow: 0 0 8px rgba(197, 168, 128, 0.15);
        }
        .btn-send {
            background-color: #1a2f27;
            color: #fff;
            border: none;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            font-size: 16px;
        }
        .btn-send:hover {
            background-color: #c5a880;
            transform: scale(1.05);
        }

        /* Footer */
        footer {
            background-color: #111e19;
            color: rgba(255,255,255,0.5);
            padding: 40px 8% 25px 8%;
            font-size: 13px;
        }
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 30px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 20px;
        }
        .footer-logo {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 700;
            color: #fff;
        }
        .footer-socials {
            display: flex;
            gap: 20px;
        }
        .footer-socials a {
            color: rgba(255,255,255,0.6);
            font-size: 20px;
            transition: color 0.3s;
        }
        .footer-socials a:hover {
            color: #c5a880;
        }
        .copyright {
            text-align: center;
            font-size: 12px;
        }
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

    <!-- Header Galeri -->
    <header class="page-header">
        <p class="section-subtitle">Customer Care</p>
        <h1 class="section-title">Pesan ke Admin</h1>
        <p class="section-desc">Diskusikan rencana perjalanan impian Anda bersama tim admin kami secara langsung.</p>
    </header>

    <!-- Main Content Area split -->
    <main class="main-container">

        <!-- Sisi Kiri: Kotak Pesan Admin -->
        <section class="pesan-section">
            <div class="pesan-header">
                <div class="admin-avatar">A</div>
                <div class="admin-status">
                    <span class="admin-name">Admin Zenith</span>
                    <span class="status-tag"><span class="status-dot"></span> Pesan Langsung dengan Tim Kami</span>
                </div>
            </div>

            <?php if (!is_logged_in()): ?>
            <div class="pesan-messages" id="pesanMessages" style="display:flex;align-items:center;justify-content:center;">
                <div style="text-align:center;padding:30px;">
                    <i class="fa-solid fa-lock" style="font-size:32px;color:#c5a880;margin-bottom:15px;"></i>
                    <p style="font-size:14px;color:#555;margin-bottom:15px;">
                        Anda perlu <strong>login</strong> untuk mengirim pesan dan melihat riwayat pesan dengan admin.<br>
                        Tanpa login, Anda hanya bisa melihat tampilan halaman ini.
                    </p>
                    <a href="login.php?redirect=pesan.php" style="display:inline-block;background:#1a2f27;color:#fff;padding:10px 22px;border-radius:20px;font-size:13px;margin-right:8px;">Masuk</a>
                    <a href="register.php" style="display:inline-block;background:#c5a880;color:#fff;padding:10px 22px;border-radius:20px;font-size:13px;">Daftar</a>
                </div>
            </div>
            <div class="pesan-input-area">
                <input type="text" class="pesan-input" placeholder="Login untuk mulai kirim pesan..." disabled>
                <button class="btn-send" disabled style="opacity:0.5;cursor:not-allowed;"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
            <?php else: ?>
            <div class="pesan-messages" id="pesanMessages">
                <div class="message-bubble message-admin">
                    Halo <?= h($_SESSION['user_name']) ?>! Selamat datang di Zenith Tour & Travel. 😊
                    <br><br>Ada yang bisa kami bantu terkait paket wisata, destinasi, atau pembayaran Anda? Silakan tulis pertanyaan Anda di bawah, tim kami akan segera membalas.
                    <span class="message-time" id="openTime">09:00</span>
                </div>
            </div>

            <div class="typing-indicator" id="typingIndicator" style="display:none;">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <span style="font-size: 11px; color: #999; margin-left: 5px;">Menunggu balasan admin...</span>
            </div>

            <div class="pesan-input-area">
                <input type="text" class="pesan-input" id="pesanInput" placeholder="Tulis pesan untuk admin..." onkeydown="handlePesanEnter(event)">
                <button class="btn-send" onclick="sendMessage()"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
            <?php endif; ?>
        </section>

    </main>

    <!-- Footer -->
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

    <!-- JavaScript System -->
    <script>
        // 1. Mobile Menu Toggle
        function toggleMenu() {
            const navContainer = document.getElementById('navContainer');
            navContainer.classList.toggle('active');
        }

        // 2. Set Up Waktu Pesan Pembuka
        document.getElementById('openTime').textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

        // 3. Sistem Pesan Real ke Admin (khusus user yang sudah login)
        const isLoggedIn = <?= is_logged_in() ? 'true' : 'false' ?>;
        let lastMessageId = 0;
        let pesanPollTimer = null;

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function appendMessage(text, sender, time) {
            const pesanMessages = document.getElementById('pesanMessages');
            const bubble = document.createElement('div');
            bubble.className = `message-bubble message-${sender}`;
            const formattedText = escapeHtml(text).replace(/\n/g, '<br>');
            bubble.innerHTML = `${formattedText} <span class="message-time">${time}</span>`;
            pesanMessages.appendChild(bubble);
            pesanMessages.scrollTop = pesanMessages.scrollHeight;
        }

        async function loadMessages(initial) {
            try {
                const res = await fetch('pesan-fetch.php?after=' + lastMessageId);
                const data = await res.json();
                if (!data.success) return;
                data.messages.forEach(m => {
                    appendMessage(m.message, m.sender === 'admin' ? 'admin' : 'user', m.time);
                    lastMessageId = m.id;
                });
            } catch (e) { /* diamkan error polling */ }
        }

        async function sendMessage() {
            const pesanInput = document.getElementById('pesanInput');
            const userText = pesanInput.value.trim();
            if (!userText) return;
            pesanInput.value = "";

            try {
                const res = await fetch('pesan-send.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'message=' + encodeURIComponent(userText)
                });
                const data = await res.json();
                if (data.success) {
                    appendMessage(userText, 'user', data.time);
                    lastMessageId = data.id;
                }
            } catch (e) { /* diamkan error kirim */ }
        }

        function handlePesanEnter(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        if (isLoggedIn) {
            loadMessages(true);
            pesanPollTimer = setInterval(() => loadMessages(false), 4000);
        }

        // 4. Fungsi Handler untuk Modal Search (Persis Index)
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