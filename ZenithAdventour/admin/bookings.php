<?php
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/includes/pagination.php';

$total_rows = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM bookings"))['c'];
$offset = get_page_offset();

$bookings = mysqli_query($koneksi, "SELECT b.*, u.name AS nama_akun, u.email AS email_akun
    FROM bookings b LEFT JOIN users u ON b.user_id = u.id
    ORDER BY b.created_at DESC
    LIMIT " . ADMIN_PER_PAGE . " OFFSET $offset");

$page_title = 'Booking';
$active_menu = 'bookings';
require __DIR__ . '/includes/admin_header.php';
?>
<div class="topbar">
    <div>
        <h1>Daftar Booking</h1>
        <p class="sub">Semua booking, baik dari web maupun input manual via WhatsApp.</p>
    </div>
    <a href="booking-wa.php" class="btn btn-view"><i class="fa-brands fa-whatsapp"></i> Tambah Booking via WA</a>
</div>

<div class="card">
<div class="table-scroll">
<table>
    <tr><th>Kode</th><th>Pelanggan</th><th>Sumber</th><th>Destinasi</th><th>Peserta</th><th>Telepon</th><th>Status Pembayaran</th><th>Pemberangkatan</th><th>Tanggal</th></tr>
    <?php if ($total_rows === 0): ?>
        <tr><td colspan="9" style="color:#999;">Belum ada booking.</td></tr>
    <?php endif; ?>
    <?php while ($b = mysqli_fetch_assoc($bookings)): ?>
    <?php
        $nama_tampil = $b['user_id'] ? $b['nama_akun'] : $b['nama_pelanggan'];
        $email_tampil = $b['user_id'] ? $b['email_akun'] : ($b['email_pelanggan'] ?: '-');
    ?>
    <tr>
        <td><?= h($b['kode_booking']) ?></td>
        <td><?= h($nama_tampil) ?><br><small style="color:#999;"><?= h($email_tampil) ?></small></td>
        <td>
            <?php if ($b['sumber'] === 'admin_wa'): ?>
                <span class="badge" style="color:#1f8a4c;background:#e3f6ea;"><i class="fa-brands fa-whatsapp"></i> WA Admin</span>
            <?php else: ?>
                <span class="badge" style="color:#555;background:#eee;"><i class="fa-solid fa-globe"></i> Web</span>
            <?php endif; ?>
        </td>
        <td><?= h($b['destinasi']) ?></td>
        <td><?= (int)$b['jumlah_peserta'] ?></td>
        <td><?= h($b['telepon']) ?></td>
        <td>
            <?php
            $labels = [
                'menunggu_pembayaran' => ['#c98a1f','#fdf3e2','Menunggu Pembayaran'],
                'menunggu_konfirmasi' => ['#1f6fc9','#e5f0fd','Menunggu Konfirmasi'],
                'dikonfirmasi' => ['#237040','#e7f5ec','Dikonfirmasi'],
                'ditolak' => ['#a33','#fdeaea','Ditolak'],
            ];
            $c = $labels[$b['status']];
            ?>
            <span class="badge" style="color:<?= $c[0] ?>;background:<?= $c[1] ?>;"><?= $c[2] ?></span>
        </td>
        <td>
            <?php if ($b['status'] === 'dikonfirmasi'):
                $kb = [
                    'belum_berangkat' => ['#c98a1f','#fdf3e2','Belum Berangkat'],
                    'proses' => ['#1f6fc9','#e5f0fd','Proses'],
                    'selesai' => ['#237040','#e7f5ec','Selesai'],
                ][$b['status_keberangkatan']];
            ?>
                <span class="badge" style="color:<?= $kb[0] ?>;background:<?= $kb[1] ?>;"><?= $kb[2] ?></span>
            <?php else: ?>
                <span style="color:#ccc;font-size:12px;">-</span>
            <?php endif; ?>
        </td>
        <td><?= date('d M Y', strtotime($b['created_at'])) ?></td>
    </tr>
    <?php if (!empty($b['catatan_admin'])): ?>
    <tr>
        <td></td>
        <td colspan="8" style="font-size:12px;color:#888;padding-top:0;"><i class="fa-solid fa-note-sticky"></i> <?= nl2br(h($b['catatan_admin'])) ?></td>
    </tr>
    <?php endif; ?>
    <?php endwhile; ?>
</table>
</div>
<?php render_pagination($total_rows); ?>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
