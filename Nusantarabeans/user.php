<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];
$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['form_profile'])) {
        $full_name = trim($_POST['fullName'] ?? '');
        $phone = trim($_POST['phoneNum'] ?? '');
        $stmt = $pdo->prepare('UPDATE users SET full_name = ?, phone = ? WHERE id = ?');
        $stmt->execute([$full_name, $phone, $user_id]);
        $_SESSION['user_name'] = $full_name;
        $msg = 'Informasi profil (Nama & Nomor HP) berhasil diperbarui!';
        $msg_type = 'success';
    } elseif (isset($_POST['form_recovery'])) {
        $recovery_email = trim($_POST['recoveryEmail'] ?? '');
        $stmt = $pdo->prepare('UPDATE users SET recovery_email = ? WHERE id = ?');
        $stmt->execute([$recovery_email, $user_id]);
        $msg = 'Email pemulihan berhasil diperbarui!';
        $msg_type = 'success';
    } elseif (isset($_POST['form_password'])) {
        $current = $_POST['currentPass'] ?? '';
        $new = $_POST['newPass'] ?? '';
        $confirm = $_POST['confirmPass'] ?? '';

        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($current, $row['password_hash'])) {
            $msg = 'Password saat ini salah.';
            $msg_type = 'error';
        } elseif ($new !== $confirm) {
            $msg = 'Konfirmasi password baru tidak cocok!';
            $msg_type = 'error';
        } elseif (strlen($new) < 6) {
            $msg = 'Password baru minimal 6 karakter.';
            $msg_type = 'error';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->execute([$hash, $user_id]);
            $msg = 'Kata sandi berhasil diubah!';
            $msg_type = 'success';
        }
    }
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$profile = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Saya - Nusantara Beans</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 512 512%22><path fill=%22%23C5A059%22 d=%22M96 96c0-35.3 28.7-64 64-64H384c35.3 0 64 28.7 64 64v32H96V96zM48 224h416c26.5 0 48 21.5 48 48v32c0 53-43 96-96 96H160c-53 0-96-43-96-96V272c0-26.5 21.5-48 48-48zm352 160h32c35.3 0 64-28.7 64-64V288H432v96zM32 448H480c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32-14.3-32-32s14.3-32-32-32z%22/></svg>">

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

        /* ================= USER PROFILE CONTAINER ================= */
        .user-page-container {
            flex: 1;
            max-width: 850px;
            width: 92%;
            margin: 95px auto 50px auto;
        }

        .page-header-title {
            font-family: var(--font-heading);
            font-size: clamp(24px, 4vw, 34px);
            color: var(--color-coffee);
            margin-bottom: 25px;
        }
        .page-header-title span { color: var(--color-gold); }

        .profile-card {
            background: var(--color-white);
            border-radius: 8px;
            border: 1px solid rgba(197, 160, 89, 0.2);
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
            padding: 30px;
            margin-bottom: 30px;
        }

        .profile-card-title {
            font-family: var(--font-heading);
            font-size: 20px;
            color: var(--color-coffee);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(197, 160, 89, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .profile-card-title i {
            color: var(--color-gold);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13.5px;
            font-weight: 500;
            color: var(--color-coffee);
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 11px 16px;
            border: 1px solid rgba(197, 160, 89, 0.4);
            border-radius: 6px;
            font-family: var(--font-body);
            font-size: 14px;
            background: var(--color-cream);
            color: var(--color-text);
            outline: none;
            transition: border-color 0.3s, background 0.3s;
        }

        .form-input:focus {
            border-color: var(--color-gold);
            background: var(--color-white);
        }

        .btn-save {
            background: var(--color-gold);
            color: var(--color-white);
            border: none;
            padding: 11px 24px;
            border-radius: 30px;
            font-family: var(--font-body);
            font-weight: 600;
            font-size: 13.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .btn-save:hover {
            background: var(--color-coffee);
            color: var(--color-gold);
            transform: translateY(-2px);
        }

        /* ================= FOOTER (Sesuai index.html) ================= */
        .site-footer {
            background: #1A120D;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            padding: 50px 5% 20px;
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
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

    <!-- USER PROFILE PAGE CONTAINER -->
    <div class="user-page-container">
        <h2 class="page-header-title">Pengaturan <span>Akun Saya</span></h2>

        <?php if ($msg): ?>
            <p style="margin-bottom:15px; font-weight:600; color: <?php echo $msg_type === 'success' ? '#1a7a1a' : '#b00020'; ?>;">
                <?php echo htmlspecialchars($msg); ?>
            </p>
        <?php endif; ?>

        <!-- Card 1: Informasi Profil (Nama & Nomor HP) -->
        <div class="profile-card">
            <h3 class="profile-card-title"><i class="fas fa-user-circle"></i> Informasi Profil</h3>
            <form id="profileForm" method="POST">
                <input type="hidden" name="form_profile" value="1">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-input" name="fullName" id="fullName" value="<?php echo htmlspecialchars($profile['full_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nomor HP / WhatsApp</label>
                    <input type="tel" class="form-input" name="phoneNum" id="phoneNum" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" required>
                </div>
                <button type="submit" class="btn-save">Simpan Perubahan</button>
            </form>
        </div>

        <!-- Card 2: Email Pemulihan -->
        <div class="profile-card">
            <h3 class="profile-card-title"><i class="fas fa-envelope-shield"></i> Email Pemulihan</h3>
            <form id="recoveryEmailForm" method="POST">
                <input type="hidden" name="form_recovery" value="1">
                <div class="form-group">
                    <label class="form-label">Email Pemulihan Aktif</label>
                    <input type="email" class="form-input" name="recoveryEmail" id="recoveryEmail" value="<?php echo htmlspecialchars($profile['recovery_email'] ?? ''); ?>" required>
                </div>
                <button type="submit" class="btn-save">Perbarui Email</button>
            </form>
        </div>

        <!-- Card 3: Ganti Password -->
        <div class="profile-card">
            <h3 class="profile-card-title"><i class="fas fa-lock"></i> Keamanan / Kata Sandi</h3>
            <form id="passwordForm" method="POST">
                <input type="hidden" name="form_password" value="1">
                <div class="form-group">
                    <label class="form-label">Password Saat Ini</label>
                    <input type="password" class="form-input" name="currentPass" id="currentPass" placeholder="Masukkan password lama" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <input type="password" class="form-input" name="newPass" id="newPass" placeholder="Masukkan password baru" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" class="form-input" name="confirmPass" id="confirmPass" placeholder="Ulangi password baru" required>
                </div>
                <button type="submit" class="btn-save">Ganti Password</button>
            </form>
        </div>
    </div>

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
    </script>
</body>
</html>
