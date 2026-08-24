<?php
require 'includes/auth.php';
requireLogin();
require 'config/koneksi.php';

$id = (int)($_GET['id'] ?? 0);
$userId = $_SESSION['user_id'];

$stmt = mysqli_prepare($koneksi, "SELECT judul, konten, file_materi, status, created_at FROM materi WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$materi = mysqli_fetch_assoc($result);

// Admin selalu boleh baca. User biasa wajib punya pengajuan berstatus 'approved'.
$punyaAkses = isAdmin();
if (!$punyaAkses && $materi) {
    $cekAkses = mysqli_prepare($koneksi, "SELECT status FROM pengajuan_materi WHERE materi_id=? AND user_id=?");
    mysqli_stmt_bind_param($cekAkses, "ii", $id, $userId);
    mysqli_stmt_execute($cekAkses);
    $akses = mysqli_fetch_assoc(mysqli_stmt_get_result($cekAkses));
    $punyaAkses = $akses && $akses['status'] === 'approved';
}

// Kalau materinya berbasis file .html dan user sudah punya akses,
// langsung redirect ke file aslinya (full page, bukan di-embed).
// Tombol "Kembali" ada di dalam halaman file itu sendiri (lihat api/materi-file.php).
if ($materi && !empty($materi['file_materi']) && $materi['status'] !== 'Locked' && $punyaAkses) {
    header("Location: api/materi-file.php?id=" . $id);
    exit;
}

$pageTitle  = $materi ? "Baca Materi: " . $materi['judul'] : "Materi Tidak Ditemukan";
$activePage = "materi.php";

include 'includes/header.php';
?>
<div class="back-nav">
    <a href="materi.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Materi</a>
</div>

<?php if (!$materi || $materi['status'] === 'Locked' || !$punyaAkses): ?>
    <div class="article-card">
        <h1 class="article-title"><?= (!$materi) ? 'Materi Tidak Ditemukan' : 'Akses Belum Diizinkan' ?></h1>
        <div class="article-body">
            <?php if (!$materi): ?>
                <p>Maaf, modul atau bahan bacaan yang Anda cari tidak tersedia.</p>
            <?php elseif ($materi['status'] === 'Locked'): ?>
                <p>Materi ini masih terkunci oleh admin.</p>
            <?php else: ?>
                <p>Kamu belum punya akses ke materi ini. Silakan ajukan permintaan akses dulu di halaman Daftar Materi, lalu tunggu persetujuan admin.</p>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="article-card">
        <div class="article-meta">
            <span><i class="fa-solid fa-calendar"></i> Diperbarui: <?= date('d M Y', strtotime($materi['created_at'])) ?></span>
            <span><i class="fa-solid fa-circle-check"></i> Status: <b><?= htmlspecialchars($materi['status']) ?></b></span>
        </div>
        <h1 class="article-title"><?= htmlspecialchars($materi['judul']) ?></h1>

        <?php if (!empty($materi['konten'])): ?>
            <div class="article-body">
                <p><?= nl2br(htmlspecialchars($materi['konten'])) ?></p>
            </div>
        <?php else: ?>
            <div class="article-body">
                <p>Belum ada isi materi yang diupload admin.</p>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
