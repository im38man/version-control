<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT * FROM messages WHERE user_id = ? ORDER BY id ASC');
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll();

// tandai pesan dari admin sudah dibaca begitu halaman dibuka
$pdo->prepare("UPDATE messages SET is_read = 1 WHERE user_id = ? AND sender_role = 'admin' AND is_read = 0")
    ->execute([$user_id]);

$last_id = !empty($messages) ? end($messages)['id'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan dengan Admin - Nusantara Beans</title>
    
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

        /* ================= NAVBAR (Sesuai index.html) ================= */
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

        /* ================= CHAT SECTION CONTAINER (DITENGAHKAN) ================= */
        :root {
            --chat-vvh: 100vh;   /* diisi JS dari visualViewport, akurat walau keyboard/toolbar muncul */
            --chat-offset: 110px; /* total padding atas+bawah wrapper, disesuaikan per breakpoint */
        }

        .chat-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 80px 15px 30px 15px; /* Memberi jarak aman dari navbar fixed di atas */
            width: 100%;
            min-height: 0;
        }

        .chat-main-container {
            width: 100%;
            max-width: 850px;
            background: var(--color-white);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(44, 30, 22, 0.08);
            border: 1px solid rgba(197, 160, 89, 0.25);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            /* Menggunakan tinggi dinamis berbasis layar agar selalu pas di tengah vertikal */
            height: 75vh;
            height: 75dvh;
            max-height: 720px;
            /* Batas aman: tidak pernah melebihi tinggi viewport yang BENAR-BENAR terlihat
               (visualViewport dari JS), supaya tombol kirim tidak kepotong keyboard/toolbar */
            max-height: min(720px, calc(var(--chat-vvh) - var(--chat-offset)));
            min-height: 320px;
        }

        /* Chat Header */
        .chat-header {
            background-color: var(--color-coffee);
            color: var(--color-white);
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid var(--color-gold);
            flex-shrink: 0; 
            z-index: 10;
        }
        .chat-admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .admin-avatar-wrapper {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--color-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-coffee);
            font-size: 16px;
            font-weight: 700;
            border: 2px solid var(--color-white);
            flex-shrink: 0;
        }
        .status-dot {
            position: absolute;
            bottom: 1px;
            right: 1px;
            width: 9px;
            height: 9px;
            background-color: #28a745;
            border-radius: 50%;
            border: 2px solid var(--color-coffee);
        }
        .admin-info h3 {
            font-family: var(--font-heading);
            font-size: 16px;
            color: var(--color-gold);
            margin-bottom: 2px;
        }
        .admin-info span {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.7);
        }
        .chat-header-actions i {
            font-size: 16px;
            color: var(--color-gold);
            cursor: pointer;
            transition: color 0.3s;
        }
        .chat-header-actions i:hover { color: var(--color-white); }

        /* Chat Body / Messages Area */
        .chat-body {
            padding: 20px;
            flex: 1; 
            overflow-y: auto; 
            display: flex;
            flex-direction: column;
            gap: 15px;
            background-color: #FAF8F5;
        }
        .chat-body::-webkit-scrollbar { width: 6px; }
        .chat-body::-webkit-scrollbar-thumb { background-color: #C5A059; border-radius: 10px; }

        .chat-date-divider {
            text-align: center;
            font-size: 11px;
            color: #888;
            margin: 5px 0;
            position: relative;
        }
        .chat-date-divider span {
            background: #EFECE6;
            padding: 4px 12px;
            border-radius: 10px;
        }

        .message {
            max-width: 75%;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 13px;
            line-height: 1.5;
            position: relative;
            animation: fadeInMessage 0.3s ease;
        }
        @keyframes fadeInMessage {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.admin {
            background-color: var(--color-white);
            color: var(--color-text);
            align-self: flex-start;
            border-top-left-radius: 2px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border: 1px solid rgba(197, 160, 89, 0.15);
        }
        .message.user {
            background-color: var(--color-coffee);
            color: var(--color-white);
            align-self: flex-end;
            border-top-right-radius: 2px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .message-time {
            font-size: 10px;
            display: block;
            margin-top: 4px;
            text-align: right;
            opacity: 0.7;
        }
        .message.admin .message-time { color: #888; }
        .message.user .message-time { color: rgba(255,255,255,0.75); }

        .message-location {
            font-size: 10px;
            display: block;
            margin-top: 4px;
            opacity: 0.7;
        }
        .message-location i { margin-right: 3px; }
        .message.admin .message-location { color: #888; }
        .message.user .message-location { color: rgba(255,255,255,0.75); }

        /* Typing Indicator Animation */
        .typing-indicator {
            display: none;
            align-self: flex-start;
            background: var(--color-white);
            padding: 10px 15px;
            border-radius: 12px;
            border-top-left-radius: 2px;
            border: 1px solid rgba(197, 160, 89, 0.15);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .typing-indicator span {
            height: 7px;
            width: 7px;
            float: left;
            margin: 0 2px;
            background-color: var(--color-gold);
            border-radius: 50%;
            display: inline-block;
            animation: bounce 1.3s infinite ease-in-out;
        }
        .typing-indicator span:nth-child(2) { animation-delay: -1.1s; }
        .typing-indicator span:nth-child(3) { animation-delay: -0.9s; }
        @keyframes bounce {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-5px); }
        }

        /* Chat Footer / Input Form */
        .chat-footer {
            padding: 12px 18px;
            background-color: var(--color-white);
            border-top: 1px solid rgba(197, 160, 89, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
            z-index: 10;
        }
        .chat-input-tool {
            background: transparent;
            border: none;
            font-size: 16px;
            color: var(--color-gold);
            cursor: pointer;
            transition: color 0.3s;
        }
        .chat-input-tool:hover { color: var(--color-coffee); }

        .message-image {
            max-width: 200px;
            max-height: 200px;
            width: 100%;
            border-radius: 8px;
            display: block;
            margin-bottom: 4px;
            cursor: pointer;
            object-fit: cover;
        }
        .chat-attach-preview {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 15px;
            background: var(--color-cream);
            border-top: 1px solid rgba(197, 160, 89, 0.2);
            flex-shrink: 0;
            z-index: 10;
        }
        .chat-attach-preview img { width: 35px; height: 35px; object-fit: cover; border-radius: 6px; flex-shrink: 0; }
        .chat-attach-preview span { flex: 1; font-size: 12px; color: #666; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .chat-attach-preview button { background: transparent; border: none; color: #B00020; cursor: pointer; font-size: 13px; padding: 4px; }

        .chat-input {
            flex: 1;
            min-width: 0; /* FIX: tanpa ini <input> tidak mau menyusut di flexbox, mendorong tombol kirim keluar layar di HP */
            padding: 9px 14px;
            border: 1px solid rgba(197, 160, 89, 0.4);
            border-radius: 25px;
            outline: none;
            font-family: var(--font-body);
            font-size: 13px;
            background: var(--color-cream);
            transition: border-color 0.3s;
        }
        .chat-input:focus {
            border-color: var(--color-gold);
            background: var(--color-white);
        }
        .chat-send-btn {
            background-color: var(--color-gold);
            color: var(--color-white);
            border: none;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            flex-shrink: 0;
        }
        .chat-send-btn:hover {
            background-color: var(--color-coffee);
            color: var(--color-gold);
            transform: scale(1.05);
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

        /* ================= RESPONSIVE QUERY KHUSUS MOBILE ================= */
        @media (max-width: 768px) {
            :root { --chat-offset: 80px; }
            .chat-wrapper {
                padding: 65px 10px 15px 10px;
            }
            .chat-main-container {
                height: 80vh;
                height: 80dvh;
                max-height: calc(var(--chat-vvh) - var(--chat-offset));
                min-height: 260px;
            }
            .message {
                max-width: 85%;
            }
        }

        @media (max-width: 480px) {
            :root { --chat-offset: 70px; }
            .chat-wrapper {
                padding: 60px 8px 10px 8px;
            }
            .chat-main-container {
                height: 82vh; /* Memastikan muat di layar HP kecil termasuk saat browser bar muncul */
                height: 82dvh;
                max-height: calc(var(--chat-vvh) - var(--chat-offset));
                min-height: 240px;
            }
            .chat-header { padding: 10px 12px; }
            .admin-avatar-wrapper { width: 34px; height: 34px; font-size: 14px; }
            .admin-info h3 { font-size: 14px; }
            .admin-info span { font-size: 10px; }
            .message {
                max-width: 90%;
                font-size: 12.5px;
                padding: 8px 12px;
            }
            .chat-input {
                font-size: 16px; /* Mencegah auto-zoom di iOS Safari */
                padding: 8px 12px;
            }
            .chat-send-btn { width: 36px; height: 36px; }
            .chat-input-tool { font-size: 15px; }
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

    <!-- CHAT WRAPPER (MEMBUAT POSISI TEPAT DI TENGAH) -->
    <div class="chat-wrapper">
        <div class="chat-main-container">
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="chat-admin-profile">
                    <div class="admin-avatar-wrapper">
                        NB
                        <div class="status-dot"></div>
                    </div>
                    <div class="admin-info">
                        <h3>Admin Nusantara Beans</h3>
                        <span>Online • Siap membantu</span>
                    </div>
                </div>
                <div class="chat-header-actions">
                    <i class="fas fa-ellipsis-v" title="Opsi Lainnya"></i>
                </div>
            </div>

            <!-- Chat Body / Message Log -->
            <div class="chat-body" id="chatBody">
                <div class="chat-date-divider">
                    <span>Hari Ini</span>
                </div>
                <?php if (empty($messages)): ?>
                <div class="message admin">
                    Halo! Selamat datang di Nusantara Beans. Ada yang bisa kami bantu terkait produk biji kopi atau pemesanan Anda hari ini? 😊
                    <span class="message-time">--:--</span>
                </div>
                <?php else: foreach ($messages as $m): ?>
                <div class="message <?php echo $m['sender_role']; ?>">
                    <?php if (!empty($m['image_path'])): ?>
                        <a href="pesan-image.php?path=<?php echo urlencode($m['image_path']); ?>" target="_blank" rel="noopener">
                            <img src="pesan-image.php?path=<?php echo urlencode($m['image_path']); ?>" class="message-image" alt="Foto">
                        </a>
                    <?php endif; ?>
                    <?php if (trim((string) $m['message']) !== ''): ?>
                        <?php echo nl2br(htmlspecialchars($m['message'], ENT_QUOTES, 'UTF-8')); ?>
                    <?php endif; ?>
                    <?php if (!empty($m['sender_location'])): ?>
                        <span class="message-location"><i class="fas fa-location-dot"></i><?php echo htmlspecialchars($m['sender_location']); ?></span>
                    <?php endif; ?>
                    <span class="message-time"><?php echo date('H:i', strtotime($m['created_at'])); ?></span>
                </div>
                <?php endforeach; endif; ?>
                <!-- Typing Indicator Animation Box -->
                <div class="typing-indicator" id="typingIndicator">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>

            <!-- Preview Lampiran Foto -->
            <div class="chat-attach-preview" id="chatAttachPreview" style="display:none;">
                <img id="chatAttachPreviewImg" src="" alt="Preview">
                <span id="chatAttachPreviewName"></span>
                <button type="button" id="chatAttachCancel" title="Batal"><i class="fas fa-times"></i></button>
            </div>

            <!-- Chat Footer / Input Form -->
            <form class="chat-footer" id="chatForm">
                <input type="file" id="chatImageInput" accept="image/*" style="display:none;">
                <button type="button" class="chat-input-tool" id="chatAttachBtn" title="Lampirkan Foto"><i class="fas fa-paperclip"></i></button>
                <input type="text" class="chat-input" id="chatInput" placeholder="Ketik pesan Anda di sini..." autocomplete="off">
                <button type="submit" class="chat-send-btn" title="Kirim Pesan"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>

    <!-- FOOTER -->
<?php include 'includes/footer.php'; ?>

    <!-- ================= JAVASCRIPT ================= -->
    <script>
        // Toggle Burger Menu & Social Dropdown
        const burgerBtn = document.getElementById('burgerBtn');
        const socialDropdown = document.getElementById('socialDropdown');
        
        burgerBtn.addEventListener('click', (e) => {
            socialDropdown.classList.toggle('active');
            e.stopPropagation();
        });

        // Search Dropdown Toggle Logic & Icon Switcher
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

        // Close dropdowns when clicking outside
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

        // ================= FIX TOMBOL KIRIM KEPOTONG DI MOBILE =================
        // vh biasa tidak update saat keyboard/toolbar browser muncul di HP,
        // jadi kita ukur tinggi viewport yang BENAR-BENAR terlihat lewat visualViewport.
        function updateChatViewportHeight() {
            const vv = window.visualViewport;
            const h = vv ? vv.height : window.innerHeight;
            document.documentElement.style.setProperty('--chat-vvh', h + 'px');
        }
        updateChatViewportHeight();
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', updateChatViewportHeight);
            window.visualViewport.addEventListener('scroll', updateChatViewportHeight);
        } else {
            window.addEventListener('resize', updateChatViewportHeight);
        }

        // ================= DETEKSI DAERAH PENGIRIM (OTOMATIS, TANPA POPUP IZIN) =================
        // Dipakai deteksi dari IP (selalu jalan, tidak butuh izin apapun dari user/HP).
        // GPS presisi hanya dipakai kalau browser SUDAH pernah mengizinkan sebelumnya
        // (dicek diam-diam lewat Permissions API, tidak akan memunculkan popup baru).
        let senderLocationText = sessionStorage.getItem('nb_chat_location') || '';

        function setSenderLocation(text) {
            if (!text) return;
            senderLocationText = text;
            sessionStorage.setItem('nb_chat_location', text);
        }

        function detectLocationByIP() {
            fetch('https://ipwho.is/')
                .then((r) => r.json())
                .then((data) => {
                    if (data && data.success !== false) {
                        const lokasi = [data.city, data.region].filter(Boolean).join(', ');
                        setSenderLocation(lokasi);
                    }
                })
                .catch(() => {});
        }

        function reverseGeocode(lat, lon) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}&zoom=10&addressdetails=1`)
                .then((r) => r.json())
                .then((data) => {
                    const addr = data.address || {};
                    const kota = addr.city || addr.town || addr.municipality || addr.county || addr.village || '';
                    const provinsi = addr.state || '';
                    const lokasi = [kota, provinsi].filter(Boolean).join(', ');
                    setSenderLocation(lokasi);
                })
                .catch(() => {});
        }

        if (!senderLocationText) {
            detectLocationByIP();
            if (navigator.geolocation && navigator.permissions) {
                navigator.permissions.query({ name: 'geolocation' }).then((status) => {
                    if (status.state === 'granted') {
                        navigator.geolocation.getCurrentPosition(
                            (pos) => reverseGeocode(pos.coords.latitude, pos.coords.longitude),
                            () => {},
                            { enableHighAccuracy: false, timeout: 8000, maximumAge: 600000 }
                        );
                    }
                }).catch(() => {});
            }
        }

        // ================= CHAT REALTIME (POLLING) =================
        const chatForm = document.getElementById('chatForm');
        const chatInput = document.getElementById('chatInput');
        const chatBody = document.getElementById('chatBody');
        const typingIndicator = document.getElementById('typingIndicator');
        let lastId = <?php echo (int) $last_id; ?>;

        const chatAttachBtn = document.getElementById('chatAttachBtn');
        const chatImageInput = document.getElementById('chatImageInput');
        const chatAttachPreview = document.getElementById('chatAttachPreview');
        const chatAttachPreviewImg = document.getElementById('chatAttachPreviewImg');
        const chatAttachPreviewName = document.getElementById('chatAttachPreviewName');
        const chatAttachCancel = document.getElementById('chatAttachCancel');
        const MAX_IMAGE_SIZE = 1024 * 1024; // 1MB
        let selectedImageFile = null;

        function getCurrentTime() {
            const now = new Date();
            let hours = now.getHours();
            let minutes = now.getMinutes();
            hours = hours < 10 ? '0' + hours : hours;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            return `${hours}:${minutes}`;
        }

        function scrollToBottom() {
            chatBody.scrollTo({
                top: chatBody.scrollHeight,
                behavior: 'smooth'
            });
        }

        function escapeHtml(text) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return text.replace(/[&<>"']/g, function (m) { return map[m]; });
        }

        function appendMessage(role, text, time, imageUrl, location) {
            const div = document.createElement('div');
            div.className = 'message ' + role;
            let html = '';
            if (imageUrl) {
                html += '<a href="' + imageUrl + '" target="_blank" rel="noopener"><img src="' + imageUrl + '" class="message-image" alt="Foto"></a>';
            }
            if (text) {
                html += escapeHtml(text).replace(/\n/g, '<br>');
            }
            if (location) {
                html += '<span class="message-location"><i class="fas fa-location-dot"></i>' + escapeHtml(location) + '</span>';
            }
            html += '<span class="message-time">' + time + '</span>';
            div.innerHTML = html;
            chatBody.insertBefore(div, typingIndicator);
            scrollToBottom();
        }

        chatAttachBtn.addEventListener('click', () => chatImageInput.click());
        chatImageInput.addEventListener('change', () => {
            const file = chatImageInput.files[0];
            if (!file) return;
            if (!file.type.startsWith('image/')) {
                alert('File yang dilampirkan harus berupa foto.');
                chatImageInput.value = '';
                return;
            }
            if (file.size > MAX_IMAGE_SIZE) {
                alert('Ukuran foto maksimal 1MB.');
                chatImageInput.value = '';
                return;
            }
            selectedImageFile = file;
            chatAttachPreviewName.textContent = file.name;
            const reader = new FileReader();
            reader.onload = (e) => { chatAttachPreviewImg.src = e.target.result; };
            reader.readAsDataURL(file);
            chatAttachPreview.style.display = 'flex';
        });
        chatAttachCancel.addEventListener('click', () => {
            selectedImageFile = null;
            chatImageInput.value = '';
            chatAttachPreview.style.display = 'none';
        });

        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const messageText = chatInput.value.trim();
            if (!messageText && !selectedImageFile) return;

            const formData = new FormData();
            formData.append('message', messageText);
            if (senderLocationText) formData.append('location', senderLocationText);
            if (selectedImageFile) formData.append('image', selectedImageFile);

            chatInput.value = '';
            chatImageInput.value = '';
            chatAttachPreview.style.display = 'none';
            selectedImageFile = null;

            fetch('messages-send.php', {
                method: 'POST',
                body: formData
            })
                .then((r) => r.json())
                .then((data) => {
                    if (data.status === 'ok') {
                        const imageUrl = data.image_path ? 'pesan-image.php?path=' + encodeURIComponent(data.image_path) : null;
                        appendMessage('user', messageText, getCurrentTime(), imageUrl, data.sender_location);
                        lastId = Math.max(lastId, data.id);
                    } else if (data.status === 'need_login') {
                        window.location.href = 'login.php';
                    } else {
                        alert(data.message || 'Gagal mengirim pesan.');
                    }
                })
                .catch(() => alert('Terjadi kesalahan jaringan.'));
        });

        // Polling pesan baru dari admin setiap 3 detik
        function pollMessages() {
            fetch('messages-fetch.php?after_id=' + lastId)
                .then((r) => r.json())
                .then((data) => {
                    if (data.status !== 'ok' || !data.messages) return;
                    data.messages.forEach((m) => {
                        if (m.sender_role === 'admin') {
                            const imageUrl = m.image_path ? 'pesan-image.php?path=' + encodeURIComponent(m.image_path) : null;
                            appendMessage('admin', m.message, m.created_at.substring(11, 16), imageUrl, m.sender_location);
                        }
                        lastId = Math.max(lastId, parseInt(m.id));
                    });
                })
                .catch(() => {});
        }
        setInterval(pollMessages, 3000);

        // Memastikan halaman digulir ke bawah saat baru dimuat
        window.addEventListener('load', () => {
            chatBody.scrollTop = chatBody.scrollHeight;
        });
    </script>
</body>
</html>