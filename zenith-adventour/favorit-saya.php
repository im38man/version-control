<?php
require_once __DIR__ . '/includes/session.php';
require_login('favorit-saya.php');

$stmt = mysqli_prepare($koneksi, 'SELECT destination_slug, destination_title, created_at FROM favorites WHERE user_id = ? ORDER BY created_at DESC');
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$favorites = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Favorit Saya - Zenith Tour & Travel</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Poppins',sans-serif; background:#fcfbf7; color:#333; }
.wrap { max-width:1000px; margin:0 auto; padding:50px 20px; }
.top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
.top-bar a { color:#1a2f27; font-size:14px; }
h1 { font-family:'Playfair Display',serif; color:#1a2f27; margin-bottom:30px; }
.fav-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px,1fr)); gap:22px; }
.fav-card { background:#fff; border-radius:10px; padding:20px; box-shadow:0 4px 15px rgba(0,0,0,0.05); display:flex; justify-content:space-between; align-items:center; }
.fav-card h3 { font-size:16px; color:#1a2f27; margin-bottom:6px; }
.fav-card span { font-size:12px; color:#999; }
.fav-card a.btn-view { background:#1a2f27; color:#fff; padding:8px 14px; border-radius:20px; font-size:12px; }
.fav-remove { background:none; border:none; color:#c5423b; cursor:pointer; font-size:16px; margin-left:10px; }
.empty { text-align:center; padding:60px 0; color:#888; }
</style>
</head>
<body>
<div class="wrap">
    <div class="top-bar">
        <a href="index.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
        <span>Halo, <?= h($_SESSION['user_name']) ?></span>
    </div>
    <h1><i class="fa-solid fa-heart" style="color:#c5423b;"></i> Destinasi Favorit Saya</h1>

    <?php if (empty($favorites)): ?>
        <div class="empty">
            <p>Anda belum menambahkan destinasi favorit.</p>
            <p><a href="semua-destinasi.php" style="color:#c5a880;">Jelajahi destinasi</a> lalu klik tombol "Tambah ke Favorit".</p>
        </div>
    <?php else: ?>
        <div class="fav-grid">
        <?php foreach ($favorites as $fav): ?>
            <div class="fav-card" id="fav-<?= h($fav['destination_slug']) ?>">
                <div>
                    <h3><?= h($fav['destination_title']) ?></h3>
                    <span>Ditambahkan <?= date('d M Y', strtotime($fav['created_at'])) ?></span>
                </div>
                <div style="display:flex;align-items:center;">
                    <a class="btn-view" href="paket-<?= h($fav['destination_slug']) ?>.php">Lihat</a>
                    <button class="fav-remove" onclick="removeFav('<?= h($fav['destination_slug']) ?>', this)" title="Hapus dari favorit"><i class="fa-solid fa-trash"></i></button>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<script>
function removeFav(slug, btn) {
    fetch('favorite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'slug=' + encodeURIComponent(slug) + '&title=x'
    }).then(r => r.json()).then(data => {
        if (data.success) {
            document.getElementById('fav-' + slug).remove();
        }
    });
}
</script>
</body>
</html>
