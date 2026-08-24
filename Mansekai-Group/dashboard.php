<?php
require_once __DIR__ . '/lib.php';
require_login_page();

$ownerId = (int) current_user_id();
$csrf = csrf_token();

$stmtU = $conn->prepare("SELECT name, username, email, role_title, avatar, recovery_email, recovery_phone, socials FROM users WHERE id = ? LIMIT 1");
$stmtU->bind_param('i', $ownerId);
$stmtU->execute();
$userRow = $stmtU->get_result()->fetch_assoc();
if (!$userRow) { header('Location: logout.php'); exit; }

$bootstrapProfile = [
    'name' => $userRow['name'],
    'role' => $userRow['role_title'] ?: 'Team Member',
    'username' => $userRow['username'],
    'avatar' => $userRow['avatar'] ?: avatar_url($userRow['name']),
    'recoveryEmail' => $userRow['recovery_email'] ?: $userRow['email'],
    'recoveryPhone' => $userRow['recovery_phone'] ?: '',
    'socials' => $userRow['socials'] ? json_decode($userRow['socials'], true) : ['instagram' => '', 'facebook' => '', 'x' => '', 'tiktok' => '', 'youtube' => '', 'github' => '', 'others' => ''],
];

$bootstrap = [
    'profile' => $bootstrapProfile,
    'team' => ud_get($conn, $ownerId, 'team', []),
    'companies' => ud_get($conn, $ownerId, 'companies', []),
    'projects' => ud_get($conn, $ownerId, 'projects', []),
    'tasks' => ud_get($conn, $ownerId, 'tasks', []),
    'schedules' => ud_get($conn, $ownerId, 'schedules', []),
    'activities' => ud_get($conn, $ownerId, 'activities', []),
    'cashflow' => ud_get($conn, $ownerId, 'cashflow', []),
    'threads' => ud_get($conn, $ownerId, 'threads', []),
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard UI - Complete Social & Finance Integration</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            /* Disesuaikan dengan nuansa warna biru gelap keunguan pada logo.png */
            --primary: #3b3086;
            --primary-light: rgba(59, 48, 134, 0.1);
            --bg-color: #f5f6fa;
            --text-dark: #2d3436;
            --text-light: #8e94a8;
            --white: #ffffff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            --dropdown-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            
            --icon-orange: #ff9f43;
            --icon-green: #2ecc71;
            --icon-blue: #3498db;
            --icon-purple: #9b59b6;
            --icon-brown: #a0522d;
            --icon-yellow: #f1c40f;
            --icon-red: #ff4757;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        body {
            background-color: var(--bg-color);
            color: var(--text-dark);
            display: flex;
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        .overlay-backdrop {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.4); z-index: 998;
            opacity: 0; visibility: hidden; transition: 0.3s ease;
        }
        .overlay-backdrop.show { opacity: 1; visibility: visible; }

        /* Layout Grid */
        .dashboard {
            display: grid;
            grid-template-columns: 80px 1fr 350px;
            width: 100%;
            height: 100%;
            padding: 20px;
            gap: 20px;
            transition: 0.3s ease;
        }
        .dashboard.sidebar-hidden { grid-template-columns: 0px 1fr 350px; }

        /* Sidebar Left */
        .sidebar {
            background: var(--white); border-radius: 20px; display: flex; flex-direction: column;
            align-items: center; padding: 30px 0; box-shadow: var(--shadow);
            overflow-y: auto; overflow-x: hidden; transition: 0.3s ease; z-index: 999;
        }
        .sidebar::-webkit-scrollbar { display: none; }
        .dashboard.sidebar-hidden .sidebar { transform: translateX(-100px); opacity: 0; }

        .logo { color: var(--primary); font-size: 24px; margin-bottom: 40px; cursor: pointer; display: flex; justify-content: center; align-items: center; }
        .logo img { width: 36px; height: 36px; object-fit: contain; border-radius: 8px; }
        
        .menu-icons { display: flex; flex-direction: column; gap: 5px; width: 100%; }
        .menu-item {
            width: 100%; padding: 15px 0; display: flex; justify-content: center;
            align-items: center; color: var(--text-light); cursor: pointer;
            transition: all 0.3s ease; position: relative;
        }
        .menu-item i { font-size: 20px; transition: 0.3s; }
        .menu-item:hover { color: var(--primary); background: var(--primary-light); }
        .menu-item.active { color: var(--primary); background: transparent; }
        .menu-item.active::before {
            content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 4px;
            background: var(--primary); border-radius: 0 4px 4px 0;
        }

        /* Main Content */
        .main-content { display: flex; flex-direction: column; overflow-y: auto; overflow-x: visible; padding-right: 5px; position: relative; }
        .main-content::-webkit-scrollbar { display: none; }
        
        header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;
            background: var(--white); padding: 15px 30px; border-radius: 20px; box-shadow: var(--shadow);
            position: relative; z-index: 100;
        }

        .header-left i, .header-right > i { font-size: 20px; color: var(--text-dark); cursor: pointer; transition: color 0.2s; }
        .header-right > i { color: var(--text-light); font-size: 18px; }
        .header-left i:hover, .header-right > i:hover { color: var(--primary); }
        .header-right { display: flex; align-items: center; gap: 15px; position: relative; }
        
        .user-profile { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 500; cursor: pointer; }
        .user-profile img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; }

        /* Language Dropdown Select (Far Right) */
        .lang-dropdown {
            background: var(--bg-color);
            border: 1px solid #e1e4e8;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-dark);
            outline: none;
            cursor: pointer;
            transition: 0.2s;
            font-family: 'Poppins', sans-serif;
        }
        .lang-dropdown:hover { border-color: var(--primary); }

        /* Dropdowns */
        .dropdown-menu {
            position: absolute; top: 50px; right: 0; background: white; border-radius: 15px;
            box-shadow: var(--dropdown-shadow); width: 220px; min-width: 220px; padding: 10px;
            opacity: 0; visibility: hidden; transform: translateY(10px); transition: all 0.3s; z-index: 2000;
        }
        .dropdown-menu.active { opacity: 1; visibility: visible; transform: translateY(0); }
        .dropdown-header { font-size: 12px; font-weight: 600; color: var(--text-light); padding: 10px; border-bottom: 1px solid #eee; margin-bottom: 5px; white-space: nowrap; }
        .dropdown-item { padding: 10px 12px; display: flex; align-items: center; gap: 12px; cursor: pointer; border-radius: 10px; transition: 0.2s; font-size: 13px; white-space: nowrap; }
        .dropdown-item span { display: inline-block !important; visibility: visible !important; opacity: 1 !important; color: inherit; }
        .dropdown-item:hover { background: var(--bg-color); color: var(--primary); }

        /* Unified Projects & Section Headings */
        .projects-section { width: 100%; overflow: visible; }
        .projects-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; background: transparent; padding: 0; box-shadow: none; }
        .projects-header h1, .projects-section > h1 { font-size: 24px; font-weight: 600; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; color: var(--text-dark); background: transparent; }
        
        .badge-count { 
            background: #ff4757; color: white; font-size: 12px; width: 24px; height: 24px; 
            display: inline-flex; justify-content: center; align-items: center; border-radius: 50%; 
            vertical-align: middle; 
        }

        .tabs-container { width: 100%; overflow-x: auto; }
        .tabs-container::-webkit-scrollbar { display: none; }
        .tabs { display: flex; gap: 20px; border-bottom: 2px solid #e1e4e8; padding-bottom: 10px; min-width: max-content; }
        .tab { font-size: 14px; font-weight: 500; color: var(--text-light); cursor: pointer; position: relative; padding-bottom: 10px; margin-bottom: -12px; transition: 0.2s; }
        .tab:hover { color: var(--text-dark); }
        .tab.active { color: var(--primary); border-bottom: 2px solid var(--primary); }
        .tab span { font-size: 10px; background: #e1e4e8; padding: 2px 5px; border-radius: 10px; margin-left: 5px; }

        .actions { display: flex; align-items: center; gap: 15px; }
        .actions i { cursor: pointer; transition: 0.2s; }
        .actions i:hover, .actions i.active-view { color: var(--primary) !important; }

        .btn-create {
            background: var(--primary); color: white; border: none; padding: 10px 20px;
            border-radius: 20px; font-size: 12px; font-weight: 600; cursor: pointer;
            display: flex; align-items: center; gap: 8px; transition: 0.2s; white-space: nowrap;
        }
        .btn-create:hover { opacity: 0.9; transform: translateY(-2px); }

        /* Cards Grid View */
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; padding-bottom: 20px; overflow: visible; transition: all 0.3s ease; }
        
        .card { 
            background: var(--white); border-radius: 20px; padding: 20px; 
            box-shadow: var(--shadow); position: relative; display: flex; 
            flex-direction: column; align-items: center; text-align: center; 
            transition: 0.3s; cursor: pointer; overflow: visible; z-index: 10;
        }
        .card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08); z-index: 15; }
        .card-menu { position: absolute; top: 15px; right: 15px; color: var(--text-light); cursor: pointer; padding: 5px; z-index: 20; }
        .card-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; justify-content: center; align-items: center; font-size: 20px; color: white; margin-bottom: 15px; margin-top: 10px; flex-shrink: 0; }
        
        .card-info { width: 100%; }
        .card-info h3 { font-size: 15px; margin-bottom: 5px; width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .card-info p { font-size: 11px; color: var(--text-light); margin-bottom: 15px; width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        
        .team-avatars { display: flex; justify-content: center; margin-bottom: 20px; min-height: 30px; align-items: center; }
        .team-avatars img { width: 30px; height: 30px; border-radius: 50%; border: 2px solid white; margin-left: -10px; object-fit: cover; }
        .team-avatars img:first-child { margin-left: 0; }
        .team-avatars span { font-size: 11px; color: var(--text-light); font-style: italic; }

        .progress-section { width: 100%; text-align: left; margin-bottom: 15px; }
        .progress-header { display: flex; justify-content: space-between; font-size: 12px; font-weight: 600; margin-bottom: 6px; }
        .progress-bar { width: 100%; height: 6px; background: #e1e4e8; border-radius: 3px; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 3px; transition: width 0.3s ease; }

        .card-footer { width: 100%; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f2f6; padding-top: 15px; }
        .card-stats { display: flex; gap: 12px; color: var(--text-light); font-size: 12px; }
        .card-stats span { cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 4px; }
        .card-stats span:hover { color: var(--primary); }
        .time-left { font-size: 10px; padding: 4px 10px; border-radius: 10px; font-weight: 600; }

        /* List View Overrides */
        .cards-grid.list-view { display: flex; flex-direction: column; gap: 12px; overflow: visible; }
        .cards-grid.list-view .card {
            display: grid;
            grid-template-columns: 45px minmax(160px, 2fr) minmax(140px, 1.2fr) 90px 110px;
            align-items: center;
            text-align: left;
            padding: 12px 25px;
            gap: 20px;
            flex-direction: unset;
            overflow: visible;
        }
        .cards-grid.list-view .card-menu {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            right: 20px;
        }
        .cards-grid.list-view .card-icon { margin: 0; flex-shrink: 0; }
        .cards-grid.list-view .card-info { width: 100%; min-width: 0; }
        .cards-grid.list-view .card-info h3,
        .cards-grid.list-view .card-info p { margin-bottom: 0; }
        .cards-grid.list-view .team-avatars { display: none; }
        .cards-grid.list-view .progress-section { margin-bottom: 0; width: 100%; min-width: 0; }
        .cards-grid.list-view .card-footer { display: contents; }
        .cards-grid.list-view .card-stats { display: flex; gap: 12px; font-size: 12px; color: var(--text-light); justify-content: center; }
        .cards-grid.list-view .time-left { justify-self: start; white-space: nowrap; }

        /* Team Management Table View Styles & Pagination */
        .team-table-container { background: white; border-radius: 15px; padding: 20px; box-shadow: var(--shadow); margin-top: 20px; }
        .team-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; gap: 15px; flex-wrap: wrap; }
        .team-toolbar-left { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; flex: 1; }
        .team-search-input { padding: 8px 15px; border: 1px solid #ddd; border-radius: 10px; outline: none; font-size: 13px; width: 220px; font-family: 'Poppins', sans-serif; }
        .team-search-input:focus { border-color: var(--primary); }

        .team-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
        .team-table th, .team-table td { padding: 12px 15px; border-bottom: 1px solid #eee; }
        .team-table th { color: var(--text-light); font-weight: 600; }
        .team-table img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; }
        .badge-role { padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 600; }
        .badge-team { background: rgba(59, 48, 134, 0.1); color: var(--primary); }
        .badge-client { background: rgba(46, 204, 113, 0.1); color: var(--icon-green); }
        .badge-prospect { background: rgba(255, 159, 67, 0.1); color: var(--icon-orange); }

        .pagination-container { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; font-size: 12px; color: var(--text-light); }
        .pagination-buttons { display: flex; gap: 5px; }
        .page-btn { background: var(--bg-color); border: 1px solid #e1e4e8; padding: 6px 12px; border-radius: 8px; cursor: pointer; font-weight: 600; color: var(--text-dark); transition: 0.2s; }
        .page-btn:hover:not(:disabled) { background: var(--primary); color: white; border-color: var(--primary); }
        .page-btn:disabled { opacity: 0.5; cursor: not-allowed; }

        /* Right Panel */
        .right-panel { background: var(--white); border-radius: 20px; padding: 25px; box-shadow: var(--shadow); overflow-y: auto; transition: 0.3s ease; z-index: 999; }
        .right-panel::-webkit-scrollbar { display: none; }
        .section-title { font-size: 16px; margin-bottom: 5px; }
        .section-subtitle { font-size: 11px; color: var(--text-light); margin-bottom: 20px; }

        .chart-wrapper { padding: 15px; border-radius: 20px; background: #fafafa; border: 1px solid #eee; margin-bottom: 25px; cursor: pointer; transition: 0.3s; }
        .chart-wrapper:hover { border-color: var(--primary); transform: translateY(-2px); }
        .chart-container { display: flex; justify-content: center; align-items: center; margin-bottom: 15px; transition: 0.3s; }
        .pie-chart {
            width: 110px; height: 110px; border-radius: 50%;
            background: conic-gradient(var(--icon-green) 0% 50%, var(--icon-red) 50% 100%);
            box-shadow: inset 0 0 15px rgba(0,0,0,0.1), 0 5px 15px rgba(0,0,0,0.05);
            position: relative;
        }
        .pie-chart::after {
            content: ''; position: absolute; top: 15px; left: 15px; width: 80px; height: 80px; background: #fafafa; border-radius: 50%;
        }
        .cf-stats { display: flex; justify-content: space-between; }
        .cf-stat-item { text-align: center; }
        .cf-stat-val { font-size: 11px; font-weight: 600; display: block; }
        .cf-stat-label { font-size: 10px; color: var(--text-light); }

        .activity-list { display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px; }
        .activity-item { display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 8px; border-radius: 10px; transition: 0.2s; }
        .activity-item:hover { background-color: var(--bg-color); transform: translateX(5px); }
        .activity-item img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .activity-info h4 { font-size: 13px; }
        .activity-info p { font-size: 11px; color: var(--text-light); }
        .activity-time { font-size: 10px; color: var(--text-light); margin-left: auto; }

        .summary-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .stat-box { background: #f8f9fa; padding: 15px; border-radius: 15px; text-align: center; display: flex; flex-direction: column; justify-content: center; min-height: 80px; cursor: pointer; transition: 0.2s; }
        .stat-box:hover:not(.primary) { background-color: #e9ecef; transform: translateY(-2px); }
        .stat-box.primary { background: var(--primary); color: white; box-shadow: 0 10px 20px rgba(59, 48, 134, 0.3); transform: scale(1.05); }

        /* Utility Colors */
        .text-green { color: var(--icon-green); background: rgba(46, 204, 113, 0.1); border: 1px solid rgba(46, 204, 113, 0.3); }
        .text-orange { color: var(--icon-orange); background: rgba(255, 159, 67, 0.1); border: 1px solid rgba(255, 159, 67, 0.3); }
        .text-blue { color: var(--icon-blue); background: rgba(52, 152, 219, 0.1); border: 1px solid rgba(52, 152, 219, 0.3); }
        .text-red { color: #e74c3c; background: rgba(231, 76, 60, 0.1); border: 1px solid rgba(231, 76, 60, 0.3); }

        /* Modals Overlays */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); backdrop-filter: blur(3px);
            display: flex; justify-content: center; align-items: center;
            z-index: 99999; opacity: 0; visibility: hidden; transition: 0.3s;
        }
        .modal-overlay.show { opacity: 1; visibility: visible; }
        
        .modal-content {
            background: white; padding: 30px; border-radius: 20px;
            width: 90%; max-width: 550px; transform: translateY(30px); transition: 0.3s; position: relative;
            max-height: 90vh; overflow-y: auto; z-index: 100000;
        }
        .modal-overlay.show .modal-content { transform: translateY(0); }
        .btn-close-modal { position: absolute; top: 15px; right: 20px; font-size: 20px; cursor: pointer; color: var(--text-light); }
        
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; font-size: 12px; margin-bottom: 5px; color: var(--text-light); font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 10px; outline: none; font-size: 13px; font-family: 'Poppins', sans-serif; }

        /* Color & Icon Pickers */
        .color-picker-group, .icon-picker-group { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 5px; }
        .color-option {
            width: 32px; height: 32px; border-radius: 8px; display: flex; justify-content: center; align-items: center;
            cursor: pointer; border: 2px solid transparent; transition: 0.2s;
        }
        .color-option.selected { border-color: var(--text-dark); transform: scale(1.1); }
        
        .icon-picker-container {
            max-height: 140px; overflow-y: auto; border: 1px solid #eee; padding: 10px; border-radius: 10px; background: #fafafa;
        }
        .icon-option {
            width: 35px; height: 35px; border-radius: 8px; display: inline-flex; justify-content: center; align-items: center;
            cursor: pointer; border: 2px solid transparent; background: white; color: var(--text-dark); font-size: 14px; transition: 0.2s; margin: 3px;
        }
        .icon-option:hover { background: var(--primary-light); color: var(--primary); }
        .icon-option.selected { border-color: var(--primary); background: var(--primary); color: white; transform: scale(1.1); }

        /* Gallery & Comment Specific inside Modal */
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; margin-top: 15px; }
        .gallery-item { height: 80px; border-radius: 8px; background: #eee; background-size: cover; background-position: center; border: 1px solid #ddd; }
        
        .comment-box-list { display: flex; flex-direction: column; gap: 10px; max-height: 200px; overflow-y: auto; margin-bottom: 15px; }
        .comment-bubble { background: #f8f9fa; padding: 10px 15px; border-radius: 10px; font-size: 12px; border: 1px solid #eee; }
        .comment-bubble b { color: var(--primary); }

        /* Search Modal Specific */
        #modalSearch { align-items: flex-start; padding-top: 10vh; }
        .search-container {
            width: 100%; background: var(--white); border-radius: 15px;
            display: flex; align-items: center; padding: 10px 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .search-container .search-icon { font-size: 20px; color: var(--text-light); margin-right: 15px; }
        .search-container input { flex: 1; border: none; padding: 15px 0; font-size: 18px; outline: none; background: transparent; }
        .search-container .esc-hint { font-size: 12px; color: var(--text-light); background: var(--bg-color); padding: 6px 10px; border-radius: 8px; font-weight: 600; cursor: pointer; }

        .btn-toggle-right { display: none; } 

        @media (max-width: 1200px) {
            .dashboard { grid-template-columns: 80px 1fr; }
            .dashboard.sidebar-hidden { grid-template-columns: 0px 1fr; }
            .btn-toggle-right { display: inline-block !important; }
            .right-panel {
                position: fixed; right: -350px; top: 20px; height: calc(100vh - 40px);
                width: 320px; box-shadow: -10px 0 30px rgba(0,0,0,0.1); z-index: 1000;
            }
            .right-panel.show { right: 20px; }
        }

        @media (max-width: 768px) {
            .dashboard { grid-template-columns: 1fr; padding: 10px; gap: 10px; }
            .dashboard.sidebar-hidden { grid-template-columns: 1fr; }
            .sidebar {
                position: fixed; left: -100%; top: 10px; height: calc(100vh - 20px);
                width: 80px; box-shadow: 10px 0 30px rgba(0,0,0,0.1);
            }
            .dashboard.sidebar-visible .sidebar { left: 10px; opacity: 1; transform: translateX(0); }
            .right-panel { top: 10px; height: calc(100vh - 20px); right: -100%; width: 280px; }
            .right-panel.show { right: 10px; }
            header { padding: 15px; margin-bottom: 20px; border-radius: 15px; }
            .user-profile span { display: none; } 
            .user-profile img { width: 30px; height: 30px; }
            
            .dropdown-menu { width: 220px; right: -10px; }
            #profileDropdown { right: 0 !important; width: 230px !important; min-width: 230px !important; max-width: 85vw !important; }
            
            .projects-header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .actions { width: 100%; justify-content: space-between; margin-top: 10px; }
            
            .cards-grid.list-view .card { 
                display: flex !important;
                flex-direction: column !important; 
                align-items: stretch !important; 
                text-align: left !important; 
                gap: 15px !important; 
                padding: 20px !important;
                grid-template-columns: none !important;
            }
            .cards-grid.list-view .card-icon { margin: 0 !important; }
            .cards-grid.list-view .card-menu { position: absolute !important; top: 15px !important; right: 15px !important; transform: none !important; }
            .cards-grid.list-view .card-info { width: 100% !important; }
            .cards-grid.list-view .progress-section { display: flex !important; flex-direction: column !important; width: 100% !important; margin-bottom: 0 !important; }
            .cards-grid.list-view .card-footer { display: flex !important; justify-content: space-between !important; align-items: center !important; border-top: 1px solid #f1f2f6 !important; padding-top: 15px !important; width: 100% !important; }
        }
    </style>
</head>
<body>

    <div class="overlay-backdrop" id="mobileBackdrop" onclick="closeAllDrawers()"></div>

    <div class="dashboard" id="mainDashboard">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="logo" onclick="openDynamicModal('System Info', '<p>Dashboard V5.0<br>Interactive Social & Cash Flow.</p>')">
                <img src="logo.png" alt="Logo">
            </div>
            <div class="menu-icons">
                <div class="menu-item" data-view="analytics" title="Analytics"><i class="fa-solid fa-chart-line"></i></div>
                <div class="menu-item" data-view="timeline" title="Timeline / Jadwal Harian"><i class="fa-regular fa-clock"></i></div>
                <div class="menu-item active" data-view="projects" title="Projects"><i class="fa-solid fa-desktop"></i></div>
                <div class="menu-item" data-view="team" title="Team & Clients"><i class="fa-solid fa-users"></i></div>
                <div class="menu-item" data-view="messages" title="Messages"><i class="fa-regular fa-comment-dots"></i></div>
                <div class="menu-item" data-view="discussions" title="Discussions"><i class="fa-solid fa-feather-pointed"></i></div>
                <div class="menu-item" data-view="settings" title="Settings"><i class="fa-solid fa-gear"></i></div>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <header>
                <div class="header-left">
                    <i class="fa-solid fa-bars-staggered" onclick="toggleSidebar()" title="Toggle Sidebar"></i>
                </div>
                <div class="header-right">
                    <i class="fa-solid fa-magnifying-glass" onclick="openSearchModal()" title="Search"></i>
                    
                    <div style="position:relative;">
                        <i class="fa-regular fa-clipboard" onclick="toggleDropdown('clipboardDropdown', event)" title="Tasks"></i>
                        <div id="clipboardDropdown" class="dropdown-menu"></div>
                    </div>

                    <div style="position:relative;">
                        <i class="fa-regular fa-bell" onclick="toggleDropdown('notifDropdown', event)" title="Notifications" style="cursor:pointer;"></i>
                        <span style="position:absolute; top:-5px; right:-5px; background:#ff4757; width:10px; height:10px; border-radius:50%;"></span>
                        <div id="notifDropdown" class="dropdown-menu">
                            <div class="dropdown-header" data-i18n="latestNotif">Notifikasi Pesan Masuk</div>
                            <div class="dropdown-item" onclick="openNotificationChat(1, 'Belong Interactive', 'https://ui-avatars.com/api/?name=Belong+Interactive&background=random')">
                                <img src="https://ui-avatars.com/api/?name=Belong+Interactive&background=random" style="width:28px; height:28px; border-radius:50%; object-fit:cover;">
                                <div style="overflow:hidden;">
                                    <b>Belong Interactive</b><br>
                                    <span style="font-size:11px; color:var(--text-light); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block;">Halo Ghulam, apakah progress...</span>
                                </div>
                            </div>
                            <div class="dropdown-item" onclick="openNotificationChat(2, 'PT Solusi Digital (Prospek)', 'https://ui-avatars.com/api/?name=PT+Solusi+Digital&background=random')">
                                <img src="https://ui-avatars.com/api/?name=PT+Solusi+Digital&background=random" style="width:28px; height:28px; border-radius:50%; object-fit:cover;">
                                <div style="overflow:hidden;">
                                    <b>PT Solusi Digital (Prospek)</b><br>
                                    <span style="font-size:11px; color:var(--text-light); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block;">Hai, kami tertarik untuk diskusi...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <i class="fa-solid fa-chart-pie btn-toggle-right" onclick="toggleRightPanel()" title="Statistik Dashboard" style="color:var(--primary);"></i>

                    <!-- User Profile -->
                    <div class="user-profile" style="position:relative;" onclick="toggleDropdown('profileDropdown', event)">
                        <img id="headerUserAvatar" src="" alt="Profile">
                        <span id="headerUserName">Ghulam <i class="fa-solid fa-chevron-down" style="font-size: 10px; margin-left: 5px;"></i></span>
                        <div id="profileDropdown" class="dropdown-menu" style="top:45px;">
                            <div class="dropdown-header" id="dropdownHelloUser">Halo, Ghulam!</div>
                            <div class="dropdown-item" onclick="openViewProfileModal()"><i class="fa-regular fa-user"></i> <span data-i18n="viewProfile">Lihat Profil</span></div>
                            <div class="dropdown-item" onclick="switchViewToSettings()"><i class="fa-solid fa-gear"></i> <span data-i18n="settings">Pengaturan</span></div>
                            <div class="dropdown-item" style="color:#ff4757;" onclick="window.location.href='logout.php'"><i class="fa-solid fa-arrow-right-from-bracket"></i> <span data-i18n="logout">Keluar</span></div>
                        </div>
                    </div>

                    <!-- Language Dropdown (Far Right) -->
                    <select id="langSelect" class="lang-dropdown" onchange="setLanguage(this.value)" title="Pilih Bahasa">
                        <option value="id">🇮🇩 ID</option>
                        <option value="en">🇬🇧 EN</option>
                        <option value="ja">🇯🇵 JA</option>
                    </select>
                </div>
            </header>

            <div id="view-container"></div>
        </main>

        <!-- RIGHT PANEL -->
        <aside class="right-panel" id="rightPanel">
            <h2 class="section-title" data-i18n="onlinePayments">Online payments</h2>
            <p class="section-subtitle" data-i18n="visaCards">Visa cards</p>
            
            <div class="chart-wrapper" onclick="openCashFlowModal()" title="Klik untuk kelola Cash Flow">
                <div class="chart-container">
                    <div class="pie-chart"></div>
                </div>
                <div class="cf-stats">
                    <div class="cf-stat-item">
                        <span class="cf-stat-val" style="color:var(--icon-green)" id="cfInDisplay">$0</span>
                        <span class="cf-stat-label"><span data-i18n="incomeLabel">Income</span> (<span id="cfInPercent">50%</span>)</span>
                    </div>
                    <div class="cf-stat-item">
                        <span class="cf-stat-val" style="color:var(--icon-red)" id="cfOutDisplay">$0</span>
                        <span class="cf-stat-label"><span data-i18n="expenseLabel">Expense</span> (<span id="cfOutPercent">50%</span>)</span>
                    </div>
                    <div class="cf-stat-item">
                        <span class="cf-stat-val" style="color:var(--primary)" id="cfNetDisplay">$0</span>
                        <span class="cf-stat-label" data-i18n="netLabel">Net</span>
                    </div>
                </div>
            </div>

            <h2 class="section-title" style="display: flex; justify-content: space-between; align-items: center;">
                <span style="cursor:pointer; color:var(--primary);" onclick="openAllHistoryModal()" title="Klik untuk lihat semua riwayat"><span data-i18n="recentActivities">Recent Activities</span> <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px;"></i></span>
                <span id="currentDateSpan" style="font-size: 11px; font-weight: normal; color: var(--text-light);"></span>
            </h2>
            
            <div class="activity-list" id="activityListContainer"></div>

            <div class="summary-stats">
                <div class="stat-box" onclick="openDynamicModal('Laporan', 'Total seluruh proyek aktif & selesai')">
                    <h2 id="statTotal">0</h2>
                    <p data-i18n="totalProject">Total Project</p>
                </div>
                <div class="stat-box primary" onclick="openDynamicModal('Laporan', 'Total proyek status started / upcoming')">
                    <h2 id="statStarted">0</h2>
                    <p data-i18n="upcoming">Upcoming</p>
                </div>
                <div class="stat-box" onclick="openDynamicModal('Laporan', 'Total proyek status approval & discrepancy')">
                    <h2 id="statInProgress">0</h2>
                    <p data-i18n="inProgress">In Progress</p>
                </div>
                <div class="stat-box" onclick="openDynamicModal('Laporan', 'Total proyek yang sudah selesai')">
                    <h2 id="statCompleted">0</h2>
                    <p data-i18n="completedStat">Completed</p>
                </div>
            </div>
        </aside>
    </div>
    
    <!-- MODAL SEARCH -->
    <div class="modal-overlay" id="modalSearch" onclick="closeModals(event)">
        <div style="width: 90%; max-width: 650px; display: flex; flex-direction: column; gap: 10px; position: absolute; top: 15vh;" onclick="event.stopPropagation()">
            <div class="search-container">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="searchInput" placeholder="Cari proyek, tim, atau tugas..." oninput="executeLiveSearch(this.value)">
                <span class="esc-hint" onclick="closeSpecificModal('modalSearch')">ESC</span>
            </div>
            <div id="searchResultsBox" style="background: white; border-radius: 15px; padding: 15px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); max-height: 350px; overflow-y: auto; display: none;"></div>
        </div>
    </div>

    <!-- MODAL CREATE / EDIT PROJECT -->
    <div class="modal-overlay" id="modalProject" onclick="closeModals(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <i class="fa-solid fa-xmark btn-close-modal" onclick="closeSpecificModal('modalProject')"></i>
            <h2 id="modalProjectTitle" style="margin-bottom: 20px;">Buat Proyek Baru</h2>
            <input type="hidden" id="editProjectId">
            
            <div class="form-group">
                <label data-i18n="projectName">Nama Proyek</label>
                <input type="text" id="projTitle" placeholder="Misal: Redesign Landing Page">
            </div>
            <div class="form-group">
                <label data-i18n="clientName">Klien / Perusahaan</label>
                <input type="text" id="projClient" placeholder="Nama Klien">
            </div>
            <div class="form-group">
                <label data-i18n="statusCategory">Status Kategori</label>
                <select id="projStatus">
                    <option value="started" data-i18n="started">Started</option>
                    <option value="approval" data-i18n="approval">Approval</option>
                    <option value="discrepancy" data-i18n="discrepancy">Discrepancy</option>
                    <option value="completed" data-i18n="completed">Completed</option>
                </select>
            </div>
            <div class="form-group">
                <label data-i18n="progressPercent">Progress (%)</label>
                <input type="number" id="projProgress" min="0" max="100" value="50">
            </div>
            <div class="form-group">
                <label data-i18n="deadlineLabel">Deadline (WIB / Asia/Jakarta)</label>
                <input type="date" id="projDeadline">
            </div>
            
            <div class="form-group">
                <label data-i18n="pickColor">Pilih Warna Kartu</label>
                <div class="color-picker-group">
                    <div class="color-option selected" style="background:#ff9f43;" onclick="selectColor('#ff9f43', this)"></div>
                    <div class="color-option" style="background:#2ecc71;" onclick="selectColor('#2ecc71', this)"></div>
                    <div class="color-option" style="background:#3498db;" onclick="selectColor('#3498db', this)"></div>
                    <div class="color-option" style="background:#9b59b6;" onclick="selectColor('#9b59b6', this)"></div>
                    <div class="color-option" style="background:#f1c40f;" onclick="selectColor('#f1c40f', this)"></div>
                    <div class="color-option" style="background:#e74c3c;" onclick="selectColor('#e74c3c', this)"></div>
                    <div class="color-option" style="background:#1abc9c;" onclick="selectColor('#1abc9c', this)"></div>
                    <div class="color-option" style="background:#34495e;" onclick="selectColor('#34495e', this)"></div>
                </div>
                <input type="hidden" id="projColor" value="#ff9f43">
            </div>

            <div class="form-group">
                <label data-i18n="pickIcon">Pilih Simbol Icon</label>
                <div class="icon-picker-container">
                    <div class="icon-option selected" onclick="selectIcon('fa-rocket', this)"><i class="fa-solid fa-rocket"></i></div>
                    <div class="icon-option" onclick="selectIcon('fa-laptop-code', this)"><i class="fa-solid fa-laptop-code"></i></div>
                    <div class="icon-option" onclick="selectIcon('fa-plane-departure', this)"><i class="fa-solid fa-plane-departure"></i></div>
                    <div class="icon-option" onclick="selectIcon('fa-cubes', this)"><i class="fa-solid fa-cubes"></i></div>
                    <div class="icon-option" onclick="selectIcon('fa-mobile-screen-button', this)"><i class="fa-solid fa-mobile-screen-button"></i></div>
                    <div class="icon-option" onclick="selectIcon('fa-server', this)"><i class="fa-solid fa-server"></i></div>
                    <div class="icon-option" onclick="selectIcon('fa-code', this)"><i class="fa-solid fa-code"></i></div>
                    <div class="icon-option" onclick="selectIcon('fa-database', this)"><i class="fa-solid fa-database"></i></div>
                    <div class="icon-option" onclick="selectIcon('fa-shield-halved', this)"><i class="fa-solid fa-shield-halved"></i></div>
                    <div class="icon-option" onclick="selectIcon('fa-bug', this)"><i class="fa-solid fa-bug"></i></div>
                    <div class="icon-option" onclick="selectIcon('fa-chart-pie', this)"><i class="fa-solid fa-chart-pie"></i></div>
                    <div class="icon-option" onclick="selectIcon('fa-globe', this)"><i class="fa-solid fa-globe"></i></div>
                    <div class="icon-option" onclick="selectIcon('fa-bullhorn', this)"><i class="fa-solid fa-bullhorn"></i></div>
                    <div class="icon-option" onclick="selectIcon('fa-chart-line', this)"><i class="fa-solid fa-chart-line"></i></div>
                    <div class="icon-option" onclick="selectIcon('fa-wallet', this)"><i class="fa-solid fa-wallet"></i></div>
                    <div class="icon-option" onclick="selectIcon('fa-store', this)"><i class="fa-solid fa-store"></i></div>
                    <div class="icon-option" onclick="selectIcon('fa-gamepad', this)"><i class="fa-solid fa-gamepad"></i></div>
                    <div class="icon-option" onclick="selectIcon('fa-palette', this)"><i class="fa-solid fa-palette"></i></div>
                    <div class="icon-option" onclick="selectIcon('fa-pen-nib', this)"><i class="fa-solid fa-pen-nib"></i></div>
                    <div class="icon-option" onclick="selectIcon('fa-graduation-cap', this)"><i class="fa-solid fa-graduation-cap"></i></div>
                </div>
                <input type="hidden" id="projIcon" value="fa-rocket">
            </div>

            <button class="btn-create" style="width: 100%; justify-content: center; margin-top: 25px; padding: 15px;" onclick="saveProjectData()" data-i18n="saveProject">Simpan Proyek</button>
        </div>
    </div>

    <!-- GENERIC DYNAMIC MODAL -->
    <div class="modal-overlay" id="modalDynamic" onclick="closeModals(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <i class="fa-solid fa-xmark btn-close-modal" onclick="closeSpecificModal('modalDynamic')"></i>
            <h2 id="dynamicTitle" style="margin-bottom: 15px; font-size:20px;">Judul</h2>
            <div id="dynamicBody" style="font-size: 14px; color: var(--text-dark); line-height: 1.6;">Konten...</div>
        </div>
    </div>

    <script>
        // --- DATA AWAL DIKIRIM LANGSUNG DARI SERVER (PHP + MySQL), BUKAN LAGI localStorage ---
        const CSRF_TOKEN = <?php echo json_encode($csrf); ?>;
        const BOOTSTRAP = <?php echo json_encode($bootstrap, JSON_UNESCAPED_UNICODE); ?>;

        // --- HELPER FUNCTION UNTUK GENERATE AVARAR INISIAL ---
        function generateAvatar(name) {
            return `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'User')}&background=random&color=fff&bold=true`;
        }

        /**
         * Simpan satu modul data (projects/team/companies/tasks/schedules/activities/cashflow/threads)
         * ke server (tabel user_data di MySQL), menggantikan localStorage.setItem lama.
         * Dipanggil "fire-and-forget" supaya UI tetap responsif seperti sebelumnya.
         */
        function persist(key, data) {
            fetch(`api/data.php?key=${key}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ data, csrf: CSRF_TOKEN })
            }).catch(err => console.error('Gagal menyimpan ke server:', key, err));
        }

        // --- DEKLARASI SELURUH VARIABEL STATE (diisi dari data user yang sedang login) ---
        let userProfile = BOOTSTRAP.profile;
        if (!userProfile.avatar) userProfile.avatar = generateAvatar(userProfile.name);

        let teamMembers = Array.isArray(BOOTSTRAP.team) ? BOOTSTRAP.team : [];
        let companiesData = Array.isArray(BOOTSTRAP.companies) ? BOOTSTRAP.companies : [];
        let projectsData = Array.isArray(BOOTSTRAP.projects) ? BOOTSTRAP.projects : [];
        let tasksData = Array.isArray(BOOTSTRAP.tasks) ? BOOTSTRAP.tasks : [];
        let dailySchedules = Array.isArray(BOOTSTRAP.schedules) ? BOOTSTRAP.schedules : [];
        let recentActivities = Array.isArray(BOOTSTRAP.activities) ? BOOTSTRAP.activities : [];
        let cashFlowData = Array.isArray(BOOTSTRAP.cashflow) ? BOOTSTRAP.cashflow : [];

        function executeLiveSearch(query) {
            const box = document.getElementById('searchResultsBox');
            if(!box) return;
            if(!query.trim()) {
                box.style.display = 'none';
                box.innerHTML = '';
                return;
            }
            const q = query.toLowerCase();
            let html = '';
            const matchedProjects = projectsData.filter(p => p.title.toLowerCase().includes(q) || p.client.toLowerCase().includes(q));
            const matchedTeam = teamMembers.filter(m => m.name.toLowerCase().includes(q) || m.role.toLowerCase().includes(q));
            const matchedTasks = tasksData.filter(t => t.title.toLowerCase().includes(q));

            if(matchedProjects.length === 0 && matchedTeam.length === 0 && matchedTasks.length === 0) {
                box.style.display = 'block';
                box.innerHTML = `<p style="text-align:center; color:var(--text-light); font-size:13px; padding:10px;">${t('noResultsFor')} "${query}"</p>`;
                return;
            }

            box.style.display = 'block';
            if(matchedProjects.length > 0) {
                html += `<div style="font-size:11px; font-weight:600; color:var(--text-light); margin-bottom:5px;">${t('projectsHeaderSearch').toUpperCase()}</div>`;
                matchedProjects.forEach(p => {
                    html += `
                        <div onclick="closeSpecificModal('modalSearch'); openEditProjectModal(${p.id});" style="padding:8px 10px; border-radius:8px; cursor:pointer; font-size:13px; display:flex; align-items:center; gap:10px;" onmouseover="this.style.background='var(--bg-color)'" onmouseout="this.style.background='transparent'">
                            <i class="fa-solid ${p.icon}" style="color:${p.color};"></i>
                            <div><b>${p.title}</b> <span style="color:var(--text-light); font-size:11px;">(${t('clientName')}: ${p.client} - ${p.progress}%)</span></div>
                        </div>
                    `;
                });
            }

            if(matchedTeam.length > 0) {
                html += `<div style="font-size:11px; font-weight:600; color:var(--text-light); margin:10px 0 5px;">${t('teamAndClients').toUpperCase()}</div>`;
                matchedTeam.forEach(m => {
                    html += `
                        <div onclick="closeSpecificModal('modalSearch'); switchViewToTeam();" style="padding:8px 10px; border-radius:8px; cursor:pointer; font-size:13px; display:flex; align-items:center; gap:10px;" onmouseover="this.style.background='var(--bg-color)'" onmouseout="this.style.background='transparent'">
                            <img src="${m.avatar}" style="width:25px; height:25px; border-radius:50%; object-fit:cover;">
                            <div><b>${m.name}</b> <span style="color:var(--text-light); font-size:11px;">(${m.role})</span></div>
                        </div>
                    `;
                });
            }

            if(matchedTasks.length > 0) {
                html += `<div style="font-size:11px; font-weight:600; color:var(--text-light); margin:10px 0 5px;">${t('pendingTasks').toUpperCase()}</div>`;
                matchedTasks.forEach(t => {
                    html += `
                        <div onclick="closeSpecificModal('modalSearch'); openAllTasksModal();" style="padding:8px 10px; border-radius:8px; cursor:pointer; font-size:13px; display:flex; align-items:center; gap:10px;" onmouseover="this.style.background='var(--bg-color)'" onmouseout="this.style.background='transparent'">
                            <i class="fa-solid fa-list-check" style="color:var(--primary);"></i>
                            <div><b>${t.title}</b> <span style="color:var(--text-light); font-size:11px;">(${t.completed ? t('completed') : 'Pending'})</span></div>
                        </div>
                    `;
                });
            }
            box.innerHTML = html;
        }

        function switchViewToTeam() {
            document.querySelectorAll('.sidebar .menu-item').forEach(i => i.classList.remove('active'));
            const teamMenu = document.querySelector('.sidebar .menu-item[data-view="team"]');
            if(teamMenu) teamMenu.classList.add('active');

            const container = document.getElementById('view-container');
            if(container) {
                container.innerHTML = renderTeamManagementView();
                currentTeamPage = 1;
                renderTeamTableRows();
                applyTranslations();
            }
        }

        function openManageTeamModal() {
            const teamOnlyMembers = teamMembers.filter(m => m.type === 'team');
            let html = `
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <p style="color:var(--text-light); font-size:12px; margin:0;" data-i18n="manageTeamDesc">Kelola anggota tim secara cepat langsung dari sini:</p>
                    <button class="btn-create" style="padding:6px 12px; font-size:11px;" onclick="openModalAddTeamFromAnalytics()"><i class="fa-solid fa-plus"></i> <span data-i18n="addBtn">Tambah</span></button>
                </div>
                <div style="display:flex; flex-direction:column; gap:10px; max-height:280px; overflow-y:auto; margin-bottom:15px;" id="manageTeamModalList">
            `;

            if(teamOnlyMembers.length === 0) {
                html += `<p style="text-align:center; color:var(--text-light); padding:15px;" data-i18n="noTeam">Belum ada data tim.</p>`;
            } else {
                teamOnlyMembers.forEach(m => {
                    let badgeClass = 'badge-team';
                    let typeLabel = t('teamOption');

                    html += `
                        <div style="display:flex; align-items:center; gap:12px; padding:10px 14px; background:#f8f9fa; border:1px solid #eee; border-radius:10px; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:10px; flex:1; cursor:pointer;" onclick="openEditTeamMemberModal(${m.id})">
                                <img src="${m.avatar}" style="width:34px; height:34px; border-radius:50%; object-fit:cover;">
                                <div>
                                    <div style="font-weight:600; font-size:13px;">${m.name} <span class="badge-role ${badgeClass}" style="margin-left:5px; font-size:9px;">${typeLabel}</span></div>
                                    <div style="font-size:11px; color:var(--text-light);">${m.role}</div>
                                </div>
                            </div>
                            <div style="display:flex; gap:8px;">
                                <button onclick="openEditTeamMemberModal(${m.id})" style="background:none; border:none; color:var(--primary); cursor:pointer; font-size:13px;" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                <button onclick="deleteTeamMemberFromAnalytics(${m.id})" style="background:none; border:none; color:#ff4757; cursor:pointer; font-size:13px;" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    `;
                });
            }
            html += `</div>`;
            html += `<button class="btn-create" style="width:100%; justify-content:center; padding:10px;" onclick="closeSpecificModal('modalDynamic'); switchViewToTeam();" data-i18n="openFullTeam">Buka Full Team Table View</button>`;

            openDynamicModal("Manage Team (Analytics)", html);
        }

        function openModalAddTeamFromAnalytics() {
            let html = `
                <div class="form-group"><label data-i18n="nameLabel">Nama Lengkap</label><input type="text" id="modalTeamName" placeholder="Nama Personel"></div>
                <div class="form-group"><label data-i18n="roleLabel">Peran / Jabatan</label><input type="text" id="modalTeamRole" placeholder="Misal: Frontend Dev"></div>
                <input type="hidden" id="modalTeamType" value="team">
                <button class="btn-create" style="width:100%; justify-content:center; padding:12px; margin-top:10px;" onclick="saveModalTeamNew()" data-i18n="saveMember">Simpan Anggota Baru</button>
            `;
            openDynamicModal("Tambah Anggota Tim Baru", html);
        }

        function saveModalTeamNew() {
            const name = document.getElementById('modalTeamName').value.trim();
            const role = document.getElementById('modalTeamRole').value.trim();
            const type = document.getElementById('modalTeamType').value || 'team';

            if(!name) { alert("Nama wajib diisi!"); return; }

            teamMembers.unshift({
                id: Date.now(),
                name,
                role: role || 'Member',
                type,
                avatar: generateAvatar(name)
            });

            syncLocalStorage();
            logActivity(`Menambahkan tim baru: ${name}`);
            openManageTeamModal();
        }

        function deleteTeamMemberFromAnalytics(id) {
            if(confirm("Yakin ingin menghapus anggota tim ini?")) {
                teamMembers = teamMembers.filter(m => m.id != id);
                syncLocalStorage();
                logActivity("Menghapus anggota tim dari modal");
                openManageTeamModal();
            }
        }

        function openAddCompanyModal() {
            let html = `
                <input type="hidden" id="editCompanyId" value="">
                <div class="form-group">
                    <label data-i18n="compNameLabel">Nama Perusahaan / Bisnis</label>
                    <input type="text" id="compNameInput" placeholder="Misal: PT Maju Bersama">
                </div>
                <div class="form-group">
                    <label data-i18n="compTypeLabel">Tipe / Bidang Usaha</label>
                    <input type="text" id="compTypeInput" placeholder="Misal: IT / Tech Agency">
                </div>
                <div class="form-group">
                    <label data-i18n="assetsLabel">Total Aset ($)</label>
                    <input type="number" id="compAssetsInput" placeholder="150000" value="0">
                </div>
                <div class="form-group">
                    <label data-i18n="liabLabel">Total Liabilitas / Hutang ($)</label>
                    <input type="number" id="compLiabInput" placeholder="45000" value="0">
                </div>
                <div class="form-group">
                    <label data-i18n="pickColor">Pilih Warna Kartu Rekening</label>
                    <div class="color-picker-group" id="compColorPicker">
                        <div class="color-option selected" style="background:#3b3086;" onclick="selectCompColor('#3b3086', this)"></div>
                        <div class="color-option" style="background:#2ecc71;" onclick="selectCompColor('#2ecc71', this)"></div>
                        <div class="color-option" style="background:#3498db;" onclick="selectCompColor('#3498db', this)"></div>
                        <div class="color-option" style="background:#ff9f43;" onclick="selectCompColor('#ff9f43', this)"></div>
                        <div class="color-option" style="background:#e74c3c;" onclick="selectCompColor('#e74c3c', this)"></div>
                        <div class="color-option" style="background:#34495e;" onclick="selectCompColor('#34495e', this)"></div>
                    </div>
                    <input type="hidden" id="compColorInput" value="#3b3086">
                </div>
                <button class="btn-create" style="width:100%; justify-content:center; padding:12px; margin-top:15px;" onclick="saveCompanyData()" data-i18n="saveCompany">Simpan Kartu Bisnis</button>
            `;
            openDynamicModal(t('addCompanyModalTitle'), html);
        }

        function selectCompColor(colorHex, element) {
            document.querySelectorAll('#compColorPicker .color-option').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            document.getElementById('compColorInput').value = colorHex;
        }

        function openEditCompanyModal(id) {
            const comp = companiesData.find(c => c.id == id);
            if(!comp) return;

            let html = `
                <input type="hidden" id="editCompanyId" value="${comp.id}">
                <div class="form-group">
                    <label data-i18n="compNameLabel">Nama Perusahaan / Bisnis</label>
                    <input type="text" id="compNameInput" value="${comp.name}">
                </div>
                <div class="form-group">
                    <label data-i18n="compTypeLabel">Tipe / Bidang Usaha</label>
                    <input type="text" id="compTypeInput" value="${comp.type}">
                </div>
                <div class="form-group">
                    <label data-i18n="assetsLabel">Total Aset ($)</label>
                    <input type="number" id="compAssetsInput" value="${comp.assets}">
                </div>
                <div class="form-group">
                    <label data-i18n="liabLabel">Total Liabilitas / Hutang ($)</label>
                    <input type="number" id="compLiabInput" value="${comp.liabilities}">
                </div>
                <div class="form-group">
                    <label data-i18n="pickColor">Pilih Warna Kartu Rekening</label>
                    <div class="color-picker-group" id="compColorPicker">
                        <div class="color-option ${comp.color === '#3b3086' ? 'selected' : ''}" style="background:#3b3086;" onclick="selectCompColor('#3b3086', this)"></div>
                        <div class="color-option ${comp.color === '#2ecc71' ? 'selected' : ''}" style="background:#2ecc71;" onclick="selectCompColor('#2ecc71', this)"></div>
                        <div class="color-option ${comp.color === '#3498db' ? 'selected' : ''}" style="background:#3498db;" onclick="selectCompColor('#3498db', this)"></div>
                        <div class="color-option ${comp.color === '#ff9f43' ? 'selected' : ''}" style="background:#ff9f43;" onclick="selectCompColor('#ff9f43', this)"></div>
                        <div class="color-option ${comp.color === '#e74c3c' ? 'selected' : ''}" style="background:#e74c3c;" onclick="selectCompColor('#e74c3c', this)"></div>
                        <div class="color-option ${comp.color === '#34495e' ? 'selected' : ''}" style="background:#34495e;" onclick="selectCompColor('#34495e', this)"></div>
                    </div>
                    <input type="hidden" id="compColorInput" value="${comp.color || '#3b3086'}">
                </div>
                <button class="btn-create" style="width:100%; justify-content:center; padding:12px; margin-top:15px;" onclick="saveCompanyData()" data-i18n="saveChanges">Simpan Perubahan</button>
            `;
            openDynamicModal(t('editCompanyModalTitle'), html);
        }

        function saveCompanyData() {
            const id = document.getElementById('editCompanyId').value;
            const name = document.getElementById('compNameInput').value.trim();
            const type = document.getElementById('compTypeInput').value.trim();
            const assets = Number(document.getElementById('compAssetsInput').value) || 0;
            const liabilities = Number(document.getElementById('compLiabInput').value) || 0;
            const color = document.getElementById('compColorInput').value;

            if(!name) { alert("Nama perusahaan wajib diisi!"); return; }

            if(id) {
                const comp = companiesData.find(c => c.id == id);
                if(comp) {
                    comp.name = name;
                    comp.type = type;
                    comp.assets = assets;
                    comp.liabilities = liabilities;
                    comp.color = color;
                    logActivity(`Memperbarui data kartu rekening perusahaan "${name}"`);
                }
            } else {
                companiesData.push({
                    id: Date.now(),
                    name,
                    type: type || 'General Business',
                    assets,
                    liabilities,
                    color
                });
                logActivity(`Menambahkan kartu rekening perusahaan baru "${name}"`);
            }

            persist('companies', companiesData);
            closeSpecificModal('modalDynamic');

            const container = document.getElementById('view-container');
            if(container) container.innerHTML = renderAnalyticsView();
        }

        function deleteCompany(id) {
            if(confirm("Yakin ingin menghapus kartu perusahaan ini?")) {
                companiesData = companiesData.filter(c => c.id != id);
                persist('companies', companiesData);
                logActivity("Menghapus kartu rekening perusahaan");

                const container = document.getElementById('view-container');
                if(container) container.innerHTML = renderAnalyticsView();
            }
        }

        function renderAnalyticsView() {
            let cardsHtml = '';
            let totalAssetsAll = 0;
            let totalLiabAll = 0;
            const teamOnlyCount = teamMembers.filter(m => m.type === 'team').length;

            companiesData.forEach(comp => {
                const equity = comp.assets - comp.liabilities;
                totalAssetsAll += comp.assets;
                totalLiabAll += comp.liabilities;

                cardsHtml += `
                    <div style="background: linear-gradient(135deg, ${comp.color || '#3b3086'}, #221a52); color: white; border-radius: 20px; padding: 25px; box-shadow: 0 10px 25px rgba(59,48,134,0.25); position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; min-height: 210px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; z-index: 2;">
                            <div>
                                <span style="font-size: 10px; background: rgba(255,255,255,0.25); padding: 4px 10px; border-radius: 20px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">${comp.type}</span>
                                <h3 style="font-size: 18px; margin-top: 10px; font-weight: 600; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">${comp.name}</h3>
                            </div>
                            <div style="display: flex; gap: 6px;">
                                <button onclick="openEditCompanyModal(${comp.id})" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; transition: 0.2s;" title="Edit Kartu" onmouseover="this.style.background='rgba(255,255,255,0.4)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'"><i class="fa-solid fa-pen" style="font-size: 12px;"></i></button>
                                <button onclick="deleteCompany(${comp.id})" style="background: rgba(255,75,87,0.35); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; transition: 0.2s;" title="Hapus Kartu" onmouseover="this.style.background='rgba(255,75,87,0.7)'" onmouseout="this.style.background='rgba(255,75,87,0.35)'"><i class="fa-solid fa-trash" style="font-size: 12px;"></i></button>
                            </div>
                        </div>

                        <div style="position: absolute; right: 25px; bottom: 70px; opacity: 0.15; font-size: 55px; pointer-events: none;">
                            <i class="fa-solid fa-credit-card"></i>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 25px; background: rgba(0,0,0,0.18); backdrop-filter: blur(5px); padding: 12px; border-radius: 12px; z-index: 2;">
                            <div>
                                <span style="font-size: 9px; opacity: 0.8; display: block; letter-spacing: 0.5px;" data-i18n="assetsText">ASET</span>
                                <span style="font-size: 12px; font-weight: 600;">$${Number(comp.assets).toLocaleString()}</span>
                            </div>
                            <div>
                                <span style="font-size: 9px; opacity: 0.8; display: block; letter-spacing: 0.5px;" data-i18n="liabText">LIABILITAS</span>
                                <span style="font-size: 12px; font-weight: 600;">$${Number(comp.liabilities).toLocaleString()}</span>
                            </div>
                            <div>
                                <span style="font-size: 9px; opacity: 0.8; display: block; letter-spacing: 0.5px;" data-i18n="equityText">MODAL BERSIH</span>
                                <span style="font-size: 12px; font-weight: 700; color: #a3ffb8;">$${equity.toLocaleString()}</span>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; font-size: 11px; z-index: 2;">
                            <span style="opacity: 0.9;"><i class="fa-solid fa-users"></i> ${teamOnlyCount} <span data-i18n="teamConnected">Tim Terhubung</span></span>
                            <button onclick="openManageTeamModal()" style="background: white; color: var(--text-dark); border: none; padding: 6px 14px; border-radius: 10px; font-weight: 600; cursor: pointer; font-size: 11px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);" data-i18n="manageTeamBtn">Manage Team</button>
                        </div>
                    </div>
                `;
            });

            const netEquityAll = totalAssetsAll - totalLiabAll;

            return `
                <div class="projects-section">
                    <div class="projects-header">
                        <div>
                            <h1><span data-i18n="analyticsTitle">Business Analytics & Kartu Rekening</span> <span class="badge-count">${companiesData.length}</span></h1>
                            <p style="font-size:12px; color:var(--text-light);" data-i18n="analyticsDesc">Kelola aset dan liabilitas bisnis atau perusahaan Anda dalam bentuk kartu rekening multi-akun.</p>
                        </div>
                        <button class="btn-create" onclick="openAddCompanyModal()"><i class="fa-solid fa-plus"></i> <span data-i18n="addCompanyBtn">Tambah Perusahaan</span></button>
                    </div>

                    <div class="summary-stats" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 25px;">
                        <div class="stat-box primary">
                            <h2 style="color:white;">$${netEquityAll.toLocaleString()}</h2>
                            <p data-i18n="netEquityTitle">Total Modal Keseluruhan (Net Equity)</p>
                            <span style="font-size:10px; margin-top:5px; color:rgba(255,255,255,0.8);" data-i18n="netEquitySub">Akumulasi seluruh aset dikurangi liabilitas</span>
                        </div>
                        <div class="stat-box" onclick="openManageTeamModal()" title="Klik untuk pilih atau kelola tim">
                            <h2>${teamOnlyCount}</h2>
                            <p data-i18n="teamCountTitle">Jumlah Tim</p>
                            <span style="font-size:10px; margin-top:5px; color:var(--text-light);" data-i18n="teamCountSub">Buka modal pilihan tim instan</span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; margin-bottom: 25px;">
                        ${cardsHtml || `<p style="grid-column: 1/-1; text-align:center; color:var(--text-light); padding:30px;" data-i18n="noCompanies">Belum ada kartu rekening perusahaan. Silakan klik Tambah Perusahaan.</p>`}
                    </div>
                </div>
            `;
        }

        function renderTimelineView() {
            let html = `
                <div class="projects-section">
                    <div class="projects-header">
                        <div>
                            <h1><span data-i18n="timelineTitle">Jadwal Harian & Milestone</span> <span class="badge-count">${dailySchedules.length}</span></h1>
                            <p style="font-size:12px; color:var(--text-light);" data-i18n="timelineDesc">Kelola agenda kegiatan dan milestone harian Anda dengan border outline warna-warni.</p>
                        </div>
                        <button class="btn-create" onclick="openAddScheduleModal()"><i class="fa-solid fa-plus"></i> <span data-i18n="addScheduleBtn">Tambah Jadwal</span></button>
                    </div>

                    <div style="background:white; border-radius:15px; padding:25px; box-shadow:var(--shadow); margin-top:20px;">
                        <div style="display:flex; flex-direction:column; gap:15px;" id="scheduleListContainer">
            `;

            if(dailySchedules.length === 0) {
                html += `<p style="text-align:center; color:var(--text-light); padding:20px;" data-i18n="noSchedule">Belum ada jadwal harian yang ditambahkan.</p>`;
            } else {
                dailySchedules.sort((a,b) => a.time.localeCompare(b.time)).forEach(sch => {
                    const borderColor = sch.borderColor || '#3b3086';
                    html += `
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:16px 20px; background:#fff; border:2px solid ${borderColor}; border-radius:14px; box-shadow:0 4px 15px rgba(0,0,0,0.02); transition:0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="display:flex; align-items:center; gap:18px;">
                                <div style="background:${borderColor}; color:white; padding:8px 14px; border-radius:10px; font-weight:600; font-size:12px; white-space:nowrap; box-shadow:0 4px 10px ${borderColor}44;">
                                    <i class="fa-regular fa-clock"></i> ${sch.time}
                                </div>
                                <div>
                                    <h4 style="font-size:14px; margin-bottom:4px; color:var(--text-dark);">${sch.activity}</h4>
                                    <span style="font-size:11px; color:var(--text-light); background:#f1f2f6; padding:2px 8px; border-radius:6px; font-weight:500;">${sch.category}</span>
                                </div>
                            </div>
                            <div style="display:flex; gap:10px; align-items:center;">
                                <button onclick="openEditScheduleModal(${sch.id})" style="background:none; border:none; color:var(--text-light); cursor:pointer; font-size:14px; transition:0.2s;" title="Edit Milestone" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-light)'"><i class="fa-solid fa-pen"></i></button>
                                <button onclick="deleteSchedule(${sch.id})" style="background:none; border:none; color:#ff4757; cursor:pointer; font-size:14px;" title="Hapus Jadwal"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    `;
                });
            }
            html += `</div></div></div>`;
            return html;
        }

        function openAddScheduleModal() {
            let html = `
                <div class="form-group">
                    <label data-i18n="schTimeLabel">Waktu Kegiatan (Misal: 08:00 - 09:30)</label>
                    <input type="text" id="schTimeInput" placeholder="09:00 - 10:30">
                </div>
                <div class="form-group">
                    <label data-i18n="schActivityLabel">Nama / Deskripsi Kegiatan</label>
                    <input type="text" id="schActivityInput" placeholder="Meeting dengan klien...">
                </div>
                <div class="form-group">
                    <label data-i18n="schCatLabel">Kategori</label>
                    <select id="schCategoryInput" class="lang-dropdown" style="width:100%;">
                        <option value="Meeting">Meeting</option>
                        <option value="Development">Development</option>
                        <option value="Client">Client</option>
                        <option value="Personal">Personal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label data-i18n="schColorLabel">Pilih Warna Outline Border Milestone</label>
                    <div class="color-picker-group" id="schColorPickerGroup">
                        <div class="color-option selected" style="background:#ff9f43;" onclick="selectSchColor('#ff9f43', this)"></div>
                        <div class="color-option" style="background:#2ecc71;" onclick="selectSchColor('#2ecc71', this)"></div>
                        <div class="color-option" style="background:#3498db;" onclick="selectSchColor('#3498db', this)"></div>
                        <div class="color-option" style="background:#9b59b6;" onclick="selectSchColor('#9b59b6', this)"></div>
                        <div class="color-option" style="background:#e74c3c;" onclick="selectSchColor('#e74c3c', this)"></div>
                        <div class="color-option" style="background:#3b3086;" onclick="selectSchColor('#3b3086', this)"></div>
                    </div>
                    <input type="hidden" id="schBorderColor" value="#ff9f43">
                </div>
                <button class="btn-create" style="width:100%; justify-content:center; padding:12px; margin-top:15px;" onclick="saveNewSchedule()" data-i18n="saveScheduleBtn">Simpan Jadwal Milestone</button>
            `;
            openDynamicModal(t('addSchModalTitle'), html);
        }

        function selectSchColor(colorHex, element) {
            const group = document.getElementById('schColorPickerGroup');
            if(group) group.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            const input = document.getElementById('schBorderColor');
            if(input) input.value = colorHex;
        }

        function saveNewSchedule() {
            const time = document.getElementById('schTimeInput').value.trim();
            const activity = document.getElementById('schActivityInput').value.trim();
            const category = document.getElementById('schCategoryInput').value;
            const borderColor = document.getElementById('schBorderColor').value;

            if(!time || !activity) {
                alert("Waktu dan deskripsi kegiatan wajib diisi!");
                return;
            }

            dailySchedules.push({
                id: Date.now(),
                time,
                activity,
                category,
                borderColor
            });

            persist('schedules', dailySchedules);
            logActivity(`Menambahkan milestone jadwal baru: "${activity}" (${time})`);
            closeSpecificModal('modalDynamic');

            const container = document.getElementById('view-container');
            if(container) container.innerHTML = renderTimelineView();
        }

        function openEditScheduleModal(id) {
            const sch = dailySchedules.find(s => s.id == id);
            if(!sch) return;

            let html = `
                <input type="hidden" id="editSchId" value="${sch.id}">
                <div class="form-group">
                    <label data-i18n="schTimeLabel">Waktu Kegiatan</label>
                    <input type="text" id="editSchTimeInput" value="${sch.time}">
                </div>
                <div class="form-group">
                    <label data-i18n="schActivityLabel">Nama / Deskripsi Kegiatan</label>
                    <input type="text" id="editSchActivityInput" value="${sch.activity}">
                </div>
                <div class="form-group">
                    <label data-i18n="schCatLabel">Kategori</label>
                    <select id="editSchCategoryInput" class="lang-dropdown" style="width:100%;">
                        <option value="Meeting" ${sch.category === 'Meeting' ? 'selected' : ''}>Meeting</option>
                        <option value="Development" ${sch.category === 'Development' ? 'selected' : ''}>Development</option>
                        <option value="Client" ${sch.category === 'Client' ? 'selected' : ''}>Client</option>
                        <option value="Personal" ${sch.category === 'Personal' ? 'selected' : ''}>Personal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label data-i18n="schColorLabel">Pilih Warna Outline Border Milestone</label>
                    <div class="color-picker-group" id="editSchColorPickerGroup">
                        <div class="color-option ${sch.borderColor === '#ff9f43' ? 'selected' : ''}" style="background:#ff9f43;" onclick="selectEditSchColor('#ff9f43', this)"></div>
                        <div class="color-option ${sch.borderColor === '#2ecc71' ? 'selected' : ''}" style="background:#2ecc71;" onclick="selectEditSchColor('#2ecc71', this)"></div>
                        <div class="color-option ${sch.borderColor === '#3498db' ? 'selected' : ''}" style="background:#3498db;" onclick="selectEditSchColor('#3498db', this)"></div>
                        <div class="color-option ${sch.borderColor === '#9b59b6' ? 'selected' : ''}" style="background:#9b59b6;" onclick="selectEditSchColor('#9b59b6', this)"></div>
                        <div class="color-option ${sch.borderColor === '#e74c3c' ? 'selected' : ''}" style="background:#e74c3c;" onclick="selectEditSchColor('#e74c3c', this)"></div>
                        <div class="color-option ${sch.borderColor === '#3b3086' ? 'selected' : ''}" style="background:#3b3086;" onclick="selectEditSchColor('#3b3086', this)"></div>
                    </div>
                    <input type="hidden" id="editSchBorderColor" value="${sch.borderColor || '#ff9f43'}">
                </div>
                <button class="btn-create" style="width:100%; justify-content:center; padding:12px; margin-top:15px;" onclick="saveEditedSchedule()" data-i18n="saveChanges">Simpan Perubahan Milestone</button>
            `;
            openDynamicModal(t('editSchModalTitle'), html);
        }

        function selectEditSchColor(colorHex, element) {
            const group = document.getElementById('editSchColorPickerGroup');
            if(group) group.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            const input = document.getElementById('editSchBorderColor');
            if(input) input.value = colorHex;
        }

        function saveEditedSchedule() {
            const id = document.getElementById('editSchId').value;
            const time = document.getElementById('editSchTimeInput').value.trim();
            const activity = document.getElementById('editSchActivityInput').value.trim();
            const category = document.getElementById('editSchCategoryInput').value;
            const borderColor = document.getElementById('editSchBorderColor').value;

            if(!time || !activity) {
                alert("Waktu dan deskripsi kegiatan wajib diisi!");
                return;
            }

            const sch = dailySchedules.find(s => s.id == id);
            if(sch) {
                sch.time = time;
                sch.activity = activity;
                sch.category = category;
                sch.borderColor = borderColor;

                persist('schedules', dailySchedules);
                logActivity(`Memperbarui milestone: "${activity}"`);
                closeSpecificModal('modalDynamic');

                const container = document.getElementById('view-container');
                if(container) container.innerHTML = renderTimelineView();
            }
        }

        function deleteSchedule(id) {
            dailySchedules = dailySchedules.filter(s => s.id !== id);
            persist('schedules', dailySchedules);
            logActivity("Menghapus jadwal harian");

            const container = document.getElementById('view-container');
            if(container) container.innerHTML = renderTimelineView();
        }

        function renderTasksDropdown() {
            const dropdown = document.getElementById('clipboardDropdown');
            if(!dropdown) return;
            let html = `<div class="dropdown-header" data-i18n="pendingTasks">Tugas Tertunda (${tasksData.filter(t=>!t.completed).length})</div>`;
            
            if(tasksData.length === 0) {
                html += `<div class="dropdown-item" style="color:var(--text-light); justify-content:center;"><span data-i18n="noTasks">Tidak ada tugas</span></div>`;
            } else {
                tasksData.slice(0, 3).forEach(t => {
                    const iconClass = t.completed ? "fa-solid fa-circle-check text-green" : "fa-regular fa-circle";
                    const textStyle = t.completed ? "text-decoration: line-through; color: var(--text-light);" : "";
                    html += `
                        <div class="dropdown-item" style="justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:10px; flex:1;" onclick="toggleTaskCompletion(${t.id}, event)">
                                <i class="${iconClass}" style="cursor:pointer; font-size:14px; color:${t.completed ? 'var(--icon-green)' : 'inherit'}"></i>
                                <span style="${textStyle}">${t.title}</span>
                            </div>
                        </div>
                    `;
                });
            }
            html += `<div class="dropdown-item" style="color:var(--primary); justify-content:center; font-weight:600; border-top:1px solid #eee; margin-top:5px;" onclick="openAllTasksModal()"><i class="fa-solid fa-list-check"></i> <span data-i18n="viewAllTask">View All Task</span></div>`;
            dropdown.innerHTML = html;
        }

        function toggleTaskCompletion(id, event) {
            if(event) event.stopPropagation();
            const task = tasksData.find(t => t.id == id);
            if(task) {
                task.completed = !task.completed;
                persist('tasks', tasksData);
                renderTasksDropdown();
                if(document.getElementById('allTasksModalList')) {
                    openAllTasksModal(); 
                }
            }
        }

        function openAllTasksModal() {
            let html = `
                <div style="margin-bottom:15px; display:flex; gap:10px;">
                    <input type="text" id="newTaskTitleInput" placeholder="${t('addTaskPlaceholder')}" style="flex:1; padding:8px 12px; border:1px solid #ddd; border-radius:8px; font-size:12px; outline:none;">
                    <button class="btn-create" style="padding:8px 15px;" onclick="addNewTask()" data-i18n="addBtn">Tambah</button>
                </div>
                <div style="display:flex; flex-direction:column; gap:8px; max-height:250px; overflow-y:auto;" id="allTasksModalList">
            `;
            if(tasksData.length === 0) {
                html += `<p style="text-align:center; color:#999; padding:15px; font-size:12px;" data-i18n="noTasks">Belum ada tugas.</p>`;
            } else {
                tasksData.forEach(task => {
                    const iconClass = task.completed ? "fa-solid fa-circle-check" : "fa-regular fa-circle";
                    const textStyle = task.completed ? "text-decoration: line-through; color: var(--text-light);" : "";
                    html += `
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:10px; background:#f8f9fa; border:1px solid #eee; border-radius:8px; font-size:12px;">
                            <div style="display:flex; align-items:center; gap:10px; cursor:pointer;" onclick="toggleTaskCompletion(${task.id})">
                                <i class="${iconClass}" style="color:${task.completed ? 'var(--icon-green)' : '#8e94a8'}"></i>
                                <span style="${textStyle}"><b>${task.title}</b></span>
                            </div>
                            <div style="display:flex; gap:8px;">
                                <button onclick="openEditTaskModal(${task.id})" style="background:none; border:none; color:var(--primary); cursor:pointer; font-size:12px;" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                <button onclick="deleteTask(${task.id})" style="background:none; border:none; color:#ff4757; cursor:pointer; font-size:12px;" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    `;
                });
            }
            html += `</div>`;
            openDynamicModal(t('tasksModalTitle'), html);
        }

        function addNewTask() {
            const input = document.getElementById('newTaskTitleInput');
            const title = input ? input.value.trim() : '';
            if(!title) { alert("Nama tugas tidak boleh kosong!"); return; }
            tasksData.push({ id: Date.now(), title, completed: false });
            persist('tasks', tasksData);
            logActivity(`Menambahkan tugas baru: "${title}"`);
            renderTasksDropdown();
            openAllTasksModal();
        }

        function openEditTaskModal(id) {
            const task = tasksData.find(t => t.id == id);
            if(!task) return;
            let html = `
                <div class="form-group">
                    <label data-i18n="taskTitleLabel">Judul Tugas</label>
                    <input type="text" id="editTaskTitleInput" value="${task.title}">
                </div>
                <button class="btn-create" style="width:100%; justify-content:center; padding:12px;" onclick="saveEditedTask(${task.id})" data-i18n="saveChanges">Simpan Perubahan</button>
            `;
            openDynamicModal(t('editTaskModalTitle'), html);
        }

        function saveEditedTask(id) {
            const input = document.getElementById('editTaskTitleInput');
            const title = input ? input.value.trim() : '';
            if(!title) { alert("Judul tugas tidak boleh kosong!"); return; }
            const task = tasksData.find(t => t.id == id);
            if(task) {
                task.title = title;
                persist('tasks', tasksData);
                logActivity(`Memperbarui tugas: "${title}"`);
                renderTasksDropdown();
                openAllTasksModal();
            }
        }

        function deleteTask(id) {
            tasksData = tasksData.filter(t => t.id != id);
            persist('tasks', tasksData);
            logActivity("Menghapus tugas");
            renderTasksDropdown();
            openAllTasksModal();
        }

        function logActivity(actionText) {
            recentActivities.unshift({
                id: Date.now(),
                name: userProfile.name,
                role: userProfile.role,
                text: actionText,
                time: t('justNow'),
                avatar: userProfile.avatar
            });
            persist('activities', recentActivities);
            renderActivities();
        }

        function renderActivities() {
            const container = document.getElementById('activityListContainer');
            if(!container) return;

            let html = '';
            const latest3 = recentActivities.slice(0, 3);
            if(latest3.length === 0) {
                html = `<p style="text-align:center; color:var(--text-light); font-size:12px; padding:10px;" data-i18n="noActivities">Belum ada aktivitas.</p>`;
            } else {
                latest3.forEach(act => {
                    html += `
                        <div class="activity-item" onclick="openDynamicModal('${t('activityDetailTitle')}', '<b>${act.name}</b> (${act.role}) melakukan aktivitas: ${act.text}')">
                            <img src="${act.avatar}" alt="user">
                            <div class="activity-info">
                                <h4>${act.name}</h4>
                                <p>${act.text}</p>
                            </div>
                            <span class="activity-time">${act.time}</span>
                        </div>
                    `;
                });
            }
            container.innerHTML = html;
        }

        function openViewProfileModal() {
            const s = userProfile.socials || {};
            let socialHtml = '<div style="display:flex; gap:15px; justify-content:center; margin-top:20px; font-size:22px;">';
            if(s.instagram) socialHtml += `<a href="${s.instagram}" target="_blank" style="color:#e1306c;" title="Instagram"><i class="fa-brands fa-instagram"></i></a>`;
            if(s.facebook) socialHtml += `<a href="${s.facebook}" target="_blank" style="color:#1877f2;" title="Facebook"><i class="fa-brands fa-facebook"></i></a>`;
            if(s.x) socialHtml += `<a href="${s.x}" target="_blank" style="color:#1da1f2;" title="Twitter"><i class="fa-brands fa-twitter"></i></a>`;
            if(s.tiktok) socialHtml += `<a href="${s.tiktok}" target="_blank" style="color:#000;" title="TikTok"><i class="fa-brands fa-tiktok"></i></a>`;
            if(s.youtube) socialHtml += `<a href="${s.youtube}" target="_blank" style="color:#ff0000;" title="YouTube"><i class="fa-brands fa-youtube"></i></a>`;
            if(s.github) socialHtml += `<a href="${s.github}" target="_blank" style="color:#333;" title="GitHub"><i class="fa-brands fa-github"></i></a>`;
            if(s.others) socialHtml += `<a href="${s.others}" target="_blank" style="color:var(--primary);" title="Website Lainnya"><i class="fa-solid fa-globe"></i></a>`;
            socialHtml += '</div>';

            let html = `
                <div align="center">
                    <img src="${userProfile.avatar}" style="border-radius:50%; width:100px; height:100px; object-fit:cover; border:3px solid var(--primary);"><br><br>
                    <h3 style="font-size:18px;">${userProfile.name}</h3>
                    <p style="color:var(--text-light); font-size:13px; margin-top:5px;">${userProfile.role}</p>
                    <p style="color:var(--text-light); font-size:12px; margin-top:3px;">@${userProfile.username || 'username'}</p>
                    ${socialHtml}
                </div>
            `;
            openDynamicModal(t('profileModalTitle'), html);
        }

        function updateFinanceDisplay() {
            let totalIn = 0;
            let totalOut = 0;
            cashFlowData.forEach(item => {
                if(item.type === 'income') totalIn += Number(item.amount);
                else totalOut += Number(item.amount);
            });

            const inEl = document.getElementById('cfInDisplay');
            const outEl = document.getElementById('cfOutDisplay');
            const netEl = document.getElementById('cfNetDisplay');

            if(inEl) inEl.innerText = '$' + totalIn.toLocaleString();
            if(outEl) outEl.innerText = '-$' + totalOut.toLocaleString();
            if(netEl) netEl.innerText = '$' + (totalIn - totalOut).toLocaleString();

            const totalFlow = totalIn + totalOut;
            let incomePercent = 50;
            let expensePercent = 50;
            if(totalFlow > 0) {
                incomePercent = Math.round((totalIn / totalFlow) * 100);
                expensePercent = 100 - incomePercent;
            }

            const pieEl = document.querySelector('.pie-chart');
            if(pieEl) {
                pieEl.style.background = `conic-gradient(var(--icon-green) 0% ${incomePercent}%, var(--icon-red) ${incomePercent}% 100%)`;
            }
            if(document.getElementById('cfInPercent')) document.getElementById('cfInPercent').innerText = incomePercent + '%';
            if(document.getElementById('cfOutPercent')) document.getElementById('cfOutPercent').innerText = expensePercent + '%';
        }

        function openCashFlowModal() {
            const currentYear = new Date().getFullYear();

            let html = `
                <div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
                    <div style="flex:1;">
                        <label style="font-size:11px; color:var(--text-light);" data-i18n="filterMonth">Filter Bulan</label>
                        <select id="cfFilterMonth" class="lang-dropdown" style="width:100%;" onchange="renderCashFlowList()">
                            <option value="all" data-i18n="allMonths">Semua Bulan</option>
                            <option value="01">Januari</option>
                            <option value="02">Februari</option>
                            <option value="03">Maret</option>
                            <option value="04">April</option>
                            <option value="05">Mei</option>
                            <option value="06">Juni</option>
                            <option value="07">Juli</option>
                            <option value="08" selected>Augustus</option>
                            <option value="09">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label style="font-size:11px; color:var(--text-light);" data-i18n="filterYear">Filter Tahun</label>
                        <select id="cfFilterYear" class="lang-dropdown" style="width:100%;" onchange="renderCashFlowList()">
                            <option value="all" data-i18n="allYears">Semua Tahun</option>
                            <option value="${currentYear}" selected>${currentYear}</option>
                            <option value="${currentYear - 1}">${currentYear - 1}</option>
                        </select>
                    </div>
                </div>

                <div id="cfSummaryBox" style="background:#f8f9fa; padding:15px; border-radius:12px; margin-bottom:20px; display:flex; justify-content:space-around; text-align:center;"></div>

                <div style="background:#fafafa; border:1px solid #eee; padding:15px; border-radius:12px; margin-bottom:20px;">
                    <h3 style="font-size:13px; margin-bottom:10px; color:var(--primary);" data-i18n="addCashFlowTitle">Tambah Arus Kas Baru</h3>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:10px;">
                        <select id="cfNewType" class="lang-dropdown" style="width:100%;">
                            <option value="income" data-i18n="incomeOption">Pemasukan (Income)</option>
                            <option value="expense" data-i18n="expenseOption">Pengeluaran (Expense)</option>
                        </select>
                        <input type="number" id="cfNewAmount" placeholder="Nominal ($)" style="padding:8px 12px; border:1px solid #ddd; border-radius:8px; font-size:12px; outline:none;">
                    </div>
                    <div style="display:flex; gap:10px; margin-bottom:10px;">
                        <input type="text" id="cfNewDesc" placeholder="${t('cfDescPlaceholder')}" style="flex:1; padding:8px 12px; border:1px solid #ddd; border-radius:8px; font-size:12px; outline:none;">
                        <input type="date" id="cfNewDate" value="${new Date().toISOString().split('T')[0]}" style="padding:8px 12px; border:1px solid #ddd; border-radius:8px; font-size:12px; outline:none;">
                    </div>
                    <button class="btn-create" style="width:100%; justify-content:center; padding:10px;" onclick="addCashFlowItem()" data-i18n="addTransactionBtn">Tambah Transaksi</button>
                </div>

                <h3 style="font-size:13px; margin-bottom:10px;" data-i18n="transHistoryTitle">Riwayat Transaksi</h3>
                <div id="cfTransactionsList" style="max-height:220px; overflow-y:auto; display:flex; flex-direction:column; gap:8px;"></div>
            `;

            openDynamicModal("Manajemen Cash Flow Online Payments", html);
            renderCashFlowList();
        }

        function renderCashFlowList() {
            const monthFilter = document.getElementById('cfFilterMonth').value;
            const yearFilter = document.getElementById('cfFilterYear').value;
            const listContainer = document.getElementById('cfTransactionsList');
            const summaryBox = document.getElementById('cfSummaryBox');
            if(!listContainer) return;

            let filtered = cashFlowData.filter(item => {
                const parts = (item.date || '').split('-');
                if(parts.length < 2) return false;
                const [itemYear, itemMonth] = parts;
                const matchMonth = monthFilter === 'all' || itemMonth === monthFilter;
                const matchYear = yearFilter === 'all' || itemYear === yearFilter;
                return matchMonth && matchYear;
            });

            let totalIncome = 0;
            let totalExpense = 0;

            let html = '';
            if(filtered.length === 0) {
                html = `<p style="text-align:center; color:#999; padding:15px; font-size:12px;" data-i18n="noTransactions">Tidak ada data transaksi pada periode ini.</p>`;
            } else {
                filtered.forEach(item => {
                    if(item.type === 'income') totalIncome += Number(item.amount);
                    else totalExpense += Number(item.amount);

                    const badgeColor = item.type === 'income' ? 'var(--icon-green)' : '#ff4757';
                    const sign = item.type === 'income' ? '+' : '-';

                    html += `
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:10px; background:#fff; border:1px solid #eee; border-radius:10px; font-size:12px;">
                            <div>
                                <b>${item.desc}</b><br>
                                <span style="color:var(--text-light); font-size:10px;">${item.date}</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:15px;">
                                <span style="color:${badgeColor}; font-weight:600;">${sign}$${Number(item.amount).toLocaleString()}</span>
                                <button onclick="deleteCashFlowItem(${item.id})" style="background:none; border:none; color:#ff4757; cursor:pointer; font-size:12px;"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    `;
                });
            }
            listContainer.innerHTML = html;

            let netBalance = totalIncome - totalExpense;
            if(summaryBox) {
                summaryBox.innerHTML = `
                    <div>
                        <span style="font-size:11px; color:var(--text-light);" data-i18n="incomeLabel">Pemasukan</span>
                        <h4 style="color:var(--icon-green); font-size:15px;">+$${totalIncome.toLocaleString()}</h4>
                    </div>
                    <div>
                        <span style="font-size:11px; color:var(--text-light);" data-i18n="expenseLabel">Pengeluaran</span>
                        <h4 style="color:#ff4757; font-size:15px;">-$${totalExpense.toLocaleString()}</h4>
                    </div>
                    <div>
                        <span style="font-size:11px; color:var(--text-light);" data-i18n="netLabel">Net Balance</span>
                        <h4 style="color:var(--primary); font-size:15px;">$${netBalance.toLocaleString()}</h4>
                    </div>
                `;
            }
        }

        function addCashFlowItem() {
            const type = document.getElementById('cfNewType').value;
            const amount = document.getElementById('cfNewAmount').value;
            const desc = document.getElementById('cfNewDesc').value.trim();
            const date = document.getElementById('cfNewDate').value;

            if(!amount || !desc || !date) {
                alert("Semua field transaksi harus diisi!");
                return;
            }

            cashFlowData.unshift({
                id: Date.now(),
                type,
                amount: parseFloat(amount),
                desc,
                date
            });

            syncLocalStorage();
            logActivity(`Menambahkan transaksi ${type === 'income' ? 'Pemasukan' : 'Pengeluaran'} senilai $${amount}`);
            renderCashFlowList();
            updateFinanceDisplay();
            
            document.getElementById('cfNewAmount').value = '';
            document.getElementById('cfNewDesc').value = '';
        }

        function deleteCashFlowItem(id) {
            cashFlowData = cashFlowData.filter(item => item.id !== id);
            syncLocalStorage();
            logActivity("Menghapus entri transaksi cash flow");
            renderCashFlowList();
            updateFinanceDisplay();
        }

        function openAllHistoryModal() {
            let html = `<p style="margin-bottom:15px; color:var(--text-light);" data-i18n="allHistoryDesc">Daftar seluruh riwayat aktivitas sistem:</p>`;
            html += `<div style="display:flex; flex-direction:column; gap:10px; max-height:300px; overflow-y:auto; margin-bottom:15px;">`;
            
            if(recentActivities.length === 0) {
                html += `<p style="text-align:center; color:#999; padding:20px;" data-i18n="noActivities">Tidak ada riwayat aktivitas.</p>`;
            } else {
                recentActivities.forEach(act => {
                    html += `
                        <div style="display:flex; align-items:center; gap:10px; padding:10px; background:#f8f9fa; border-radius:10px;">
                            <img src="${act.avatar}" style="width:30px; height:30px; border-radius:50%; object-fit:cover;">
                            <div style="flex:1; font-size:12px;">
                                <b>${act.name}</b>: ${act.text} <br><span style="color:var(--text-light); font-size:10px;">${act.time}</span>
                            </div>
                            <button onclick="deleteActivity(${act.id})" style="background:none; border:none; color:#ff4757; cursor:pointer; font-size:13px;" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    `;
                });
            }
            html += `</div>`;
            if(recentActivities.length > 0) {
                html += `<button class="btn-create" style="background:#ff4757; width:100%; justify-content:center; padding:10px;" onclick="clearAllActivities()" data-i18n="clearHistoryBtn">Hapus Semua Riwayat</button>`;
            }
            openDynamicModal(t('allHistoryTitle'), html);
        }

        function deleteActivity(id) {
            recentActivities = recentActivities.filter(a => a.id !== id);
            persist('activities', recentActivities);
            renderActivities();
            openAllHistoryModal();
        }

        function clearAllActivities() {
            if(confirm("Yakin ingin menghapus seluruh riwayat aktivitas?")) {
                recentActivities = [];
                persist('activities', recentActivities);
                renderActivities();
                openAllHistoryModal();
            }
        }

        function updateDashboardStats() {
            const total = projectsData.length;
            const started = projectsData.filter(p => p.status === 'started').length;
            const inProgress = projectsData.filter(p => p.status === 'approval' || p.status === 'discrepancy').length;
            const completed = projectsData.filter(p => p.status === 'completed').length;

            const elTotal = document.getElementById('statTotal');
            const elStarted = document.getElementById('statStarted');
            const elInProgress = document.getElementById('statInProgress');
            const elCompleted = document.getElementById('statCompleted');

            if(elTotal) elTotal.innerText = total;
            if(elStarted) elStarted.innerText = started;
            if(elInProgress) elInProgress.innerText = inProgress;
            if(elCompleted) elCompleted.innerText = completed;

            const teamCountSpan = document.getElementById('totalTeamCount');
            if(teamCountSpan) teamCountSpan.innerText = teamMembers.length;
        }

        function syncLocalStorage() {
            persist('projects', projectsData);
            persist('team', teamMembers);
            persist('cashflow', cashFlowData);
            persist('tasks', tasksData);
            updateDashboardStats();
            updateFinanceDisplay();
        }

        async function saveUserProfile() {
            const nameEl = document.getElementById('setProfileName');
            const roleEl = document.getElementById('setProfileRole');
            const avatarEl = document.getElementById('setProfileAvatar');
            const recoveryEmailEl = document.getElementById('setProfileRecoveryEmail');
            const recoveryPhoneEl = document.getElementById('setProfileRecoveryPhone');
            const newPasswordEl = document.getElementById('setProfileNewPassword');
            const confirmPasswordEl = document.getElementById('setProfileConfirmPassword');

            if(!nameEl) return;

            const name = nameEl.value.trim();
            const role = roleEl.value.trim();
            const avatarInput = avatarEl.value.trim();
            const recoveryEmail = recoveryEmailEl ? recoveryEmailEl.value.trim() : userProfile.recoveryEmail;
            const recoveryPhone = recoveryPhoneEl ? recoveryPhoneEl.value.trim() : userProfile.recoveryPhone;
            const newPassword = newPasswordEl ? newPasswordEl.value.trim() : '';
            const confirmPassword = confirmPasswordEl ? confirmPasswordEl.value.trim() : '';

            if(!name) { alert("Nama tidak boleh kosong!"); return; }

            if(newPassword && newPassword !== confirmPassword) {
                alert("Password baru dan konfirmasi password tidak cocok!");
                return;
            }
            if(newPassword && newPassword.length < 6) {
                alert("Password baru minimal 6 karakter!");
                return;
            }

            const finalAvatar = avatarInput ? avatarInput : generateAvatar(name);
            const socials = {
                instagram: document.getElementById('setSocialIg').value.trim(),
                facebook: document.getElementById('setSocialFb').value.trim(),
                x: document.getElementById('setSocialX').value.trim(),
                tiktok: document.getElementById('setSocialTiktok').value.trim(),
                youtube: document.getElementById('setSocialYt').value.trim(),
                github: document.getElementById('setSocialGh').value.trim(),
                others: document.getElementById('setSocialOther').value.trim()
            };

            // Password baru (kalau diisi) dihash & disimpan di server lewat API,
            // TIDAK PERNAH disimpan di object userProfile / localStorage di sisi client.
            const res = await fetch('api/profile.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name, role, avatar: finalAvatar,
                    recoveryEmail, recoveryPhone, socials,
                    newPassword: newPassword || undefined,
                    csrf: CSRF_TOKEN
                })
            });
            const data = await res.json();
            if(!data.ok) {
                alert(data.error || "Gagal menyimpan profil.");
                return;
            }

            userProfile = { ...userProfile, name, role: role || 'Developer', recoveryEmail, recoveryPhone, avatar: data.avatar || finalAvatar, socials };
            if(newPasswordEl) newPasswordEl.value = '';
            if(confirmPasswordEl) confirmPasswordEl.value = '';

            if(newPassword) logActivity("Mengubah password akun");
            updateProfileUI();
            logActivity("Memperbarui informasi profil, keamanan & media sosial");
            alert("Profil dan keamanan akun berhasil diperbarui!");

            const container = document.getElementById('view-container');
            if(container) container.innerHTML = renderSettingsView();
        }

        function updateProfileUI() {
            const headerName = document.getElementById('headerUserName');
            if(headerName) {
                headerName.innerHTML = `${userProfile.name} <i class="fa-solid fa-chevron-down" style="font-size: 10px; margin-left: 5px;"></i>`;
            }
            const headerAvatar = document.getElementById('headerUserAvatar');
            if(headerAvatar) {
                headerAvatar.src = userProfile.avatar;
            }
            const dropdownHello = document.getElementById('dropdownHelloUser');
            if(dropdownHello) {
                dropdownHello.innerText = `${t('helloUserPrefix')} ${userProfile.name}!`;
            }
        }

        function switchViewToSettings() {
            document.querySelectorAll('.sidebar .menu-item').forEach(i => i.classList.remove('active'));
            const settingsMenu = document.querySelector('.sidebar .menu-item[data-view="settings"]');
            if(settingsMenu) settingsMenu.classList.add('active');

            const container = document.getElementById('view-container');
            if(container) {
                container.innerHTML = renderSettingsView();
                applyTranslations();
            }
        }

        let currentLang = localStorage.getItem('dashboard_lang') || 'id';

        const translations = {
            id: {
                projects: "Proyek",
                createProject: "Buat Proyek",
                all: "Semua",
                started: "Dimulai",
                approval: "Persetujuan",
                discrepancy: "Discrepancy",
                completed: "Selesai",
                teamAndClients: "Tim & Klien",
                addMemberOrClient: "Tambah Anggota / Klien",
                searchPlaceholder: "Cari nama atau peran...",
                nameLabel: "Nama Lengkap",
                roleLabel: "Peran / Keterangan",
                typeLabel: "Tipe Peran",
                teamOption: "Tim",
                clientOption: "Klien",
                prospectOption: "Prospek",
                avatarUrl: "URL Foto Avatar",
                saveMember: "Simpan Entitas",
                assignTeam: "Tambah Tim / Klien",
                editProject: "Edit Proyek",
                delete: "Hapus",
                saveProject: "Simpan Proyek",
                projectName: "Nama Proyek",
                clientName: "Klien / Perusahaan",
                statusCategory: "Status Kategori",
                progressPercent: "Progress (%)",
                deadlineLabel: "Deadline (WIB / Asia/Jakarta)",
                pickColor: "Pilih Warna Kartu",
                pickIcon: "Pilih Simbol Icon",
                pendingTasks: "Tugas Tertunda",
                latestNotif: "Notifikasi Terbaru",
                helloUserPrefix: "Halo,",
                viewProfile: "Lihat Profil",
                settings: "Pengaturan",
                logout: "Keluar",
                onlinePayments: "Online payments",
                visaCards: "Visa cards",
                recentActivities: "Recent Activities",
                totalProject: "Total Project",
                upcoming: "Upcoming",
                inProgress: "In Progress",
                completedStat: "Completed",
                noTeam: "Belum ada tim",
                noProjects: "Tidak ada proyek pada kategori ini.",
                galleryTitle: "Galeri Gambar Proyek",
                addPhotoBtn: "Tambah Gambar Baru",
                noPhotos: "Belum ada gambar yang diunggah.",
                discussionTitle: "Diskusi & Komentar Proyek",
                noComments: "Belum ada komentar.",
                commentPlaceholder: "Tulis komentar...",
                sendBtn: "Kirim",
                profileSettings: "Pengaturan Profil",
                saveChanges: "Simpan Perubahan",
                page: "Halaman",
                accountInfo: "Informasi Akun",
                securityInfo: "Keamanan Akun",
                usernameLabel: "Username",
                recoveryEmailLabel: "Email Pemulihan",
                recoveryPhoneLabel: "No HP Pemulihan",
                changePasswordLabel: "Ganti Password",
                passwordPlaceholder: "Masukkan password baru (kosongkan jika tetap)",
                socialMediaLinks: "Social Media Links",
                messagesTitle: "Messages (Pesan Masuk)",
                discussionsTitle: "Discussions & Cuitan Thread",
                tweetPlaceholder: "Apa yang sedang hangat dibicarakan di tim?",
                tweetBtn: "Cuitkan",
                justNow: "Baru saja",
                noResultsFor: "Tidak ditemukan hasil untuk",
                projectsHeaderSearch: "PROYEK",
                manageTeamDesc: "Kelola anggota tim secara cepat langsung dari sini:",
                addBtn: "Tambah",
                openFullTeam: "Buka Full Team Table View",
                compNameLabel: "Nama Perusahaan / Bisnis",
                compTypeLabel: "Tipe / Bidang Usaha",
                assetsLabel: "Total Aset ($)",
                liabLabel: "Total Liabilitas / Hutang ($)",
                saveCompany: "Simpan Kartu Bisnis",
                addCompanyModalTitle: "Tambah Perusahaan / Kartu Rekening Baru",
                editCompanyModalTitle: "Edit Kartu Rekening Perusahaan",
                assetsText: "ASET",
                liabText: "LIABILITAS",
                equityText: "MODAL BERSIH",
                teamConnected: "Tim Terhubung",
                manageTeamBtn: "Manage Team",
                analyticsTitle: "Business Analytics & Kartu Rekening",
                analyticsDesc: "Kelola aset dan liabilitas bisnis atau perusahaan Anda dalam bentuk kartu rekening multi-akun.",
                addCompanyBtn: "Tambah Perusahaan",
                netEquityTitle: "Total Modal Keseluruhan (Net Equity)",
                netEquitySub: "Akumulasi seluruh aset dikurangi liabilitas",
                teamCountTitle: "Jumlah Tim",
                teamCountSub: "Buka modal pilihan tim instan",
                noCompanies: "Belum ada kartu rekening perusahaan. Silakan klik Tambah Perusahaan.",
                timelineTitle: "Jadwal Harian & Milestone",
                timelineDesc: "Kelola agenda kegiatan dan milestone harian Anda dengan border outline warna-warni.",
                addScheduleBtn: "Tambah Jadwal",
                noSchedule: "Belum ada jadwal harian yang ditambahkan.",
                schTimeLabel: "Waktu Kegiatan (Misal: 08:00 - 09:30)",
                schActivityLabel: "Nama / Deskripsi Kegiatan",
                schCatLabel: "Kategori",
                schColorLabel: "Pilih Warna Outline Border Milestone",
                saveScheduleBtn: "Simpan Jadwal Milestone",
                addSchModalTitle: "Tambah Jadwal / Milestone Baru",
                editSchModalTitle: "Edit Jadwal / Milestone",
                noTasks: "Tidak ada tugas",
                viewAllTask: "View All Task",
                addTaskPlaceholder: "Tambah tugas baru...",
                tasksModalTitle: "Manajemen Semua Tugas (View All Task)",
                taskTitleLabel: "Judul Tugas",
                editTaskModalTitle: "Edit Tugas",
                noActivities: "Belum ada aktivitas.",
                activityDetailTitle: "Detail Aktivitas",
                profileModalTitle: "Profil Saya",
                filterMonth: "Filter Bulan",
                allMonths: "Semua Bulan",
                filterYear: "Filter Tahun",
                allYears: "Semua Tahun",
                addCashFlowTitle: "Tambah Arus Kas Baru",
                incomeOption: "Pemasukan (Income)",
                expenseOption: "Pengeluaran (Expense)",
                cfDescPlaceholder: "Keterangan transaksi...",
                addTransactionBtn: "Tambah Transaksi",
                transHistoryTitle: "Riwayat Transaksi",
                noTransactions: "Tidak ada data transaksi pada periode ini.",
                incomeLabel: "Income",
                expenseLabel: "Expense",
                netLabel: "Net",
                allHistoryDesc: "Daftar seluruh riwayat aktivitas sistem:",
                clearHistoryBtn: "Hapus Semua Riwayat",
                allHistoryTitle: "Semua Riwayat Aktivitas"
            },
            en: {
                projects: "Projects",
                createProject: "Create Project",
                all: "All",
                started: "Started",
                approval: "Approval",
                discrepancy: "Discrepancy",
                completed: "Completed",
                teamAndClients: "Team & Clients",
                addMemberOrClient: "Add Member / Client",
                searchPlaceholder: "Search name or role...",
                nameLabel: "Full Name",
                roleLabel: "Role / Description",
                typeLabel: "Role Type",
                teamOption: "Team",
                clientOption: "Client",
                prospectOption: "Prospect",
                avatarUrl: "Avatar Photo URL",
                saveMember: "Save Entity",
                assignTeam: "Assign Team / Client",
                editProject: "Edit Project",
                delete: "Delete",
                saveProject: "Save Project",
                projectName: "Project Name",
                clientName: "Client / Company",
                statusCategory: "Status Category",
                progressPercent: "Progress (%)",
                deadlineLabel: "Deadline (WIB / Asia/Jakarta)",
                pickColor: "Select Card Color",
                pickIcon: "Select Icon",
                pendingTasks: "Pending Tasks",
                latestNotif: "Latest Notifications",
                helloUserPrefix: "Hello,",
                viewProfile: "View Profile",
                settings: "Settings",
                logout: "Logout",
                onlinePayments: "Online payments",
                visaCards: "Visa cards",
                recentActivities: "Recent Activities",
                totalProject: "Total Project",
                upcoming: "Upcoming",
                inProgress: "In Progress",
                completedStat: "Completed",
                noTeam: "No team assigned",
                noProjects: "No projects in this category.",
                galleryTitle: "Project Image Gallery",
                addPhotoBtn: "Add New Image",
                noPhotos: "No images uploaded yet.",
                discussionTitle: "Project Discussion",
                noComments: "No comments yet.",
                commentPlaceholder: "Write a comment...",
                sendBtn: "Send",
                profileSettings: "Profile Settings",
                saveChanges: "Save Changes",
                page: "Page",
                accountInfo: "Account Information",
                securityInfo: "Account Security",
                usernameLabel: "Username",
                recoveryEmailLabel: "Recovery Email",
                recoveryPhoneLabel: "Recovery Phone",
                changePasswordLabel: "Change Password",
                passwordPlaceholder: "Enter new password (leave blank to keep)",
                socialMediaLinks: "Social Media Links",
                messagesTitle: "Messages",
                discussionsTitle: "Discussions & Thread",
                tweetPlaceholder: "What's hot in the team right now?",
                tweetBtn: "Tweet",
                justNow: "Just now",
                noResultsFor: "No results found for",
                projectsHeaderSearch: "PROJECTS",
                manageTeamDesc: "Manage team members quickly directly from here:",
                addBtn: "Add",
                openFullTeam: "Open Full Team Table View",
                compNameLabel: "Company / Business Name",
                compTypeLabel: "Business Type / Sector",
                assetsLabel: "Total Assets ($)",
                liabLabel: "Total Liabilities / Debt ($)",
                saveCompany: "Save Business Card",
                addCompanyModalTitle: "Add New Company / Account Card",
                editCompanyModalTitle: "Edit Company Account Card",
                assetsText: "ASSETS",
                liabText: "LIABILITIES",
                equityText: "NET EQUITY",
                teamConnected: "Team Connected",
                manageTeamBtn: "Manage Team",
                analyticsTitle: "Business Analytics & Account Cards",
                analyticsDesc: "Manage your business assets and liabilities in multi-account cards format.",
                addCompanyBtn: "Add Company",
                netEquityTitle: "Total Net Equity",
                netEquitySub: "Accumulation of all assets minus liabilities",
                teamCountTitle: "Team Count",
                teamCountSub: "Open instant team selection modal",
                noCompanies: "No company account cards yet. Click Add Company.",
                timelineTitle: "Daily Schedule & Milestones",
                timelineDesc: "Manage your daily activities and milestones with colorful border outlines.",
                addScheduleBtn: "Add Schedule",
                noSchedule: "No daily schedules added yet.",
                schTimeLabel: "Activity Time (Ex: 08:00 - 09:30)",
                schActivityLabel: "Activity Name / Description",
                schCatLabel: "Category",
                schColorLabel: "Select Milestone Border Outline Color",
                saveScheduleBtn: "Save Milestone Schedule",
                addSchModalTitle: "Add New Schedule / Milestone",
                editSchModalTitle: "Edit Schedule / Milestone",
                noTasks: "No tasks",
                viewAllTask: "View All Task",
                addTaskPlaceholder: "Add new task...",
                tasksModalTitle: "Manage All Tasks",
                taskTitleLabel: "Task Title",
                editTaskModalTitle: "Edit Task",
                noActivities: "No activities yet.",
                activityDetailTitle: "Activity Detail",
                profileModalTitle: "My Profile",
                filterMonth: "Filter Month",
                allMonths: "All Months",
                filterYear: "Filter Year",
                allYears: "All Years",
                addCashFlowTitle: "Add New Cash Flow",
                incomeOption: "Income",
                expenseOption: "Expense",
                cfDescPlaceholder: "Transaction description...",
                addTransactionBtn: "Add Transaction",
                transHistoryTitle: "Transaction History",
                noTransactions: "No transaction data for this period.",
                incomeLabel: "Income",
                expenseLabel: "Expense",
                netLabel: "Net",
                allHistoryDesc: "List of all system activity histories:",
                clearHistoryBtn: "Clear All History",
                allHistoryTitle: "All Activity History"
            },
            ja: {
                projects: "プロジェクト",
                createProject: "プロジェクト作成",
                all: "すべて",
                started: "開始",
                approval: "承認",
                discrepancy: "不一致",
                completed: "完了",
                teamAndClients: "チームとクライアント",
                addMemberOrClient: "メンバー / クライアント追加",
                searchPlaceholder: "名前または役割で検索...",
                nameLabel: "氏名",
                roleLabel: "役割 / 詳細",
                typeLabel: "ロールタイプ",
                teamOption: "チーム",
                clientOption: "クライアント",
                prospectOption: "見込み客",
                avatarUrl: "アバター画像URL",
                saveMember: "保存",
                assignTeam: "チーム / クライアント割当",
                editProject: "プロジェクト編集",
                delete: "削除",
                saveProject: "プロジェクト保存",
                projectName: "プロジェクト名",
                clientName: "クライアント / 企業",
                statusCategory: "ステータス",
                progressPercent: "進捗 (%)",
                deadlineLabel: "期限 (WIB / Asia/Jakarta)",
                pickColor: "カードカラー選択",
                pickIcon: "アイコン選択",
                pendingTasks: "保留中のタスク",
                latestNotif: "最新の通知",
                helloUserPrefix: "こんにちは、",
                viewProfile: "プロフィールを見る",
                settings: "設定",
                logout: "ログアウト",
                onlinePayments: "オンライン決済",
                visaCards: "ビザカード",
                recentActivities: "最近のアクティビティ",
                totalProject: "総プロジェクト",
                upcoming: "予定",
                inProgress: "進行中",
                completedStat: "完了",
                noTeam: "チーム未割当",
                noProjects: "このカテゴリのプロジェクトはありません。",
                galleryTitle: "プロジェクト画像ギャラリー",
                addPhotoBtn: "画像を追加",
                noPhotos: "画像がアップロードされていません。",
                discussionTitle: "プロジェクトディスカッション",
                noComments: "コメントはまだありません。",
                commentPlaceholder: "コメントを入力...",
                sendBtn: "送信",
                profileSettings: "プロフィール設定",
                saveChanges: "変更を保存",
                page: "ページ",
                accountInfo: "アカウント情報",
                securityInfo: "アカウントセキュリティ",
                usernameLabel: "ユーザー名",
                recoveryEmailLabel: "復旧用メール",
                recoveryPhoneLabel: "復旧用電話番号",
                changePasswordLabel: "パスワード変更",
                passwordPlaceholder: "新しいパスワード (変更しない場合は空欄)",
                socialMediaLinks: "ソーシャルメディアリンク",
                messagesTitle: "メッセージ",
                discussionsTitle: "ディスカッション & スレッド",
                tweetPlaceholder: "チームで今何が話題ですか？",
                tweetBtn: "ツイート",
                justNow: "たった今",
                noResultsFor: "検索結果が見つかりません:",
                projectsHeaderSearch: "プロジェクト",
                manageTeamDesc: "ここから直接チームメンバーを素早く管理できます:",
                addBtn: "追加",
                openFullTeam: "フルチームテーブルビューを開く",
                compNameLabel: "企業名 / ビジネス名",
                compTypeLabel: "業種 / 分野",
                assetsLabel: "総資産 ($)",
                liabLabel: "総負債 ($)",
                saveCompany: "ビジネスカードを保存",
                addCompanyModalTitle: "新規企業 / 口座カード追加",
                editCompanyModalTitle: "企業口座カード編集",
                assetsText: "資産",
                liabText: "負債",
                equityText: "純資産",
                teamConnected: "接続されたチーム",
                manageTeamBtn: "チーム管理",
                analyticsTitle: "ビジネス分析 & 口座カード",
                analyticsDesc: "マルチアカウントカード形式でビジネスの資産と負債を管理します。",
                addCompanyBtn: "企業追加",
                netEquityTitle: "総純資産 (Net Equity)",
                netEquitySub: "全資産から負債を引いた累積",
                teamCountTitle: "チーム数",
                teamCountSub: "インスタントチーム選択モーダルを開く",
                noCompanies: "企業口座カードがありません。「企業追加」をクリックしてください。",
                timelineTitle: "デイリースケジュール & マイルストーン",
                timelineDesc: "カラフルなボーダーアウトラインで日々の活動やマイルストーンを管理します。",
                addScheduleBtn: "スケジュール追加",
                noSchedule: "デイリースケジュールが追加されていません。",
                schTimeLabel: "活動時間 (例: 08:00 - 09:30)",
                schActivityLabel: "活動名 / 詳細",
                schCatLabel: "カテゴリ",
                schColorLabel: "マイルストーンボーダーアウトラインの色を選択",
                saveScheduleBtn: "マイルストーンスケジュール保存",
                addSchModalTitle: "新規スケジュール / マイルストーン追加",
                editSchModalTitle: "スケジュール / マイルストーン編集",
                noTasks: "タスクなし",
                viewAllTask: "すべてのタスクを表示",
                addTaskPlaceholder: "新しいタスクを追加...",
                tasksModalTitle: "タスク管理",
                taskTitleLabel: "タスクタイトル",
                editTaskModalTitle: "タスク編集",
                noActivities: "アクティビティはまだありません。",
                activityDetailTitle: "アクティビティ詳細",
                profileModalTitle: "マイプロフィール",
                filterMonth: "月フィルター",
                allMonths: "すべての月",
                filterYear: "年フィルター",
                allYears: "すべての年",
                addCashFlowTitle: "新規キャッシュフロー追加",
                incomeOption: "収入",
                expenseOption: "支出",
                cfDescPlaceholder: "取引の説明...",
                addTransactionBtn: "取引追加",
                transHistoryTitle: "取引履歴",
                noTransactions: "この期間の取引データはありません。",
                incomeLabel: "収入",
                expenseLabel: "支出",
                netLabel: "純額",
                allHistoryDesc: "すべてのシステムアクティビティ履歴:",
                clearHistoryBtn: "すべての履歴を削除",
                allHistoryTitle: "すべてのアクティビティ履歴"
            }
        };

        function setLanguage(lang) {
            currentLang = lang;
            localStorage.setItem('dashboard_lang', lang);
            const selectEl = document.getElementById('langSelect');
            if(selectEl) selectEl.value = lang;
            applyTranslations();
            renderTasksDropdown();
            
            const activeMenu = document.querySelector('.sidebar .menu-item.active');
            if(activeMenu) {
                const view = activeMenu.getAttribute('data-view');
                const container = document.getElementById('view-container');
                if(!container) return;
                if(view === 'team') {
                    container.innerHTML = renderTeamManagementView();
                    renderTeamTableRows();
                } else if(view === 'projects') {
                    renderProjectsGrid('all');
                } else if(view === 'settings') {
                    container.innerHTML = renderSettingsView();
                } else if(view === 'messages') {
                    container.innerHTML = renderMessagesView();
                } else if(view === 'discussions') {
                    container.innerHTML = renderDiscussionsView();
                } else if(view === 'timeline') {
                    container.innerHTML = renderTimelineView();
                } else if (view === 'analytics') {
                    container.innerHTML = renderAnalyticsView();
                }
            }
        }

        function t(key) {
            return translations[currentLang][key] || translations['en'][key] || key;
        }

        function applyTranslations() {
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if(translations[currentLang] && translations[currentLang][key]) {
                    el.innerText = translations[currentLang][key];
                }
            });
            const searchInput = document.getElementById('teamSearchInput');
            if(searchInput) searchInput.placeholder = t('searchPlaceholder');
        }

        function calculateTimeLeft(deadlineString, status) {
            if (status === 'completed') return { text: "Done", class: "text-green" };
            if (!deadlineString) return { text: "No Deadline", class: "text-blue" };

            const todayStr = new Date().toLocaleString('en-US', { timeZone: 'Asia/Jakarta' });
            const todayDate = new Date(todayStr);
            todayDate.setHours(0, 0, 0, 0);

            const deadlineDate = new Date(deadlineString);
            deadlineDate.setHours(0, 0, 0, 0);

            const diffTime = deadlineDate - todayDate;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays < 0) {
                return { text: "Overdue (" + Math.abs(diffDays) + "d ago)", class: "text-red" };
            } else if (diffDays === 0) {
                return { text: "Due Today", class: "text-orange" };
            } else if (diffDays === 1) {
                return { text: "1 Day left", class: "text-orange" };
            } else if (diffDays <= 7) {
                return { text: diffDays + " Days left", class: "text-orange" };
            } else if (diffDays <= 14) {
                return { text: "2 Weeks left", class: "text-green" };
            } else {
                const weeks = Math.floor(diffDays / 7);
                return { text: weeks + " Weeks left", class: "text-green" };
            }
        }

        function toggleSidebar() {
            const dashboard = document.getElementById('mainDashboard');
            if(!dashboard) return;
            if(window.innerWidth <= 768) {
                dashboard.classList.toggle('sidebar-visible');
                const backdrop = document.getElementById('mobileBackdrop');
                if(backdrop) backdrop.classList.toggle('show');
            } else {
                dashboard.classList.toggle('sidebar-hidden');
            }
        }

        function toggleRightPanel() {
            const panel = document.getElementById('rightPanel');
            const backdrop = document.getElementById('mobileBackdrop');
            if(panel) panel.classList.toggle('show');
            if(backdrop) backdrop.classList.add('show');
        }

        function closeAllDrawers() {
            const dashboard = document.getElementById('mainDashboard');
            const rightPanel = document.getElementById('rightPanel');
            const backdrop = document.getElementById('mobileBackdrop');
            if(dashboard) dashboard.classList.remove('sidebar-visible');
            if(rightPanel) rightPanel.classList.remove('show');
            if(backdrop) backdrop.classList.remove('show');
        }

        function toggleDropdown(id, event) {
            if(event) event.stopPropagation();
            const allDropdowns = document.querySelectorAll('.dropdown-menu');
            const target = document.getElementById(id);
            allDropdowns.forEach(dd => { if(dd.id !== id) dd.classList.remove('active'); });
            if(target) target.classList.toggle('active');
        }

        document.addEventListener('click', () => {
            document.querySelectorAll('.dropdown-menu').forEach(dd => dd.classList.remove('active'));
        });

        function openSearchModal() {
            const modal = document.getElementById('modalSearch');
            if(modal) modal.classList.add('show');
            setTimeout(() => {
                const searchInput = document.getElementById('searchInput');
                if(searchInput) {
                    searchInput.value = "";
                    searchInput.focus();
                }
                const box = document.getElementById('searchResultsBox');
                if(box) {
                    box.style.display = 'none';
                    box.innerHTML = '';
                }
            }, 100);
        }

        function openDynamicModal(title, contentHTML) {
            const titleEl = document.getElementById('dynamicTitle');
            const bodyEl = document.getElementById('dynamicBody');
            const modal = document.getElementById('modalDynamic');
            if(titleEl) titleEl.innerText = title;
            if(bodyEl) bodyEl.innerHTML = contentHTML;
            if(modal) modal.classList.add('show');
        }

        function closeModals(event) {
            if(event.target.classList.contains('modal-overlay')) {
                document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('show'));
            }
        }
        function closeSpecificModal(id) {
            const el = document.getElementById(id);
            if(el) el.classList.remove('show');
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('show'));
            }
        });

        function openImageGallery(projectId, event) {
            if(event) event.stopPropagation();
            const p = projectsData.find(item => item.id == projectId);
            if(!p) return;

            let html = `<p style="margin-bottom:15px; color:var(--text-light);">${t('galleryTitle')}: <b>${p.title}</b></p>`;
            if(!p.images || p.images.length === 0) {
                html += `<p style="text-align:center; padding:20px; color:#999;">${t('noPhotos')}</p>`;
            } else {
                html += `<div class="gallery-grid">`;
                p.images.forEach(imgUrl => {
                    html += `<div class="gallery-item" style="background-image: url('${imgUrl}');"></div>`;
                });
                html += `</div>`;
            }

            html += `
                <input type="file" id="galleryFileInput_${p.id}" accept="image/png,image/jpeg,image/webp,image/gif" style="display:none;" onchange="uploadProjectImage(${p.id}, this)">
                <button class="btn-create" style="margin-top:20px; width:100%; justify-content:center;" onclick="document.getElementById('galleryFileInput_${p.id}').click()"><i class="fa-solid fa-upload"></i> ${t('addPhotoBtn')}</button>
                <p id="galleryUploadStatus_${p.id}" style="font-size:11px; color:var(--text-light); text-align:center; margin-top:8px;"></p>
            `;
            openDynamicModal(t('galleryTitle'), html);
        }

        async function uploadProjectImage(projectId, inputEl) {
            const file = inputEl.files && inputEl.files[0];
            if(!file) return;
            const p = projectsData.find(item => item.id == projectId);
            if(!p) return;

            const statusEl = document.getElementById(`galleryUploadStatus_${projectId}`);
            if(statusEl) statusEl.innerText = 'Mengupload foto...';

            const formData = new FormData();
            formData.append('photo', file);
            formData.append('csrf', CSRF_TOKEN);

            try {
                const res = await fetch('api/upload.php', { method: 'POST', body: formData });
                const data = await res.json();
                if(!data.ok) {
                    if(statusEl) statusEl.innerText = '';
                    alert(data.error || 'Gagal upload foto.');
                    return;
                }
                if(!p.images) p.images = [];
                p.images.push(data.url);
                syncLocalStorage();
                renderProjectsGrid('all');
                logActivity(`Menambahkan gambar ke proyek "${p.title}"`);
                openImageGallery(projectId, null);
            } catch(err) {
                if(statusEl) statusEl.innerText = '';
                alert('Gagal upload foto, cek koneksi internet.');
            } finally {
                inputEl.value = '';
            }
        }

        function openCommentsModal(projectId, event) {
            if(event) event.stopPropagation();
            const p = projectsData.find(item => item.id == projectId);
            if(!p) return;

            let html = `<p style="margin-bottom:15px; color:var(--text-light);">${t('discussionTitle')}: <b>${p.title}</b></p>`;
            html += `<div class="comment-box-list" id="commentList_${p.id}">`;
            
            if(!p.comments || p.comments.length === 0) {
                html += `<p style="text-align:center; padding:15px; color:#999; font-size:12px;">${t('noComments')}</p>`;
            } else {
                p.comments.forEach(c => {
                    html += `<div class="comment-bubble"><b>${c.user}:</b> ${c.text}</div>`;
                });
            }
            html += `</div>`;

            html += `
                <div style="margin-top:15px; display:flex; gap:10px;">
                    <input type="text" id="newCommentText_${p.id}" placeholder="${t('commentPlaceholder')}" style="flex:1; padding:8px 12px; border:1px solid #ddd; border-radius:8px; font-size:12px; outline:none;" onkeyup="if(event.key==='Enter') postComment(${p.id})">
                    <button class="btn-create" style="padding:8px 15px;" onclick="postComment(${p.id})">${t('sendBtn')}</button>
                </div>
            `;

            openDynamicModal(t('discussionTitle'), html);
        }

        function postComment(projectId) {
            const input = document.getElementById(`newCommentText_${projectId}`);
            const text = input ? input.value.trim() : '';
            if(!text) return;

            const p = projectsData.find(item => item.id == projectId);
            if(p) {
                if(!p.comments) p.comments = [];
                p.comments.push({ user: `${userProfile.name} (Anda)`, text: text });
                syncLocalStorage();
                renderProjectsGrid('all');
                logActivity(`Mengirim komentar pada proyek "${p.title}"`);
                openCommentsModal(projectId, null);
            }
        }

        function openAddTeamModal(projectId, event) {
            if(event) event.stopPropagation();
            const p = projectsData.find(item => item.id == projectId);
            if(!p) return;

            let html = `<p style="margin-bottom:15px; color:var(--text-light);">${t('assignTeam')}: <b>${p.title}</b></p>`;
            html += `<div style="display:flex; flex-direction:column; gap:10px; max-height:250px; overflow-y:auto; margin-bottom:20px;">`;
            
            teamMembers.forEach(member => {
                const isAssigned = p.team && p.team.includes(member.id);
                let roleBadge = `<span class="badge-role badge-team">Team</span>`;
                if(member.type === 'client') roleBadge = `<span class="badge-role badge-client">Client</span>`;
                if(member.type === 'prospect') roleBadge = `<span class="badge-role badge-prospect">Prospect</span>`;

                html += `
                    <label style="display:flex; align-items:center; gap:12px; padding:8px 12px; background:#f8f9fa; border-radius:10px; cursor:pointer;">
                        <input type="checkbox" value="${member.id}" ${isAssigned ? 'checked' : ''} style="width:16px; height:16px;" class="team-checkbox">
                        <img src="${member.avatar}" style="width:30px; height:30px; border-radius:50%; object-fit:cover;">
                        <div style="flex:1;">
                            <div style="font-weight:600; font-size:13px; display:flex; justify-content:space-between; align-items:center;">
                                <span>${member.name}</span>
                                ${roleBadge}
                            </div>
                            <div style="font-size:11px; color:var(--text-light);">${member.role}</div>
                        </div>
                    </label>
                `;
            });
            html += `</div>`;
            html += `<button class="btn-create" style="width:100%; justify-content:center; padding:12px;" onclick="saveProjectTeam(${p.id})">${t('saveProject')}</button>`;

            openDynamicModal(t('assignTeam'), html);
        }

        function saveProjectTeam(projectId) {
            const p = projectsData.find(item => item.id == projectId);
            if(!p) return;

            const checkboxes = document.querySelectorAll('.team-checkbox');
            let selectedTeam = [];
            checkboxes.forEach(cb => {
                if(cb.checked) {
                    selectedTeam.push(parseInt(cb.value));
                }
            });

            p.team = selectedTeam;
            syncLocalStorage();
            logActivity(`Memperbarui tim/klien/prospek pada proyek "${p.title}"`);
            closeSpecificModal('modalDynamic');
            renderProjectsGrid('all');
        }

        let currentTeamPage = 1;
        const rowsPerPage = 5;

        function renderTeamManagementView() {
            return `
                <div class="projects-section">
                    <h1>${t('teamAndClients')} <span class="badge-count" id="totalTeamCount">${teamMembers.length}</span></h1>
                    
                    <div class="team-table-container">
                        <div class="team-toolbar">
                            <div class="team-toolbar-left">
                                <input type="text" id="teamSearchInput" class="team-search-input" placeholder="${t('searchPlaceholder')}" onkeyup="filterTeamTable()">
                                <select id="teamTypeFilter" class="lang-dropdown" onchange="filterTeamTable()">
                                    <option value="all" data-i18n="all">Semua Tipe</option>
                                    <option value="team" data-i18n="teamOption">Tim</option>
                                    <option value="client" data-i18n="clientOption">Klien</option>
                                    <option value="prospect" data-i18n="prospectOption">Prospek</option>
                                </select>
                            </div>
                            <button class="btn-create" onclick="openAddTeamMemberModal()"><i class="fa-solid fa-plus"></i> ${t('addMemberOrClient')}</button>
                        </div>
                        
                        <table class="team-table">
                            <thead>
                                <tr>
                                    <th>Avatar</th>
                                    <th>${t('nameLabel')}</th>
                                    <th>${t('roleLabel')}</th>
                                    <th>${t('typeLabel')}</th>
                                    <th style="text-align:right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="teamTableBody"></tbody>
                        </table>

                        <div class="pagination-container">
                            <span id="paginationInfo">Menampilkan 1-5 dari data</span>
                            <div class="pagination-buttons" id="paginationButtons"></div>
                        </div>
                    </div>
                </div>
            `;
        }

        function renderTeamTableRows() {
            const tbody = document.getElementById('teamTableBody');
            const searchInput = document.getElementById('teamSearchInput');
            const typeFilter = document.getElementById('teamTypeFilter');
            if(!tbody) return;

            const keyword = searchInput ? searchInput.value.toLowerCase() : '';
            const selectedType = typeFilter ? typeFilter.value : 'all';
            
            const filtered = teamMembers.filter(m => {
                const name = m.name || '';
                const role = m.role || '';
                const type = m.type || 'team';
                const matchesKeyword = name.toLowerCase().includes(keyword) || role.toLowerCase().includes(keyword);
                const matchesType = selectedType === 'all' || type === selectedType;
                return matchesKeyword && matchesType;
            });

            const totalPages = Math.ceil(filtered.length / rowsPerPage) || 1;
            if(currentTeamPage > totalPages) currentTeamPage = totalPages || 1;

            const startIdx = (currentTeamPage - 1) * rowsPerPage;
            const paginatedData = filtered.slice(startIdx, startIdx + rowsPerPage);

            let html = '';
            if(paginatedData.length === 0) {
                html = `<tr><td colspan="5" style="text-align:center; color:var(--text-light); padding:20px;">No data available.</td></tr>`;
            } else {
                paginatedData.forEach(m => {
                    const typeVal = m.type || 'team';
                    let badgeClass = 'badge-team';
                    let typeLabel = t('teamOption');
                    if(typeVal === 'client') { badgeClass = 'badge-client'; typeLabel = t('clientOption'); }
                    if(typeVal === 'prospect') { badgeClass = 'badge-prospect'; typeLabel = t('prospectOption'); }

                    html += `
                        <tr>
                            <td><img src="${m.avatar}"></td>
                            <td><b>${m.name}</b></td>
                            <td>${m.role}</td>
                            <td><span class="badge-role ${badgeClass}">${typeLabel}</span></td>
                            <td style="text-align:right;">
                                <div style="display:flex; gap:8px; justify-content:flex-end;">
                                    <button onclick="openEditTeamMemberModal(${m.id})" style="background:none; border:none; color:var(--primary); cursor:pointer; font-size:14px;" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                    <button onclick="deleteTeamMember(${m.id})" style="background:none; border:none; color:#ff4757; cursor:pointer; font-size:14px;" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            }
            tbody.innerHTML = html;

            const infoEl = document.getElementById('paginationInfo');
            if(infoEl) {
                infoEl.innerText = `${t('page')} ${currentTeamPage} dari ${totalPages} (Total ${filtered.length} data)`;
            }

            const btnContainer = document.getElementById('paginationButtons');
            if(btnContainer) {
                let btnHtml = `
                    <button class="page-btn" onclick="changeTeamPage(${currentTeamPage - 1})" ${currentTeamPage === 1 ? 'disabled' : ''}><i class="fa-solid fa-chevron-left"></i></button>
                `;
                for(let i=1; i<=totalPages; i++) {
                    btnHtml += `<button class="page-btn ${i === currentTeamPage ? 'active' : ''}" style="${i === currentTeamPage ? 'background:var(--primary); color:white; border-color:var(--primary);' : ''}" onclick="changeTeamPage(${i})">${i}</button>`;
                }
                btnHtml += `
                    <button class="page-btn" onclick="changeTeamPage(${currentTeamPage + 1})" ${currentTeamPage === totalPages ? 'disabled' : ''}><i class="fa-solid fa-chevron-right"></i></button>
                `;
                btnContainer.innerHTML = btnHtml;
            }
        }

        function filterTeamTable() {
            currentTeamPage = 1;
            renderTeamTableRows();
        }

        function changeTeamPage(targetPage) {
            currentTeamPage = targetPage;
            renderTeamTableRows();
        }

        function openAddTeamMemberModal() {
            let html = `
                <div class="form-group"><label>${t('nameLabel')}</label><input type="text" id="newTeamName" placeholder="Nama Personel / Klien / Prospek"></div>
                <div class="form-group"><label>${t('roleLabel')}</label><input type="text" id="newTeamRole" placeholder="Misal: Calon Klien / Lead Gen"></div>
                <div class="form-group">
                    <label>${t('typeLabel')}</label>
                    <select id="newTeamType" style="width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 10px; outline: none; font-size: 13px;">
                        <option value="team">${t('teamOption')}</option>
                        <option value="client">${t('clientOption')}</option>
                        <option value="prospect">${t('prospectOption')}</option>
                    </select>
                </div>
                <div class="form-group"><label>${t('avatarUrl')}</label><input type="text" id="newTeamAvatar" placeholder="Kosongkan untuk otomatis pakai inisial nama"></div>
                <button class="btn-create" style="width:100%; justify-content:center; margin-top:15px;" onclick="saveNewTeamMember()">${t('saveMember')}</button>
            `;
            openDynamicModal(t('addMemberOrClient'), html);
        }

        function saveNewTeamMember() {
            const nameEl = document.getElementById('newTeamName');
            const roleEl = document.getElementById('newTeamRole');
            const typeEl = document.getElementById('newTeamType');
            const avatarEl = document.getElementById('newTeamAvatar');

            if(!nameEl) return;
            const name = nameEl.value.trim();
            const role = roleEl.value.trim();
            const type = typeEl.value;
            const avatarInput = avatarEl.value.trim();

            if(!name) { alert("Nama harus diisi!"); return; }

            const finalAvatar = avatarInput ? avatarInput : generateAvatar(name);

            teamMembers.unshift({
                id: Date.now(),
                name,
                role: role || 'Member',
                type,
                avatar: finalAvatar
            });

            syncLocalStorage();
            logActivity(`Menambahkan entitas baru: ${name} (${role})`);
            closeSpecificModal('modalDynamic');
            const container = document.getElementById('view-container');
            if(container) {
                container.innerHTML = renderTeamManagementView();
                renderTeamTableRows();
            }
        }

        function openEditTeamMemberModal(id) {
            const m = teamMembers.find(item => item.id == id);
            if(!m) return;

            let html = `
                <input type="hidden" id="editTeamId" value="${m.id}">
                <div class="form-group"><label>${t('nameLabel')}</label><input type="text" id="editTeamName" value="${m.name}"></div>
                <div class="form-group"><label>${t('roleLabel')}</label><input type="text" id="editTeamRole" value="${m.role}"></div>
                <div class="form-group">
                    <label>${t('typeLabel')}</label>
                    <select id="editTeamType" style="width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 10px; outline: none; font-size: 13px;">
                        <option value="team" ${m.type === 'team' ? 'selected' : ''}>${t('teamOption')}</option>
                        <option value="client" ${m.type === 'client' ? 'selected' : ''}>${t('clientOption')}</option>
                        <option value="prospect" ${m.type === 'prospect' ? 'selected' : ''}>${t('prospectOption')}</option>
                    </select>
                </div>
                <div class="form-group"><label>${t('avatarUrl')}</label><input type="text" id="editTeamAvatar" value="${m.avatar}" placeholder="Kosongkan untuk otomatis pakai inisial nama"></div>
                <button class="btn-create" style="width:100%; justify-content:center; margin-top:15px;" onclick="saveEditedTeamMember()">${t('saveChanges')}</button>
            `;
            openDynamicModal("Edit Anggota / Klien / Prospek", html);
        }

        function saveEditedTeamMember() {
            const id = document.getElementById('editTeamId').value;
            const name = document.getElementById('editTeamName').value.trim();
            const role = document.getElementById('editTeamRole').value.trim();
            const type = document.getElementById('editTeamType').value;
            const avatarInput = document.getElementById('editTeamAvatar').value.trim();

            if(!name) { alert("Nama harus diisi!"); return; }

            const m = teamMembers.find(item => item.id == id);
            if(m) {
                m.name = name;
                m.role = role;
                m.type = type;
                m.avatar = avatarInput ? avatarInput : generateAvatar(name);

                syncLocalStorage();
                logActivity(`Memperbarui data entitas: ${name}`);
                closeSpecificModal('modalDynamic');
                
                const activeMenu = document.querySelector('.sidebar .menu-item.active');
                if(activeMenu && activeMenu.getAttribute('data-view') === 'team') {
                    renderTeamTableRows();
                }
            }
        }

        function deleteTeamMember(id) {
            const member = teamMembers.find(m => m.id == id);
            if(confirm("Hapus entitas ini?")) {
                teamMembers = teamMembers.filter(m => m.id != id);
                projectsData.forEach(p => {
                    if(p.team) p.team = p.team.filter(memberId => memberId != id);
                });
                syncLocalStorage();
                if(member) logActivity(`Menghapus ${member.name} dari daftar`);
                renderTeamTableRows();
                
                const activeMenu = document.querySelector('.sidebar .menu-item.active');
                if(activeMenu && activeMenu.getAttribute('data-view') === 'analytics') {
                    const eqEl = document.getElementById('anaTeamCountDisplay');
                    if(eqEl) eqEl.innerText = teamMembers.filter(m => m.type === 'team').length;
                }
            }
        }

        function renderSettingsView() {
            const s = userProfile.socials || {};
            return `
                <div class="projects-section">
                    <h1 data-i18n="profileSettings">Pengaturan Profil</h1>
                    <div style="background: white; padding: 30px; border-radius: 20px; box-shadow: var(--shadow); margin-top: 20px;">
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px;">
                            <div>
                                <h3 style="font-size: 15px; margin-bottom: 20px; color: var(--primary);" data-i18n="accountInfo">Informasi Akun</h3>
                                <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px;">
                                    <img id="settingsAvatarPreview" src="${userProfile.avatar}" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary);">
                                    <div>
                                        <h3 id="settingsNamePreview" style="font-size: 18px;">${userProfile.name}</h3>
                                        <p style="color: var(--text-light); font-size: 13px;">${userProfile.role}</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label data-i18n="nameLabel">Nama Lengkap</label>
                                    <input type="text" id="setProfileName" value="${userProfile.name}">
                                </div>
                                <div class="form-group">
                                    <label data-i18n="roleLabel">Peran / Jabatan</label>
                                    <input type="text" id="setProfileRole" value="${userProfile.role}">
                                </div>
                                <div class="form-group">
                                    <label data-i18n="avatarUrl">URL Foto Profil</label>
                                    <input type="text" id="setProfileAvatar" value="${userProfile.avatar}" placeholder="Kosongkan untuk otomatis pakai inisial nama" oninput="const prev=document.getElementById('settingsAvatarPreview'); if(prev && this.value) prev.src=this.value;">
                                </div>

                                <hr style="border:0; border-top:1px solid #eee; margin:25px 0;">

                                <h3 style="font-size: 15px; margin-bottom: 15px; color: var(--primary);" data-i18n="securityInfo">Keamanan Akun</h3>
                                <div class="form-group">
                                    <label data-i18n="usernameLabel">Username (untuk login, tidak bisa diubah)</label>
                                    <input type="text" id="setProfileUsername" value="${userProfile.username || ''}" placeholder="Username akun" disabled style="background:#eee; cursor:not-allowed;">
                                </div>
                                <div class="form-group">
                                    <label data-i18n="recoveryEmailLabel">Email Pemulihan</label>
                                    <input type="email" id="setProfileRecoveryEmail" value="${userProfile.recoveryEmail || ''}" placeholder="email@pemulihan.com">
                                </div>
                                <div class="form-group">
                                    <label data-i18n="recoveryPhoneLabel">No HP Pemulihan</label>
                                    <input type="text" id="setProfileRecoveryPhone" value="${userProfile.recoveryPhone || ''}" placeholder="+628xxxxxxxxxx">
                                </div>
                                <div class="form-group">
                                    <label data-i18n="changePasswordLabel">Ganti Password (kosongkan jika tidak ingin ganti)</label>
                                    <input type="password" id="setProfileNewPassword" placeholder="${t('passwordPlaceholder')}">
                                </div>
                                <div class="form-group">
                                    <label data-i18n="confirmNewPasswordLabel">Konfirmasi Password Baru</label>
                                    <input type="password" id="setProfileConfirmPassword" placeholder="Ulangi password baru">
                                </div>
                            </div>

                            <div>
                                <h3 style="font-size: 15px; margin-bottom: 20px; color: var(--primary);" data-i18n="socialMediaLinks">Social Media Links</h3>
                                <div class="form-group"><label><i class="fa-brands fa-instagram"></i> Instagram URL</label><input type="text" id="setSocialIg" value="${s.instagram || ''}" placeholder="https://instagram.com/username"></div>
                                <div class="form-group"><label><i class="fa-brands fa-facebook"></i> Facebook URL</label><input type="text" id="setSocialFb" value="${s.facebook || ''}" placeholder="https://facebook.com/username"></div>
                                <div class="form-group"><label><i class="fa-brands fa-twitter"></i> Twitter URL</label><input type="text" id="setSocialX" value="${s.x || ''}" placeholder="https://twitter.com/username"></div>
                                <div class="form-group"><label><i class="fa-brands fa-tiktok"></i> TikTok URL</label><input type="text" id="setSocialTiktok" value="${s.tiktok || ''}" placeholder="https://tiktok.com/@username"></div>
                                <div class="form-group"><label><i class="fa-brands fa-youtube"></i> YouTube URL</label><input type="text" id="setSocialYt" value="${s.youtube || ''}" placeholder="https://youtube.com/@channel"></div>
                                <div class="form-group"><label><i class="fa-brands fa-github"></i> GitHub URL</label><input type="text" id="setSocialGh" value="${s.github || ''}" placeholder="https://github.com/username"></div>
                                <div class="form-group"><label><i class="fa-solid fa-globe"></i> Website / Lainnya URL</label><input type="text" id="setSocialOther" value="${s.others || ''}" placeholder="https://yourwebsite.com"></div>
                            </div>
                        </div>

                        <button class="btn-create" style="margin-top: 30px; width: 100%; justify-content: center; padding: 14px;" onclick="saveUserProfile();" data-i18n="saveChanges">Simpan Perubahan</button>
                    </div>
                </div>
            `;
        }

        let chatConversations = {
            1: [
                { sender: 'client', text: 'Halo Ghulam, apakah progress redesign landing page minggu ini sudah bisa direview?', time: '10:45 AM' },
                { sender: 'me', text: 'Halo! Tentu, progres sudah mencapai 75% dan siap dicek di tab project.', time: '10:50 AM' }
            ],
            2: [
                { sender: 'client', text: 'Hai, kami tertarik untuk diskusi potensi kerja sama aplikasi mobile.', time: 'Kemarin' }
            ]
        };

        function renderMessagesView() {
            return `
                <div class="projects-section">
                    <h1 data-i18n="messagesTitle">Messages (Pesan Masuk)</h1>
                    <div style="background:white; border-radius:15px; padding:20px; box-shadow:var(--shadow); margin-top:20px; display:flex; flex-direction:column; gap:12px;">
                        
                        <div onclick="openWhatsAppChat(1, 'Belong Interactive', 'https://ui-avatars.com/api/?name=Belong+Interactive&background=random')" style="display:flex; align-items:center; gap:15px; padding:12px; background:#f8f9fa; border-radius:12px; cursor:pointer; transition:0.2s;" onmouseover="this.style.background='#eee'" onmouseout="this.style.background='#f8f9fa'">
                            <img src="https://ui-avatars.com/api/?name=Belong+Interactive&background=random" style="width:45px; height:45px; border-radius:50%; object-fit:cover;">
                            <div style="flex:1;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
                                    <b>Belong Interactive</b>
                                    <span style="font-size:10px; color:var(--text-light);">10:50 AM</span>
                                </div>
                                <p style="font-size:12px; color:var(--text-light); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Halo! Tentu, progres sudah mencapai 75%...</p>
                            </div>
                        </div>

                        <div onclick="openWhatsAppChat(2, 'PT Solusi Digital (Prospek)', 'https://ui-avatars.com/api/?name=PT+Solusi+Digital&background=random')" style="display:flex; align-items:center; gap:15px; padding:12px; background:#f8f9fa; border-radius:12px; cursor:pointer; transition:0.2s;" onmouseover="this.style.background='#eee'" onmouseout="this.style.background='#f8f9fa'">
                            <img src="https://ui-avatars.com/api/?name=PT+Solusi+Digital&background=random" style="width:45px; height:45px; border-radius:50%; object-fit:cover;">
                            <div style="flex:1;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
                                    <b>PT Solusi Digital (Prospek)</b>
                                    <span style="font-size:10px; color:var(--text-light);">Kemarin</span>
                                </div>
                                <p style="font-size:12px; color:var(--text-light); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">Hai, kami tertarik untuk diskusi potensi kerja sama...</p>
                            </div>
                        </div>

                    </div>
                </div>
            `;
        }

        function openNotificationChat(chatId, contactName, contactAvatar) {
            document.querySelectorAll('.sidebar .menu-item').forEach(i => i.classList.remove('active'));
            const msgMenu = document.querySelector('.sidebar .menu-item[data-view="messages"]');
            if(msgMenu) msgMenu.classList.add('active');

            const container = document.getElementById('view-container');
            if(container) {
                container.innerHTML = renderMessagesView();
            }
            openWhatsAppChat(chatId, contactName, contactAvatar);
        }

        function openWhatsAppChat(chatId, contactName, contactAvatar) {
            let messages = chatConversations[chatId] || [];
            let chatHtml = `
                <div style="display:flex; align-items:center; gap:12px; padding-bottom:10px; border-bottom:1px solid #eee; margin-bottom:15px;">
                    <img src="${contactAvatar}" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                    <div>
                        <h4 style="font-size:14px;">${contactName}</h4>
                        <span style="font-size:10px; color:var(--icon-green);">● Online</span>
                    </div>
                </div>
                <div id="waChatScroll_${chatId}" style="display:flex; flex-direction:column; gap:10px; max-height:260px; overflow-y:auto; padding:5px; margin-bottom:15px; background:#e5ddd5; border-radius:10px; padding:15px;">
            `;

            messages.forEach(m => {
                const isMe = m.sender === 'me';
                const bgBubble = isMe ? '#dcf8c6' : '#ffffff';
                const alignBubble = isMe ? 'margin-left:auto;' : 'margin-right:auto;';
                chatHtml += `
                    <div style="background:${bgBubble}; padding:8px 12px; border-radius:8px; max-width:75%; ${alignBubble} font-size:12px; box-shadow:0 1px 2px rgba(0,0,0,0.1);">
                        <p>${m.text}</p>
                        <span style="font-size:9px; color:#999; float:right; margin-top:3px;">${m.time}</span>
                    </div>
                `;
            });

            chatHtml += `
                </div>
                <div style="display:flex; gap:8px;">
                    <input type="text" id="waInput_${chatId}" placeholder="Ketik pesan..." style="flex:1; padding:10px 15px; border:1px solid #ddd; border-radius:20px; outline:none; font-size:12px;" onkeyup="if(event.key==='Enter') sendWhatsAppMessage(${chatId}, '${contactName}', '${contactAvatar}')">
                    <button class="btn-create" style="border-radius:50%; width:40px; height:40px; padding:0; display:flex; justify-content:center; align-items:center;" onclick="sendWhatsAppMessage(${chatId}, '${contactName}', '${contactAvatar}')"><i class="fa-solid fa-paper-plane"></i></button>
                </div>
            `;

            openDynamicModal("Chat WhatsApp: " + contactName, chatHtml);
            
            setTimeout(() => {
                const scrollEl = document.getElementById(`waChatScroll_${chatId}`);
                if(scrollEl) scrollEl.scrollTop = scrollEl.scrollHeight;
            }, 50);
        }

        function sendWhatsAppMessage(chatId, contactName, contactAvatar) {
            const input = document.getElementById(`waInput_${chatId}`);
            if(!input) return;
            const text = input.value.trim();
            if(!text) return;

            if(!chatConversations[chatId]) chatConversations[chatId] = [];
            chatConversations[chatId].push({
                sender: 'me',
                text: text,
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            });

            logActivity(`Mengirim pesan WhatsApp ke ${contactName}`);
            openWhatsAppChat(chatId, contactName, contactAvatar);
        }

        let discussionsThreads = Array.isArray(BOOTSTRAP.threads) ? BOOTSTRAP.threads : [];

        function renderDiscussionsView() {
            let html = `
                <div class="projects-section">
                    <h1 data-i18n="discussionsTitle">Discussions & Cuitan Thread</h1>
                    <div style="background:white; border-radius:15px; padding:20px; box-shadow:var(--shadow); margin-top:20px;">
                        <div style="display:flex; gap:12px; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:15px;">
                            <img src="${userProfile.avatar}" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                            <div style="flex:1;">
                                <textarea id="newThreadText" placeholder="${t('tweetPlaceholder')}" style="width:100%; border:none; outline:none; resize:none; font-size:13px; font-family:'Poppins',sans-serif;" rows="2"></textarea>
                                <div style="display:flex; justify-content:flex-end; margin-top:10px;">
                                    <button class="btn-create" style="padding:6px 15px;" onclick="postNewThread()" data-i18n="tweetBtn">Cuitkan</button>
                                </div>
                            </div>
                        </div>
                        <div style="display:flex; flex-direction:column; gap:15px;" id="threadsFeedContainer">
            `;

            discussionsThreads.forEach(th => {
                const replyCount = th.replyList ? th.replyList.length : 0;
                html += `
                    <div style="display:flex; gap:12px; padding:12px; background:#f8f9fa; border-radius:12px; border:1px solid #eee;">
                        <img src="${th.avatar}" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                        <div style="flex:1;">
                            <div style="display:flex; align-items:center; gap:6px; margin-bottom:3px;">
                                <b>${th.name}</b>
                                <span style="font-size:11px; color:var(--text-light);">${th.handle} · ${th.time}</span>
                            </div>
                            <p style="font-size:13px; color:var(--text-dark); margin-bottom:10px;">${th.text}</p>
                            <div style="display:flex; gap:20px; font-size:12px; color:var(--text-light);">
                                <span style="cursor:pointer;" onclick="likeThread(${th.id})"><i class="fa-regular fa-heart" style="${th.liked ? 'color:#ff4757; font-weight:bold;' : ''}"></i> ${th.likes}</span>
                                <span style="cursor:pointer;" onclick="openThreadReplies(${th.id})"><i class="fa-regular fa-comment"></i> ${replyCount}</span>
                                <span style="cursor:pointer;" onclick="repostThread(${th.id})"><i class="fa-solid fa-retweet"></i> ${th.reposts || 0}</span>
                                <span style="cursor:pointer; margin-left:auto; color:#ff4757;" onclick="deleteThread(${th.id})" title="Hapus Cuitan"><i class="fa-solid fa-trash"></i></span>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += `</div></div></div>`;
            return html;
        }

        function postNewThread() {
            const txtEl = document.getElementById('newThreadText');
            if(!txtEl) return;
            const text = txtEl.value.trim();
            if(!text) { alert("Cuitan tidak boleh kosong!"); return; }

            discussionsThreads.unshift({
                id: Date.now(),
                name: userProfile.name,
                handle: "@" + (userProfile.username || userProfile.name.toLowerCase().replace(/\s+/g, '_')),
                avatar: userProfile.avatar,
                text: text,
                time: t('justNow'),
                likes: 0,
                liked: false,
                reposts: 0,
                replyList: []
            });

            persist('threads', discussionsThreads);
            logActivity("Membuat cuitan thread baru");
            txtEl.value = "";
            
            const container = document.getElementById('view-container');
            if(container) container.innerHTML = renderDiscussionsView();
        }

        function likeThread(id) {
            const th = discussionsThreads.find(t => t.id === id);
            if(th) {
                th.liked = !th.liked;
                th.likes += th.liked ? 1 : -1;
                persist('threads', discussionsThreads);
                logActivity(th.liked ? `Menyukai cuitan dari ${th.name}` : `Membatalkan suka pada cuitan dari ${th.name}`);
                const container = document.getElementById('view-container');
                if(container) container.innerHTML = renderDiscussionsView();
            }
        }

        function openThreadReplies(id) {
            const th = discussionsThreads.find(t => t.id === id);
            if(!th) return;
            if(!th.replyList) th.replyList = [];

            let html = `<div style="font-size:13px; margin-bottom:15px; padding-bottom:10px; border-bottom:1px solid #eee;"><b>${th.name}:</b> ${th.text}</div>`;
            html += `<div style="max-height:220px; overflow-y:auto; display:flex; flex-direction:column; gap:10px; margin-bottom:15px;" id="replyListContainer_${th.id}">`;

            if(th.replyList.length === 0) {
                html += `<p style="text-align:center; color:#999; font-size:12px; padding:10px;">Belum ada balasan.</p>`;
            } else {
                th.replyList.forEach(rep => {
                    html += `
                        <div style="background:#f8f9fa; padding:10px; border-radius:10px; font-size:12px; border:1px solid #eee;">
                            <b>${rep.name}:</b> ${rep.text} <br><span style="font-size:9px; color:var(--text-light);">${rep.time}</span>
                        </div>
                    `;
                });
            }
            html += `</div>`;
            html += `
                <div style="display:flex; gap:8px;">
                    <input type="text" id="newReplyInput_${th.id}" placeholder="Tulis balasan..." style="flex:1; padding:8px 12px; border:1px solid #ddd; border-radius:8px; font-size:12px; outline:none;" onkeyup="if(event.key==='Enter') submitReply(${th.id})">
                    <button class="btn-create" style="padding:8px 15px;" onclick="submitReply(${th.id})">${t('sendBtn')}</button>
                </div>
            `;
            openDynamicModal("Balasan Cuitan", html);
        }

        function submitReply(id) {
            const input = document.getElementById(`newReplyInput_${id}`);
            if(!input) return;
            const text = input.value.trim();
            if(!text) return;

            const th = discussionsThreads.find(t => t.id === id);
            if(th) {
                if(!th.replyList) th.replyList = [];
                th.replyList.push({
                    name: userProfile.name,
                    text: text,
                    time: t('justNow')
                });
                persist('threads', discussionsThreads);
                logActivity(`Membalas cuitan dari ${th.name}`);
                openThreadReplies(id);

                const container = document.getElementById('view-container');
                if(container && document.querySelector('.sidebar .menu-item[data-view="discussions"]').classList.contains('active')) {
                    container.innerHTML = renderDiscussionsView();
                }
            }
        }

        function repostThread(id) {
            const th = discussionsThreads.find(t => t.id === id);
            if(th) {
                th.reposts = (th.reposts || 0) + 1;
                discussionsThreads.unshift({
                    id: Date.now(),
                    name: userProfile.name,
                    handle: "@" + (userProfile.username || userProfile.name.toLowerCase().replace(/\s+/g, '_')),
                    avatar: userProfile.avatar,
                    text: `🔁 Repost dari ${th.name}: "${th.text}"`,
                    time: t('justNow'),
                    likes: 0,
                    liked: false,
                    reposts: 0,
                    replyList: []
                });
                persist('threads', discussionsThreads);
                logActivity(`Membagikan ulang (repost) cuitan ${th.name}`);
                const container = document.getElementById('view-container');
                if(container) container.innerHTML = renderDiscussionsView();
            }
        }

        function deleteThread(id) {
            if(confirm("Yakin ingin menghapus cuitan ini?")) {
                discussionsThreads = discussionsThreads.filter(t => t.id !== id);
                persist('threads', discussionsThreads);
                logActivity("Menghapus cuitan");
                const container = document.getElementById('view-container');
                if(container) container.innerHTML = renderDiscussionsView();
            }
        }

        function openCreateProjectModal() {
            const titleEl = document.getElementById('modalProjectTitle');
            const idEl = document.getElementById('editProjectId');
            const projTitle = document.getElementById('projTitle');
            const projClient = document.getElementById('projClient');
            const projProgress = document.getElementById('projProgress');
            const projStatus = document.getElementById('projStatus');
            const projDeadline = document.getElementById('projDeadline');

            if(titleEl) titleEl.innerText = t('createProject');
            if(idEl) idEl.value = "";
            if(projTitle) projTitle.value = "";
            if(projClient) projClient.value = "";
            if(projProgress) projProgress.value = "50";
            if(projStatus) projStatus.value = "started";
            if(projDeadline) projDeadline.value = "";
            
            document.querySelectorAll('.color-option').forEach((el, idx) => {
                if(idx === 0) el.classList.add('selected'); else el.classList.remove('selected');
            });
            document.querySelectorAll('.icon-option').forEach((el, idx) => {
                if(idx === 0) el.classList.add('selected'); else el.classList.remove('selected');
            });
            const colorEl = document.getElementById('projColor');
            const iconEl = document.getElementById('projIcon');
            if(colorEl) colorEl.value = "#ff9f43";
            if(iconEl) iconEl.value = "fa-rocket";

            const modalProj = document.getElementById('modalProject');
            if(modalProj) modalProj.classList.add('show');
        }

        function openEditProjectModal(id) {
            const p = projectsData.find(item => item.id == id);
            if(!p) return;
            const titleEl = document.getElementById('modalProjectTitle');
            const idEl = document.getElementById('editProjectId');
            const projTitle = document.getElementById('projTitle');
            const projClient = document.getElementById('projClient');
            const projProgress = document.getElementById('projProgress');
            const projStatus = document.getElementById('projStatus');
            const projDeadline = document.getElementById('projDeadline');
            const colorEl = document.getElementById('projColor');
            const iconEl = document.getElementById('projIcon');

            if(titleEl) titleEl.innerText = t('editProject');
            if(idEl) idEl.value = p.id;
            if(projTitle) projTitle.value = p.title;
            if(projClient) projClient.value = p.client;
            if(projProgress) projProgress.value = p.progress;
            if(projStatus) projStatus.value = p.status;
            if(projDeadline) projDeadline.value = p.deadline || "";
            if(colorEl) colorEl.value = p.color;
            if(iconEl) iconEl.value = p.icon;

            document.querySelectorAll('.color-option').forEach(el => {
                if(el.style.backgroundColor === p.color || el.style.background.includes(p.color)) {
                    el.classList.add('selected');
                } else {
                    el.classList.remove('selected');
                }
            });

            document.querySelectorAll('.icon-option').forEach(el => {
                const iTag = el.querySelector('i');
                if(iTag && iTag.classList.contains(p.icon)) {
                    el.classList.add('selected');
                } else {
                    el.classList.remove('selected');
                }
            });

            const modalProj = document.getElementById('modalProject');
            if(modalProj) modalProj.classList.add('show');
        }

        function selectColor(colorHex, element) {
            document.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            const colorInput = document.getElementById('projColor');
            if(colorInput) colorInput.value = colorHex;
        }

        function selectIcon(iconClass, element) {
            document.querySelectorAll('.icon-option').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            const iconInput = document.getElementById('projIcon');
            if(iconInput) iconInput.value = iconClass;
        }

        function saveProjectData() {
            const idEl = document.getElementById('editProjectId');
            const titleEl = document.getElementById('projTitle');
            const clientEl = document.getElementById('projClient');
            const statusEl = document.getElementById('projStatus');
            const progressEl = document.getElementById('projProgress');
            const deadlineEl = document.getElementById('projDeadline');
            const colorEl = document.getElementById('projColor');
            const iconEl = document.getElementById('projIcon');

            if(!titleEl) return;
            const id = idEl ? idEl.value : '';
            const title = titleEl.value.trim();
            const client = clientEl ? clientEl.value.trim() : '';
            const status = statusEl ? statusEl.value : 'started';
            const progress = progressEl ? (parseInt(progressEl.value) || 0) : 0;
            const deadline = deadlineEl ? deadlineEl.value : '';
            const color = colorEl ? colorEl.value : '#ff9f43';
            const icon = iconEl ? iconEl.value : 'fa-rocket';

            if(!title) {
                alert("Nama proyek harus diisi!");
                return;
            }

            if(id) {
                const idx = projectsData.findIndex(item => item.id == id);
                if(idx !== -1) {
                    projectsData[idx] = { ...projectsData[idx], title, client, status, progress, deadline, color, icon };
                    logActivity(`Memperbarui proyek "${title}"`);
                }
            } else {
                const newProj = {
                    id: Date.now(),
                    title,
                    client,
                    status,
                    progress,
                    deadline,
                    color,
                    icon,
                    team: [],
                    images: [],
                    comments: []
                };
                projectsData.push(newProj);
                logActivity(`Membuat proyek baru "${title}"`);
            }

            syncLocalStorage();
            closeSpecificModal('modalProject');
            renderProjectsGrid('all');
        }

        function deleteProject(id, event) {
            if(event) event.stopPropagation();
            const p = projectsData.find(item => item.id == id);
            if(confirm("Yakin ingin menghapus proyek ini?")) {
                projectsData = projectsData.filter(item => item.id != id);
                syncLocalStorage();
                if(p) logActivity(`Menghapus proyek "${p.title}"`);
                renderProjectsGrid('all');
            }
        }

        function renderProjectsGrid(filterStatus = 'all') {
            const container = document.getElementById('projectsGrid');
            if(!container) return;

            let html = '';
            const filtered = filterStatus === 'all' ? projectsData : projectsData.filter(p => p.status === filterStatus);

            if(filtered.length === 0) {
                container.innerHTML = `<p style="grid-column: 1/-1; text-align:center; color:var(--text-light); padding:30px;">${t('noProjects')}</p>`;
                return;
            }

            filtered.forEach(p => {
                const imgCount = p.images ? p.images.length : 0;
                const commentCount = p.comments ? p.comments.length : 0;
                const timeInfo = calculateTimeLeft(p.deadline, p.status);

                let avatarsHTML = '';
                if(p.team && p.team.length > 0) {
                    p.team.forEach(memberId => {
                        const m = teamMembers.find(tm => tm.id == memberId);
                        if(m) {
                            avatarsHTML += `<img src="${m.avatar}" title="${m.name} (${m.role})">`;
                        }
                    });
                }
                if(!avatarsHTML) {
                    avatarsHTML = `<span>${t('noTeam')}</span>`;
                }

                html += `
                    <div class="card card-item" data-status="${p.status}" onclick="openDynamicModal('${p.title}', '${t('clientName')}: ${p.client}<br>Status: ${p.status.toUpperCase()}<br>Progress: ${p.progress}%<br>Deadline: ${p.deadline || 'Tidak ada'}')">
                        <i class="fa-solid fa-ellipsis-vertical card-menu" onclick="event.stopPropagation(); toggleDropdown('menuCard_${p.id}', event)"></i>
                        <div id="menuCard_${p.id}" class="dropdown-menu" style="right:20px; top:35px; width:160px;">
                            <div class="dropdown-item" onclick="openAddTeamModal(${p.id}, event)"><i class="fa-solid fa-user-plus" style="color:var(--primary);"></i> <span>${t('assignTeam')}</span></div>
                            <div class="dropdown-item" onclick="openEditProjectModal(${p.id})"><i class="fa-solid fa-pen"></i> <span>${t('editProject')}</span></div>
                            <div class="dropdown-item" style="color:red;" onclick="deleteProject(${p.id}, event)"><i class="fa-solid fa-trash"></i> <span>${t('delete')}</span></div>
                        </div>

                        <div class="card-icon" style="background:${p.color};"><i class="fa-solid ${p.icon}"></i></div>
                        
                        <div class="card-info">
                            <h3>${p.title}</h3>
                            <p>${p.client}</p>
                        </div>

                        <div class="team-avatars">
                            ${avatarsHTML}
                        </div>

                        <div class="progress-section">
                            <div class="progress-header"><span>Progress</span><span>${p.progress}%</span></div>
                            <div class="progress-bar"><div class="progress-fill" style="width: ${p.progress}%; background: ${p.color};"></div></div>
                        </div>

                        <div class="card-footer">
                            <div class="card-stats">
                                <span onclick="openImageGallery(${p.id}, event)" title="Lihat Galeri Foto"><i class="fa-regular fa-image"></i> ${imgCount}</span>
                                <span onclick="openCommentsModal(${p.id}, event)" title="Lihat Diskusi"><i class="fa-regular fa-comment"></i> ${commentCount}</span>
                            </div>
                            <span class="time-left ${timeInfo.class}"><i class="fa-regular fa-clock"></i> ${timeInfo.text}</span>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
            
            const totalSpan = document.getElementById('totalProjectsCount');
            if(totalSpan) totalSpan.innerText = projectsData.length;
            updateDashboardStats();
        }

        window.toggleView = function(viewType) {
            const grid = document.getElementById('projectsGrid');
            if(!grid) return;
            const icons = document.querySelectorAll('.view-toggle-icon');
            icons.forEach(i => i.classList.remove('active-view'));
            if(viewType === 'list') {
                grid.classList.add('list-view');
                const iconList = document.getElementById('iconList');
                if(iconList) iconList.classList.add('active-view');
            } else {
                grid.classList.remove('list-view');
                const iconGrid = document.getElementById('iconGrid');
                if(iconGrid) iconGrid.classList.add('active-view');
            }
        };

        window.filterProjectsTab = function(filterStr, elementClicked) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            elementClicked.classList.add('active');
            renderProjectsGrid(filterStr);
        };

        document.addEventListener("DOMContentLoaded", () => {
            const selectEl = document.getElementById('langSelect');
            if(selectEl) selectEl.value = currentLang;
            updateProfileUI();
            applyTranslations();
            renderActivities();
            renderTasksDropdown();
            updateDashboardStats();
            updateFinanceDisplay();

            const dateSpan = document.getElementById('currentDateSpan');
            if(dateSpan) {
                const options = { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'Asia/Jakarta' };
                dateSpan.innerText = new Intl.DateTimeFormat('en-GB', options).format(new Date());
            }

            const views = {
                projects: `
                    <div class="projects-section">
                        <div class="projects-header">
                            <div>
                                <h1><span data-i18n="projects">Projects</span> <span class="badge-count" id="totalProjectsCount">${projectsData.length}</span></h1>
                                <div class="tabs-container">
                                    <div class="tabs">
                                        <div class="tab active" onclick="filterProjectsTab('all', this)" data-i18n="all">All</div>
                                        <div class="tab" onclick="filterProjectsTab('started', this)" data-i18n="started">Started</div>
                                        <div class="tab" onclick="filterProjectsTab('approval', this)" data-i18n="approval">Approval</div>
                                        <div class="tab" onclick="filterProjectsTab('discrepancy', this)" data-i18n="discrepancy">Discrepancy</div>
                                        <div class="tab" onclick="filterProjectsTab('completed', this)" data-i18n="completed">Completed</div>
                                    </div>
                                </div>
                            </div>
                            <div class="actions">
                                <i id="iconList" class="fa-solid fa-bars view-toggle-icon" title="List View" onclick="toggleView('list')"></i>
                                <i id="iconGrid" class="fa-solid fa-grip view-toggle-icon active-view" title="Grid View" onclick="toggleView('grid')"></i>
                                <button class="btn-create" onclick="openCreateProjectModal()"><i class="fa-solid fa-plus"></i> <span data-i18n="createProject">Create Project</span></button>
                            </div>
                        </div>
                        <div class="cards-grid" id="projectsGrid"></div>
                    </div>`
            };

            const container = document.getElementById('view-container');
            const menuItems = document.querySelectorAll('.sidebar .menu-item');

            if(container) {
                container.innerHTML = views.projects;
                renderProjectsGrid('all');
            }

            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    const targetView = this.getAttribute('data-view');
                    if (!views[targetView] && targetView !== 'timeline' && targetView !== 'analytics' && targetView !== 'team' && targetView !== 'settings' && targetView !== 'messages' && targetView !== 'discussions') return;

                    menuItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');

                    if(window.innerWidth <= 768) {
                        closeAllDrawers();
                    }

                    if(container) {
                        container.style.opacity = 0;
                        setTimeout(() => {
                            if(targetView === 'team') {
                                container.innerHTML = renderTeamManagementView();
                                currentTeamPage = 1;
                                renderTeamTableRows();
                            } else if(targetView === 'settings') {
                                container.innerHTML = renderSettingsView();
                            } else if(targetView === 'messages') {
                                container.innerHTML = renderMessagesView();
                            } else if(targetView === 'discussions') {
                                container.innerHTML = renderDiscussionsView();
                            } else if(targetView === 'timeline') {
                                container.innerHTML = renderTimelineView();
                            } else if(targetView === 'analytics') {
                                container.innerHTML = renderAnalyticsView();
                            } else {
                                container.innerHTML = views[targetView];
                                if(targetView === 'projects') renderProjectsGrid('all');
                            }
                            applyTranslations();
                            container.style.opacity = 1;
                            container.style.transition = 'opacity 0.2s ease-in';
                        }, 150);
                    }
                });
            });
        });
    </script>
</body>
</html>