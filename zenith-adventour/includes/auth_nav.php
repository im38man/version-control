<?php
/**
 * Blok tombol akun (Favorit, Pesan, Konfirmasi Pembayaran, Masuk/Daftar/Keluar).
 * Disisipkan sebagai elemen TERAKHIR di dalam <div class="nav-right-container">
 * (sejajar dengan .nav-links dan .nav-socials), sehingga otomatis:
 * - Desktop (nav-right-container: flex row)  -> berada PALING KANAN
 * - Mobile  (nav-right-container: flex column saat menu dibuka) -> berada PALING BAWAH
 * Style dibuat mandiri (self-contained) agar konsisten di semua halaman.
 */
if (!function_exists('is_logged_in')) {
    require_once __DIR__ . '/session.php';
}
?>
<style>
.auth-nav-block {
    display: flex;
    align-items: center;
    gap: 16px;
    border-left: 1px solid #e5e0d8;
    padding-left: 20px;
}
.auth-nav-block a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #1a2f27;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    white-space: nowrap;
    transition: color 0.3s;
}
.auth-nav-block a:hover { color: #c5a880; }
.auth-nav-block .icon-only { font-size: 17px; }
.auth-nav-block .btn-auth-solid {
    background: #1a2f27;
    color: #fff !important;
    padding: 8px 16px;
    border-radius: 20px;
}
.auth-nav-block .btn-auth-solid:hover { background: #c5a880; }
.auth-nav-block .btn-auth-outline {
    border: 1px solid #1a2f27;
    padding: 7px 15px;
    border-radius: 20px;
}
.auth-nav-block .btn-auth-outline:hover { border-color: #c5a880; color: #c5a880; }

@media (max-width: 991px) {
    .auth-nav-block {
        border-left: none;
        padding-left: 0;
        border-top: 1px solid #f3eee7;
        padding-top: 18px;
        width: 100%;
        justify-content: center;
        flex-wrap: wrap;
        gap: 14px 18px;
    }
}
</style>
<div class="auth-nav-block">
    <a href="favorit-saya.php" title="Favorit Saya"><i class="fa-solid fa-heart icon-only"></i><span>Favorit</span></a>
    <a href="pesan.php" title="Pesan Admin"><i class="fa-solid fa-comment-dots icon-only"></i><span>Pesan</span></a>
    <a href="payment-confirm.php" title="Konfirmasi Pembayaran"><i class="fa-solid fa-money-check-dollar icon-only"></i><span>Pembayaran</span></a>
    <a href="testimoni.php" title="Testimoni"><i class="fa-solid fa-star icon-only"></i><span>Testimoni</span></a>

    <?php if (is_logged_in()): ?>
        <?php if (is_admin()): ?>
            <a href="admin/index.php" title="Dashboard Admin"><i class="fa-solid fa-gauge icon-only"></i><span>Admin</span></a>
        <?php endif; ?>
        <a href="akun.php" class="auth-user" title="Pengaturan Akun"><i class="fa-solid fa-user icon-only"></i> <?= h($_SESSION['user_name']) ?></a>
        <a href="logout.php" class="btn-auth-outline">Keluar</a>
    <?php else: ?>
        <a href="login.php" class="btn-auth-outline">Masuk</a>
        <a href="register.php" class="btn-auth-solid">Daftar</a>
    <?php endif; ?>
</div>
