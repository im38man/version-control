<?php
require 'includes/auth.php';
requireLogin();
require 'config/koneksi.php';

$pageTitle  = "Materi Pembelajaran - Mansekai";
$activePage = "materi.php";
$userId = $_SESSION['user_id'];
$info = "";

// ================= HANDLE PENGAJUAN AKSES =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajukan_materi_id'])) {
    $materiId = (int)$_POST['ajukan_materi_id'];

    $cek = mysqli_prepare($koneksi, "SELECT id FROM pengajuan_materi WHERE materi_id=? AND user_id=?");
    mysqli_stmt_bind_param($cek, "ii", $materiId, $userId);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);

    if (mysqli_stmt_num_rows($cek) === 0) {
        $insert = mysqli_prepare($koneksi, "INSERT INTO pengajuan_materi (materi_id, user_id, status) VALUES (?, ?, 'pending')");
        mysqli_stmt_bind_param($insert, "ii", $materiId, $userId);
        mysqli_stmt_execute($insert);
        $info = "Pengajuan akses materi berhasil dikirim, tunggu persetujuan admin.";
    } else {
        // Sudah pernah ditolak, ajukan ulang -> reset ke pending
        $update = mysqli_prepare($koneksi, "UPDATE pengajuan_materi SET status='pending' WHERE materi_id=? AND user_id=? AND status='rejected'");
        mysqli_stmt_bind_param($update, "ii", $materiId, $userId);
        mysqli_stmt_execute($update);
        $info = "Pengajuan dikirim ulang, tunggu persetujuan admin.";
    }
}

// ================= PAGINATION =================
$perPage = 6;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$totalMateri = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM materi"))['total'];
$totalPage = max(1, (int)ceil($totalMateri / $perPage));
if ($page > $totalPage) { $page = $totalPage; $offset = ($page - 1) * $perPage; }

// Semua materi + status pengajuan milik user yang login (kalau ada), per halaman
$sql = "SELECT m.id, m.judul, m.deskripsi, m.status AS status_materi, p.status AS status_pengajuan
        FROM materi m
        LEFT JOIN pengajuan_materi p ON p.materi_id = m.id AND p.user_id = $userId
        ORDER BY m.created_at DESC
        LIMIT $perPage OFFSET $offset";
$result = mysqli_query($koneksi, $sql);

include 'includes/header.php';
?>
<div class="header-title">
    <h1>Daftar Materi Pembelajaran</h1>
    <p>Ajukan akses ke admin untuk setiap materi yang ingin kamu pelajari.</p>
</div>

<?php if ($info): ?><div class="alert alert-success"><?= htmlspecialchars($info) ?></div><?php endif; ?>

<div class="card">
    <h3>Modul Aktif Semester Ini</h3>

    <?php if (mysqli_num_rows($result) === 0): ?>
        <p style="margin-top:15px; color: var(--text-muted);">
            Belum ada materi. <?= isAdmin() ? 'Tambahkan lewat menu Kelola Materi.' : 'Hubungi admin untuk menambahkan.' ?>
        </p>
    <?php endif; ?>

    <div class="materi-grid">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <?php
                $statusClass = 'proses';
                if ($row['status_materi'] === 'Selesai') $statusClass = 'selesai';
                if ($row['status_materi'] === 'Locked') $statusClass = 'locked';
                $isLocked = $row['status_materi'] === 'Locked';
                $statusPengajuan = $row['status_pengajuan']; // null, pending, approved, rejected
            ?>
            <div class="materi-card">
                <div class="materi-card-top">
                    <i class="fa-solid fa-circle-play" style="color: var(--accent-green-dark);"></i>
                    <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($row['status_materi']) ?></span>
                </div>
                <h4 class="materi-card-title"><?= htmlspecialchars($row['judul']) ?></h4>
                <?php if (!empty($row['deskripsi'])): ?>
                    <p class="materi-card-desc"><?= htmlspecialchars($row['deskripsi']) ?></p>
                <?php endif; ?>

                <div class="materi-card-action">
                    <?php if ($isLocked): ?>
                        <a href="#" class="btn-baca locked-btn"><i class="fa-solid fa-lock"></i> Terkunci</a>

                    <?php elseif (isAdmin()): ?>
                        <a href="baca-materi.php?id=<?= (int)$row['id'] ?>" class="btn-baca"><i class="fa-solid fa-book-open-reader"></i> Baca Materi</a>

                    <?php elseif ($statusPengajuan === 'approved'): ?>
                        <a href="baca-materi.php?id=<?= (int)$row['id'] ?>" class="btn-baca"><i class="fa-solid fa-book-open-reader"></i> Baca Materi</a>

                    <?php elseif ($statusPengajuan === 'pending'): ?>
                        <span class="badge proses"><i class="fa-solid fa-hourglass-half"></i> Menunggu Persetujuan</span>

                    <?php elseif ($statusPengajuan === 'rejected'): ?>
                        <span class="badge locked" style="margin-right:4px;">Ditolak Admin</span>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="ajukan_materi_id" value="<?= (int)$row['id'] ?>">
                            <button type="submit" class="btn-baca"><i class="fa-solid fa-rotate-right"></i> Ajukan Lagi</button>
                        </form>

                    <?php else: ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="ajukan_materi_id" value="<?= (int)$row['id'] ?>">
                            <button type="submit" class="btn-baca"><i class="fa-solid fa-paper-plane"></i> Ajukan Akses</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <?php if ($totalPage > 1): ?>
        <div class="pagination">
            <a href="?page=<?= max(1, $page - 1) ?>" class="page-btn <?= $page <= 1 ? 'disabled' : '' ?>"><i class="fa-solid fa-chevron-left"></i></a>

            <?php for ($i = 1; $i <= $totalPage; $i++): ?>
                <a href="?page=<?= $i ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <a href="?page=<?= min($totalPage, $page + 1) ?>" class="page-btn <?= $page >= $totalPage ? 'disabled' : '' ?>"><i class="fa-solid fa-chevron-right"></i></a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
