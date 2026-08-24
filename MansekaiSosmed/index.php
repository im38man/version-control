<?php
require 'includes/auth.php';
requireLogin();

$pageTitle  = "Dashboard Materi Pembelajaran - Mansekai";
$activePage = "index.php";

require 'config/koneksi.php';

include 'includes/header.php';

// ================= DASHBOARD ADMIN (terpisah dari dashboard user) =================
if (isAdmin()):
    $totalMateri  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM materi"))['total'];
    $totalUser    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users WHERE role='user'"))['total'];
    $totalPending = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pengajuan_materi WHERE status='pending'"))['total'];
    $pengajuanTerbaru = mysqli_query($koneksi, "
        SELECT p.status, p.created_at, m.judul AS judul_materi, u.nama AS nama_user
        FROM pengajuan_materi p
        JOIN materi m ON p.materi_id = m.id
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC LIMIT 5
    ");
    $userTerbaru = mysqli_query($koneksi, "
        SELECT u.id, u.nama, u.username, u.created_at, p.avatar
        FROM users u
        LEFT JOIN profil p ON p.user_id = u.id
        WHERE u.role = 'user'
        ORDER BY u.created_at DESC LIMIT 5
    ");
    $avatarDefaultAdmin = 'img.magnific.com/premium-vector/user-profile-icon-circle_1256048-12499.jpg?auto=format&fit=crop&w=200&q=80';
?>
<div class="header-title">
    <h1>Dashboard Admin</h1>
    <p>Ringkasan aktivitas materi dan pengajuan akses dari user.</p>
</div>

<style>
.dashadmin-user-row { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
.dashadmin-user-row:last-child { border-bottom: none; }
.dashadmin-user-row img { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.dashadmin-user-row .info { flex-grow: 1; min-width: 0; }
.dashadmin-user-row .info b { display: block; font-size: 0.88rem; }
.dashadmin-user-row .info span { display: block; font-size: 0.76rem; color: var(--text-muted); }
.dashadmin-user-row .btn-lihat-profil { flex-shrink: 0; border: none; background-color: #f0f0f0; color: var(--text-dark); padding: 7px 14px; border-radius: 8px; font-size: 0.78rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: 0.2s; }
.dashadmin-user-row .btn-lihat-profil:hover { background-color: #e0e0e0; }
</style>

<div class="grid-2">
    <div class="card">
        <h3>Ringkasan</h3>
        <ul class="module-list">
            <li class="module-item"><span><i class="fa-solid fa-book"></i> Total Materi</span><b><?= $totalMateri ?></b></li>
            <li class="module-item"><span><i class="fa-solid fa-users"></i> Total User</span><b><?= $totalUser ?></b></li>
            <li class="module-item"><span><i class="fa-solid fa-hourglass-half"></i> Pengajuan Pending</span><b><?= $totalPending ?></b></li>
        </ul>
        <a href="admin.php" class="btn-primary" style="margin-top:16px; text-decoration:none;"><i class="fa-solid fa-user-shield"></i> Kelola Materi & Pengajuan</a>
    </div>

    <div class="card">
        <h3>Pengajuan Terbaru</h3>
        <ul class="module-list">
            <?php if (mysqli_num_rows($pengajuanTerbaru) === 0): ?>
                <li class="module-item"><span>Belum ada pengajuan masuk.</span></li>
            <?php endif; ?>
            <?php while ($p = mysqli_fetch_assoc($pengajuanTerbaru)): ?>
                <?php
                    $cls = $p['status'] === 'approved' ? 'selesai' : ($p['status'] === 'rejected' ? 'locked' : 'proses');
                    $label = ['approved' => 'Disetujui', 'rejected' => 'Ditolak', 'pending' => 'Menunggu'][$p['status']];
                ?>
                <li class="module-item">
                    <span><?= htmlspecialchars($p['nama_user']) ?> — <?= htmlspecialchars($p['judul_materi']) ?></span>
                    <span class="badge <?= $cls ?>"><?= $label ?></span>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>

    <div class="card">
        <h3>User Terbaru</h3>
        <?php if (mysqli_num_rows($userTerbaru) === 0): ?>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 10px;">Belum ada user yang daftar.</p>
        <?php endif; ?>
        <?php while ($u = mysqli_fetch_assoc($userTerbaru)): ?>
            <div class="dashadmin-user-row">
                <img src="<?= htmlspecialchars($u['avatar'] ?: $avatarDefaultAdmin) ?>" alt="<?= htmlspecialchars($u['nama']) ?>">
                <div class="info">
                    <b><?= htmlspecialchars($u['nama']) ?></b>
                    <span>@<?= htmlspecialchars($u['username']) ?> &middot; gabung <?= date('d M Y', strtotime($u['created_at'])) ?></span>
                </div>
                <a class="btn-lihat-profil" href="lihat-profil.php?id=<?= (int) $u['id'] ?>"><i class="fa-solid fa-eye"></i> Lihat Profil</a>
            </div>
        <?php endwhile; ?>
        <a href="admin-user.php" class="btn-secondary" style="margin-top:16px; text-decoration:none; display:inline-block;"><i class="fa-solid fa-user-group"></i> Kelola Semua User</a>
    </div>
</div>
<?php
else:
// ================= DASHBOARD USER (desain personal asli kamu) =================
?>
<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        :root {
            --bg-sidebar: #0f111a;
            --bg-main: #f4ecf0;
            --accent-green: #00f2c3;
            --accent-green-dark: #00c49f;
            --text-dark: #2d3142;
            --text-muted: #7d8597;
            --card-bg: #ffffff;
            --sidebar-hover: #1c2130;
        }

        body {
            display: flex;
            background-color: var(--bg-main);
            color: var(--text-dark);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* --- SIDEBAR DESKTOP --- */
        aside {
            width: 260px;
            background-color: var(--bg-sidebar);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            padding: 20px 0;
            z-index: 100;
            transition: all 0.3s ease;
        }

        .sidebar-brand {
            padding: 0 24px 20px 24px;
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--accent-green);
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 12px;
            flex-grow: 1;
            overflow-y: auto;
        }

        .sidebar-menu li {
            margin-bottom: 6px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.95rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar-menu a:hover, .sidebar-menu li.active a {
            background-color: var(--sidebar-hover);
            color: #fff;
        }

        .sidebar-menu li.active a {
            border-left: 4px solid var(--accent-green);
        }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .mobile-logout-link {
            display: none !important; 
        }

        /* --- MAIN CONTENT --- */
        main {
            margin-left: 260px;
            flex-grow: 1;
            padding: 30px;
            background: linear-gradient(135deg, #f9f1f5 0%, #e8e2eb 100%);
            min-height: 100vh;
            width: calc(100% - 260px);
            transition: all 0.3s ease;
        }

        .header-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .header-title h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .header-title p {
            font-size: 0.9rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }

        /* --- DASHBOARD GRID --- */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-bottom: 30px;
        }

        .card {
            background-color: var(--card-bg);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 100%;
        }

        .card:hover {
            transform: translateY(-4px);
        }

        /* Kartu Profil / Welcome */
        .welcome-card {
            background: #11131d;
            color: #fff;
            text-align: center;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .btn-edit-profile {
            position: absolute;
            top: 16px;
            right: 16px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--accent-green);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }

        .btn-edit-profile:hover {
            background: var(--accent-green);
            color: #000;
        }

        .welcome-card img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--accent-green);
            margin-bottom: 12px;
        }

        .welcome-card h3 {
            font-size: 1.2rem;
            margin-bottom: 4px;
        }

        .welcome-card p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 16px;
        }

        .social-icons {
            display: flex;
            gap: 14px;
            font-size: 1.1rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .social-icons a {
            color: #fff;
            transition: color 0.2s;
            display: none;
        }

        .social-icons a:hover {
            color: var(--accent-green);
        }

        /* Mengikuti / Pengikut di kartu profil dashboard */
        .welcome-stats {
            display: flex;
            gap: 10px;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid rgba(255,255,255,0.1);
            width: 100%;
        }

        .welcome-stats .stat-mini {
            flex: 1;
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
            padding: 4px;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .welcome-stats .stat-mini:hover {
            background: rgba(255,255,255,0.06);
        }

        .welcome-stats .stat-mini b {
            display: block;
            font-size: 1rem;
        }

        .welcome-stats .stat-mini span {
            font-size: 0.72rem;
            color: var(--text-muted);
        }

        /* Modal daftar following / followers (dipakai dari kartu profil dashboard) */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            align-items: flex-start;
            justify-content: center;
            padding: 60px 16px;
        }
        .modal-overlay.aktif { display: flex; }
        .modal-box {
            background-color: var(--card-bg);
            border-radius: 16px;
            padding: 22px;
            width: 100%;
            max-width: 480px;
            max-height: 75vh;
            display: flex;
            flex-direction: column;
        }
        .modal-box .modal-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .modal-box .modal-head h3 { font-size: 1.05rem; font-weight: 700; margin: 0; color: var(--text-dark); }
        .modal-box .modal-close { background: none; border: none; font-size: 1.1rem; cursor: pointer; color: var(--text-muted); }
        .modal-box .modal-close:hover { color: var(--danger); }
        .modal-box .modal-body { overflow-y: auto; flex: 1; }

        .user-list { margin-top: 4px; display: flex; flex-direction: column; gap: 10px; }
        .user-row {
            display: flex; align-items: center; gap: 12px; background-color: #f7f7f7;
            border-radius: 12px; padding: 12px 16px;
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

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .winstreak-card-light {
            background-color: var(--card-bg);
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .streak-count {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--accent-green-dark);
            margin: 10px 0;
        }

        .btn-streak {
            background-color: var(--text-dark);
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.85rem;
            width: 100%;
            transition: all 0.2s;
            margin-top: 10px;
        }

        .btn-streak:hover:not(:disabled) {
            background-color: #000;
        }

        .btn-streak:disabled {
            background-color: #e0e0e0;
            color: #888;
            cursor: not-allowed;
        }

        .progress-container {
            max-height: 180px;
            overflow-y: auto;
            margin-bottom: 12px;
        }

        .progress-item {
            margin-bottom: 14px;
            background: #fafafa;
            padding: 10px;
            border-radius: 8px;
            border-left: 4px solid var(--accent-green-dark);
        }

        .progress-info {
            display: flex;
            justify-content: space-between;
            font-size: 0.82rem;
            margin-bottom: 6px;
            font-weight: 500;
            gap: 10px;
        }

        .progress-bar-bg {
            width: 100%;
            height: 8px;
            background-color: #eee;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background-color: var(--accent-green-dark);
            border-radius: 4px;
        }

        .input-group-mini {
            display: flex;
            gap: 6px;
            margin-bottom: 12px;
        }

        .input-group-mini input, .input-group-mini select {
            flex-grow: 1;
            padding: 6px 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            outline: none;
            font-size: 0.75rem;
            background: #fff;
            min-width: 0;
        }

        .input-group-mini button {
            background-color: var(--accent-green-dark);
            color: #fff;
            border: none;
            padding: 0 10px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.75rem;
            white-space: nowrap;
        }

        .module-list {
            list-style: none;
            max-height: 160px;
            overflow-y: auto;
        }

        .module-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.85rem;
            gap: 8px;
        }

        .module-item span {
            display: flex;
            align-items: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .module-item span i {
            color: var(--accent-green-dark);
            margin-right: 6px;
            flex-shrink: 0;
        }

        .mod-title {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .badge {
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-block;
            white-space: nowrap;
        }
        .badge.selesai { background-color: rgba(0, 242, 195, 0.15); color: #00876b; }
        .badge.proses { background-color: rgba(255, 193, 7, 0.15); color: #b78103; }
        .badge.locked { background-color: rgba(108, 117, 125, 0.15); color: #495057; }

        .todo-input-row {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .todo-input-row input {
            flex-grow: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            outline: none;
            font-size: 0.85rem;
            min-width: 0;
        }

        .todo-input-row button {
            background-color: var(--accent-green-dark);
            color: #fff;
            border: none;
            padding: 0 16px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.85rem;
            white-space: nowrap;
        }

        .task-list {
            list-style: none;
            max-height: 160px;
            overflow-y: auto;
        }

        .task-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            background: #fafafa;
            border-radius: 8px;
            margin-bottom: 8px;
            font-size: 0.85rem;
            border-left: 4px solid var(--accent-green-dark);
            gap: 10px;
        }

        .task-text {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .task-item.completed {
            opacity: 0.6;
            text-decoration: line-through;
            border-left-color: #ccc;
        }

        .action-icons {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-shrink: 0;
        }

        .action-icons i {
            cursor: pointer;
            color: var(--text-muted);
            transition: color 0.2s;
            font-size: 0.85rem;
        }

        .action-icons i.fa-pen-to-square:hover { color: #007bff; }
        .action-icons i.fa-trash:hover { color: #ff4d4d; }
        .action-icons i.fa-check:hover { color: var(--accent-green-dark); }

        .timer-card {
            background: #11131d;
            color: #fff;
            text-align: center;
        }

        .timer-display {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--accent-green);
            margin: 10px 0;
            letter-spacing: 2px;
        }

        .timer-btns {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-timer {
            padding: 8px 18px;
            border-radius: 6px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.85rem;
        }

        .btn-start { background-color: var(--accent-green); color: #000; }
        .btn-reset { background-color: #2c3247; color: #fff; }

        .schedule-list {
            list-style: none;
            max-height: 160px;
            overflow-y: auto;
        }

        .schedule-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.85rem;
            gap: 10px;
        }

        .sched-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
            max-width: 140px;
        }

        .schedule-time {
            font-weight: 600;
            color: var(--accent-green-dark);
            font-size: 0.75rem;
            background: rgba(0, 242, 195, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
            white-space: nowrap;
        }

        /* --- MEDIA QUERY RESPONSIF --- */
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 992px) {
            aside {
                width: 75px;
                padding: 10px 0;
            }
            aside .sidebar-brand, 
            aside .sidebar-footer, 
            aside .sidebar-menu .menu-label {
                display: none;
            }
            aside .sidebar-menu a {
                justify-content: center;
                padding: 14px;
                font-size: 1.1rem;
            }
            aside .sidebar-menu li.active a {
                border-left: none;
                border-bottom: 3px solid var(--accent-green);
            }
            main {
                margin-left: 75px;
                width: calc(100% - 75px);
                padding: 20px 15px;
            }
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
			@media (max-width: 600px) {
            body {
                display: block;
                background-color: var(--bg-main);
                width: 100%;
                overflow-x: hidden;
            }
            aside {
                width: 100%;
                height: 56px;
                position: fixed;
                bottom: 0 !important;
                top: auto !important;
                left: 0;
                right: 0;
                flex-direction: row;
                padding: 0;
                padding-bottom: env(safe-area-inset-bottom); /* Supaya aman di atas gestur bar HP */
                border-top: 1px solid rgba(255,255,255,0.1);
                z-index: 999999;
                background-color: var(--bg-sidebar);
            }
            aside .sidebar-brand,
            aside .sidebar-footer {
                display: none;
            }
            .sidebar-menu {
                display: flex;
                flex-direction: row;
                justify-content: space-around;
                padding: 0;
                align-items: center;
                width: 100%;
                margin: 0;
            }
            .sidebar-menu li {
                margin-bottom: 0;
                flex-grow: 1;
                text-align: center;
            }
            .sidebar-menu a {
                padding: 8px 0;
                border-radius: 0;
                justify-content: center;
                font-size: 0.95rem;
            }
            .sidebar-menu li.active a {
                border-left: none;
                border-bottom: 3px solid var(--accent-green);
                background-color: var(--sidebar-hover);
            }
            .mobile-logout-link {
                display: flex !important;
                color: #ff4d4d !important;
            }
            /* Konten Utama & Grid Card dikunci lebarnya pas 100% */
            main {
                margin-left: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 12px !important;
                margin-bottom: 100px !important; 
                box-sizing: border-box !important;
                overflow-x: hidden !important;
            }

            .header-title h1 {
                font-size: 1.2rem !important;
            }
            .header-title p {
                font-size: 0.8rem !important;
            }

            /* Paksa seluruh card grid jadi 1 kolom lurus ke bawah & batasi ukurannya */
            .dashboard-grid {
                display: flex !important;
                flex-direction: column !important;
                gap: 14px !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 0 14px 0 !important;
            }

            .card {
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                padding: 16px !important;
                margin: 0 !important;
                overflow-x: hidden !important;
            }

            /* Elemen di dalam card (input, list, dll) menyesuaikan lebar HP */
            .input-group-mini, .todo-input-row {
                width: 100% !important;
                box-sizing: border-box !important;
            }

            .input-group-mini input, .input-group-mini select, .todo-input-row input {
                width: 100% !important;
                flex: 1 !important;
                min-width: 0 !important;
            }

            .card-title {
                flex-wrap: wrap !important;
                gap: 6px !important;
            }
        }
    </style>

        <script>
            window.addEventListener('DOMContentLoaded', () => {
                const sidebarMenu = document.querySelector('.sidebar-menu');
                if (sidebarMenu) {
                    const logoutLi = document.createElement('li');
                    logoutLi.innerHTML = `<a href="logout.php" class="mobile-logout-link" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>`;
                    sidebarMenu.appendChild(logoutLi);
                }
            });
        </script>

        <div class="header-title">
            <h1>Dashboard Materi Pembelajaran</h1>
            <p>Kelola modul, status belajar, tugas, jadwal, dan target pembelajaran secara interaktif.</p>
        </div>

        <div class="dashboard-grid">
            <!-- 1. Profil & Welcome (Tersinkron dengan Profil) -->
            <div class="card welcome-card">
                <a href="profil.php" class="btn-edit-profile" title="Edit Profil">
                    <i class="fa-solid fa-pen"></i>
                </a>

                <img id="dashAvatar" src="https://img.magnific.com/premium-vector/user-profile-icon-circle_1256048-12499.jpg?auto=format&fit=crop&w=200&q=80" alt="Avatar">
                <h3 id="dashName">Welcome, User</h3>
                <p id="dashBio">your job here</p>
                <div class="social-icons" id="dashSocialContainer">
                    <a id="linkGithub" href="#" target="_blank" title="GitHub"><i class="fa-brands fa-github"></i></a>
                    <a id="linkLinkedin" href="#" target="_blank" title="LinkedIn"><i class="fa-brands fa-linkedin"></i></a>
                    <a id="linkInstagram" href="#" target="_blank" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a id="linkTiktok" href="#" target="_blank" title="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                    <a id="linkFacebook" href="#" target="_blank" title="Facebook"><i class="fa-brands fa-facebook"></i></a>
                    <a id="linkX" href="#" target="_blank" title="X (Twitter)"><i class="fa-brands fa-twitter"></i></a>
                    <a id="linkYoutube" href="#" target="_blank" title="YouTube"><i class="fa-brands fa-youtube"></i></a>
                </div>

                <div class="welcome-stats">
                    <button type="button" class="stat-mini" onclick="bukaModalIkutan('following')">
                        <b id="statMengikutiDash">0</b>
                        <span>Mengikuti</span>
                    </button>
                    <button type="button" class="stat-mini" onclick="bukaModalIkutan('followers')">
                        <b id="statPengikutDash">0</b>
                        <span>Pengikut</span>
                    </button>
                </div>
            </div>

            <!-- Modal daftar following / followers, dibuka dari kartu profil di atas -->
            <div class="modal-overlay" id="modalIkutan">
                <div class="modal-box">
                    <div class="modal-head">
                        <h3 id="modalIkutanJudul">Mengikuti</h3>
                        <button type="button" class="modal-close" onclick="tutupModalIkutan()"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="modal-body user-list" id="modalIkutanIsi"></div>
                </div>
            </div>

            <!-- 3. Todo-Lists Interaktif -->
            <div class="card">
                <div class="card-title">
                    <span>Todo-Lists Materi</span>
                    <i class="fa-solid fa-list-check" style="color: var(--accent-green-dark);"></i>
                </div>
                <div class="todo-input-row">
                    <input type="text" id="taskInput" placeholder="Tambah tugas baru...">
                    <button onclick="addTask()">Add</button>
                </div>
                <ul class="task-list" id="taskList">
                    <!-- Tugas dimuat dinamis dari database -->
                </ul>
            </div>

            <!-- 4. Modul Terbaru -->
            <div class="card">
                <div class="card-title">
                    <span>Modul Terbaru</span>
                    <i class="fa-solid fa-book" style="color: var(--accent-green-dark);"></i>
                </div>
                <div class="input-group-mini">
                    <input type="text" id="moduleInput" placeholder="Nama modul...">
                    <select id="moduleStatusSelect">
                        <option value="Selesai">Selesai</option>
                        <option value="Proses" selected>Proses</option>
                        <option value="Locked">Locked</option>
                    </select>
                    <button onclick="addModule()">Add</button>
                </div>
                <ul class="module-list" id="moduleList">
                    <!-- Modul dimuat dinamis dari database -->
                </ul>
            </div>

            <!-- 5. Focus Timer (Pomodoro) -->
            <div class="card timer-card">
                <div>
                    <div class="card-title" style="color: #fff; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 8px; margin-bottom: 8px;">
                        <span>Focus Timer</span>
                        <i class="fa-solid fa-clock" style="color: var(--accent-green);"></i>
                    </div>
                    <p style="font-size: 0.75rem; color: var(--text-muted);">Sesi Belajar & Latihan</p>
                </div>
                <div class="timer-display" id="timerDisplay">25:00</div>
                <div class="timer-btns">
                    <button class="btn-timer btn-start" onclick="startTimer()">Mulai</button>
                    <button class="btn-timer btn-reset" onclick="resetTimer()">Reset</button>
                </div>
            </div>

            <!-- 6. Jadwal Belajar Harian -->
            <div class="card">
                <div class="card-title">
                    <span>Jadwal Belajar Harian</span>
                    <i class="fa-solid fa-calendar-days" style="color: var(--accent-green-dark);"></i>
                </div>
                <div class="input-group-mini">
                    <input type="text" id="schedName" placeholder="Materi...">
                    <input type="text" id="schedTime" placeholder="Jam" style="max-width: 65px;">
                    <button onclick="addSchedule()">Add</button>
                </div>
                <ul class="schedule-list" id="scheduleList">
                    <!-- Jadwal dimuat dinamis dari database -->
                </ul>
            </div>
            
            <!-- 2. FITUR WINSTREAK BELAJAR (Tema Terang & Ukuran Sama) -->
            <div class="card winstreak-card-light">
                <div>
                    <div class="card-title">
                        <span>Winstreak Belajar</span>
                        <i class="fa-solid fa-fire" style="color: var(--accent-green-dark);"></i>
                    </div>
                    <p style="font-size: 0.75rem; color: var(--text-muted);">Konsistensi Berturut-turut</p>
                </div>
                
                <div class="streak-count" id="streakCountDisplay">0 Hari</div>
                
                <button class="btn-streak" id="winstreakBtn" onclick="claimWinstreak()">Klaim Winstreak Hari Ini</button>
            </div>
        </div>

        <!-- Bagian Bawah: Target Pembelajaran -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-title">
                <span>Pertanggungjawaban & Target Pembelajaran</span>
                <span style="font-size: 0.85rem; color: var(--text-muted);">Semester Aktif</span>
            </div>

            <div class="input-group-mini" style="max-width: 400px; margin-bottom: 16px;">
                <input type="text" id="targetNameInput" placeholder="Nama Target Pembelajaran...">
                <input type="number" id="targetPercentInput" placeholder="%" min="0" max="100" style="max-width: 65px;">
                <button onclick="addTarget()">Add Target</button>
            </div>

            <div class="progress-container" id="targetListContainer">
                <!-- Target dimuat dinamis dari database -->
            </div>
        </div>
    
<script>
        async function apiCall(url, method, body) {
            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: body ? JSON.stringify(body) : undefined
            });
            return res.json();
        }

        async function loadDashboardProfile() {
            const res = await apiCall('api/profil.php', 'GET');
            if (!res.success) return;
            const p = res.data;

            if (p.nama) document.getElementById('dashName').textContent = 'Welcome, ' + p.nama;
            if (p.bio) document.getElementById('dashBio').textContent = p.bio;
            if (p.avatar) document.getElementById('dashAvatar').src = p.avatar;

            const links = {
                'linkGithub': p.link_github,
                'linkLinkedin': p.link_linkedin,
                'linkInstagram': p.link_instagram,
                'linkTiktok': p.link_tiktok,
                'linkFacebook': p.link_facebook,
                'linkX': p.link_x,
                'linkYoutube': p.link_youtube
            };

            for (const [id, url] of Object.entries(links)) {
                const el = document.getElementById(id);
                if (url && url.trim() !== "") {
                    el.href = url;
                    el.style.display = 'inline-block';
                } else {
                    el.style.display = 'none';
                }
            }
        }

        // ==== Mengikuti / Pengikut di kartu profil dashboard ====
        async function muatRingkasanIkutan() {
            const res = await apiCall('api/pertemanan.php?action=ringkasan', 'GET');
            if (!res.success) return;
            document.getElementById('statMengikutiDash').textContent = res.data.mengikuti;
            document.getElementById('statPengikutDash').textContent = res.data.pengikut;
        }

        function baseRowHTMLDash(u, showFollowBtn) {
            const avatar = u.avatar || 'https://img.magnific.com/premium-vector/user-profile-icon-circle_1256048-12499.jpg?auto=format&fit=crop&w=200&q=80';
            const followBtn = showFollowBtn ? `
                <button class="btn-follow-kecil ${u.is_following ? 'unfollow' : 'follow'}"
                    data-userid="${u.id}" data-following="${u.is_following ? '1' : '0'}"
                    onclick="toggleFollowDash(this)">
                    ${u.is_following ? 'Mengikuti' : 'Follow'}
                </button>` : '';
            return `
                <div class="user-row" data-row-userid="${u.id}">
                    <img src="${avatar}" alt="Avatar">
                    <div class="user-info">
                        <a href="lihat-profil.php?id=${u.id}">${escapeHtml(u.nama)}</a>
                        <div class="username">@${escapeHtml(u.username)}</div>
                    </div>
                    ${followBtn}
                </div>`;
        }

        const modalIkutan      = document.getElementById('modalIkutan');
        const modalIkutanJudul = document.getElementById('modalIkutanJudul');
        const modalIkutanIsi   = document.getElementById('modalIkutanIsi');

        async function bukaModalIkutan(jenis) {
            modalIkutanJudul.textContent = jenis === 'following' ? 'Mengikuti' : 'Pengikut';
            modalIkutanIsi.innerHTML = '<div class="empty-state">Memuat...</div>';
            modalIkutan.classList.add('aktif');

            const res = await apiCall('api/pertemanan.php?action=' + jenis, 'GET');
            if (!res.success) { modalIkutanIsi.innerHTML = `<div class="empty-state">${res.message || 'Gagal memuat.'}</div>`; return; }
            if (res.data.length === 0) {
                modalIkutanIsi.innerHTML = `<div class="empty-state">Belum ada ${jenis === 'following' ? 'orang yang kamu ikuti' : 'pengikut'}.</div>`;
                return;
            }
            if (jenis === 'following') {
                modalIkutanIsi.innerHTML = res.data.map(u => baseRowHTMLDash({ ...u, is_following: true }, true)).join('');
            } else {
                modalIkutanIsi.innerHTML = res.data.map(u => baseRowHTMLDash(u, false)).join('');
            }
        }

        function tutupModalIkutan() {
            modalIkutan.classList.remove('aktif');
        }

        modalIkutan.addEventListener('click', (e) => {
            if (e.target === modalIkutan) tutupModalIkutan();
        });

        async function toggleFollowDash(btn) {
            const userId = btn.dataset.userid;
            const sedangMengikuti = btn.dataset.following === '1';
            const action = sedangMengikuti ? 'unfollow' : 'follow';

            btn.disabled = true;
            const res = await apiCall('api/pertemanan.php', 'POST', { action, user_id: userId });
            if (res.success) {
                const kini = !sedangMengikuti;
                btn.dataset.following = kini ? '1' : '0';
                btn.className = 'btn-follow-kecil ' + (kini ? 'unfollow' : 'follow');
                btn.textContent = kini ? 'Mengikuti' : 'Follow';
                muatRingkasanIkutan();

                if (modalIkutanJudul.textContent === 'Mengikuti' && !kini) {
                    const row = btn.closest('.user-row');
                    if (row) row.remove();
                    if (modalIkutanIsi.children.length === 0) {
                        modalIkutanIsi.innerHTML = '<div class="empty-state">Belum ada orang yang kamu ikuti.</div>';
                    }
                }
            } else {
                alert(res.message || 'Gagal memproses permintaan.');
            }
            btn.disabled = false;
        }

        async function checkWinstreakStatus() {
            const res = await apiCall('api/dashboard.php?resource=streak', 'GET');
            if (!res.success) return;

            const streakCount = res.data.streak_count || 0;
            const lastClaimDate = res.data.last_claim_date;
            const today = new Date().toISOString().slice(0, 10);

            const btn = document.getElementById('winstreakBtn');
            const display = document.getElementById('streakCountDisplay');
            display.textContent = streakCount + ' Hari';

            if (lastClaimDate === today) {
                btn.disabled = true;
                btn.textContent = "Winstreak Hari Ini Selesai 🔥";
            } else {
                btn.disabled = false;
                btn.textContent = "Klaim Winstreak Hari Ini";
            }
        }

        async function claimWinstreak() {
            const res = await apiCall('api/dashboard.php?resource=streak', 'POST');
            if (res.success) {
                document.getElementById('streakCountDisplay').textContent = res.data.streak_count + ' Hari';
                alert('Mantap! Winstreak belajar hari ini berhasil ditambahkan 🔥');
            } else {
                alert(res.message || 'Winstreak hari ini sudah diklaim.');
            }
            checkWinstreakStatus();
        }

        window.onload = function() {
            loadDashboardProfile();
            checkWinstreakStatus();
            loadTasks();
            loadModules();
            loadSchedules();
            loadTargets();
            muatRingkasanIkutan();
        };

        async function loadTasks() {
            const res = await apiCall('api/dashboard.php?resource=tasks', 'GET');
            if (!res.success) return;
            const ul = document.getElementById('taskList');
            ul.innerHTML = '';
            res.data.forEach(task => ul.appendChild(buatElemenTask(task)));
        }

        function buatElemenTask(task) {
            const li = document.createElement('li');
            li.className = 'task-item' + (task.selesai ? ' completed' : '');
            li.dataset.id = task.id;
            li.innerHTML = `
                <span class="task-text">${escapeHtml(task.teks)}</span>
                <div class="action-icons">
                    <i class="fa-solid fa-check" onclick="toggleTask(this)" title="Selesai"></i>
                    <i class="fa-solid fa-pen-to-square" onclick="editTask(this)" title="Edit"></i>
                    <i class="fa-solid fa-trash" onclick="deleteTask(this)" title="Hapus"></i>
                </div>
            `;
            return li;
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        async function addTask() {
            const input = document.getElementById('taskInput');
            if(!input.value.trim()) return;
            const res = await apiCall('api/dashboard.php?resource=tasks', 'POST', { teks: input.value });
            if (res.success) {
                document.getElementById('taskList').appendChild(buatElemenTask(res.data));
                input.value = '';
            }
        }

        function toggleTask(element) {
            const taskItem = element.closest('.task-item');
            taskItem.classList.toggle('completed');
            const selesai = taskItem.classList.contains('completed');
            apiCall('api/dashboard.php?resource=tasks', 'PUT', { id: taskItem.dataset.id, selesai });
        }

        function editTask(element) {
            const taskItem = element.closest('.task-item');
            const spanText = taskItem.querySelector('.task-text');
            const newText = prompt("Edit tugas:", spanText.textContent);
            if(newText !== null && newText.trim() !== "") {
                spanText.textContent = newText;
                apiCall('api/dashboard.php?resource=tasks', 'PUT', { id: taskItem.dataset.id, teks: newText });
            }
        }

        function deleteTask(element) {
            const taskItem = element.closest('.task-item');
            apiCall('api/dashboard.php?resource=tasks', 'DELETE', { id: taskItem.dataset.id });
            taskItem.remove();
        }

        async function loadModules() {
            const res = await apiCall('api/dashboard.php?resource=modules', 'GET');
            if (!res.success) return;
            const ul = document.getElementById('moduleList');
            ul.innerHTML = '';
            res.data.forEach(mod => ul.appendChild(buatElemenModule(mod)));
        }

        function buatElemenModule(mod) {
            const badgeClass = mod.status === 'Selesai' ? 'selesai' : (mod.status === 'Locked' ? 'locked' : 'proses');
            const li = document.createElement('li');
            li.className = 'module-item';
            li.dataset.id = mod.id;
            li.innerHTML = `
                <span><i class="fa-solid fa-circle-play"></i> <b class="mod-title">${escapeHtml(mod.nama)}</b></span>
                <div class="action-icons">
                    <span class="badge ${badgeClass}" onclick="toggleModuleStatus(this)" title="Klik untuk ubah status">${mod.status}</span>
                    <i class="fa-solid fa-pen-to-square" onclick="editModule(this)" title="Edit Nama"></i>
                    <i class="fa-solid fa-trash" onclick="deleteModule(this)" title="Hapus"></i>
                </div>
            `;
            return li;
        }

        async function addModule() {
            const input = document.getElementById('moduleInput');
            const statusSelect = document.getElementById('moduleStatusSelect');
            if(!input.value.trim()) return;

            const res = await apiCall('api/dashboard.php?resource=modules', 'POST', { nama: input.value, status: statusSelect.value });
            if (res.success) {
                document.getElementById('moduleList').appendChild(buatElemenModule(res.data));
                input.value = '';
            }
        }

        function editModule(element) {
            const modItem = element.closest('.module-item');
            const modTitle = modItem.querySelector('.mod-title');
            const newTitle = prompt("Edit nama modul:", modTitle.textContent);
            if(newTitle !== null && newTitle.trim() !== "") {
                modTitle.textContent = newTitle;
                apiCall('api/dashboard.php?resource=modules', 'PUT', { id: modItem.dataset.id, nama: newTitle });
            }
        }

        function toggleModuleStatus(badgeElement) {
            const modItem = badgeElement.closest('.module-item');
            let newStatus = 'Selesai';
            if(badgeElement.classList.contains('selesai')) {
                badgeElement.classList.remove('selesai'); badgeElement.classList.add('proses');
                badgeElement.textContent = 'Proses'; newStatus = 'Proses';
            } else if(badgeElement.classList.contains('proses')) {
                badgeElement.classList.remove('proses'); badgeElement.classList.add('locked');
                badgeElement.textContent = 'Locked'; newStatus = 'Locked';
            } else {
                badgeElement.classList.remove('locked'); badgeElement.classList.add('selesai');
                badgeElement.textContent = 'Selesai'; newStatus = 'Selesai';
            }
            apiCall('api/dashboard.php?resource=modules', 'PUT', { id: modItem.dataset.id, status: newStatus });
        }

        function deleteModule(element) {
            const modItem = element.closest('.module-item');
            apiCall('api/dashboard.php?resource=modules', 'DELETE', { id: modItem.dataset.id });
            modItem.remove();
        }

        async function loadSchedules() {
            const res = await apiCall('api/dashboard.php?resource=jadwal', 'GET');
            if (!res.success) return;
            const ul = document.getElementById('scheduleList');
            ul.innerHTML = '';
            res.data.forEach(s => ul.appendChild(buatElemenSchedule(s)));
        }

        function buatElemenSchedule(s) {
            const li = document.createElement('li');
            li.className = 'schedule-item';
            li.dataset.id = s.id;
            li.innerHTML = `
                <div>
                    <span class="sched-name"><b>${escapeHtml(s.nama)}</b></span>
                    <span class="schedule-time sched-time-val">${escapeHtml(s.jam)}</span>
                </div>
                <div class="action-icons">
                    <i class="fa-solid fa-pen-to-square" onclick="editSchedule(this)" title="Edit"></i>
                    <i class="fa-solid fa-trash" onclick="deleteSchedule(this)" title="Hapus"></i>
                </div>
            `;
            return li;
        }

        async function addSchedule() {
            const nameInput = document.getElementById('schedName');
            const timeInput = document.getElementById('schedTime');
            if(!nameInput.value.trim() || !timeInput.value.trim()) return;

            const res = await apiCall('api/dashboard.php?resource=jadwal', 'POST', { nama: nameInput.value, jam: timeInput.value });
            if (res.success) {
                document.getElementById('scheduleList').appendChild(buatElemenSchedule(res.data));
                nameInput.value = '';
                timeInput.value = '';
            }
        }

        function editSchedule(element) {
            const schedItem = element.closest('.schedule-item');
            const schedName = schedItem.querySelector('.sched-name');
            const schedTime = schedItem.querySelector('.sched-time-val');

            const newName = prompt("Edit nama kegiatan:", schedName.textContent);
            const newTime = prompt("Edit waktu kegiatan:", schedTime.textContent);

            if(newName !== null && newName.trim() !== "") schedName.innerHTML = `<b>${escapeHtml(newName)}</b>`;
            if(newTime !== null && newTime.trim() !== "") schedTime.textContent = newTime;

            if ((newName !== null && newName.trim() !== "") || (newTime !== null && newTime.trim() !== "")) {
                apiCall('api/dashboard.php?resource=jadwal', 'PUT', {
                    id: schedItem.dataset.id,
                    nama: schedName.textContent,
                    jam: schedTime.textContent
                });
            }
        }

        function deleteSchedule(element) {
            const schedItem = element.closest('.schedule-item');
            apiCall('api/dashboard.php?resource=jadwal', 'DELETE', { id: schedItem.dataset.id });
            schedItem.remove();
        }

        async function loadTargets() {
            const res = await apiCall('api/dashboard.php?resource=target', 'GET');
            if (!res.success) return;
            const container = document.getElementById('targetListContainer');
            container.innerHTML = '';
            res.data.forEach(t => container.appendChild(buatElemenTarget(t)));
        }

        function buatElemenTarget(t) {
            const div = document.createElement('div');
            div.className = 'progress-item';
            div.dataset.id = t.id;
            div.innerHTML = `
                <div class="progress-info">
                    <span class="target-title">${escapeHtml(t.nama)}</span>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="target-pct">${t.persen}%</span>
                        <div class="action-icons">
                            <i class="fa-solid fa-pen-to-square" onclick="editTarget(this)" title="Edit"></i>
                            <i class="fa-solid fa-trash" onclick="deleteTargetItem(this)" title="Hapus"></i>
                        </div>
                    </div>
                </div>
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" style="width: ${t.persen}%;"></div>
                </div>
            `;
            return div;
        }

        async function addTarget() {
            const nameInput = document.getElementById('targetNameInput');
            const percentInput = document.getElementById('targetPercentInput');
            if(!nameInput.value.trim() || !percentInput.value.trim()) return;

            let pct = parseInt(percentInput.value);
            if(pct < 0) pct = 0;
            if(pct > 100) pct = 100;

            const res = await apiCall('api/dashboard.php?resource=target', 'POST', { nama: nameInput.value, persen: pct });
            if (res.success) {
                document.getElementById('targetListContainer').appendChild(buatElemenTarget(res.data));
                nameInput.value = '';
                percentInput.value = '';
            }
        }

        function editTarget(element) {
            const progressItem = element.closest('.progress-item');
            const targetTitle = progressItem.querySelector('.target-title');
            const targetPct = progressItem.querySelector('.target-pct');
            const progressBar = progressItem.querySelector('.progress-bar-fill');

            const newTitle = prompt("Edit nama target pembelajaran:", targetTitle.textContent);
            const newPctStr = prompt("Edit persentase progress (0-100):", targetPct.textContent.replace('%', ''));

            if(newTitle !== null && newTitle.trim() !== "") {
                targetTitle.textContent = newTitle;
            }

            let newPct = null;
            if(newPctStr !== null && !isNaN(newPctStr)) {
                newPct = parseInt(newPctStr);
                if(newPct < 0) newPct = 0;
                if(newPct > 100) newPct = 100;
                targetPct.textContent = newPct + '%';
                progressBar.style.width = newPct + '%';
            }

            apiCall('api/dashboard.php?resource=target', 'PUT', {
                id: progressItem.dataset.id,
                nama: targetTitle.textContent,
                persen: newPct !== null ? newPct : parseInt(targetPct.textContent)
            });
        }

        function deleteTargetItem(element) {
            const progressItem = element.closest('.progress-item');
            apiCall('api/dashboard.php?resource=target', 'DELETE', { id: progressItem.dataset.id });
            progressItem.remove();
        }

        let timerInterval;
        let timeLeft = 25 * 60;

        function updateTimerDisplay() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('timerDisplay').textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        function startTimer() {
            if(timerInterval) return;
            timerInterval = setInterval(() => {
                if(timeLeft > 0) {
                    timeLeft--;
                    updateTimerDisplay();
                } else {
                    clearInterval(timerInterval);
                    alert('Sesi belajar selesai! Istirahat sejenak.');
                    timerInterval = null;
                }
            }, 1000);
        }

        function resetTimer() {
            clearInterval(timerInterval);
            timerInterval = null;
            timeLeft = 25 * 60;
            updateTimerDisplay();
        }
    </script>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>