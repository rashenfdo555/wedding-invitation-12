<?php
// admin_master/create-user.php - REDESIGNED WITH PROFESSIONAL THEME

session_start();
require_once '../includes/db.php';

// Check if master admin is logged in
if (!isset($_SESSION['master_logged_in']) || $_SESSION['master_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$themes = getActiveThemes();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $couple_name = trim($_POST['couple_name'] ?? '');
    $wedding_date = $_POST['wedding_date'] ?? '';
    $theme_id = (int)($_POST['theme_id'] ?? 0);
    
    // Validate
    if (empty($username) || empty($password) || empty($email) || empty($full_name)) {
        $error = 'All fields are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif ($theme_id <= 0) {
        $error = 'Please select a theme.';
    } else {
        // Check if username exists
        $existing = getUserAdminByUsername($username);
        if ($existing) {
            $error = 'Username already exists. Please choose another.';
        } else {
            $data = [
                'username' => $username,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'email' => $email,
                'full_name' => $full_name,
                'couple_name' => $couple_name,
                'wedding_date' => $wedding_date,
                'theme_id' => $theme_id,
                'created_by' => $_SESSION['master_id']
            ];
            
            $user_id = createUserAdmin($data);
            
            if ($user_id) {
                // Get theme file name
                $theme = getThemeById($theme_id);
                $theme_file = $theme ? $theme['file_name'] : 'index.php';
                
                // Get default colors for this theme
                $default_colors = getDefaultColorsByTheme($theme_id);
                
                // Create default invitation for the user
                $slug = generateSlug($couple_name ?: $username);
                $invitation_data = [
                    'user_admin_id' => $user_id,
                    'theme_id' => $theme_id,
                    'slug' => $slug,
                    'couple_names' => $couple_name ?: $full_name,
                    'bride_name' => '',
                    'groom_name' => '',
                    'couple_headline' => $couple_name ?: $full_name,
                    'wedding_date' => $wedding_date ? date('M d Y', strtotime($wedding_date)) : '',
                    'month' => $wedding_date ? date('F', strtotime($wedding_date)) : '',
                    'day' => $wedding_date ? date('d', strtotime($wedding_date)) : '',
                    'year' => $wedding_date ? date('Y', strtotime($wedding_date)) : '',
                    'countdown_target' => $wedding_date ? $wedding_date . ' 17:00:00' : '',
                    'sub_headline' => 'Welcome to our wedding! We are excited to share this special day with you.',
                    'venue_title' => '',
                    'venue_location' => '',
                    'venue_address' => '',
                    'google_maps_url' => 'https://maps.google.com',
                    'event_time' => '5:00 PM To 11:00 PM',
                    'whatsapp_phone' => '',
                    'hero_image' => 'hero-couple.jpg',
                    'bride_image' => 'bride.jpg',
                    'groom_image' => 'groom.jpg',
                    'intro_video' => 'intro-open.mp4',
                    'intro_audio' => 'intro-music1.mp3',
                    'bg_audio' => 'intro-music.mp3',
                    'show_profiles' => 1
                ];
                
                createInvitation($invitation_data);
                
                $success = "
<div style='background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-radius: 12px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-left: 5px solid #2ECC71;'>
    <div style='display: flex; align-items: center; gap: 12px; margin-bottom: 20px;'>
        <span style='font-size: 28px;'>🎉</span>
        <h3 style='color: #2C3E50; margin: 0; font-weight: 600; font-size: 22px;'>User Created Successfully!</h3>
    </div>
    
    <div style='background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 15px 0;'>
        <table style='width: 100%; border-collapse: collapse;'>
            <tr>
                <td style='padding: 10px 15px; color: #7F8C8D; font-weight: 500; width: 140px;'>Username:</td>
                <td style='padding: 10px 15px; color: #2C3E50; font-weight: 600;'>" . htmlspecialchars($username) . "</td>
            </tr>
            <tr>
                <td style='padding: 10px 15px; color: #7F8C8D; font-weight: 500;'>Password:</td>
                <td style='padding: 10px 15px; color: #2C3E50; font-weight: 600;'>" . htmlspecialchars($password) . "</td>
            </tr>
            <tr>
                <td style='padding: 10px 15px; color: #7F8C8D; font-weight: 500;'>Theme:</td>
                <td style='padding: 10px 15px; color: #3498DB; font-weight: 600;'>" . htmlspecialchars($theme['name']) . "</td>
            </tr>
            <tr>
                <td style='padding: 10px 15px; color: #7F8C8D; font-weight: 500;'>Status:</td>
                <td style='padding: 10px 15px;'>
                    <span style='background: #2ECC71; color: white; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; display: inline-block;'>
                        ✅ Active
                    </span>
                    <span style='color: #7F8C8D; font-size: 13px; margin-left: 8px;'>Can login immediately</span>
                </td>
            </tr>
        </table>
    </div>
    
    <div style='display: flex; gap: 12px; margin-top: 20px; flex-wrap: wrap;'>
        <a href='index.php' style='
            background: linear-gradient(135deg, #2C3E50 0%, #34495E 100%);
            color: white; 
            padding: 10px 24px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: 500; 
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(44,62,80,0.2);
        ' onmouseover='this.style.transform=\"translateY(-2px)\"; this.style.boxShadow=\"0 4px 12px rgba(44,62,80,0.3)\";' 
        onmouseout='this.style.transform=\"translateY(0)\"; this.style.boxShadow=\"0 2px 8px rgba(44,62,80,0.2)\";'>
            <span>🏠</span> Back to Dashboard
        </a>
    </div>
</div>";            } else {
                $error = 'Failed to create user. Please try again.';
            }
        }
    }
}

$master_initials = strtoupper(substr($_SESSION['master_full_name'] ?? 'M', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User - Master Admin</title>
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
            max-width: 600px;
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
        .form-group label .required {
            color: var(--danger);
        }
        .form-group input, .form-group select {
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
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 4px rgba(212, 168, 88, 0.15);
        }
        .form-group input::placeholder {
            color: var(--gray-500);
        }
        .form-group select option {
            background: var(--dark-800);
            color: #fff;
        }
        .form-group small {
            color: var(--gray-500);
            font-size: 12px;
            display: block;
            margin-top: 4px;
        }
        
        .status-notice {
            background: var(--success-bg);
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            border-left: 4px solid var(--success);
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--success);
            font-size: 13px;
        }
        .status-notice i {
            font-size: 16px;
        }
        .status-notice strong {
            color: #fff;
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
        .alert-success a {
            color: var(--gold);
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
            .top-header .right .btn-primary span {
                display: none;
            }
            .top-header .right .btn-primary {
                padding: 10px 14px;
            }
            
            .form-card {
                padding: 20px;
                max-width: 100%;
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
            .form-group input, .form-group select {
                font-size: 13px;
                padding: 10px 14px;
            }
            .btn-primary {
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
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-crown"></i>
            <span>Master<span>Admin</span></span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Main Menu</div>
            <a href="index.php"><i class="fa-solid fa-gauge-high"></i> <span>Dashboard</span></a>
            <a href="create-user.php" class="active"><i class="fa-solid fa-user-plus"></i> <span>Create User</span></a>
            <a href="themes.php"><i class="fa-solid fa-palette"></i> <span>Themes</span></a>
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
                    <h1>Create New User</h1>
                    <p>Add a new wedding invitation user</p>
                </div>
            </div>
            <div class="right">
                <a href="index.php" class="btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> <span>Back</span>
                </a>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <?php echo $success; ?>
            </div>
        <?php else: ?>
            <div class="form-card">
                <div class="form-title">
                    <i class="fa-solid fa-user-plus"></i>
                    User Details
                </div>
                <form method="POST">
                    <div class="form-group">
                        <label for="username">Username <span class="required">*</span></label>
                        <input type="text" id="username" name="username" placeholder="e.g., johnsmith" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <input type="text" id="password" name="password" placeholder="Minimum 6 characters" required>
                        <small>User will use this to login</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" placeholder="user@example.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name <span class="required">*</span></label>
                        <input type="text" id="full_name" name="full_name" placeholder="e.g., John Smith" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="couple_name">Couple Name</label>
                        <input type="text" id="couple_name" name="couple_name" placeholder="e.g., John & Jane">
                    </div>
                    
                    <div class="form-group">
                        <label for="wedding_date">Wedding Date</label>
                        <input type="date" id="wedding_date" name="wedding_date">
                    </div>
                    
                    <div class="form-group">
                        <label for="theme_id">Select Theme <span class="required">*</span></label>
                        <select id="theme_id" name="theme_id" required>
                            <option value="">-- Select a theme --</option>
                            <?php foreach ($themes as $theme): ?>
                                <option value="<?php echo $theme['id']; ?>">
                                    <?php echo htmlspecialchars($theme['name']); ?> (<?php echo htmlspecialchars($theme['file_name']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small>This will determine the design/look of the invitation</small>
                    </div>
                    
                    <div class="status-notice">
                        <i class="fa-solid fa-check-circle"></i>
                        Account will be <strong>ACTIVE</strong> immediately after creation.
                    </div>
                    
                    <button type="submit" class="btn-primary" style="width:100%; justify-content:center;">
                        <i class="fa-solid fa-user-plus"></i> Create User
                    </button>
                </form>
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