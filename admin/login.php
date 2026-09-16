<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

$error = '';

// Check for status messages from URL
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'suspended':
            $error = 'Your account has been suspended. Please contact the master admin.';
            break;
        case 'pending':
            $error = 'Your account is pending approval. Please wait for the master admin to activate your account.';
            break;
        case 'inactive':
            $error = 'Your account is deactivated. Please contact the master admin.';
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $user = getUserAdminByUsername($username);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        // Check account status
        if ($user['account_status'] === 'suspended') {
            $error = 'Your account has been suspended. Please contact the master admin.';
        } elseif ($user['account_status'] === 'pending') {
            $error = 'Your account is pending approval. Please wait for the master admin to activate your account.';
        } elseif ($user['is_active'] != 1) {
            $error = 'Your account is deactivated. Please contact the master admin.';
        } else {
            // Account is active - allow login
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['couple_name'] = $user['couple_name'];
            
            // Update last login
            $conn = getDBConnection();
            $stmt = $conn->prepare("UPDATE user_admins SET last_login = NOW() WHERE id = ?");
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            
            header('Location: dashboard.php');
            exit;
        }
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Sign In - WeddingAdmin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ========================================
           PREMIUM LOGIN - LUXURIOUS & DECENT
           ======================================== */
        
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }
        
        :root {
            --primary: #6C5CE7;
            --primary-dark: #5A4BD1;
            --primary-light: #A29BFE;
            --primary-bg: rgba(108, 92, 231, 0.08);
            --primary-gradient: linear-gradient(135deg, #6C5CE7 0%, #A29BFE 100%);
            --gold: #F9CA24;
            --gold-light: #F9E79F;
            --rose: #FD79A8;
            --rose-light: #FFE5EE;
            --dark: #1A1A2E;
            --dark-800: #2D2D44;
            --dark-700: #4A4A6A;
            --gray-500: #8E8EA8;
            --gray-400: #B0B0C4;
            --gray-300: #D1D1E0;
            --gray-200: #E8E8F0;
            --gray-100: #F5F5FA;
            --bg-body: #F8F7FC;
            --bg-white: #FFFFFF;
            --success: #2ECC71;
            --success-bg: rgba(46, 204, 113, 0.12);
            --danger: #E74C3C;
            --danger-bg: rgba(231, 76, 60, 0.12);
            --shadow-xs: 0 1px 3px rgba(26,20,16,0.04);
            --shadow-sm: 0 2px 12px rgba(108, 92, 231, 0.08);
            --shadow-md: 0 8px 32px rgba(26,20,16,0.08);
            --shadow-lg: 0 16px 48px rgba(108, 92, 231, 0.12);
            --shadow-xl: 0 20px 60px rgba(108, 92, 231, 0.18);
            --radius-sm: 12px;
            --radius-md: 16px;
            --radius-lg: 24px;
            --radius-xl: 28px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --max-width: 440px;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background: linear-gradient(135deg, #F8F7FC 0%, #EDEBF7 50%, #E8E4F4 100%);
        }
        
        /* ========================================
           LOGIN CONTAINER
           ======================================== */
        .login-container {
            background: var(--bg-white);
            border-radius: var(--radius-lg);
            padding: 48px 40px 40px;
            max-width: var(--max-width);
            width: 100%;
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }
        
        .login-container::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(108, 92, 231, 0.03) 0%, transparent 70%);
            pointer-events: none;
        }
        
        /* ========================================
           HEADER
           ======================================== */
        .login-header {
            text-align: center;
            margin-bottom: 32px;
            position: relative;
            z-index: 1;
        }
        
        .login-header .logo-icon {
            width: 72px;
            height: 72px;
            background: var(--primary-gradient);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 30px;
            color: #fff;
            box-shadow: 0 4px 24px rgba(108, 92, 231, 0.3);
            transition: var(--transition);
        }
        
        .login-header .logo-icon:hover {
            transform: scale(1.05) rotate(-3deg);
        }
        
        .login-header .logo-icon i {
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.1));
        }
        
        .login-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
            letter-spacing: -0.5px;
        }
        
        .login-header h1 span {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .login-header .subtitle {
            color: var(--gray-500);
            font-size: 14px;
            margin-top: 4px;
            font-weight: 400;
        }
        
        .login-header .divider {
            width: 60px;
            height: 2px;
            background: var(--primary-gradient);
            margin: 12px auto 0;
            border-radius: 2px;
        }
        
        /* ========================================
           FORM
           ======================================== */
        .login-form {
            position: relative;
            z-index: 1;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--dark-700);
            margin-bottom: 6px;
        }
        
        .form-group label .required {
            color: var(--rose);
        }
        
        .form-group .input-wrap {
            position: relative;
        }
        
        .form-group .input-wrap .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 16px;
            transition: var(--transition);
            pointer-events: none;
        }
        
        .form-group .input-wrap input {
            width: 100%;
            padding: 14px 16px 14px 46px;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: var(--transition);
            background: var(--gray-100);
            color: var(--dark);
            outline: none;
        }
        
        .form-group .input-wrap input:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(108, 92, 231, 0.1);
        }
        
        .form-group .input-wrap input:focus ~ .input-icon,
        .form-group .input-wrap input:focus + .input-icon {
            color: var(--primary);
        }
        
        .form-group .input-wrap input::placeholder {
            color: var(--gray-400);
        }
        
        .form-group .input-wrap input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 30px var(--gray-100) inset !important;
            -webkit-text-fill-color: var(--dark) !important;
        }
        
        /* ========================================
           BUTTON
           ======================================== */
        .login-btn {
            width: 100%;
            padding: 16px 20px;
            background: var(--primary-gradient);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 4px;
            box-shadow: 0 4px 16px rgba(108, 92, 231, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .login-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(108, 92, 231, 0.4);
        }
        
        .login-btn:active {
            transform: scale(0.96);
        }
        
        .login-btn i {
            font-size: 16px;
        }
        
        /* ========================================
           ALERTS
           ======================================== */
        .alert {
            padding: 14px 18px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 3px solid transparent;
            position: relative;
            z-index: 1;
        }
        
        .alert i {
            font-size: 16px;
            flex-shrink: 0;
        }
        
        .alert-error {
            background: var(--danger-bg);
            color: #A93226;
            border-left-color: var(--danger);
        }
        
        .alert-warning {
            background: #FFF8E1;
            color: #856404;
            border-left-color: #FFC107;
        }
        
        /* ========================================
           FOOTER
           ======================================== */
        .login-footer {
            text-align: center;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-200);
            position: relative;
            z-index: 1;
        }
        
        .login-footer p {
            font-size: 13px;
            color: var(--gray-500);
        }
        
        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .login-footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .login-footer .footer-heart {
            color: var(--rose);
            margin: 0 4px;
        }
        
        /* ========================================
           DECORATIVE ELEMENTS
           ======================================== */
        .deco-ring {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(108, 92, 231, 0.06);
            pointer-events: none;
        }
        
        .deco-ring-1 {
            width: 200px;
            height: 200px;
            top: -80px;
            right: -60px;
        }
        
        .deco-ring-2 {
            width: 140px;
            height: 140px;
            bottom: -40px;
            left: -40px;
        }
        
        .deco-dot {
            position: absolute;
            border-radius: 50%;
            background: var(--primary-bg);
            pointer-events: none;
        }
        
        .deco-dot-1 {
            width: 8px;
            height: 8px;
            top: 60px;
            right: 30px;
        }
        
        .deco-dot-2 {
            width: 12px;
            height: 12px;
            bottom: 50px;
            right: 40px;
        }
        
        /* ========================================
           RESPONSIVE
           ======================================== */
        @media (max-width: 480px) {
            .login-container {
                padding: 32px 24px 28px;
                border-radius: var(--radius-md);
                margin: 10px;
            }
            
            .login-header .logo-icon {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
            
            .login-header .subtitle {
                font-size: 13px;
            }
            
            .form-group .input-wrap input {
                padding: 12px 14px 12px 42px;
                font-size: 13px;
            }
            
            .login-btn {
                font-size: 14px;
                padding: 14px;
            }
            
            .login-footer p {
                font-size: 12px;
            }
            
            .deco-ring-1 {
                width: 150px;
                height: 150px;
                top: -60px;
                right: -40px;
            }
            
            .deco-ring-2 {
                width: 100px;
                height: 100px;
                bottom: -30px;
                left: -30px;
            }
        }
        
        @media (max-width: 360px) {
            .login-container {
                padding: 24px 16px 20px;
            }
            
            .login-header .logo-icon {
                width: 48px;
                height: 48px;
                font-size: 20px;
            }
            
            .login-header h1 {
                font-size: 20px;
            }
        }
        
        /* ========================================
           ANIMATIONS
           ======================================== */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-container {
            animation: fadeUp 0.6s ease-out forwards;
        }
        
        .form-group {
            animation: fadeUp 0.6s ease-out 0.1s both;
        }
        
        .form-group:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .login-btn {
            animation: fadeUp 0.6s ease-out 0.3s both;
        }
        
        .login-footer {
            animation: fadeUp 0.6s ease-out 0.4s both;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Decorative Elements -->
        <div class="deco-ring deco-ring-1"></div>
        <div class="deco-ring deco-ring-2"></div>
        <div class="deco-dot deco-dot-1"></div>
        <div class="deco-dot deco-dot-2"></div>
        
        <!-- Header -->
        <div class="login-header">
            <div class="logo-icon">
                <i class="fa-solid fa-heart"></i>
            </div>
            <h1>Wedding<span>Admin</span></h1>
            <p class="subtitle">Customize your wedding invitation</p>
            <div class="divider"></div>
        </div>
        
        <!-- Alerts -->
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Form -->
        <form method="POST" autocomplete="off" class="login-form">
            <div class="form-group">
                <label for="username">Username <span class="required">*</span></label>
                <div class="input-wrap">
                    <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>
                    <i class="fa-solid fa-user input-icon"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <div class="input-wrap">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <i class="fa-solid fa-lock input-icon"></i>
                </div>
            </div>
            
            <button type="submit" class="login-btn">
                <i class="fa-solid fa-right-to-bracket"></i>
                Sign In
            </button>
        </form>
        
        <!-- Footer -->
        <div class="login-footer">
            <p>
                <span class="footer-heart"><i class="fa-solid fa-heart"></i></span>
                Wedding Invitation Admin
                <span class="footer-heart"><i class="fa-solid fa-heart"></i></span>
            </p>
        </div>
    </div>
</body>
</html>