<?php
require 'includes/auth.php';
requireLogin();

require 'config/koneksi.php';

$pageTitle  = "Profil - Mansekai";
$activePage = "profil.php";
$pesanProfil = "";
$errorProfil = "";

// Ganti password (khusus admin, langsung ke database)
if (isAdmin() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ganti_password'])) {
    $passwordLama = $_POST['password_lama'] ?? '';
    $passwordBaru = $_POST['password_baru'] ?? '';
    $konfirmasi   = $_POST['password_konfirmasi'] ?? '';

    $stmt = mysqli_prepare($koneksi, "SELECT password FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$row || !password_verify($passwordLama, $row['password'])) {
        $errorProfil = "Password lama salah.";
    } elseif (strlen($passwordBaru) < 6) {
        $errorProfil = "Password baru minimal 6 karakter.";
    } elseif ($passwordBaru !== $konfirmasi) {
        $errorProfil = "Konfirmasi password tidak cocok.";
    } else {
        $hash = password_hash($passwordBaru, PASSWORD_DEFAULT);
        $update = mysqli_prepare($koneksi, "UPDATE users SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($update, "si", $hash, $_SESSION['user_id']);
        mysqli_stmt_execute($update);
        $pesanProfil = "Password berhasil diperbarui.";
    }
}

include 'includes/header.php';
?>
<style>
/* Style tambahan khusus halaman ini (di luar assets/style.css).
   Dipakai bareng oleh admin & user, jadi class-nya dikasih nama
   khusus (card-profil dkk) biar TIDAK bentrok sama .card / .grid-2
   bawaan assets/style.css yang dipakai kartu "Informasi Akun" &
   "Ganti Password" punya admin. */

.header-title-profil { width: 100%; text-align: center; margin-bottom: 20px; }
.header-title-profil h1 { font-size: 1.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; color: var(--text-dark); }
.header-title-profil p { font-size: 0.9rem; color: var(--text-muted); }

.card-profil-wrap { display: flex; justify-content: center; }
.card-profil { background-color: #11131d; color: #fff; border-radius: 16px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-top: 20px; width: 100%; max-width: 550px; text-align: center; }
.card-profil img { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent-green); margin-bottom: 15px; }

.card-profil .form-group { margin-bottom: 15px; text-align: left; }
.card-profil .form-group label { display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 5px; }
.card-profil .form-group input { width: 100%; padding: 8px 12px; border: 1px solid #333; border-radius: 6px; background: #1c2130; color: #fff; font-size: 0.9rem; outline: none; }
.card-profil .form-group input:focus { border-color: var(--accent-green); }

.btn-save { background-color: var(--accent-green); color: #000; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; width: 100%; margin-top: 10px; font-size: 0.9rem; transition: 0.2s; }
.btn-save:hover { background-color: var(--accent-green-dark); }

.social-inputs-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 15px; text-align: left; }
@media (max-width: 600px) { .social-inputs-grid { grid-template-columns: 1fr; } }
</style>

<?php if (isAdmin()): ?>
<div class="header-title">
    <h1>Profil Admin</h1>
    <p>Kelola informasi akun, foto, bio, dan tautan sosial media kamu.</p>
</div>

<div class="grid-2">
    <div class="card">
        <h3>Informasi Akun</h3>
        <ul class="module-list">
            <li class="module-item"><span><i class="fa-solid fa-user"></i> Nama</span><b><?= htmlspecialchars($_SESSION['nama']) ?></b></li>
            <li class="module-item"><span><i class="fa-solid fa-at"></i> Username</span><b><?= htmlspecialchars($_SESSION['username']) ?></b></li>
            <li class="module-item"><span><i class="fa-solid fa-user-shield"></i> Role</span><span class="badge selesai">Admin</span></li>
        </ul>
    </div>

    <div class="card">
        <h3>Ganti Password</h3>
        <?php if ($errorProfil): ?><div class="alert alert-error"><?= htmlspecialchars($errorProfil) ?></div><?php endif; ?>
        <?php if ($pesanProfil): ?><div class="alert alert-success"><?= htmlspecialchars($pesanProfil) ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="ganti_password" value="1">
            <div class="form-group">
                <label>Password Lama</label>
                <input type="password" name="password_lama" required>
            </div>
            <div class="form-group">
                <label>Password Baru</label>
                <input type="password" name="password_baru" required>
            </div>
            <div class="form-group">
                <label>Konfirmasi Password Baru</label>
                <input type="password" name="password_konfirmasi" required>
            </div>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-key"></i> Update Password</button>
        </form>
    </div>
</div>
<?php else: ?>
<div class="header-title-profil">
    <h1>Profil Pengguna</h1>
    <p>Kelola informasi akun dan tautan sosial media Anda.</p>
</div>
<?php endif; ?>

<!-- Kartu ini SELALU tampil, baik untuk admin maupun user, supaya
     bio, nama, foto profil, dan link sosial media bisa diedit
     oleh SEMUA akun (per user, tersimpan di tabel profil & users). -->
<div class="card-profil-wrap">
    <div class="card-profil">
        <img id="previewAvatar" src="img.magnific.com/premium-vector/user-profile-icon-circle_1256048-12499.jpg?auto=format&fit=crop&w=200&q=80" alt="Avatar">

        <div class="form-group">
            <label>Foto Profil</label>
            <input type="file" id="inputAvatarFile" accept=".jpg,.jpeg,.png,.gif,.webp" onchange="uploadAvatar(event)">
            <small style="color: var(--text-muted); display:block; margin-top:4px;">JPG/PNG/GIF/WEBP, maksimal 3MB.</small>
        </div>
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" id="inputName" placeholder="Nama Anda...">
        </div>
        <div class="form-group">
            <label>Bio / Profesi</label>
            <input type="text" id="inputBio" placeholder="Bio singkat...">
        </div>

        <hr style="border: 0; border-top: 1px solid #333; margin: 20px 0;">
        <p style="font-size: 0.85rem; color: var(--accent-green); margin-bottom: 12px; text-align: left;">Tautan Media Sosial & Platform:</p>

        <div class="social-inputs-grid">
            <div class="form-group" style="grid-column: span 2;">
                <label>GitHub Link</label>
                <input type="text" id="inputGithub" placeholder="https://github.com/...">
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label>LinkedIn Link</label>
                <input type="text" id="inputLinkedin" placeholder="https://linkedin.com/...">
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label>Instagram Link</label>
                <input type="text" id="inputInstagram" placeholder="https://instagram.com/...">
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label>TikTok Link</label>
                <input type="text" id="inputTiktok" placeholder="https://tiktok.com/...">
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label>Facebook Link</label>
                <input type="text" id="inputFacebook" placeholder="https://facebook.com/...">
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label>X (Twitter) Link</label>
                <input type="text" id="inputX" placeholder="https://x.com/...">
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label>YouTube Link</label>
                <input type="text" id="inputYoutube" placeholder="https://youtube.com/...">
            </div>
        </div>

        <button class="btn-save" onclick="saveProfile()">Simpan Perubahan</button>
    </div>
</div>

<script>

    // Data profil (bio, nama, link sosmed, foto) disimpan di database per user
    // login (tabel profil & users), dipakai bareng oleh admin maupun user biasa.
    async function apiProfil(method, body) {
        const res = await fetch('api/profil.php', {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: body ? JSON.stringify(body) : undefined
        });
        if (!res.ok) {
            console.error('Gagal memuat/menyimpan profil, status:', res.status);
            return { success: false, message: 'Server error (' + res.status + ')' };
        }
        try {
            return await res.json();
        } catch (e) {
            console.error('Respons server bukan JSON yang valid:', e);
            return { success: false, message: 'Respons server tidak valid.' };
        }
    }

    async function loadProfileData() {
        const res = await apiProfil('GET');
        if (!res.success) {
            alert(res.message || 'Gagal memuat data profil.');
            return;
        }
        const p = res.data;

        document.getElementById('inputName').value = p.nama || '';
        document.getElementById('inputBio').value = p.bio || '';
        document.getElementById('previewAvatar').src = p.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80';

        document.getElementById('inputGithub').value = p.link_github || '';
        document.getElementById('inputLinkedin').value = p.link_linkedin || '';
        document.getElementById('inputInstagram').value = p.link_instagram || '';
        document.getElementById('inputTiktok').value = p.link_tiktok || '';
        document.getElementById('inputFacebook').value = p.link_facebook || '';
        document.getElementById('inputX').value = p.link_x || '';
        document.getElementById('inputYoutube').value = p.link_youtube || '';
    }

    // Upload foto profil langsung ke server begitu file dipilih
    async function uploadAvatar(event) {
        const file = event.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('avatar', file);

        try {
            const res = await fetch('api/avatar.php', { method: 'POST', body: formData });
            const json = await res.json();

            if (json.success) {
                document.getElementById('previewAvatar').src = json.data.avatar;
                alert('Foto profil berhasil diperbarui!');
            } else {
                alert(json.message || 'Gagal mengunggah foto profil.');
            }
        } catch (e) {
            console.error(e);
            alert('Terjadi kesalahan saat mengunggah foto.');
        }
        event.target.value = '';
    }

    async function saveProfile() {
        const payload = {
            nama: document.getElementById('inputName').value,
            bio: document.getElementById('inputBio').value,
            link_github: document.getElementById('inputGithub').value,
            link_linkedin: document.getElementById('inputLinkedin').value,
            link_instagram: document.getElementById('inputInstagram').value,
            link_tiktok: document.getElementById('inputTiktok').value,
            link_facebook: document.getElementById('inputFacebook').value,
            link_x: document.getElementById('inputX').value,
            link_youtube: document.getElementById('inputYoutube').value,
        };

        const res = await apiProfil('POST', payload);
        if (res.success) {
            alert('Profil, bio, dan tautan sosial media berhasil disimpan!');
            loadProfileData();
        } else {
            alert(res.message || 'Gagal menyimpan profil.');
        }
    }

    window.onload = loadProfileData;

</script>
<?php include 'includes/footer.php'; ?>
