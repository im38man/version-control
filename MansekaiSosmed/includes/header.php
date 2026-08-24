<?php
// Variabel yang bisa di-set sebelum include file ini:
// $pageTitle   -> judul tab browser
// $activePage  -> nama file aktif untuk highlight menu, misal "materi.php"
if (!isset($pageTitle)) $pageTitle = "Mansekai Study";
if (!isset($activePage)) $activePage = "";

function menuActive($file, $activePage) {
    return $file === $activePage ? 'class="active"' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css?v=b3323308">
</head>
<body>
    <aside>
        <div>
            <div class="sidebar-brand"><i class="fa-solid fa-graduation-cap"></i> Mansekai Study</div>
            <ul class="sidebar-menu">
                <li <?= menuActive('index.php', $activePage) ?>><a href="index.php"><i class="fa-solid fa-house"></i> <span class="menu-label">Home</span></a></li>
                <li <?= menuActive('materi.php', $activePage) ?>><a href="materi.php"><i class="fa-solid fa-book-open"></i> <span class="menu-label">Materi</span></a></li>
                <li <?= menuActive('notepad.php', $activePage) ?>><a href="notepad.php"><i class="fa-solid fa-note-sticky"></i> <span class="menu-label">Notepad</span></a></li>
                <li <?= menuActive('aruskas.php', $activePage) ?>><a href="aruskas.php"><i class="fa-solid fa-wallet"></i> <span class="menu-label">Arus Kas</span></a></li>
                <li <?= menuActive('upload.php', $activePage) ?>><a href="upload.php"><i class="fa-solid fa-file-arrow-up"></i> <span class="menu-label">Upload Dokumen</span></a></li>
                <li <?= menuActive('pertemanan.php', $activePage) ?>><a href="pertemanan.php"><i class="fa-solid fa-user-group"></i> <span class="menu-label">Pertemanan</span></a></li>
                <li <?= menuActive('pesan.php', $activePage) ?>>
                    <a href="pesan.php">
                        <span class="menu-icon-wrap">
                            <i class="fa-solid fa-comment-dots"></i>
                            <span id="badgeChatUnread" class="menu-notif-badge" style="display:none;">0</span>
                        </span>
                        <span class="menu-label">Pesan</span>
                    </a>
                </li>
                <li <?= menuActive('pengaturan.php', $activePage) ?>><a href="pengaturan.php"><i class="fa-solid fa-gear"></i> <span class="menu-label">Pengaturan</span></a></li>
                <?php if (isAdmin()): ?>
                <li <?= menuActive('admin.php', $activePage) ?>><a href="admin.php"><i class="fa-solid fa-book-open-reader"></i> <span class="menu-label">Kelola Materi</span></a></li>
                <li <?= menuActive('admin-user.php', $activePage) ?>><a href="admin-user.php"><i class="fa-solid fa-user-shield"></i> <span class="menu-label">Kelola User</span></a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="sidebar-footer">
            <span>Login: <?= htmlspecialchars($_SESSION['nama'] ?? '') ?> (<?= htmlspecialchars($_SESSION['role'] ?? '') ?>)</span><br>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span></a>
        </div>
    </aside>
    <main>