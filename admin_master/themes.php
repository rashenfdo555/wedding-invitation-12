<?php
// admin_master/themes.php - REDESIGNED WITH PROFESSIONAL THEME

session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if master admin is logged in
if (!isset($_SESSION['master_logged_in']) || $_SESSION['master_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$themes = getAllThemes();

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $theme_id = $_POST['theme_id'] ?? 0;
    
    $name = trim($_POST['name'] ?? '');
    $file_name = trim($_POST['file_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($name) || empty($file_name)) {
        $error = 'Theme name and file name are required.';
    } else {
        // Validate file_name (must be index*.php)
        if (!preg_match('/^index[0-9]*\.php$/', $file_name)) {
            $error = 'File name must be in format: index.php, index1.php, index2.php, etc.';
        } else {
            // Check if file exists in invitation folder
            $file_path = '../invitation/' . $file_name;
            if (!file_exists($file_path)) {
                $error = 'File "' . $file_name . '" does not exist in the invitation folder.';
            } else {
                if ($action === 'add') {
                    $data = [
                        'name' => $name,
                        'file_name' => $file_name,
                        'description' => $description,
                        'preview_image' => null,
                        'is_active' => $is_active
                    ];
                    
                    if (createTheme($data)) {
                        $success = 'Theme created successfully!';
                        $themes = getAllThemes();
                    } else {
                        $error = 'Failed to create theme.';
                    }
                } elseif ($action === 'edit' && $theme_id > 0) {
                    $data = [
                        'name' => $name,
                        'file_name' => $file_name,
                        'description' => $description,
                        'preview_image' => null,
                        'is_active' => $is_active
                    ];
                    
                    if (updateTheme($theme_id, $data)) {
                        $success = 'Theme updated successfully!';
                        $themes = getAllThemes();
                    } else {
                        $error = 'Failed to update theme.';
                    }
                }
            }
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Check if any user is using this theme
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_admins WHERE theme_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $error = 'Cannot delete this theme. It is currently assigned to ' . $row['count'] . ' user(s).';
    } else {
        if (deleteTheme($id)) {
            $success = 'Theme deleted successfully!';
            $themes = getAllThemes();
        } else {
            $error = 'Failed to delete theme.';
        }
    }
}

// Get theme data for editing
$edit_theme = null;
if (isset($_GET['edit'])) {
    $edit_theme = getThemeById((int)$_GET['edit']);
}

$master_initials = strtoupper(substr($_SESSION['master_full_name'] ?? 'M', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme Management - Master Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ========================================
           MASTER ADMIN - PROFESSIONAL THEME (Shared)
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
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--dark-700);
            color: var(--gray-300);
            border: 1px solid var(--dark-600);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }
        .btn-secondary:hover {
            background: var(--dark-600);
            color: #fff;
        }
        .btn-danger {
            background: var(--danger);
            color: #fff;
        }
        .btn-danger:hover { background: #e74c3c; }
        
        /* ===== FORM CARD ===== */
        .form-card {
            background: var(--dark-800);
            border-radius: var(--radius);
            border: 1px solid var(--dark-700);
            padding: 32px;
            max-width: 700px;
            margin-bottom: 30px;
        }
        .form-card .form-title {
            font-size: 18px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-card .form-title i {
            color: var(--gold);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--gray-300);
            margin-bottom: 6px;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--dark-700);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background: var(--dark-900);
            color: #fff;
            transition: all 0.3s;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 4px rgba(212, 168, 88, 0.15);
        }
        .form-group input::placeholder {
            color: var(--gray-500);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 60px;
        }
        .form-group .helper-text {
            color: var(--gray-500);
            font-size: 12px;
            margin-top: 4px;
        }
        .form-group .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
        }
        .form-group .checkbox-group input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--gold);
        }
        .form-group .checkbox-group label {
            margin-bottom: 0;
            cursor: pointer;
            color: var(--gray-300);
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 3px solid transparent;
        }
        .alert i {
            font-size: 16px;
            flex-shrink: 0;
        }
        .alert-error {
            background: var(--danger-bg);
            color: #F1948A;
            border-left-color: var(--danger);
        }
        .alert-success {
            background: var(--success-bg);
            color: #82E0AA;
            border-left-color: var(--success);
        }
        
        /* ===== THEMES GRID ===== */
        .themes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .theme-card {
            background: var(--dark-800);
            border-radius: var(--radius);
            border: 1px solid var(--dark-700);
            padding: 24px;
            transition: all 0.3s;
        }
        .theme-card:hover {
            border-color: var(--gold);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        .theme-card .theme-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .theme-card .theme-header h3 {
            font-size: 18px;
            color: #fff;
        }
        .theme-card .theme-header .theme-file {
            font-size: 12px;
            color: var(--gray-500);
            background: var(--dark-700);
            padding: 2px 12px;
            border-radius: 4px;
            font-family: monospace;
        }
        .theme-card .theme-desc {
            color: var(--gray-500);
            font-size: 14px;
            margin: 8px 0 12px;
        }
        .theme-card .theme-status {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .theme-card .theme-status.active {
            background: var(--success-bg);
            color: var(--success);
        }
        .theme-card .theme-status.inactive {
            background: var(--dark-700);
            color: var(--gray-500);
        }
        .theme-card .theme-actions {
            display: flex;
            gap: 8px;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid var(--dark-700);
            flex-wrap: wrap;
        }
        .theme-card .theme-actions a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            text-decoration: none;
            transition: all 0.3s;
            border: 1px solid var(--dark-700);
            background: var(--dark-900);
            color: var(--gray-500);
        }
        .theme-card .theme-actions a:hover {
            background: var(--dark-700);
            color: #fff;
        }
        .theme-card .theme-actions a.edit:hover {
            border-color: var(--gold);
            color: var(--gold);
        }
        .theme-card .theme-actions a.delete:hover {
            border-color: var(--danger);
            color: var(--danger);
        }
        .theme-card .theme-actions a.colors:hover {
            border-color: var(--primary-light);
            color: var(--primary-light);
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
        }
        
        /* ===== RESPONSIVE ===== */
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
            .top-header .right .btn-primary span,
            .top-header .right .btn-secondary span {
                display: none;
            }
            .top-header .right .btn-primary,
            .top-header .right .btn-secondary {
                padding: 10px 14px;
            }
            
            .form-card {
                padding: 20px;
                max-width: 100%;
            }
            .themes-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 12px;
            }
            .top-header .left .page-title h1 {
                font-size: 17px;
            }
            .top-header {
                gap: 10px;
            }
            .form-card {
                padding: 16px;
            }
            .form-group input, .form-group textarea {
                font-size: 13px;
                padding: 10px 14px;
            }
            .btn-primary, .btn-secondary {
                width: 100%;
                justify-content: center;
            }
            .theme-card .theme-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
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
            <a href="index.php"><i class="fa-solid fa-gauge-high"></i> <span>Dashboard</span></a>
            <a href="create-user.php"><i class="fa-solid fa-user-plus"></i> <span>Create User</span></a>
            <a href="themes.php" class="active"><i class="fa-solid fa-palette"></i> <span>Themes</span></a>
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
    <div class="main-content">
        <div class="top-header">
            <div class="left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="page-title">
                    <h1>Theme Management</h1>
                    <p>Manage invitation themes and colors</p>
                </div>
            </div>
            <div class="right">
                <a href="themes.php" class="btn-secondary btn-sm">
                    <i class="fa-solid fa-rotate-right"></i> <span>Refresh</span>
                </a>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <!-- Add/Edit Theme Form -->
        <div class="form-card">
            <div class="form-title">
                <i class="fa-solid fa-palette"></i>
                <?php echo $edit_theme ? 'Edit Theme' : 'Add New Theme'; ?>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $edit_theme ? 'edit' : 'add'; ?>">
                <?php if ($edit_theme): ?>
                    <input type="hidden" name="theme_id" value="<?php echo $edit_theme['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Theme Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" placeholder="e.g., Classic Gold" 
                           value="<?php echo $edit_theme ? htmlspecialchars($edit_theme['name']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="file_name">File Name <span class="required">*</span></label>
                    <input type="text" id="file_name" name="file_name" placeholder="e.g., index1.php" 
                           value="<?php echo $edit_theme ? htmlspecialchars($edit_theme['file_name']) : ''; ?>" required>
                    <div class="helper-text">Must be in format: index.php, index1.php, index2.php, etc. File must exist in the invitation folder.</div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Brief description of this theme"><?php echo $edit_theme ? htmlspecialchars($edit_theme['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" <?php echo ($edit_theme && $edit_theme['is_active'] == 1) ? 'checked' : 'checked'; ?>>
                        <label for="is_active">Active (Available for selection)</label>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-save"></i> 
                    <?php echo $edit_theme ? 'Update Theme' : 'Create Theme'; ?>
                </button>
                
                <?php if ($edit_theme): ?>
                    <a href="themes.php" class="btn-secondary" style="margin-left:10px;">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Themes List -->
        <h2 style="font-family: 'Playfair Display', serif; font-size: 22px; color: #fff; margin: 30px 0 16px;">
            Available Themes <span style="font-size: 14px; color: var(--gray-500); font-family: 'Inter', sans-serif;">(<?php echo count($themes); ?>)</span>
        </h2>
        
        <?php if (empty($themes)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-palette"></i>
                <h4>No Themes Created</h4>
                <p>Add your first theme using the form above.</p>
            </div>
        <?php else: ?>
            <div class="themes-grid">
                <?php foreach ($themes as $theme): ?>
                    <div class="theme-card">
                        <div class="theme-header">
                            <h3><?php echo htmlspecialchars($theme['name']); ?></h3>
                            <span class="theme-file"><?php echo htmlspecialchars($theme['file_name']); ?></span>
                        </div>
                        <div class="theme-desc">
                            <?php echo htmlspecialchars($theme['description'] ?? 'No description'); ?>
                        </div>
                        <div>
                            <span class="theme-status <?php echo $theme['is_active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $theme['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                        <div class="theme-actions">
                            <a href="themes.php?edit=<?php echo $theme['id']; ?>" class="edit">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </a>
                            <a href="themes.php?delete=<?php echo $theme['id']; ?>" class="delete" onclick="return confirm('Delete this theme? This cannot be undone.')">
                                <i class="fa-solid fa-trash-can"></i> Delete
                            </a>
                            <a href="theme-colors.php?theme_id=<?php echo $theme['id']; ?>" class="colors">
                                <i class="fa-solid fa-palette"></i> Colors
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Sidebar Toggle
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