<?php
require_once __DIR__ . '/includes/admin_auth.php';
require_once __DIR__ . '/includes/pagination.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    $stmt = mysqli_prepare($koneksi, 'DELETE FROM testimonials WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $delete_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $msg = 'Testimoni berhasil dihapus.';
}

$total_rows = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM testimonials"))['c'];
$offset = get_page_offset();

$testimonials = mysqli_query($koneksi, "
    SELECT t.*, u.name, u.email, b.kode_booking
    FROM testimonials t
    JOIN users u ON t.user_id = u.id
    JOIN bookings b ON t.booking_id = b.id
    ORDER BY t.created_at DESC
    LIMIT " . ADMIN_PER_PAGE . " OFFSET $offset
");

$page_title = 'Testimoni';
$active_menu = 'testimoni';
require __DIR__ . '/includes/admin_header.php';
?>
<h1>Testimoni Pelanggan</h1>
<p class="sub">Testimoni hanya bisa ditulis oleh pelanggan yang pemberangkatannya sudah ditandai <strong>Selesai</strong>.</p>

<?php if ($msg): ?><div class="alert alert-success"><?= h($msg) ?></div><?php endif; ?>

<div class="card">
<div class="table-scroll">
<table>
    <tr><th>Pelanggan</th><th>Destinasi</th><th>Kode Booking</th><th>Rating</th><th>Komentar</th><th>Tanggal</th><th>Aksi</th></tr>
    <?php if ($total_rows === 0): ?>
        <tr><td colspan="7" style="color:#999;">Belum ada testimoni masuk.</td></tr>
    <?php endif; ?>
    <?php while ($t = mysqli_fetch_assoc($testimonials)): ?>
    <tr>
        <td><?= h($t['name']) ?><br><small style="color:#999;"><?= h($t['email']) ?></small></td>
        <td><?= h($t['destinasi']) ?></td>
        <td><?= h($t['kode_booking']) ?></td>
        <td><?= str_repeat('★', (int)$t['rating']) . str_repeat('☆', 5 - (int)$t['rating']) ?></td>
        <td style="max-width:280px;"><?= nl2br(h($t['comment'])) ?></td>
        <td><?= date('d M Y', strtotime($t['created_at'])) ?></td>
        <td>
            <form method="POST" onsubmit="return confirm('Hapus testimoni ini?');">
                <input type="hidden" name="delete_id" value="<?= $t['id'] ?>">
                <button type="submit" class="btn btn-reject">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
</div>
<?php render_pagination($total_rows); ?>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
