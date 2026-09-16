<?php
// admin/table-finder.php - PREMIUM THEME

session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getUserAdminById($user_id);
$invitation = getInvitationByUserId($user_id);

if (!$invitation) {
    header('Location: dashboard.php');
    exit;
}

// Get feature flags
$features = getUserFeatures($user_id);
$has_gallery = $features['has_gallery'] ?? 0;
$has_table_finder = $features['has_table_finder'] ?? 0;

$table_entries = getTableFinderEntries($invitation['id']);
$table_count = countTableFinderEntries($invitation['id']);
$error = '';
$success = '';

// Helper function to truncate text
function truncateText($text, $length = 10) {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}

// Handle Clear Table Finder
if (isset($_POST['clear_tables']) && $_POST['clear_tables'] == '1') {
    if (isset($_POST['confirm_clear']) && $_POST['confirm_clear'] == 'yes') {
        if (clearTableFinderEntries($invitation['id'])) {
            $success = 'All table entries cleared successfully!';
            $table_entries = getTableFinderEntries($invitation['id']);
            $table_count = countTableFinderEntries($invitation['id']);
        } else {
            $error = 'Failed to clear table entries.';
        }
    } else {
        $error = 'Please confirm that you want to clear all table entries.';
    }
}

// Add entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_entry'])) {
    $guest_name = trim($_POST['guest_name'] ?? '');
    $table_number = trim($_POST['table_number'] ?? '');
    $family_name = trim($_POST['family_name'] ?? '');
    
    if (empty($guest_name) || empty($table_number)) {
        $error = 'Guest name and table number are required.';
    } else {
        if (addTableFinderEntry($invitation['id'], $guest_name, $table_number, $family_name)) {
            $success = 'Guest added successfully!';
            $table_entries = getTableFinderEntries($invitation['id']);
            $table_count = countTableFinderEntries($invitation['id']);
        } else {
            $error = 'Failed to add guest.';
        }
    }
}

// Delete entry
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (deleteTableFinderEntry($id)) {
        $success = 'Entry deleted successfully!';
        $table_entries = getTableFinderEntries($invitation['id']);
        $table_count = countTableFinderEntries($invitation['id']);
    }
}

$initials = strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1));
$couple_name = $_SESSION['couple_name'] ?? 'No couple set';
$full_name = htmlspecialchars($_SESSION['full_name']);
$unique_tables = array_unique(array_column($table_entries, 'table_number'));
$avg_per_table = $table_count > 0 ? round($table_count / max(1, count($unique_tables)), 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Find Your Table - <?php echo $full_name; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ========================================
           PREMIUM TABLE FINDER
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
            --warning: #F39C12;
            --warning-bg: rgba(243, 156, 18, 0.15);
            --shadow-xs: 0 1px 3px rgba(26,20,16,0.04);
            --shadow-sm: 0 2px 12px rgba(108, 92, 231, 0.08);
            --shadow-md: 0 8px 32px rgba(26,20,16,0.08);
            --shadow-lg: 0 16px 48px rgba(108, 92, 231, 0.12);
            --radius-sm: 12px;
            --radius-md: 16px;
            --radius-lg: 24px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --nav-height: 72px;
            --max-width: 480px;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            min-height: 100vh;
            color: var(--dark);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .app-container {
            max-width: var(--max-width);
            margin: 0 auto;
            padding-bottom: calc(var(--nav-height) + 20px);
            background: var(--bg-body);
            min-height: 100vh;
            position: relative;
        }

        /* ========================================
           TOP BAR
           ======================================== */
        .top-bar {
            background: var(--bg-white);
            padding: 16px 20px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid rgba(0,0,0,0.02);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            background: rgba(255,255,255,0.92);
        }

        .top-bar .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .top-bar .brand .logo-icon {
            width: 38px;
            height: 38px;
            background: var(--primary-gradient);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #fff;
            box-shadow: 0 4px 16px rgba(108, 92, 231, 0.25);
        }

        .top-bar .brand .logo-text {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            letter-spacing: -0.5px;
        }

        .top-bar .brand .logo-text span {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .top-bar .right-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .top-bar .right-actions .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            box-shadow: 0 2px 12px rgba(108, 92, 231, 0.2);
            cursor: pointer;
            transition: var(--transition);
            flex-shrink: 0;
            text-decoration: none;
        }

        .top-bar .right-actions .avatar:active {
            transform: scale(0.92);
        }

        /* ========================================
           PAGE HEADER
           ======================================== */
        .page-header {
            padding: 20px 20px 0;
        }

        .page-header .header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .page-header .header-top .back-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--gray-500);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: var(--transition);
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            background: var(--gray-100);
        }

        .page-header .header-top .back-btn:hover {
            background: var(--gray-200);
            color: var(--dark);
        }

        .page-header .header-top .back-btn:active {
            transform: scale(0.94);
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--dark);
            margin-top: 4px;
        }

        .page-header p {
            color: var(--gray-500);
            font-size: 14px;
            margin-top: 2px;
        }

        /* ========================================
           ALERTS
           ======================================== */
        .alert {
            padding: 14px 18px;
            border-radius: var(--radius-sm);
            margin: 16px 20px 0;
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
            background: var(--primary-gradient);
            color: #fff;
            box-shadow: 0 4px 16px rgba(108, 92, 231, 0.25);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(108, 92, 231, 0.35);
        }

        .btn-primary:active {
            transform: scale(0.94);
        }

        .btn-secondary {
            background: var(--bg-white);
            color: var(--dark-700);
            border: 1px solid var(--gray-200);
        }

        .btn-secondary:hover {
            background: var(--gray-100);
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-secondary:active {
            transform: scale(0.94);
        }

        .btn-danger {
            background: var(--danger);
            color: #fff;
        }

        .btn-danger:hover {
            background: #C0392B;
            transform: translateY(-2px);
        }

        .btn-danger:active {
            transform: scale(0.94);
        }

        .btn-sm {
            padding: 6px 14px;
            font-size: 12px;
            border-radius: 6px;
        }

        /* ========================================
           STATS
           ======================================== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            padding: 16px 20px 0;
        }

        .stat-card {
            background: var(--bg-white);
            padding: 14px 12px;
            border-radius: var(--radius-sm);
            border: 1px solid rgba(0,0,0,0.04);
            text-align: center;
            transition: var(--transition);
            box-shadow: var(--shadow-xs);
        }

        .stat-card:hover {
            border-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .stat-card .stat-number {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark);
            line-height: 1.2;
        }

        .stat-card .stat-label {
            font-size: 11px;
            color: var(--gray-500);
            font-weight: 500;
            margin-top: 2px;
        }

        .stat-card .stat-icon {
            font-size: 16px;
            color: var(--primary);
            margin-bottom: 4px;
        }

        /* ========================================
           CARDS
           ======================================== */
        .card {
            background: var(--bg-white);
            border-radius: var(--radius-md);
            border: 1px solid rgba(0,0,0,0.04);
            overflow: hidden;
            margin: 16px 20px 0;
            box-shadow: var(--shadow-xs);
            transition: var(--transition);
        }

        .card:hover {
            border-color: var(--gray-200);
        }

        .card-header {
            padding: 16px 18px;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .card-header h3 {
            font-size: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--dark);
        }

        .card-header h3 i {
            color: var(--primary);
        }

        .card-header .card-badge {
            font-size: 11px;
            font-weight: 500;
            color: var(--gray-500);
            background: var(--gray-100);
            padding: 2px 12px;
            border-radius: 20px;
        }

        .card-body {
            padding: 18px;
        }

        .card-body:last-child {
            padding-bottom: 18px;
        }

        /* ========================================
           FORM
           ======================================== */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 10px;
            align-items: end;
        }

        .form-row .form-group {
            margin-bottom: 0;
        }

        .form-row .form-group label {
            font-size: 12px;
            font-weight: 600;
            color: var(--dark-700);
            margin-bottom: 4px;
            display: block;
        }

        .form-row .form-group label .required {
            color: var(--danger);
        }

        .form-row .form-group input {
            padding: 9px 12px;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            background: var(--gray-100);
            width: 100%;
            transition: var(--transition);
            outline: none;
            color: var(--dark);
        }

        .form-row .form-group input:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(108, 92, 231, 0.1);
        }

        .form-row .form-group input::placeholder {
            color: var(--gray-400);
        }

        /* ========================================
           SEARCH
           ======================================== */
        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .search-box input {
            flex: 1;
            min-width: 150px;
            padding: 9px 14px;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            background: var(--gray-100);
            transition: var(--transition);
            outline: none;
            color: var(--dark);
        }

        .search-box input:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(108, 92, 231, 0.1);
        }

        .search-box input::placeholder {
            color: var(--gray-400);
        }

        .search-box .btn {
            flex-shrink: 0;
        }

        .search-stats {
            font-size: 12px;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            margin-left: auto;
        }

        /* ========================================
           TABLE
           ======================================== */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-list {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .table-list th {
            text-align: left;
            padding: 10px 12px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gray-500);
            border-bottom: 2px solid var(--gray-200);
            font-weight: 600;
        }

        .table-list td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--gray-100);
            vertical-align: middle;
        }

        .table-list tr:hover td {
            background: var(--gray-100);
        }

        .table-list tr:last-child td {
            border-bottom: none;
        }

        .table-list .badge {
            background: var(--primary-bg);
            color: var(--primary);
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .table-list .badge.gold {
            background: rgba(249, 202, 36, 0.12);
            color: var(--gold);
        }

        .table-list .action-btn {
            width: 28px;
            height: 28px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--gray-200);
            background: #fff;
            color: var(--gray-500);
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            text-decoration: none;
        }

        .table-list .action-btn:hover {
            background: var(--danger-bg);
            border-color: var(--danger);
            color: var(--danger);
        }

        /* ========================================
           EMPTY STATE
           ======================================== */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray-500);
        }

        .empty-state .empty-icon {
            font-size: 40px;
            color: var(--gray-300);
            margin-bottom: 8px;
        }

        .empty-state h4 {
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            color: var(--dark-700);
        }

        .empty-state p {
            font-size: 13px;
        }

        .no-results {
            text-align: center;
            padding: 30px 20px;
            display: none;
            color: var(--gray-500);
        }

        .no-results .icon {
            font-size: 32px;
            color: var(--gray-300);
            margin-bottom: 6px;
        }

        .no-results p {
            font-size: 13px;
        }

        /* ========================================
           DANGER ZONE
           ======================================== */
        .danger-zone {
            margin: 16px 20px 0;
            background: #fff;
            border-radius: var(--radius-md);
            border: 1px solid #F5C6C6;
            overflow: hidden;
            box-shadow: var(--shadow-xs);
        }

        .danger-zone .danger-header {
            background: var(--danger-bg);
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid #F5C6C6;
        }

        .danger-zone .danger-header i {
            color: var(--danger);
            font-size: 18px;
        }

        .danger-zone .danger-header h3 {
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 600;
            color: var(--danger);
            margin: 0;
        }

        .danger-zone .danger-header .badge-danger {
            margin-left: auto;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #fff;
            background: var(--danger);
            padding: 2px 12px;
            border-radius: 20px;
        }

        .danger-zone .danger-body {
            padding: 16px 18px 18px;
        }

        .danger-zone .danger-item {
            background: var(--gray-100);
            border-radius: var(--radius-sm);
            padding: 14px 16px;
            border: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .danger-zone .danger-item:hover {
            border-color: #F5C6C6;
        }

        .danger-zone .danger-item .item-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 4px;
        }

        .danger-zone .danger-item .item-top .item-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: var(--dark-800);
        }

        .danger-zone .danger-item .item-top .item-label i {
            color: var(--danger);
        }

        .danger-zone .danger-item .item-top .item-count {
            font-size: 12px;
            font-weight: 500;
            color: var(--gray-500);
            background: var(--gray-200);
            padding: 1px 12px;
            border-radius: 20px;
        }

        .danger-zone .danger-item .item-desc {
            font-size: 13px;
            color: var(--gray-600);
            margin-bottom: 10px;
        }

        .danger-zone .danger-item .warning-box {
            background: var(--danger-bg);
            padding: 8px 14px;
            border-radius: var(--radius-sm);
            border-left: 3px solid var(--danger);
            font-size: 12px;
            color: var(--danger);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .danger-zone .danger-item .warning-box i {
            font-size: 14px;
        }

        .danger-zone .danger-item .confirm-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .danger-zone .danger-item .confirm-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: var(--danger);
        }

        .danger-zone .danger-item .confirm-row label {
            font-size: 13px;
            cursor: pointer;
            color: var(--dark-700);
        }

        /* ========================================
           RESPONSIVE
           ======================================== */
        @media (min-width: 641px) {
            .app-container {
                max-width: 768px;
                padding: 0 20px 100px;
                background: transparent;
            }

            .top-bar {
                border-radius: 0 0 20px 20px;
                border-bottom: 1px solid rgba(0,0,0,0.04);
                margin: 0 -20px;
                padding: 16px 40px 12px;
            }

            .page-header {
                padding: 24px 0 0;
            }

            .page-header h1 {
                font-size: 30px;
            }

            .stats-grid {
                padding: 16px 0 0;
                gap: 14px;
            }

            .stat-card {
                padding: 18px 16px;
            }

            .stat-card .stat-number {
                font-size: 26px;
            }

            .card {
                margin: 16px 0 0;
            }

            .alert {
                margin: 16px 0 0;
            }

            .danger-zone {
                margin: 16px 0 0;
            }
        }

        @media (min-width: 1025px) {
            .app-container {
                max-width: 1024px;
                padding: 0 40px 100px;
            }

            .top-bar {
                margin: 0 -40px;
                padding: 16px 60px 12px;
            }

            .page-header h1 {
                font-size: 34px;
            }

            .stats-grid {
                gap: 18px;
            }

            .form-row {
                gap: 14px;
            }
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr 1fr;
            }
            .form-row .btn {
                grid-column: span 2;
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr 1fr;
                gap: 8px;
                padding: 12px 16px 0;
            }

            .stat-card {
                padding: 10px 8px;
            }

            .stat-card .stat-number {
                font-size: 18px;
            }

            .stat-card .stat-label {
                font-size: 10px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            .form-row .btn {
                grid-column: span 1;
                width: 100%;
                justify-content: center;
            }

            .search-box {
                flex-direction: column;
            }
            .search-box input {
                min-width: unset;
                width: 100%;
            }
            .search-box .btn {
                width: 100%;
                justify-content: center;
            }
            .search-stats {
                margin-left: 0;
                justify-content: center;
            }

            .table-list th,
            .table-list td {
                padding: 6px 8px;
                font-size: 12px;
            }

            .card-header {
                padding: 12px 14px;
                flex-direction: column;
                align-items: stretch;
            }

            .card-body {
                padding: 14px;
            }

            .danger-zone .danger-header {
                padding: 12px 14px;
                flex-wrap: wrap;
            }
            .danger-zone .danger-header .badge-danger {
                margin-left: 0;
            }
            .danger-zone .danger-body {
                padding: 14px;
            }
            .danger-zone .danger-item {
                padding: 12px 14px;
            }
            .danger-zone .danger-item .item-top {
                flex-direction: column;
                align-items: flex-start;
            }
            .danger-zone .danger-item .confirm-row {
                flex-direction: column;
                align-items: flex-start;
            }
            .danger-zone .danger-item .btn-danger {
                width: 100%;
                justify-content: center;
            }

            .page-header .header-top .back-btn span {
                display: none;
            }
        }

        @media (max-width: 360px) {
            .top-bar .brand .logo-text {
                font-size: 15px;
            }

            .page-header h1 {
                font-size: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            .stats-grid .stat-card:last-child {
                grid-column: span 2;
            }
        }

        /* ========================================
           UTILITY
           ======================================== */
        .text-muted { color: var(--gray-500); }
        .mt-4 { margin-top: 4px; }
        .mb-4 { margin-bottom: 4px; }
        .mt-8 { margin-top: 8px; }
        .mb-8 { margin-bottom: 8px; }
    </style>
</head>
<body>

<div class="app-container">
    <!-- ========================================
    TOP BAR
    ======================================== -->
    <header class="top-bar">
        <div class="brand">
            <div class="logo-icon"><i class="fa-solid fa-heart"></i></div>
            <div class="logo-text">Wedding<span>Admin</span></div>
        </div>
        <div class="right-actions">
            <a href="dashboard.php" class="avatar" title="Logout"><?php echo $initials; ?></a>
        </div>
    </header>

    <!-- ========================================
    PAGE HEADER
    ======================================== -->
    <div class="page-header">
        <div class="header-top">
            <a href="dashboard.php" class="back-btn">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back</span>
            </a>
        </div>
        <h1>Find Your Table</h1>
        <p>Manage guest table assignments for your wedding</p>
    </div>

    <!-- ========================================
    ALERTS
    ======================================== -->
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

    <!-- ========================================
    STATS
    ======================================== -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
            <div class="stat-number"><?php echo $table_count; ?></div>
            <div class="stat-label">Total Guests</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-solid fa-table"></i></div>
            <div class="stat-number"><?php echo count($unique_tables); ?></div>
            <div class="stat-label">Tables</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-solid fa-calculator"></i></div>
            <div class="stat-number"><?php echo $avg_per_table; ?></div>
            <div class="stat-label">Avg per Table</div>
        </div>
    </div>

    <!-- ========================================
    ADD GUEST FORM
    ======================================== -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-user-plus"></i> Add Guest</h3>
        </div>
        <div class="card-body">
            <form method="POST" id="addGuestForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>Guest Name <span class="required">*</span></label>
                        <input type="text" name="guest_name" placeholder="e.g., John Doe" required>
                    </div>
                    <div class="form-group">
                        <label>Table Number <span class="required">*</span></label>
                        <input type="text" name="table_number" placeholder="e.g., Table 1" required>
                    </div>
                    <div class="form-group">
                        <label>Family Name</label>
                        <input type="text" name="family_name" placeholder="e.g., Smith Family">
                    </div>
                    <button type="submit" name="add_entry" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i> Add
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================
    GUEST LIST
    ======================================== -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-list"></i> Guest List</h3>
            <span class="card-badge"><?php echo $table_count; ?> guests</span>
        </div>
        <div class="card-body">
            <?php if (empty($table_entries)): ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="fa-solid fa-user"></i></div>
                    <h4>No Guests Yet</h4>
                    <p>Add your first guest to get started</p>
                </div>
            <?php else: ?>
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search guests by name, table or family..." onkeyup="filterTable()">
                    <button class="btn btn-secondary btn-sm" onclick="filterTable()">
                        <i class="fa-solid fa-search"></i> Search
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="clearSearch()">
                        <i class="fa-solid fa-xmark"></i> Clear
                    </button>
                    <span class="search-stats" id="searchStats"></span>
                </div>

                <div class="table-responsive">
                    <table class="table-list" id="tableFinderTable">
                        <thead>
                            <tr>
                                <th>Guest Name</th>
                                <th>Table</th>
                                <th>Family</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($table_entries as $entry): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($entry['guest_name']); ?></strong></td>
                                    <td><span class="badge <?php echo rand(0,1) ? 'gold' : ''; ?>"><?php echo htmlspecialchars($entry['table_number']); ?></span></td>
                                    <td><?php echo htmlspecialchars($entry['family_name'] ?? '-'); ?></td>
                                    <td style="text-align:right;">
                                        <a href="?delete=<?php echo $entry['id']; ?>" class="action-btn" onclick="return confirm('Delete this entry?')" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- No Results -->
                <div class="no-results" id="noResultsMsg">
                    <div class="icon"><i class="fa-solid fa-search"></i></div>
                    <p>No guests found matching your search.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========================================
    DANGER ZONE
    ======================================== -->
    <?php if ($table_count > 0): ?>
    <div class="danger-zone">
        
        <div class="danger-body">
            <div class="danger-item">
                <div class="item-top">
                    <span class="item-label">
                        <i class="fa-solid fa-users"></i>
                        Table Entries
                    </span>
                    <span class="item-count"><?php echo $table_count; ?> entries</span>
                </div>
                <p class="item-desc">Permanently delete all <?php echo $table_count; ?> table entries from your invitation.</p>
                
                <form method="POST" onsubmit="return confirmClearTables();">
                    <input type="hidden" name="clear_tables" value="1">
                    <div class="confirm-row">
                        <input type="checkbox" id="confirmClear" name="confirm_clear" value="yes" required>
                        <label for="confirmClear">I understand, delete all <?php echo $table_count; ?> entries</label>
                    </div>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-trash-can"></i> Clear All Entries
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ========================================
    BOTTOM NAVIGATION
    ======================================== -->
    <?php include 'includes/bottom-nav.php'; ?>
</div>

<script>
    // ========================================
    // TABLE SEARCH
    // ========================================
    function filterTable() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toUpperCase().trim();
        const table = document.getElementById('tableFinderTable');
        if (!table) return;
        
        const rows = table.getElementsByTagName('tr');
        const noResults = document.getElementById('noResultsMsg');
        let visibleCount = 0;
        
        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;
            for (let j = 0; j < cells.length - 1; j++) {
                const text = cells[j].textContent || cells[j].innerText;
                if (text.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
            rows[i].style.display = found ? '' : 'none';
            if (found) visibleCount++;
        }
        
        // Show/hide no results
        if (noResults) {
            noResults.style.display = (visibleCount === 0 && filter !== '') ? 'block' : 'none';
        }
        
        // Update stats
        const stats = document.getElementById('searchStats');
        if (stats) {
            if (filter === '') {
                stats.textContent = 'Showing all ' + <?php echo $table_count; ?> + ' guests';
            } else {
                stats.textContent = 'Found ' + visibleCount + ' matching guests';
            }
        }
    }

    function clearSearch() {
        const input = document.getElementById('searchInput');
        input.value = '';
        filterTable();
        input.focus();
    }

    // Real-time search
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', filterTable);
        }
        // Initial stats
        const stats = document.getElementById('searchStats');
        if (stats) {
            stats.textContent = 'Showing all ' + <?php echo $table_count; ?> + ' guests';
        }
    });

    // ========================================
    // CONFIRM CLEAR TABLES
    // ========================================
    function confirmClearTables() {
        const checkbox = document.getElementById('confirmClear');
        if (!checkbox.checked) {
            alert('Please check the confirmation box to proceed.');
            return false;
        }
        return confirm('⚠️ WARNING: This will permanently delete ALL table entries!\n\nAre you sure you want to continue?');
    }

    // ========================================
    // HAPTIC FEEDBACK
    // ========================================
    document.querySelectorAll('.btn, .stat-card, .table-list tr, .danger-item').forEach(el => {
        el.addEventListener('touchstart', function() {
            if (navigator.vibrate) navigator.vibrate(6);
        }, { passive: true });
    });
</script>
</body>
</html>