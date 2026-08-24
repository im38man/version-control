<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_login();

$stmt = $pdo->prepare('SELECT id, name, number, color, img_network AS imgNetwork, img_bank AS imgBank, img_local AS imgLocal, is_default AS isDefault FROM accounts WHERE user_id = ? ORDER BY id ASC');
$stmt->execute([$user['id']]);
$initialAccounts = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT id, name, pct FROM allocations WHERE user_id = ? ORDER BY id ASC');
$stmt->execute([$user['id']]);
$initialAllocations = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT t.id, t.account_id AS accountId, t.type, t.description AS `desc`, t.amount, t.tx_date AS date, t.transfer_id AS transferId, t.debt_id AS debtId, d.type AS debtType FROM transactions t LEFT JOIN debts d ON d.id = t.debt_id WHERE t.user_id = ? ORDER BY t.tx_date DESC, t.id DESC');
$stmt->execute([$user['id']]);
$initialTransactions = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT id, type, person_name AS personName, description AS `desc`, amount, account_id AS accountId, status, tx_date AS date FROM debts WHERE user_id = ? ORDER BY status ASC, id DESC');
$stmt->execute([$user['id']]);
$initialDebts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mancares | Wealth Dashboard</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 rx=%2220%22 fill=%22%230f172a%22/><text x=%2250%%22 y=%2255%%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22sans-serif%22 font-weight=%22bold%22 font-size=%2250%22 fill=%22%23ffffff%22>M</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .font-mono-custom { font-family: 'Space Grotesk', monospace; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .nav-item { position: relative; transition: all 0.2s ease-in-out; color: #94a3b8; }
        .nav-item:hover { background: rgba(255, 255, 255, 0.05); color: #f1f5f9; }
        .nav-item.active { background: #334155; color: #ffffff; font-weight: 600; }
        .view-section { display: none; }
        .view-section.active { display: block; animation: fadeUp 0.3s ease forwards; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .hover-card { transition: all 0.3s ease; }
        .hover-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08); }
        .bank-card {
            position: relative; overflow: hidden;
            background-image: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, rgba(0,0,0,0.4) 100%);
            background-blend-mode: overlay; aspect-ratio: 1.6 / 1; width: 100%;
            display: flex; flex-direction: column; justify-content: space-between;
        }
        .bank-card::after { content: ''; position: absolute; width: 220px; height: 220px; background: rgba(255,255,255,0.04); border-radius: 50%; top: -80px; right: -50px; pointer-events: none;}
        .bank-card::before { content: ''; position: absolute; width: 140px; height: 140px; border: 2px solid rgba(255,255,255,0.06); border-radius: 50%; bottom: -40px; right: 30px; pointer-events: none;}
        .asset-logo { object-fit: contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3)); }
        .logo-bank { max-height: 28px; max-width: 80px; }
        .logo-network { max-height: 32px; max-width: 50px; }
        .logo-local { max-height: 20px; max-width: 45px; opacity: 0.9; }
        .text-shadow-sm { text-shadow: 0 1px 3px rgba(0,0,0,0.4); }
    </style>
</head>
<body class="flex h-screen text-slate-800 overflow-hidden relative">

    <aside id="sidebar" class="absolute lg:relative inset-y-0 left-0 w-72 bg-slate-900 text-slate-300 flex flex-col flex-shrink-0 border-r border-slate-800 z-40 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
        <div class="p-6 lg:p-8 pb-6 flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-white font-bold text-xl">M</div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-white">MANCARES<span class="text-slate-500">.</span></h1>
                    <p class="text-[10px] font-medium text-slate-400 tracking-widest uppercase">Wealth System</p>
                </div>
            </div>
            <button onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-white p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <nav class="flex-1 py-6 px-4 space-y-1.5 overflow-y-auto">
            <p class="px-4 text-[10px] font-semibold text-slate-500 tracking-wider uppercase mb-3">Menu Utama</p>
            <button onclick="switchView('dashboard'); toggleSidebarOnMobile();" id="nav-dashboard" class="nav-item active w-full flex items-center gap-3.5 px-4 py-3 rounded-xl text-left font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Dashboard
            </button>
            <button onclick="switchView('accounts'); toggleSidebarOnMobile();" id="nav-accounts" class="nav-item w-full flex items-center gap-3.5 px-4 py-3 rounded-xl text-left font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                Rekening & Dompet
            </button>
            <button onclick="switchView('budget'); toggleSidebarOnMobile();" id="nav-budget" class="nav-item w-full flex items-center gap-3.5 px-4 py-3 rounded-xl text-left font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Alokasi Budget
            </button>
            <button onclick="switchView('cashflow'); toggleSidebarOnMobile();" id="nav-cashflow" class="nav-item w-full flex items-center gap-3.5 px-4 py-3 rounded-xl text-left font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                Arus Kas (Cashflow)
            </button>
            <button onclick="switchView('transfer'); toggleSidebarOnMobile();" id="nav-transfer" class="nav-item w-full flex items-center gap-3.5 px-4 py-3 rounded-xl text-left font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 4v12m0 0l4-4m-4 4l-4-4"></path></svg>
                Perpindahan Dana
            </button>
            <button onclick="switchView('debts'); toggleSidebarOnMobile();" id="nav-debts" class="nav-item w-full flex items-center gap-3.5 px-4 py-3 rounded-xl text-left font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m-6 4h6m-6 4h4M5 3h14a1 1 0 011 1v16a1 1 0 01-1 1H5a1 1 0 01-1-1V4a1 1 0 011-1z"></path></svg>
                Hutang & Piutang
            </button>
            <button onclick="switchView('analytics'); toggleSidebarOnMobile();" id="nav-analytics" class="nav-item w-full flex items-center gap-3.5 px-4 py-3 rounded-xl text-left font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                Analisis Grafik
            </button>
            <?php if (is_admin()): ?>
            <a href="admin/index.php" class="nav-item w-full flex items-center gap-3.5 px-4 py-3 rounded-xl text-left font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Kelola User (Admin)
            </a>
            <?php endif; ?>
        </nav>

        <div class="p-4 mx-4 mb-6 rounded-2xl bg-slate-800/80 border border-slate-700/60 space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-slate-700 text-white font-bold flex items-center justify-center text-sm"><?= htmlspecialchars(strtoupper(substr($user['username'], 0, 1))) ?></div>
                <div class="overflow-hidden">
                    <p class="text-xs font-bold text-white truncate"><?= htmlspecialchars($user['username']) ?></p>
                    <p class="text-[10px] text-slate-400 truncate">Sesi Aktif &middot; <?= $user['role'] === 'admin' ? 'Admin' : 'User' ?></p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-700/60">
                <a href="account.php" class="w-full bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-semibold py-2 px-2 rounded-xl transition text-center">
                    Ganti Akun
                </a>
                <button onclick="logoutSession()" class="w-full bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 text-xs font-semibold py-2 px-2 rounded-xl transition text-center">
                    Logout
                </button>
            </div>
        </div>
    </aside>

    <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden"></div>

    <main class="flex-1 h-full overflow-y-auto relative w-full flex flex-col">
        <header class="bg-white/80 backdrop-blur-md px-6 lg:px-10 py-5 border-b border-slate-200/60 sticky top-0 z-20 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="lg:hidden text-slate-700 hover:text-slate-900 focus:outline-none p-1">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <div>
                    <h2 id="page-title" class="text-2xl lg:text-3xl font-extrabold text-slate-800 tracking-tight">Overview</h2>
                    <p class="text-xs lg:text-sm font-medium text-slate-500 mt-0.5" id="current-date"></p>
                </div>
            </div>
        </header>

        <div class="p-6 lg:p-10 max-w-7xl mx-auto w-full flex-1">

            <!-- DASHBOARD -->
            <section id="view-dashboard" class="view-section active space-y-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <div class="bg-white hover-card p-6 lg:p-7 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                        <p class="text-xs lg:text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Net Worth</p>
                        <p id="dash-balance" class="font-mono-custom text-3xl lg:text-4xl font-bold text-slate-800 truncate">Rp 0</p>
                    </div>
                    <div class="bg-white hover-card p-6 lg:p-7 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                        <p class="text-xs lg:text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Total Inflow</p>
                        <p id="dash-in" class="font-mono-custom text-xl lg:text-2xl font-bold text-emerald-600 truncate">Rp 0</p>
                    </div>
                    <div class="bg-white hover-card p-6 lg:p-7 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                        <p class="text-xs lg:text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Total Outflow</p>
                        <p id="dash-out" class="font-mono-custom text-xl lg:text-2xl font-bold text-rose-600 truncate">Rp 0</p>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg lg:text-xl font-extrabold text-slate-800 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        Portfolio Rekening & Dompet
                    </h3>
                    <div id="dash-accounts-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>
                </div>
            </section>

            <!-- REKENING & DOMPET -->
            <section id="view-accounts" class="view-section space-y-8">
                <div class="bg-white p-6 lg:p-8 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                    <h3 class="text-lg lg:text-xl font-bold text-slate-800 mb-2">Desain Rekening Baru (Local Assets)</h3>
                    <p class="text-xs lg:text-sm text-slate-500 mb-6 border-b border-slate-100 pb-4">Pilih logo dari folder <code class="bg-slate-100 px-2 py-1 rounded text-rose-500 font-mono">assets/img/</code></p>
                    <form id="form-account" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                            <div class="md:col-span-5">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Akun</label>
                                <input type="text" id="new-acc-name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-slate-800 font-medium">
                            </div>
                            <div class="md:col-span-5">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Nomor Rekening</label>
                                <input type="text" id="new-acc-number" placeholder="Contoh: 1234 5678 9012" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-slate-800 font-mono-custom">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Warna</label>
                                <input type="color" id="new-acc-color" value="#0f172a" class="w-full h-[52px] rounded-xl cursor-pointer bg-transparent border-0 p-0">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-200">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Network</label>
                                <select id="sel-network" class="w-full bg-white border border-slate-200 rounded-xl p-3.5 font-medium">
                                    <option value="">Tidak ada</option>
                                    <option value="mastercard.svg">Mastercard</option>
                                    <option value="visa.svg">Visa</option>
                                    <option value="jcb.svg">JCB</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Bank</label>
                                <select id="sel-bank" class="w-full bg-white border border-slate-200 rounded-xl p-3.5 font-medium">
                                    <option value="">Tidak ada</option>
                                    <option value="bca.svg">BCA</option>
                                    <option value="bjb.svg">BJB</option>
                                    <option value="bni.svg">BNI</option>
                                    <option value="bri.svg">BRI</option>
                                    <option value="mandiri.svg">Mandiri</option>
                                    <option value="jenius.svg">Jenius</option>
                                    <option value="gopay.svg">GoPay</option>
                                    <option value="shopeepay.svg">ShopeePay</option>
                                    <option value="dana.svg">Dana</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Lokal</label>
                                <select id="sel-local" class="w-full bg-white border border-slate-200 rounded-xl p-3.5 font-medium">
                                    <option value="">Tidak ada</option>
                                    <option value="gpn.svg">GPN</option>
                                    <option value="alto.svg">Alto</option>
                                    <option value="prima.svg">Prima</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="w-full sm:w-auto bg-slate-900 hover:bg-slate-800 text-white font-bold py-3.5 px-8 rounded-xl transition">
                                Buat Kartu
                            </button>
                        </div>
                    </form>
                </div>
                <div>
                    <h3 class="text-lg lg:text-xl font-bold text-slate-800 mb-6">Daftar Rekening & Desain Aktif</h3>
                    <div id="accounts-list-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>
                </div>
            </section>

            <!-- ALOKASI BUDGET -->
            <section id="view-budget" class="view-section space-y-8">
                <div class="bg-white p-6 lg:p-8 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                    <label class="block text-xs lg:text-sm font-bold text-slate-400 uppercase tracking-widest mb-2">Penghasilan Utama (Master Budget)</label>
                    <div class="relative max-w-md">
                        <span class="absolute left-4 top-3.5 text-slate-400 font-bold">Rp</span>
                        <input type="number" id="budget-income" class="w-full pl-12 bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-slate-800 font-mono-custom font-bold text-lg" oninput="updateIncome(this.value)">
                    </div>
                </div>
                <div class="bg-white p-6 lg:p-8 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-slate-100 pb-5 mb-6 gap-4">
                        <h3 class="text-lg lg:text-xl font-bold text-slate-800">Distribusi Pos Alokasi</h3>
                        <button onclick="addAllocation()" class="w-full sm:w-auto bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold py-2.5 px-5 rounded-xl transition text-center">
                            + Tambah Pos
                        </button>
                    </div>
                    <div class="mb-6 space-y-2">
                        <div class="flex justify-between items-center text-xs sm:text-sm font-bold">
                            <span class="text-slate-500">Total Teralokasi</span>
                            <span id="alloc-total-label" class="text-slate-800">0% / 100%</span>
                        </div>
                        <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                            <div id="alloc-total-bar" class="h-full bg-slate-800 transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <p id="alloc-remaining-label" class="text-[11px] sm:text-xs text-slate-400">Sisa slot: 100%</p>
                    </div>
                    <div id="allocations-container" class="space-y-4"></div>
                </div>
            </section>

            <!-- PERPINDAHAN DANA -->
            <section id="view-transfer" class="view-section space-y-8">
                <div class="bg-white p-6 lg:p-8 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                    <h3 class="text-lg lg:text-xl font-bold text-slate-800 mb-2">Pindahkan Dana Antar Rekening</h3>
                    <p class="text-xs lg:text-sm text-slate-500 mb-6 border-b border-slate-100 pb-4">Saldo akan otomatis dipotong dari rekening asal dan ditambahkan ke rekening tujuan.</p>
                    <form id="form-transfer" class="grid grid-cols-1 md:grid-cols-12 gap-5 items-end">
                        <div class="md:col-span-4">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Dari Rekening</label>
                            <select id="transfer-from" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-bold"></select>
                        </div>
                        <div class="md:col-span-1 flex justify-center pb-3.5 text-slate-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Ke Rekening</label>
                            <select id="transfer-to" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-bold"></select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nominal (Rp)</label>
                            <input type="number" id="transfer-amount" required min="1" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-mono-custom font-bold">
                        </div>
                        <div class="md:col-span-12">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Catatan <span class="text-slate-400 font-normal">(opsional)</span></label>
                            <input type="text" id="transfer-desc" placeholder="Contoh: Nabung ke rekening tujuan" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-medium">
                        </div>
                        <div class="md:col-span-12 flex justify-end mt-2">
                            <button type="submit" class="w-full sm:w-auto bg-slate-900 hover:bg-slate-800 text-white font-extrabold py-3.5 px-8 rounded-xl transition">
                                Pindahkan Dana
                            </button>
                        </div>
                    </form>
                </div>
                <div class="bg-white rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
                    <div class="p-6 lg:p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <h3 class="text-lg lg:text-xl font-bold text-slate-800">Riwayat Perpindahan Dana</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[550px]">
                            <thead>
                                <tr class="bg-white text-slate-400 text-xs uppercase tracking-widest">
                                    <th class="p-5 font-bold border-b border-slate-100">Tanggal</th>
                                    <th class="p-5 font-bold border-b border-slate-100">Dari</th>
                                    <th class="p-5 font-bold border-b border-slate-100">Ke</th>
                                    <th class="p-5 font-bold border-b border-slate-100 text-right">Nominal</th>
                                    <th class="p-5 font-bold border-b border-slate-100 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="transfer-table-body" class="text-sm divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- HUTANG & PIUTANG -->
            <section id="view-debts" class="view-section space-y-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="bg-white p-6 lg:p-7 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                        <p class="text-xs lg:text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Piutang Aktif (Dipinjamkan)</p>
                        <p id="debt-total-piutang" class="font-mono-custom text-2xl lg:text-3xl font-bold text-emerald-600 truncate">Rp 0</p>
                    </div>
                    <div class="bg-white p-6 lg:p-7 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                        <p class="text-xs lg:text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Hutang Aktif (Berhutang)</p>
                        <p id="debt-total-hutang" class="font-mono-custom text-2xl lg:text-3xl font-bold text-rose-600 truncate">Rp 0</p>
                    </div>
                </div>

                <div class="bg-white p-6 lg:p-8 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                    <h3 class="text-lg lg:text-xl font-bold text-slate-800 mb-2">Catat Hutang / Piutang Baru</h3>
                    <p class="text-xs lg:text-sm text-slate-500 mb-6 border-b border-slate-100 pb-4">Dana akan otomatis diambil/ditambahkan ke rekening yang dipilih, dan ikut dihitung ke Net Worth.</p>
                    <form id="form-debt" class="grid grid-cols-1 md:grid-cols-12 gap-5 items-end">
                        <div class="md:col-span-3">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Tipe</label>
                            <select id="debt-type" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-bold">
                                <option value="piutang">Piutang (Saya Meminjamkan)</option>
                                <option value="hutang">Hutang (Saya Meminjam)</option>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Orang</label>
                            <input type="text" id="debt-person" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-medium">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nominal (Rp)</label>
                            <input type="number" id="debt-amount" required min="1" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-mono-custom font-bold">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Rekening</label>
                            <select id="debt-account" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-bold"></select>
                        </div>
                        <div class="md:col-span-12">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Catatan <span class="text-slate-400 font-normal">(opsional)</span></label>
                            <input type="text" id="debt-desc" placeholder="Contoh: Pinjaman untuk modal usaha" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-medium">
                        </div>
                        <div class="md:col-span-12 flex justify-end mt-2">
                            <button type="submit" class="w-full sm:w-auto bg-slate-900 hover:bg-slate-800 text-white font-extrabold py-3.5 px-8 rounded-xl transition">
                                Catat & Ambil Dana
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
                    <div class="p-6 lg:p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <h3 class="text-lg lg:text-xl font-bold text-slate-800">Daftar Hutang & Piutang</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[750px]">
                            <thead>
                                <tr class="bg-white text-slate-400 text-xs uppercase tracking-widest">
                                    <th class="p-5 font-bold border-b border-slate-100">Tanggal</th>
                                    <th class="p-5 font-bold border-b border-slate-100">Nama</th>
                                    <th class="p-5 font-bold border-b border-slate-100">Tipe</th>
                                    <th class="p-5 font-bold border-b border-slate-100">Rekening</th>
                                    <th class="p-5 font-bold border-b border-slate-100 text-right">Nominal</th>
                                    <th class="p-5 font-bold border-b border-slate-100">Status</th>
                                    <th class="p-5 font-bold border-b border-slate-100 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="debt-table-body" class="text-sm divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- CASHFLOW -->
            <section id="view-cashflow" class="view-section space-y-8">
                <div class="bg-white p-6 lg:p-8 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                    <h3 class="text-lg lg:text-xl font-bold text-slate-800 mb-6 border-b border-slate-100 pb-4">Pencatatan Transaksi Baru</h3>
                    <form id="form-cashflow" class="grid grid-cols-1 md:grid-cols-12 gap-5 items-end">
                        <div class="md:col-span-4">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Deskripsi</label>
                            <input type="text" id="tx-desc" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-medium">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nominal (Rp)</label>
                            <input type="number" id="tx-amount" required min="1" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-mono-custom font-bold">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Tipe</label>
                            <select id="tx-type" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-bold">
                                <option value="out">Keluar (-)</option>
                                <option value="in">Masuk (+)</option>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Rekening</label>
                            <select id="tx-account" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-bold"></select>
                        </div>
                        <div class="md:col-span-12 flex justify-end mt-2">
                            <button type="submit" class="w-full sm:w-auto bg-slate-900 hover:bg-slate-800 text-white font-extrabold py-3.5 px-8 rounded-xl transition">
                                Catat Transaksi
                            </button>
                        </div>
                    </form>
                </div>
                <div class="bg-white rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
                    <div class="p-6 lg:p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <h3 class="text-lg lg:text-xl font-bold text-slate-800">Buku Besar (Ledger)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[650px]">
                            <thead>
                                <tr class="bg-white text-slate-400 text-xs uppercase tracking-widest">
                                    <th class="p-5 font-bold border-b border-slate-100">Tanggal</th>
                                    <th class="p-5 font-bold border-b border-slate-100">Keterangan</th>
                                    <th class="p-5 font-bold border-b border-slate-100">Rekening</th>
                                    <th class="p-5 font-bold border-b border-slate-100">Tipe</th>
                                    <th class="p-5 font-bold border-b border-slate-100 text-right">Nominal</th>
                                    <th class="p-5 font-bold border-b border-slate-100 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tx-table-body" class="text-sm divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- ANALYTICS -->
            <section id="view-analytics" class="view-section space-y-8">
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg lg:text-xl font-bold text-slate-800">Filter Periode Grafik</h3>
                        <p class="text-xs lg:text-sm text-slate-500">Pilih bulan dan tahun secara terpisah (hingga 2030)</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                        <select id="filter-month" onchange="renderCharts()" class="w-full sm:w-auto bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-800">
                            <option value="all">Semua Bulan</option>
                            <option value="Jan">Januari</option><option value="Feb">Februari</option><option value="Mar">Maret</option>
                            <option value="Apr">April</option><option value="Mei">Mei</option><option value="Jun">Juni</option>
                            <option value="Jul">Juli</option><option value="Agu">Agustus</option><option value="Sep">September</option>
                            <option value="Okt">Oktober</option><option value="Nov">November</option><option value="Des">Desember</option>
                        </select>
                        <select id="filter-year" onchange="renderCharts()" class="w-full sm:w-auto bg-slate-50 border border-slate-200 rounded-xl p-3.5 font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-800">
                            <option value="all">Semua Tahun</option>
                            <option value="2024">2024</option><option value="2025">2025</option><option value="2026">2026</option>
                            <option value="2027">2027</option><option value="2028">2028</option><option value="2029">2029</option><option value="2030">2030</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white p-6 lg:p-8 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                        <h3 class="text-lg lg:text-xl font-bold text-slate-800 mb-6">Perbandingan Pemasukan vs Pengeluaran</h3>
                        <div class="relative w-full h-[280px] sm:h-[320px]"><canvas id="barChart"></canvas></div>
                    </div>
                    <div class="bg-white p-6 lg:p-8 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                        <h3 class="text-lg lg:text-xl font-bold text-slate-800 mb-6">Rasio Keseluruhan (Pie Chart)</h3>
                        <div class="relative w-full h-[280px] sm:h-[320px] flex items-center justify-center"><canvas id="pieChart"></canvas></div>
                    </div>
                </div>
            </section>

            <div class="h-12"></div>
        </div>
    </main>

    <!-- MODAL KONFIRMASI -->
    <div id="modal-confirm" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-sm rounded-3xl p-6 lg:p-8 shadow-2xl space-y-5 text-center">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div>
                <h3 id="confirm-title" class="text-lg font-bold text-slate-800">Konfirmasi Aksi</h3>
                <p id="confirm-message" class="text-xs lg:text-sm text-slate-500 mt-1">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
            </div>
            <div class="grid grid-cols-2 gap-3 pt-2">
                <button onclick="closeConfirmModal()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 rounded-xl transition text-sm">Batal</button>
                <button id="confirm-yes-btn" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-3 rounded-xl transition text-sm shadow-lg shadow-rose-600/20">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;

        document.getElementById('current-date').innerText = new Date().toLocaleDateString('id-ID', {
            timeZone: 'Asia/Jakarta', weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });

        const formatRp = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num || 0);
        const formatRek = (str) => { if(!str) return ''; return str.replace(/\W/gi, '').replace(/(.{4})/g, '$1 ').trim(); };
        const basePath = "assets/img/";
        const MONTHS_ID = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

        function todayJakarta() {
            return new Date().toLocaleDateString('en-CA', { timeZone: 'Asia/Jakarta' }); // YYYY-MM-DD
        }
        function dateParts(iso) {
            const d = new Date(iso + 'T00:00:00');
            return { day: String(d.getDate()).padStart(2,'0'), month: MONTHS_ID[d.getMonth()], year: String(d.getFullYear()) };
        }
        function displayDate(iso) { const p = dateParts(iso); return `${p.day} ${p.month} ${p.year}`; }

        // ==== Data awal dari server (PHP -> MySQL) ====
        let state = {
            income: <?= (float)$user['income'] ?>,
            accounts: <?= json_encode($initialAccounts) ?>,
            allocations: <?= json_encode($initialAllocations) ?>,
            transactions: <?= json_encode($initialTransactions) ?>,
            debts: <?= json_encode($initialDebts) ?>
        };

        async function api(url, method = 'GET', body = null) {
            const opts = { method, headers: { 'Content-Type': 'application/json' } };
            if (method !== 'GET') {
                const payload = body ? { ...body } : {};
                payload.csrf_token = CSRF_TOKEN;
                opts.body = JSON.stringify(payload);
            }
            const res = await fetch(url, opts);
            let json;
            try { json = await res.json(); } catch(e) { json = { success: false, message: 'Respon server tidak valid.' }; }
            if (res.status === 401) { window.location.href = 'login.php?timeout=1'; throw new Error('unauthorized'); }
            if (!json.success) { alert(json.message || 'Terjadi kesalahan.'); throw new Error(json.message || 'error'); }
            return json;
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('sidebar-backdrop').classList.toggle('hidden');
        }
        function toggleSidebarOnMobile() { if (window.innerWidth < 1024) toggleSidebar(); }

        let confirmCallback = null;
        function showConfirmModal(title, message, callback) {
            document.getElementById('confirm-title').innerText = title;
            document.getElementById('confirm-message').innerText = message;
            confirmCallback = callback;
            document.getElementById('modal-confirm').classList.remove('hidden');
        }
        function closeConfirmModal() {
            document.getElementById('modal-confirm').classList.add('hidden');
            confirmCallback = null;
        }
        document.getElementById('confirm-yes-btn').addEventListener('click', function() {
            if (confirmCallback) confirmCallback();
            closeConfirmModal();
        });

        function logoutSession() {
            showConfirmModal("Konfirmasi Logout", "Yakin ingin mengakhiri sesi akun ini?", function() {
                window.location.href = 'logout.php';
            });
        }

        const titles = { 'dashboard': 'Overview', 'accounts': 'Manajemen Rekening', 'budget': 'Alokasi Budget', 'cashflow': 'Sistem Arus Kas', 'transfer': 'Perpindahan Dana', 'debts': 'Hutang & Piutang', 'analytics': 'Analisis Grafik' };
        function switchView(viewId) {
            document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            document.getElementById(`view-${viewId}`).classList.add('active');
            document.getElementById(`nav-${viewId}`).classList.add('active');
            document.getElementById('page-title').innerText = titles[viewId];
            if (viewId === 'analytics') renderCharts();
        }

        // ==== AKUN / REKENING ====
        document.getElementById('form-account').addEventListener('submit', async function(e) {
            e.preventDefault();
            const name = document.getElementById('new-acc-name').value.trim();
            const number = document.getElementById('new-acc-number').value.trim();
            const color = document.getElementById('new-acc-color').value;
            const imgNetwork = document.getElementById('sel-network').value;
            const imgBank = document.getElementById('sel-bank').value;
            const imgLocal = document.getElementById('sel-local').value;
            if (!name) return;

            const res = await api('api/accounts.php', 'POST', { name, number, color, imgNetwork, imgBank, imgLocal });
            state.accounts.push({ id: res.data.id, name, number, color, imgNetwork, imgBank, imgLocal, isDefault: 0 });
            this.reset();
            document.getElementById('new-acc-color').value = "#0f172a";
            updateAllUI();
        });

        function removeAccount(id) {
            if (state.accounts.length === 1) {
                showConfirmModal("Peringatan", "Minimal harus ada 1 rekening aktif di sistem!", () => {});
                return;
            }
            showConfirmModal("Hapus Kartu Rekening", "Apakah Anda yakin ingin menghapus rekening ini beserta riwayat terkait?", async () => {
                await api('api/accounts.php', 'DELETE', { id });
                state.accounts = state.accounts.filter(a => a.id !== id);
                updateAllUI();
            });
        }

        async function updateAccountColor(id, newColor) {
            const acc = state.accounts.find(a => a.id === id);
            if (acc) {
                acc.color = newColor;
                updateAllUI();
                await api('api/accounts.php', 'PUT', { id, color: newColor });
            }
        }

        function buildCustomCard(acc, balance = null) {
            const dispBalance = balance !== null ? formatRp(balance) : 'Preview';
            const renderNetwork = acc.imgNetwork ? `<img src="${basePath}${acc.imgNetwork}" class="asset-logo logo-network" alt="Network" onerror="this.style.display='none'">` : `<span class="text-white/50 text-[10px] font-bold">${acc.number == 'Wallet' ? 'CASH' : 'CARD'}</span>`;
            const renderBank = acc.imgBank ? `<img src="${basePath}${acc.imgBank}" class="asset-logo logo-bank" alt="Bank" onerror="this.style.display='none'">` : `<span class="text-white font-extrabold tracking-wider text-sm shadow-sm">${acc.name.substring(0,10)}</span>`;
            const renderLocal = acc.imgLocal ? `<img src="${basePath}${acc.imgLocal}" class="asset-logo logo-local absolute bottom-4 right-4" alt="Local" onerror="this.style.display='none'">` : '';
            return `
                <div class="bank-card p-5 rounded-3xl shadow-xl hover-card border border-white/10" style="background-color: ${acc.color || '#1e293b'};">
                    ${renderLocal}
                    <div class="flex justify-between items-start z-10 w-full mb-auto">
                        <div class="flex items-start justify-start w-12 h-8 text-white">${renderNetwork}</div>
                        <div class="flex items-start justify-end h-8 text-white">${renderBank}</div>
                    </div>
                    <div class="z-10 mt-auto">
                        <p class="font-mono-custom text-white/80 text-[10px] tracking-[0.2em] mb-1 text-shadow-sm">${acc.number ? formatRek(acc.number) : 'CASH / WALLET'}</p>
                        <p class="text-xl font-mono-custom font-bold text-white text-shadow-sm truncate">${dispBalance}</p>
                    </div>
                </div>
            `;
        }

        function renderAccountsUI() {
            const container = document.getElementById('accounts-list-container');
            const selectDropdown = document.getElementById('tx-account');
            const transferFrom = document.getElementById('transfer-from');
            const transferTo = document.getElementById('transfer-to');
            const debtAccount = document.getElementById('debt-account');
            const prevFrom = transferFrom.value, prevTo = transferTo.value, prevDebtAcc = debtAccount.value;
            container.innerHTML = ''; selectDropdown.innerHTML = ''; transferFrom.innerHTML = ''; transferTo.innerHTML = ''; debtAccount.innerHTML = '';

            const accBalances = {};
            state.accounts.forEach(acc => accBalances[acc.id] = 0);
            state.transactions.forEach(tx => {
                if(tx.type === 'in') { if(accBalances[tx.accountId] !== undefined) accBalances[tx.accountId] += Number(tx.amount); }
                else { if(accBalances[tx.accountId] !== undefined) accBalances[tx.accountId] -= Number(tx.amount); }
            });

            state.accounts.forEach(acc => {
                const label = `${acc.name} ${acc.number !== 'Wallet' && acc.number ? '('+acc.number+')' : ''}`;
                selectDropdown.innerHTML += `<option value="${acc.id}">${label}</option>`;
                transferFrom.innerHTML += `<option value="${acc.id}">${label}</option>`;
                transferTo.innerHTML += `<option value="${acc.id}">${label}</option>`;
                debtAccount.innerHTML += `<option value="${acc.id}">${label}</option>`;
                container.innerHTML += `
                    <div class="relative group">
                        ${buildCustomCard(acc, accBalances[acc.id])}
                        <label class="absolute -top-3 right-12 bg-white text-slate-700 rounded-full p-2 shadow-lg hover:scale-110 transition z-20 border border-slate-100 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 cursor-pointer" title="Ubah Warna Kartu">
                            <input type="color" value="${acc.color || '#0f172a'}" onchange="updateAccountColor(${acc.id}, this.value)" class="absolute opacity-0 w-0 h-0 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 11l9 9m-9-9v4m0-4h4"></path></svg>
                        </label>
                        ${!Number(acc.isDefault) ? `<button onclick="removeAccount(${acc.id})" class="absolute -top-3 -right-3 bg-white text-rose-500 rounded-full p-2 shadow-lg hover:scale-110 transition z-20 border border-slate-100 opacity-100 lg:opacity-0 lg:group-hover:opacity-100" title="Hapus Kartu"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>` : ''}
                    </div>
                `;
            });

            if (prevFrom && [...transferFrom.options].some(o => o.value === prevFrom)) transferFrom.value = prevFrom;
            if (prevTo && [...transferTo.options].some(o => o.value === prevTo)) transferTo.value = prevTo;
            if (prevDebtAcc && [...debtAccount.options].some(o => o.value === prevDebtAcc)) debtAccount.value = prevDebtAcc;
        }


        // ==== BUDGET ====
        let incomeDebounce = null;
        function updateIncome(val) {
            state.income = parseFloat(val) || 0;
            renderBudgetUI();
            clearTimeout(incomeDebounce);
            incomeDebounce = setTimeout(() => api('api/income.php', 'POST', { income: state.income }).catch(()=>{}), 500);
        }

        async function addAllocation() {
            const res = await api('api/allocations.php', 'POST');
            state.allocations.push({ id: res.data.id, name: res.data.name, pct: res.data.pct });
            renderBudgetUI();
        }

        function removeAllocation(id) {
            showConfirmModal("Hapus Pos Alokasi", "Apakah Anda yakin ingin menghapus pos alokasi budget ini?", async () => {
                await api('api/allocations.php', 'DELETE', { id });
                state.allocations = state.allocations.filter(a => a.id !== id);
                renderBudgetUI();
            });
        }

        let allocDebounce = {};
        function allocTotal(excludeId = null) {
            return state.allocations.reduce((sum, a) => sum + (a.id === excludeId ? 0 : Number(a.pct)), 0);
        }
        function updateAllocData(id, field, value) {
            const i = state.allocations.findIndex(a => a.id === id);
            if (i === -1) return;

            if (field === 'pct') {
                let newVal = parseFloat(value);
                if (isNaN(newVal) || newVal < 0) newVal = 0;

                const maxAllowed = Math.max(0, Math.round((100 - allocTotal(id)) * 100) / 100);
                if (newVal > maxAllowed) {
                    newVal = maxAllowed;
                }
                state.allocations[i].pct = newVal;
                renderBudgetUI(); // langsung sinkronkan input & progress bar ke nilai yang di-clamp
            } else {
                state.allocations[i][field] = value;
            }

            clearTimeout(allocDebounce[id + field]);
            allocDebounce[id + field] = setTimeout(() => {
                api('api/allocations.php', 'PUT', { id, field, value: state.allocations[i][field] }).catch(() => {
                    // kalau server tolak (misal race condition), sinkronkan ulang dari data terbaru
                    renderBudgetUI();
                });
            }, 500);
        }

        function renderBudgetUI() {
            document.getElementById('budget-income').value = state.income || '';
            const container = document.getElementById('allocations-container');
            container.innerHTML = '';

            const total = Math.min(100, Math.round(allocTotal() * 100) / 100);
            const remaining = Math.max(0, Math.round((100 - total) * 100) / 100);
            document.getElementById('alloc-total-label').innerText = `${total}% / 100%`;
            document.getElementById('alloc-total-bar').style.width = `${total}%`;
            document.getElementById('alloc-total-bar').className = `h-full transition-all duration-300 ${total >= 100 ? 'bg-emerald-500' : 'bg-slate-800'}`;
            document.getElementById('alloc-remaining-label').innerText = total >= 100 ? 'Alokasi sudah penuh (100%).' : `Sisa slot: ${remaining}%`;

            state.allocations.forEach(alloc => {
                const nom = (state.income * (alloc.pct / 100));
                const maxForThis = Math.max(0, Math.round((100 - allocTotal(alloc.id)) * 100) / 100);
                container.innerHTML += `
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <input type="text" value="${alloc.name}" onchange="updateAllocData(${alloc.id}, 'name', this.value)" class="flex-1 bg-white border border-slate-300 rounded-lg p-2.5 font-bold">
                        <div class="flex items-center justify-between sm:justify-start gap-3">
                            <div class="relative w-28">
                                <input type="number" min="0" max="${maxForThis}" value="${alloc.pct}" onkeyup="updateAllocData(${alloc.id}, 'pct', this.value)" onchange="updateAllocData(${alloc.id}, 'pct', this.value)" class="w-full bg-white border border-slate-300 rounded-lg p-2.5 text-center font-bold">
                                <span class="absolute right-3 top-3 text-slate-400 font-bold text-xs">%</span>
                            </div>
                            <div class="flex-1 sm:w-40 text-right bg-white p-2.5 rounded-lg border border-slate-200"><p class="text-slate-700 font-mono-custom font-bold text-sm truncate">${formatRp(nom)}</p></div>
                            <button onclick="removeAllocation(${alloc.id})" class="text-rose-400 hover:text-rose-600 p-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                        </div>
                    </div>
                `;
            });
        }

        // ==== CASHFLOW ====
        document.getElementById('form-cashflow').addEventListener('submit', async function(e) {
            e.preventDefault();
            const desc = document.getElementById('tx-desc').value;
            const amount = parseFloat(document.getElementById('tx-amount').value);
            const accountId = parseInt(document.getElementById('tx-account').value);
            const type = document.getElementById('tx-type').value;
            if (!(amount > 0 && accountId)) return;

            const date = todayJakarta();
            const res = await api('api/transactions.php', 'POST', { desc, amount, accountId, type, date });
            state.transactions.unshift({ id: res.data.id, date: res.data.date || date, desc, amount, accountId, type, transferId: null, debtId: null, debtType: null });
            this.reset();
            updateAllUI();
        });

        function removeTransaction(id) {
            const tx = state.transactions.find(t => t.id === id);
            const isTransfer = tx && tx.transferId;
            const isDebt = tx && tx.debtId;
            const msg = isTransfer
                ? "Ini bagian dari perpindahan dana. Kedua sisi (asal & tujuan) akan ikut terhapus. Lanjutkan?"
                : isDebt
                ? "Ini bagian dari catatan hutang/piutang. Data hutang/piutang terkait akan ikut terhapus. Lanjutkan?"
                : "Apakah Anda yakin ingin menghapus catatan transaksi ini?";
            showConfirmModal("Hapus Transaksi", msg, async () => {
                const res = await api('api/transactions.php', 'DELETE', { id });
                const deletedIds = (res.data && res.data.deletedIds) || [id];
                state.transactions = state.transactions.filter(tx => !deletedIds.includes(tx.id));
                if (res.data && res.data.deletedDebtId) {
                    state.debts = state.debts.filter(d => d.id !== res.data.deletedDebtId);
                }
                updateAllUI();
            });
        }

        // ==== PERPINDAHAN DANA ====
        document.getElementById('form-transfer').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fromAccountId = parseInt(document.getElementById('transfer-from').value);
            const toAccountId = parseInt(document.getElementById('transfer-to').value);
            const amount = parseFloat(document.getElementById('transfer-amount').value);
            const desc = document.getElementById('transfer-desc').value.trim();
            if (!(amount > 0 && fromAccountId && toAccountId)) return;
            if (fromAccountId === toAccountId) { alert('Rekening asal dan tujuan tidak boleh sama.'); return; }

            const date = todayJakarta();
            const res = await api('api/transfer.php', 'POST', { fromAccountId, toAccountId, amount, desc, date });
            state.transactions.unshift({ id: res.data.in.id, date: res.data.in.date, desc: res.data.in.desc, amount: res.data.in.amount, accountId: res.data.in.accountId, type: 'in', transferId: res.data.transferId, debtId: null, debtType: null });
            state.transactions.unshift({ id: res.data.out.id, date: res.data.out.date, desc: res.data.out.desc, amount: res.data.out.amount, accountId: res.data.out.accountId, type: 'out', transferId: res.data.transferId, debtId: null, debtType: null });
            this.reset();
            updateAllUI();
        });

        function renderTransferUI() {
            const tbody = document.getElementById('transfer-table-body');
            tbody.innerHTML = '';
            const transferOutTx = state.transactions.filter(tx => tx.transferId && tx.type === 'out');
            transferOutTx.forEach(outTx => {
                const inTx = state.transactions.find(tx => tx.transferId === outTx.transferId && tx.type === 'in');
                const fromAcc = state.accounts.find(a => a.id === outTx.accountId) || { name: 'Terhapus' };
                const toAcc = inTx ? (state.accounts.find(a => a.id === inTx.accountId) || { name: 'Terhapus' }) : { name: 'Terhapus' };
                tbody.innerHTML += `
                    <tr class="hover:bg-slate-50 border-b border-slate-50">
                        <td class="p-4 text-slate-500 text-xs">${displayDate(outTx.date)}</td>
                        <td class="p-4 font-bold text-slate-800">${fromAcc.name}</td>
                        <td class="p-4 font-bold text-slate-800">${toAcc.name}</td>
                        <td class="p-4 text-right font-mono-custom font-bold text-slate-800">${formatRp(outTx.amount)}</td>
                        <td class="p-4 text-center">
                            <button onclick="removeTransaction(${outTx.id})" class="text-slate-400 hover:text-rose-500 transition p-1.5 rounded-lg hover:bg-rose-50" title="Hapus Transfer">
                                <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </td>
                    </tr>
                `;
            });
            if (!transferOutTx.length) {
                tbody.innerHTML = `<tr><td colspan="5" class="p-8 text-center text-slate-400">Belum ada perpindahan dana.</td></tr>`;
            }
        }

        // ==== HUTANG & PIUTANG ====
        document.getElementById('form-debt').addEventListener('submit', async function(e) {
            e.preventDefault();
            const type = document.getElementById('debt-type').value;
            const personName = document.getElementById('debt-person').value.trim();
            const amount = parseFloat(document.getElementById('debt-amount').value);
            const accountId = parseInt(document.getElementById('debt-account').value);
            const desc = document.getElementById('debt-desc').value.trim();
            if (!(personName && amount > 0 && accountId)) return;

            const date = todayJakarta();
            const res = await api('api/debts.php', 'POST', { type, personName, amount, accountId, desc, date });
            state.debts.unshift({ id: res.data.id, type, personName, desc, amount, accountId, status: 'belum_lunas', date: res.data.transaction.date });
            state.transactions.unshift({
                id: res.data.transaction.id, date: res.data.transaction.date, desc: res.data.transaction.desc,
                amount: res.data.transaction.amount, accountId: res.data.transaction.accountId, type: res.data.transaction.type,
                transferId: null, debtId: res.data.id, debtType: type
            });
            this.reset();
            updateAllUI();
        });

        async function updateDebtStatus(id, status) {
            const d = state.debts.find(x => x.id === id);
            if (d) d.status = status;
            renderDebtsUI();
            try {
                await api('api/debts.php', 'PUT', { id, status });
            } catch (e) {
                renderDebtsUI();
            }
        }

        function removeDebt(id) {
            showConfirmModal("Hapus Data Hutang/Piutang", "Transaksi dana terkait juga akan ikut terhapus & saldo rekening akan disesuaikan kembali. Lanjutkan?", async () => {
                await api('api/debts.php', 'DELETE', { id });
                state.debts = state.debts.filter(d => d.id !== id);
                state.transactions = state.transactions.filter(tx => tx.debtId !== id);
                updateAllUI();
            });
        }

        function renderDebtsUI() {
            const tbody = document.getElementById('debt-table-body');
            tbody.innerHTML = '';

            let totalPiutang = 0, totalHutang = 0;
            state.debts.forEach(d => {
                if (d.status === 'belum_lunas') {
                    if (d.type === 'piutang') totalPiutang += Number(d.amount);
                    else totalHutang += Number(d.amount);
                }
            });
            document.getElementById('debt-total-piutang').innerText = formatRp(totalPiutang);
            document.getElementById('debt-total-hutang').innerText = formatRp(totalHutang);

            state.debts.forEach(d => {
                const acc = state.accounts.find(a => a.id === d.accountId) || { name: 'Terhapus' };
                const isPiutang = d.type === 'piutang';
                const belumLunasLabel = isPiutang ? 'Dipinjamkan' : 'Berhutang';
                tbody.innerHTML += `
                    <tr class="hover:bg-slate-50 border-b border-slate-50">
                        <td class="p-4 text-slate-500 text-xs">${displayDate(d.date)}</td>
                        <td class="p-4 font-bold text-slate-800">${d.personName}${d.desc ? `<p class="text-[11px] font-normal text-slate-400">${d.desc}</p>` : ''}</td>
                        <td class="p-4"><span class="px-2 py-1 rounded text-xs font-bold ${isPiutang ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'}">${isPiutang ? 'Piutang' : 'Hutang'}</span></td>
                        <td class="p-4 text-slate-600 text-xs">${acc.name}</td>
                        <td class="p-4 text-right font-mono-custom font-bold text-slate-800">${formatRp(d.amount)}</td>
                        <td class="p-4">
                            <select onchange="updateDebtStatus(${d.id}, this.value)" class="text-xs font-bold rounded-lg border p-2 ${d.status === 'lunas' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-amber-50 border-amber-200 text-amber-700'}">
                                <option value="belum_lunas" ${d.status === 'belum_lunas' ? 'selected' : ''}>${belumLunasLabel}</option>
                                <option value="lunas" ${d.status === 'lunas' ? 'selected' : ''}>Lunas</option>
                            </select>
                        </td>
                        <td class="p-4 text-center">
                            <button onclick="removeDebt(${d.id})" class="text-slate-400 hover:text-rose-500 transition p-1.5 rounded-lg hover:bg-rose-50" title="Hapus">
                                <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </td>
                    </tr>
                `;
            });
            if (!state.debts.length) {
                tbody.innerHTML = `<tr><td colspan="7" class="p-8 text-center text-slate-400">Belum ada data hutang/piutang.</td></tr>`;
            }
        }

        let barChartInstance = null;
        let pieChartInstance = null;

        function renderCharts() {
            const selectedMonth = document.getElementById('filter-month').value;
            const selectedYear = document.getElementById('filter-year').value;
            let filteredTx = state.transactions;

            if (selectedMonth !== 'all') {
                filteredTx = filteredTx.filter(tx => dateParts(tx.date).month.toLowerCase() === selectedMonth.toLowerCase());
            }
            if (selectedYear !== 'all') {
                filteredTx = filteredTx.filter(tx => dateParts(tx.date).year === selectedYear);
            }

            let chartLabel;
            if (selectedMonth !== 'all' && selectedYear !== 'all') chartLabel = `${selectedMonth} ${selectedYear}`;
            else if (selectedMonth !== 'all') chartLabel = `Bulan ${selectedMonth}`;
            else if (selectedYear !== 'all') chartLabel = `Tahun ${selectedYear}`;
            else chartLabel = "Keseluruhan";

            let totalInflow = 0, totalOutflow = 0;
            const monthlyMap = { [chartLabel]: { income: 0, expense: 0 } };
            filteredTx.forEach(tx => {
                if (tx.transferId) return; // perpindahan dana bukan pemasukan/pengeluaran
                const amt = Number(tx.amount);
                if (tx.type === 'in') { monthlyMap[chartLabel].income += amt; totalInflow += amt; }
                else { monthlyMap[chartLabel].expense += amt; totalOutflow += amt; }
            });

            const labels = Object.keys(monthlyMap);
            const incomeData = labels.map(l => monthlyMap[l].income);
            const expenseData = labels.map(l => monthlyMap[l].expense);

            const ctxBar = document.getElementById('barChart').getContext('2d');
            if (barChartInstance) barChartInstance.destroy();
            barChartInstance = new Chart(ctxBar, {
                type: 'bar',
                data: { labels: labels, datasets: [
                    { label: 'Pemasukan', data: incomeData, backgroundColor: '#10b981', borderRadius: 8 },
                    { label: 'Pengeluaran', data: expenseData, backgroundColor: '#f43f5e', borderRadius: 8 }
                ]},
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
            });

            const ctxPie = document.getElementById('pieChart').getContext('2d');
            if (pieChartInstance) pieChartInstance.destroy();
            pieChartInstance = new Chart(ctxPie, {
                type: 'pie',
                data: { labels: ['Total Pemasukan', 'Total Pengeluaran'], datasets: [{ data: [totalInflow, totalOutflow], backgroundColor: ['#10b981', '#f43f5e'], borderWidth: 0 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });
        }

        function renderDashboardUI() {
            let totalIn = 0, totalOut = 0;
            const accBalances = {};
            state.accounts.forEach(acc => accBalances[acc.id] = 0);

            state.transactions.forEach(tx => {
                const amt = Number(tx.amount);
                // Saldo per rekening tetap dihitung (perpindahan dana tetap mutasi saldo)
                if (tx.type === 'in') { if(accBalances[tx.accountId] !== undefined) accBalances[tx.accountId] += amt; }
                else { if(accBalances[tx.accountId] !== undefined) accBalances[tx.accountId] -= amt; }

                // Tapi perpindahan dana BUKAN pemasukan/pengeluaran, jadi tidak dihitung di total In/Out
                if (tx.transferId) return;
                if (tx.type === 'in') totalIn += amt;
                else totalOut += amt;
            });

            document.getElementById('dash-balance').innerText = formatRp(totalIn - totalOut);
            document.getElementById('dash-in').innerText = formatRp(totalIn);
            document.getElementById('dash-out').innerText = formatRp(totalOut);

            const dashAccounts = document.getElementById('dash-accounts-grid');
            dashAccounts.innerHTML = '';
            state.accounts.forEach(acc => dashAccounts.innerHTML += buildCustomCard(acc, accBalances[acc.id]));

            const tbody = document.getElementById('tx-table-body');
            tbody.innerHTML = '';
            state.transactions.forEach(tx => {
                const isMasuk = tx.type === 'in';
                const acc = state.accounts.find(a => a.id === tx.accountId) || {name: 'Terhapus', color: '#ccc'};
                tbody.innerHTML += `
                    <tr class="hover:bg-slate-50 border-b border-slate-50">
                        <td class="p-4 text-slate-500 text-xs">${displayDate(tx.date)}</td>
                        <td class="p-4 text-slate-800 font-bold">${tx.desc} ${tx.transferId ? '<span class="ml-1 px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 text-[10px] font-bold align-middle">TRANSFER</span>' : ''} ${tx.debtId ? `<span class="ml-1 px-1.5 py-0.5 rounded ${tx.debtType === 'piutang' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'} text-[10px] font-bold align-middle">${tx.debtType === 'piutang' ? 'PIUTANG' : 'HUTANG'}</span>` : ''}</td>
                        <td class="p-4"><span class="px-2 py-1 rounded text-xs font-bold text-white shadow-sm" style="background-color: ${acc.color}">${acc.name}</span></td>
                        <td class="p-4"><span class="${isMasuk ? 'text-emerald-500' : 'text-rose-500'} font-bold text-xs">${isMasuk ? 'IN' : 'OUT'}</span></td>
                        <td class="p-4 text-right font-mono-custom font-bold ${isMasuk ? 'text-emerald-600' : 'text-slate-800'}">${isMasuk ? '+' : '-'}${formatRp(tx.amount)}</td>
                        <td class="p-4 text-center">
                            <button onclick="removeTransaction(${tx.id})" class="text-slate-400 hover:text-rose-500 transition p-1.5 rounded-lg hover:bg-rose-50" title="Hapus Transaksi">
                                <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }

        function updateAllUI() {
            renderAccountsUI();
            renderBudgetUI();
            renderDashboardUI();
            renderTransferUI();
            renderDebtsUI();
            if (document.getElementById('view-analytics').classList.contains('active')) renderCharts();
        }

        updateAllUI();
    </script>
</body>
</html>
