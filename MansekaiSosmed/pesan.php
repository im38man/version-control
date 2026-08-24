<?php
require 'includes/auth.php';
requireLogin();

require 'config/koneksi.php';

$pageTitle  = "Pesan - Mansekai";
$activePage = "pesan.php";

$targetIdAwal = (int) ($_GET['user_id'] ?? 0);

include 'includes/header.php';
?>
<style>
/* Style tambahan khusus halaman ini (di luar assets/style.css) */

.pesan-wrap { display: flex; gap: 20px; margin-top: 20px; height: calc(100vh - 160px); min-height: 420px; }

.pesan-sidebar { width: 300px; flex-shrink: 0; background-color: var(--card-bg); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); display: flex; flex-direction: column; overflow: hidden; }
.pesan-sidebar-header { padding: 18px; border-bottom: 1px solid #f0f0f0; }
.pesan-sidebar-header input { width: 100%; padding: 9px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.85rem; outline: none; }
.pesan-sidebar-header input:focus { border-color: var(--accent-green-dark); }
.pesan-search-result { border-bottom: 1px solid #f0f0f0; }

.pesan-tabs { display: flex; border-bottom: 1px solid #f0f0f0; flex-shrink: 0; }
.pesan-tab { flex: 1; text-align: center; padding: 11px 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer; color: var(--text-muted); border-bottom: 2px solid transparent; position: relative; }
.pesan-tab.active { color: var(--accent-green-dark); border-bottom-color: var(--accent-green-dark); }
.pesan-tab .tab-dot { background: var(--danger); color: #fff; font-size: 0.62rem; font-weight: 700; min-width: 15px; height: 15px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; padding: 0 4px; margin-left: 4px; vertical-align: middle; }

.pesan-list { flex-grow: 1; overflow-y: auto; }
.pesan-item { display: flex; align-items: center; gap: 12px; padding: 13px 16px; cursor: pointer; border-bottom: 1px solid #f6f6f6; transition: 0.15s; }
.pesan-item:hover { background: #faf5f7; }
.pesan-item.active { background: rgba(0,196,159,0.08); }
.pesan-item img { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.pesan-item .pesan-item-info { flex-grow: 1; min-width: 0; }
.pesan-item .pesan-item-info b { display: block; font-size: 0.88rem; }
.pesan-item .pesan-item-info span { display: block; font-size: 0.76rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pesan-item .unread-dot { background: var(--danger); color: #fff; font-size: 0.68rem; font-weight: 700; min-width: 18px; height: 18px; border-radius: 10px; display: flex; align-items: center; justify-content: center; padding: 0 5px; flex-shrink: 0; }

.permintaan-actions { display: flex; gap: 6px; flex-shrink: 0; }
.permintaan-actions button { border: none; border-radius: 8px; padding: 6px 10px; font-size: 0.75rem; font-weight: 600; cursor: pointer; }
.btn-terima { background: var(--accent-green-dark); color: #fff; }
.btn-tolak { background: #f0f0f0; color: var(--text-dark); }

/* MODIFIKASI: Area main disembunyikan secara default */
.pesan-main { display: none; flex-grow: 1; background-color: var(--card-bg); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); flex-direction: column; overflow: hidden; }

/* Munculkan area main kalau chat-aktif */
.pesan-wrap.chat-aktif .pesan-main { display: flex; }

.pesan-main-header { padding: 16px 20px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 12px; }

/* MODIFIKASI: Tombol Back */
.btn-back { display: none; background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-dark); margin-right: 5px; }

.pesan-main-header img { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; }
.pesan-main-header b { font-size: 0.95rem; }
.pesan-main-header span { display: block; font-size: 0.78rem; color: var(--text-muted); }

.pesan-messages { flex-grow: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 10px; }
.bubble-row { display: flex; align-items: center; gap: 6px; max-width: 100%; }
.bubble-row.keluar { align-self: flex-end; flex-direction: row-reverse; }
.bubble-row.masuk { align-self: flex-start; }
.bubble { max-width: 65%; padding: 10px 14px; border-radius: 14px; font-size: 0.88rem; line-height: 1.4; word-wrap: break-word; position: relative; }
.bubble.masuk { background: #f0f0f0; color: var(--text-dark); border-bottom-left-radius: 4px; }
.bubble.keluar { background: var(--accent-green-dark); color: #fff; border-bottom-right-radius: 4px; }
.bubble .waktu { display: block; font-size: 0.68rem; opacity: 0.7; margin-top: 4px; }
.btn-hapus-pesan { background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 0.8rem; opacity: 0; transition: 0.15s; padding: 4px; flex-shrink: 0; }
.bubble-row:hover .btn-hapus-pesan { opacity: 1; }
.btn-hapus-pesan:hover { color: var(--danger); }

.pesan-input-row { display: flex; gap: 10px; padding: 14px 20px; border-top: 1px solid #f0f0f0; }
.pesan-input-row input { flex-grow: 1; padding: 10px 14px; border: 1px solid #ddd; border-radius: 20px; font-size: 0.88rem; outline: none; }
.pesan-input-row input:focus { border-color: var(--accent-green-dark); }
.pesan-input-row button { border: none; background: var(--accent-green-dark); color: #fff; width: 42px; height: 42px; border-radius: 50%; cursor: pointer; flex-shrink: 0; }
.pesan-input-row button:hover { background: #00876b; }

.pesan-empty { flex-grow: 1; display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 0.9rem; text-align: center; padding: 20px; }

.pesan-permintaan-box { flex-grow: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; padding: 20px; text-align: center; }
.pesan-permintaan-box img { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; }
.pesan-permintaan-box p { color: var(--text-muted); font-size: 0.85rem; max-width: 320px; }
.pesan-permintaan-box .aksi { display: flex; gap: 10px; }
.pesan-permintaan-box button { border: none; border-radius: 10px; padding: 10px 18px; font-size: 0.85rem; font-weight: 600; cursor: pointer; }

@media (max-width: 800px) {
    /* MODIFIKASI: Penyesuaian tinggi dan lebar biar responsif di HP */
    .pesan-wrap { flex-direction: column; height: calc(100vh - 120px); }
    .pesan-sidebar, .pesan-main { width: 100%; height: 100%; }

    /* Jika chat sedang aktif, sembunyikan sidebar dan tampilkan tombol back */
    .pesan-wrap.chat-aktif .pesan-sidebar { display: none; }
    .btn-back { display: block; }
}
</style>

<div class="header-title">
    <h1>Pesan</h1>
    <p>Pesan langsung dengan sesama user Mansekai Study.</p>
</div>

<div class="pesan-wrap" id="pesanWrap">
    <div class="pesan-sidebar">
        <div class="pesan-sidebar-header">
            <input type="text" id="inputCariPesan" placeholder="Cari user buat mulai pesan...">
        </div>
        <div class="pesan-tabs">
            <div class="pesan-tab active" data-tab="percakapan" id="tabPercakapan">Pesan</div>
            <div class="pesan-tab" data-tab="permintaan" id="tabPermintaan">Permintaan <span class="tab-dot" id="dotPermintaan" style="display:none;">0</span></div>
        </div>
        <div class="pesan-list" id="hasilCariPesan" style="display:none;"></div>
        <div class="pesan-list" id="daftarPercakapan"></div>
        <div class="pesan-list" id="daftarPermintaan" style="display:none;"></div>
    </div>

    <!-- Area ini sekarang akan tersembunyi sampai di-klik -->
    <div class="pesan-main" id="pesanMain"></div>
</div>

<script>
    let userAktif = <?= $targetIdAwal > 0 ? $targetIdAwal : 'null' ?>;
    let pollTimer = null;
    let footerState = null; // biar footer/input nggak di-render ulang tiap polling (nyebabin keyboard HP turun)

    async function apiPesan(url, options) {
        const res = await fetch(url, options);
        try { return await res.json(); } catch (e) { return { success: false, message: 'Respons server tidak valid.' }; }
    }

    function waktuSingkat(iso) {
        if (!iso) return '';
        const d = new Date(iso.replace(' ', 'T'));
        return d.toLocaleString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
    }

    function escapeHTML(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    async function muatDaftarPercakapan() {
        const res = await apiPesan('api/pesan.php?action=percakapan');
        const wadah = document.getElementById('daftarPercakapan');
        if (!res.success) { wadah.innerHTML = '<div class="pesan-empty">' + (res.message || 'Gagal memuat.') + '</div>'; return; }

        if (!res.data.length) {
            wadah.innerHTML = '<div class="pesan-empty" style="padding:30px 16px;">Belum ada percakapan. Cari user di atas buat mulai pesan.</div>';
            return;
        }

        wadah.innerHTML = res.data.map(c => `
            <div class="pesan-item ${c.id === userAktif ? 'active' : ''}" data-id="${c.id}" data-nama="${escapeHTML(c.nama)}" data-username="${escapeHTML(c.username)}" data-avatar="${escapeHTML(c.avatar)}">
                <img src="${c.avatar}" alt="${escapeHTML(c.nama)}">
                <div class="pesan-item-info">
                    <b>${escapeHTML(c.nama)}</b>
                    <span>${c.dari_saya ? 'Kamu: ' : ''}${escapeHTML(c.pesan_terakhir)}</span>
                </div>
                ${c.unread > 0 ? `<div class="unread-dot">${c.unread > 9 ? '9+' : c.unread}</div>` : ''}
            </div>
        `).join('');
    }

    // ==== Tab: daftar permintaan pesan masuk ====
    async function muatDaftarPermintaan() {
        const res = await apiPesan('api/pesan.php?action=permintaan_masuk');
        const wadah = document.getElementById('daftarPermintaan');
        if (!res.success) { wadah.innerHTML = '<div class="pesan-empty">' + (res.message || 'Gagal memuat.') + '</div>'; return; }

        if (!res.data.length) {
            wadah.innerHTML = '<div class="pesan-empty" style="padding:30px 16px;">Belum ada permintaan pesan masuk.</div>';
        } else {
            wadah.innerHTML = res.data.map(p => `
                <div class="pesan-item" data-id="${p.id}">
                    <img src="${p.avatar}" alt="${escapeHTML(p.nama)}">
                    <div class="pesan-item-info">
                        <b>${escapeHTML(p.nama)}</b>
                        <span>@${escapeHTML(p.username)} ingin kirim pesan</span>
                    </div>
                    <div class="permintaan-actions">
                        <button class="btn-terima" data-aksi="terima" data-id="${p.id}">Terima</button>
                        <button class="btn-tolak" data-aksi="tolak" data-id="${p.id}">Tolak</button>
                    </div>
                </div>
            `).join('');
        }

        const dot = document.getElementById('dotPermintaan');
        if (res.data.length > 0) {
            dot.style.display = 'inline-flex';
            dot.textContent = res.data.length > 9 ? '9+' : res.data.length;
        } else {
            dot.style.display = 'none';
        }
    }

    document.getElementById('daftarPermintaan').addEventListener('click', async (e) => {
        const tombol = e.target.closest('button[data-aksi]');
        if (!tombol) return;
        const partnerId = parseInt(tombol.dataset.id, 10);
        const aksi = tombol.dataset.aksi === 'terima' ? 'terima_permintaan' : 'tolak_permintaan';

        tombol.disabled = true;
        const res = await apiPesan('api/pesan.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: aksi, user_id: partnerId })
        });

        if (res.success) {
            await muatDaftarPermintaan();
            if (aksi === 'terima_permintaan') {
                muatDaftarPercakapan();
            }
        } else {
            alert(res.message || 'Gagal memproses permintaan.');
            tombol.disabled = false;
        }
    });

    document.getElementById('tabPercakapan').addEventListener('click', () => {
        document.getElementById('tabPercakapan').classList.add('active');
        document.getElementById('tabPermintaan').classList.remove('active');
        document.getElementById('daftarPercakapan').style.display = 'block';
        document.getElementById('daftarPermintaan').style.display = 'none';
    });

    document.getElementById('tabPermintaan').addEventListener('click', () => {
        document.getElementById('tabPermintaan').classList.add('active');
        document.getElementById('tabPercakapan').classList.remove('active');
        document.getElementById('daftarPermintaan').style.display = 'block';
        document.getElementById('daftarPercakapan').style.display = 'none';
        muatDaftarPermintaan();
    });

    async function cariUserPesan() {
        const q = document.getElementById('inputCariPesan').value.trim();
        const wadah = document.getElementById('hasilCariPesan');
        const daftar = document.getElementById('daftarPercakapan');

        if (q === '') { wadah.style.display = 'none'; daftar.style.display = 'block'; return; }

        const res = await apiPesan('api/pertemanan.php?action=search&q=' + encodeURIComponent(q));
        daftar.style.display = 'none';
        wadah.style.display = 'block';

        if (!res.success || !res.data.length) {
            wadah.innerHTML = '<div class="pesan-empty" style="padding:20px 16px;">Tidak ada user yang cocok.</div>';
            return;
        }

        wadah.innerHTML = res.data.map(u => `
            <div class="pesan-item pesan-search-result" data-id="${u.id}" data-nama="${escapeHTML(u.nama)}" data-username="${escapeHTML(u.username)}" data-avatar="${escapeHTML(u.avatar)}">
                <img src="${u.avatar}" alt="${escapeHTML(u.nama)}">
                <div class="pesan-item-info">
                    <b>${escapeHTML(u.nama)}</b>
                    <span>@${escapeHTML(u.username)}</span>
                </div>
            </div>
        `).join('');
    }

    // FUNGSI BARU: Untuk menutup chat (khususnya buat balik ke list di versi HP)
    function tutupPesan() {
        document.getElementById('pesanWrap').classList.remove('chat-aktif');
        userAktif = null;
        if (pollTimer) clearInterval(pollTimer);
        muatDaftarPercakapan(); // Refresh list biar tanda 'active' hilang
        history.replaceState(null, '', 'pesan.php');
    }

    async function bukaPesan(id, nama, username, avatar) {
        userAktif = id;
        document.getElementById('inputCariPesan').value = '';
        document.getElementById('hasilCariPesan').style.display = 'none';
        document.getElementById('daftarPercakapan').style.display = 'block';

        // Menambahkan class untuk memunculkan area pesan
        document.getElementById('pesanWrap').classList.add('chat-aktif');

        history.replaceState(null, '', 'pesan.php?user_id=' + id);

        document.getElementById('pesanMain').innerHTML = `
            <div class="pesan-main-header">
                <button class="btn-back" onclick="tutupPesan()"><i class="fa-solid fa-arrow-left"></i></button>
                <img src="${avatar}" alt="${nama}">
                <div><b>${escapeHTML(nama)}</b><span>@${escapeHTML(username)}</span></div>
            </div>
            <div class="pesan-messages" id="pesanMessages"></div>
            <div id="pesanFooter"></div>
        `;
        footerState = null; // chat baru dibuka -> footer wajib dirender dari nol sekali

        await muatPesan();
        muatDaftarPercakapan();
        muatBadgeNotif();

        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(muatPesan, 2500);
    }

    // Render footer (kotak input kalau udah bisa chat, atau kotak permintaan pesan kalau belum)
    function renderFooterPesan(data) {
        const footer = document.getElementById('pesanFooter');
        if (!footer) return;

        // Kunci state biar footer (khususnya <input>) nggak di-render ulang tiap
        // polling 2.5 detik kalau statusnya sama kayak sebelumnya. Kalau ini
        // dilewat, input pesan kebongkar-pasang terus -> keyboard HP jadi turun
        // sendiri padahal user lagi ngetik.
        const stateKey = data.bisa_chat ? 'chat' : ('req_' + data.status_permintaan);
        if (stateKey === footerState) return;
        footerState = stateKey;

        if (data.bisa_chat) {
            footer.innerHTML = `
                <form class="pesan-input-row" id="formKirimPesan">
                    <input type="text" id="inputPesan" placeholder="Tulis pesan..." autocomplete="off" maxlength="2000">
                    <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
                </form>
            `;
            document.getElementById('formKirimPesan').addEventListener('submit', kirimPesan);
            return;
        }

        const status = data.status_permintaan;
        let html = '';
        if (status === 'menunggu_dia') {
            html = `<div class="pesan-permintaan-box">
                <p>Permintaan pesan kamu masih menunggu diterima sama <b>${escapeHTML(data.partner.nama)}</b>.</p>
            </div>`;
        } else if (status === 'menunggu_saya') {
            html = `<div class="pesan-permintaan-box">
                <p><b>${escapeHTML(data.partner.nama)}</b> ingin kirim pesan ke kamu. Terima dulu biar bisa mulai obrolan.</p>
                <div class="aksi">
                    <button class="btn-terima" onclick="terimaDariChat(${data.partner.id})">Terima</button>
                    <button class="btn-tolak" onclick="tolakDariChat(${data.partner.id})">Tolak</button>
                </div>
            </div>`;
        } else {
            html = `<div class="pesan-permintaan-box">
                <p>Kalian belum saling follow. Kirim permintaan pesan dulu ke <b>${escapeHTML(data.partner.nama)}</b> sebelum bisa chat.</p>
                <div class="aksi">
                    <button class="btn-terima" onclick="kirimPermintaan(${data.partner.id})">Kirim Permintaan Pesan</button>
                </div>
            </div>`;
        }
        footer.innerHTML = html;
    }

    async function kirimPermintaan(partnerId) {
        const res = await apiPesan('api/pesan.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'kirim_permintaan', user_id: partnerId })
        });
        if (res.success) {
            await muatPesan();
        } else {
            alert(res.message || 'Gagal mengirim permintaan.');
        }
    }

    async function terimaDariChat(partnerId) {
        const res = await apiPesan('api/pesan.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'terima_permintaan', user_id: partnerId })
        });
        if (res.success) { await muatPesan(); } else { alert(res.message || 'Gagal.'); }
    }

    async function tolakDariChat(partnerId) {
        const res = await apiPesan('api/pesan.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'tolak_permintaan', user_id: partnerId })
        });
        if (res.success) { await muatPesan(); } else { alert(res.message || 'Gagal.'); }
    }

    async function hapusPesan(pesanId) {
        if (!confirm('Hapus pesan ini buat kamu?')) return;
        const res = await apiPesan('api/pesan.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'hapus', pesan_id: pesanId })
        });
        if (res.success) {
            await muatPesan();
            muatDaftarPercakapan();
        } else {
            alert(res.message || 'Gagal menghapus pesan.');
        }
    }

    async function muatPesan() {
        if (!userAktif) return;
        const res = await apiPesan('api/pesan.php?action=pesan&user_id=' + userAktif);
        const wadah = document.getElementById('pesanMessages');
        if (!wadah) return;

        if (!res.success) { wadah.innerHTML = '<div class="pesan-empty">' + (res.message || 'Gagal memuat pesan.') + '</div>'; return; }

        const posisiBawah = wadah.scrollTop + wadah.clientHeight >= wadah.scrollHeight - 40;

        wadah.innerHTML = res.data.messages.length
            ? res.data.messages.map(m => `
                <div class="bubble-row ${m.dari_saya ? 'keluar' : 'masuk'}">
                    <div class="bubble ${m.dari_saya ? 'keluar' : 'masuk'}">
                        ${escapeHTML(m.pesan)}
                        <span class="waktu">${waktuSingkat(m.waktu)}</span>
                    </div>
                    <button class="btn-hapus-pesan" title="Hapus pesan" onclick="hapusPesan(${m.id})"><i class="fa-solid fa-trash"></i></button>
                </div>
            `).join('')
            : '<div class="pesan-empty">Belum ada pesan. Mulai obrolan yuk!</div>';

        if (posisiBawah || wadah.dataset.pernahMuat !== '1') {
            wadah.scrollTop = wadah.scrollHeight;
        }
        wadah.dataset.pernahMuat = '1';

        renderFooterPesan(res.data);
    }

    async function kirimPesan(e) {
        e.preventDefault();
        const input = document.getElementById('inputPesan');
        if (!input) return;
        const pesan = input.value.trim();
        if (pesan === '') return;

        input.value = '';
        input.disabled = true;

        const res = await apiPesan('api/pesan.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'kirim', user_id: userAktif, pesan })
        });

        await muatPesan();
        const inputBaru = document.getElementById('inputPesan');
        if (inputBaru) { inputBaru.disabled = false; inputBaru.focus(); }

        if (res.success) {
            muatDaftarPercakapan();
        } else {
            alert(res.message || 'Gagal mengirim pesan.');
        }
    }

    async function muatBadgeNotif() {
        const res = await apiPesan('api/pesan.php?action=unread_total');
        const badge = document.getElementById('badgeChatUnread');
        if (badge && res.success) {
            const total = res.data.total + res.data.permintaan;
            badge.textContent = total;
            badge.style.display = total > 0 ? 'inline-flex' : 'none';
        }
    }

    document.getElementById('inputCariPesan').addEventListener('input', cariUserPesan);

    document.getElementById('hasilCariPesan').addEventListener('click', (e) => {
        const item = e.target.closest('.pesan-item');
        if (!item) return;
        bukaPesan(parseInt(item.dataset.id, 10), item.dataset.nama, item.dataset.username, item.dataset.avatar);
    });

    document.getElementById('daftarPercakapan').addEventListener('click', (e) => {
        const item = e.target.closest('.pesan-item');
        if (!item) return;
        bukaPesan(parseInt(item.dataset.id, 10), item.dataset.nama, item.dataset.username, item.dataset.avatar);
    });

    muatDaftarPercakapan();
    muatDaftarPermintaan();
    muatBadgeNotif();

    <?php if ($targetIdAwal > 0): ?>
    (async () => {
        const res = await apiPesan('api/pesan.php?action=pesan&user_id=<?= (int) $targetIdAwal ?>');
        if (res.success) {
            bukaPesan(res.data.partner.id, res.data.partner.nama, res.data.partner.username, res.data.partner.avatar);
        }
    })();
    <?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>
