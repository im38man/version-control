<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$user = current_user();
$activeTab = 'vip';
$flash = flash_get();
$vipStatus = refresh_vip_status($conn, $user['id']); // none|pending|approved|rejected

$vipClasses = [];
if ($vipStatus === 'approved') {
    $res = $conn->query('SELECT * FROM vip_classes ORDER BY sort_order ASC');
    while ($row = $res->fetch_assoc()) {
        $modStmt = $conn->prepare('SELECT module_text FROM vip_class_modules WHERE vip_class_id = ? ORDER BY sort_order ASC');
        $modStmt->bind_param('i', $row['id']);
        $modStmt->execute();
        $modRes = $modStmt->get_result();
        $modules = [];
        while ($m = $modRes->fetch_assoc()) { $modules[] = $m['module_text']; }
        $modStmt->close();
        $row['modules'] = $modules;
        $vipClasses[] = $row;
    }
}

$pageTitle = 'ManTrading - VIP Class';
require_once __DIR__ . '/includes/head.php';
?>
<div class="flex bg-gray-50 lg:h-screen lg:overflow-hidden">
  <?php require __DIR__ . '/includes/sidebar.php'; ?>

  <div class="flex-1 flex flex-col min-w-0 lg:overflow-hidden">
    <header class="bg-white px-6 py-5 border-b border-gray-200 flex justify-between items-center z-10 sticky top-0 shadow-sm">
      <div class="flex items-center gap-4">
        <button onclick="toggleSidebar(true)" class="lg:hidden text-gray-500 hover:text-gray-800"><i class="fa-solid fa-bars text-xl"></i></button>
        <div>
          <h1 class="text-xl md:text-2xl font-bold text-gray-800 tracking-tight">ManTrading VIP Class</h1>
          <p class="text-gray-500 text-xs md:text-sm mt-0.5">Materi edukasi eksklusif untuk tingkatkan skill tradingmu ke level pro.</p>
        </div>
      </div>
    </header>

    <main class="flex-1 lg:overflow-y-auto p-4 md:p-6 lg:p-8 custom-scrollbar bg-gray-50/50">
      <div class="max-w-7xl mx-auto flex flex-col gap-6 animate-fade-in-up">

        <?php if ($flash): ?>
          <div class="text-xs font-semibold rounded-xl p-3 <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>">
            <?= e($flash['msg']) ?>
          </div>
        <?php endif; ?>

        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-indigo-900 rounded-3xl p-6 md:p-8 text-white shadow-xl relative overflow-hidden flex flex-col md:flex-row justify-between items-center gap-6">
          <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
          <div class="z-10 max-w-xl">
            <span class="bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[10px] font-extrabold uppercase px-3 py-1 rounded-full tracking-wider">Eksklusif Member VIP</span>
            <h2 class="text-2xl md:text-3xl font-extrabold mt-3 tracking-tight">Akselerasikan Profit Trading Bersama Materi Masterclass</h2>
            <p class="text-slate-300 text-xs md:text-sm mt-2 leading-relaxed">
              Akses materi VIP wajib disetujui admin terlebih dahulu. Ajukan sekali, dan seluruh masterclass di bawah akan terbuka begitu disetujui.
            </p>
          </div>
          <div class="z-10 bg-white/10 backdrop-blur-md border border-white/10 p-5 rounded-2xl flex gap-6 text-center">
            <div>
              <p class="text-2xl font-black font-mono text-amber-400">4+</p>
              <p class="text-[10px] text-slate-300 uppercase font-bold tracking-wider mt-0.5">Masterclass</p>
            </div>
            <div class="w-px bg-white/20"></div>
            <div>
              <p class="text-2xl font-black font-mono text-emerald-400">100%</p>
              <p class="text-[10px] text-slate-300 uppercase font-bold tracking-wider mt-0.5">Praktikal</p>
            </div>
          </div>
        </div>

        <?php if ($vipStatus === 'approved'): ?>
          <!-- SUDAH DISETUJUI: TAMPILKAN SEMUA KELAS -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
            <?php foreach ($vipClasses as $item): ?>
              <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                  <div class="relative h-48 sm:h-56 overflow-hidden bg-gray-100">
                    <img src="<?= e($item['image']) ?>" alt="<?= e($item['title']) ?>" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                    <div class="absolute top-3 left-3 bg-slate-950/80 backdrop-blur-md text-white text-[10px] font-bold px-3 py-1 rounded-lg border border-white/10 uppercase"><?= e($item['category']) ?></div>
                  </div>
                  <div class="p-6">
                    <div class="flex items-center gap-3 text-xs text-gray-400 font-semibold mb-2">
                      <span><i class="fa-solid fa-layer-group text-indigo-500 mr-1.5"></i> <?= e($item['level']) ?></span>
                      <span>•</span>
                      <span><i class="fa-solid fa-clock text-indigo-500 mr-1.5"></i> <?= e($item['duration']) ?></span>
                    </div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-2"><?= e($item['title']) ?></h3>
                    <p class="text-xs md:text-sm text-gray-600 leading-relaxed"><?= e($item['description']) ?></p>
                  </div>
                </div>
                <div class="p-6 pt-0 flex items-center justify-between border-t border-gray-100 mt-4">
                  <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100"><i class="fa-solid fa-check-circle mr-1"></i> Akses Terbuka</span>
                  <button onclick='openClassModal(<?= htmlspecialchars(json_encode($item), ENT_QUOTES) ?>)' class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-xs md:text-sm font-semibold transition-all shadow-md shadow-indigo-600/20 flex items-center gap-2">
                    <span>Lihat Materi</span> <i class="fa-solid fa-arrow-right text-xs"></i>
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

        <?php else: ?>
          <!-- BELUM DISETUJUI: TAMPILKAN GERBANG REQUEST -->
          <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-10 text-center flex flex-col items-center gap-4 max-w-xl mx-auto">
            <?php if ($vipStatus === 'pending'): ?>
              <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center text-3xl"><i class="fa-solid fa-hourglass-half"></i></div>
              <h2 class="text-xl font-bold text-gray-800">Pengajuan Sedang Diproses</h2>
              <p class="text-sm text-gray-500">Permintaan akses VIP Class lu sedang menunggu persetujuan admin. Kami akan buka akses begitu disetujui.</p>
            <?php elseif ($vipStatus === 'rejected'): ?>
              <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center text-3xl"><i class="fa-solid fa-circle-xmark"></i></div>
              <h2 class="text-xl font-bold text-gray-800">Pengajuan Ditolak</h2>
              <p class="text-sm text-gray-500">Pengajuan akses VIP Class lu sebelumnya belum disetujui admin. Lu bisa ajukan ulang di bawah ini.</p>
              <form method="POST" action="vip-request.php">
<?= csrf_field() ?>
                <button type="submit" class="mt-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-bold text-sm transition-all shadow-lg shadow-indigo-600/25 flex items-center gap-2">
                  <i class="fa-solid fa-paper-plane"></i> Ajukan Ulang Akses VIP
                </button>
              </form>
            <?php else: ?>
              <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-3xl"><i class="fa-solid fa-lock"></i></div>
              <h2 class="text-xl font-bold text-gray-800">Materi VIP Terkunci</h2>
              <p class="text-sm text-gray-500">Ajukan permintaan akses ke admin untuk membuka seluruh masterclass VIP Class ManTrading.</p>
              <form method="POST" action="vip-request.php">
<?= csrf_field() ?>
                <button type="submit" class="mt-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-bold text-sm transition-all shadow-lg shadow-indigo-600/25 flex items-center gap-2">
                  <i class="fa-solid fa-paper-plane"></i> Ajukan Akses VIP Class
                </button>
              </form>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      </div>
    </main>
  </div>
</div>

<!-- MODAL LIHAT MATERI -->
<div id="classModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 sm:p-6">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="toggleModal('classModal', false)"></div>
  <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[95vh] overflow-y-auto custom-scrollbar animate-fade-in-up">
    <div class="relative h-48 sm:h-64 bg-gray-900">
      <img id="classModalImg" src="" alt="" class="w-full h-full object-cover opacity-60">
      <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent flex flex-col justify-end p-6">
        <span id="classModalCategory" class="text-indigo-400 text-xs font-bold uppercase tracking-wider mb-1"></span>
        <h2 id="classModalTitle" class="text-xl md:text-2xl font-extrabold text-white"></h2>
      </div>
      <button onclick="toggleModal('classModal', false)" class="absolute top-4 right-4 w-9 h-9 rounded-full bg-slate-900/80 text-white hover:bg-red-600 transition-colors flex items-center justify-center shadow-md"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="p-6 space-y-6">
      <div>
        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Deskripsi Kelas</h4>
        <p id="classModalDesc" class="text-sm text-gray-700 leading-relaxed"></p>
      </div>
      <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200">
        <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-3 flex items-center gap-2"><i class="fa-solid fa-list-check"></i> Silabus & Modul Pembelajaran</h4>
        <ul id="classModalModules" class="space-y-2.5"></ul>
      </div>
      <div class="flex justify-end gap-3 pt-2">
        <button id="classModalStartBtn" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold text-sm transition-all shadow-lg shadow-indigo-600/25 flex items-center justify-center gap-2">
          <i class="fa-solid fa-play"></i> Mulai Belajar Sekarang
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function openClassModal(item) {
  document.getElementById('classModalImg').src = item.image;
  document.getElementById('classModalCategory').textContent = item.category;
  document.getElementById('classModalTitle').textContent = item.title;
  document.getElementById('classModalDesc').textContent = item.description;
  const list = document.getElementById('classModalModules');
  list.innerHTML = '';
  item.modules.forEach((mod, idx) => {
    const li = document.createElement('li');
    li.className = 'flex items-start gap-3 text-xs md:text-sm text-gray-700 bg-white p-3 rounded-xl border border-gray-100 shadow-2xs';
    li.innerHTML = `<span class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs shrink-0">${idx + 1}</span><span class="mt-0.5"></span>`;
    li.querySelector('span.mt-0\\.5').textContent = mod;
    list.appendChild(li);
  });
  const startBtn = document.getElementById('classModalStartBtn');
  if (item.content_url) {
    startBtn.onclick = () => { window.location.href = item.content_url; };
    startBtn.classList.remove('opacity-60', 'cursor-not-allowed');
  } else {
    startBtn.onclick = () => alert('Materi buat kelas ini belum diisi admin. Coba lagi nanti ya bro.');
    startBtn.classList.add('opacity-60', 'cursor-not-allowed');
  }
  toggleModal('classModal', true);
}
</script>
</body>
</html>
