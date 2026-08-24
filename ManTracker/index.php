<?php
require 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ==========================================
// API HANDLER (Menyimpan & Memuat data dari DB via JS Fetch)
// ==========================================
if (isset($_GET['api'])) {
    header('Content-Type: application/json');
    $user_id = $_SESSION['user_id'];
    
    // LOAD DATA
    if ($_GET['api'] == 'load') {
        $month = (int)$_GET['month'];
        $year = (int)$_GET['year'];
        
        $stmt = $pdo->prepare("SELECT habits_json FROM monthly_habits WHERE user_id = ? AND month = ? AND year = ?");
        $stmt->execute([$user_id, $month, $year]);
        $result = $stmt->fetch();
        
        echo $result ? $result['habits_json'] : '[]';
        exit;
    }
    
    // SAVE DATA
    if ($_GET['api'] == 'save' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $month = (int)$data['month'];
        $year = (int)$data['year'];
        $habits_json = json_encode($data['habits']);
        
        $stmt = $pdo->prepare("SELECT id FROM monthly_habits WHERE user_id = ? AND month = ? AND year = ?");
        $stmt->execute([$user_id, $month, $year]);
        
        if ($stmt->fetch()) {
            // Update
            $stmt = $pdo->prepare("UPDATE monthly_habits SET habits_json = ? WHERE user_id = ? AND month = ? AND year = ?");
            $stmt->execute([$habits_json, $user_id, $month, $year]);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO monthly_habits (user_id, month, year, habits_json) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $month, $year, $habits_json]);
        }
        echo json_encode(['status' => 'success']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Man-Tracker Apps | Monthly Dashboard</title>
    
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='20' fill='%230f172a'/%3E%3Ctext x='50' y='65' font-family='Arial, sans-serif' font-weight='900' font-size='50' fill='%23f59e0b' text-anchor='middle' font-style='italic'%3EMT%3C/text%3E%3C/svg%3E">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        ::-webkit-scrollbar { height: 8px; width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .sidebar-transition { transition: transform 0.3s ease-in-out; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen overflow-x-hidden">

    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/60 z-30 hidden lg:hidden backdrop-blur-sm transition-opacity opacity-0" onclick="toggleSidebar()"></div>

    <div class="flex min-h-screen">
        
        <!-- SIDEBAR -->
        <aside id="sidebar" class="fixed lg:sticky top-0 left-0 h-screen w-72 bg-slate-900 text-slate-300 p-6 flex flex-col justify-between shrink-0 shadow-2xl lg:shadow-xl z-40 sidebar-transition -translate-x-full lg:translate-x-0">
            <div class="space-y-8">
                
                <div class="flex items-center justify-between lg:justify-start gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-slate-800 to-slate-950 border border-slate-700/50 flex items-center justify-center shadow-lg relative overflow-hidden group cursor-default">
                            <div class="absolute inset-0 bg-amber-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <span class="font-extrabold text-xl tracking-tighter text-amber-500 italic relative z-10" style="text-shadow: 0 2px 10px rgba(245,158,11,0.3);">MT</span>
                        </div>
                        <div>
                            <h1 class="font-extrabold text-white tracking-wide text-lg leading-tight">Man-Tracker<br><span class="text-amber-500 text-sm">.apps</span></h1>
                        </div>
                    </div>
                    <button onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-white p-2 text-xl"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <nav class="space-y-2 pt-2">
                    <div class="text-xs uppercase tracking-wider text-slate-500 font-bold px-3 py-1">Main Menu</div>
                    <a href="#" class="flex items-center gap-3 px-3 py-3 rounded-xl bg-white/10 text-white font-medium text-sm transition shadow-sm border border-white/5">
                        <i class="fa-solid fa-chart-column text-amber-500 w-5 text-center"></i>
                        <span>Monthly Tracker</span>
                    </a>
                </nav>

                <div class="bg-slate-800/50 border border-slate-700 p-4 rounded-xl space-y-2">
                    <div class="flex items-center gap-2 text-amber-500 text-xs font-bold uppercase tracking-wide">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>User Active</span>
                    </div>
                    <p class="text-sm text-slate-300 font-bold">
                        <?= htmlspecialchars($_SESSION['username']) ?>
                    </p>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-800 text-xs text-slate-500 flex flex-col gap-3">
                <a href="logout.php" class="w-full py-2.5 bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-500 hover:text-white rounded-xl transition cursor-pointer font-bold flex items-center justify-center gap-2">
                    <i class="fa-solid fa-power-off"></i> LOGOUT
                </a>
            </div>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="flex-1 p-4 sm:p-6 lg:p-10 space-y-6 overflow-x-hidden min-w-0 w-full">
            
            <div class="lg:hidden flex items-center justify-between bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="text-slate-700 hover:text-amber-500 text-xl p-1 transition cursor-pointer"><i class="fa-solid fa-bars"></i></button>
                    <span class="font-extrabold text-slate-900 tracking-tight">Man-Tracker<span class="text-amber-500">.apps</span></span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-900 text-amber-500 flex items-center justify-center text-sm font-bold shadow-inner uppercase">
                    <?= substr($_SESSION['username'], 0, 1) ?>
                </div>
            </div>

            <header class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative z-10">
                <div>
                    <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                        Performance <span class="text-amber-500">Board</span>
                    </h2>
                    <p class="text-slate-500 text-sm mt-1 font-medium">Data Anda kini tersinkronisasi di Server Aman.</p>
                </div>
                
                <div class="flex gap-2 bg-slate-50 p-1.5 rounded-xl border border-slate-200 w-full xl:w-auto shadow-inner">
                    <select id="monthSelect" class="px-3 md:px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 cursor-pointer flex-1">
                        <option value="0">Januari</option>
                        <option value="1">Februari</option>
                        <option value="2">Maret</option>
                        <option value="3">April</option>
                        <option value="4">Mei</option>
                        <option value="5">Juni</option>
                        <option value="6">Juli</option>
                        <option value="7">Agustus</option>
                        <option value="8">September</option>
                        <option value="9">Oktober</option>
                        <option value="10">November</option>
                        <option value="11">Desember</option>
                    </select>
                    <select id="yearSelect" class="px-3 md:px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-bold text-slate-700 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 cursor-pointer w-24 md:w-28">
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                        <option value="2027">2027</option>
                    </select>
                </div>
            </header>

            <!-- STATISTIK, CHARTS, & FORM (Persis sama UI-nya) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col justify-center relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 text-slate-50 text-7xl opacity-50 transition-transform group-hover:scale-110"><i class="fa-solid fa-percent"></i></div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 relative z-10">Total Progres</p>
                    <h3 id="monthlyPercentage" class="text-3xl md:text-4xl font-black text-slate-900 mt-2 relative z-10">0<span class="text-xl md:text-2xl text-amber-500">%</span></h3>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col justify-center relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 text-slate-50 text-7xl opacity-50 transition-transform group-hover:scale-110"><i class="fa-solid fa-list-check"></i></div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 relative z-10">Total Habit</p>
                    <h3 id="totalHabits" class="text-3xl md:text-4xl font-black text-slate-900 mt-2 relative z-10">0</h3>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col justify-center relative overflow-hidden group">
                    <div class="absolute -right-4 -top-4 text-slate-50 text-7xl opacity-50 transition-transform group-hover:scale-110"><i class="fa-solid fa-bolt"></i></div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 relative z-10">Hari Selesai</p>
                    <h3 id="completedCount" class="text-3xl md:text-4xl font-black text-slate-900 mt-2 relative z-10">0</h3>
                </div>
                <div class="bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-800 flex flex-col justify-center relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/10 blur-3xl rounded-full"></div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 relative z-10">Status Konsistensi</p>
                    <h3 id="streakScore" class="text-lg md:text-xl font-bold text-amber-500 mt-2 relative z-10 uppercase tracking-wide">Load...</h3>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 relative z-0">
                <div class="bg-white p-4 md:p-6 rounded-2xl shadow-sm border border-slate-200 lg:col-span-2">
                    <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2 text-sm md:text-base"><i class="fa-solid fa-chart-simple text-amber-500"></i> Daily Completion</h3>
                    <div class="h-48 md:h-64 w-full relative"><canvas id="barChart"></canvas></div>
                </div>
                <div class="bg-white p-4 md:p-6 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2 text-sm md:text-base"><i class="fa-solid fa-chart-pie text-amber-500"></i> Category Focus</h3>
                    <div class="h-48 md:h-64 w-full relative flex justify-center"><canvas id="pieChart"></canvas></div>
                </div>
            </div>

            <form id="habitForm" class="flex flex-col sm:flex-row gap-3 bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-slate-200 w-full relative z-0">
                <input type="text" id="habitInput" placeholder="Tulis target habit baru..." required class="px-4 py-3 md:py-3.5 text-sm font-medium bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-slate-800 flex-1">
                <select id="habitCategory" class="px-4 py-3 md:py-3.5 text-sm font-bold bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-slate-700 cursor-pointer w-full sm:w-48">
                    <option value="Fisik">💪 Fisik</option>
                    <option value="Mental">🧠 Mental</option>
                    <option value="Belajar">📚 Belajar</option>
                    <option value="Kerja">💼 Kerja / Karier</option>
                    <option value="Keuangan">💰 Keuangan</option>
                    <option value="Spiritual">🧘 Spiritual</option>
                    <option value="Lainnya">📌 Lainnya</option>
                </select>
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-sm px-6 md:px-8 py-3 md:py-3.5 rounded-xl shadow-md transition-all flex justify-center gap-2">
                    <i class="fa-solid fa-plus text-lg"></i> ADD
                </button>
            </form>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden relative z-0">
                <div class="p-4 md:p-6 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                    <div>
                        <h3 class="font-bold text-base md:text-lg text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-calendar-days text-slate-400"></i> Action Calendar
                        </h3>
                        <p class="text-[10px] md:text-xs text-slate-500 mt-1 lg:hidden">Geser tabel ke kanan 👉</p>
                    </div>
                </div>
                <div class="overflow-x-auto pb-4">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead><tr id="trafficHeader" class="bg-slate-100 text-slate-500 uppercase tracking-wider border-b border-slate-200 text-xs"></tr></thead>
                        <tbody id="trafficTableBody" class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- Script AJAX untuk sinkronisasi Database -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        let isSidebarOpen = false;

        function toggleSidebar() {
            isSidebarOpen = !isSidebarOpen;
            if (isSidebarOpen) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }

        const monthSelect = document.getElementById('monthSelect');
        const yearSelect = document.getElementById('yearSelect');
        const habitForm = document.getElementById('habitForm');
        const habitInput = document.getElementById('habitInput');
        const habitCategory = document.getElementById('habitCategory');
        const trafficHeader = document.getElementById('trafficHeader');
        const trafficTableBody = document.getElementById('trafficTableBody');
        const monthlyPercentageEl = document.getElementById('monthlyPercentage');
        const totalHabitsEl = document.getElementById('totalHabits');
        const completedCountEl = document.getElementById('completedCount');
        const streakScoreEl = document.getElementById('streakScore');

        let barChart = null;
        let pieChart = null;
        let habits = [];

        const categoryIcons = {
            'Fisik': '<i class="fa-solid fa-dumbbell text-slate-700"></i>',
            'Mental': '<i class="fa-solid fa-brain text-slate-700"></i>',
            'Belajar': '<i class="fa-solid fa-book-open text-slate-700"></i>',
            'Kerja': '<i class="fa-solid fa-briefcase text-slate-700"></i>',
            'Keuangan': '<i class="fa-solid fa-wallet text-slate-700"></i>',
            'Spiritual': '<i class="fa-solid fa-om text-slate-700"></i>',
            'Lainnya': '<i class="fa-solid fa-hashtag text-slate-700"></i>'
        };

        const currentDate = new Date();
        monthSelect.value = currentDate.getMonth(); 
        yearSelect.value = currentDate.getFullYear();

        function getDaysInMonth(month, year) { return new Date(year, parseInt(month) + 1, 0).getDate(); }
        function createDefaultDates(days) { let obj = {}; for (let i = 1; i <= days; i++) obj[i] = false; return obj; }

        // ===============================================
        // FUNGSI AJAX: MENGAMBIL DATA DARI PHP/DATABASE
        // ===============================================
        async function loadData() {
            const m = monthSelect.value;
            const y = yearSelect.value;
            const daysInCurrentMonth = getDaysInMonth(m, y);
            
            try {
                const response = await fetch(`index.php?api=load&month=${m}&year=${y}`);
                const data = await response.json();
                habits = data;
                
                // Pastikan struktur dates selalu benar
                habits.forEach(habit => {
                    if (!habit.dates) habit.dates = createDefaultDates(daysInCurrentMonth);
                });
            } catch (error) {
                console.error("Gagal memuat data dari database", error);
                habits = [];
            }
            renderDashboard();
        }

        // ===============================================
        // FUNGSI AJAX: MENYIMPAN DATA KE PHP/DATABASE
        // ===============================================
        async function saveDataToDatabase() {
            const m = monthSelect.value;
            const y = yearSelect.value;
            
            try {
                await fetch('index.php?api=save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ month: m, year: y, habits: habits })
                });
            } catch (error) {
                console.error("Gagal menyimpan ke database", error);
                alert('Gagal menyinkronkan data dengan server.');
            }
        }

        function updateCharts(daysInMonth) {
            const labelsBar = [];
            const dataBar = [];
            for (let i = 1; i <= daysInMonth; i++) {
                labelsBar.push(`${i}`);
                let dayCompletedCount = 0;
                habits.forEach(h => { if(h.dates[i]) dayCompletedCount++; });
                dataBar.push(dayCompletedCount);
            }

            if (barChart) barChart.destroy();
            const ctxBar = document.getElementById('barChart').getContext('2d');
            barChart = new Chart(ctxBar, {
                type: 'bar',
                data: { labels: labelsBar, datasets: [{ label: 'Habit Selesai', data: dataBar, backgroundColor: '#1e293b', hoverBackgroundColor: '#f59e0b', borderRadius: 4 }] },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } }, x: { grid: { display: false } } }, plugins: { legend: { display: false } } }
            });

            const categoryCounts = {};
            habits.forEach(h => { categoryCounts[h.category] = (categoryCounts[h.category] || 0) + 1; });
            const pieLabels = Object.keys(categoryCounts);
            const pieData = Object.values(categoryCounts);
            const bgColors = ['#f59e0b', '#334155', '#10b981', '#ef4444', '#0ea5e9', '#8b5cf6', '#64748b'];

            if (pieChart) pieChart.destroy();
            const ctxPie = document.getElementById('pieChart').getContext('2d');
            pieChart = new Chart(ctxPie, {
                type: 'doughnut',
                data: { labels: pieLabels.length > 0 ? pieLabels : ['Belum Ada'], datasets: [{ data: pieData.length > 0 ? pieData : [1], backgroundColor: pieData.length > 0 ? bgColors : ['#e2e8f0'], borderWidth: 2, borderColor: '#ffffff' }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11, family: 'Plus Jakarta Sans' } } } } }
            });
        }

        function renderDashboard() {
            const daysInCurrentMonth = getDaysInMonth(monthSelect.value, yearSelect.value);
            
            let headerHTML = `<th class="p-3 md:p-4 font-bold min-w-[200px] md:min-w-[280px] max-w-[300px] sticky left-0 bg-slate-100 z-20 border-r border-slate-200 text-[10px] md:text-xs shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">TARGET & KATEGORI</th>`;
            for (let i = 1; i <= daysInCurrentMonth; i++) {
                headerHTML += `<th class="p-2 md:p-3 text-center font-bold text-slate-700 min-w-[40px] md:min-w-[45px] border-r border-slate-200/50">${i}</th>`;
            }
            trafficHeader.innerHTML = headerHTML;

            trafficTableBody.innerHTML = '';
            let totalCheckboxes = habits.length * daysInCurrentMonth;
            let checkedCount = 0;

            if (habits.length === 0) {
                trafficTableBody.innerHTML = `
                    <tr><td colspan="${daysInCurrentMonth + 1}" class="p-10 md:p-16 text-center bg-white sticky left-0 w-full">
                        <i class="fa-solid fa-crosshairs text-4xl md:text-5xl text-slate-200 mb-3 md:mb-4 block"></i>
                        <h4 class="text-slate-800 font-bold text-base md:text-lg">Area Operasi Kosong</h4>
                        <p class="text-slate-500 text-xs md:text-sm mt-1">Tetapkan target pertama Anda untuk bulan ini.</p>
                    </td></tr>`;
            }

            habits.forEach(habit => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-slate-50/80 transition group';
                const iconHTML = categoryIcons[habit.category] || '<i class="fa-solid fa-tag text-slate-700"></i>';

                let rowHTML = `
                    <td class="p-2 md:p-3 sticky left-0 bg-white group-hover:bg-slate-50 z-10 border-r border-slate-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] transition-colors">
                        <div class="flex justify-between items-center gap-1 md:gap-2">
                            <div class="overflow-hidden">
                                <div class="font-bold text-slate-900 truncate text-xs md:text-sm" title="${habit.name}">${habit.name}</div>
                                <span class="text-[9px] md:text-[10px] uppercase tracking-wider px-1.5 md:px-2 py-0.5 bg-slate-100 text-slate-600 rounded font-bold inline-flex items-center gap-1 mt-1 border border-slate-200">
                                    ${iconHTML} <span class="hidden sm:inline">${habit.category}</span>
                                </span>
                            </div>
                            <button onclick="deleteHabit(${habit.id})" class="text-slate-300 hover:text-rose-600 hover:bg-rose-50 p-1.5 md:p-2.5 rounded-lg transition cursor-pointer shrink-0"><i class="fa-solid fa-xmark text-sm md:text-lg"></i></button>
                        </div>
                    </td>
                `;
                
                for (let i = 1; i <= daysInCurrentMonth; i++) {
                    const isChecked = habit.dates[i];
                    if (isChecked) checkedCount++;
                    rowHTML += `
                        <td class="p-0.5 md:p-1 text-center border-r border-slate-100">
                            <label class="cursor-pointer flex justify-center items-center w-full h-full py-2 hover:bg-slate-100 rounded transition">
                                <input type="checkbox" ${isChecked ? 'checked' : ''} onchange="toggleDateProgress(${habit.id}, ${i})" class="w-5 h-5 md:w-6 md:h-6 text-slate-900 bg-slate-50 border-2 border-slate-300 rounded focus:ring-slate-900 focus:ring-offset-0 transition-all cursor-pointer">
                            </label>
                        </td>
                    `;
                }
                
                row.innerHTML = rowHTML;
                trafficTableBody.appendChild(row);
            });

            totalHabitsEl.textContent = habits.length;
            completedCountEl.textContent = checkedCount;

            const percentage = totalCheckboxes > 0 ? Math.round((checkedCount / totalCheckboxes) * 100) : 0;
            monthlyPercentageEl.innerHTML = `${percentage}<span class="text-xl md:text-2xl text-amber-500">%</span>`;

            if (percentage >= 90) { streakScoreEl.innerHTML = `DOMINASI <i class="fa-solid fa-crown text-amber-400 ml-1"></i>`; streakScoreEl.className = "text-sm md:text-xl font-black text-amber-500 mt-1 md:mt-2 relative z-10 uppercase tracking-widest"; } 
            else if (percentage >= 60) { streakScoreEl.innerHTML = `SOLID <i class="fa-solid fa-shield text-slate-300 ml-1"></i>`; streakScoreEl.className = "text-sm md:text-xl font-black text-white mt-1 md:mt-2 relative z-10 uppercase tracking-widest"; } 
            else if (percentage >= 30) { streakScoreEl.innerHTML = `RUTINITAS AKTIF <i class="fa-solid fa-person-running text-slate-400 ml-1"></i>`; streakScoreEl.className = "text-sm md:text-xl font-bold text-slate-300 mt-1 md:mt-2 relative z-10 uppercase tracking-widest"; } 
            else if (percentage > 0) { streakScoreEl.innerHTML = `MEMULAI <i class="fa-solid fa-power-off text-emerald-500 ml-1"></i>`; streakScoreEl.className = "text-sm md:text-xl font-bold text-slate-400 mt-1 md:mt-2 relative z-10 uppercase tracking-widest"; } 
            else { streakScoreEl.innerHTML = `STANDBY <i class="fa-solid fa-circle-notch text-slate-600 ml-1"></i>`; streakScoreEl.className = "text-sm md:text-xl font-bold text-slate-600 mt-1 md:mt-2 relative z-10 uppercase tracking-widest"; }

            updateCharts(daysInCurrentMonth);
            
            // Simpan ke DB setiap kali render dipanggil akibat interaksi user
            saveDataToDatabase();
        }

        habitForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const habitName = habitInput.value.trim();
            if (!habitName) return;
            const daysInCurrentMonth = getDaysInMonth(monthSelect.value, yearSelect.value);
            habits.push({ id: Date.now(), name: habitName, category: habitCategory.value, dates: createDefaultDates(daysInCurrentMonth) });
            habitInput.value = '';
            renderDashboard();
        });

        function toggleDateProgress(id, dateNum) {
            const habit = habits.find(h => h.id === id);
            if (habit) { habit.dates[dateNum] = !habit.dates[dateNum]; renderDashboard(); }
        }

        function deleteHabit(id) {
            if (confirm('Hapus target ini dari daftar?')) { habits = habits.filter(h => h.id !== id); renderDashboard(); }
        }

        monthSelect.addEventListener('change', loadData);
        yearSelect.addEventListener('change', loadData);

        // Load awal data dari server
        loadData();
    </script>
</body>
</html>