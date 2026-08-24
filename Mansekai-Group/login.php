<?php
require_once __DIR__ . '/config.php';
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}
$csrf = csrf_token();
$devLink = '';
if (isset($_GET['dev_link'])) {
    $devLink = $_GET['dev_link'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication - Dashboard V5.0</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3b3086;
            --primary-light: rgba(59, 48, 134, 0.1);
            --bg-color: #f5f6fa;
            --text-dark: #2d3436;
            --text-light: #8e94a8;
            --white: #ffffff;
            --shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            --icon-green: #2ecc71;
            --icon-red: #ff4757;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        body {
            background-color: var(--bg-color);
            color: var(--text-dark);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Top Right Language Dropdown - Fully Responsive Fix */
        .auth-top-bar {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .lang-dropdown {
            background: var(--white);
            border: 1px solid #e1e4e8;
            padding: 8px 14px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-dark);
            outline: none;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            font-family: 'Poppins', sans-serif;
        }
        .lang-dropdown:hover { border-color: var(--primary); }

        /* Auth Card Container */
        .auth-container {
            background: var(--white);
            width: 100%;
            max-width: 440px;
            padding: 40px;
            border-radius: 24px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            margin-top: 30px;
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 25px;
        }
        .auth-logo img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(59, 48, 134, 0.2);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .auth-header h2 {
            font-size: 22px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 6px;
        }
        .auth-header p {
            font-size: 12px;
            color: var(--text-light);
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            font-size: 12px;
            margin-bottom: 6px;
            color: var(--text-dark);
            font-weight: 500;
        }
        .input-icon-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-icon-wrapper i {
            position: absolute;
            left: 15px;
            color: var(--text-light);
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px 12px 42px;
            border: 1px solid #e1e4e8;
            border-radius: 12px;
            outline: none;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            transition: 0.2s;
            background: #fafafa;
        }
        .form-group input:focus {
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        .auth-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            margin-bottom: 25px;
            gap: 10px;
            flex-wrap: wrap;
        }
        .auth-options label {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            color: var(--text-light);
        }
        .auth-options a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        .auth-options a:hover { text-decoration: underline; }

        .btn-auth {
            background: var(--primary);
            color: white;
            border: none;
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 8px 20px rgba(59, 48, 134, 0.25);
        }
        .btn-auth:hover {
            opacity: 0.95;
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(59, 48, 134, 0.35);
        }

        .auth-footer {
            text-align: center;
            margin-top: 25px;
            font-size: 12px;
            color: var(--text-light);
        }
        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        .auth-footer a:hover { text-decoration: underline; }

        .auth-alert {
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 12px;
            margin-bottom: 20px;
            display: none;
        }
        .auth-alert.error { background: rgba(255, 71, 87, 0.1); color: var(--icon-red); border: 1px solid rgba(255, 71, 87, 0.3); }
        .auth-alert.success { background: rgba(46, 204, 113, 0.1); color: var(--icon-green); border: 1px solid rgba(46, 204, 113, 0.3); }

        .auth-view { display: none; }
        .auth-view.active { display: block; animation: fadeIn 0.3s ease; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Mobile Responsiveness Improvements */
        @media (max-width: 480px) {
            body { padding: 15px; align-items: flex-start; }
            .auth-top-bar { top: 10px; right: 10px; }
            .lang-dropdown { padding: 6px 10px; font-size: 11px; }
            .auth-container { padding: 30px 15px; margin-top: 45px; border-radius: 18px; }
            .auth-header h2 { font-size: 19px; }
        }
    </style>
</head>
<body>

    <!-- Language Selector Top Right (Fixed for Mobile) -->
    <div class="auth-top-bar">
        <select id="langSelect" class="lang-dropdown" onchange="setLanguage(this.value)">
            <option value="id">🇮🇩 ID</option>
            <option value="en">🇬🇧 EN</option>
            <option value="ja">🇯🇵 JA</option>
        </select>
    </div>

    <div class="auth-container">
        <div class="auth-logo">
            <img src="logo.png" alt="Logo">
        </div>

        <?php if ($devLink): ?>
        <div class="auth-alert success" style="display:block; word-break:break-all;">
            Mode developer (Brevo belum dikonfigurasi): gunakan link ini untuk reset password:<br>
            <a href="<?php echo htmlspecialchars($devLink); ?>"><?php echo htmlspecialchars($devLink); ?></a>
        </div>
        <?php endif; ?>

        <!-- 1. VIEW LOGIN -->
        <div id="viewLogin" class="auth-view active">
            <div class="auth-header">
                <h2 data-i18n="loginTitle">Masuk ke Akun Anda</h2>
                <p data-i18n="loginSubtitle">Silakan masukkan detail akun Anda untuk melanjutkan</p>
            </div>

            <div id="loginAlert" class="auth-alert"></div>

            <form onsubmit="handleLogin(event)">
                <div class="form-group">
                    <label data-i18n="emailLabel">Email / Username</label>
                    <div class="input-icon-wrapper">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="text" id="loginEmail" placeholder="ghulam@email.com" required>
                    </div>
                </div>
                <div class="form-group">
                    <label data-i18n="passwordLabel">Password</label>
                    <div class="input-icon-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="loginPassword" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="auth-options">
                    <label><input type="checkbox"> <span data-i18n="rememberMe">Ingat saya</span></label>
                    <a href="#" onclick="switchView('reset', event)" data-i18n="forgotPassword">Lupa password?</a>
                </div>

                <button type="submit" class="btn-auth" data-i18n="loginBtn">Masuk</button>
            </form>

            <div class="auth-footer">
                <span data-i18n="noAccount">Belum punya akun?</span> <a href="#" onclick="switchView('register', event)" data-i18n="registerLink">Daftar sekarang</a>
            </div>
        </div>

        <!-- 2. VIEW REGISTER -->
        <div id="viewRegister" class="auth-view">
            <div class="auth-header">
                <h2 data-i18n="registerTitle">Buat Akun Baru</h2>
                <p data-i18n="registerSubtitle">Daftar untuk mulai menggunakan dashboard V5.0</p>
            </div>

            <div id="regAlert" class="auth-alert"></div>

            <form onsubmit="handleRegister(event)">
                <div class="form-group">
                    <label data-i18n="fullNameLabel">Nama Lengkap</label>
                    <div class="input-icon-wrapper">
                        <i class="fa-regular fa-user"></i>
                        <input type="text" id="regName" placeholder="Nama Anda" required>
                    </div>
                </div>
                <div class="form-group">
                    <label data-i18n="emailLabel">Email / Username</label>
                    <div class="input-icon-wrapper">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="text" id="regEmail" placeholder="email@domain.com" required>
                    </div>
                </div>
                <div class="form-group">
                    <label data-i18n="passwordLabel">Password</label>
                    <div class="input-icon-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="regPassword" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="form-group">
                    <label data-i18n="confirmPasswordLabel">Konfirmasi Password</label>
                    <div class="input-icon-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="regConfirmPassword" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn-auth" style="margin-top: 10px;" data-i18n="registerBtn">Daftar</button>
            </form>

            <div class="auth-footer">
                <span data-i18n="haveAccount">Sudah punya akun?</span> <a href="#" onclick="switchView('login', event)" data-i18n="loginLink">Masuk di sini</a>
            </div>
        </div>

        <!-- 3. VIEW RESET PASSWORD -->
        <div id="viewReset" class="auth-view">
            <div class="auth-header">
                <h2 data-i18n="resetTitle">Reset Password</h2>
                <p data-i18n="resetSubtitle">Masukkan email pemulihan untuk menerima instruksi reset password</p>
            </div>

            <div id="resetAlert" class="auth-alert"></div>

            <form onsubmit="handleResetPassword(event)">
                <div class="form-group">
                    <label data-i18n="recoveryEmailLabel">Email Pemulihan</label>
                    <div class="input-icon-wrapper">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" id="resetEmail" placeholder="email@pemulihan.com" required>
                    </div>
                </div>

                <button type="submit" class="btn-auth" style="margin-top: 10px;" data-i18n="sendResetBtn">Kirim Instruksi Reset</button>
            </form>

            <div class="auth-footer">
                <a href="#" onclick="switchView('login', event)" data-i18n="backToLogin"><i class="fa-solid fa-arrow-left"></i> Kembali ke Login</a>
            </div>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = "<?php echo $csrf; ?>";
        let currentLang = localStorage.getItem('dashboard_lang') || 'id';

        const translations = {
            id: {
                loginTitle: "Masuk ke Akun Anda",
                loginSubtitle: "Silakan masukkan detail akun Anda untuk melanjutkan",
                emailLabel: "Email / Username",
                passwordLabel: "Password",
                rememberMe: "Ingat saya",
                forgotPassword: "Lupa password?",
                loginBtn: "Masuk",
                noAccount: "Belum punya akun?",
                registerLink: "Daftar sekarang",
                registerTitle: "Buat Akun Baru",
                registerSubtitle: "Daftar untuk mulai menggunakan dashboard V5.0",
                fullNameLabel: "Nama Lengkap",
                confirmPasswordLabel: "Konfirmasi Password",
                registerBtn: "Daftar",
                haveAccount: "Sudah punya akun?",
                loginLink: "Masuk di sini",
                resetTitle: "Reset Password",
                resetSubtitle: "Masukkan email pemulihan untuk menerima instruksi reset password",
                recoveryEmailLabel: "Email Pemulihan",
                sendResetBtn: "Kirim Instruksi Reset",
                backToLogin: "Kembali ke Login",
                loginSuccess: "Login Berhasil! Mengalihkan ke dashboard...",
                regSuccess: "Registrasi Berhasil! Silakan masuk.",
                passMismatch: "Password dan konfirmasi password tidak cocok!",
                resetSent: "Instruksi reset password telah dikirim ke email Anda!"
            },
            en: {
                loginTitle: "Sign In to Your Account",
                loginSubtitle: "Please enter your account details to continue",
                emailLabel: "Email / Username",
                passwordLabel: "Password",
                rememberMe: "Remember me",
                forgotPassword: "Forgot password?",
                loginBtn: "Sign In",
                noAccount: "Don't have an account?",
                registerLink: "Register now",
                registerTitle: "Create New Account",
                registerSubtitle: "Sign up to start using dashboard V5.0",
                fullNameLabel: "Full Name",
                confirmPasswordLabel: "Confirm Password",
                registerBtn: "Sign Up",
                haveAccount: "Already have an account?",
                loginLink: "Sign in here",
                resetTitle: "Reset Password",
                resetSubtitle: "Enter recovery email to receive password reset instructions",
                recoveryEmailLabel: "Recovery Email",
                sendResetBtn: "Send Reset Instructions",
                backToLogin: "Back to Login",
                loginSuccess: "Login Successful! Redirecting to dashboard...",
                regSuccess: "Registration Successful! Please sign in.",
                passMismatch: "Password and confirm password do not match!",
                resetSent: "Password reset instructions have been sent to your email!"
            },
            ja: {
                loginTitle: "アカウントにサインイン",
                loginSubtitle: "続行するにはアカウントの詳細を入力してください",
                emailLabel: "メール / ユーザー名",
                passwordLabel: "パスワード",
                rememberMe: "ログイン状態を保持する",
                forgotPassword: "パスワードをお忘れですか？",
                loginBtn: "サインイン",
                noAccount: "アカウントをお持ちではありませんか？",
                registerLink: "今すぐ登録",
                registerTitle: "新規アカウント作成",
                registerSubtitle: "ダッシュボード V5.0 の利用を開始するには登録してください",
                fullNameLabel: "氏名",
                confirmPasswordLabel: "パスワード（確認）",
                registerBtn: "登録",
                haveAccount: "すでにアカウントをお持ちですか？",
                loginLink: "こちらからサインイン",
                resetTitle: "パスワードリセット",
                resetSubtitle: "パスワードリセットの手順を受け取るための復旧用メールを入力してください",
                recoveryEmailLabel: "復旧用メール",
                sendResetBtn: "リセット手順を送信",
                backToLogin: "ログインに戻る",
                loginSuccess: "ログイン成功！ダッシュボードにリダイレクト中...",
                regSuccess: "登録成功！サインインしてください。",
                passMismatch: "パスワードと確認用パスワードが一致しません！",
                resetSent: "パスワードリセットの手順をメールに送信しました！"
            }
        };

        function setLanguage(lang) {
            currentLang = lang;
            localStorage.setItem('dashboard_lang', lang);
            const selectEl = document.getElementById('langSelect');
            if(selectEl) selectEl.value = lang;
            applyTranslations();
        }

        function t(key) {
            return translations[currentLang][key] || translations['en'][key] || key;
        }

        function applyTranslations() {
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if(translations[currentLang] && translations[currentLang][key]) {
                    el.innerText = translations[currentLang][key];
                }
            });
        }

        function switchView(viewName, event) {
            if(event) event.preventDefault();
            
            document.querySelectorAll('.auth-view').forEach(v => v.classList.remove('active'));
            document.querySelectorAll('.auth-alert').forEach(a => { a.style.display = 'none'; a.innerText = ''; });

            if(viewName === 'login') {
                document.getElementById('viewLogin').classList.add('active');
            } else if(viewName === 'register') {
                document.getElementById('viewRegister').classList.add('active');
            } else if(viewName === 'reset') {
                document.getElementById('viewReset').classList.add('active');
            }
        }

        function showAlert(elementId, message, type) {
            const alertEl = document.getElementById(elementId);
            alertEl.innerText = message;
            alertEl.className = `auth-alert ${type}`;
            alertEl.style.display = 'block';
        }

        async function apiAuth(action, payload) {
            const res = await fetch(`api/auth.php?action=${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ...payload, csrf: CSRF_TOKEN })
            });
            let data;
            try { data = await res.json(); } catch(e) { data = { ok: false, error: 'Respon server tidak valid.' }; }
            return data;
        }

        function setSubmitting(form, isSubmitting) {
            const btn = form.querySelector('button[type="submit"]');
            if(!btn) return;
            btn.disabled = isSubmitting;
            btn.style.opacity = isSubmitting ? '0.6' : '1';
        }

        async function handleLogin(event) {
            event.preventDefault();
            const form = event.target;
            setSubmitting(form, true);
            const identity = document.getElementById('loginEmail').value.trim();
            const password = document.getElementById('loginPassword').value;

            const data = await apiAuth('login', { identity, password });
            setSubmitting(form, false);

            if(!data.ok) {
                showAlert('loginAlert', data.error || 'Login gagal.', 'error');
                return;
            }
            showAlert('loginAlert', t('loginSuccess'), 'success');
            setTimeout(() => { window.location.href = 'dashboard.php'; }, 900);
        }

        async function handleRegister(event) {
            event.preventDefault();
            const form = event.target;
            const name = document.getElementById('regName').value.trim();
            const email = document.getElementById('regEmail').value.trim();
            const pass = document.getElementById('regPassword').value;
            const confirmPass = document.getElementById('regConfirmPassword').value;

            if(pass !== confirmPass) {
                showAlert('regAlert', t('passMismatch'), 'error');
                return;
            }

            setSubmitting(form, true);
            const data = await apiAuth('register', { name, email, password: pass, confirm: confirmPass });
            setSubmitting(form, false);

            if(!data.ok) {
                showAlert('regAlert', data.error || 'Registrasi gagal.', 'error');
                return;
            }
            showAlert('regAlert', t('regSuccess'), 'success');
            setTimeout(() => {
                switchView('login');
                document.getElementById('loginEmail').value = email;
            }, 1200);
        }

        async function handleResetPassword(event) {
            event.preventDefault();
            const form = event.target;
            const email = document.getElementById('resetEmail').value.trim();

            setSubmitting(form, true);
            const data = await apiAuth('request_reset', { email });
            setSubmitting(form, false);

            if(!data.ok) {
                showAlert('resetAlert', data.error || 'Gagal mengirim instruksi reset.', 'error');
                return;
            }
            showAlert('resetAlert', t('resetSent'), 'success');
            if(data.dev_link) {
                window.location.href = 'login.php?dev_link=' + encodeURIComponent(data.dev_link);
                return;
            }
            setTimeout(() => {
                switchView('login');
            }, 2000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const selectEl = document.getElementById('langSelect');
            if(selectEl) selectEl.value = currentLang;
            applyTranslations();
        });
    </script>
</body>
</html>