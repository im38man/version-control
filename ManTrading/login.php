<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$view = $_GET['view'] ?? 'login'; // login | register | forgot
if (!in_array($view, ['login', 'register', 'forgot'], true)) {
    $view = 'login';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
}

// ---------- PROSES LOGIN ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $view = 'login';
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi ya bro!';
    } else {
        $stmt = $conn->prepare('SELECT id, full_name, email, password_hash, role, vip_status, mentor_status FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $userRow = $result->fetch_assoc();
        $stmt->close();

        if (!$userRow || !password_verify($password, $userRow['password_hash'])) {
            $errors[] = 'Email atau password salah, coba lagi bro.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id']       = $userRow['id'];
            $_SESSION['full_name']     = $userRow['full_name'];
            $_SESSION['email']         = $userRow['email'];
            $_SESSION['role']          = $userRow['role'];
            $_SESSION['vip_status']    = $userRow['vip_status'];
            $_SESSION['mentor_status'] = $userRow['mentor_status'];
            header('Location: index.php');
            exit;
        }
    }
}

// ---------- PROSES REGISTER ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'register') {
    $view = 'register';
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($fullName === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $errors[] = 'Semua kolom wajib diisi ya bro!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Konfirmasi password tidak cocok!';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email ini sudah terdaftar. Silakan login.';
        }
        $stmt->close();

        if (!$errors) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (full_name, email, password_hash, role, vip_status) VALUES (?, ?, ?, \'user\', \'none\')');
            $stmt->bind_param('sss', $fullName, $email, $hash);
            $stmt->execute();
            $stmt->close();

            flash_set('Registrasi berhasil! Silakan login dengan akun baru lu.', 'success');
            header('Location: login.php?view=login');
            exit;
        }
    }
}

// ---------- PROSES LUPA PASSWORD (reset sederhana tanpa email) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'forgot') {
    $view = 'forgot';
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $errors[] = 'Masukkan email akun lu terlebih dahulu!';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        $found = $stmt->num_rows > 0;
        $stmt->close();

        // Pesan sama baik email ditemukan atau tidak (mencegah enumerasi akun)
        flash_set('Kalau email tersebut terdaftar, instruksi reset password sudah kami proses. Hubungi admin ManTrading untuk bantuan reset manual.', 'success');
        header('Location: login.php?view=login');
        exit;
    }
}

$flash = flash_get();
$pageTitle = 'ManTrading - Authentication';
require_once __DIR__ . '/includes/head.php';
?>
<div class="min-h-screen flex items-center justify-center bg-slate-950 p-4">
  <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-800 p-8 animate-fade-in-up relative overflow-hidden">
    <div class="absolute top-0 right-0 -mt-12 -mr-12 w-40 h-40 bg-indigo-600/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="text-center mb-8">
      <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-400 flex items-center justify-center shadow-lg shadow-indigo-500/30 mx-auto mb-3">
        <i class="fa-solid fa-chart-line text-white text-xl"></i>
      </div>
      <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Man<span class="text-indigo-600">Trading</span></h1>
      <p class="text-xs text-slate-500 mt-1 font-medium italic">Journey me change the future</p>
    </div>

    <?php if ($flash): ?>
      <div class="mb-4 text-xs font-semibold rounded-xl p-3 <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' ?>">
        <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <?php if ($errors): ?>
      <div class="mb-4 text-xs font-semibold rounded-xl p-3 bg-rose-50 text-rose-700 border border-rose-200 space-y-1">
        <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($view === 'login'): ?>
      <form method="POST" action="login.php" class="space-y-4">
<?= csrf_field() ?>
        <input type="hidden" name="action" value="login">
        <div class="space-y-1">
          <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Email Address</label>
          <div class="relative">
            <i class="fa-solid fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="email" name="email" placeholder="name@example.com" required
              class="w-full bg-gray-50 border border-gray-300 text-slate-900 pl-10 pr-4 py-3 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
          </div>
        </div>
        <div class="space-y-1">
          <div class="flex justify-between items-center">
            <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Password</label>
            <a href="login.php?view=forgot" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">Lupa Password?</a>
          </div>
          <div class="relative">
            <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="password" name="password" placeholder="••••••••" required
              class="w-full bg-gray-50 border border-gray-300 text-slate-900 pl-10 pr-4 py-3 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
          </div>
        </div>
        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold text-sm transition-all shadow-lg shadow-indigo-600/25 mt-2 flex items-center justify-center gap-2">
          <span>Masuk Sesi</span> <i class="fa-solid fa-arrow-right text-xs"></i>
        </button>
        <div class="text-center pt-4 border-t border-gray-100 mt-6">
          <p class="text-xs text-slate-500">Belum punya akun? <a href="login.php?view=register" class="font-bold text-indigo-600 hover:text-indigo-700 ml-1">Daftar Sekarang</a></p>
        </div>
      </form>

    <?php elseif ($view === 'register'): ?>
      <form method="POST" action="login.php" class="space-y-4">
<?= csrf_field() ?>
        <input type="hidden" name="action" value="register">
        <div class="space-y-1">
          <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Nama Lengkap</label>
          <div class="relative">
            <i class="fa-solid fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="text" name="full_name" placeholder="Budi Setiawan" required value="<?= e($_POST['full_name'] ?? '') ?>"
              class="w-full bg-gray-50 border border-gray-300 text-slate-900 pl-10 pr-4 py-3 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
          </div>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Email Address</label>
          <div class="relative">
            <i class="fa-solid fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="email" name="email" placeholder="name@example.com" required value="<?= e($_POST['email'] ?? '') ?>"
              class="w-full bg-gray-50 border border-gray-300 text-slate-900 pl-10 pr-4 py-3 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
          </div>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Password</label>
          <div class="relative">
            <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="password" name="password" placeholder="Minimal 6 karakter" required
              class="w-full bg-gray-50 border border-gray-300 text-slate-900 pl-10 pr-4 py-3 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
          </div>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Konfirmasi Password</label>
          <div class="relative">
            <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="password" name="confirm_password" placeholder="••••••••" required
              class="w-full bg-gray-50 border border-gray-300 text-slate-900 pl-10 pr-4 py-3 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
          </div>
        </div>
        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold text-sm transition-all shadow-lg shadow-indigo-600/25 mt-2 flex items-center justify-center gap-2">
          <span>Buat Akun Baru</span> <i class="fa-solid fa-user-plus text-xs"></i>
        </button>
        <div class="text-center pt-4 border-t border-gray-100 mt-6">
          <p class="text-xs text-slate-500">Sudah punya akun? <a href="login.php?view=login" class="font-bold text-indigo-600 hover:text-indigo-700 ml-1">Masuk di sini</a></p>
        </div>
      </form>

    <?php else: // forgot ?>
      <form method="POST" action="login.php" class="space-y-4">
<?= csrf_field() ?>
        <input type="hidden" name="action" value="forgot">
        <div class="text-center mb-2">
          <p class="text-xs text-slate-500 leading-relaxed">Masukkan email terdaftar lu. Kami akan bantu proses reset password akun ManTrading lu.</p>
        </div>
        <div class="space-y-1">
          <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Email Address</label>
          <div class="relative">
            <i class="fa-solid fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="email" name="email" placeholder="name@example.com" required
              class="w-full bg-gray-50 border border-gray-300 text-slate-900 pl-10 pr-4 py-3 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
          </div>
        </div>
        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold text-sm transition-all shadow-lg shadow-indigo-600/25 mt-2 flex items-center justify-center gap-2">
          <span>Kirim Reset Link</span> <i class="fa-solid fa-paper-plane text-xs"></i>
        </button>
        <div class="text-center pt-4 border-t border-gray-100 mt-6">
          <a href="login.php?view=login" class="text-xs font-bold text-slate-600 hover:text-indigo-600 transition-colors flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Halaman Login
          </a>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
