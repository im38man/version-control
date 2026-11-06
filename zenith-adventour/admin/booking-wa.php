<?php
require_once __DIR__ . '/includes/admin_auth.php';

$error = '';
$success = '';

$destinasi_list = [
    'Pesona Dataran Tinggi Bandung',
    'Keagungan Budaya Klasik Yogyakarta',
    'Pesona Tropis Nusa Penida Bali',
    'Pesona Alam Malang & Sunrise Bromo',
    'Eksotika Metropolitan Jakarta',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipe_pelanggan = $_POST['tipe_pelanggan'] ?? 'manual';
    $existing_user_id = (int)($_POST['existing_user_id'] ?? 0);
    $nama_pelanggan = trim($_POST['nama_pelanggan'] ?? '');
    $email_pelanggan = trim($_POST['email_pelanggan'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $destinasi = trim($_POST['destinasi'] ?? '');
    $destinasi_custom = trim($_POST['destinasi_custom'] ?? '');
    $jumlah = (int)($_POST['jumlah'] ?? 1);
    $status = $_POST['status'] ?? 'dikonfirmasi';
    $catatan_admin = trim($_POST['catatan_admin'] ?? '');

    if ($destinasi === '__lainnya__') {
        $destinasi = $destinasi_custom;
    }
    if ($jumlah < 1) $jumlah = 1;
    if (!in_array($status, ['menunggu_pembayaran', 'menunggu_konfirmasi', 'dikonfirmasi', 'ditolak'])) {
        $status = 'dikonfirmasi';
    }

    $user_id_final = null;
    $nama_final = null;
    $email_final = null;

    if ($tipe_pelanggan === 'existing') {
        if (!$existing_user_id) {
            $error = 'Pilih akun pelanggan terdaftar terlebih dahulu.';
        } else {
            $stmt = mysqli_prepare($koneksi, "SELECT id, name, email FROM users WHERE id = ? AND role = 'user'");
            mysqli_stmt_bind_param($stmt, 'i', $existing_user_id);
            mysqli_stmt_execute($stmt);
            $u = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);
            if (!$u) {
                $error = 'Akun pelanggan tidak ditemukan.';
            } else {
                $user_id_final = $u['id'];
            }
        }
    } else {
        if ($nama_pelanggan === '') {
            $error = 'Nama pelanggan wajib diisi.';
        } else {
            $nama_final = $nama_pelanggan;
            $email_final = $email_pelanggan !== '' ? $email_pelanggan : null;
        }
    }

    if (!$error && $destinasi === '') {
        $error = 'Destinasi wajib diisi.';
    }
    if (!$error && $telepon === '') {
        $error = 'Nomor telepon wajib diisi.';
    }

    if (!$error) {
        $kode = 'ZTB-' . strtoupper(bin2hex(random_bytes(3)));
        $admin_id = $_SESSION['user_id'];

        $stmt = mysqli_prepare($koneksi, "INSERT INTO bookings
            (user_id, nama_pelanggan, email_pelanggan, kode_booking, destinasi, jumlah_peserta, telepon, status, sumber, catatan_admin, dibuat_oleh_admin)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'admin_wa', ?, ?)");
        mysqli_stmt_bind_param(
            $stmt, 'issssisssi',
            $user_id_final, $nama_final, $email_final, $kode, $destinasi, $jumlah, $telepon, $status, $catatan_admin, $admin_id
        );
        if (mysqli_stmt_execute($stmt)) {
            $success = "Booking manual berhasil ditambahkan dengan kode $kode.";
        } else {
            $error = 'Gagal menyimpan booking: ' . mysqli_error($koneksi);
        }
        mysqli_stmt_close($stmt);
    }
}

$existing_users = mysqli_query($koneksi, "SELECT id, name, email, phone FROM users WHERE role = 'user' ORDER BY name ASC");

$page_title = 'Booking via WhatsApp';
$active_menu = 'booking-wa';
require __DIR__ . '/includes/admin_header.php';
?>
<h1>Kelola Booking via WhatsApp</h1>
<p class="sub">Untuk pelanggan yang memesan lewat chat WhatsApp. Isi data diri & perjalanannya di sini agar tercatat di sistem seperti booking lainnya.</p>

<?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= h($success) ?> <a href="bookings.php" style="color:#237040;font-weight:600;">Lihat di daftar Booking &rarr;</a></div><?php endif; ?>

<div class="card" style="max-width:700px;">
    <form method="POST" id="formBookingWA">
        <div style="margin-bottom:18px;">
            <label style="display:block;font-size:13px;font-weight:600;margin-bottom:8px;">Jenis Pelanggan</label>
            <div style="display:flex;gap:20px;font-size:13px;">
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                    <input type="radio" name="tipe_pelanggan" value="manual" checked onchange="toggleTipePelanggan()"> Belum punya akun (isi manual)
                </label>
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                    <input type="radio" name="tipe_pelanggan" value="existing" onchange="toggleTipePelanggan()"> Sudah punya akun terdaftar
                </label>
            </div>
        </div>

        <div id="blokManual">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;margin-bottom:6px;">Nama Pelanggan</label>
                    <input type="text" name="nama_pelanggan" style="width:100%;padding:11px 14px;border:1px solid #e5e0d8;border-radius:8px;font-size:14px;">
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;margin-bottom:6px;">Email (opsional)</label>
                    <input type="email" name="email_pelanggan" style="width:100%;padding:11px 14px;border:1px solid #e5e0d8;border-radius:8px;font-size:14px;">
                </div>
            </div>
        </div>

        <div id="blokExisting" style="display:none;margin-bottom:14px;">
            <label style="display:block;font-size:13px;font-weight:500;margin-bottom:6px;">Pilih Akun Pelanggan Terdaftar</label>
            <select name="existing_user_id" style="width:100%;padding:11px 14px;border:1px solid #e5e0d8;border-radius:8px;font-size:14px;">
                <option value="">-- Pilih Pelanggan --</option>
                <?php while ($u = mysqli_fetch_assoc($existing_users)): ?>
                    <option value="<?= $u['id'] ?>"><?= h($u['name']) ?> (<?= h($u['email']) ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>

        <div style="margin-bottom:14px;">
            <label style="display:block;font-size:13px;font-weight:500;margin-bottom:6px;">Nomor WhatsApp Pelanggan</label>
            <input type="tel" name="telepon" placeholder="08123456xxx" required style="width:100%;padding:11px 14px;border:1px solid #e5e0d8;border-radius:8px;font-size:14px;">
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:14px;">
            <div>
                <label style="display:block;font-size:13px;font-weight:500;margin-bottom:6px;">Destinasi</label>
                <select name="destinasi" id="destinasiSelect" onchange="toggleDestinasiCustom()" style="width:100%;padding:11px 14px;border:1px solid #e5e0d8;border-radius:8px;font-size:14px;">
                    <?php foreach ($destinasi_list as $d): ?>
                        <option value="<?= h($d) ?>"><?= h($d) ?></option>
                    <?php endforeach; ?>
                    <option value="__lainnya__">Lainnya (isi manual)</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:500;margin-bottom:6px;">Jumlah Peserta</label>
                <input type="number" name="jumlah" min="1" value="1" style="width:100%;padding:11px 14px;border:1px solid #e5e0d8;border-radius:8px;font-size:14px;">
            </div>
        </div>

        <div id="blokDestinasiCustom" style="display:none;margin-bottom:14px;">
            <label style="display:block;font-size:13px;font-weight:500;margin-bottom:6px;">Nama Destinasi Lainnya</label>
            <input type="text" name="destinasi_custom" style="width:100%;padding:11px 14px;border:1px solid #e5e0d8;border-radius:8px;font-size:14px;">
        </div>

        <div style="margin-bottom:14px;">
            <label style="display:block;font-size:13px;font-weight:500;margin-bottom:6px;">Status Pembayaran</label>
            <select name="status" style="width:100%;padding:11px 14px;border:1px solid #e5e0d8;border-radius:8px;font-size:14px;">
                <option value="dikonfirmasi" selected>Sudah Dikonfirmasi (deal & transfer sudah selesai via WA)</option>
                <option value="menunggu_pembayaran">Menunggu Pembayaran</option>
                <option value="menunggu_konfirmasi">Menunggu Verifikasi Bukti Transfer</option>
                <option value="ditolak">Ditolak / Batal</option>
            </select>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block;font-size:13px;font-weight:500;margin-bottom:6px;">Catatan Perjalanan (opsional)</label>
            <textarea name="catatan_admin" rows="3" placeholder="Contoh: tanggal keberangkatan, permintaan khusus, ringkasan chat WA, dsb." style="width:100%;padding:11px 14px;border:1px solid #e5e0d8;border-radius:8px;font-size:14px;font-family:inherit;"></textarea>
        </div>

        <button type="submit" class="btn btn-view" style="padding:11px 28px;font-size:13px;">Simpan Booking</button>
    </form>
</div>

<script>
function toggleTipePelanggan() {
    const isExisting = document.querySelector('input[name="tipe_pelanggan"]:checked').value === 'existing';
    document.getElementById('blokManual').style.display = isExisting ? 'none' : 'block';
    document.getElementById('blokExisting').style.display = isExisting ? 'block' : 'none';
}
function toggleDestinasiCustom() {
    const val = document.getElementById('destinasiSelect').value;
    document.getElementById('blokDestinasiCustom').style.display = (val === '__lainnya__') ? 'block' : 'none';
}
</script>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
