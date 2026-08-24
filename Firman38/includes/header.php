<?php
// includes/header.php — dipanggil di setiap halaman SETELAH require config/db.php
$current = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="id">

<head>
  <title>Firman.</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
  <link rel="stylesheet" href="css/lightbox.min.css">
  <link rel="stylesheet" href="css/style.css?v=8">
</head>

<body>
  <?php include __DIR__ . '/svg-icons.php'; ?>

  <header id="top" class="position-sticky top-0 start-0" style="z-index:10;">
    <nav class="navbar bg-white fixed-top">
      <div class="container-fluid">
        <div class="d-flex align-items-center icon-social">
          <a class="navbar-brand d-flex" href="index.php">
            <img src="images/logo.png" class="img-fluid" id="logo">
          </a>
          <a href="#" class="text-decoration-none"><svg class="instagram" width="20" height="20"><use xlink:href="#instagram"></use></svg></a>
          <a href="#" class="text-decoration-none"><svg class="facebook" width="20" height="20"><use xlink:href="#facebook"></use></svg></a>
          <a href="#" class="text-decoration-none"><svg class="twitter" width="20" height="20"><use xlink:href="#twitter"></use></svg></a>
          <a href="#" class="text-decoration-none"><svg class="whatsapp" width="20" height="20"><use xlink:href="#whatsapp"></use></svg></a>
          <a href="#" class="text-decoration-none"><svg class="telegram" width="20" height="20"><use xlink:href="#telegram"></use></svg></a>
          <a href="#" class="text-decoration-none"><svg class="github" width="20" height="20"><use xlink:href="#github"></use></svg></a>
          <a href="#" class="text-decoration-none"><svg class="behance" width="20" height="20"><use xlink:href="#behance"></use></svg></a>
        </div>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas"
          data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
          <span class="navabar-toggler-icon">
            <svg class="text-primary menu" width="32" height="32"><use xlink:href="#menu"></use></svg>
          </span>
        </button>
        <div class="offcanvas offcanvas-end text-white bg-black" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
          <div class="offcanvas-header">
            <button type="button" class="btn-close btn-close-white ms-3" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body">
            <ul class="navbar-nav flex-grow-1 p-4">
              <li class="nav-item">
                <a class="nav-link <?= $current === 'index.php' ? 'active' : '' ?> text-uppercase ls-4 text-white" href="index.php">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $current === 'pesan.php' ? 'active' : '' ?> text-uppercase ls-4 text-white" href="pesan.php">
                  Message Me <?php $c = navMessageBadge($pdo); if ($c > 0) echo '<span class="badge bg-danger">' . $c . '</span>'; ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-uppercase ls-4 text-white" href="index.php">My Portofolio</a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-uppercase ls-4 text-white" href="index.php">Blog Spot</a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-uppercase ls-4 text-white" href="index.php">Project</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $current === 'community.php' ? 'active' : '' ?> text-uppercase ls-4 text-white" href="community.php">Community</a>
              </li>
              <?php if (isLoggedIn()): ?>
              <li class="nav-item">
                <span class="nav-link text-uppercase ls-4 text-white-50" style="cursor:default;">
                  Hai, <?= h($_SESSION['name']) ?>
                </span>
              </li>
              <li class="nav-item">
                <a class="nav-link text-uppercase ls-4 text-white" href="logout.php">Logout</a>
              </li>
              <?php else: ?>
              <li class="nav-item">
                <a class="nav-link <?= $current === 'login.php' ? 'active' : '' ?> text-uppercase ls-4 text-white" href="login.php">Login</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $current === 'register.php' ? 'active' : '' ?> text-uppercase ls-4 text-white" href="register.php">Sign Up</a>
              </li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
    </nav>
  </header>
