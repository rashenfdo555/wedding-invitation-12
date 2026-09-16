<?php
// admin_master/edit-user.php - REDESIGNED WITH PROFESSIONAL THEME

session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['master_logged_in']) || $_SESSION['master_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;
$user = getUserAdminById($id);
if (!$user) {
    header('Location: index.php');
    exit;
}

$themes = getActiveThemes();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Handle password change
    if (isset($_POST['change_password']) && $_POST['change_password'] == 1) {
        $new_password = $_POST['new_password'] ?? '';
        if (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            if (changeUserAdminPassword($id, $hash)) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password.';
            }
        }
    } 
    // Handle user update
    else {
        $theme_id = (int)($_POST['theme_id'] ?? 0);
        
        if ($theme_id <= 0) {
            $error = 'Please select a theme.';
        } else {
            // Get values from POST
            $email = trim($_POST['email'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $couple_name = trim($_POST['couple_name'] ?? '');
            $wedding_date = $_POST['wedding_date'] ?? null;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $account_status = $_POST['account_status'] ?? 'active';
            
            // Validate account_status
            $valid_statuses = ['active', 'suspended', 'pending'];
            if (!in_array($account_status, $valid_statuses)) {
                $account_status = 'active';
            }
            
            // ============================================================
            // DIRECT DATABASE UPDATE - 100% WORKING
            // ============================================================
            $conn = getDBConnection();
            
            // Build SQL directly
            $sql = "UPDATE user_admins SET 
                    email = '{$conn->real_escape_string($email)}',
                    full_name = '{$conn->real_escape_string($full_name)}',
                    couple_name = '{$conn->real_escape_string($couple_name)}',
                    wedding_date = " . ($wedding_date ? "'$wedding_date'" : "NULL") . ",
                    is_active = $is_active,
                    account_status = '$account_status',
                    theme_id = $theme_id
                    WHERE id = $id";
            
            if ($conn->query($sql)) {
                // Update feature flags
                $has_gallery = isset($_POST['has_gallery']) ? 1 : 0;
                $has_table_finder = isset($_POST['has_table_finder']) ? 1 : 0;
                updateUserFeatures($id, $has_gallery, $has_table_finder);
                
                // If theme changed, update invitation theme
                $invitation = getInvitationByUserId($id);
                if ($invitation) {
                    $stmt = $conn->prepare("UPDATE invitations SET theme_id = ? WHERE id = ?");
                    $stmt->bind_param("ii", $theme_id, $invitation['id']);
                    $stmt->execute();
                    resetInvitationColorsToTheme($invitation['id'], $theme_id);
                }
                
                $success = "User updated successfully!<br>Status set to: <strong>{$account_status}</strong>";
                $user = getUserAdminById($id);
            } else {
                $error = 'Failed to update user: ' . $conn->error;
            }
        }
    }
}

// Get current feature flags
$features = getUserFeatures($id);
$invitation = getInvitationByUserId($id);

// Refresh user data
$user = getUserAdminById($id);
$current_status = $user['account_status'] ?? 'active';

$master_initials = strtoupper(substr($_SESSION['master_full_name'] ?? 'M', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Master Admin</title>
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
        .top-header .left .page-title h1 .highlight {
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
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--danger);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }
        .btn-danger:hover {
            background: #C0392B;
            transform: translateY(-2px);
        }
        
        /* ===== FORM CARD ===== */
        .form-card {
            background: var(--dark-800);
            border-radius: var(--radius);
            border: 1px solid var(--dark-700);
            padding: 28px 32px;
            max-width: 650px;
            margin-bottom: 20px;
        }
        .form-card .form-title {
            font-size: 17px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-card .form-title i {
            color: var(--gold);
        }
        .form-card .form-title .badge {
            margin-left: auto;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 2px 12px;
            border-radius: 20px;
        }
        .form-card .form-title .badge.active {
            background: var(--success-bg);
            color: var(--success);
        }
        .form-card .form-title .badge.suspended {
            background: var(--danger-bg);
            color: var(--danger);
        }
        .form-card .form-title .badge.pending {
            background: var(--warning-bg);
            color: var(--warning);
        }
        .form-group {
            margin-bottom: 18px;
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
        .form-group input:disabled {
            opacity: 0.5;
            cursor: not-allowed;
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
        .form-group .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 0;
        }
        .form-group .checkbox-group input {
            width: auto;
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--gold);
        }
        .form-group .checkbox-group label {
            margin-bottom: 0;
            cursor: pointer;
            font-weight: 500;
            color: var(--gray-300);
        }
        .form-group .checkbox-group .feature-desc {
            font-size: 12px;
            color: var(--gray-500);
            font-weight: 300;
        }
        hr {
            border: none;
            border-top: 1px solid var(--dark-700);
            margin: 20px 0;
        }
        
        .status-display {
            padding: 14px 18px;
            border-radius: var(--radius-sm);
            margin-bottom: 6px;
            font-size: 13px;
        }
        .status-display.active {
            background: var(--success-bg);
            color: var(--success);
            border-left: 3px solid var(--success);
        }
        .status-display.suspended {
            background: var(--danger-bg);
            color: var(--danger);
            border-left: 3px solid var(--danger);
        }
        .status-display.pending {
            background: var(--warning-bg);
            color: var(--warning);
            border-left: 3px solid var(--warning);
        }
        .status-display strong {
            display: block;
            font-size: 14px;
        }
        .status-display p {
            margin-top: 4px;
            font-size: 13px;
            opacity: 0.8;
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
            .btn-primary, .btn-secondary, .btn-danger {
                width: 100%;
                justify-content: center;
            }
            .form-card .form-title {
                flex-wrap: wrap;
            }
            .form-card .form-title .badge {
                margin-left: 0;
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
                    <h1>Edit User: <span class="highlight"><?php echo htmlspecialchars($user['full_name']); ?></span></h1>
                    <p>Manage user account and feature access</p>
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
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <!-- Current Status -->
        <div class="form-card">
            <div class="form-title">
                <i class="fa-solid fa-circle-info"></i>
                Account Status
                <span class="badge <?php echo $current_status; ?>">
                    <?php echo strtoupper($current_status); ?>
                </span>
            </div>
            <div class="status-display <?php echo $current_status; ?>">
                <strong>
                    <?php if ($current_status == 'active'): ?>
                        ✅ Account is active
                    <?php elseif ($current_status == 'suspended'): ?>
                        ⛔ Account is suspended
                    <?php else: ?>
                        ⏳ Account is pending approval
                    <?php endif; ?>
                </strong>
                <p>
                    <?php if ($current_status == 'active'): ?>
                        User can login and access all enabled features.
                    <?php elseif ($current_status == 'suspended'): ?>
                        User cannot login until the account is reactivated.
                    <?php else: ?>
                        User cannot login until the account is activated.
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <!-- User Information -->
        <div class="form-card">
            <div class="form-title">
                <i class="fa-solid fa-user"></i>
                User Information
            </div>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    <small>Username cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Full Name <span class="required">*</span></label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="couple_name">Couple Name</label>
                    <input type="text" id="couple_name" name="couple_name" value="<?php echo htmlspecialchars($user['couple_name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="wedding_date">Wedding Date</label>
                    <input type="date" id="wedding_date" name="wedding_date" value="<?php echo $user['wedding_date']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="theme_id">Theme <span class="required">*</span></label>
                    <select id="theme_id" name="theme_id" required>
                        <option value="">-- Select a theme --</option>
                        <?php foreach ($themes as $theme): ?>
                            <option value="<?php echo $theme['id']; ?>" <?php echo ($user['theme_id'] == $theme['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($theme['name']); ?> (<?php echo htmlspecialchars($theme['file_name']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small>Changing the theme will update the invitation design and reset colors</small>
                </div>
                
                <div class="form-group">
                    <label for="account_status">Account Status <span class="required">*</span></label>
                    <select id="account_status" name="account_status" required>
                        <option value="active" <?php echo ($current_status == 'active') ? 'selected="selected"' : ''; ?>>Active - User can login</option>
                        <option value="suspended" <?php echo ($current_status == 'suspended') ? 'selected="selected"' : ''; ?>>Suspended - User cannot login</option>
                        <option value="pending" <?php echo ($current_status == 'pending') ? 'selected="selected"' : ''; ?>>Pending - User cannot login</option>
                    </select>
                    <small>Changing status to "Active" will allow the user to login immediately.</small>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" <?php echo ($user['is_active'] == 1) ? 'checked="checked"' : ''; ?>>
                        <label for="is_active">Account Enabled</label>
                    </div>
                    <small>If unchecked, the account is disabled even if status is "Active".</small>
                </div>
                
                <hr>
                
                <div class="form-title" style="font-size:15px; margin-bottom:12px;">
                    <i class="fa-solid fa-puzzle-piece"></i>
                    Feature Access
                </div>
                <p style="color: var(--gray-500); font-size: 13px; margin-bottom: 16px;">
                    Enable or disable features for this user. If disabled, the feature won't appear in their invitation.
                </p>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="has_gallery" name="has_gallery" <?php echo ($features['has_gallery'] ?? 0) ? 'checked="checked"' : ''; ?>>
                        <label for="has_gallery">
                            <i class="fa-solid fa-images"></i> Gallery
                            <span class="feature-desc">- Allow user to upload and display wedding photos</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="has_table_finder" name="has_table_finder" <?php echo ($features['has_table_finder'] ?? 0) ? 'checked="checked"' : ''; ?>>
                        <label for="has_table_finder">
                            <i class="fa-solid fa-magnifying-glass"></i> Find Your Table
                            <span class="feature-desc">- Allow user to add guest table assignments</span>
                        </label>
                    </div>
                </div>
                
                <hr>
                
                <button type="submit" class="btn-primary" name="update_user" value="1">
                    <i class="fa-solid fa-save"></i> Update User
                </button>
            </form>
        </div>
        
        <!-- Change Password -->
        <div class="form-card">
            <div class="form-title">
                <i class="fa-solid fa-key"></i>
                Change Password
            </div>
            <form method="POST" action="">
                <input type="hidden" name="change_password" value="1">
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="text" id="new_password" name="new_password" placeholder="Enter new password (min 6 characters)" required>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-key"></i> Change Password
                </button>
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