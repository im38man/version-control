<?php
/**
 * Header bersama untuk seluruh halaman admin.
 * Panggil dengan: $active_menu = 'dashboard'; require __DIR__.'/includes/admin_header.php';
 */
$active_menu = $active_menu ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? h($page_title) . ' - ' : '' ?>Admin Zenith Tour & Travel</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
body { background:#f4f2ec; color:#333; }
.admin-layout { display:flex; min-height:100vh; }
.sidebar { width:230px; background:#1a2f27; color:#fff; padding:25px 0; flex-shrink:0; }
.sidebar .brand { font-family:'Playfair Display',serif; font-size:19px; padding:0 22px 25px; border-bottom:1px solid rgba(255,255,255,0.1); margin-bottom:15px; color:#c5a880; }
.sidebar a { display:flex; align-items:center; gap:12px; padding:12px 22px; color:rgba(255,255,255,0.75); font-size:14px; text-decoration:none; transition:background 0.2s; }
.sidebar a:hover, .sidebar a.active { background:rgba(255,255,255,0.08); color:#fff; }
.sidebar a i { width:18px; }
.content { flex:1; padding:35px 40px; max-width:1200px; }
.content h1 { font-family:'Playfair Display',serif; color:#1a2f27; margin-bottom:6px; font-size:26px; }
.content .sub { color:#888; font-size:14px; margin-bottom:28px; }
.stat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:18px; margin-bottom:30px; }
.stat-card { background:#fff; border-radius:12px; padding:22px; box-shadow:0 4px 15px rgba(0,0,0,0.04); }
.stat-card .num { font-size:28px; font-weight:600; color:#1a2f27; }
.stat-card .label { font-size:13px; color:#888; margin-top:4px; }
.card { background:#fff; border-radius:12px; padding:26px; box-shadow:0 4px 15px rgba(0,0,0,0.04); margin-bottom:25px; }
table { width:100%; border-collapse:collapse; font-size:13px; }
th { text-align:left; padding:10px; background:#faf8f3; color:#555; font-weight:600; }
td { padding:10px; border-bottom:1px solid #f0ede6; }
.badge { padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; white-space:nowrap; }
.btn { display:inline-block; padding:7px 14px; border-radius:6px; font-size:12px; border:none; cursor:pointer; text-decoration:none; }
.btn-approve { background:#237040; color:#fff; }
.btn-reject { background:#a33; color:#fff; }
.btn-view { background:#1a2f27; color:#fff; }
.alert { padding:12px 16px; border-radius:8px; font-size:13px; margin-bottom:18px; }
.alert-success { background:#e7f5ec; color:#237040; }
.alert-error { background:#fdeaea; color:#a33; }
.topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; }
.topbar .who { font-size:13px; color:#666; }
.topbar a.logout { color:#a33; font-size:13px; }

/* Pagination */
.pagination { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-top:18px; padding-top:16px; border-top:1px solid #f0ede6; }
.pagination-info { font-size:12px; color:#999; }
.pagination-links { display:flex; gap:4px; flex-wrap:wrap; }
.page-link { display:inline-flex; align-items:center; justify-content:center; min-width:32px; height:32px; padding:0 8px; border-radius:6px; background:#f4f2ec; color:#1a2f27; text-decoration:none; font-size:12px; font-weight:500; }
.page-link:hover { background:#e5e0d8; }
.page-link.active { background:#1a2f27; color:#fff; }
.page-dots { padding:0 4px; color:#999; font-size:12px; align-self:center; }

/* Responsif: tabel bisa digeser horizontal di layar sempit */
.table-scroll { overflow-x:auto; -webkit-overflow-scrolling:touch; }
table { min-width:640px; }

/* Responsif: sidebar admin jadi menu mendatar & bisa digulir di HP/tablet */
@media (max-width: 900px) {
    .admin-layout { flex-direction:column; }
    .sidebar { width:100%; padding:16px 0; }
    .sidebar .brand { padding:0 18px 12px; }
    .sidebar .role-badge-wrap { padding:0 18px 12px; }
    .sidebar-links { display:flex; overflow-x:auto; -webkit-overflow-scrolling:touch; gap:2px; padding:0 12px 4px; }
    .sidebar-links a { flex-shrink:0; white-space:nowrap; padding:10px 14px; border-radius:8px; }
    .content { padding:22px 16px; max-width:100%; }
    .content h1 { font-size:21px; }
    .stat-grid { grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:12px; }
    .stat-card { padding:16px; }
    .stat-card .num { font-size:22px; }
    .card { padding:16px; }
}
@media (max-width: 480px) {
    .content { padding:16px 12px; }
    .pagination { flex-direction:column; align-items:flex-start; }
}
</style>
</head>
<body>
<div class="admin-layout">
    <aside class="sidebar">
        <div class="brand">Zenith Admin</div>
        <div class="role-badge-wrap" style="padding:0 22px 15px;">
            <span class="badge" style="color:<?= is_master_admin() ? '#8a5a00' : '#c5a880' ?>;background:<?= is_master_admin() ? '#fdf0d8' : 'rgba(255,255,255,0.08)' ?>;font-size:11px;">
                <?php if (is_master_admin()): ?><i class="fa-solid fa-crown"></i> Admin Master<?php else: ?>Admin<?php endif; ?>
            </span>
        </div>
        <div class="sidebar-links">
        <a href="index.php" class="<?= $active_menu === 'dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="bookings.php" class="<?= $active_menu === 'bookings' ? 'active' : '' ?>"><i class="fa-solid fa-suitcase-rolling"></i> Booking</a>
        <a href="booking-wa.php" class="<?= $active_menu === 'booking-wa' ? 'active' : '' ?>"><i class="fa-brands fa-whatsapp"></i> Booking via WA</a>
        <a href="payments.php" class="<?= $active_menu === 'payments' ? 'active' : '' ?>"><i class="fa-solid fa-money-check-dollar"></i> Konfirmasi Pembayaran</a>
        <a href="keberangkatan.php" class="<?= $active_menu === 'keberangkatan' ? 'active' : '' ?>"><i class="fa-solid fa-plane-departure"></i> Pemberangkatan</a>
        <a href="testimoni.php" class="<?= $active_menu === 'testimoni' ? 'active' : '' ?>"><i class="fa-solid fa-star"></i> Testimoni</a>
        <a href="pesan.php" class="<?= $active_menu === 'pesan' ? 'active' : '' ?>"><i class="fa-solid fa-comment-dots"></i> Pesan Pelanggan</a>
        <a href="users.php" class="<?= $active_menu === 'users' ? 'active' : '' ?>"><i class="fa-solid fa-users"></i> Pengguna</a>
        <a href="favorites.php" class="<?= $active_menu === 'favorites' ? 'active' : '' ?>"><i class="fa-solid fa-heart"></i> Favorit</a>
        <a href="../index.php"><i class="fa-solid fa-arrow-left"></i> Lihat Situs</a>
        <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
        </div>
    </aside>
    <main class="content">
