<?php
require 'includes/auth.php';
requireLogin();

$pageTitle  = "Arus Kas - Mansekai";
$activePage = "aruskas.php";

include 'includes/header.php';
?>
<style>
/* Style tambahan khusus halaman ini (di luar assets/style.css) */

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        :root { --bg-sidebar: #0f111a; --bg-main: #f4ecf0; --accent-green: #00f2c3; --accent-green-dark: #00c49f; --text-dark: #2d3142; --text-muted: #7d8597; --card-bg: #ffffff; --sidebar-hover: #1c2130; }
        body { display: flex; background-color: var(--bg-main); color: var(--text-dark); min-height: 100vh; overflow-x: hidden; }
        aside { width: 260px; background-color: var(--bg-sidebar); color: #fff; display: flex; flex-direction: column; justify-content: space-between; position: fixed; height: 100vh; left: 0; top: 0; padding: 20px 0; z-index: 100; transition: all 0.3s ease; }
        .sidebar-brand { padding: 0 24px 20px 24px; font-size: 1.1rem; font-weight: 600; color: var(--accent-green); border-bottom: 1px solid rgba(255,255,255,0.08); }
        .sidebar-menu { list-style: none; padding: 20px 12px; flex-grow: 1; overflow-y: auto; }
        .sidebar-menu li { margin-bottom: 6px; }
        .sidebar-menu a { display: flex; align-items: center; gap: 14px; padding: 12px 16px; color: var(--text-muted); text-decoration: none; font-size: 0.95rem; border-radius: 8px; transition: 0.3s; }
        .sidebar-menu a:hover, .sidebar-menu li.active a { background-color: var(--sidebar-hover); color: #fff; }
        .sidebar-menu li.active a { border-left: 4px solid var(--accent-green); }
        .sidebar-footer { padding: 16px 24px; border-top: 1px solid rgba(255,255,255,0.08); font-size: 0.85rem; color: var(--text-muted); }
        main { margin-left: 260px; flex-grow: 1; padding: 30px; background: linear-gradient(135deg, #f9f1f5 0%, #e8e2eb 100%); min-height: 100vh; width: calc(100% - 260px); transition: all 0.3s ease; }
        .header-title h1 { font-size: 1.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; }
        .card { background-color: var(--card-bg); border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-top: 20px; width: 100%; max-width: 600px; }
        .cashflow-summary { font-size: 1rem; margin-bottom: 15px; font-weight: 600; }
        .input-group { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
        .input-group input, .input-group select { flex-grow: 1; padding: 8px 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; font-size: 0.85rem; min-width: 110px; }
        .input-group button { background-color: var(--accent-green-dark); color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .cashflow-list { list-style: none; max-height: 250px; overflow-y: auto; }
        .cashflow-item { display: flex; justify-content: space-between; font-size: 0.9rem; padding: 8px 0; border-bottom: 1px solid #f0f0f0; gap: 10px; }
        .cashflow-item.masuk { color: #00876b; }
        .cashflow-item.keluar { color: #ff4d4d; }
        @media (max-width: 992px) {
            aside { width: 75px; padding: 10px 0; }
            aside .sidebar-brand, aside .sidebar-footer, aside .sidebar-menu .menu-label { display: none; }
            aside .sidebar-menu a { justify-content: center; padding: 14px; font-size: 1.1rem; }
            aside .sidebar-menu li.active a { border-left: none; border-bottom: 3px solid var(--accent-green); }
            main { margin-left: 75px; width: calc(100% - 75px); padding: 20px 15px; }
        }
        @media (max-width: 600px) {
            body { flex-direction: column; }
            aside { width: 100%; height: 60px; position: fixed; bottom: 0; top: auto; flex-direction: row; padding: 0; border-top: 1px solid rgba(255,255,255,0.1); z-index: 1000; }
            .sidebar-menu { display: flex; flex-direction: row; justify-content: space-around; padding: 0; align-items: center; width: 100%; }
            .sidebar-menu li { margin-bottom: 0; flex-grow: 1; text-align: center; }
            .sidebar-menu a { padding: 15px 0; border-radius: 0; justify-content: center; }
            .sidebar-menu li.active a { border-bottom: 3px solid var(--accent-green); background-color: var(--sidebar-hover); }
            main { margin-left: 0; width: 100%; padding: 15px; margin-bottom: 70px; }
            .header-title h1 { font-size: 1.3rem; }
        }
    
</style>

        <div class="header-title">
            <h1>Arus Kas Keluar Masuk</h1>
            <p style="color: var(--text-muted);">Catat pemasukan dan pengeluaran finansial harian Anda.</p>
        </div>
        <div class="card">
            <div class="cashflow-summary">
                <span>Total Saldo: <b id="cashBalance">Rp 0</b></span>
            </div>
            <div class="input-group">
                <input type="text" id="cashDesc" placeholder="Keterangan transaksi...">
                <input type="number" id="cashAmount" placeholder="Nominal">
                <select id="cashType">
                    <option value="masuk">Masuk</option>
                    <option value="keluar">Keluar</option>
                </select>
                <button onclick="addCashflow()">Tambah</button>
            </div>
            <ul class="cashflow-list" id="cashflowList"></ul>
        </div>
    
<script>

        // Data arus kas disimpan di database (per user login), bukan lagi localStorage,
        // supaya tiap akun punya catatan keuangan masing-masing.
        let cashData = [];

        async function apiCashflow(method, body) {
            const res = await fetch('api/aruskas.php', {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: body ? JSON.stringify(body) : undefined
            });
            return res.json();
        }

        async function loadCashflow() {
            const res = await apiCashflow('GET');
            if (res.success) {
                cashData = res.data.map(i => ({ id: i.id, desc: i.deskripsi, amount: i.jumlah, type: i.tipe }));
                renderCashflow();
            }
        }

        function renderCashflow() {
            const list = document.getElementById('cashflowList');
            list.innerHTML = "";
            let balance = 0;
            cashData.forEach((item) => {
                if(item.type === 'masuk') balance += Number(item.amount);
                else balance -= Number(item.amount);
                const li = document.createElement('li');
                li.className = `cashflow-item ${item.type}`;
                li.innerHTML = `<span>${item.desc}</span><span>${item.type === 'masuk' ? '+' : '-'}Rp ${Number(item.amount).toLocaleString()} <i class="fa-solid fa-trash" style="cursor:pointer; color:#888; margin-left:8px;" onclick="deleteCash(${item.id})"></i></span>`;
                list.appendChild(li);
            });
            document.getElementById('cashBalance').textContent = `Rp ${balance.toLocaleString()}`;
        }

        async function addCashflow() {
            const desc = document.getElementById('cashDesc').value;
            const amount = document.getElementById('cashAmount').value;
            const type = document.getElementById('cashType').value;
            if(!desc || !amount) return;

            const res = await apiCashflow('POST', { deskripsi: desc, jumlah: amount, tipe: type });
            if (res.success) {
                cashData.push({ id: res.data.id, desc: res.data.deskripsi, amount: res.data.jumlah, type: res.data.tipe });
                document.getElementById('cashDesc').value = "";
                document.getElementById('cashAmount').value = "";
                renderCashflow();
            }
        }

        async function deleteCash(id) {
            await apiCashflow('DELETE', { id });
            cashData = cashData.filter(i => i.id !== id);
            renderCashflow();
        }

        window.onload = loadCashflow;
    
</script>
<?php include 'includes/footer.php'; ?>
