<?php
require __DIR__ . '/config/db.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Email atau password salah.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            header('Location: ' . ($user['role'] === 'admin' ? 'pesan.php' : 'index.php'));
            exit;
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="p-5">
  <div class="container">
    <div class="row justify-content-center my-5">
      <div class="col-md-5">
        <h2 class="display-4 mb-4">Login</h2>

        <?php foreach ($errors as $err): ?>
          <div class="alert alert-danger rounded-4"><?= h($err) ?></div>
        <?php endforeach; ?>

        <form method="POST">
          <div class="mb-3">
            <input type="email" class="form-control p-3 rounded-4" name="email" placeholder="your email"
              value="<?= h($_POST['email'] ?? '') ?>" required />
          </div>
          <div class="mb-3">
            <input type="password" class="form-control p-3 rounded-4" name="password" placeholder="password" required />
          </div>
          <div class="d-grid">
            <button type="submit" class="btn btn-dark btn-lg text-uppercase rounded-4">Masuk</button>
          </div>
        </form>
        <p class="mt-3">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
