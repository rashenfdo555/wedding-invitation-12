<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

$invitations = getAllInvitations();
$total = count($invitations);
$upcoming = 0;
$past = 0;
$active = 0;
$today = date('Y-m-d');

foreach ($invitations as $inv) {
    if ($inv['wedding_date'] >= $today) {
        $upcoming++;
        if ($inv['wedding_date'] <= date('Y-m-d', strtotime('+30 days'))) {
            $active++;
        }
    } else {
        $past++;
    }
}

$initials = strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Wedding Invitations</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ========================================
           ADMIN MASTER - UX FOCUSED
           ======================================== */
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary: #4A6FA5;
            --primary-dark: #3A5A8A;
            --primary-light: #7A9BC9;
            --primary-bg: rgba(74, 111, 165, 0.08);
            --accent: #E8A87C;
            --dark-900: #1A1A2E;
            --dark-800: #2D2D44;
            --dark-700: #4A4A6A;
            --gray-500: #8E8EA8;
            --gray-400: #B0B0C4;
            --gray-300: #D1D1E0;
            --gray-200: #E8E8F0;
            --gray-100: #F5F5FA;
            --bg-body: #F5F5FA;
            --bg-white: #FFFFFF;
            --bg-sidebar: #1A1A2E;
            --border-color: #E8E8F0;
            --shadow-sm: 0 1px 3px rgba(26,20,16,0.06);
            --shadow-md: 0 4px 20px rgba(26,20,16,0.08);
            --shadow-lg: 0 8px 40px rgba(26,20,16,0.12);
            --radius: 16px;
            --radius-sm: 10px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --success: #2ECC71;
            --success-bg: rgba(46, 204, 113, 0.15);
            --danger: #E74C3C;
            --danger-bg: rgba(231, 76, 60, 0.15);
            --warning: #F39C12;
            --warning-bg: rgba(243, 156, 18, 0.15);
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            color: var(--dark-900);
            line-height: 1.6;
        }

        /* ========================================
           SCROLLBAR
           ======================================== */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg-body);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary);
        }

        /* ========================================
           SIDEBAR OVERLAY
           ======================================== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            backdrop-filter: blur(4px);
        }
        .sidebar-overlay.active {
            display: block;
        }

        /* ========================================
           SIDEBAR
           ======================================== */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1000;
            transition: var(--transition);
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 28px 24px;
            display: flex;
            align-items: center;
            gap: 14px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .sidebar-brand .brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 2px 12px rgba(74, 111, 165, 0.3);
        }
        .sidebar-brand .brand-text {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 600;
            color: #fff;
            letter-spacing: -0.5px;
        }
        .sidebar-brand .brand-text span {
            color: var(--primary-light);
        }

        .sidebar-nav {
            flex: 1;
            padding: 20px 16px;
        }
        .sidebar-nav .nav-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: rgba(255,255,255,0.3);
            padding: 0 12px 12px 12px;
        }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            border-radius: 10px;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            transition: var(--transition);
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 4px;
        }
        .sidebar-nav a:hover {
            background: rgba(255,255,255,0.06);
            color: #fff;
        }
        .sidebar-nav a.active {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 4px 15px rgba(74, 111, 165, 0.3);
        }
        .sidebar-nav a i {
            width: 22px;
            text-align: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .sidebar-nav a .badge {
            margin-left: auto;
            background: rgba(255,255,255,0.1);
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .sidebar-nav a.active .badge {
            background: rgba(255,255,255,0.2);
        }

        .sidebar-footer {
            padding: 16px 20px 24px;
            border-top: 1px solid rgba(255,255,255,0.06);
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(255,255,255,0.04);
            margin-bottom: 8px;
        }
        .user-info .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            flex-shrink: 0;
        }
        .user-info .user-details {
            flex: 1;
        }
        .user-info .user-details .name {
            color: #fff;
            font-size: 14px;
            font-weight: 500;
        }
        .user-info .user-details .role {
            color: rgba(255,255,255,0.4);
            font-size: 12px;
        }
        .logout-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255,255,255,0.4);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding: 10px 12px;
            border-radius: 10px;
            transition: var(--transition);
        }
        .logout-btn:hover {
            background: var(--danger-bg);
            color: #E74C3C;
        }

        /* ========================================
           MAIN CONTENT
           ======================================== */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 32px 40px 40px;
            min-height: 100vh;
        }

        /* ========================================
           TOP HEADER
           ======================================== */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .top-header .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .top-header .header-left .menu-toggle {
            display: none;
            background: var(--bg-white);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 10px 12px;
            cursor: pointer;
            color: var(--dark-700);
            font-size: 18px;
            transition: var(--transition);
        }
        .top-header .header-left .menu-toggle:hover {
            background: var(--gray-100);
            border-color: var(--primary);
        }
        .top-header .header-left .page-title h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 600;
            color: var(--dark-900);
        }
        .top-header .header-left .page-title p {
            color: var(--gray-500);
            font-size: 14px;
            margin-top: 2px;
        }

        /* ========================================
           BUTTONS
           ======================================== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            text-decoration: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            box-shadow: 0 2px 12px rgba(74, 111, 165, 0.2);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(74, 111, 165, 0.35);
        }
        .btn-secondary {
            background: #fff;
            color: var(--dark-700);
            border: 1px solid var(--gray-200);
        }
        .btn-secondary:hover {
            background: var(--gray-100);
            border-color: var(--primary);
            color: var(--primary);
        }
        .btn-outline {
            background: transparent;
            color: var(--gray-500);
            border: 1.5px solid var(--gray-300);
        }
        .btn-outline:hover {
            background: var(--gray-100);
            border-color: var(--primary);
            color: var(--primary);
        }
        .btn-sm {
            padding: 6px 14px;
            font-size: 12px;
            border-radius: 6px;
        }
        .btn-danger {
            background: var(--danger);
            color: #fff;
        }
        .btn-danger:hover {
            background: #C0392B;
            transform: translateY(-2px);
        }

        /* ========================================
           STATS
           ======================================== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: var(--bg-white);
            padding: 24px 28px;
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 18px;
            transition: var(--transition);
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-light);
        }
        .stat-card .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        .stat-card .stat-icon.gold {
            background: var(--primary-bg);
            color: var(--primary);
        }
        .stat-card .stat-icon.blue {
            background: #EBF3FA;
            color: #2C7BE5;
        }
        .stat-card .stat-icon.green {
            background: var(--success-bg);
            color: var(--success);
        }
        .stat-card .stat-icon.purple {
            background: #F0EBFA;
            color: #8B5CF6;
        }
        .stat-card .stat-number {
            font-size: 26px;
            font-weight: 700;
            line-height: 1.2;
            color: var(--dark-900);
        }
        .stat-card .stat-label {
            font-size: 13px;
            color: var(--gray-500);
            font-weight: 500;
        }

        /* ========================================
           INVITATIONS SECTION
           ======================================== */
        .invitations-section {
            background: var(--bg-white);
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }
        .invitations-section .section-header {
            padding: 24px 28px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .invitations-section .section-header h2 {
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--dark-900);
        }
        .invitations-section .section-header h2 i {
            color: var(--primary);
        }

        /* ========================================
           EMPTY STATE
           ======================================== */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }
        .empty-state .empty-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
            color: var(--primary);
        }
        .empty-state h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--dark-900);
        }
        .empty-state p {
            color: var(--gray-500);
            margin-bottom: 24px;
        }

        /* ========================================
           INVITATIONS GRID
           ======================================== */
        .invitations-grid {
            padding: 20px 28px 28px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }
        .invitation-card {
            background: var(--bg-white);
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            overflow: hidden;
            transition: var(--transition);
        }
        .invitation-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-4px);
            border-color: var(--primary-light);
        }
        .invitation-card .card-preview {
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            position: relative;
            padding: 20px;
            text-align: center;
        }
        .invitation-card .card-preview .overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.2);
        }
        .invitation-card .card-preview span {
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
        }
        .invitation-card .card-body {
            padding: 18px 20px 16px;
        }
        .invitation-card .card-body h3 {
            font-size: 17px;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--dark-900);
        }
        .invitation-card .card-body .card-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 13px;
            color: var(--gray-500);
        }
        .invitation-card .card-body .card-meta i {
            width: 16px;
            margin-right: 6px;
            color: var(--gray-400);
        }
        .invitation-card .card-footer {
            padding: 12px 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            background: var(--gray-100);
        }
        .invitation-card .card-footer .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--bg-white);
            color: var(--gray-500);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            font-size: 14px;
        }
        .invitation-card .card-footer .btn-action:hover {
            background: var(--gray-100);
            border-color: var(--gray-300);
            color: var(--dark-900);
        }
        .invitation-card .card-footer .btn-action.view:hover {
            background: #EBF3FA;
            border-color: #2C7BE5;
            color: #2C7BE5;
        }
        .invitation-card .card-footer .btn-action.delete:hover {
            background: var(--danger-bg);
            border-color: var(--danger);
            color: var(--danger);
        }
        .invitation-card .card-footer .btn-action i {
            font-size: 14px;
        }

        /* ========================================
           RESPONSIVE
           ======================================== */
        @media (max-width: 1024px) {
            .invitations-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
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
                padding: 20px;
            }

            .top-header .header-left .menu-toggle {
                display: flex;
            }
            .top-header .header-left .page-title h1 {
                font-size: 22px;
            }
            .top-header .header-left .page-title p {
                display: none;
            }
            .top-header .header-right .btn span {
                display: none;
            }
            .top-header .header-right .btn {
                padding: 10px 14px;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }
            .stat-card {
                padding: 16px 18px;
            }
            .stat-card .stat-icon {
                width: 44px;
                height: 44px;
                font-size: 18px;
            }
            .stat-card .stat-number {
                font-size: 20px;
            }

            .invitations-grid {
                grid-template-columns: 1fr;
                padding: 16px;
            }
            .invitations-section .section-header {
                padding: 16px 20px;
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }
            .invitation-card .card-preview {
                height: 100px;
                font-size: 22px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 16px;
            }
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            .stat-card {
                padding: 14px 16px;
            }
            .stat-card .stat-number {
                font-size: 18px;
            }
            .top-header {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }
            .top-header .header-right {
                display: flex;
                justify-content: stretch;
            }
            .top-header .header-right .btn {
                width: 100%;
                justify-content: center;
            }
            .invitations-section .section-header .header-actions {
                flex-direction: column;
            }
            .invitations-section .section-header .header-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="fa-solid fa-heart"></i>
            </div>
            <div class="brand-text">Wedding<span>Admin</span></div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Main Menu</div>
            <a href="index.php" class="active">
                <i class="fa-solid fa-gauge-high"></i>
                Dashboard
                <span class="badge"><?php echo $total; ?></span>
            </a>
            <a href="create-invitation.php">
                <i class="fa-solid fa-plus"></i>
                New Invitation
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="avatar">
                    <?php echo $initials; ?>
                </div>
                <div class="user-details">
                    <div class="name"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></div>
                    <div class="role">Administrator</div>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <button class="menu-toggle" id="menuToggle" aria-label="Toggle sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="page-title">
                    <h1>Dashboard</h1>
                    <p>Manage your wedding invitations</p>
                </div>
            </div>
            <div class="header-right">
                <a href="create-invitation.php" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>
                    <span>New Invitation</span>
                </a>
            </div>
        </header>
        
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon gold">
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <div>
                    <div class="stat-number"><?php echo $total; ?></div>
                    <div class="stat-label">Total Invitations</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <div class="stat-number"><?php echo $upcoming; ?></div>
                    <div class="stat-label">Upcoming Weddings</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <div class="stat-number"><?php echo $active; ?></div>
                    <div class="stat-label">Active (Next 30 Days)</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <div class="stat-number"><?php echo $past; ?></div>
                    <div class="stat-label">Past Weddings</div>
                </div>
            </div>
        </div>
        
        <!-- Invitations List -->
        <div class="invitations-section">
            <div class="section-header">
                <h2><i class="fa-solid fa-list"></i> All Invitations</h2>
                <a href="create-invitation.php" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-plus"></i> Create New
                </a>
            </div>
            
            <?php if (empty($invitations)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fa-solid fa-envelope-open"></i>
                    </div>
                    <h3>No Invitations Yet</h3>
                    <p>Create your first wedding invitation to get started.</p>
                    <a href="create-invitation.php" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i> Create Your First Invitation
                    </a>
                </div>
            <?php else: ?>
                <div class="invitations-grid">
                    <?php foreach ($invitations as $inv): ?>
                        <div class="invitation-card">
                            <div class="card-preview" style="background: <?php echo $inv['primary_color'] ?? '#4A6FA5'; ?>">
                                <div class="overlay"></div>
                                <span><?php echo htmlspecialchars($inv['couple_names']); ?></span>
                            </div>
                            <div class="card-body">
                                <h3><?php echo htmlspecialchars($inv['couple_names']); ?></h3>
                                <div class="card-meta">
                                    <span>
                                        <i class="fa-solid fa-calendar"></i>
                                        <?php echo htmlspecialchars($inv['wedding_date'] ?? 'Date TBD'); ?>
                                    </span>
                                    <span>
                                        <i class="fa-solid fa-location-dot"></i>
                                        <?php echo htmlspecialchars($inv['venue_location'] ?? 'Location TBD'); ?>
                                    </span>
                                    <span>
                                        <i class="fa-solid fa-link"></i>
                                        <?php echo htmlspecialchars($inv['slug']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a href="../intro/index.php?slug=<?php echo $inv['slug']; ?>" target="_blank" class="btn-action view" title="View Invitation">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="edit-invitation.php?id=<?php echo $inv['id']; ?>" class="btn-action" title="Edit Invitation">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <a href="delete-invitation.php?id=<?php echo $inv['id']; ?>" class="btn-action delete" title="Delete Invitation" onclick="return confirm('Are you sure you want to delete this invitation? This action cannot be undone.')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

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
    </script>
</body>
</html>