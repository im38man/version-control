<?php
require 'includes/auth.php';
requireLogin();

require 'config/koneksi.php';

$pageTitle  = "Lihat Profil - Mansekai";
$activePage = "pertemanan.php";

$targetId = (int) ($_GET['id'] ?? 0);
$myId     = (int) $_SESSION['user_id'];
$target   = null;
$errorMsg = "";

if ($targetId <= 0) {
    $errorMsg = "ID user tidak valid.";
} else {
    $stmt = mysqli_prepare($koneksi, "
        SELECT u.id, u.nama, u.username, p.avatar, p.bio,
               p.link_github, p.link_linkedin, p.link_instagram,
               p.link_tiktok, p.link_facebook, p.link_x, p.link_youtube
        FROM users u
        LEFT JOIN profil p ON p.user_id = u.id
        WHERE u.id = ?
    ");
    mysqli_stmt_bind_param($stmt, "i", $targetId);
    mysqli_stmt_execute($stmt);
    $target = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$target) {
        $errorMsg = "User dengan ID #$targetId tidak ditemukan.";
    } else {
        // Hitung jumlah mengikuti & pengikut punya user yang dilihat
        $stmtA = mysqli_prepare($koneksi, "SELECT COUNT(*) AS jumlah FROM follows WHERE follower_id = ?");
        mysqli_stmt_bind_param($stmtA, "i", $targetId);
        mysqli_stmt_execute($stmtA);
        $target['jumlah_mengikuti'] = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtA))['jumlah'];

        $stmtB = mysqli_prepare($koneksi, "SELECT COUNT(*) AS jumlah FROM follows WHERE following_id = ?");
        mysqli_stmt_bind_param($stmtB, "i", $targetId);
        mysqli_stmt_execute($stmtB);
        $target['jumlah_pengikut'] = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtB))['jumlah'];

        // Cek apakah saya (yang login) sudah follow user ini
        $stmtC = mysqli_prepare($koneksi, "SELECT 1 FROM follows WHERE follower_id = ? AND following_id = ?");
        mysqli_stmt_bind_param($stmtC, "ii", $myId, $targetId);
        mysqli_stmt_execute($stmtC);
        $target['is_following'] = (bool) mysqli_fetch_assoc(mysqli_stmt_get_result($stmtC));
    }
}

$avatarDefault = 'img.magnific.com/premium-vector/user-profile-icon-circle_1256048-12499.jpg?auto=format&fit=crop&w=200&q=80';

$daftarSosmed = [
    'link_github'    => ['label' => 'GitHub',    'icon' => 'fa-brands fa-github'],
    'link_linkedin'  => ['label' => 'LinkedIn',  'icon' => 'fa-brands fa-linkedin'],
    'link_instagram' => ['label' => 'Instagram', 'icon' => 'fa-brands fa-instagram'],
    'link_tiktok'    => ['label' => 'TikTok',    'icon' => 'fa-brands fa-tiktok'],
    'link_facebook'  => ['label' => 'Facebook',  'icon' => 'fa-brands fa-facebook'],
    'link_x'         => ['label' => 'X',         'icon' => 'fa-brands fa-twitter'],
    'link_youtube'   => ['label' => 'YouTube',   'icon' => 'fa-brands fa-youtube'],
];

include 'includes/header.php';
?>
<style>
/* Style tambahan khusus halaman ini (di luar assets/style.css) */

.profil-lihat-wrap { display: flex; justify-content: center; }
.profil-lihat-card { background-color: #11131d; color: #fff; border-radius: 16px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-top: 20px; width: 100%; max-width: 550px; text-align: center; }
.profil-lihat-card img { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent-green); margin-bottom: 15px; }
.profil-lihat-card h2 { font-size: 1.3rem; margin-bottom: 2px; }
.profil-lihat-card .username-id { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 14px; }
.profil-lihat-card .bio { font-size: 0.9rem; color: #ddd; margin-bottom: 18px; }

.profil-lihat-stats { display: flex; justify-content: center; gap: 30px; margin-bottom: 20px; }
.profil-lihat-stats div { text-align: center; }
.profil-lihat-stats b { display: block; font-size: 1.1rem; }
.profil-lihat-stats span { font-size: 0.78rem; color: var(--text-muted); }

/* Diubah menjadi warna putih dan hover menjadi accent-green */
.profil-lihat-sosmed { display: flex; justify-content: center; flex-wrap: wrap; gap: 12px; margin-bottom: 22px; }
.profil-lihat-sosmed a { color: #fff; font-size: 1.3rem; text-decoration: none; transition: color 0.2s; }
.profil-lihat-sosmed a:hover { color: var(--accent-green); }

.btn-follow-besar { border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; cursor: pointer; width: 100%; transition: 0.2s; }
.btn-follow-besar.follow { background-color: var(--accent-green); color: #000; }
.btn-follow-besar.follow:hover { background-color: var(--accent-green-dark); color: #fff; }
.btn-follow-besar.unfollow { background-color: #333; color: #fff; }
.btn-follow-besar.unfollow:hover { background-color: #ff4d4d; }
</style>

<div class="header-title">
    <h1>Lihat Profil</h1>
    <p><a href="pertemanan.php" style="color: var(--accent-green-dark); text-decoration: none;"><i class="fa-solid fa-arrow-left"></i> Kembali ke Pertemanan</a></p>
</div>

<?php if ($errorMsg): ?>
    <div class="alert alert-error" style="max-width: 550px; margin: 0 auto;"><?= htmlspecialchars($errorMsg) ?></div>
<?php else: ?>
<div class="profil-lihat-wrap">
    <div class="profil-lihat-card">
        <img src="<?= htmlspecialchars($target['avatar'] ?: $avatarDefault) ?>" alt="Avatar">
        <h2><?= htmlspecialchars($target['nama']) ?></h2>
        <div class="username-id">@<?= htmlspecialchars($target['username']) ?> &middot; ID #<?= (int) $target['id'] ?></div>

        <?php if (!empty($target['bio'])): ?>
            <div class="bio"><?= htmlspecialchars($target['bio']) ?></div>
        <?php endif; ?>

        <div class="profil-lihat-stats">
            <div><b><?= (int) $target['jumlah_mengikuti'] ?></b><span>Mengikuti</span></div>
            <div><b><?= (int) $target['jumlah_pengikut'] ?></b><span>Pengikut</span></div>
        </div>

        <?php
        $adaSosmed = false;
        foreach ($daftarSosmed as $key => $info) { if (!empty($target[$key])) $adaSosmed = true; }
        if ($adaSosmed):
        ?>
        <div class="profil-lihat-sosmed">
            <?php foreach ($daftarSosmed as $key => $info): if (!empty($target[$key])): ?>
                <a href="<?= htmlspecialchars($target[$key]) ?>" target="_blank" rel="noopener" title="<?= htmlspecialchars($info['label']) ?>"><i class="<?= $info['icon'] ?>"></i></a>
            <?php endif; endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($target['id'] === $myId): ?>
            <p style="font-size: 0.8rem; color: var(--text-muted);">Ini profil kamu sendiri. <a href="profil.php" style="color: var(--accent-green);">Edit di sini</a>.</p>
        <?php else: ?>
            <div style="display: flex; gap: 10px;">
                <button id="btnFollowToggle"
                    class="btn-follow-besar <?= $target['is_following'] ? 'unfollow' : 'follow' ?>"
                    data-following="<?= $target['is_following'] ? '1' : '0' ?>"
                    onclick="toggleFollow(<?= (int) $target['id'] ?>)">
                    <i class="fa-solid <?= $target['is_following'] ? 'fa-user-minus' : 'fa-user-plus' ?>"></i>
                    <span><?= $target['is_following'] ? 'Unfollow' : 'Follow' ?></span>
                </button>
                <a href="pesan.php?user_id=<?= (int) $target['id'] ?>" class="btn-follow-besar" style="background-color: #333; color: #fff; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fa-solid fa-comment-dots"></i> <span>Kirim Pesan</span>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    async function toggleFollow(userId) {
        const btn = document.getElementById('btnFollowToggle');
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
                btn.className = 'btn-follow-besar ' + (kini ? 'unfollow' : 'follow');
                btn.innerHTML = `<i class="fa-solid ${kini ? 'fa-user-minus' : 'fa-user-plus'}"></i> <span>${kini ? 'Unfollow' : 'Follow'}</span>`;
            } else {
                alert(json.message || 'Gagal memproses permintaan.');
            }
        } catch (e) {
            alert('Terjadi kesalahan, coba lagi.');
        }
        btn.disabled = false;
    }
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>