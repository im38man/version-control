<?php
require_once __DIR__ . '/includes/admin_auth.php';

$total_users = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM users WHERE role='user'"))['c'];
$total_bookings = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM bookings"))['c'];
$pending_payments = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM payments WHERE status='pending'"))['c'];
$total_favorites = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM favorites"))['c'];
$unread_pesan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM pesan_messages WHERE sender='user' AND is_read=0"))['c'];
$proses_keberangkatan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM bookings WHERE status='dikonfirmasi' AND status_keberangkatan='proses'"))['c'];
$total_testimoni = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM testimonials"))['c'];

$latest_payments = mysqli_query($koneksi, "SELECT p.*, b.kode_booking, b.destinasi, u.name FROM payments p
    JOIN bookings b ON p.booking_id = b.id JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC LIMIT 5");

$page_title = 'Dashboard';
$active_menu = 'dashboard';
require __DIR__ . '/includes/admin_header.php';
?>
<div class="topbar">
    <div>
        <h1>Dashboard</h1>
        <p class="sub">Ringkasan aktivitas Zenith Tour & Travel</p>
    </div>
    <div class="who">Halo, <?= h($_SESSION['user_name']) ?></div>
</div>

<div class="stat-grid">
    <div class="stat-card"><div class="num"><?= $total_users ?></div><div class="label">Total Pengguna</div></div>
    <div class="stat-card"><div class="num"><?= $total_bookings ?></div><div class="label">Total Booking</div></div>
    <div class="stat-card"><div class="num"><?= $pending_payments ?></div><div class="label">Pembayaran Menunggu Verifikasi</div></div>
    <div class="stat-card"><div class="num"><?= $total_favorites ?></div><div class="label">Total Favorit Tersimpan</div></div>
    <div class="stat-card"><div class="num"><?= $unread_pesan ?></div><div class="label">Pesan Belum Dibalas</div></div>
    <div class="stat-card"><div class="num"><?= $proses_keberangkatan ?></div><div class="label">Pemberangkatan Dalam Proses</div></div>
    <div class="stat-card"><div class="num"><?= $total_testimoni ?></div><div class="label">Total Testimoni Masuk</div></div>
</div>

<div class="card">
    <h3 style="font-family:'Playfair Display',serif;color:#1a2f27;margin-bottom:15px;">Konfirmasi Pembayaran Terbaru</h3>
    <div class="table-scroll">
    <table>
        <tr><th>Kode</th><th>Nama</th><th>Destinasi</th><th>Nominal</th><th>Status</th><th></th></tr>
        <?php while ($p = mysqli_fetch_assoc($latest_payments)): ?>
        <tr>
            <td><?= h($p['kode_booking']) ?></td>
            <td><?= h($p['name']) ?></td>
            <td><?= h($p['destinasi']) ?></td>
            <td>Rp <?= number_format($p['nominal'], 0, ',', '.') ?></td>
            <td>
                <?php
                $c = ['pending'=>['#c98a1f','#fdf3e2','Menunggu'],'disetujui'=>['#237040','#e7f5ec','Disetujui'],'ditolak'=>['#a33','#fdeaea','Ditolak']][$p['status']];
                ?>
                <span class="badge" style="color:<?= $c[0] ?>;background:<?= $c[1] ?>;"><?= $c[2] ?></span>
            </td>
            <td><a class="btn btn-view" href="payments.php">Kelola</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
    </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
