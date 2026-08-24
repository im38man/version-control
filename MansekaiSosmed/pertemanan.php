<?php
require 'includes/auth.php';
requireLogin();

require 'config/koneksi.php';

$pageTitle  = "Pertemanan - Mansekai";
$activePage = "pertemanan.php";

$myId = (int) $_SESSION['user_id'];

// Hitung jumlah mengikuti & pengikut punya user yang login (buat render awal,
// sebelum JS jalan / kalau JS gagal)
$stmtA = mysqli_prepare($koneksi, "SELECT COUNT(*) AS jumlah FROM follows WHERE follower_id = ?");
mysqli_stmt_bind_param($stmtA, "i", $myId);
mysqli_stmt_execute($stmtA);
$jumlahMengikuti = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmtA))['jumlah'];

$stmtB = mysqli_prepare($koneksi, "SELECT COUNT(*) AS jumlah FROM follows WHERE following_id = ?");
mysqli_stmt_bind_param($stmtB, "i", $myId);
mysqli_stmt_execute($stmtB);
$jumlahPengikut = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmtB))['jumlah'];

include 'includes/header.php';
?>
<style>
/* Style tambahan khusus halaman ini (di luar assets/style.css) */

.pertemanan-stats { display: flex; gap: 20px; margin-bottom: 10px; }
.pertemanan-stats .stat-box {
    flex: 1; background-color: var(--card-bg); border-radius: 16px; padding: 20px;
    text-align: center; cursor: pointer; box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    transition: 0.2s; border: none;
}
.pertemanan-stats .stat-box:hover { transform: translateY(-2px); box-shadow: 0 6px 24px rgba(0,0,0,0.08); }
.pertemanan-stats .stat-box b { display: block; font-size: 1.6rem; color: var(--text-dark); }
.pertemanan-stats .stat-box span { font-size: 0.85rem; color: var(--text-muted); }

.search-box-wrap { position: relative; margin-top: 10px; }
.search-box-wrap input {
    width: 100%; padding: 12px 16px 12px 42px; border-radius: 10px; border: 1px solid #e0e0e0;
    font-size: 0.9rem; font-family: inherit; outline: none; box-sizing: border-box;
}
.search-box-wrap input:focus { border-color: var(--accent-green-dark); }
.search-box-wrap i.fa-magnifying-glass { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }

.user-list { margin-top: 16px; display: flex; flex-direction: column; gap: 10px; }
.user-row {
    display: flex; align-items: center; gap: 12px; background-color: var(--card-bg);
    border-radius: 12px; padding: 12px 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.04);
}
.user-row img { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.user-row .user-info { flex: 1; min-width: 0; }
.user-row .user-info a { text-decoration: none; color: var(--text-dark); font-weight: 600; font-size: 0.92rem; }
.user-row .user-info .username { font-size: 0.78rem; color: var(--text-muted); }
.user-row .btn-follow-kecil {
    border: none; padding: 7px 14px; border-radius: 7px; font-weight: 600; font-size: 0.78rem;
    cursor: pointer; white-space: nowrap; transition: 0.2s;
}
.user-row .btn-follow-kecil.follow { background-color: var(--accent-green); color: #000; }
.user-row .btn-follow-kecil.follow:hover { background-color: var(--accent-green-dark); color: #fff; }
.user-row .btn-follow-kecil.unfollow { background-color: #333; color: #fff; }
.user-row .btn-follow-kecil.unfollow:hover { background-color: #ff4d4d; }

.empty-state { text-align: center; color: var(--text-muted); font-size: 0.85rem; padding: 30px 10px; }

/* Modal daftar following / followers */
.modal-overlay {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5);
    z-index: 999; align-items: flex-start; justify-content: center; padding: 60px 16px;
}
.modal-overlay.aktif { display: flex; }
.modal-box {
    background-color: var(--card-bg); border-radius: 16px; padding: 22px; width: 100%;
    max-width: 480px; max-height: 75vh; display: flex; flex-direction: column;
}
.modal-box .modal-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.modal-box .modal-head h3 { font-size: 1.05rem; font-weight: 700; margin: 0; }
.modal-box .modal-close { background: none; border: none; font-size: 1.1rem; cursor: pointer; color: var(--text-muted); }
.modal-box .modal-close:hover { color: var(--danger); }
.modal-box .modal-body { overflow-y: auto; flex: 1; }
</style>

<div class="header-title">
    <h1>Pertemanan</h1>
    <p>Cari teman, lihat siapa yang kamu ikuti, dan siapa yang mengikuti kamu.</p>
</div>

<div class="pertemanan-stats">
    <button type="button" class="stat-box" onclick="bukaModal('following')">
        <b id="statMengikuti"><?= $jumlahMengikuti ?></b>
        <span>Mengikuti</span>
    </button>
    <button type="button" class="stat-box" onclick="bukaModal('followers')">
        <b id="statPengikut"><?= $jumlahPengikut ?></b>
        <span>Pengikut</span>
    </button>
</div>

<div class="card">
    <div class="search-box-wrap">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="inputCari" placeholder="Cari nama, username, atau ID user...">
    </div>
    <div id="hasilCari" class="user-list"></div>
</div>

<!-- Modal daftar following / followers -->
<div class="modal-overlay" id="modalDaftar">
    <div class="modal-box">
        <div class="modal-head">
            <h3 id="modalJudul">Mengikuti</h3>
            <button type="button" class="modal-close" onclick="tutupModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body user-list" id="modalIsi"></div>
    </div>
</div>

<script>
const AVATAR_DEFAULT = 'img.magnific.com/premium-vector/user-profile-icon-circle_1256048-12499.jpg?auto=format&fit=crop&w=200&q=80';

function baseRowHTML(u, showFollowBtn) {
    const avatar = u.avatar || AVATAR_DEFAULT;
    const followBtn = showFollowBtn ? `
        <button class="btn-follow-kecil ${u.is_following ? 'unfollow' : 'follow'}"
            data-userid="${u.id}" data-following="${u.is_following ? '1' : '0'}"
            onclick="toggleFollow(this)">
            ${u.is_following ? 'Mengikuti' : 'Follow'}
        </button>` : '';
    return `
        <div class="user-row" data-row-userid="${u.id}">
            <img src="${avatar}" alt="Avatar">
            <div class="user-info">
                <a href="lihat-profil.php?id=${u.id}">${u.nama}</a>
                <div class="username">@${u.username}</div>
            </div>
            ${followBtn}
        </div>`;
}

// ==== Cari user ====
let timerCari = null;
const inputCari = document.getElementById('inputCari');
const hasilCari = document.getElementById('hasilCari');

inputCari.addEventListener('input', () => {
    clearTimeout(timerCari);
    const q = inputCari.value.trim();
    if (q === '') { hasilCari.innerHTML = ''; return; }
    timerCari = setTimeout(() => jalankanCari(q), 350);
});

async function jalankanCari(q) {
    hasilCari.innerHTML = '<div class="empty-state">Mencari...</div>';
    try {
        const res = await fetch('api/pertemanan.php?action=search&q=' + encodeURIComponent(q));
        const json = await res.json();
        if (!json.success) { hasilCari.innerHTML = `<div class="empty-state">${json.message || 'Gagal mencari.'}</div>`; return; }
        if (json.data.length === 0) { hasilCari.innerHTML = '<div class="empty-state">Tidak ada user ditemukan.</div>'; return; }
        hasilCari.innerHTML = json.data.map(u => baseRowHTML(u, true)).join('');
    } catch (e) {
        hasilCari.innerHTML = '<div class="empty-state">Terjadi kesalahan, coba lagi.</div>';
    }
}

// ==== Follow / Unfollow dari daftar (search / modal) ====
async function toggleFollow(btn) {
    const userId = btn.dataset.userid;
    const sedangMengikuti = btn.dataset.following === '1';
    const action = sedangMengikuti ? 'unfollow' : 'follow';

    btn.disabled = true;
    try {
        const res = await fetch('api/pertemanan.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action, user_id: userId })
        });
        const json = await res.json();
        if (json.success) {
            const kini = !sedangMengikuti;
            btn.dataset.following = kini ? '1' : '0';
            btn.className = 'btn-follow-kecil ' + (kini ? 'unfollow' : 'follow');
            btn.textContent = kini ? 'Mengikuti' : 'Follow';
            muatRingkasan();
        } else {
            alert(json.message || 'Gagal memproses permintaan.');
        }
    } catch (e) {
        alert('Terjadi kesalahan, coba lagi.');
    }
    btn.disabled = false;
}

// ==== Ringkasan jumlah mengikuti / pengikut ====
async function muatRingkasan() {
    try {
        const res = await fetch('api/pertemanan.php?action=ringkasan');
        const json = await res.json();
        if (json.success) {
            document.getElementById('statMengikuti').textContent = json.data.mengikuti;
            document.getElementById('statPengikut').textContent = json.data.pengikut;
        }
    } catch (e) { /* diem aja */ }
}

// ==== Modal daftar following / followers ====
const modalDaftar = document.getElementById('modalDaftar');
const modalJudul  = document.getElementById('modalJudul');
const modalIsi    = document.getElementById('modalIsi');

async function bukaModal(jenis) {
    modalJudul.textContent = jenis === 'following' ? 'Mengikuti' : 'Pengikut';
    modalIsi.innerHTML = '<div class="empty-state">Memuat...</div>';
    modalDaftar.classList.add('aktif');

    try {
        const res = await fetch('api/pertemanan.php?action=' + jenis);
        const json = await res.json();
        if (!json.success) { modalIsi.innerHTML = `<div class="empty-state">${json.message || 'Gagal memuat.'}</div>`; return; }
        if (json.data.length === 0) {
            modalIsi.innerHTML = `<div class="empty-state">Belum ada ${jenis === 'following' ? 'orang yang kamu ikuti' : 'pengikut'}.</div>`;
            return;
        }
        // Di daftar "Mengikuti" pasti is_following = true (unfollow langsung dari sini).
        // Di daftar "Pengikut" nggak otomatis kita follow balik, jadi tombolnya disembunyikan.
        if (jenis === 'following') {
            modalIsi.innerHTML = json.data.map(u => baseRowHTML({ ...u, is_following: true }, true)).join('');
        } else {
            modalIsi.innerHTML = json.data.map(u => baseRowHTML(u, false)).join('');
        }
    } catch (e) {
        modalIsi.innerHTML = '<div class="empty-state">Terjadi kesalahan, coba lagi.</div>';
    }
}

function tutupModal() {
    modalDaftar.classList.remove('aktif');
}

// Klik di luar modal-box buat nutup modal
modalDaftar.addEventListener('click', (e) => {
    if (e.target === modalDaftar) tutupModal();
});

// Kalau unfollow dari dalam modal "Mengikuti", ilangin barisnya + update ringkasan
modalIsi.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-follow-kecil');
    if (!btn) return;
    // Kasih delay dikit biar animasi toggleFollow (di atas) selesai duluan,
    // baru kita cek: kalau statusnya sekarang "tidak follow" dan ini modal Mengikuti, hapus barisnya.
    setTimeout(() => {
        if (modalJudul.textContent === 'Mengikuti' && btn.dataset.following === '0') {
            const row = btn.closest('.user-row');
            if (row) row.remove();
            if (modalIsi.children.length === 0) {
                modalIsi.innerHTML = '<div class="empty-state">Belum ada orang yang kamu ikuti.</div>';
            }
        }
    }, 250);
});
</script>

<?php include 'includes/footer.php'; ?>
