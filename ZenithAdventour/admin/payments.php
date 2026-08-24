<?php
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/includes/pagination.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = (int)($_POST['payment_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $catatan_admin = trim($_POST['catatan_admin'] ?? '');

    if ($payment_id && in_array($action, ['disetujui', 'ditolak'])) {
        $stmt = mysqli_prepare($koneksi, 'UPDATE payments SET status = ?, catatan_admin = ? WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'ssi', $action, $catatan_admin, $payment_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Sinkronkan status booking terkait
        $booking_status = $action === 'disetujui' ? 'dikonfirmasi' : 'ditolak';
        $stmt2 = mysqli_prepare($koneksi, 'UPDATE bookings b JOIN payments p ON b.id = p.booking_id SET b.status = ? WHERE p.id = ?');
        mysqli_stmt_bind_param($stmt2, 'si', $booking_status, $payment_id);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);

        $msg = 'Status pembayaran berhasil diperbarui.';
    }
}

$filter = $_GET['status'] ?? 'pending';
$allowed_filters = ['pending', 'disetujui', 'ditolak', 'semua'];
if (!in_array($filter, $allowed_filters)) $filter = 'pending';

$where = '';
if ($filter !== 'semua') {
    $where = " WHERE p.status = '" . mysqli_real_escape_string($koneksi, $filter) . "'";
}

$total_rows = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM payments p" . $where))['c'];
$offset = get_page_offset();

$sql = "SELECT p.*, b.kode_booking, b.destinasi, b.jumlah_peserta, b.telepon, u.name, u.email
        FROM payments p JOIN bookings b ON p.booking_id = b.id JOIN users u ON p.user_id = u.id"
        . $where . " ORDER BY p.created_at DESC LIMIT " . ADMIN_PER_PAGE . " OFFSET $offset";
$payments = mysqli_query($koneksi, $sql);

$page_title = 'Konfirmasi Pembayaran';
$active_menu = 'payments';
require __DIR__ . '/includes/admin_header.php';
?>
<h1>Konfirmasi Pembayaran</h1>
<p class="sub">Verifikasi bukti transfer yang dikirim pelanggan.</p>

<?php if ($msg): ?><div class="alert alert-success"><?= h($msg) ?></div><?php endif; ?>

<div style="margin-bottom:20px;">
    <?php foreach (['pending' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'semua' => 'Semua'] as $key => $label): ?>
        <a href="payments.php?status=<?= $key ?>" class="btn" style="background:<?= $filter === $key ? '#1a2f27' : '#eee' ?>;color:<?= $filter === $key ? '#fff' : '#333' ?>;margin-right:6px;"><?= $label ?></a>
    <?php endforeach; ?>
</div>

<div class="card">
<div class="table-scroll">
<table>
    <tr><th>Kode</th><th>Pelanggan</th><th>Destinasi</th><th>Peserta</th><th>Nominal</th><th>Bukti</th><th>Status</th><th>Aksi</th></tr>
    <?php if ($total_rows === 0): ?>
        <tr><td colspan="8" style="color:#999;">Tidak ada data pembayaran untuk filter ini.</td></tr>
    <?php endif; ?>
    <?php while ($p = mysqli_fetch_assoc($payments)): ?>
    <tr>
        <td><?= h($p['kode_booking']) ?></td>
        <td><?= h($p['name']) ?><br><small style="color:#999;"><?= h($p['telepon']) ?></small></td>
        <td><?= h($p['destinasi']) ?></td>
        <td><?= (int)$p['jumlah_peserta'] ?></td>
        <td>Rp <?= number_format($p['nominal'], 0, ',', '.') ?></td>
        <td><a href="../uploads/payments/<?= h($p['bukti_file']) ?>" target="_blank" class="btn btn-view">Lihat Bukti</a></td>
        <td>
            <?php $c = ['pending'=>['#c98a1f','#fdf3e2','Menunggu'],'disetujui'=>['#237040','#e7f5ec','Disetujui'],'ditolak'=>['#a33','#fdeaea','Ditolak']][$p['status']]; ?>
            <span class="badge" style="color:<?= $c[0] ?>;background:<?= $c[1] ?>;"><?= $c[2] ?></span>
            <?php if ($p['catatan_admin']): ?><br><small style="color:#999;"><?= h($p['catatan_admin']) ?></small><?php endif; ?>
        </td>
        <td>
            <?php if ($p['status'] === 'pending'): ?>
            <form method="POST" style="display:inline-block;" onsubmit="return confirm('Setujui pembayaran ini?');">
                <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                <input type="hidden" name="action" value="disetujui">
                <button type="submit" class="btn btn-approve">Setujui</button>
            </form>
            <form method="POST" style="display:inline-block;" onsubmit="return confirm('Tolak pembayaran ini?');">
                <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                <input type="hidden" name="action" value="ditolak">
                <button type="submit" class="btn btn-reject">Tolak</button>
            </form>
            <?php else: ?>
                <span style="color:#aaa;font-size:12px;">Sudah diproses</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
</div>
<?php render_pagination($total_rows, 'status=' . urlencode($filter) . '&'); ?>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
