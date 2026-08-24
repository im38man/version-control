<?php
/**
 * ManTrading - Setup Wizard
 * Buka file ini pertama kali sebelum pakai aplikasi (mis. https://domainlu.com/setup-admin.php)
 * Aman dijalankan berkali-kali sebelum instalasi selesai (idempotent).
 * Setelah instalasi sukses, file ini otomatis mengunci diri (.installed).
 */

require_once __DIR__ . '/config.db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrf_token_setup(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify_setup(): void {
    $sent = $_POST['csrf_token'] ?? '';
    $valid = !empty($_SESSION['csrf_token']) && is_string($sent) && hash_equals($_SESSION['csrf_token'], $sent);
    if (!$valid) {
        http_response_code(403);
        die('Sesi form sudah kadaluarsa. Silakan refresh halaman ini dan coba lagi.');
    }
}

function csrf_field_setup(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token_setup(), ENT_QUOTES, 'UTF-8') . '">';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify_setup();
}

$installedLock = __DIR__ . '/.installed';
$alreadyInstalled = file_exists($installedLock);

$dbConfigured = DB_HOST !== '' && DB_NAME !== '' && DB_USER !== '';
$errors = [];
$success = null;
$conn = null;

function try_connect(string $host, string $name, string $user, string $pass): array {
    mysqli_report(MYSQLI_REPORT_OFF);
    $c = @new mysqli($host, $user, $pass, $name);
    if ($c->connect_error) {
        return [null, $c->connect_error];
    }
    $c->set_charset('utf8mb4');
    $c->query("SET time_zone = '+07:00'");
    return [$c, null];
}

function write_db_config(string $host, string $name, string $user, string $pass): bool {
    $content = "<?php\n"
        . "/**\n * ManTrading - Kredensial Database (auto-generated oleh setup-admin.php)\n */\n"
        . "define('DB_HOST', " . var_export($host, true) . ");\n"
        . "define('DB_NAME', " . var_export($name, true) . ");\n"
        . "define('DB_USER', " . var_export($user, true) . ");\n"
        . "define('DB_PASS', " . var_export($pass, true) . ");\n";
    return @file_put_contents(__DIR__ . '/config.db.php', $content) !== false;
}

function import_schema(mysqli $conn, array &$errors): bool {
    $sqlPath = __DIR__ . '/schema.sql';
    if (!is_file($sqlPath)) {
        $errors[] = 'File schema.sql tidak ditemukan di folder project.';
        return false;
    }
    $sql = file_get_contents($sqlPath);
    if ($conn->multi_query($sql)) {
        do {
            if ($res = $conn->store_result()) { $res->free(); }
        } while ($conn->more_results() && $conn->next_result());
    }
    if ($conn->errno) {
        $errors[] = 'Import schema gagal: ' . $conn->error;
        return false;
    }
    return true;
}

// ================= STEP 1: Konfigurasi Database =================
if (!$alreadyInstalled && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_db') {
    $host = trim($_POST['db_host'] ?? '');
    $name = trim($_POST['db_name'] ?? '');
    $user = trim($_POST['db_user'] ?? '');
    $pass = $_POST['db_pass'] ?? '';

    if ($host === '' || $name === '' || $user === '') {
        $errors[] = 'Host, nama database, dan user database wajib diisi.';
    } else {
        [$testConn, $connErr] = try_connect($host, $name, $user, $pass);
        if (!$testConn) {
            $errors[] = 'Koneksi database gagal: ' . $connErr . '. Cek lagi kredensialnya bro.';
        } else {
            if (!write_db_config($host, $name, $user, $pass)) {
                $errors[] = 'Gagal menyimpan config.db.php. Cek permission folder (harus writable), atau isi manual lewat FTP.';
            } elseif (!import_schema($testConn, $errors)) {
                // errors sudah di-set di dalam import_schema()
            } else {
                $conn = $testConn;
                define('DB_HOST', $host);
                define('DB_NAME', $name);
                define('DB_USER', $user);
                define('DB_PASS', $pass);
                $dbConfigured = true;
                $success = 'db_saved';
            }
        }
    }
}

// Kalau DB sudah pernah dikonfigurasi sebelumnya (reload halaman di step 2), sambungkan ulang
if (!$alreadyInstalled && $dbConfigured && !$conn) {
    [$conn, $connErr] = try_connect(DB_HOST, DB_NAME, DB_USER, DB_PASS);
    if (!$conn) {
        $errors[] = 'Koneksi ke database yang tersimpan gagal: ' . $connErr . '. Isi ulang form di bawah.';
        $dbConfigured = false;
    }
}

// ================= STEP 2: Buat Akun Admin =================
if (!$alreadyInstalled && $dbConfigured && $conn && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_admin') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($fullName === '' || $email === '' || $password === '') {
        $errors[] = 'Semua kolom wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, role, vip_status) VALUES (?, ?, ?, 'admin', 'approved') ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = 'admin'");
        $stmt->bind_param('sss', $fullName, $email, $hash);
        if ($stmt->execute()) {
            $stmt->close();
            @file_put_contents($installedLock, date('c'));
            $alreadyInstalled = true;
            $success = 'admin_created';
        } else {
            $errors[] = 'Gagal membuat akun admin: ' . $stmt->error;
        }
    }
}

$pageTitle = 'ManTrading - Setup Instalasi';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] } } } }</script>
</head>
<body class="bg-slate-950 text-gray-800 antialiased min-h-screen flex items-center justify-center p-4 font-sans">
<div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl p-8 relative overflow-hidden">
  <div class="absolute top-0 right-0 -mt-12 -mr-12 w-40 h-40 bg-indigo-600/10 rounded-full blur-2xl pointer-events-none"></div>

  <div class="text-center mb-6">
    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-400 flex items-center justify-center shadow-lg shadow-indigo-500/30 mx-auto mb-3">
      <i class="fa-solid fa-chart-line text-white text-xl"></i>
    </div>
    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Man<span class="text-indigo-600">Trading</span> Setup</h1>
    <p class="text-xs text-slate-500 mt-1 font-medium">Wizard instalasi awal — jalan sekali saja</p>
  </div>

  <?php if ($errors): ?>
    <div class="mb-5 text-xs font-semibold rounded-xl p-3 bg-rose-50 text-rose-700 border border-rose-200 space-y-1">
      <?php foreach ($errors as $err): ?><p><i class="fa-solid fa-circle-exclamation mr-1"></i> <?= htmlspecialchars($err) ?></p><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($alreadyInstalled && $success !== 'admin_created'): ?>
    <!-- SUDAH PERNAH DIINSTAL -->
    <div class="text-center space-y-4">
      <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-2xl mx-auto"><i class="fa-solid fa-check"></i></div>
      <h2 class="text-lg font-bold text-gray-800">ManTrading Sudah Terinstal</h2>
      <p class="text-xs text-gray-500 leading-relaxed">
        Aplikasi ini sudah pernah di-setup sebelumnya. Kalau lu mau instal ulang (misalnya ganti database), hapus dulu file <code class="bg-gray-100 px-1.5 py-0.5 rounded font-mono">.installed</code> lewat FTP/File Manager hosting, baru buka halaman ini lagi.
      </p>
      <a href="login.php" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-bold text-sm transition-all shadow-lg shadow-indigo-600/25 mt-2">
        <i class="fa-solid fa-right-to-bracket"></i> Ke Halaman Login
      </a>
    </div>

  <?php elseif ($success === 'admin_created'): ?>
    <!-- SELESAI -->
    <div class="text-center space-y-4">
      <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-2xl mx-auto"><i class="fa-solid fa-party-horn"></i></div>
      <h2 class="text-lg font-bold text-gray-800">Instalasi Berhasil!</h2>
      <p class="text-xs text-gray-500 leading-relaxed">Database sudah siap dan akun admin sudah dibuat. Sekarang lu bisa langsung login.</p>
      <a href="login.php" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-bold text-sm transition-all shadow-lg shadow-indigo-600/25 mt-2">
        <i class="fa-solid fa-right-to-bracket"></i> Login Sekarang
      </a>
    </div>

  <?php elseif ($dbConfigured && $conn): ?>
    <!-- STEP 2: BUAT ADMIN -->
    <div class="mb-5 text-xs font-semibold rounded-xl p-3 bg-emerald-50 text-emerald-700 border border-emerald-200">
      <i class="fa-solid fa-check-circle mr-1"></i> Database tersambung & tabel sudah dibuat. Tinggal satu langkah lagi.
    </div>
    <form method="POST" class="space-y-4">
<?= csrf_field_setup() ?>
      <input type="hidden" name="action" value="create_admin">
      <p class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Buat Akun Admin Pertama</p>
      <div class="space-y-1">
        <label class="text-xs font-bold text-slate-600">Nama Lengkap</label>
        <input type="text" name="full_name" required placeholder="Nama Admin" class="w-full bg-gray-50 border border-gray-300 text-slate-900 px-4 py-2.5 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-bold text-slate-600">Email</label>
        <input type="email" name="email" required placeholder="admin@domainlu.com" class="w-full bg-gray-50 border border-gray-300 text-slate-900 px-4 py-2.5 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
          <label class="text-xs font-bold text-slate-600">Password</label>
          <input type="password" name="password" required placeholder="Min. 6 karakter" class="w-full bg-gray-50 border border-gray-300 text-slate-900 px-4 py-2.5 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>
        <div class="space-y-1">
          <label class="text-xs font-bold text-slate-600">Konfirmasi</label>
          <input type="password" name="confirm_password" required placeholder="Ulangi password" class="w-full bg-gray-50 border border-gray-300 text-slate-900 px-4 py-2.5 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>
      </div>
      <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold text-sm transition-all shadow-lg shadow-indigo-600/25 flex items-center justify-center gap-2 mt-2">
        <i class="fa-solid fa-user-shield"></i> Buat Akun Admin & Selesaikan Instalasi
      </button>
    </form>

  <?php else: ?>
    <!-- STEP 1: KONFIGURASI DATABASE -->
    <div class="mb-5 bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-[11px] text-slate-500 leading-relaxed">
      <p class="font-bold text-slate-600 mb-1"><i class="fa-solid fa-circle-info mr-1 text-indigo-500"></i> Pakai hosting InfinityFree?</p>
      Buat database dulu di panel <b>MySQL Databases</b>, lalu salin <b>hostname</b>, <b>nama database</b>, dan <b>username</b> yang formatnya biasanya <code class="bg-white px-1 rounded border">sqlXXX.infinityfree.com</code> dan <code class="bg-white px-1 rounded border">epiz_xxxxxxx_dbname</code>.
    </div>
    <form method="POST" class="space-y-4">
<?= csrf_field_setup() ?>
      <input type="hidden" name="action" value="save_db">
      <div class="space-y-1">
        <label class="text-xs font-bold text-slate-600">DB Host</label>
        <input type="text" name="db_host" required placeholder="localhost / sqlXXX.infinityfree.com" value="<?= htmlspecialchars($_POST['db_host'] ?? DB_HOST) ?>" class="w-full bg-gray-50 border border-gray-300 text-slate-900 px-4 py-2.5 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-bold text-slate-600">Nama Database</label>
        <input type="text" name="db_name" required placeholder="epiz_xxxxxxx_mantrading" value="<?= htmlspecialchars($_POST['db_name'] ?? DB_NAME) ?>" class="w-full bg-gray-50 border border-gray-300 text-slate-900 px-4 py-2.5 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-bold text-slate-600">DB User</label>
        <input type="text" name="db_user" required placeholder="epiz_xxxxxxx" value="<?= htmlspecialchars($_POST['db_user'] ?? DB_USER) ?>" class="w-full bg-gray-50 border border-gray-300 text-slate-900 px-4 py-2.5 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
      </div>
      <div class="space-y-1">
        <label class="text-xs font-bold text-slate-600">DB Password</label>
        <input type="password" name="db_pass" placeholder="Password database" class="w-full bg-gray-50 border border-gray-300 text-slate-900 px-4 py-2.5 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
      </div>
      <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold text-sm transition-all shadow-lg shadow-indigo-600/25 flex items-center justify-center gap-2 mt-2">
        <i class="fa-solid fa-database"></i> Sambungkan & Buat Tabel
      </button>
    </form>
  <?php endif; ?>

  <div class="mt-6 pt-4 border-t border-gray-100 text-center">
    <p class="text-[10px] text-gray-400">PHP <?= PHP_VERSION ?> · mysqli <?= extension_loaded('mysqli') ? 'aktif ✓' : 'TIDAK AKTIF ✗' ?> · fileinfo <?= extension_loaded('fileinfo') ? 'aktif ✓' : 'TIDAK AKTIF ✗' ?></p>
  </div>
</div>
</body>
</html>
