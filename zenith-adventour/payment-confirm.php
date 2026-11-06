<?php
require_once __DIR__ . '/includes/session.php';
require_login('payment-confirm.php');

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$destinasi_list = [
    'Pesona Dataran Tinggi Bandung' => 'bandung',
    'Keagungan Budaya Klasik Yogyakarta' => 'yogyakarta',
    'Pesona Tropis Nusa Penida Bali' => 'bali',
    'Pesona Alam Malang & Sunrise Bromo' => 'malang',
    'Eksotika Metropolitan Jakarta' => 'jakarta',
];

// Prefill dari tombol "Pesan Langsung di Web" di halaman paket (opsional)
$prefill_destinasi = trim($_GET['destinasi'] ?? '');
$prefill_jumlah = (int)($_GET['jumlah'] ?? 0);
if ($prefill_jumlah < 1) $prefill_jumlah = 1;

$upload_dir = __DIR__ . '/uploads/payments/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destinasi = trim($_POST['destinasi'] ?? '');
    $jumlah = (int)($_POST['jumlah'] ?? 1);
    $telepon = trim($_POST['telepon'] ?? '');
    $nominal = trim($_POST['nominal'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');

    $nominal_clean = preg_replace('/[^0-9]/', '', $nominal);

    if ($destinasi === '' || $telepon === '' || $nominal_clean === '') {
        $error = 'Mohon lengkapi semua data yang wajib diisi.';
    } elseif (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Bukti transfer wajib diunggah.';
    } else {
        $file = $_FILES['bukti'];
        $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) {
            $error = 'Format file bukti harus JPG, PNG, atau PDF.';
        } elseif ($file['size'] > 3 * 1024 * 1024) {
            $error = 'Ukuran file bukti maksimal 3MB.';
        } else {
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $safe_name = 'bukti_' . $user_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest_path = $upload_dir . $safe_name;

            if (move_uploaded_file($file['tmp_name'], $dest_path)) {
                mysqli_begin_transaction($koneksi);
                try {
                    $kode = 'ZTB-' . strtoupper(bin2hex(random_bytes(3)));
                    $stmt = mysqli_prepare($koneksi, 'INSERT INTO bookings (user_id, kode_booking, destinasi, jumlah_peserta, telepon, status) VALUES (?, ?, ?, ?, ?, "menunggu_konfirmasi")');
                    mysqli_stmt_bind_param($stmt, 'issis', $user_id, $kode, $destinasi, $jumlah, $telepon);
                    mysqli_stmt_execute($stmt);
                    $booking_id = mysqli_insert_id($koneksi);
                    mysqli_stmt_close($stmt);

                    $stmt2 = mysqli_prepare($koneksi, 'INSERT INTO payments (booking_id, user_id, nominal, bukti_file, catatan, status) VALUES (?, ?, ?, ?, ?, "pending")');
                    mysqli_stmt_bind_param($stmt2, 'iidss', $booking_id, $user_id, $nominal_clean, $safe_name, $catatan);
                    mysqli_stmt_execute($stmt2);
                    mysqli_stmt_close($stmt2);

                    mysqli_commit($koneksi);
                    $success = "Konfirmasi pembayaran berhasil dikirim! Kode booking Anda: $kode. Tim kami akan memverifikasi dalam 1x24 jam.";
                } catch (Exception $e) {
                    mysqli_rollback($koneksi);
                    $error = 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.';
                }
            } else {
                $error = 'Gagal mengunggah file bukti transfer.';
            }
        }
    }
}

// Ambil riwayat konfirmasi pembayaran milik user
$stmt = mysqli_prepare($koneksi, 'SELECT p.*, b.kode_booking, b.destinasi FROM payments p JOIN bookings b ON p.booking_id = b.id WHERE p.user_id = ? ORDER BY p.created_at DESC');
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$riwayat = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$status_label = [
    'pending' => ['Menunggu Verifikasi', '#c98a1f', '#fdf3e2'],
    'disetujui' => ['Disetujui', '#237040', '#e7f5ec'],
    'ditolak' => ['Ditolak', '#a33', '#fdeaea'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Konfirmasi Pembayaran - Zenith Tour & Travel</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Poppins',sans-serif; background:#fcfbf7; color:#333; }
.wrap { max-width:900px; margin:0 auto; padding:50px 20px; }
.top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; }
.top-bar a { color:#1a2f27; font-size:14px; }
h1 { font-family:'Playfair Display',serif; color:#1a2f27; margin-bottom:10px; }
.bank-info { background:#1a2f27; color:#fff; padding:20px 25px; border-radius:10px; margin-bottom:30px; }
.bank-info h3 { font-size:15px; margin-bottom:10px; color:#c5a880; }
.bank-info p { font-size:14px; margin-bottom:4px; }
.card { background:#fff; padding:30px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.05); margin-bottom:30px; }
.form-group { margin-bottom:16px; }
.form-group label { display:block; font-size:13px; font-weight:500; margin-bottom:6px; color:#444; }
.form-group input, .form-group select, .form-group textarea { width:100%; padding:12px 14px; border:1px solid #e5e0d8; border-radius:8px; font-size:14px; font-family:inherit; }
button[type=submit] { padding:13px 30px; background:#1a2f27; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:500; cursor:pointer; }
button[type=submit]:hover { background:#c5a880; }
.alert { padding:12px 14px; border-radius:8px; font-size:13px; margin-bottom:18px; }
.alert-error { background:#fdeaea; color:#a33; }
.alert-success { background:#e7f5ec; color:#237040; }
table { width:100%; border-collapse:collapse; font-size:13px; min-width:520px; }
th, td { text-align:left; padding:10px; border-bottom:1px solid #f0ede6; }
.badge { padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; white-space:nowrap; }

/* Riwayat konfirmasi: tabel biasa di desktop, jadi kartu bertumpuk di layar kecil */
@media (max-width: 700px) {
    .riwayat-table { min-width:0; }
    .riwayat-table thead { display:none; }
    .riwayat-table, .riwayat-table tbody, .riwayat-table tr, .riwayat-table td { display:block; width:100%; }
    .riwayat-table tr { margin-bottom:12px; border:1px solid #f0ede6; border-radius:10px; padding:6px 14px; }
    .riwayat-table td { border:none; padding:8px 0; display:flex; justify-content:space-between; align-items:center; gap:10px; border-bottom:1px solid #f7f4ee; }
    .riwayat-table td:last-child { border-bottom:none; }
    .riwayat-table td::before { content: attr(data-label); font-weight:600; color:#888; font-size:12px; }
}
@media (max-width: 480px) {
    .wrap { padding:30px 14px; }
    .top-bar { flex-direction:column; align-items:flex-start; gap:8px; }
    .card { padding:20px; }
}
</style>
</head>
<body>
<div class="wrap">
    <div class="top-bar">
        <a href="index.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
        <span>Halo, <?= h($_SESSION['user_name']) ?></span>
    </div>
    <h1>Konfirmasi Pembayaran</h1>
    <p style="color:#777;margin-bottom:25px;">Sudah transfer? Isi form di bawah agar tim kami dapat memverifikasi pembayaran Anda.</p>

    <div class="bank-info">
        <h3>Transfer ke Rekening Resmi Kami</h3>
        <p>Bank BCA - 123 456 7890 a.n. PT Zenith Adventour Indonesia</p>
        <p>Bank Mandiri - 987 654 3210 a.n. PT Zenith Adventour Indonesia</p>
    </div>

    <div class="card">
        <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>
        <?php if ($prefill_destinasi !== '' && isset($destinasi_list[$prefill_destinasi]) && !$success): ?>
            <div class="alert" style="background:#eef6ff;color:#1f6fc9;">
                <i class="fa-solid fa-circle-info"></i> Form sudah terisi otomatis untuk paket <strong><?= h($prefill_destinasi) ?></strong>. Silakan lengkapi data transfer di bawah.
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Pilih Paket Destinasi</label>
                <select name="destinasi" required>
                    <option value="" disabled <?= $prefill_destinasi === '' ? 'selected' : '' ?>>-- Pilih Paket --</option>
                    <?php foreach ($destinasi_list as $nama => $slug): ?>
                        <option value="<?= h($nama) ?>" <?= $prefill_destinasi === $nama ? 'selected' : '' ?>><?= h($nama) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Jumlah Peserta</label>
                <input type="number" name="jumlah" min="1" value="<?= $prefill_jumlah ?>" required>
            </div>
            <div class="form-group">
                <label>Nomor WhatsApp Aktif</label>
                <input type="tel" name="telepon" placeholder="08123456xxx" required>
            </div>
            <div class="form-group">
                <label>Nominal Transfer (Rp)</label>
                <input type="text" name="nominal" placeholder="Contoh: 2200000" required>
            </div>
            <div class="form-group">
                <label>Unggah Bukti Transfer (JPG/PNG/PDF, maks 3MB)</label>
                <input type="file" name="bukti" accept=".jpg,.jpeg,.png,.pdf" required>
            </div>
            <div class="form-group">
                <label>Catatan (opsional)</label>
                <textarea name="catatan" rows="3"></textarea>
            </div>
            <button type="submit">Kirim Konfirmasi Pembayaran</button>
        </form>
    </div>

    <div class="card">
        <h3 style="font-family:'Playfair Display',serif;color:#1a2f27;margin-bottom:15px;">Riwayat Konfirmasi Saya</h3>
        <?php if (empty($riwayat)): ?>
            <p style="color:#999;font-size:13px;">Belum ada riwayat konfirmasi pembayaran.</p>
        <?php else: ?>
        <div style="overflow-x:auto;">
        <table class="riwayat-table">
            <thead>
            <tr><th>Kode Booking</th><th>Destinasi</th><th>Nominal</th><th>Status</th><th>Tanggal</th></tr>
            </thead>
            <tbody>
            <?php foreach ($riwayat as $r): $s = $status_label[$r['status']]; ?>
            <tr>
                <td data-label="Kode Booking"><?= h($r['kode_booking']) ?></td>
                <td data-label="Destinasi"><?= h($r['destinasi']) ?></td>
                <td data-label="Nominal">Rp <?= number_format($r['nominal'], 0, ',', '.') ?></td>
                <td data-label="Status"><span class="badge" style="color:<?= $s[1] ?>;background:<?= $s[2] ?>;"><?= $s[0] ?></span></td>
                <td data-label="Tanggal"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
