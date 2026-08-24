<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$flash = flash_get();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $userId = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($userId > 0 && in_array($action, ['approve', 'reject'], true)) {
        $newStatus = $action === 'approve' ? 'approved' : 'rejected';

        $stmt = $conn->prepare('UPDATE users SET vip_status = ? WHERE id = ?');
        $stmt->bind_param('si', $newStatus, $userId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE vip_requests SET status = ?, responded_at = NOW() WHERE user_id = ? AND status = 'pending'");
        $stmt->bind_param('si', $newStatus, $userId);
        $stmt->execute();
        $stmt->close();

        flash_set($action === 'approve' ? 'Akses VIP disetujui.' : 'Pengajuan VIP ditolak.', 'success');
    }
    header('Location: vip-requests.php');
    exit;
}

// Daftar user dengan status pending (butuh aksi) + riwayat status lain
$pending = $conn->query("SELECT id, full_name, email, vip_status FROM users WHERE vip_status = 'pending' ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
$others = $conn->query("SELECT id, full_name, email, vip_status FROM users WHERE vip_status IN ('approved','rejected') ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);

$user = current_user();
$activeTab = 'vip-admin';
$basePath = '../';
$pageTitle = 'ManTrading - Kelola Akses VIP';
require_once __DIR__ . '/../includes/head.php';
?>
<div class="flex bg-gray-50 lg:h-screen lg:overflow-hidden">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="flex-1 flex flex-col min-w-0 lg:overflow-hidden">
    <header class="bg-white px-6 py-5 border-b border-gray-200 flex justify-between items-center z-10 sticky top-0 shadow-sm">
      <div class="flex items-center gap-4">
        <button onclick="toggleSidebar(true)" class="lg:hidden text-gray-500 hover:text-gray-800"><i class="fa-solid fa-bars text-xl"></i></button>
        <div>
          <h1 class="text-xl md:text-2xl font-bold text-gray-800 tracking-tight">Kelola Akses VIP Class</h1>
          <p class="text-gray-500 text-xs md:text-sm mt-0.5">Setujui atau tolak pengajuan akses materi VIP dari member.</p>
        </div>
      </div>
    </header>

    <main class="flex-1 lg:overflow-y-auto p-4 md:p-6 lg:p-8 custom-scrollbar bg-gray-50/50">
      <div class="max-w-4xl mx-auto flex flex-col gap-6 animate-fade-in-up">

        <?php if ($flash): ?>
          <div class="text-xs font-semibold rounded-xl p-3 <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>">
            <?= e($flash['msg']) ?>
          </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
          <div class="p-5 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2"><i class="fa-solid fa-hourglass-half text-amber-500"></i> Menunggu Persetujuan (<?= count($pending) ?>)</h2>
          </div>
          <?php if (!$pending): ?>
            <p class="p-6 text-sm text-gray-500 text-center">Tidak ada pengajuan yang menunggu saat ini.</p>
          <?php else: foreach ($pending as $p): ?>
            <div class="p-4 md:p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div>
                <p class="font-bold text-gray-800 text-sm"><?= e($p['full_name']) ?></p>
                <p class="text-xs text-gray-500"><?= e($p['email']) ?></p>
              </div>
              <div class="flex gap-2">
                <form method="POST">
<?= csrf_field() ?>
                  <input type="hidden" name="user_id" value="<?= (int)$p['id'] ?>">
                  <input type="hidden" name="action" value="approve">
                  <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-bold transition-all shadow shadow-emerald-600/20"><i class="fa-solid fa-check mr-1"></i> Setujui</button>
                </form>
                <form method="POST">
<?= csrf_field() ?>
                  <input type="hidden" name="user_id" value="<?= (int)$p['id'] ?>">
                  <input type="hidden" name="action" value="reject">
                  <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg text-xs font-bold transition-all shadow shadow-rose-600/20"><i class="fa-solid fa-xmark mr-1"></i> Tolak</button>
                </form>
              </div>
            </div>
          <?php endforeach; endif; ?>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
          <div class="p-5 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2"><i class="fa-solid fa-clock-rotate-left text-gray-400"></i> Riwayat Status</h2>
          </div>
          <?php if (!$others): ?>
            <p class="p-6 text-sm text-gray-500 text-center">Belum ada riwayat.</p>
          <?php else: foreach ($others as $o): ?>
            <div class="p-4 md:p-5 border-b border-gray-100 flex items-center justify-between gap-3">
              <div>
                <p class="font-bold text-gray-800 text-sm"><?= e($o['full_name']) ?></p>
                <p class="text-xs text-gray-500"><?= e($o['email']) ?></p>
              </div>
              <span class="text-[10px] font-bold uppercase px-3 py-1 rounded-full border <?= $o['vip_status'] === 'approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' ?>">
                <?= $o['vip_status'] === 'approved' ? 'Disetujui' : 'Ditolak' ?>
              </span>
            </div>
          <?php endforeach; endif; ?>
        </div>

      </div>
    </main>
  </div>
</div>
</body>
</html>
