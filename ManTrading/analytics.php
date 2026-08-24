<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$user = current_user();
$activeTab = 'analytics';

$monthsList = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
];

// ---------- Tahun yang tersedia ----------
$stmt = $conn->prepare("SELECT DISTINCT YEAR(trade_date) AS y FROM trades WHERE user_id = ? ORDER BY y DESC");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$availableYears = ['All'];
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $availableYears[] = (string)$row['y'];
}
$stmt->close();

$alyYear = $_GET['year'] ?? 'All';
if (!in_array($alyYear, $availableYears, true)) $alyYear = 'All';

$alyMonth = $_GET['month'] ?? 'All';
if ($alyMonth !== 'All' && !isset($monthsList[$alyMonth])) $alyMonth = 'All';

$alyDay = $_GET['day'] ?? 'All';
if ($alyDay !== 'All' && (!ctype_digit($alyDay) || (int)$alyDay < 1 || (int)$alyDay > 31)) $alyDay = 'All';
if ($alyMonth === 'All') $alyDay = 'All'; // tanggal cuma relevan kalau bulan sudah dipilih

// Jumlah hari di bulan terpilih (untuk isi dropdown tanggal)
$daysInMonth = 31;
if ($alyMonth !== 'All') {
    $yearForCalc = $alyYear !== 'All' ? (int)$alyYear : (int)date('Y');
    $daysInMonth = (int)date('t', mktime(0, 0, 0, (int)$alyMonth, 1, $yearForCalc));
}

// ---------- Query trades sesuai filter ----------
$sql = 'SELECT * FROM trades WHERE user_id = ?';
$types = 'i';
$params = [$user['id']];

if ($alyYear !== 'All') { $sql .= ' AND YEAR(trade_date) = ?'; $types .= 'i'; $params[] = (int)$alyYear; }
if ($alyMonth !== 'All') { $sql .= ' AND MONTH(trade_date) = ?'; $types .= 'i'; $params[] = (int)$alyMonth; }
if ($alyDay !== 'All') { $sql .= ' AND DAY(trade_date) = ?'; $types .= 'i'; $params[] = (int)$alyDay; }

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$trades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ---------- Hitung statistik ----------
$grossProfit = 0; $grossLoss = 0; $netPips = 0;
$win = 0; $loss = 0; $be = 0; $pending = 0;

foreach ($trades as $t) {
    $usd = (float)$t['usd'];
    if ($usd > 0) $grossProfit += $usd;
    if ($usd < 0) $grossLoss += $usd;
    $netPips += (float)$t['pips'];

    switch ($t['pnl_status']) {
        case 'Profit': $win++; break;
        case 'Loss': $loss++; break;
        case 'Breakeven': $be++; break;
        default: $pending++;
    }
}
$netProfit = $grossProfit + $grossLoss;
$totalClosed = $win + $loss + $be;
$winRate = $totalClosed > 0 ? round(($win / $totalClosed) * 100, 1) : 0;
$totalFiltered = count($trades);

// ---------- Hitung TP Hit Rate (TP1-TP5 & All TP) ----------
$tpHitCount = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
$allTpCount = 0;
foreach ($trades as $t) {
    $hits = 0;
    for ($i = 1; $i <= 5; $i++) {
        if (!empty($t["tp{$i}_hit"])) {
            $tpHitCount[$i]++;
            $hits++;
        }
    }
    if ($hits === 5) $allTpCount++;
}
$tpRate = [];
for ($i = 1; $i <= 5; $i++) {
    $tpRate[$i] = $totalFiltered > 0 ? round(($tpHitCount[$i] / $totalFiltered) * 100, 1) : 0;
}
$allTpRate = $totalFiltered > 0 ? round(($allTpCount / $totalFiltered) * 100, 1) : 0;

// ---------- Hitung SL Hit Rate (per level SL1-SL5, trailing) ----------
$slHitCountByLevel = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
$slHitCount = 0; // trade yang minimal 1x kena SL (di level manapun)
foreach ($trades as $t) {
    $anySl = false;
    for ($i = 1; $i <= 5; $i++) {
        if (!empty($t["sl{$i}_hit"])) {
            $slHitCountByLevel[$i]++;
            $anySl = true;
        }
    }
    if ($anySl) $slHitCount++;
}
$slRate = $totalFiltered > 0 ? round(($slHitCount / $totalFiltered) * 100, 1) : 0;
$slRateByLevel = [];
for ($i = 1; $i <= 5; $i++) {
    $slRateByLevel[$i] = $totalFiltered > 0 ? round(($slHitCountByLevel[$i] / $totalFiltered) * 100, 1) : 0;
}

$pageTitle = 'ManTrading - Advanced Analytics';
require_once __DIR__ . '/includes/head.php';
?>
<div class="flex bg-gray-50 lg:h-screen lg:overflow-hidden">
  <?php require __DIR__ . '/includes/sidebar.php'; ?>

  <div class="flex-1 flex flex-col min-w-0 lg:overflow-hidden">
    <header class="bg-white px-6 py-5 border-b border-gray-200 flex justify-between items-center z-10 sticky top-0 shadow-sm">
      <div class="flex items-center gap-4">
        <button onclick="toggleSidebar(true)" class="lg:hidden text-gray-500 hover:text-gray-800">
          <i class="fa-solid fa-bars text-xl"></i>
        </button>
        <div>
          <h1 class="text-xl md:text-2xl font-bold text-gray-800 tracking-tight">Data Analytics</h1>
          <p class="text-gray-500 text-xs md:text-sm mt-0.5">Analisa mendalam performa trading lu.</p>
        </div>
      </div>
    </header>

    <main class="flex-1 lg:overflow-y-auto p-4 md:p-6 lg:p-8 custom-scrollbar bg-gray-50/50">
      <div class="flex flex-col gap-6 animate-fade-in-up">

        <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
          <div class="flex items-center gap-2 mb-1 sm:mb-0">
            <i class="fa-solid fa-filter text-indigo-500"></i>
            <span class="text-sm font-bold text-gray-700">Filter Analisis:</span>
          </div>

          <select name="year" onchange="this.form.submit()" class="bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm font-semibold outline-none cursor-pointer w-full sm:w-auto">
            <?php foreach ($availableYears as $y): ?>
              <option value="<?= e($y) ?>" <?= $y === $alyYear ? 'selected' : '' ?>><?= $y === 'All' ? 'Semua Tahun' : e($y) ?></option>
            <?php endforeach; ?>
          </select>

          <select name="month" onchange="this.form.submit()" class="bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm font-semibold outline-none cursor-pointer w-full sm:w-auto">
            <option value="All" <?= $alyMonth === 'All' ? 'selected' : '' ?>>Semua Bulan</option>
            <?php foreach ($monthsList as $num => $name): ?>
              <option value="<?= $num ?>" <?= $num === $alyMonth ? 'selected' : '' ?>><?= $name ?></option>
            <?php endforeach; ?>
          </select>

          <select name="day" onchange="this.form.submit()" <?= $alyMonth === 'All' ? 'disabled' : '' ?> class="bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-sm font-semibold outline-none cursor-pointer w-full sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed">
            <option value="All" <?= $alyDay === 'All' ? 'selected' : '' ?>>Semua Tanggal</option>
            <?php for ($i = 1; $i <= $daysInMonth; $i++): $d = str_pad((string)$i, 2, '0', STR_PAD_LEFT); ?>
              <option value="<?= $d ?>" <?= $d === $alyDay ? 'selected' : '' ?>>Tgl <?= $d ?></option>
            <?php endfor; ?>
          </select>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

          <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm flex flex-col gap-4 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-6 opacity-5 pointer-events-none"><i class="fa-solid fa-dollar-sign text-8xl"></i></div>
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Profitability</h3>
            <div>
              <p class="text-xs text-gray-500 mb-1">Net Profit</p>
              <h2 class="text-3xl font-extrabold font-mono <?= $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
                <?= $netProfit >= 0 ? '+$' . number_format($netProfit, 2) : '-$' . number_format(abs($netProfit), 2) ?>
              </h2>
            </div>
            <div class="flex justify-between items-center pt-4 border-t border-gray-100">
              <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase">Gross Profit</p>
                <p class="text-sm font-bold font-mono text-emerald-500">+$<?= number_format($grossProfit, 2) ?></p>
              </div>
              <div class="text-right">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Gross Loss</p>
                <p class="text-sm font-bold font-mono text-rose-500">-$<?= number_format(abs($grossLoss), 2) ?></p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm flex flex-col gap-4 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-6 opacity-5 pointer-events-none"><i class="fa-solid fa-bullseye text-8xl"></i></div>
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Performance (Win Rate)</h3>
            <div class="flex items-end gap-2">
              <h2 class="text-4xl font-extrabold font-mono text-gray-800"><?= $winRate ?>%</h2>
            </div>
            <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden flex mt-2">
              <div style="width: <?= $totalClosed > 0 ? ($win / $totalClosed) * 100 : 0 ?>%" class="h-full bg-emerald-500"></div>
              <div style="width: <?= $totalClosed > 0 ? ($loss / $totalClosed) * 100 : 0 ?>%" class="h-full bg-rose-500"></div>
              <div style="width: <?= $totalClosed > 0 ? ($be / $totalClosed) * 100 : 0 ?>%" class="h-full bg-gray-400"></div>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-2">
              <div class="text-center bg-emerald-50 rounded-lg p-2 border border-emerald-100">
                <p class="text-[10px] font-bold text-emerald-600 uppercase">Win</p>
                <p class="text-sm font-bold text-emerald-700"><?= $win ?></p>
              </div>
              <div class="text-center bg-rose-50 rounded-lg p-2 border border-rose-100">
                <p class="text-[10px] font-bold text-rose-600 uppercase">Loss</p>
                <p class="text-sm font-bold text-rose-700"><?= $loss ?></p>
              </div>
              <div class="text-center bg-gray-100 rounded-lg p-2 border border-gray-200">
                <p class="text-[10px] font-bold text-gray-500 uppercase">BE</p>
                <p class="text-sm font-bold text-gray-600"><?= $be ?></p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm flex flex-col justify-between">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Trade Stats</h3>
            <div class="space-y-4">
              <div class="flex justify-between items-center p-3 bg-indigo-50/50 rounded-xl border border-indigo-100/50">
                <span class="text-xs font-bold text-gray-600">Total Eksekusi (Filtered)</span>
                <span class="font-mono font-extrabold text-indigo-600 text-lg"><?= $totalFiltered ?></span>
              </div>
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                <span class="text-xs font-bold text-gray-600">Net Pips</span>
                <span class="font-mono font-extrabold text-lg <?= $netPips >= 0 ? 'text-emerald-500' : 'text-rose-500' ?>">
                  <?= $netPips >= 0 ? '+' . number_format($netPips, 1) : number_format($netPips, 1) ?>
                </span>
              </div>
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                <span class="text-xs font-bold text-gray-600">Pending Setup</span>
                <span class="font-mono font-extrabold text-amber-500 text-lg"><?= $pending ?></span>
              </div>
            </div>
          </div>

        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
          <div class="flex items-center justify-between mb-5">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Win Rate per Risk:Reward</h3>
            <span class="text-[10px] text-gray-400 font-medium">Seberapa sering SL & tiap level TP kena (dari total trade terfilter)</span>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3">
            <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-4 text-center">
              <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider mb-1">Win Rate</p>
              <p class="text-2xl font-extrabold font-mono text-indigo-600"><?= $winRate ?>%</p>
            </div>
            <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-4 text-center">
              <p class="text-[10px] font-bold text-rose-500 uppercase tracking-wider mb-1">SL (Total)</p>
              <p class="text-2xl font-extrabold font-mono text-rose-600"><?= $slRate ?>%</p>
              <p class="text-[10px] text-rose-400 mt-0.5"><?= $slHitCount ?>/<?= $totalFiltered ?> trade</p>
            </div>
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-center">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">1:<?= $i ?></p>
                <p class="text-lg font-extrabold font-mono text-emerald-600">TP <?= $tpRate[$i] ?>%</p>
                <p class="text-[10px] text-gray-400"><?= $tpHitCount[$i] ?>/<?= $totalFiltered ?></p>
                <p class="text-lg font-extrabold font-mono text-rose-600 mt-1">SL <?= $slRateByLevel[$i] ?>%</p>
                <p class="text-[10px] text-gray-400"><?= $slHitCountByLevel[$i] ?>/<?= $totalFiltered ?></p>
              </div>
            <?php endfor; ?>
          </div>
          <div class="mt-3 rounded-xl border border-emerald-100 bg-emerald-50/50 p-4 flex items-center justify-between">
            <div>
              <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">All TP (TP1-TP5 semua kena)</p>
              <p class="text-[10px] text-emerald-500 mt-0.5"><?= $allTpCount ?> dari <?= $totalFiltered ?> trade</p>
            </div>
            <p class="text-2xl font-extrabold font-mono text-emerald-600"><?= $allTpRate ?>%</p>
          </div>
        </div>

      </div>
    </main>
  </div>
</div>
</body>
</html>
