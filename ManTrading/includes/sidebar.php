<?php
/** Butuh $activeTab (journal|community|vip) sudah di-set di halaman pemanggil */
$user = current_user();
$activeTab = $activeTab ?? '';
$basePath = $basePath ?? ''; // set '../' di halaman dalam folder admin/
function navClass($tab, $active) {
    return $tab === $active
        ? 'w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/10 transition-all'
        : 'w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-800/50 transition-all';
}
?>
<div id="sidebarOverlay" class="fixed inset-0 bg-gray-900/60 z-40 lg:hidden backdrop-blur-sm hidden" onclick="toggleSidebar(false)"></div>

<div id="sidebar" class="fixed lg:relative z-50 w-72 h-full bg-slate-950 border-r border-slate-800 flex flex-col transition-transform duration-300 -translate-x-full lg:translate-x-0">
  <div class="flex flex-col p-6 border-b border-slate-800">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-400 flex items-center justify-center shadow-lg shadow-indigo-500/20">
        <i class="fa-solid fa-chart-line text-white text-lg"></i>
      </div>
      <div class="flex flex-col">
        <span class="text-xl font-extrabold text-white tracking-tight">Man<span class="text-indigo-400">Trading</span></span>
      </div>
    </div>
    <p class="mt-2 text-[10px] text-slate-400 font-medium tracking-widest uppercase italic">Journey me change the future</p>
  </div>

  <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
    <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Main Menu</p>

    <a href="<?= $basePath ?>index.php" class="<?= navClass('journal', $activeTab) ?>">
      <i class="fa-solid fa-book-journal-whills w-5 text-center"></i> Journal Entry
    </a>

    <a href="<?= $basePath ?>analytics.php" class="<?= navClass('analytics', $activeTab) ?>">
      <i class="fa-solid fa-chart-pie w-5 text-center"></i> Advanced Analytics
    </a>

    <a href="<?= $basePath ?>community.php" class="<?= navClass('community', $activeTab) ?>">
      <i class="fa-solid fa-users w-5 text-center"></i> Community
    </a>

    <a href="<?= $basePath ?>vip-class.php" class="<?= navClass('vip', $activeTab) ?>">
      <i class="fa-solid fa-rocket w-5 text-center"></i> VIP Class
    </a>

    <?php if (is_admin()): ?>
    <p class="px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-4">Admin</p>
    <a href="<?= $basePath ?>admin/users.php" class="<?= navClass('users-admin', $activeTab) ?>">
      <i class="fa-solid fa-users-gear w-5 text-center"></i> Kelola User
    </a>
    <a href="<?= $basePath ?>admin/mentor-requests.php" class="<?= navClass('mentor-admin', $activeTab) ?>">
      <i class="fa-solid fa-chalkboard-user w-5 text-center"></i> Pengajuan Mentor
    </a>
    <a href="<?= $basePath ?>admin/vip-requests.php" class="<?= navClass('vip-admin', $activeTab) ?>">
      <i class="fa-solid fa-user-shield w-5 text-center"></i> Kelola Akses VIP
    </a>
    <a href="<?= $basePath ?>admin/vip-manage.php" class="<?= navClass('vip-manage', $activeTab) ?>">
      <i class="fa-solid fa-pen-to-square w-5 text-center"></i> Edit Materi VIP
    </a>
    <?php endif; ?>
  </nav>

  <div class="p-4 border-t border-slate-800 flex flex-col gap-2 bg-slate-950">
    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-900/80 border border-slate-800/80 shadow-inner">
      <div class="flex items-center gap-3 min-w-0">
        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-600 to-indigo-400 text-white flex items-center justify-center font-bold text-xs shadow-md shrink-0">
          <?= e(strtoupper(substr($user['full_name'], 0, 2))) ?>
        </div>
        <div class="min-w-0">
          <p class="text-xs font-bold text-slate-200 truncate"><?= e($user['full_name']) ?></p>
          <p class="text-[10px] font-medium <?= $user['role'] === 'admin' ? 'text-amber-400' : ((($user['mentor_status'] ?? 'none') === 'approved') ? 'text-amber-400' : 'text-emerald-400') ?>">
            <?= $user['role'] === 'admin' ? 'Administrator' : ((($user['mentor_status'] ?? 'none') === 'approved') ? 'Mentor' : 'Trader') ?>
          </p>
        </div>
      </div>
    </div>

    <button onclick="toggleModal('logoutModal', true)" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-rose-400 hover:bg-rose-500/15 transition-all text-xs font-semibold">
      <i class="fa-solid fa-right-from-bracket w-5 text-center"></i> Logout
    </button>
  </div>
</div>

<!-- MODAL KONFIRMASI LOGOUT -->
<div id="logoutModal" class="fixed inset-0 z-[110] hidden items-center justify-center p-4">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="toggleModal('logoutModal', false)"></div>
  <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center animate-fade-in-up">
    <div class="w-14 h-14 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
      <i class="fa-solid fa-right-from-bracket"></i>
    </div>
    <h3 class="text-lg font-bold text-gray-800 mb-1">Keluar Sesi?</h3>
    <p class="text-xs text-gray-500 mb-6">Yakin ingin keluar dari sesi ManTrading, bro? Sesi akun lu akan diakhiri.</p>
    <div class="flex gap-3">
      <button onclick="toggleModal('logoutModal', false)" class="flex-1 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 py-2.5 rounded-xl font-semibold transition-all text-sm">Batal</button>
      <a href="<?= $basePath ?>logout.php" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl font-semibold transition-all shadow-lg shadow-indigo-600/25 text-sm">Ya, Keluar</a>
    </div>
  </div>
</div>

<script>
  function toggleSidebar(open) {
    document.getElementById('sidebar').classList.toggle('-translate-x-full', !open);
    document.getElementById('sidebarOverlay').classList.toggle('hidden', !open);
  }
  function toggleModal(id, open) {
    const el = document.getElementById(id);
    el.classList.toggle('hidden', !open);
    el.classList.toggle('flex', open);
  }
</script>
