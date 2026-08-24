<?php
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/includes/pagination.php';

$popular = mysqli_query($koneksi, "SELECT destination_title, COUNT(*) jml FROM favorites GROUP BY destination_title ORDER BY jml DESC");

$total_rows = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM favorites"))['c'];
$offset = get_page_offset();
$recent = mysqli_query($koneksi, "SELECT f.*, u.name FROM favorites f JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC LIMIT " . ADMIN_PER_PAGE . " OFFSET $offset");

$page_title = 'Favorit';
$active_menu = 'favorites';
require __DIR__ . '/includes/admin_header.php';
?>
<h1>Destinasi Favorit</h1>
<p class="sub">Statistik destinasi yang paling banyak difavoritkan pelanggan.</p>

<div class="card">
    <h3 style="font-family:'Playfair Display',serif;color:#1a2f27;margin-bottom:15px;">Destinasi Terpopuler</h3>
    <div class="table-scroll">
    <table>
        <tr><th>Destinasi</th><th>Jumlah Favorit</th></tr>
        <?php if (mysqli_num_rows($popular) === 0): ?>
            <tr><td colspan="2" style="color:#999;">Belum ada data favorit.</td></tr>
        <?php endif; ?>
        <?php while ($row = mysqli_fetch_assoc($popular)): ?>
        <tr><td><?= h($row['destination_title']) ?></td><td><?= $row['jml'] ?></td></tr>
        <?php endwhile; ?>
    </table>
    </div>
</div>

<div class="card">
    <h3 style="font-family:'Playfair Display',serif;color:#1a2f27;margin-bottom:15px;">Aktivitas Favorit Terbaru</h3>
    <div class="table-scroll">
    <table>
        <tr><th>Pengguna</th><th>Destinasi</th><th>Tanggal</th></tr>
        <?php if ($total_rows === 0): ?>
            <tr><td colspan="3" style="color:#999;">Belum ada aktivitas favorit.</td></tr>
        <?php endif; ?>
        <?php while ($row = mysqli_fetch_assoc($recent)): ?>
        <tr>
            <td><?= h($row['name']) ?></td>
            <td><?= h($row['destination_title']) ?></td>
            <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    </div>
    <?php render_pagination($total_rows); ?>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
