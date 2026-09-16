<?php
session_start();
require_once '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $user = getMasterAdminByUsername($username);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['master_logged_in'] = true;
        $_SESSION['master_id'] = $user['id'];
        $_SESSION['master_username'] = $user['username'];
        $_SESSION['master_full_name'] = $user['full_name'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ========================================
           MASTER LOGIN - PROFESSIONAL THEME
           ======================================== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary: #2D7D6F;
            --primary-dark: #1F5C51;
            --primary-light: #4A9A8A;
            --primary-bg: rgba(45, 125, 111, 0.1);
            --gold: #D4A858;
            --gold-light: #E8C87A;
            --dark-900: #0F1A18;
            --dark-800: #1A2A27;
            --dark-700: #2A3F3A;
            --dark-600: #3D5A54;
            --gray-500: #8EA8A2;
            --gray-400: #B0C4BE;
            --gray-300: #D1E0DB;
            --gray-200: #E8F0ED;
            --gray-100: #F5FAF8;
            --shadow-xl: 0 12px 60px rgba(0, 0, 0, 0.5);
            --radius-lg: 20px;
            --radius-md: 14px;
            --radius-sm: 10px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0F1A18 0%, #1A2A27 50%, #2A3F3A 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            -webkit-font-smoothing: antialiased;
        }
        
        .login-container {
            background: var(--dark-800);
            border-radius: var(--radius-lg);
            padding: 48px 40px 40px;
            max-width: 420px;
            width: 100%;
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--gold), var(--gold-light), var(--gold));
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .login-header .logo-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 28px;
            color: var(--dark-900);
            box-shadow: 0 4px 20px rgba(212, 168, 88, 0.3);
        }
        
        .login-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 600;
            color: #fff;
            letter-spacing: -0.5px;
        }
        
        .login-header h1 span {
            color: var(--gold);
        }
        
        .login-header p {
            color: var(--gray-500);
            font-size: 14px;
            margin-top: 4px;
            font-weight: 400;
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
            color: #E74C3C;
        }
        
        .form-group .input-wrap {
            position: relative;
        }
        
        .form-group .input-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-500);
            font-size: 16px;
            transition: var(--transition);
        }
        
        .form-group .input-wrap input {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 1.5px solid var(--dark-700);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: var(--transition);
            background: var(--dark-900);
            color: #fff;
        }
        
        .form-group .input-wrap input:focus {
            outline: none;
            border-color: var(--gold);
            background: var(--dark-700);
            box-shadow: 0 0 0 4px rgba(212, 168, 88, 0.15);
        }
        
        .form-group .input-wrap input:focus + i,
        .form-group .input-wrap input:focus ~ i {
            color: var(--gold);
        }
        
        .form-group .input-wrap input::placeholder {
            color: var(--gray-500);
        }
        
        .login-btn {
            width: 100%;
            padding: 14px 20px;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            color: var(--dark-900);
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
            box-shadow: 0 2px 12px rgba(212, 168, 88, 0.25);
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(212, 168, 88, 0.4);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-btn i {
            font-size: 16px;
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
            background: rgba(231, 76, 60, 0.15);
            color: #F1948A;
            border-left-color: #E74C3C;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--dark-700);
        }
        
        .login-footer p {
            font-size: 13px;
            color: var(--gray-500);
        }
        
        .login-footer strong {
            color: var(--gold);
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 32px 24px 28px;
                border-radius: var(--radius-md);
            }
            
            .login-header .logo-icon {
                width: 52px;
                height: 52px;
                font-size: 22px;
            }
            
            .login-header h1 {
                font-size: 22px;
            }
            
            .login-header p {
                font-size: 13px;
            }
            
            .form-group input {
                font-size: 13px;
                padding: 10px 14px 10px 40px;
            }
            
            .login-btn {
                font-size: 14px;
                padding: 12px;
            }
            
            .login-footer p {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-icon">
                <i class="fa-solid fa-crown"></i>
            </div>
            <h1>Master<span>Admin</span></h1>
            <p>Wedding Invitation System</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label for="username">Username <span class="required">*</span></label>
                <div class="input-wrap">
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                    <i class="fa-solid fa-user"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <div class="input-wrap">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <i class="fa-solid fa-lock"></i>
                </div>
            </div>
            
            <button type="submit" class="login-btn">
                <i class="fa-solid fa-right-to-bracket"></i>
                Sign In
            </button>
        </form>
        
        <div class="login-footer">
            <p>Default: <strong>master</strong> / <strong>master123</strong></p>
        </div>
    </div>
</body>
</html>