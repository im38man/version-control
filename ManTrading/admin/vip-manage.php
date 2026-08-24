<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$flash = flash_get();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
}

// ================= PROSES SIMPAN KELAS (TAMBAH / EDIT) =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_class') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $image = trim($_POST['image'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $level = trim($_POST['level'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $contentUrl = trim($_POST['content_url'] ?? '');

    if ($title === '' || $category === '') {
        flash_set('Judul dan kategori kelas wajib diisi.', 'error');
        header('Location: vip-manage.php' . ($id ? "?edit=$id" : ''));
        exit;
    }

    if ($id > 0) {
        $stmt = $conn->prepare('UPDATE vip_classes SET title=?, category=?, image=?, description=?, level=?, duration=?, content_url=? WHERE id=?');
        $stmt->bind_param('sssssssi', $title, $category, $image, $description, $level, $duration, $contentUrl, $id);
        $stmt->execute();
        $stmt->close();
        flash_set('Kelas berhasil diupdate.', 'success');
        header('Location: vip-manage.php?edit=' . $id);
        exit;
    } else {
        $sortOrder = (int)($conn->query('SELECT COALESCE(MAX(sort_order),0)+1 AS n FROM vip_classes')->fetch_assoc()['n']);
        $stmt = $conn->prepare('INSERT INTO vip_classes (title, category, image, description, level, duration, content_url, sort_order) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->bind_param('sssssssi', $title, $category, $image, $description, $level, $duration, $contentUrl, $sortOrder);
        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();
        flash_set('Kelas baru berhasil dibuat. Sekarang tambahin modul/silabusnya di bawah.', 'success');
        header('Location: vip-manage.php?edit=' . $newId);
        exit;
    }
}

// ================= PROSES HAPUS KELAS =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_class') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $conn->prepare('DELETE FROM vip_classes WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    flash_set('Kelas & seluruh modulnya berhasil dihapus.', 'success');
    header('Location: vip-manage.php');
    exit;
}

// ================= PROSES TAMBAH MODUL =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_module') {
    $classId = (int)($_POST['vip_class_id'] ?? 0);
    $text = trim($_POST['module_text'] ?? '');
    if ($text !== '' && $classId > 0) {
        $sortOrder = (int)($conn->query("SELECT COALESCE(MAX(sort_order),0)+1 AS n FROM vip_class_modules WHERE vip_class_id = $classId")->fetch_assoc()['n']);
        $stmt = $conn->prepare('INSERT INTO vip_class_modules (vip_class_id, module_text, sort_order) VALUES (?,?,?)');
        $stmt->bind_param('isi', $classId, $text, $sortOrder);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: vip-manage.php?edit=' . $classId);
    exit;
}

// ================= PROSES HAPUS MODUL =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_module') {
    $moduleId = (int)($_POST['module_id'] ?? 0);
    $classId = (int)($_POST['vip_class_id'] ?? 0);
    $stmt = $conn->prepare('DELETE FROM vip_class_modules WHERE id = ?');
    $stmt->bind_param('i', $moduleId);
    $stmt->execute();
    $stmt->close();
    header('Location: vip-manage.php?edit=' . $classId);
    exit;
}

// ================= AMBIL DATA UNTUK TAMPILAN =================
$classes = $conn->query('SELECT * FROM vip_classes ORDER BY sort_order ASC, id ASC')->fetch_all(MYSQLI_ASSOC);

$editClass = null;
$editModules = [];
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $conn->prepare('SELECT * FROM vip_classes WHERE id = ?');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editClass = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($editClass) {
        $stmt = $conn->prepare('SELECT * FROM vip_class_modules WHERE vip_class_id = ? ORDER BY sort_order ASC, id ASC');
        $stmt->bind_param('i', $editId);
        $stmt->execute();
        $editModules = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

$user = current_user();
$activeTab = 'vip-manage';
$basePath = '../';
$pageTitle = 'ManTrading - Edit Materi VIP';
require_once __DIR__ . '/../includes/head.php';
?>
<div class="flex bg-gray-50 lg:h-screen lg:overflow-hidden">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="flex-1 flex flex-col min-w-0 lg:overflow-hidden">
    <header class="bg-white px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-200 flex flex-wrap justify-between items-center gap-3 z-10 sticky top-0 shadow-sm">
      <div class="flex items-center gap-3 sm:gap-4 min-w-0">
        <button onclick="toggleSidebar(true)" class="lg:hidden text-gray-500 hover:text-gray-800 shrink-0"><i class="fa-solid fa-bars text-xl"></i></button>
        <div class="min-w-0">
          <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-800 tracking-tight truncate">Edit Materi VIP Class</h1>
          <p class="text-gray-500 text-[11px] sm:text-xs md:text-sm mt-0.5 truncate">Tambah, edit, atau hapus kelas & modul masterclass.</p>
        </div>
      </div>
      <button onclick="openClassModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl font-semibold text-xs md:text-sm transition-all shadow-lg shadow-indigo-600/25 flex items-center gap-2 shrink-0">
        <i class="fa-solid fa-plus"></i> <span class="hidden sm:inline">Tambah Kelas</span>
      </button>
    </header>

    <main class="flex-1 lg:overflow-y-auto p-3 sm:p-4 md:p-6 lg:p-8 custom-scrollbar bg-gray-50/50">
      <div class="max-w-5xl mx-auto flex flex-col gap-4 sm:gap-6 animate-fade-in-up min-w-0">

        <?php if ($flash): ?>
          <div class="text-xs font-semibold rounded-xl p-3 <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>">
            <?= e($flash['msg']) ?>
          </div>
        <?php endif; ?>

        <?php if (!$classes): ?>
          <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center text-gray-500">Belum ada kelas VIP. Klik "Tambah Kelas" untuk buat yang pertama.</div>
        <?php else: ?>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
            <?php foreach ($classes as $c): ?>
              <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm flex flex-col">
                <div class="h-36 sm:h-40 bg-gray-100 overflow-hidden">
                  <?php if ($c['image']): ?>
                    <img src="<?= e($c['image']) ?>" alt="<?= e($c['title']) ?>" class="w-full h-full object-cover">
                  <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-gray-300"><i class="fa-solid fa-image text-3xl"></i></div>
                  <?php endif; ?>
                </div>
                <div class="p-4 flex flex-col gap-1.5 flex-1">
                  <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider"><?= e($c['category']) ?></span>
                  <h3 class="text-sm sm:text-base font-bold text-gray-800 leading-snug"><?= e($c['title']) ?></h3>
                  <p class="text-xs text-gray-500 line-clamp-2 flex-1"><?= e($c['description']) ?></p>
                  <?php if (!empty($c['content_url'])): ?>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-100 px-2 py-1 rounded-md w-fit"><i class="fa-solid fa-link"></i> Materi terhubung</span>
                  <?php else: ?>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-100 px-2 py-1 rounded-md w-fit"><i class="fa-solid fa-triangle-exclamation"></i> Belum ada link materi</span>
                  <?php endif; ?>
                  <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100">
                    <button onclick="openClassModal(<?= (int)$c['id'] ?>)" class="flex-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 border border-indigo-100 px-3 py-2 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5">
                      <i class="fa-solid fa-pen"></i> Edit
                    </button>
                    <form method="POST" onsubmit="return confirm('Hapus kelas <?= e($c['title']) ?> beserta semua modulnya?');">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="delete_class">
                      <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                      <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-100 px-3 py-2 rounded-lg text-xs font-bold transition-all"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </div>
    </main>
  </div>
</div>

<!-- MODAL TAMBAH/EDIT KELAS -->
<div id="classFormModal" class="fixed inset-0 z-[100] <?= ($editClass || isset($_GET['edit'])) ? 'flex' : 'hidden' ?> items-center justify-center p-3 sm:p-6">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeClassModal()"></div>
  <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[95vh] overflow-y-auto custom-scrollbar animate-fade-in-up">
    <div class="flex justify-between items-center p-5 sm:p-6 border-b border-gray-100 bg-gray-50/50 rounded-t-2xl sticky top-0 z-10">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-md"><i class="fa-solid <?= $editClass ? 'fa-pen' : 'fa-plus' ?>"></i></div>
        <div>
          <h2 class="text-base sm:text-lg font-bold text-gray-800"><?= $editClass ? 'Edit Kelas: ' . e($editClass['title']) : 'Tambah Kelas Baru' ?></h2>
          <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Isi detail masterclass VIP.</p>
        </div>
      </div>
      <button onclick="closeClassModal()" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-red-500 transition-colors flex items-center justify-center shrink-0"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <form method="POST" class="p-5 sm:p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
<?= csrf_field() ?>
      <input type="hidden" name="action" value="save_class">
      <input type="hidden" name="id" value="<?= $editClass ? (int)$editClass['id'] : '' ?>">

      <div class="space-y-1.5 sm:col-span-2">
        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Judul Kelas <span class="text-red-500">*</span></label>
        <input type="text" name="title" required value="<?= e($editClass['title'] ?? '') ?>" placeholder="Gold Scalping Elite" class="w-full bg-gray-50 border border-gray-300 text-gray-900 p-2.5 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
      </div>
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kategori <span class="text-red-500">*</span></label>
        <input type="text" name="category" required value="<?= e($editClass['category'] ?? '') ?>" placeholder="XAUUSD Specialist" class="w-full bg-gray-50 border border-gray-300 text-gray-900 p-2.5 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
      </div>
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Level</label>
        <input type="text" name="level" value="<?= e($editClass['level'] ?? '') ?>" placeholder="Beginner / Advanced" class="w-full bg-gray-50 border border-gray-300 text-gray-900 p-2.5 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
      </div>
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Durasi</label>
        <input type="text" name="duration" value="<?= e($editClass['duration'] ?? '') ?>" placeholder="4 Minggu" class="w-full bg-gray-50 border border-gray-300 text-gray-900 p-2.5 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
      </div>
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">URL Gambar Cover</label>
        <input type="url" name="image" value="<?= e($editClass['image'] ?? '') ?>" placeholder="https://..." class="w-full bg-gray-50 border border-gray-300 text-gray-900 p-2.5 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
      </div>
      <div class="space-y-1.5 sm:col-span-2">
        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Link Materi (halaman "Mulai Belajar")</label>
        <input type="text" name="content_url" value="<?= e($editClass['content_url'] ?? '') ?>" placeholder="materi/materi1.php" class="w-full bg-gray-50 border border-gray-300 text-gray-900 p-2.5 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
        <p class="text-[11px] text-gray-400">Path relatif ke file materinya, contoh: <code class="bg-gray-100 px-1 rounded">materi/materi1.php</code>. Bikin file-nya di folder <code class="bg-gray-100 px-1 rounded">materi/</code> — pakai <code class="bg-gray-100 px-1 rounded">materi/materi1.php</code> sebagai contoh format. Kosongin kalau materinya belum siap.</p>
      </div>
      <div class="space-y-1.5 sm:col-span-2">
        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Deskripsi</label>
        <textarea name="description" rows="3" placeholder="Deskripsi singkat kelas..." class="w-full bg-gray-50 border border-gray-300 text-gray-900 p-2.5 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none resize-none"><?= e($editClass['description'] ?? '') ?></textarea>
      </div>

      <div class="sm:col-span-2 flex justify-end gap-3 pt-2 border-t border-gray-100 mt-2">
        <button type="button" onclick="closeClassModal()" class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-5 py-2.5 rounded-lg font-semibold text-sm transition-all">Batal</button>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-semibold text-sm transition-all shadow-lg shadow-indigo-600/25 flex items-center gap-2">
          <i class="fa-solid fa-check"></i> <?= $editClass ? 'Update Kelas' : 'Simpan & Lanjut Isi Modul' ?>
        </button>
      </div>
    </form>

    <?php if ($editClass): ?>
    <div class="p-5 sm:p-6 border-t border-gray-100 bg-gray-50/50">
      <h3 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2"><i class="fa-solid fa-list-check text-indigo-500"></i> Modul / Silabus</h3>
      <div class="space-y-2 mb-3">
        <?php if (!$editModules): ?>
          <p class="text-xs text-gray-400 italic">Belum ada modul. Tambahin di bawah.</p>
        <?php else: foreach ($editModules as $m): ?>
          <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg p-2.5">
            <span class="flex-1 text-xs sm:text-sm text-gray-700"><?= e($m['module_text']) ?></span>
            <form method="POST" onsubmit="return confirm('Hapus modul ini?');" class="shrink-0">
<?= csrf_field() ?>
              <input type="hidden" name="action" value="delete_module">
              <input type="hidden" name="module_id" value="<?= (int)$m['id'] ?>">
              <input type="hidden" name="vip_class_id" value="<?= (int)$editClass['id'] ?>">
              <button type="submit" class="text-gray-400 hover:text-rose-500 w-7 h-7 rounded-lg flex items-center justify-center transition-colors"><i class="fa-solid fa-trash text-xs"></i></button>
            </form>
          </div>
        <?php endforeach; endif; ?>
      </div>
      <form method="POST" class="flex gap-2">
<?= csrf_field() ?>
        <input type="hidden" name="action" value="add_module">
        <input type="hidden" name="vip_class_id" value="<?= (int)$editClass['id'] ?>">
        <input type="text" name="module_text" required placeholder="Tambah modul baru..." class="flex-1 min-w-0 bg-white border border-gray-300 text-gray-800 text-xs sm:text-sm p-2.5 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg font-semibold text-xs sm:text-sm transition-all shadow-md shadow-indigo-600/20 shrink-0">Tambah</button>
      </form>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
  function openClassModal(id) {
    if (id) {
      window.location.href = 'vip-manage.php?edit=' + id;
      return;
    }
    const m = document.getElementById('classFormModal');
    m.classList.remove('hidden'); m.classList.add('flex');
  }
  function closeClassModal() {
    window.location.href = 'vip-manage.php';
  }
</script>
</body>
</html>
