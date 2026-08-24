<?php
require __DIR__ . '/config/db.php';

// Kunci halaman ini kalau sudah ada admin — cegah orang lain bikin admin baru
$adminExists = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();

$errors = [];
$success = false;

if (!$adminExists && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = 'Semua field wajib diisi.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password admin minimal 8 karakter.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email sudah dipakai.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "admin")');
        $stmt->execute([$name, $email, $hash]);
        $success = true;
        $adminExists = true;
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="p-5">
  <div class="container">
    <div class="row justify-content-center my-5">
      <div class="col-md-5">
        <h2 class="display-4 mb-4">Setup Admin</h2>

        <?php if ($success): ?>
          <div class="alert alert-success rounded-4">
            Akun admin berhasil dibuat. Silakan <a href="login.php">login di sini</a>.
          </div>
        <?php elseif ($adminExists): ?>
          <div class="alert alert-warning rounded-4">
            Akun admin sudah pernah dibuat. Halaman ini terkunci demi keamanan.
            Hapus atau pindahkan file <code>setup-admin.php</code> dari server.
          </div>
        <?php else: ?>
          <?php foreach ($errors as $err): ?>
            <div class="alert alert-danger rounded-4"><?= h($err) ?></div>
          <?php endforeach; ?>
          <p class="text-dark-emphasis">Halaman ini hanya bisa dipakai satu kali untuk membuat akun admin pertama.</p>
          <form method="POST">
            <div class="mb-3">
              <input type="text" class="form-control p-3 rounded-4" name="name" placeholder="nama admin"
                value="<?= h($_POST['name'] ?? '') ?>" required />
            </div>
            <div class="mb-3">
              <input type="email" class="form-control p-3 rounded-4" name="email" placeholder="email admin"
                value="<?= h($_POST['email'] ?? '') ?>" required />
            </div>
            <div class="mb-3">
              <input type="password" class="form-control p-3 rounded-4" name="password" placeholder="password (min 8 karakter)" required />
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-dark btn-lg text-uppercase rounded-4">Buat Admin</button>
            </div>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
