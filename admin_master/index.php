<?php
// admin_master/index.php - REDESIGNED WITH PROFESSIONAL THEME

session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if master admin is logged in
if (!isset($_SESSION['master_logged_in']) || $_SESSION['master_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$user_admins = getAllUserAdmins();
$total_users = count($user_admins);
$active_users = array_filter($user_admins, function($u) { return $u['is_active'] == 1; });
$total_active = count($active_users);

$master_initials = strtoupper(substr($_SESSION['master_full_name'] ?? 'M', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ========================================
           MASTER ADMIN - PROFESSIONAL THEME
           ======================================== */
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary: #2D7D6F;
            --primary-dark: #1F5C51;
            --primary-light: #4A9A8A;
            --primary-bg: rgba(45, 125, 111, 0.08);
            --gold: #D4A858;
            --gold-light: #E8C87A;
            --gold-bg: rgba(212, 168, 88, 0.1);
            --dark-900: #0F1A18;
            --dark-800: #1A2A27;
            --dark-700: #2A3F3A;
            --dark-600: #3D5A54;
            --gray-500: #8EA8A2;
            --gray-400: #B0C4BE;
            --gray-300: #D1E0DB;
            --gray-200: #E8F0ED;
            --gray-100: #F5FAF8;
            --success: #2ECC71;
            --success-bg: rgba(46, 204, 113, 0.15);
            --danger: #E74C3C;
            --danger-bg: rgba(231, 76, 60, 0.15);
            --warning: #F39C12;
            --warning-bg: rgba(243, 156, 18, 0.15);
            --shadow: 0 2px 12px rgba(0, 0, 0, 0.3);
            --shadow-lg: 0 8px 40px rgba(0, 0, 0, 0.4);
            --radius: 14px;
            --radius-sm: 10px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark-900);
            min-height: 100vh;
            color: #fff;
        }
        
        /* ===== SIDEBAR OVERLAY ===== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 998;
            backdrop-filter: blur(4px);
        }
        .sidebar-overlay.active {
            display: block;
        }
        
        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--dark-800);
            padding: 20px 0;
            border-right: 1px solid var(--dark-700);
            overflow-y: auto;
            z-index: 1000;
            transition: var(--transition);
            transform: translateX(0);
        }
        
        .sidebar-brand {
            padding: 0 24px 30px;
            font-size: 20px;
            font-weight: 700;
            border-bottom: 1px solid var(--dark-700);
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Playfair Display', serif;
        }
        .sidebar-brand i {
            color: var(--gold);
            font-size: 22px;
        }
        .sidebar-brand span {
            color: #fff;
        }
        .sidebar-brand span span {
            color: var(--gold);
        }
        
        .sidebar-nav {
            padding: 20px 0;
        }
        .sidebar-nav .nav-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--gray-500);
            padding: 0 24px 12px;
        }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            color: var(--gray-500);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            font-size: 14px;
            font-weight: 500;
        }
        .sidebar-nav a:hover {
            background: var(--dark-700);
            color: #fff;
        }
        .sidebar-nav a.active {
            background: var(--gold-bg);
            color: var(--gold);
            border-left-color: var(--gold);
        }
        .sidebar-nav a i {
            width: 20px;
            font-size: 16px;
        }
        .sidebar-nav a .badge {
            margin-left: auto;
            background: var(--dark-700);
            padding: 0 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            color: var(--gray-500);
        }
        .sidebar-nav a.active .badge {
            background: rgba(212, 168, 88, 0.2);
            color: var(--gold);
        }
        
        .sidebar-footer {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            padding: 0 24px;
        }
        .sidebar-footer .user-info {
            padding: 12px 0;
            border-top: 1px solid var(--dark-700);
            font-size: 14px;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-footer .user-info .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            color: var(--dark-900);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 13px;
            flex-shrink: 0;
        }
        .sidebar-footer .user-info .user-details {
            flex: 1;
            min-width: 0;
        }
        .sidebar-footer .user-info .user-details .name {
            color: #fff;
            font-weight: 500;
        }
        .sidebar-footer .user-info .user-details .role {
            font-size: 11px;
            color: var(--gray-500);
        }
        .sidebar-footer .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px;
            background: var(--dark-700);
            color: var(--gray-500);
            text-decoration: none;
            border-radius: var(--radius-sm);
            margin-top: 10px;
            transition: all 0.3s;
            font-size: 13px;
            font-weight: 500;
        }
        .sidebar-footer .logout-btn:hover {
            background: var(--danger-bg);
            color: var(--danger);
        }
        
        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px 40px;
            min-height: 100vh;
            transition: var(--transition);
        }
        
        /* ===== TOP HEADER ===== */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .top-header .left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .top-header .left .menu-toggle {
            display: none;
            background: var(--dark-800);
            border: 1px solid var(--dark-700);
            border-radius: var(--radius-sm);
            padding: 10px 12px;
            cursor: pointer;
            color: var(--gray-300);
            font-size: 18px;
            transition: var(--transition);
        }
        .top-header .left .menu-toggle:hover {
            background: var(--dark-700);
            color: #fff;
        }
        .top-header .left .page-title h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 600;
            color: #fff;
        }
        .top-header .left .page-title h1 span {
            color: var(--gold);
        }
        .top-header .left .page-title p {
            color: var(--gray-500);
            font-size: 14px;
            margin-top: 2px;
        }
        
        /* ===== BUTTONS ===== */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            color: var(--dark-900);
            border: none;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
            box-shadow: 0 2px 12px rgba(212, 168, 88, 0.2);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(212, 168, 88, 0.35);
        }
        .btn-sm {
            padding: 6px 14px;
            font-size: 12px;
            border-radius: 6px;
        }
        
        /* ===== STATS ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: var(--dark-800);
            padding: 18px 22px;
            border-radius: var(--radius);
            border: 1px solid var(--dark-700);
            transition: all 0.3s;
        }
        .stat-card:hover {
            border-color: var(--gold);
            transform: translateY(-2px);
        }
        .stat-card .stat-icon {
            font-size: 22px;
            color: var(--gold);
            margin-bottom: 6px;
        }
        .stat-card h3 {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
        }
        .stat-card p {
            color: var(--gray-500);
            font-size: 12px;
            margin-top: 2px;
            font-weight: 500;
        }
        
        /* ===== SEARCH ===== */
        .search-container {
            background: var(--dark-800);
            border-radius: var(--radius);
            border: 1px solid var(--dark-700);
            padding: 16px 24px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .search-container .search-box {
            flex: 1;
            min-width: 200px;
            position: relative;
        }
        .search-container .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-500);
        }
        .search-container .search-box input {
            width: 100%;
            padding: 12px 16px 12px 42px;
            border: 1.5px solid var(--dark-700);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s;
            background: var(--dark-900);
            color: #fff;
        }
        .search-container .search-box input:focus {
            outline: none;
            border-color: var(--gold);
            background: var(--dark-700);
            box-shadow: 0 0 0 4px rgba(212, 168, 88, 0.1);
        }
        .search-container .search-box input::placeholder {
            color: var(--gray-500);
        }
        .search-container .search-stats {
            color: var(--gray-500);
            font-size: 13px;
            white-space: nowrap;
        }
        .search-container .search-stats strong {
            color: var(--gold);
        }
        
        /* ===== TABLE ===== */
        .table-card {
            background: var(--dark-800);
            border-radius: var(--radius);
            border: 1px solid var(--dark-700);
            overflow: hidden;
        }
        .table-card .table-header {
            padding: 16px 24px;
            border-bottom: 1px solid var(--dark-700);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .table-card .table-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .table-card .table-header h3 i {
            color: var(--gold);
        }
        
        .table-wrapper {
            overflow-x: auto;
            padding: 0;
        }
        .table-wrapper table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }
        .table-wrapper th {
            background: var(--dark-700);
            padding: 12px 16px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--gray-500);
            font-weight: 600;
            border-bottom: 1px solid var(--dark-600);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .table-wrapper td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--dark-700);
            font-size: 13px;
            color: var(--gray-300);
            vertical-align: middle;
        }
        .table-wrapper tr:hover td {
            background: var(--dark-700);
        }
        .table-wrapper tr:last-child td {
            border-bottom: none;
        }
        
        .status-badge {
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }
        .status-active {
            background: var(--success-bg);
            color: var(--success);
        }
        .status-suspended {
            background: var(--danger-bg);
            color: var(--danger);
        }
        .status-pending {
            background: var(--warning-bg);
            color: var(--warning);
        }
        
        .action-btns {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .action-btns a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: var(--radius-sm);
            text-decoration: none;
            transition: all 0.3s;
            border: 1px solid var(--dark-700);
            background: var(--dark-900);
            color: var(--gray-500);
            font-size: 12px;
        }
        .action-btns a:hover {
            background: var(--dark-700);
            border-color: var(--gray-500);
            color: #fff;
        }
        .action-btns a.edit:hover {
            border-color: var(--gold);
            color: var(--gold);
        }
        .action-btns a.delete:hover {
            border-color: var(--danger);
            color: var(--danger);
        }
        .action-btns a.view:hover {
            border-color: var(--success);
            color: var(--success);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray-500);
        }
        .empty-state i {
            font-size: 48px;
            color: var(--dark-600);
            margin-bottom: 16px;
        }
        .empty-state h4 {
            color: var(--gray-300);
            margin-bottom: 4px;
        }
        .empty-state p {
            font-size: 14px;
        }
        
        .no-results {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray-500);
        }
        .no-results i {
            font-size: 32px;
            color: var(--dark-600);
            margin-bottom: 8px;
            display: block;
        }
        .no-results p {
            font-size: 14px;
        }
        
        /* ===== LEGEND ===== */
        .legend-box {
            background: var(--dark-800);
            border-radius: var(--radius);
            border: 1px solid var(--dark-700);
            padding: 14px 20px;
            margin-top: 20px;
        }
        .legend-box h3 {
            font-size: 12px;
            color: var(--gray-500);
            margin-bottom: 8px;
            font-weight: 600;
        }
        .legend-box .legend-items {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 12px;
            color: var(--gray-400);
        }
        .legend-box .legend-items .item {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .legend-box .legend-items .item .enabled {
            color: var(--success);
        }
        .legend-box .legend-items .item .disabled {
            color: var(--dark-600);
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .sidebar-overlay.active {
                display: block;
            }
            
            .main-content {
                margin-left: 0;
                padding: 16px;
            }
            
            .top-header .left .menu-toggle {
                display: flex;
            }
            .top-header .left .page-title h1 {
                font-size: 20px;
            }
            .top-header .left .page-title p {
                display: none;
            }
            .top-header .right .btn-primary span {
                display: none;
            }
            .top-header .right .btn-primary {
                padding: 10px 14px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }
            .stat-card {
                padding: 14px 16px;
            }
            .stat-card h3 {
                font-size: 20px;
            }
            
            .search-container {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
                padding: 16px;
            }
            .search-container .search-stats {
                text-align: center;
            }
            
            .table-wrapper table {
                font-size: 12px;
                min-width: 500px;
            }
            .table-wrapper th,
            .table-wrapper td {
                padding: 8px 10px;
            }
            .action-btns a {
                width: 26px;
                height: 26px;
                font-size: 11px;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 12px;
            }
            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }
            .stat-card {
                padding: 12px 14px;
            }
            .stat-card h3 {
                font-size: 17px;
            }
            .stat-card p {
                font-size: 10px;
            }
            .stat-card .stat-icon {
                font-size: 18px;
            }
            .top-header .left .page-title h1 {
                font-size: 17px;
            }
            .top-header {
                gap: 10px;
            }
            .table-wrapper table {
                min-width: 400px;
            }
            .table-wrapper th,
            .table-wrapper td {
                padding: 6px 8px;
                font-size: 11px;
            }
            .status-badge {
                font-size: 10px;
                padding: 2px 8px;
            }
            .action-btns a {
                width: 24px;
                height: 24px;
                font-size: 10px;
            }
            .legend-box .legend-items {
                gap: 10px;
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-crown"></i>
            <span>Master<span>Admin</span></span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Main Menu</div>
            <a href="index.php" class="active">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
                <span class="badge"><?php echo $total_users; ?></span>
            </a>
            <a href="create-user.php">
                <i class="fa-solid fa-user-plus"></i>
                <span>Create User</span>
            </a>
            <a href="themes.php">
                <i class="fa-solid fa-palette"></i>
                <span>Themes</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="avatar"><?php echo $master_initials; ?></div>
                <div class="user-details">
                    <div class="name"><?php echo htmlspecialchars($_SESSION['master_full_name']); ?></div>
                    <div class="role">Master Admin</div>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Header -->
        <div class="top-header">
            <div class="left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="page-title">
                    <h1>Dashboard</h1>
                    <p>Manage all wedding invitations and users</p>
                </div>
            </div>
            <div class="right">
                <a href="create-user.php" class="btn-primary">
                    <i class="fa-solid fa-plus"></i>
                    <span>Create New User</span>
                </a>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                <h3><?php echo $total_users; ?></h3>
                <p>Total Users</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--success);"><i class="fa-solid fa-user-check"></i></div>
                <h3><?php echo $total_active; ?></h3>
                <p>Active Users</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--gray-500);"><i class="fa-solid fa-user-slash"></i></div>
                <h3><?php echo $total_users - $total_active; ?></h3>
                <p>Inactive Users</p>
            </div>
        </div>
        
        <!-- Search -->
        <div class="search-container">
            <div class="search-box">
                <i class="fa-solid fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search by name, username, email, couple name..." onkeyup="liveSearch()">
            </div>
            <div class="search-stats">
                Showing <strong id="visibleCount"><?php echo count($user_admins); ?></strong> of <strong id="totalCount"><?php echo count($user_admins); ?></strong> users
            </div>
            <button class="btn btn-sm btn-secondary" onclick="clearSearch()" style="background:var(--dark-700); color:var(--gray-500); border:none; padding:6px 14px; border-radius:6px; cursor:pointer;">
                <i class="fa-solid fa-xmark"></i> Clear
            </button>
        </div>
        
        <!-- Users Table -->
        <div class="table-card">
            <div class="table-header">
                <h3><i class="fa-solid fa-users"></i> All Users</h3>
            </div>
            <div class="table-wrapper">
                <table id="userTable">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Couple</th>
                            
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <?php if (empty($user_admins)): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="fa-solid fa-users"></i>
                                        <h4>No Users Created Yet</h4>
                                        <p>Create your first user to get started</p>
                                        <a href="create-user.php" class="btn-primary btn-sm" style="margin-top:12px;">
                                            <i class="fa-solid fa-plus"></i> Create First User
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($user_admins as $user): ?>
                                <tr data-name="<?php echo strtolower($user['full_name']); ?>" 
                                    data-username="<?php echo strtolower($user['username']); ?>"
                                    data-email="<?php echo strtolower($user['email']); ?>"
                                    data-couple="<?php echo strtolower($user['couple_name'] ?? ''); ?>">
                                    <td>
                                        <strong style="color:#fff;"><?php echo htmlspecialchars($user['full_name']); ?></strong><br>
                                        <small style="color:var(--gray-500);">@<?php echo htmlspecialchars($user['username']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['couple_name'] ?? '—'); ?></td>
                                    
                                    <td>
                                        <span class="status-badge status-<?php echo $user['account_status']; ?>">
                                            <?php echo ucfirst($user['account_status']); ?>
                                        </span>
                                    </td>
                                    <td style="text-align:right;">
                                        <div class="action-btns" style="justify-content:flex-end;">
                                            <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="edit" title="Edit User">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <?php if ($user['invitation_id']): ?>
                                                <a href="../intro/index.php?slug=<?php echo $user['slug']; ?>" target="_blank" class="view" title="View Invitation">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="delete-user.php?id=<?php echo $user['id']; ?>" class="delete" onclick="return confirm('Delete this user and all their data?')" title="Delete User">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- No Results -->
            <div id="noResults" style="display:none; padding: 30px 20px; text-align: center; color: var(--gray-500);">
                <i class="fa-solid fa-search" style="font-size:32px; color:var(--dark-600); margin-bottom:8px; display:block;"></i>
                <p>No users found matching your search.</p>
            </div>
        </div>
    </div>

    <script>
        // ========================================
        // SIDEBAR TOGGLE
        // ========================================
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
        });

        overlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        document.querySelectorAll('.sidebar-nav a').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });

        // ========================================
        // LIVE SEARCH
        // ========================================
        function liveSearch() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase().trim();
            const tbody = document.getElementById('userTableBody');
            const rows = tbody.getElementsByTagName('tr');
            const visibleCount = document.getElementById('visibleCount');
            const totalCount = document.getElementById('totalCount');
            const noResults = document.getElementById('noResults');
            let visibleRows = 0;
            const totalRows = rows.length;

            // If empty state row exists (has colspan), skip
            if (totalRows === 1 && rows[0].querySelector('td[colspan]')) {
                visibleCount.textContent = '0';
                return;
            }

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const name = row.dataset.name || '';
                const username = row.dataset.username || '';
                const email = row.dataset.email || '';
                const couple = row.dataset.couple || '';
                const rowText = row.textContent.toLowerCase();

                if (filter === '') {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    if (name.indexOf(filter) > -1 || 
                        username.indexOf(filter) > -1 || 
                        email.indexOf(filter) > -1 || 
                        couple.indexOf(filter) > -1 ||
                        rowText.indexOf(filter) > -1) {
                        row.style.display = '';
                        visibleRows++;
                    } else {
                        row.style.display = 'none';
                    }
                }
            }

            // Update counts
            visibleCount.textContent = visibleRows;
            totalCount.textContent = totalRows;

            // Show/hide no results
            if (noResults) {
                if (visibleRows === 0 && filter !== '') {
                    noResults.style.display = 'block';
                } else {
                    noResults.style.display = 'none';
                }
            }
        }

        function clearSearch() {
            const input = document.getElementById('searchInput');
            input.value = '';
            liveSearch();
            input.focus();
        }

        // Real-time search on input
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('input', liveSearch);
            }
        });

        // Keyboard shortcut: ESC to clear
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const input = document.getElementById('searchInput');
                if (document.activeElement === input) {
                    clearSearch();
                }
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                document.getElementById('searchInput').focus();
            }
        });

        console.log('✨ Master Admin Dashboard loaded successfully!');
    </script>
</body>
</html>