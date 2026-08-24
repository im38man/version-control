<?php
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/includes/pagination.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = (int)($_POST['booking_id'] ?? 0);
    $status_baru = $_POST['status_keberangkatan'] ?? '';

    if ($booking_id && in_array($status_baru, ['belum_berangkat', 'proses', 'selesai'])) {
        $stmt = mysqli_prepare($koneksi, "UPDATE bookings SET status_keberangkatan = ? WHERE id = ? AND status = 'dikonfirmasi'");
        mysqli_stmt_bind_param($stmt, 'si', $status_baru, $booking_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $msg = 'Status pemberangkatan berhasil diperbarui.';
    }
}

// Hanya booking yang pembayarannya sudah dikonfirmasi yang bisa diatur pemberangkatannya
$total_rows = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM bookings WHERE status = 'dikonfirmasi'"))['c'];
$offset = get_page_offset();

$bookings = mysqli_query($koneksi, "
    SELECT b.*, u.name AS nama_akun, u.email AS email_akun,
        (SELECT id FROM testimonials t WHERE t.booking_id = b.id) as has_testimoni
    FROM bookings b
    LEFT JOIN users u ON b.user_id = u.id
    WHERE b.status = 'dikonfirmasi'
    ORDER BY
        FIELD(b.status_keberangkatan, 'proses', 'belum_berangkat', 'selesai'),
        b.created_at DESC
    LIMIT " . ADMIN_PER_PAGE . " OFFSET $offset
");

$page_title = 'Pemberangkatan';
$active_menu = 'keberangkatan';
require __DIR__ . '/includes/admin_header.php';
?>
<h1>Pemberangkatan</h1>
<p class="sub">Kelola status pemberangkatan untuk booking yang pembayarannya sudah dikonfirmasi. Setelah status diubah menjadi <strong>Selesai</strong>, pelanggan bisa menulis testimoni (khusus pelanggan yang punya akun).</p>

<?php if ($msg): ?><div class="alert alert-success"><?= h($msg) ?></div><?php endif; ?>

<div class="card">
<div class="table-scroll">
<table>
    <tr><th>Kode</th><th>Pelanggan</th><th>Destinasi</th><th>Peserta</th><th>Status Keberangkatan</th><th>Testimoni</th><th>Aksi</th></tr>
    <?php if ($total_rows === 0): ?>
        <tr><td colspan="7" style="color:#999;">Belum ada booking dengan pembayaran dikonfirmasi.</td></tr>
    <?php endif; ?>
    <?php while ($b = mysqli_fetch_assoc($bookings)): ?>
    <?php
        $label = [
            'belum_berangkat' => ['#c98a1f','#fdf3e2','Belum Berangkat'],
            'proses'          => ['#1f6fc9','#e5f0fd','Dalam Proses'],
            'selesai'         => ['#237040','#e7f5ec','Selesai'],
        ][$b['status_keberangkatan']];
        $nama_tampil = $b['user_id'] ? $b['nama_akun'] : $b['nama_pelanggan'];
        $email_tampil = $b['user_id'] ? $b['email_akun'] : ($b['email_pelanggan'] ?: 'Tanpa akun');
    ?>
    <tr>
        <td><?= h($b['kode_booking']) ?></td>
        <td><?= h($nama_tampil) ?><br><small style="color:#999;"><?= h($email_tampil) ?></small></td>
        <td><?= h($b['destinasi']) ?></td>
        <td><?= (int)$b['jumlah_peserta'] ?></td>
        <td><span class="badge" style="color:<?= $label[0] ?>;background:<?= $label[1] ?>;"><?= $label[2] ?></span></td>
        <td>
            <?php if ($b['has_testimoni']): ?>
                <span class="badge" style="color:#237040;background:#e7f5ec;">Sudah Mengisi</span>
            <?php elseif (!$b['user_id']): ?>
                <span style="color:#ccc;font-size:12px;">Tanpa akun</span>
            <?php elseif ($b['status_keberangkatan'] === 'selesai'): ?>
                <span class="badge" style="color:#c98a1f;background:#fdf3e2;">Menunggu Diisi</span>
            <?php else: ?>
                <span style="color:#ccc;font-size:12px;">-</span>
            <?php endif; ?>
        </td>
        <td>
            <form method="POST" style="display:flex;gap:6px;">
                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                <select name="status_keberangkatan" style="padding:6px 8px;border:1px solid #ddd;border-radius:6px;font-size:12px;">
                    <option value="belum_berangkat" <?= $b['status_keberangkatan']==='belum_berangkat'?'selected':'' ?>>Belum Berangkat</option>
                    <option value="proses" <?= $b['status_keberangkatan']==='proses'?'selected':'' ?>>Dalam Proses</option>
                    <option value="selesai" <?= $b['status_keberangkatan']==='selesai'?'selected':'' ?>>Selesai</option>
                </select>
                <button type="submit" class="btn btn-view">Simpan</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
</div>
<?php render_pagination($total_rows); ?>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
