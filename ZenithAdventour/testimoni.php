<?php
require_once __DIR__ . '/includes/session.php';

$error = '';
$success = '';

// Proses submit testimoni (harus login & booking-nya sudah selesai keberangkatan & belum pernah testimoni)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_logged_in()) {
        header('Location: login.php?redirect=testimoni.php');
        exit;
    }

    $booking_id = (int)($_POST['booking_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 5);
    $comment = trim($_POST['comment'] ?? '');
    $rating = max(1, min(5, $rating));

    if ($comment === '') {
        $error = 'Komentar testimoni tidak boleh kosong.';
    } else {
        // Pastikan booking ini benar milik user, sudah dikonfirmasi, pemberangkatan selesai, dan belum ada testimoni
        $stmt = mysqli_prepare($koneksi, "SELECT id, destinasi FROM bookings
            WHERE id = ? AND user_id = ? AND status = 'dikonfirmasi' AND status_keberangkatan = 'selesai'
            AND NOT EXISTS (SELECT 1 FROM testimonials t WHERE t.booking_id = bookings.id)");
        mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        $booking = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$booking) {
            $error = 'Booking tidak ditemukan atau Anda belum berhak menulis testimoni untuk booking ini.';
        } else {
            $ins = mysqli_prepare($koneksi, 'INSERT INTO testimonials (booking_id, user_id, destinasi, rating, comment) VALUES (?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($ins, 'iisis', $booking_id, $_SESSION['user_id'], $booking['destinasi'], $rating, $comment);
            if (mysqli_stmt_execute($ins)) {
                $success = 'Terima kasih! Testimoni Anda berhasil diterbitkan.';
            } else {
                $error = 'Testimoni untuk booking ini sudah pernah dikirim sebelumnya.';
            }
            mysqli_stmt_close($ins);
        }
    }
}

// Booking milik user yang berhak menulis testimoni (pemberangkatan selesai & belum ada testimoni)
$eligible_bookings = [];
if (is_logged_in()) {
    $stmt = mysqli_prepare($koneksi, "SELECT b.id, b.kode_booking, b.destinasi FROM bookings b
        WHERE b.user_id = ? AND b.status = 'dikonfirmasi' AND b.status_keberangkatan = 'selesai'
        AND NOT EXISTS (SELECT 1 FROM testimonials t WHERE t.booking_id = b.id)
        ORDER BY b.created_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $eligible_bookings = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}

// Semua testimoni yang sudah terbit (tampil ke siapa saja, termasuk tamu)
$all_testimonials = mysqli_query($koneksi, "SELECT t.*, u.name FROM testimonials t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 30");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Testimoni - Zenith Tour & Travel</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
body { background:#fcfbf7; color:#333; }
.page-header { background:#1a2f27; color:#fff; text-align:center; padding:70px 20px 60px; }
.page-header .sub { color:#c5a880; font-size:13px; letter-spacing:1px; text-transform:uppercase; margin-bottom:10px; }
.page-header h1 { font-family:'Playfair Display',serif; font-size:32px; margin-bottom:10px; }
.page-header p { color:rgba(255,255,255,0.7); font-size:14px; max-width:500px; margin:0 auto; }
.wrap { max-width:1000px; margin:0 auto; padding:50px 20px; }
.top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
.top-bar a { color:#1a2f27; font-size:14px; text-decoration:none; }

.alert { padding:12px 16px; border-radius:8px; font-size:13px; margin-bottom:20px; }
.alert-error { background:#fdeaea; color:#a33; }
.alert-success { background:#e7f5ec; color:#237040; }

.card { background:#fff; padding:28px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.05); margin-bottom:30px; }
.card h3 { font-family:'Playfair Display',serif; color:#1a2f27; margin-bottom:15px; }

.eligible-box { border:1px dashed #c5a880; border-radius:10px; padding:16px; margin-bottom:14px; }
.eligible-box .info { font-size:13px; color:#555; margin-bottom:10px; }
.star-select { display:flex; gap:6px; font-size:22px; color:#ddd; cursor:pointer; margin-bottom:12px; }
.star-select i.active { color:#f0b429; }
textarea { width:100%; padding:12px 14px; border:1px solid #e5e0d8; border-radius:8px; font-size:14px; font-family:inherit; resize:vertical; }
button[type=submit] { margin-top:10px; padding:11px 24px; background:#1a2f27; color:#fff; border:none; border-radius:8px; font-size:13px; cursor:pointer; }
button[type=submit]:hover { background:#c5a880; }

.locked-box { text-align:center; padding:30px; color:#888; font-size:13px; }
.locked-box i { font-size:28px; color:#c5a880; margin-bottom:12px; display:block; }

.testi-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px,1fr)); gap:20px; }
.testi-card { background:#fff; border-radius:12px; padding:22px; box-shadow:0 4px 15px rgba(0,0,0,0.04); }
.testi-card .stars { color:#f0b429; font-size:13px; margin-bottom:10px; }
.testi-card p.body { font-size:13px; color:#555; line-height:1.7; margin-bottom:14px; font-style:italic; }
.testi-card .who { display:flex; justify-content:space-between; align-items:center; font-size:12px; color:#999; border-top:1px solid #f3eee7; padding-top:10px; }
.testi-card .who strong { color:#1a2f27; }
.empty { text-align:center; color:#999; padding:40px 0; font-size:13px; }
</style>
</head>
<body>

<header class="page-header">
    <p class="sub">Kisah Para Penjelajah</p>
    <h1>Testimoni Pelanggan</h1>
    <p>Cerita dan pengalaman nyata dari sahabat Zenith yang telah menyelesaikan perjalanannya.</p>
</header>

<div class="wrap">
    <div class="top-bar">
        <a href="index.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
        <?php if (is_logged_in()): ?><span style="font-size:13px;color:#666;">Halo, <?= h($_SESSION['user_name']) ?></span><?php endif; ?>
    </div>

    <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>

    <div class="card">
        <h3><i class="fa-solid fa-pen-nib"></i> Tulis Testimoni</h3>

        <?php if (!is_logged_in()): ?>
            <div class="locked-box">
                <i class="fa-solid fa-lock"></i>
                Anda perlu <strong>login</strong> untuk menulis testimoni.<br><br>
                <a href="login.php?redirect=testimoni.php" style="background:#1a2f27;color:#fff;padding:9px 20px;border-radius:20px;font-size:13px;text-decoration:none;margin-right:8px;">Masuk</a>
                <a href="register.php" style="background:#c5a880;color:#fff;padding:9px 20px;border-radius:20px;font-size:13px;text-decoration:none;">Daftar</a>
            </div>
        <?php elseif (empty($eligible_bookings)): ?>
            <div class="locked-box">
                <i class="fa-solid fa-plane-departure"></i>
                Anda belum memiliki perjalanan yang <strong>ditandai selesai</strong> oleh admin.<br>
                Testimoni hanya bisa ditulis setelah pemberangkatan Anda selesai dan dikonfirmasi oleh admin.
            </div>
        <?php else: ?>
            <?php foreach ($eligible_bookings as $b): ?>
            <div class="eligible-box">
                <div class="info"><i class="fa-solid fa-suitcase-rolling"></i> Booking <strong><?= h($b['kode_booking']) ?></strong> — <?= h($b['destinasi']) ?></div>
                <form method="POST">
                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                    <input type="hidden" name="rating" id="rating-<?= $b['id'] ?>" value="5">
                    <div class="star-select" id="stars-<?= $b['id'] ?>">
                        <i class="fa-solid fa-star active" data-val="1"></i>
                        <i class="fa-solid fa-star active" data-val="2"></i>
                        <i class="fa-solid fa-star active" data-val="3"></i>
                        <i class="fa-solid fa-star active" data-val="4"></i>
                        <i class="fa-solid fa-star active" data-val="5"></i>
                    </div>
                    <textarea name="comment" rows="3" required placeholder="Ceritakan pengalaman perjalanan Anda bersama Zenith..."></textarea>
                    <button type="submit">Kirim Testimoni</button>
                </form>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <h3 style="font-family:'Playfair Display',serif;color:#1a2f27;margin-bottom:18px;">Kata Sahabat Zenith</h3>
    <?php if (mysqli_num_rows($all_testimonials) === 0): ?>
        <div class="empty">Belum ada testimoni yang diterbitkan.</div>
    <?php else: ?>
    <div class="testi-grid">
        <?php while ($t = mysqli_fetch_assoc($all_testimonials)): ?>
        <div class="testi-card">
            <div class="stars"><?= str_repeat('★', (int)$t['rating']) . str_repeat('☆', 5 - (int)$t['rating']) ?></div>
            <p class="body">"<?= h($t['comment']) ?>"</p>
            <div class="who">
                <strong><?= h($t['name']) ?></strong>
                <span><?= h($t['destinasi']) ?> · <?= date('M Y', strtotime($t['created_at'])) ?></span>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
document.querySelectorAll('.star-select').forEach(function(group) {
    const bookingId = group.id.replace('stars-', '');
    const hiddenInput = document.getElementById('rating-' + bookingId);
    const stars = group.querySelectorAll('i');
    stars.forEach(function(star) {
        star.addEventListener('click', function() {
            const val = parseInt(star.dataset.val);
            hiddenInput.value = val;
            stars.forEach(function(s) {
                s.classList.toggle('active', parseInt(s.dataset.val) <= val);
            });
        });
    });
});
</script>
</body>
</html>
