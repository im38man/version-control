<?php
require 'includes/auth.php';
requireAdmin();
require 'config/koneksi.php';

$pageTitle  = "Kelola Materi - Admin Mansekai";
$activePage = "admin.php";
$error = "";
$success = "";

// ================= HELPER UPLOAD FILE HTML MATERI =================
$FOLDER_MATERI = __DIR__ . '/uploads/materi/';
$MAX_UKURAN_HTML = 5 * 1024 * 1024; // 5MB

function uploadFileMateriHTML($fileInput, &$error) {
    global $FOLDER_MATERI, $MAX_UKURAN_HTML;

    if (!isset($fileInput) || $fileInput['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // tidak ada file baru diupload
    }
    if ($fileInput['error'] !== UPLOAD_ERR_OK) {
        $error = "Upload file materi gagal.";
        return false;
    }

    $namaAsli = $fileInput['name'];
    $ekstensi = strtolower(pathinfo($namaAsli, PATHINFO_EXTENSION));

    if (!in_array($ekstensi, ['html', 'htm'])) {
        $error = "File materi harus berformat .html.";
        return false;
    }
    if ($fileInput['size'] > $MAX_UKURAN_HTML) {
        $error = "Ukuran file materi maksimal 5MB.";
        return false;
    }

    if (!is_dir($FOLDER_MATERI)) {
        mkdir($FOLDER_MATERI, 0755, true);
    }

    // Nama file unik di server supaya tidak bentrok & tidak bisa ditebak
    $namaFile = bin2hex(random_bytes(16)) . '.html';
    $pathTujuan = $FOLDER_MATERI . $namaFile;

    if (!move_uploaded_file($fileInput['tmp_name'], $pathTujuan)) {
        $error = "Gagal menyimpan file materi di server.";
        return false;
    }

    return ['nama_file' => $namaFile, 'nama_asli' => $namaAsli];
}

// ================= HANDLE TAMBAH MATERI =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $judul     = trim($_POST['judul'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status    = $_POST['status'] ?? 'Proses';

    $uploadResult = uploadFileMateriHTML($_FILES['file_materi'] ?? null, $error);

    if ($judul === '') {
        $error = "Judul materi wajib diisi.";
    } elseif ($uploadResult === null) {
        $error = "File materi (.html) wajib diupload.";
    } elseif ($uploadResult === false) {
        // $error sudah diisi di dalam uploadFileMateriHTML()
    } else {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO materi (judul, deskripsi, file_materi, file_asli, status, dibuat_oleh) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssssi", $judul, $deskripsi, $uploadResult['nama_file'], $uploadResult['nama_asli'], $status, $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        $success = "Materi '$judul' berhasil ditambahkan untuk user.";
    }
}

// ================= HANDLE EDIT MATERI =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id        = (int)$_POST['id'];
    $judul     = trim($_POST['judul'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status    = $_POST['status'] ?? 'Proses';

    $uploadResult = uploadFileMateriHTML($_FILES['file_materi'] ?? null, $error);

    if ($judul === '') {
        $error = "Judul materi wajib diisi.";
    } elseif ($uploadResult === false) {
        // $error sudah diisi di dalam uploadFileMateriHTML()
    } elseif ($uploadResult === null) {
        // Tidak ada file baru -> update tanpa ganti file
        $stmt = mysqli_prepare($koneksi, "UPDATE materi SET judul=?, deskripsi=?, status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssi", $judul, $deskripsi, $status, $id);
        mysqli_stmt_execute($stmt);
        $success = "Materi berhasil diperbarui.";
    } else {
        // Ada file baru -> hapus file lama, simpan file baru
        $stmtLama = mysqli_prepare($koneksi, "SELECT file_materi FROM materi WHERE id=?");
        mysqli_stmt_bind_param($stmtLama, "i", $id);
        mysqli_stmt_execute($stmtLama);
        $lama = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtLama));
        if ($lama && !empty($lama['file_materi'])) {
            $fileLama = $FOLDER_MATERI . $lama['file_materi'];
            if (is_file($fileLama)) unlink($fileLama);
        }

        $stmt = mysqli_prepare($koneksi, "UPDATE materi SET judul=?, deskripsi=?, file_materi=?, file_asli=?, status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssssi", $judul, $deskripsi, $uploadResult['nama_file'], $uploadResult['nama_asli'], $status, $id);
        mysqli_stmt_execute($stmt);
        $success = "Materi berhasil diperbarui.";
    }
}

// ================= HANDLE HAPUS MATERI =================
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    $stmtCek = mysqli_prepare($koneksi, "SELECT file_materi FROM materi WHERE id=?");
    mysqli_stmt_bind_param($stmtCek, "i", $id);
    mysqli_stmt_execute($stmtCek);
    $dataLama = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCek));
    if ($dataLama && !empty($dataLama['file_materi'])) {
        $fileLama = $FOLDER_MATERI . $dataLama['file_materi'];
        if (is_file($fileLama)) unlink($fileLama);
    }

    $stmt = mysqli_prepare($koneksi, "DELETE FROM materi WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("Location: admin.php?deleted=1");
    exit;
}

// ================= HANDLE APPROVE / REJECT PENGAJUAN =================
if (isset($_GET['approve']) || isset($_GET['reject'])) {
    $pengajuanId = (int)($_GET['approve'] ?? $_GET['reject']);
    $statusBaru  = isset($_GET['approve']) ? 'approved' : 'rejected';
    $stmt = mysqli_prepare($koneksi, "UPDATE pengajuan_materi SET status=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "si", $statusBaru, $pengajuanId);
    mysqli_stmt_execute($stmt);
    header("Location: admin.php?pengajuan_updated=1");
    exit;
}

// Data materi yang mau diedit (kalau ada ?edit=id)
$editData = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM materi WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $editData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// Semua materi buat ditampilkan di tabel
$semuaMateri = mysqli_query($koneksi, "SELECT m.*, u.nama AS nama_pembuat FROM materi m LEFT JOIN users u ON m.dibuat_oleh = u.id ORDER BY m.created_at DESC");

// Pengajuan akses materi dari user, yang masih pending diutamakan
$pengajuanList = mysqli_query($koneksi, "
    SELECT p.id, p.status, p.created_at, m.judul AS judul_materi, u.nama AS nama_user
    FROM pengajuan_materi p
    JOIN materi m ON p.materi_id = m.id
    JOIN users u ON p.user_id = u.id
    ORDER BY (p.status = 'pending') DESC, p.created_at DESC
");
$jumlahPending = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pengajuan_materi WHERE status='pending'"))['total'];

include 'includes/header.php';
?>
<div class="header-title">
    <h1>Kelola Materi (Admin)</h1>
    <p>Tambahkan, edit, atau hapus materi, dan setujui pengajuan akses dari user.</p>
</div>

<?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Materi berhasil dihapus.</div><?php endif; ?>
<?php if (isset($_GET['pengajuan_updated'])): ?><div class="alert alert-success">Status pengajuan berhasil diperbarui.</div><?php endif; ?>

<div class="card">
    <h3>Pengajuan Akses Materi <?= $jumlahPending > 0 ? "<span class=\"badge proses\">$jumlahPending pending</span>" : '' ?></h3>
    <table class="admin-table">
        <thead>
            <tr><th>User</th><th>Materi</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($pengajuanList) === 0): ?>
                <tr><td colspan="5">Belum ada pengajuan dari user.</td></tr>
            <?php endif; ?>
            <?php while ($p = mysqli_fetch_assoc($pengajuanList)): ?>
                <?php
                    $cls = $p['status'] === 'approved' ? 'selesai' : ($p['status'] === 'rejected' ? 'locked' : 'proses');
                    $label = ['approved' => 'Disetujui', 'rejected' => 'Ditolak', 'pending' => 'Menunggu'][$p['status']];
                ?>
                <tr>
                    <td><?= htmlspecialchars($p['nama_user']) ?></td>
                    <td><?= htmlspecialchars($p['judul_materi']) ?></td>
                    <td><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                    <td><span class="badge <?= $cls ?>"><?= $label ?></span></td>
                    <td class="aksi">
                        <?php if ($p['status'] !== 'approved'): ?>
                            <a href="admin.php?approve=<?= (int)$p['id'] ?>" class="btn-baca"><i class="fa-solid fa-check"></i> Setujui</a>
                        <?php endif; ?>
                        <?php if ($p['status'] !== 'rejected'): ?>
                            <a href="admin.php?reject=<?= (int)$p['id'] ?>" class="btn-danger"><i class="fa-solid fa-xmark"></i> Tolak</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="grid-2">
    <!-- FORM TAMBAH / EDIT -->
    <div class="card">
        <h3><?= $editData ? 'Edit Materi' : 'Tambah Materi Baru' ?></h3>
        <form method="POST" enctype="multipart/form-data" style="margin-top: 15px;">
            <input type="hidden" name="aksi" value="<?= $editData ? 'edit' : 'tambah' ?>">
            <?php if ($editData): ?>
                <input type="hidden" name="id" value="<?= (int)$editData['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Judul Materi</label>
                <input type="text" name="judul" required value="<?= htmlspecialchars($editData['judul'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Deskripsi Singkat</label>
                <input type="text" name="deskripsi" value="<?= htmlspecialchars($editData['deskripsi'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>File Materi (.html)</label>
                <input type="file" name="file_materi" accept=".html,.htm" <?= $editData ? '' : 'required' ?>>
                <?php if ($editData && !empty($editData['file_asli'])): ?>
                    <small style="display:block; margin-top:6px; color: var(--text-muted);">
                        <i class="fa-solid fa-file-code"></i> File saat ini: <?= htmlspecialchars($editData['file_asli']) ?>
                        — kosongkan kalau tidak mau ganti file.
                    </small>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <?php foreach (['Proses', 'Selesai', 'Locked'] as $s): ?>
                        <option value="<?= $s ?>" <?= (($editData['status'] ?? 'Proses') === $s) ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-<?= $editData ? 'save' : 'plus' ?>"></i> <?= $editData ? 'Simpan Perubahan' : 'Tambah Materi' ?>
            </button>
            <?php if ($editData): ?>
                <a href="admin.php" class="btn-secondary" style="margin-left: 8px;">Batal</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- DAFTAR MATERI -->
    <div class="card">
        <h3>Semua Materi (<?= mysqli_num_rows($semuaMateri) ?>)</h3>
        <table class="admin-table">
            <thead>
                <tr><th>Judul</th><th>Status</th><th>File</th><th>Dibuat oleh</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php mysqli_data_seek($semuaMateri, 0); ?>
                <?php while ($m = mysqli_fetch_assoc($semuaMateri)): ?>
                <tr>
                    <td><?= htmlspecialchars($m['judul']) ?></td>
                    <td>
                        <?php
                            $cls = $m['status'] === 'Selesai' ? 'selesai' : ($m['status'] === 'Locked' ? 'locked' : 'proses');
                        ?>
                        <span class="badge <?= $cls ?>"><?= htmlspecialchars($m['status']) ?></span>
                    </td>
                    <td>
                        <?php if (!empty($m['file_materi'])): ?>
                            <i class="fa-solid fa-file-code" style="color: var(--accent-green-dark);" title="<?= htmlspecialchars($m['file_asli'] ?? '') ?>"></i>
                        <?php else: ?>
                            <span style="color:var(--text-muted); font-size:0.85rem;">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($m['nama_pembuat'] ?? '-') ?></td>
                    <td class="aksi">
                        <a href="admin.php?edit=<?= (int)$m['id'] ?>" class="btn-secondary"><i class="fa-solid fa-pen-to-square"></i></a>
                        <a href="admin.php?hapus=<?= (int)$m['id'] ?>" class="btn-danger" onclick="return confirm('Yakin mau hapus materi ini?')"><i class="fa-solid fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
