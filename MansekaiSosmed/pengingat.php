<?php
require 'includes/auth.php';
requireLogin();

$pageTitle  = "Pengingat - Mansekai";
$activePage = "pengingat.php";

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
        
        .card { background-color: var(--card-bg); border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-top: 20px; }
        
        .form-reminder { display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap; }
        .form-reminder input { padding: 10px 14px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.88rem; outline: none; background: #fff; }
        .form-reminder input[type="text"] { flex-grow: 1; min-width: 200px; }
        
        .form-row-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .btn-action-main { background-color: var(--accent-green-dark); color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; }
        .btn-action-main:hover { background-color: #00876b; }
        
        .btn-test { background-color: #2c3247; color: #fff; border: none; padding: 10px 15px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.85rem; transition: 0.2s; }
        .btn-test:hover { background-color: #11131d; }

        .reminder-list { list-style: none; }
        .reminder-item { display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; background: #fafafa; border-radius: 10px; margin-bottom: 12px; border-left: 4px solid var(--accent-green-dark); gap: 15px; flex-wrap: wrap; }
        .reminder-info h4 { font-size: 1rem; color: var(--text-dark); margin-bottom: 4px; }
        .reminder-info p { font-size: 0.82rem; color: var(--text-muted); }
        
        .btn-hapus { background: none; border: none; color: #ff4d4d; cursor: pointer; font-size: 1rem; }

        #alarmModal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center; }
        .alarm-modal-content { background: #fff; padding: 35px; border-radius: 16px; text-align: center; max-width: 400px; width: 90%; box-shadow: 0 10px 30px rgba(0,0,0,0.3); animation: shake 0.5s infinite alternate; }
        @keyframes shake { 0% { transform: translateX(0); } 100% { transform: translateX(5px); } }
        .alarm-modal-content h2 { font-size: 1.5rem; margin-bottom: 10px; color: var(--text-dark); }
        .alarm-modal-content p { font-size: 0.95rem; color: var(--text-muted); margin-bottom: 25px; }
        .btn-stop-alarm { background-color: #ff4d4d; color: #fff; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem; width: 100%; transition: 0.2s; }
        .btn-stop-alarm:hover { background-color: #d93636; }

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
            <h1>Pengingat Hari</h1>
            <p style="color: var(--text-muted);">Sistem alarm cuma akan berbunyi jika berada di halaman ini.</p>
        </div>

        <div class="card">
            <h3>Buat Pengingat Baru</h3>
            <div class="form-reminder" style="margin-top: 15px;">
                <input type="text" id="inputJudul" placeholder="Nama Kegiatan / Hari Penting...">
                <input type="datetime-local" id="inputWaktu">
            </div>
            <div class="form-row-actions">
                <button class="btn-action-main" onclick="tambahPengingat()">💾 Simpan Pengingat</button>
                <button class="btn-test" onclick="testAlarm()">🔊 Test Bunyi Alarm</button>
            </div>
        </div>

        <div class="card">
            <h3>Daftar Pengingat Aktif</h3>
            <ul class="reminder-list" id="reminderListContainer" style="margin-top: 15px;">
                <!-- Daftar pengingat dimuat dinamis -->
            </ul>
        </div>

        <!-- Modal alarm: sebelumnya dipanggil dari JS tapi HTML-nya belum ada, jadi alarm tidak pernah muncul -->
        <div id="alarmModal">
            <div class="alarm-modal-content">
                <h2>⏰ Waktunya!</h2>
                <p id="alarmModalText">Ini pengingat Anda.</p>
                <button class="btn-stop-alarm" onclick="matikanAlarm()">Matikan Alarm</button>
            </div>
        </div>
    
<script>

        // Data pengingat disimpan di database (per user login), bukan lagi localStorage,
        // supaya tiap akun punya daftar pengingat masing-masing dan tersinkron di semua device.
        let daftarPengingat = [];
        let audioCtx = null;
        let alarmInterval = null;

        async function apiPengingat(method, body) {
            const res = await fetch('api/pengingat.php', {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: body ? JSON.stringify(body) : undefined
            });
            return res.json();
        }

        async function loadPengingat() {
            const res = await apiPengingat('GET');
            if (res.success) {
                daftarPengingat = res.data.map(r => ({
                    id: r.id, judul: r.judul, waktu: r.waktu.replace(' ', 'T').slice(0, 16), notifTerkirim: r.notif_terkirim
                }));
                renderPengingat();
            }
        }

        // Generator Audio Offline (Web Audio API) - Tidak butuh file MP3 eksternal
        function mulaiSuaraAlarm() {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }

            // Fungsi berulang untuk membuat bunyi alarm ritmis bernada tinggi & panjang
            alarmInterval = setInterval(() => {
                if (!audioCtx) return;
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                
                osc.type = 'square';
                osc.frequency.setValueAtTime(880, audioCtx.currentTime); // Nada A5
                osc.frequency.setValueAtTime(440, audioCtx.currentTime + 0.2); // Nada A4

                gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.4);

                osc.connect(gain);
                gain.connect(audioCtx.destination);

                osc.start();
                osc.stop(audioCtx.currentTime + 0.4);
            }, 500); // Berbunyi tiap 0.5 detik secara berulang
        }

        function hentikanSuaraAlarm() {
            if (alarmInterval) {
                clearInterval(alarmInterval);
                alarmInterval = null;
            }
        }

        function testAlarm() {
            mulaiSuaraAlarm();
            setTimeout(() => {
                hentikanSuaraAlarm();
            }, 3000); // Test bunyi selama 3 detik
        }

        function renderPengingat() {
            const container = document.getElementById('reminderListContainer');
            container.innerHTML = '';

            if (daftarPengingat.length === 0) {
                container.innerHTML = '<p style="font-size: 0.85rem; color: var(--text-muted); text-align: center; padding: 10px;">Belum ada pengingat yang diatur.</p>';
                return;
            }

            daftarPengingat.forEach((item) => {
                const tanggalFormat = new Date(item.waktu).toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short' });
                const li = document.createElement('li');
                li.className = 'reminder-item';
                li.innerHTML = `
                    <div class="reminder-info">
                        <h4>${item.judul}</h4>
                        <p>🕒 ${tanggalFormat}</p>
                    </div>
                    <button class="btn-hapus" onclick="hapusPengingat(${item.id})" title="Hapus">🗑️</button>
                `;
                container.appendChild(li);
            });
        }

        async function tambahPengingat() {
            const judul = document.getElementById('inputJudul').value.trim();
            const waktu = document.getElementById('inputWaktu').value;

            if (!judul || !waktu) {
                alert('Judul dan waktu pengingat harus diisi!');
                return;
            }

            const res = await apiPengingat('POST', { judul, waktu });
            if (res.success) {
                daftarPengingat.push({ id: res.data.id, judul, waktu, notifTerkirim: false });
                renderPengingat();

                document.getElementById('inputJudul').value = '';
                document.getElementById('inputWaktu').value = '';
                alert('Pengingat berhasil disimpan!');
            } else {
                alert(res.message || 'Gagal menyimpan pengingat.');
            }
        }

        async function hapusPengingat(id) {
            if (confirm("Hapus pengingat ini?")) {
                await apiPengingat('DELETE', { id });
                daftarPengingat = daftarPengingat.filter(item => item.id !== id);
                renderPengingat();
            }
        }

        function matikanAlarm() {
            hentikanSuaraAlarm();
            document.getElementById('alarmModal').style.display = 'none';
        }

        // Cek waktu setiap detik secara real-time
        setInterval(() => {
            const sekarang = new Date().getTime();
            
            daftarPengingat.forEach(item => {
                const targetWaktu = new Date(item.waktu).getTime();
                
                if (!item.notifTerkirim && sekarang >= targetWaktu) {
                    item.notifTerkirim = true;
                    apiPengingat('PUT', { id: item.id }); // tandai sudah bunyi di database

                    // Jalankan Alarm
                    mulaiSuaraAlarm();

                    // Tampilkan Modal Peringatan
                    document.getElementById('alarmModalText').textContent = `Waktunya untuk kegiatan: "${item.judul}"`;
                    document.getElementById('alarmModal').style.display = 'flex';
                }
            });
        }, 1000);

        window.onload = loadPengingat;
    
</script>
<?php include 'includes/footer.php'; ?>
