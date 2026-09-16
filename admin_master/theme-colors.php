<?php
// admin_master/theme-colors.php - REDESIGNED WITH PROFESSIONAL THEME

session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if master admin is logged in
if (!isset($_SESSION['master_logged_in']) || $_SESSION['master_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$theme_id = (int)($_GET['theme_id'] ?? 0);
$theme = getThemeById($theme_id);

if (!$theme) {
    header('Location: themes.php');
    exit;
}

$colors = getDefaultColorsByTheme($theme_id);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $color_data = [
        'primary_color' => $_POST['primary_color'] ?? '#D4A858',
        'secondary_color' => $_POST['secondary_color'] ?? '#1A2A27',
        'text_color' => $_POST['text_color'] ?? '#E8F0ED',
        'bg_main_color' => $_POST['bg_main_color'] ?? '#0F1A18',
        'bg_card_color' => $_POST['bg_card_color'] ?? '#1A2A27',
        'text_muted_color' => $_POST['text_muted_color'] ?? '#8EA8A2',
        'text_light_gray' => $_POST['text_light_gray'] ?? '#5A7A72',
        'border_light_color' => $_POST['border_light_color'] ?? '#2A3F3A',
        'map_bg_color' => $_POST['map_bg_color'] ?? '#1A2A27'
    ];
    
    if (updateDefaultColors($theme_id, $color_data)) {
        $success = 'Theme colors updated successfully!';
        $colors = getDefaultColorsByTheme($theme_id);
    } else {
        $error = 'Failed to update theme colors.';
    }
}

$master_initials = strtoupper(substr($_SESSION['master_full_name'] ?? 'M', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme Colors - <?php echo htmlspecialchars($theme['name']); ?></title>
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
        
        /* ===== FORM CARD ===== */
        .form-card {
            background: var(--dark-800);
            border-radius: var(--radius);
            border: 1px solid var(--dark-700);
            padding: 32px;
            max-width: 800px;
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
        .form-group input[type="color"] {
            width: 100%;
            padding: 4px;
            height: 50px;
            border: 1.5px solid var(--dark-700);
            border-radius: var(--radius-sm);
            cursor: pointer;
            background: var(--dark-900);
        }
        .form-group input[type="color"]:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 4px rgba(212, 168, 88, 0.15);
        }
        .form-group .helper-text {
            color: var(--gray-500);
            font-size: 12px;
            margin-top: 4px;
        }
        
        .color-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .color-preview {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--dark-700);
            margin-top: 8px;
            background: var(--dark-900);
        }
        .color-preview .swatch {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            border: 1px solid var(--dark-700);
            flex-shrink: 0;
        }
        .color-preview .color-hex {
            font-size: 13px;
            color: var(--gray-500);
            font-family: monospace;
        }
        
        .theme-info {
            background: var(--dark-800);
            border-radius: var(--radius-sm);
            padding: 16px 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--gold);
        }
        .theme-info strong {
            font-size: 18px;
            color: #fff;
        }
        .theme-info .theme-file {
            color: var(--gray-500);
            margin-left: 12px;
            font-size: 13px;
        }
        .theme-info .theme-desc {
            color: var(--gray-500);
            font-size: 14px;
            margin-top: 4px;
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
        
        .form-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--dark-700);
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
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
            .color-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }
            .theme-info {
                padding: 12px 16px;
            }
            .theme-info strong {
                font-size: 16px;
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
            .color-preview {
                padding: 8px 12px;
            }
            .color-preview .swatch {
                width: 28px;
                height: 28px;
            }
            .btn-primary, .btn-secondary {
                width: 100%;
                justify-content: center;
            }
            .form-actions {
                flex-direction: column;
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
                    <h1>Theme Colors</h1>
                    <p>Customize default colors for <?php echo htmlspecialchars($theme['name']); ?></p>
                </div>
            </div>
            <div class="right">
                <a href="themes.php" class="btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> <span>Back to Themes</span>
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
        
        <div class="theme-info">
            <strong><?php echo htmlspecialchars($theme['name']); ?></strong>
            <span class="theme-file">(<?php echo htmlspecialchars($theme['file_name']); ?>)</span>
            <div class="theme-desc"><?php echo htmlspecialchars($theme['description'] ?? 'No description'); ?></div>
        </div>
        
        <div class="form-card">
            <div class="form-title">
                <i class="fa-solid fa-palette"></i>
                Default Color Scheme
            </div>
            <p style="color: var(--gray-500); font-size: 14px; margin-bottom: 20px;">
                These colors will be used as defaults when creating an invitation with this theme.
            </p>
            
            <form method="POST">
                <div class="color-grid">
                    <div class="form-group">
                        <label for="primary_color">Primary Color</label>
                        <input type="color" id="primary_color" name="primary_color" 
                               value="<?php echo htmlspecialchars($colors['primary_color'] ?? '#D4A858'); ?>">
                        <div class="color-preview">
                            <div class="swatch" style="background: <?php echo htmlspecialchars($colors['primary_color'] ?? '#D4A858'); ?>;"></div>
                            <span class="color-hex"><?php echo htmlspecialchars($colors['primary_color'] ?? '#D4A858'); ?></span>
                        </div>
                        <div class="helper-text">Used for accents, buttons, and highlights</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="secondary_color">Secondary Color</label>
                        <input type="color" id="secondary_color" name="secondary_color" 
                               value="<?php echo htmlspecialchars($colors['secondary_color'] ?? '#1A2A27'); ?>">
                        <div class="color-preview">
                            <div class="swatch" style="background: <?php echo htmlspecialchars($colors['secondary_color'] ?? '#1A2A27'); ?>;"></div>
                            <span class="color-hex"><?php echo htmlspecialchars($colors['secondary_color'] ?? '#1A2A27'); ?></span>
                        </div>
                        <div class="helper-text">Used for backgrounds and light accents</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="text_color">Text Primary Color</label>
                        <input type="color" id="text_color" name="text_color" 
                               value="<?php echo htmlspecialchars($colors['text_color'] ?? '#E8F0ED'); ?>">
                        <div class="color-preview">
                            <div class="swatch" style="background: <?php echo htmlspecialchars($colors['text_color'] ?? '#E8F0ED'); ?>;"></div>
                            <span class="color-hex"><?php echo htmlspecialchars($colors['text_color'] ?? '#E8F0ED'); ?></span>
                        </div>
                        <div class="helper-text">Main text color</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="text_muted_color">Text Muted Color</label>
                        <input type="color" id="text_muted_color" name="text_muted_color" 
                               value="<?php echo htmlspecialchars($colors['text_muted_color'] ?? '#8EA8A2'); ?>">
                        <div class="color-preview">
                            <div class="swatch" style="background: <?php echo htmlspecialchars($colors['text_muted_color'] ?? '#8EA8A2'); ?>;"></div>
                            <span class="color-hex"><?php echo htmlspecialchars($colors['text_muted_color'] ?? '#8EA8A2'); ?></span>
                        </div>
                        <div class="helper-text">Secondary/subtle text color</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="text_light_gray">Text Light Gray</label>
                        <input type="color" id="text_light_gray" name="text_light_gray" 
                               value="<?php echo htmlspecialchars($colors['text_light_gray'] ?? '#5A7A72'); ?>">
                        <div class="color-preview">
                            <div class="swatch" style="background: <?php echo htmlspecialchars($colors['text_light_gray'] ?? '#5A7A72'); ?>;"></div>
                            <span class="color-hex"><?php echo htmlspecialchars($colors['text_light_gray'] ?? '#5A7A72'); ?></span>
                        </div>
                        <div class="helper-text">Light text for labels and hints</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="bg_main_color">Background Main</label>
                        <input type="color" id="bg_main_color" name="bg_main_color" 
                               value="<?php echo htmlspecialchars($colors['bg_main_color'] ?? '#0F1A18'); ?>">
                        <div class="color-preview">
                            <div class="swatch" style="background: <?php echo htmlspecialchars($colors['bg_main_color'] ?? '#0F1A18'); ?>;"></div>
                            <span class="color-hex"><?php echo htmlspecialchars($colors['bg_main_color'] ?? '#0F1A18'); ?></span>
                        </div>
                        <div class="helper-text">Main page background</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="bg_card_color">Card Background</label>
                        <input type="color" id="bg_card_color" name="bg_card_color" 
                               value="<?php echo htmlspecialchars($colors['bg_card_color'] ?? '#1A2A27'); ?>">
                        <div class="color-preview">
                            <div class="swatch" style="background: <?php echo htmlspecialchars($colors['bg_card_color'] ?? '#1A2A27'); ?>;"></div>
                            <span class="color-hex"><?php echo htmlspecialchars($colors['bg_card_color'] ?? '#1A2A27'); ?></span>
                        </div>
                        <div class="helper-text">Background for cards and sections</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="border_light_color">Border Light</label>
                        <input type="color" id="border_light_color" name="border_light_color" 
                               value="<?php echo htmlspecialchars($colors['border_light_color'] ?? '#2A3F3A'); ?>">
                        <div class="color-preview">
                            <div class="swatch" style="background: <?php echo htmlspecialchars($colors['border_light_color'] ?? '#2A3F3A'); ?>;"></div>
                            <span class="color-hex"><?php echo htmlspecialchars($colors['border_light_color'] ?? '#2A3F3A'); ?></span>
                        </div>
                        <div class="helper-text">Light border color</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="map_bg_color">Map Background</label>
                        <input type="color" id="map_bg_color" name="map_bg_color" 
                               value="<?php echo htmlspecialchars($colors['map_bg_color'] ?? '#1A2A27'); ?>">
                        <div class="color-preview">
                            <div class="swatch" style="background: <?php echo htmlspecialchars($colors['map_bg_color'] ?? '#1A2A27'); ?>;"></div>
                            <span class="color-hex"><?php echo htmlspecialchars($colors['map_bg_color'] ?? '#1A2A27'); ?></span>
                        </div>
                        <div class="helper-text">Map placeholder background</div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-save"></i> Save Colors
                    </button>
                </div>
            </form>
        </div>
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