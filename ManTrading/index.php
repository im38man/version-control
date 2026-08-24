<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$user = current_user();
$activeTab = 'journal';
$flash = flash_get();

// ---------- Ambil daftar bulan yang tersedia untuk filter ----------
$monthsList = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
];

$stmt = $conn->prepare("SELECT DISTINCT DATE_FORMAT(trade_date, '%Y-%m') AS ym FROM trades WHERE user_id = ? ORDER BY ym DESC");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$availableMonths = ['All'];
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $availableMonths[] = $row['ym'];
}
$stmt->close();

$filterMonth = $_GET['month'] ?? 'All';
if (!in_array($filterMonth, $availableMonths, true)) {
    $filterMonth = 'All';
}

function formatMonthDisplay(string $ym, array $monthsList): string {
    if ($ym === 'All') return 'Semua Waktu';
    [$y, $m] = explode('-', $ym);
    return ($monthsList[$m] ?? $m) . ' ' . $y;
}

// ---------- Ambil trades sesuai filter ----------
if ($filterMonth === 'All') {
    $stmt = $conn->prepare('SELECT * FROM trades WHERE user_id = ? ORDER BY trade_date DESC, id DESC');
    $stmt->bind_param('i', $user['id']);
} else {
    $stmt = $conn->prepare("SELECT * FROM trades WHERE user_id = ? AND DATE_FORMAT(trade_date, '%Y-%m') = ? ORDER BY trade_date DESC, id DESC");
    $stmt->bind_param('is', $user['id'], $filterMonth);
}
$stmt->execute();
$trades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ---------- Hitung statistik ----------
$totalUSD = 0; $totalPips = 0; $winCount = 0; $closedCount = 0;
foreach ($trades as $t) {
    $totalUSD += (float)$t['usd'];
    $totalPips += (float)$t['pips'];
    if ($t['pnl_status'] === 'Profit') $winCount++;
    if ($t['pnl_status'] !== 'Pending') $closedCount++;
}
$winRate = $closedCount > 0 ? round(($winCount / $closedCount) * 100, 1) : 0;

function badgeStyle(string $status): string {
    switch ($status) {
        case 'Profit': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        case 'Loss': return 'bg-rose-100 text-rose-700 border-rose-200';
        case 'Breakeven': return 'bg-gray-100 text-gray-700 border-gray-300';
        default: return 'bg-amber-100 text-amber-700 border-amber-200';
    }
}

$editTrade = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $conn->prepare('SELECT * FROM trades WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $editId, $user['id']);
    $stmt->execute();
    $editTrade = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$pageTitle = 'ManTrading Dashboard';
require_once __DIR__ . '/includes/head.php';
?>
<div class="flex bg-gray-50 lg:h-screen lg:overflow-hidden">
  <?php require __DIR__ . '/includes/sidebar.php'; ?>

  <div class="flex-1 flex flex-col min-w-0 lg:overflow-hidden">
    <header class="bg-white px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-200 flex flex-wrap justify-between items-center gap-3 z-10 sticky top-0 shadow-sm">
      <div class="flex items-center gap-3 sm:gap-4 min-w-0">
        <button onclick="toggleSidebar(true)" class="lg:hidden text-gray-500 hover:text-gray-800 shrink-0">
          <i class="fa-solid fa-bars text-xl"></i>
        </button>
        <div class="min-w-0">
          <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-800 tracking-tight truncate">Dashboard Jurnal</h1>
          <p class="text-gray-500 text-[11px] sm:text-xs md:text-sm mt-0.5 truncate">Catat setiap eksekusi, pelajari hasilnya.</p>
        </div>
      </div>
      <button onclick="openTradeModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg font-semibold transition-all shadow-lg shadow-indigo-600/25 flex items-center gap-2 text-xs sm:text-sm md:text-base shrink-0">
        <i class="fa-solid fa-plus"></i> <span class="hidden sm:inline">New Journal</span>
      </button>
    </header>

    <main class="flex-1 lg:overflow-y-auto p-3 sm:p-4 md:p-6 lg:p-8 custom-scrollbar bg-gray-50/50">
      <div class="flex flex-col gap-4 sm:gap-6 animate-fade-in-up min-w-0">

        <?php if ($flash): ?>
          <div class="text-xs font-semibold rounded-xl p-3 <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>">
            <?= e($flash['msg']) ?>
          </div>
        <?php endif; ?>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
          <div class="bg-white rounded-2xl border border-gray-200 p-3 sm:p-4 shadow-sm min-w-0">
            <p class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1 truncate">Net Profit (<?= e(formatMonthDisplay($filterMonth, $monthsList)) ?>)</p>
            <h3 class="text-base sm:text-lg font-extrabold font-mono <?= $totalUSD >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
              <?= $totalUSD >= 0 ? '+$' . number_format($totalUSD, 2) : '-$' . number_format(abs($totalUSD), 2) ?>
            </h3>
          </div>
          <div class="bg-white rounded-2xl border border-gray-200 p-3 sm:p-4 shadow-sm min-w-0">
            <p class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Net Pips</p>
            <h3 class="text-base sm:text-lg font-extrabold font-mono <?= $totalPips >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
              <?= ($totalPips >= 0 ? '+' : '') . number_format($totalPips, 1) ?>
            </h3>
          </div>
          <div class="bg-white rounded-2xl border border-gray-200 p-3 sm:p-4 shadow-sm min-w-0">
            <p class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Win Rate</p>
            <h3 class="text-base sm:text-lg font-extrabold font-mono text-gray-800"><?= $winRate ?>%</h3>
          </div>
          <div class="bg-white rounded-2xl border border-gray-200 p-3 sm:p-4 shadow-sm min-w-0">
            <p class="text-[9px] sm:text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Trades</p>
            <h3 class="text-base sm:text-lg font-extrabold font-mono text-gray-800"><?= count($trades) ?></h3>
          </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm min-w-0">
          <div class="p-3 sm:p-4 border-b border-gray-200 flex flex-wrap justify-between items-center bg-white gap-3 sm:gap-4">
            <h2 class="text-base sm:text-lg font-bold text-gray-800 flex items-center gap-2">
              <i class="fa-solid fa-list-ul text-indigo-500"></i> Trade History
            </h2>
            <form method="GET" class="flex items-center gap-2">
              <span class="text-xs font-bold text-gray-500 uppercase">Filter:</span>
              <select name="month" onchange="this.form.submit()" class="bg-gray-50 border border-gray-300 text-gray-800 text-xs sm:text-sm font-semibold rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none p-2 pr-8 cursor-pointer">
                <?php foreach ($availableMonths as $m): ?>
                  <option value="<?= e($m) ?>" <?= $m === $filterMonth ? 'selected' : '' ?>><?= e(formatMonthDisplay($m, $monthsList)) ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </div>

          <div class="overflow-x-auto w-full">
            <table class="w-full text-sm text-left whitespace-nowrap">
              <thead class="bg-gray-50 text-gray-500 uppercase text-[11px] font-bold tracking-wider">
                <tr>
                  <th class="px-5 py-4">Date</th>
                  <th class="px-5 py-4">Asset</th>
                  <th class="px-5 py-4">Entry</th>
                  <th class="px-5 py-4">Lot</th>
                  <th class="px-5 py-4">Status</th>
                  <th class="px-5 py-4 text-right">Pips</th>
                  <th class="px-5 py-4 text-right">P/L (USD)</th>
                  <th class="px-5 py-4">TP Kena</th>
                  <th class="px-5 py-4 text-center">Pict</th>
                  <th class="px-5 py-4 text-center">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <?php if (!$trades): ?>
                  <tr><td colspan="10" class="px-6 py-12 text-center"><p class="font-medium text-gray-500">Tidak ada data di periode ini bro.</p></td></tr>
                <?php else: foreach ($trades as $t): ?>
                  <tr class="hover:bg-indigo-50/30 transition-colors">
                    <td class="px-5 py-3 font-medium text-gray-500 text-xs"><?= e(date('d M Y', strtotime($t['trade_date']))) ?></td>
                    <td class="px-5 py-3 font-extrabold text-gray-800 uppercase"><?= e($t['pair']) ?></td>
                    <td class="px-5 py-3 font-mono font-medium text-gray-600"><?= n($t['entry']) ?></td>
                    <td class="px-5 py-3 font-mono font-medium text-gray-600"><?= n($t['lot']) ?></td>
                    <td class="px-5 py-3"><span class="px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider border <?= badgeStyle($t['pnl_status']) ?>"><?= e($t['pnl_status']) ?></span></td>
                    <td class="px-5 py-3 text-right font-mono font-extrabold <?= $t['pips'] > 0 ? 'text-emerald-500' : ($t['pips'] < 0 ? 'text-rose-500' : 'text-gray-400') ?>">
                      <?= $t['pips'] !== null ? (($t['pips'] > 0 ? '+' : '') . n($t['pips'])) : '-' ?>
                    </td>
                    <td class="px-5 py-3 text-right font-mono font-extrabold <?= $t['usd'] > 0 ? 'text-emerald-500' : ($t['usd'] < 0 ? 'text-rose-500' : 'text-gray-400') ?>">
                      <?= $t['usd'] !== null ? (($t['usd'] > 0 ? '+$' . n($t['usd']) : '-$' . n(abs($t['usd'])))) : '-' ?>
                    </td>
                    <td class="px-5 py-3">
                      <?php
                        $tpBadges = [];
                        for ($i = 1; $i <= 5; $i++) {
                          if (!empty($t["tp{$i}_hit"])) {
                            $priceLabel = $t["tp{$i}_price"] !== null ? ' (' . n($t["tp{$i}_price"]) . ')' : '';
                            $tpBadges[] = ['label' => "1:$i" . $priceLabel];
                          }
                        }
                      ?>
                      <?php if (!$tpBadges): ?>
                        <span class="text-gray-300 text-xs">-</span>
                      <?php else: ?>
                        <div class="flex flex-wrap gap-1">
                          <?php foreach ($tpBadges as $b): ?>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200 whitespace-nowrap"><?= e($b['label']) ?></span>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td class="px-5 py-3 text-center">
                      <?php if ($t['image_before'] || $t['image_after']): ?>
                        <div class="flex gap-2 justify-center">
                          <?php if ($t['image_before']): ?><a href="<?= e($t['image_before']) ?>" target="_blank" class="text-blue-500 hover:text-blue-700" title="Before"><i class="fa-solid fa-image"></i></a><?php endif; ?>
                          <?php if ($t['image_after']): ?><a href="<?= e($t['image_after']) ?>" target="_blank" class="text-blue-500 hover:text-blue-700" title="After"><i class="fa-solid fa-image"></i></a><?php endif; ?>
                        </div>
                      <?php else: ?><span class="text-gray-300">-</span><?php endif; ?>
                    </td>
                    <td class="px-5 py-3 text-center">
                      <div class="flex items-center justify-center gap-1.5">
                        <a href="index.php?edit=<?= (int)$t['id'] ?><?= $filterMonth !== 'All' ? '&month=' . urlencode($filterMonth) : '' ?>#tradeModal" onclick="openTradeModal()" class="text-gray-400 hover:text-amber-500 w-8 h-8 rounded-lg bg-gray-50 border border-gray-200 hover:border-amber-200 transition-all flex items-center justify-center" title="Edit">
                          <i class="fa-solid fa-pen text-xs"></i>
                        </a>
                        <form method="POST" action="trade-delete.php" onsubmit="return confirm('Hapus setup <?= e($t['pair']) ?> tanggal <?= e(date('d M Y', strtotime($t['trade_date']))) ?>?');">
                          <?= csrf_field() ?>
                          <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                          <button type="submit" class="text-gray-400 hover:text-rose-500 w-8 h-8 rounded-lg bg-gray-50 border border-gray-200 hover:border-rose-200 transition-all flex items-center justify-center" title="Delete">
                            <i class="fa-solid fa-trash text-xs"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- MODAL FORM TAMBAH/EDIT TRADE -->
<div id="tradeModal" class="fixed inset-0 z-[100] <?= $editTrade ? 'flex' : 'hidden' ?> items-center justify-center p-4 sm:p-6">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeTradeModal()"></div>
  <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[95vh] overflow-y-auto custom-scrollbar animate-fade-in-up">
    <div class="flex justify-between items-center p-6 border-b border-gray-100 bg-gray-50/50 rounded-t-2xl sticky top-0 z-10">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white shadow-md <?= $editTrade ? 'bg-amber-500' : 'bg-indigo-600' ?>">
          <i class="fa-solid <?= $editTrade ? 'fa-pen' : 'fa-plus' ?>"></i>
        </div>
        <div>
          <h2 class="text-lg font-bold text-gray-800"><?= $editTrade ? 'Edit Trade Setup' : 'Log New Setup' ?></h2>
          <p class="text-xs text-gray-500 mt-0.5">Isi detail setup trading lu dengan lengkap.</p>
        </div>
      </div>
      <button onclick="closeTradeModal()" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-red-500 transition-colors flex items-center justify-center">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form method="POST" action="trade-save.php" class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
<?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= $editTrade ? (int)$editTrade['id'] : '' ?>">
      <div class="space-y-1.5 lg:col-span-2">
        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal Setup <span class="text-red-500">*</span></label>
        <input type="date" name="date" required value="<?= e($editTrade['trade_date'] ?? date('Y-m-d')) ?>" class="w-full bg-gray-50 border border-gray-300 text-gray-900 p-2.5 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
      </div>
      <div class="space-y-1.5 lg:col-span-2">
        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pair / Asset <span class="text-red-500">*</span></label>
        <div class="relative">
          <i class="fa-solid fa-coins absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
          <input type="text" name="pair" placeholder="XAUUSD" required value="<?= e($editTrade['pair'] ?? '') ?>" class="w-full bg-gray-50 border border-gray-300 text-gray-900 pl-9 p-2.5 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none uppercase transition-all">
        </div>
      </div>
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Entry <span class="text-red-500">*</span></label>
        <input type="number" step="any" name="entry" placeholder="0.00" required value="<?= n($editTrade['entry'] ?? '') ?>" class="w-full bg-gray-50 border border-gray-300 text-gray-900 p-2.5 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
      </div>
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Zona SL (Stop Loss)</label>
        <input type="number" step="any" name="sl" placeholder="0.00" value="<?= n($editTrade['sl'] ?? '') ?>" class="w-full bg-gray-50 border border-gray-300 text-gray-900 p-2.5 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
      </div>
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Lot <span class="text-red-500">*</span></label>
        <input type="number" step="any" name="lot" placeholder="0.01" required value="<?= n($editTrade['lot'] ?? '') ?>" class="w-full bg-gray-50 border border-gray-300 text-gray-900 p-2.5 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
      </div>
      <div class="space-y-1.5 lg:col-span-2">
        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Image Before (URL)</label>
        <div class="relative">
          <i class="fa-solid fa-link absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
          <input type="url" name="link_before" placeholder="https://..." value="<?= e($editTrade['image_before'] ?? '') ?>" class="w-full bg-gray-50 border border-gray-300 text-gray-900 pl-9 p-2.5 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
        </div>
      </div>
      <div class="space-y-1.5 lg:col-span-2">
        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Image After (URL)</label>
        <div class="relative">
          <i class="fa-solid fa-link absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
          <input type="url" name="link_after" placeholder="https://..." value="<?= e($editTrade['image_after'] ?? '') ?>" class="w-full bg-gray-50 border border-gray-300 text-gray-900 pl-9 p-2.5 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
        </div>
      </div>

      <div class="space-y-1.5 lg:col-span-4 mt-2">
        <div class="h-px w-full bg-gray-200 my-2"></div>
        <h3 class="text-sm font-bold text-gray-700 mb-2"><i class="fa-solid fa-bullseye mr-2 text-indigo-500"></i>Tabel Risk:Reward (Hasil per Level)</h3>
        <p class="text-[11px] text-gray-400 -mt-1 mb-2">Centang level yang beneran kena, isi Pips & USD-nya masing-masing. SL sekarang trailing per level (bisa digeser tiap TP kena, misal ke BE). Total & status trade dihitung otomatis dari sini — SL otomatis diitung minus, TP otomatis plus.</p>
      </div>

      <div class="lg:col-span-4">
        <div class="overflow-x-auto rounded-lg border border-gray-200">
          <table class="w-full text-xs sm:text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-bold tracking-wider">
              <tr>
                <th class="px-3 py-2.5 text-left">Level</th>
                <th class="px-3 py-2.5 text-left">Zona Harga</th>
                <th class="px-3 py-2.5 text-center">Kena</th>
                <th class="px-3 py-2.5 text-left">Pips</th>
                <th class="px-3 py-2.5 text-left">USD</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr class="bg-gray-50/40">
                <td class="px-3 py-2 font-bold text-gray-400">Entry</td>
                <td class="px-3 py-2 font-mono text-gray-500" id="rrEntryDisplay">-</td>
                <td class="px-3 py-2 text-center text-gray-300">-</td>
                <td class="px-3 py-2 text-gray-300">-</td>
                <td class="px-3 py-2 text-gray-300">-</td>
              </tr>
              <tr class="bg-gray-50/40">
                <td class="px-3 py-2 font-bold text-gray-400">SL Awal</td>
                <td class="px-3 py-2 font-mono text-gray-500" id="rrSlDisplay">-</td>
                <td class="px-3 py-2 text-center text-gray-300" colspan="3">referensi doang, isi trailing SL-nya di baris SL 1:1-1:5 di bawah</td>
              </tr>
              <?php for ($i = 1; $i <= 5; $i++):
                $slChecked = !empty($editTrade["sl{$i}_hit"]);
                $slPriceVal = n($editTrade["sl{$i}_price"] ?? '');
                $slPipsVal = n($editTrade["sl{$i}_pips"] ?? '');
                $slUsdVal = n($editTrade["sl{$i}_usd"] ?? '');
                $tpChecked = !empty($editTrade["tp{$i}_hit"]);
                $tpPriceVal = n($editTrade["tp{$i}_price"] ?? '');
                $tpPipsVal = n($editTrade["tp{$i}_pips"] ?? '');
                $tpUsdVal = n($editTrade["tp{$i}_usd"] ?? '');
              ?>
              <tr class="has-[:checked]:bg-rose-50/60 transition-all">
                <td class="px-3 py-2 font-bold text-rose-500">SL 1:<?= $i ?></td>
                <td class="px-3 py-2">
                  <input type="number" step="any" name="sl<?= $i ?>_price" value="<?= $slPriceVal ?>" placeholder="Harga SL fase ini" class="w-full max-w-[130px] bg-gray-50 border border-gray-300 text-gray-900 p-1.5 rounded-md text-xs font-mono focus:ring-2 focus:ring-rose-500/20 focus:border-rose-400 outline-none">
                </td>
                <td class="px-3 py-2 text-center">
                  <input type="checkbox" name="sl<?= $i ?>_hit" value="1" <?= $slChecked ? 'checked' : '' ?> class="accent-rose-600 w-4 h-4">
                </td>
                <td class="px-3 py-2">
                  <input type="number" step="any" name="sl<?= $i ?>_pips" value="<?= $slPipsVal ?>" placeholder="ex: 20" class="w-full max-w-[110px] bg-gray-50 border border-gray-300 text-gray-900 p-1.5 rounded-md text-xs font-mono focus:ring-2 focus:ring-rose-500/20 focus:border-rose-400 outline-none">
                </td>
                <td class="px-3 py-2">
                  <input type="number" step="any" name="sl<?= $i ?>_usd" value="<?= $slUsdVal ?>" placeholder="ex: 50" class="w-full max-w-[110px] bg-gray-50 border border-gray-300 text-gray-900 p-1.5 rounded-md text-xs font-mono focus:ring-2 focus:ring-rose-500/20 focus:border-rose-400 outline-none">
                </td>
              </tr>
              <tr class="has-[:checked]:bg-emerald-50/60 transition-all">
                <td class="px-3 py-2 font-bold text-gray-600">TP 1:<?= $i ?></td>
                <td class="px-3 py-2">
                  <input type="number" step="any" name="tp<?= $i ?>_price" value="<?= $tpPriceVal ?>" placeholder="Harga zona" class="w-full max-w-[130px] bg-gray-50 border border-gray-300 text-gray-900 p-1.5 rounded-md text-xs font-mono focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                </td>
                <td class="px-3 py-2 text-center">
                  <input type="checkbox" name="tp<?= $i ?>_hit" value="1" <?= $tpChecked ? 'checked' : '' ?> class="accent-emerald-600 w-4 h-4">
                </td>
                <td class="px-3 py-2">
                  <input type="number" step="any" name="tp<?= $i ?>_pips" value="<?= $tpPipsVal ?>" placeholder="ex: 30" class="w-full max-w-[110px] bg-gray-50 border border-gray-300 text-gray-900 p-1.5 rounded-md text-xs font-mono focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 outline-none">
                </td>
                <td class="px-3 py-2">
                  <input type="number" step="any" name="tp<?= $i ?>_usd" value="<?= $tpUsdVal ?>" placeholder="ex: 75" class="w-full max-w-[110px] bg-gray-50 border border-gray-300 text-gray-900 p-1.5 rounded-md text-xs font-mono focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 outline-none">
                </td>
              </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
        <p class="text-[10px] text-gray-400 mt-2">Isi Pips/USD pakai angka positif aja di semua baris — tanda plus/minusnya otomatis diatur sistem sesuai barisnya (SL selalu minus, TP selalu plus). Baris SL 1:1-1:5 dipakai buat catat SL yang digeser (misal abis TP1 kena, SL 1:2 diisi harga BE).</p>
      </div>

      <script>
        (function() {
          const entryInput = document.querySelector('#tradeModal input[name="entry"]');
          const slInput = document.querySelector('#tradeModal input[name="sl"]');
          const entryDisplay = document.getElementById('rrEntryDisplay');
          const slDisplay = document.getElementById('rrSlDisplay');
          function sync() {
            entryDisplay.textContent = entryInput.value !== '' ? entryInput.value : '-';
            slDisplay.textContent = slInput.value !== '' ? slInput.value : '-';
          }
          entryInput.addEventListener('input', sync);
          slInput.addEventListener('input', sync);
          sync();
        })();
      </script>

      <div class="lg:col-span-4 flex justify-end gap-3 mt-6 pt-6 border-t border-gray-100">
        <button type="button" onclick="closeTradeModal()" class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-6 py-2.5 rounded-lg font-semibold transition-all">Cancel</button>
        <button type="submit" class="<?= $editTrade ? 'bg-amber-500 hover:bg-amber-600 shadow-amber-500/25' : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-600/25' ?> text-white px-8 py-2.5 rounded-lg font-semibold transition-all shadow-lg flex items-center gap-2">
          <i class="fa-solid <?= $editTrade ? 'fa-check' : 'fa-save' ?>"></i> <?= $editTrade ? 'Update Journal' : 'Save Journal' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function openTradeModal() {
    const m = document.getElementById('tradeModal');
    m.classList.remove('hidden'); m.classList.add('flex');
  }
  function closeTradeModal() {
    window.location.href = 'index.php<?= $filterMonth !== 'All' ? '?month=' . urlencode($filterMonth) : '' ?>';
  }
</script>
</body>
</html>
