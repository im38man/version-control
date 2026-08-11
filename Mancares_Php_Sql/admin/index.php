<?php
require_once __DIR__ . '/../includes/auth.php';
$admin = require_admin();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Sesi form kedaluwarsa, silakan reload halaman.';
    } else {
        $action = $_POST['action'] ?? '';
        $targetId = (int)($_POST['user_id'] ?? 0);

        if ($targetId === (int)$admin['id'] && in_array($action, ['ban', 'delete', 'demote'], true)) {
            $errors[] = 'Tidak bisa melakukan aksi ini pada akun sendiri.';
        } elseif ($targetId) {
            switch ($action) {
                case 'ban':
                    $stmt = $pdo->prepare("UPDATE users SET status = 'banned' WHERE id = ?");
                    $stmt->execute([$targetId]);
                    $success = 'User diblokir.';
                    break;
                case 'unban':
                    $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
                    $stmt->execute([$targetId]);
                    $success = 'User diaktifkan kembali.';
                    break;
                case 'promote':
                    $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
                    $stmt->execute([$targetId]);
                    $success = 'User dijadikan admin.';
                    break;
                case 'demote':
                    $stmt = $pdo->prepare("UPDATE users SET role = 'user' WHERE id = ?");
                    $stmt->execute([$targetId]);
                    $success = 'Admin dijadikan user biasa.';
                    break;
                case 'delete':
                    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
                    $stmt->execute([$targetId]);
                    $success = 'User & seluruh datanya dihapus.';
                    break;
                case 'reset_password':
                    $newPass = bin2hex(random_bytes(4)); // 8 karakter acak
                    $hash = password_hash($newPass, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                    $stmt->execute([$hash, $targetId]);
                    $success = "Password direset ke: $newPass (catat sekarang, tidak ditampilkan lagi)";
                    break;
                default:
                    $errors[] = 'Aksi tidak dikenali.';
            }
        }
    }
}

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare('SELECT id, username, email, role, status, income, created_at FROM users WHERE username LIKE ? OR email LIKE ? ORDER BY id DESC');
    $like = '%' . $search . '%';
    $stmt->execute([$like, $like]);
} else {
    $stmt = $pdo->query('SELECT id, username, email, role, status, income, created_at FROM users ORDER BY id DESC');
}
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola User | MANCARES Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
<style>body{font-family:'Plus Jakarta Sans',sans-serif;background-color:#f8fafc;} .font-mono-custom{font-family:'Space Grotesk',monospace;}</style>
</head>
<body class="text-slate-800">
    <header class="bg-slate-900 text-white px-6 lg:px-10 py-5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-xl">M</div>
            <div>
                <h1 class="text-lg font-bold tracking-tight">MANCARES<span class="text-slate-500">.</span> Admin</h1>
                <p class="text-[10px] font-medium text-slate-400 tracking-widest uppercase">Kelola User</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="../index.php" class="text-xs font-semibold bg-slate-800 hover:bg-slate-700 px-4 py-2 rounded-xl transition">Ke Dashboard</a>
            <a href="../logout.php" class="text-xs font-semibold bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 px-4 py-2 rounded-xl transition">Logout</a>
        </div>
    </header>

    <main class="p-6 lg:p-10 max-w-6xl mx-auto space-y-6">
        <?php if ($errors): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-600 text-sm rounded-xl p-4 space-y-1">
            <?php foreach ($errors as $e): ?><p>&bull; <?= htmlspecialchars($e) ?></p><?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl p-4 font-mono-custom"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                <h3 class="text-lg font-bold text-slate-800">Daftar User (<?= count($users) ?>)</h3>
                <form method="GET" class="flex gap-2">
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari username / email..." class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm">
                    <button class="bg-slate-900 text-white font-bold px-4 py-2.5 rounded-xl text-sm">Cari</button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[850px]">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 text-xs uppercase tracking-widest">
                            <th class="p-4 font-bold">ID</th>
                            <th class="p-4 font-bold">Username</th>
                            <th class="p-4 font-bold">Email</th>
                            <th class="p-4 font-bold">Role</th>
                            <th class="p-4 font-bold">Status</th>
                            <th class="p-4 font-bold">Daftar</th>
                            <th class="p-4 font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="p-4 text-slate-400 font-mono-custom">#<?= $u['id'] ?></td>
                            <td class="p-4 font-bold text-slate-800"><?= htmlspecialchars($u['username']) ?> <?= $u['id'] == $admin['id'] ? '<span class="text-[10px] text-slate-400">(kamu)</span>' : '' ?></td>
                            <td class="p-4 text-slate-600"><?= htmlspecialchars($u['email']) ?></td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded text-xs font-bold <?= $u['role'] === 'admin' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600' ?>"><?= htmlspecialchars($u['role']) ?></span>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded text-xs font-bold <?= $u['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>"><?= $u['status'] === 'active' ? 'Aktif' : 'Diblokir' ?></span>
                            </td>
                            <td class="p-4 text-slate-500 text-xs"><?= htmlspecialchars(date('d M Y', strtotime($u['created_at']))) ?></td>
                            <td class="p-4">
                                <div class="flex flex-wrap gap-1.5 justify-center">
                                    <form method="POST" onsubmit="return confirm('Reset password user ini?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="action" value="reset_password">
                                        <button class="text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1.5 rounded-lg">Reset Pass</button>
                                    </form>
                                    <?php if ($u['status'] === 'active'): ?>
                                    <form method="POST" onsubmit="return confirm('Blokir user ini?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="action" value="ban">
                                        <button class="text-[11px] font-bold bg-amber-100 hover:bg-amber-200 text-amber-700 px-2.5 py-1.5 rounded-lg" <?= $u['id'] == $admin['id'] ? 'disabled' : '' ?>>Blokir</button>
                                    </form>
                                    <?php else: ?>
                                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="action" value="unban">
                                        <button class="text-[11px] font-bold bg-emerald-100 hover:bg-emerald-200 text-emerald-700 px-2.5 py-1.5 rounded-lg">Aktifkan</button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if ($u['role'] === 'user'): ?>
                                    <form method="POST" onsubmit="return confirm('Jadikan user ini admin?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="action" value="promote">
                                        <button class="text-[11px] font-bold bg-indigo-100 hover:bg-indigo-200 text-indigo-700 px-2.5 py-1.5 rounded-lg">Jadikan Admin</button>
                                    </form>
                                    <?php else: ?>
                                    <form method="POST" onsubmit="return confirm('Turunkan admin ini jadi user biasa?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="action" value="demote">
                                        <button class="text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1.5 rounded-lg" <?= $u['id'] == $admin['id'] ? 'disabled' : '' ?>>Jadikan User</button>
                                    </form>
                                    <?php endif; ?>
                                    <form method="POST" onsubmit="return confirm('Hapus user ini beserta SEMUA datanya? Tindakan tidak bisa dibatalkan!');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button class="text-[11px] font-bold bg-rose-100 hover:bg-rose-200 text-rose-700 px-2.5 py-1.5 rounded-lg" <?= $u['id'] == $admin['id'] ? 'disabled' : '' ?>>Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$users): ?>
                        <tr><td colspan="7" class="p-8 text-center text-slate-400">Tidak ada user ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
