<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$user = current_user();
$flash = flash_get();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $targetId = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($targetId === $user['id'] && in_array($action, ['make_admin', 'make_user', 'delete'], true)) {
        flash_set('Gak bisa ubah role/hapus akun diri sendiri, bro.', 'error');
        header('Location: users.php');
        exit;
    }

    if ($action === 'make_admin' && $targetId > 0) {
        $stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
        $stmt->bind_param('i', $targetId);
        $stmt->execute();
        $stmt->close();
        flash_set('User dijadikan Admin.', 'success');
    } elseif ($action === 'make_user' && $targetId > 0) {
        $stmt = $conn->prepare("UPDATE users SET role = 'user' WHERE id = ?");
        $stmt->bind_param('i', $targetId);
        $stmt->execute();
        $stmt->close();
        flash_set('Role Admin dicabut, sekarang jadi user biasa.', 'success');
    } elseif ($action === 'revoke_mentor' && $targetId > 0) {
        $stmt = $conn->prepare("UPDATE users SET mentor_status = 'none' WHERE id = ?");
        $stmt->bind_param('i', $targetId);
        $stmt->execute();
        $stmt->close();
        flash_set('Status Mentor dicabut.', 'success');
    } elseif ($action === 'revoke_vip' && $targetId > 0) {
        $stmt = $conn->prepare("UPDATE users SET vip_status = 'none' WHERE id = ?");
        $stmt->bind_param('i', $targetId);
        $stmt->execute();
        $stmt->close();
        flash_set('Akses VIP dicabut.', 'success');
    } elseif ($action === 'delete' && $targetId > 0) {
        $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $targetId);
        $stmt->execute();
        $stmt->close();
        flash_set('User berhasil dihapus (semua data terkait ikut terhapus).', 'success');
    }
    header('Location: users.php');
    exit;
}

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $stmt = $conn->prepare("SELECT id, full_name, email, role, vip_status, mentor_status, created_at FROM users WHERE full_name LIKE CONCAT('%',?,'%') OR email LIKE CONCAT('%',?,'%') ORDER BY id DESC");
    $stmt->bind_param('ss', $search, $search);
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $users = $conn->query("SELECT id, full_name, email, role, vip_status, mentor_status, created_at FROM users ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
}

$activeTab = 'users-admin';
$basePath = '../';
$pageTitle = 'ManTrading - Kelola User';
require_once __DIR__ . '/../includes/head.php';

function statusBadge(string $label, string $status): string {
    $map = [
        'none'     => 'bg-gray-100 text-gray-500 border-gray-200',
        'pending'  => 'bg-amber-50 text-amber-600 border-amber-200',
        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
    ];
    $cls = $map[$status] ?? $map['none'];
    return '<span class="text-[9px] font-bold uppercase px-2 py-1 rounded-full border ' . $cls . '">' . $label . ': ' . e($status) . '</span>';
}
?>
<div class="flex bg-gray-50 lg:h-screen lg:overflow-hidden">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="flex-1 flex flex-col min-w-0 lg:overflow-hidden">
    <header class="bg-white px-6 py-5 border-b border-gray-200 flex justify-between items-center z-10 sticky top-0 shadow-sm">
      <div class="flex items-center gap-4">
        <button onclick="toggleSidebar(true)" class="lg:hidden text-gray-500 hover:text-gray-800"><i class="fa-solid fa-bars text-xl"></i></button>
        <div>
          <h1 class="text-xl md:text-2xl font-bold text-gray-800 tracking-tight">Kelola User</h1>
          <p class="text-gray-500 text-xs md:text-sm mt-0.5">Lihat semua user, atur role Admin, dan cabut akses VIP/Mentor kalau perlu.</p>
        </div>
      </div>
    </header>

    <main class="flex-1 lg:overflow-y-auto p-4 md:p-6 lg:p-8 custom-scrollbar bg-gray-50/50">
      <div class="max-w-5xl mx-auto flex flex-col gap-6 animate-fade-in-up">

        <?php if ($flash): ?>
          <div class="text-xs font-semibold rounded-xl p-3 <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>">
            <?= e($flash['msg']) ?>
          </div>
        <?php endif; ?>

        <form method="GET" class="flex gap-2">
          <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama atau email..." class="flex-1 bg-white border border-gray-300 text-gray-900 p-2.5 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
          <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg font-semibold text-sm transition-all shrink-0"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
          <div class="p-5 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2"><i class="fa-solid fa-users w-5 text-center text-indigo-500"></i> Semua User (<?= count($users) ?>)</h2>
          </div>
          <?php if (!$users): ?>
            <p class="p-6 text-sm text-gray-500 text-center">Tidak ada user ditemukan.</p>
          <?php else: foreach ($users as $u): ?>
            <div class="p-4 md:p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <p class="font-bold text-gray-800 text-sm"><?= e($u['full_name']) ?></p>
                  <?php if ((int)$u['id'] === $user['id']): ?>
                    <span class="text-[9px] font-bold uppercase px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-200">Lu</span>
                  <?php endif; ?>
                  <span class="text-[9px] font-bold uppercase px-2 py-1 rounded-full border <?= $u['role'] === 'admin' ? 'bg-indigo-50 text-indigo-600 border-indigo-200' : 'bg-gray-100 text-gray-500 border-gray-200' ?>"><?= e($u['role']) ?></span>
                  <?= statusBadge('VIP', $u['vip_status']) ?>
                  <?= statusBadge('Mentor', $u['mentor_status']) ?>
                </div>
                <p class="text-xs text-gray-500 mt-1"><?= e($u['email']) ?></p>
              </div>

              <?php if ((int)$u['id'] !== $user['id']): ?>
              <div class="flex flex-wrap gap-2 shrink-0">
                <?php if ($u['role'] === 'admin'): ?>
                  <form method="POST" onsubmit="return confirm('Cabut role Admin dari <?= e(addslashes($u['full_name'])) ?>?');">
<?= csrf_field() ?>
                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                    <input type="hidden" name="action" value="make_user">
                    <button type="submit" class="bg-white hover:bg-gray-50 text-gray-600 border border-gray-300 px-3 py-1.5 rounded-lg text-[11px] font-bold transition-all">Cabut Admin</button>
                  </form>
                <?php else: ?>
                  <form method="POST" onsubmit="return confirm('Jadikan <?= e(addslashes($u['full_name'])) ?> Admin?');">
<?= csrf_field() ?>
                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                    <input type="hidden" name="action" value="make_admin">
                    <button type="submit" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-600 border border-indigo-200 px-3 py-1.5 rounded-lg text-[11px] font-bold transition-all">Jadikan Admin</button>
                  </form>
                <?php endif; ?>

                <?php if ($u['mentor_status'] === 'approved'): ?>
                  <form method="POST" onsubmit="return confirm('Cabut status Mentor dari <?= e(addslashes($u['full_name'])) ?>?');">
<?= csrf_field() ?>
                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                    <input type="hidden" name="action" value="revoke_mentor">
                    <button type="submit" class="bg-white hover:bg-gray-50 text-amber-600 border border-amber-200 px-3 py-1.5 rounded-lg text-[11px] font-bold transition-all">Cabut Mentor</button>
                  </form>
                <?php endif; ?>

                <?php if ($u['vip_status'] === 'approved'): ?>
                  <form method="POST" onsubmit="return confirm('Cabut akses VIP dari <?= e(addslashes($u['full_name'])) ?>?');">
<?= csrf_field() ?>
                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                    <input type="hidden" name="action" value="revoke_vip">
                    <button type="submit" class="bg-white hover:bg-gray-50 text-purple-600 border border-purple-200 px-3 py-1.5 rounded-lg text-[11px] font-bold transition-all">Cabut VIP</button>
                  </form>
                <?php endif; ?>

                <form method="POST" onsubmit="return confirm('Hapus user <?= e(addslashes($u['full_name'])) ?> secara permanen? Semua journal & postingan miliknya ikut terhapus.');">
<?= csrf_field() ?>
                  <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                  <input type="hidden" name="action" value="delete">
                  <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 px-3 py-1.5 rounded-lg text-[11px] font-bold transition-all"><i class="fa-solid fa-trash-can"></i></button>
                </form>
              </div>
              <?php endif; ?>
            </div>
          <?php endforeach; endif; ?>
        </div>

      </div>
    </main>
  </div>
</div>
</body>
</html>
