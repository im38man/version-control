<?php
require __DIR__ . '/config/db.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = 'Semua field wajib diisi.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email sudah terdaftar. Silakan login.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "user")');
        $stmt->execute([$name, $email, $hash]);

        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = 'user';

        header('Location: index.php');
        exit;
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="p-5">
  <div class="container">
    <div class="row justify-content-center my-5">
      <div class="col-md-5">
        <h2 class="display-4 mb-4">Sign Up</h2>

        <?php foreach ($errors as $err): ?>
          <div class="alert alert-danger rounded-4"><?= h($err) ?></div>
        <?php endforeach; ?>

        <form method="POST">
          <div class="mb-3">
            <input type="text" class="form-control p-3 rounded-4" name="name" placeholder="your name"
              value="<?= h($_POST['name'] ?? '') ?>" required />
          </div>
          <div class="mb-3">
            <input type="email" class="form-control p-3 rounded-4" name="email" placeholder="your email"
              value="<?= h($_POST['email'] ?? '') ?>" required />
          </div>
          <div class="mb-3">
            <input type="password" class="form-control p-3 rounded-4" name="password" placeholder="password" required />
          </div>
          <div class="mb-3">
            <input type="password" class="form-control p-3 rounded-4" name="confirm_password" placeholder="confirm password" required />
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-dark btn-lg text-uppercase rounded-4">Daftar</button>
          </div>
        </form>
        <p class="mt-3">Sudah punya akun? <a href="login.php">Login di sini</a></p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
