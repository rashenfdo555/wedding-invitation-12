<?php
// admin/create-invitation.php - UX FOCUSED

require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

$error = '';
$success = '';
$schedule_items = [];

// Get current admin user info for folder creation
$admin_user = getUserAdminById($_SESSION['user_id']);
$username = $admin_user['username'] ?? 'default';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_dir = '../uploads/';
    
    // Get existing files with username path
    $hero_image = $_POST['hero_image_existing'] ?? 'hero-couple.jpg';
    $bride_image = $_POST['bride_image_existing'] ?? 'bride.jpg';
    $groom_image = $_POST['groom_image_existing'] ?? 'groom.jpg';
    $intro_video = $_POST['intro_video_existing'] ?? 'intro-open.mp4';
    $intro_audio = $_POST['intro_audio_existing'] ?? 'intro-music1.mp3';
    $bg_audio = $_POST['bg_audio_existing'] ?? 'intro-music.mp3';
    
    // Handle file uploads with username
    if (!empty($_FILES['hero_image']['name'])) {
        $uploaded = uploadFile($_FILES['hero_image'], $upload_dir . 'images/', $username);
        if ($uploaded) $hero_image = $uploaded;
    }
    if (!empty($_FILES['bride_image']['name'])) {
        $uploaded = uploadFile($_FILES['bride_image'], $upload_dir . 'images/', $username);
        if ($uploaded) $bride_image = $uploaded;
    }
    if (!empty($_FILES['groom_image']['name'])) {
        $uploaded = uploadFile($_FILES['groom_image'], $upload_dir . 'images/', $username);
        if ($uploaded) $groom_image = $uploaded;
    }
    if (!empty($_FILES['intro_video']['name'])) {
        $uploaded = uploadFile($_FILES['intro_video'], $upload_dir . 'video/', $username, ['mp4', 'webm', 'ogg', 'mov', 'avi']);
        if ($uploaded) $intro_video = $uploaded;
    }
    if (!empty($_FILES['intro_audio']['name'])) {
        $uploaded = uploadFile($_FILES['intro_audio'], $upload_dir . 'audio/', $username, ['mp3', 'wav', 'ogg', 'm4a']);
        if ($uploaded) $intro_audio = $uploaded;
    }
    if (!empty($_FILES['bg_audio']['name'])) {
        $uploaded = uploadFile($_FILES['bg_audio'], $upload_dir . 'audio/', $username, ['mp3', 'wav', 'ogg', 'm4a']);
        if ($uploaded) $bg_audio = $uploaded;
    }
    
    $data = [
        'slug' => generateSlug($_POST['couple_names']),
        'couple_names' => trim($_POST['couple_names'] ?? ''),
        'bride_name' => trim($_POST['bride_name'] ?? ''),
        'groom_name' => trim($_POST['groom_name'] ?? ''),
        'couple_headline' => trim($_POST['couple_headline'] ?? $_POST['couple_names'] ?? ''),
        'wedding_date' => trim($_POST['wedding_date'] ?? ''),
        'month' => trim($_POST['month'] ?? ''),
        'day' => trim($_POST['day'] ?? ''),
        'year' => trim($_POST['year'] ?? ''),
        'countdown_target' => trim($_POST['countdown_target'] ?? ''),
        'sub_headline' => trim($_POST['sub_headline'] ?? ''),
        'venue_title' => trim($_POST['venue_title'] ?? ''),
        'venue_location' => trim($_POST['venue_location'] ?? ''),
        'venue_address' => trim($_POST['venue_address'] ?? ''),
        'google_maps_url' => trim($_POST['google_maps_url'] ?? ''),
        'event_time' => trim($_POST['event_time'] ?? ''),
        'whatsapp_phone' => trim($_POST['whatsapp_phone'] ?? ''),
        'primary_color' => trim($_POST['primary_color'] ?? '#4A6FA5'),
        'secondary_color' => trim($_POST['secondary_color'] ?? '#F5F5FA'),
        'text_color' => trim($_POST['text_color'] ?? '#1A1A2E'),
        'hero_image' => $hero_image,
        'bride_image' => $bride_image,
        'groom_image' => $groom_image,
        'intro_video' => $intro_video,
        'intro_audio' => $intro_audio,
        'bg_audio' => $bg_audio,
        'show_profiles' => isset($_POST['show_profiles']) ? 1 : 0
    ];
    
    $invitation_id = createInvitation($data);
    
    if ($invitation_id) {
        if (isset($_POST['schedule_time']) && is_array($_POST['schedule_time'])) {
            for ($i = 0; $i < count($_POST['schedule_time']); $i++) {
                if (!empty($_POST['schedule_time'][$i]) && !empty($_POST['schedule_title'][$i])) {
                    addScheduleItem(
                        $invitation_id,
                        $_POST['schedule_time'][$i],
                        $_POST['schedule_title'][$i],
                        $_POST['schedule_desc'][$i] ?? '',
                        $_POST['schedule_icon'][$i] ?? 'fa-star',
                        $i + 1
                    );
                }
            }
        }
        $success = 'Invitation created successfully! 🎉';
        $schedule_items = [];
    } else {
        $error = 'Failed to create invitation. Please try again.';
    }
}

$icons = getScheduleIcons();
$initials = strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Invitation - Wedding Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ========================================
           CREATE INVITATION - UX FOCUSED
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
            --success: #2ECC71;
            --success-bg: rgba(46, 204, 113, 0.15);
            --danger: #E74C3C;
            --danger-bg: rgba(231, 76, 60, 0.15);
            --shadow-sm: 0 1px 3px rgba(26,20,16,0.06);
            --shadow-md: 0 4px 20px rgba(26,20,16,0.08);
            --shadow-lg: 0 8px 40px rgba(26,20,16,0.12);
            --radius: 14px;
            --radius-sm: 10px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --sidebar-width: 260px;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            color: var(--dark-900);
            line-height: 1.6;
        }

        ::-webkit-scrollbar {
            width: 6px;
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
        .btn-add {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: transparent;
            color: var(--primary);
            border: 2px dashed var(--gray-300);
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            font-family: 'Inter', sans-serif;
            width: 100%;
            justify-content: center;
        }
        .btn-add:hover {
            border-color: var(--primary);
            background: var(--primary-bg);
        }

        /* ========================================
           FORM
           ======================================== */
        .admin-form {
            max-width: 1000px;
        }
        .form-section {
            background: var(--bg-white);
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
            padding: 28px 32px;
            margin-bottom: 20px;
            transition: var(--transition);
        }
        .form-section:hover {
            border-color: var(--gray-300);
        }
        .form-section .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border-color);
        }
        .form-section .section-header .icon-wrap {
            width: 36px;
            height: 36px;
            background: var(--primary-bg);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 16px;
            flex-shrink: 0;
        }
        .form-section .section-header h2 {
            font-family: 'Inter', sans-serif;
            font-size: 17px;
            font-weight: 600;
            color: var(--dark-900);
        }
        .form-section .section-header .required-tag {
            margin-left: auto;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #fff;
            background: var(--primary);
            padding: 2px 12px;
            border-radius: 20px;
            font-family: 'Inter', sans-serif;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: var(--dark-700);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .form-group label .required {
            color: var(--danger);
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px 14px;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: var(--transition);
            background: var(--gray-100);
            width: 100%;
            color: var(--dark-900);
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(74, 111, 165, 0.1);
        }
        .form-group input[type="color"] {
            padding: 4px;
            height: 44px;
            cursor: pointer;
            background: #fff;
        }
        .form-group input[type="file"] {
            padding: 8px 10px;
            background: var(--gray-100);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .form-group .helper-text {
            font-size: 12px;
            color: var(--gray-500);
            margin-top: 4px;
        }
        .form-group .current-file {
            font-size: 12px;
            color: var(--gray-500);
            background: var(--gray-100);
            padding: 3px 12px;
            border-radius: 6px;
            margin-top: 4px;
            display: inline-block;
        }

        /* ========================================
           SCHEDULE ITEMS
           ======================================== */
        .schedule-item {
            background: var(--gray-100);
            padding: 18px 22px;
            border-radius: var(--radius-sm);
            margin-bottom: 14px;
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }
        .schedule-item:hover {
            border-color: var(--primary-light);
        }
        .schedule-item .schedule-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }
        .schedule-item .schedule-header .item-number {
            font-size: 13px;
            font-weight: 600;
            color: var(--gray-500);
        }
        .schedule-item .schedule-header .btn-remove {
            background: none;
            border: none;
            color: var(--gray-400);
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 6px;
            transition: var(--transition);
            font-size: 14px;
        }
        .schedule-item .schedule-header .btn-remove:hover {
            background: var(--danger-bg);
            color: var(--danger);
        }

        /* ========================================
           FORM ACTIONS
           ======================================== */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 8px;
        }
        .form-actions .btn {
            min-width: 140px;
        }

        /* ========================================
           ALERTS
           ======================================== */
        .alert {
            padding: 14px 20px;
            border-radius: var(--radius-sm);
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 3px solid transparent;
        }
        .alert i {
            font-size: 18px;
            flex-shrink: 0;
        }
        .alert-success {
            background: var(--success-bg);
            color: #1A7A4A;
            border-left-color: var(--success);
        }
        .alert-error {
            background: var(--danger-bg);
            color: #A93226;
            border-left-color: var(--danger);
        }

        /* ========================================
           CHECKBOX
           ======================================== */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            cursor: pointer;
        }
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--primary);
            flex-shrink: 0;
        }
        .checkbox-group .checkbox-label {
            font-weight: 500;
            font-size: 14px;
            color: var(--dark-700);
            cursor: pointer;
        }
        .checkbox-group .checkbox-hint {
            font-size: 12px;
            color: var(--gray-500);
        }

        /* ========================================
           RESPONSIVE
           ======================================== */
        @media (max-width: 1024px) {
            .form-grid {
                grid-template-columns: 1fr 1fr;
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

            .form-section {
                padding: 20px;
            }
            .form-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }
            .form-actions {
                flex-direction: column;
            }
            .form-actions .btn {
                width: 100%;
                justify-content: center;
                min-width: unset;
            }
            .schedule-item {
                padding: 14px 16px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 16px;
            }
            .form-section {
                padding: 14px 16px;
            }
            .top-header .header-left .page-title h1 {
                font-size: 17px;
            }
            .top-header {
                gap: 10px;
            }
            .checkbox-group {
                flex-wrap: wrap;
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
            <div class="brand-icon"><i class="fa-solid fa-heart"></i></div>
            <div class="brand-text">Wedding<span>Admin</span></div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Main Menu</div>
            <a href="index.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
            <a href="create-invitation.php" class="active"><i class="fa-solid fa-plus"></i> New Invitation</a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="avatar"><?php echo $initials; ?></div>
                <div class="user-details">
                    <div class="name"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></div>
                    <div class="role">Administrator</div>
                </div>
            </div>
            <a href="logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <button class="menu-toggle" id="menuToggle"><i class="fa-solid fa-bars"></i></button>
                <div class="page-title">
                    <h1>Create New Invitation</h1>
                    <p>Fill in the details to create a beautiful wedding invitation</p>
                </div>
            </div>
            <a href="index.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </header>

        <!-- Alerts -->
        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" enctype="multipart/form-data" class="admin-form">
            <!-- Couple Information -->
            <div class="form-section">
                <div class="section-header">
                    <div class="icon-wrap"><i class="fa-solid fa-users"></i></div>
                    <h2>Couple Information</h2>
                    <span class="required-tag">Required</span>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Couple Names <span class="required">*</span></label>
                        <input type="text" name="couple_names" placeholder="e.g., John & Jane" required>
                    </div>
                    <div class="form-group">
                        <label>Headline</label>
                        <input type="text" name="couple_headline" placeholder="e.g., John & Jane">
                    </div>
                    <div class="form-group">
                        <label>Bride Name <span class="required">*</span></label>
                        <input type="text" name="bride_name" placeholder="e.g., Jane" required>
                    </div>
                    <div class="form-group">
                        <label>Groom Name <span class="required">*</span></label>
                        <input type="text" name="groom_name" placeholder="e.g., John" required>
                    </div>
                    <div class="form-group full-width">
                        <label class="checkbox-group">
                            <input type="checkbox" name="show_profiles" value="1" checked>
                            <span class="checkbox-label">Show Bride & Groom Profiles Section</span>
                            <span class="checkbox-hint">— Uncheck to hide the photo section</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Wedding Date -->
            <div class="form-section">
                <div class="section-header">
                    <div class="icon-wrap"><i class="fa-solid fa-calendar"></i></div>
                    <h2>Wedding Date</h2>
                    <span class="required-tag">Required</span>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Wedding Date <span class="required">*</span></label>
                        <input type="text" name="wedding_date" placeholder="e.g., JULY 11 2026" required>
                    </div>
                    <div class="form-group">
                        <label>Month <span class="required">*</span></label>
                        <input type="text" name="month" placeholder="e.g., JULY" required>
                    </div>
                    <div class="form-group">
                        <label>Day <span class="required">*</span></label>
                        <input type="text" name="day" placeholder="e.g., 11" required>
                    </div>
                    <div class="form-group">
                        <label>Year <span class="required">*</span></label>
                        <input type="text" name="year" placeholder="e.g., 2026" required>
                    </div>
                    <div class="form-group full-width">
                        <label>Countdown Target <span class="required">*</span></label>
                        <input type="datetime-local" name="countdown_target" required>
                        <span class="helper-text">Set the exact date and time for the countdown timer</span>
                    </div>
                </div>
            </div>

            <!-- Love Story -->
            <div class="form-section">
                <div class="section-header">
                    <div class="icon-wrap"><i class="fa-solid fa-book-open"></i></div>
                    <h2>Love Story</h2>
                </div>
                <div class="form-group">
                    <label>Story / Sub Headline</label>
                    <textarea name="sub_headline" rows="4" placeholder="Tell your love story..."></textarea>
                    <span class="helper-text">Share your journey with your guests</span>
                </div>
            </div>

            <!-- Venue Details -->
            <div class="form-section">
                <div class="section-header">
                    <div class="icon-wrap"><i class="fa-solid fa-location-dot"></i></div>
                    <h2>Venue Details</h2>
                    <span class="required-tag">Required</span>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Venue Title <span class="required">*</span></label>
                        <input type="text" name="venue_title" placeholder="e.g., Aldwark Manor Estate" required>
                    </div>
                    <div class="form-group">
                        <label>Location <span class="required">*</span></label>
                        <input type="text" name="venue_location" placeholder="e.g., York" required>
                    </div>
                    <div class="form-group full-width">
                        <label>Address <span class="required">*</span></label>
                        <input type="text" name="venue_address" placeholder="e.g., Aldwark, Alne, York" required>
                    </div>
                    <div class="form-group full-width">
                        <label>Google Maps URL <span class="required">*</span></label>
                        <input type="url" name="google_maps_url" placeholder="https://maps.google.com" required>
                    </div>
                    <div class="form-group">
                        <label>Event Time <span class="required">*</span></label>
                        <input type="text" name="event_time" placeholder="e.g., 5:00 PM To 11:00 PM" required>
                    </div>
                    <div class="form-group">
                        <label>WhatsApp Phone <span class="required">*</span></label>
                        <input type="text" name="whatsapp_phone" placeholder="e.g., 94771234567" required>
                        <span class="helper-text">Include country code without +</span>
                    </div>
                </div>
            </div>

            <!-- Theme Colors -->
            <div class="form-section">
                <div class="section-header">
                    <div class="icon-wrap"><i class="fa-solid fa-palette"></i></div>
                    <h2>Theme Colors</h2>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Primary Color</label>
                        <input type="color" name="primary_color" value="#4A6FA5">
                        <span class="helper-text">Main accent color</span>
                    </div>
                    <div class="form-group">
                        <label>Secondary Color</label>
                        <input type="color" name="secondary_color" value="#F5F5FA">
                        <span class="helper-text">Background & light accents</span>
                    </div>
                    <div class="form-group">
                        <label>Text Color</label>
                        <input type="color" name="text_color" value="#1A1A2E">
                        <span class="helper-text">Main text color</span>
                    </div>
                </div>
            </div>

            <!-- Images & Media -->
            <div class="form-section">
                <div class="section-header">
                    <div class="icon-wrap"><i class="fa-solid fa-image"></i></div>
                    <h2>Images & Media</h2>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Hero Image</label>
                        <input type="file" name="hero_image" accept="image/*">
                        <span class="helper-text">Leave empty to use default</span>
                        <input type="hidden" name="hero_image_existing" value="hero-couple.jpg">
                    </div>
                    <div class="form-group">
                        <label>Bride Image</label>
                        <input type="file" name="bride_image" accept="image/*">
                        <input type="hidden" name="bride_image_existing" value="bride.jpg">
                    </div>
                    <div class="form-group">
                        <label>Groom Image</label>
                        <input type="file" name="groom_image" accept="image/*">
                        <input type="hidden" name="groom_image_existing" value="groom.jpg">
                    </div>
                    <div class="form-group">
                        <label>Intro Video</label>
                        <input type="file" name="intro_video" accept="video/mp4,video/webm">
                        <span class="helper-text">MP4, WebM, OGG</span>
                        <input type="hidden" name="intro_video_existing" value="intro-open.mp4">
                    </div>
                    <div class="form-group">
                        <label>Intro Audio</label>
                        <input type="file" name="intro_audio" accept="audio/mp3,audio/wav">
                        <span class="helper-text">MP3, WAV, OGG</span>
                        <input type="hidden" name="intro_audio_existing" value="intro-music1.mp3">
                    </div>
                    <div class="form-group">
                        <label>Background Audio</label>
                        <input type="file" name="bg_audio" accept="audio/mp3,audio/wav">
                        <span class="helper-text">MP3, WAV, OGG</span>
                        <input type="hidden" name="bg_audio_existing" value="intro-music.mp3">
                    </div>
                </div>
            </div>

            <!-- Schedule Timeline -->
            <div class="form-section">
                <div class="section-header">
                    <div class="icon-wrap"><i class="fa-solid fa-clock"></i></div>
                    <h2>Schedule Timeline</h2>
                </div>
                <div id="schedule-container">
                    <?php for ($i = 0; $i < 7; $i++): ?>
                        <div class="schedule-item">
                            <div class="schedule-header">
                                <span class="item-number">Item <?php echo $i + 1; ?></span>
                                <button type="button" class="btn-remove" onclick="this.closest('.schedule-item').remove()">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Time</label>
                                    <input type="text" name="schedule_time[]" placeholder="e.g., 5:00 PM">
                                </div>
                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="schedule_title[]" placeholder="e.g., Guest Arrival">
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <input type="text" name="schedule_desc[]" placeholder="e.g., Welcome & seating">
                                </div>
                                <div class="form-group">
                                    <label>Icon</label>
                                    <select name="schedule_icon[]">
                                        <?php foreach ($icons as $value => $label): ?>
                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
                <button type="button" class="btn-add" onclick="addScheduleItem()">
                    <i class="fa-solid fa-plus"></i> Add Schedule Item
                </button>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="reset" class="btn btn-secondary" onclick="return confirm('Clear all form fields?')">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-check"></i> Create Invitation
                </button>
            </div>
        </form>
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

        // ========================================
        // ADD SCHEDULE ITEM
        // ========================================
        function addScheduleItem() {
            const container = document.getElementById('schedule-container');
            const count = container.children.length;
            const item = document.createElement('div');
            item.className = 'schedule-item';
            item.innerHTML = `
                <div class="schedule-header">
                    <span class="item-number">Item ${count + 1}</span>
                    <button type="button" class="btn-remove" onclick="this.closest('.schedule-item').remove()">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Time</label>
                        <input type="text" name="schedule_time[]" placeholder="e.g., 5:00 PM">
                    </div>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="schedule_title[]" placeholder="e.g., Guest Arrival">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="schedule_desc[]" placeholder="e.g., Welcome & seating">
                    </div>
                    <div class="form-group">
                        <label>Icon</label>
                        <select name="schedule_icon[]">
                            <?php foreach ($icons as $value => $label): ?>
                                <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            `;
            container.appendChild(item);
        }
    </script>
</body>
</html>