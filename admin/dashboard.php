<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and account is active
requireUserAdmin();

$user_id = $_SESSION['user_id'];

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getUserAdminById($user_id);
$invitation = getInvitationByUserId($user_id);
$schedule_items = $invitation ? getScheduleItems($invitation['id']) : [];
$gallery_images = $invitation ? getGalleryImages($invitation['id']) : [];
$table_entries = $invitation ? getTableFinderEntries($invitation['id']) : [];

// Get feature flags
$features = getUserFeatures($user_id);
$has_gallery = $features['has_gallery'] ?? 0;
$has_table_finder = $features['has_table_finder'] ?? 0;

$invitation_link = $invitation ?  'http://localhost/pre/wedding-invitation-12-final-done/intro/index.php?slug=' . $invitation['slug'] : '#';

$initials = strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1));
$couple_name = $_SESSION['couple_name'] ?? 'No couple set';
$full_name = htmlspecialchars($_SESSION['full_name']);

// Helper function to truncate text
function truncateText($text, $length = 10) {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Dashboard - <?php echo $full_name; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin-theme.css">
    <style>
        /* ========================================
           PREMIUM DASHBOARD - FULLY RESPONSIVE
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
            --shadow-xs: 0 1px 3px rgba(26,20,16,0.04);
            --shadow-sm: 0 2px 12px rgba(108, 92, 231, 0.08);
            --shadow-md: 0 8px 32px rgba(26,20,16,0.08);
            --shadow-lg: 0 16px 48px rgba(108, 92, 231, 0.12);
            --radius-sm: 12px;
            --radius-md: 16px;
            --radius-lg: 24px;
            --radius-xl: 28px;
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

        /* ========================================
           LAYOUT CONTAINER
           ======================================== */
        .app-container {
            max-width: var(--max-width);
            margin: 0 auto;
            padding-bottom: calc(var(--nav-height) + 20px);
            background: var(--bg-body);
            min-height: 100vh;
            position: relative;
        }

        /* ========================================
           TOP BAR - PREMIUM
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

        .top-bar .right-actions .notification-bell {
            position: relative;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark-700);
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
            border: none;
        }

        .top-bar .right-actions .notification-bell:active {
            transform: scale(0.92);
            background: var(--gray-200);
        }

        .top-bar .right-actions .notification-bell .dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background: var(--rose);
            border-radius: 50%;
            border: 2px solid #fff;
        }

        /* ========================================
           GREETING - CLEAN & ELEGANT
           ======================================== */
        .greeting-section {
            padding: 20px 20px 0;
        }

        .greeting-section .greeting-top {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 2px;
        }

        .greeting-section .greeting-top .greeting-emoji {
            font-size: 18px;
        }

        .greeting-section .greeting-top .greeting-text {
            font-size: 14px;
            color: var(--gray-500);
            font-weight: 500;
        }

        .greeting-section .greeting-main {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .greeting-section .greeting-main h1 {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--dark);
            line-height: 1.1;
            margin: 0;
        }

        .greeting-section .greeting-main .greeting-sub {
            font-size: 13px;
            color: var(--gray-500);
            margin-top: 2px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .greeting-section .greeting-main .greeting-sub .couple-name {
            color: var(--dark-700);
            font-weight: 500;
        }

        .greeting-section .greeting-main .greeting-sub .greeting-dot {
            color: var(--gray-300);
        }

        .greeting-section .greeting-main .greeting-sub .greeting-status {
            font-size: 12px;
            font-weight: 500;
        }

        .greeting-section .greeting-main .greeting-sub .greeting-status.live {
            color: var(--success);
        }

        .greeting-section .greeting-main .greeting-sub .greeting-status.draft {
            color: var(--gray-400);
        }

        .greeting-section .greeting-main .greeting-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            box-shadow: 0 4px 16px rgba(108, 92, 231, 0.2);
            flex-shrink: 0;
        }

        /* ========================================
           STATS - MINIMAL & PREMIUM
           ======================================== */
        .stats-row {
            display: flex;
            gap: 10px;
            padding: 16px 20px 0;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }

        .stats-row::-webkit-scrollbar {
            display: none;
        }

        .stat-mini {
            flex: 0 0 auto;
            background: var(--bg-white);
            padding: 12px 18px;
            border-radius: var(--radius-sm);
            border: 1px solid rgba(0,0,0,0.04);
            min-width: 100px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: var(--shadow-xs);
            transition: var(--transition);
        }

        .stat-mini:active {
            transform: scale(0.96);
        }

        .stat-mini .stat-icon-wrap {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .stat-mini .stat-icon-wrap.purple {
            background: var(--primary-bg);
            color: var(--primary);
        }

        .stat-mini .stat-icon-wrap.gold {
            background: rgba(249, 202, 36, 0.12);
            color: var(--gold);
        }

        .stat-mini .stat-icon-wrap.rose {
            background: var(--rose-light);
            color: var(--rose);
        }

        .stat-mini .stat-icon-wrap.green {
            background: var(--success-bg);
            color: var(--success);
        }

        .stat-mini .stat-info .stat-number {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            line-height: 1.2;
        }

        .stat-mini .stat-info .stat-label {
            font-size: 11px;
            color: var(--gray-500);
            font-weight: 500;
        }

        /* ========================================
           INVITATION LINK - PROFESSIONAL
           ======================================== */
        .invitation-card {
            margin: 16px 20px;
            background: var(--bg-white);
            border-radius: var(--radius-md);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(0,0,0,0.03);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .invitation-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-gradient);
        }

        .invitation-card .card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .invitation-card .card-header .header-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary-bg);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .invitation-card .card-header .header-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
        }

        .invitation-card .card-header .header-badge {
            margin-left: auto;
            font-size: 10px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            background: var(--success-bg);
            color: var(--success);
        }

        .invitation-card .link-container {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--gray-100);
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            border: 1px solid rgba(0,0,0,0.04);
        }

        .invitation-card .link-container .link-icon {
            color: var(--gray-500);
            font-size: 14px;
            flex-shrink: 0;
        }

        .invitation-card .link-container .link-text {
            flex: 1;
            font-size: 13px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            min-width: 0;
        }

        .invitation-card .link-container .link-text:hover {
            text-decoration: underline;
        }

        .invitation-card .link-container .copy-btn {
            background: var(--bg-white);
            border: 1px solid var(--gray-200);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: var(--dark-700);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
            font-family: 'Inter', sans-serif;
            flex-shrink: 0;
        }

        .invitation-card .link-container .copy-btn:active {
            transform: scale(0.92);
        }

        .invitation-card .link-container .copy-btn.copied {
            background: var(--success-bg);
            border-color: var(--success);
            color: var(--success);
        }

        .invitation-card .link-actions {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .invitation-card .link-actions .btn-action {
            flex: 1;
            padding: 8px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--gray-200);
            background: var(--bg-white);
            font-size: 12px;
            font-weight: 500;
            color: var(--dark-700);
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            text-align: center;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .invitation-card .link-actions .btn-action:active {
            transform: scale(0.94);
        }

        .invitation-card .link-actions .btn-action.primary {
            background: var(--primary-gradient);
            border-color: var(--primary);
            color: #fff;
            box-shadow: 0 2px 12px rgba(108, 92, 231, 0.2);
        }

        /* ========================================
           QUICK ACTIONS
           ======================================== */
        .action-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 16px 20px 0;
        }

        .action-btn {
            background: var(--bg-white);
            border: 1px solid rgba(0,0,0,0.04);
            border-radius: var(--radius-sm);
            padding: 16px 12px;
            text-align: center;
            text-decoration: none;
            color: var(--dark);
            transition: var(--transition);
            box-shadow: var(--shadow-xs);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .action-btn:active {
            transform: scale(0.94);
            background: var(--gray-100);
        }

        .action-btn .action-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--primary-bg);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: var(--transition);
        }

        .action-btn .action-icon.gold-bg {
            background: rgba(249, 202, 36, 0.1);
            color: var(--gold);
        }

        .action-btn .action-icon.rose-bg {
            background: var(--rose-light);
            color: var(--rose);
        }

        .action-btn .action-icon.green-bg {
            background: var(--success-bg);
            color: var(--success);
        }

        .action-btn .action-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--dark-700);
        }

        .action-btn .action-sub {
            font-size: 10px;
            color: var(--gray-500);
            font-weight: 400;
        }

        /* ========================================
           FEATURE STATUS - MINIMAL
           ======================================== */
        .feature-row {
            display: flex;
            gap: 8px;
            padding: 0 20px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .feature-pill {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            background: var(--bg-white);
            border: 1px solid rgba(0,0,0,0.04);
            font-size: 11px;
            font-weight: 500;
            color: var(--dark-700);
            box-shadow: var(--shadow-xs);
        }

        .feature-pill .pill-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
        }

        .feature-pill .pill-dot.active {
            background: var(--success);
        }

        .feature-pill .pill-dot.inactive {
            background: var(--gray-300);
        }

        /* ========================================
           GALLERY & GUEST LIST - WITH VIEW MORE
           ======================================== */
        .content-section {
            padding: 16px 20px 0;
        }

        .content-section .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .content-section .section-header .section-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .content-section .section-header .section-title .title-icon {
            color: var(--primary);
        }

        .content-section .section-header .section-count {
            font-size: 11px;
            font-weight: 400;
            color: var(--gray-500);
        }

        .content-section .view-more {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px;
            background: var(--bg-white);
            border-radius: var(--radius-sm);
            border: 1px solid var(--gray-200);
            color: var(--primary);
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            margin-top: 8px;
        }

        .content-section .view-more:active {
            transform: scale(0.96);
        }

        .content-section .view-more:hover {
            background: var(--primary-bg);
            border-color: var(--primary);
        }

        .content-grid {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .content-item {
            background: var(--bg-white);
            border-radius: var(--radius-sm);
            padding: 12px 14px;
            border: 1px solid rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-xs);
            transition: var(--transition);
        }

        .content-item:active {
            transform: scale(0.98);
        }

        .content-item .item-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            flex: 1;
        }

        .content-item .item-left .thumb {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-sm);
            background: var(--gray-100);
            flex-shrink: 0;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.04);
        }

        .content-item .item-left .thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .content-item .item-left .item-info .item-name {
            font-size: 13px;
            font-weight: 500;
            color: var(--dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .content-item .item-left .item-info .item-meta {
            font-size: 11px;
            color: var(--gray-500);
        }

        .content-item .item-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 12px;
            border-radius: 20px;
            background: var(--primary-bg);
            color: var(--primary);
            flex-shrink: 0;
        }

        .content-item .item-badge.gold {
            background: rgba(249, 202, 36, 0.1);
            color: var(--gold);
        }

        .empty-preview {
            text-align: center;
            padding: 24px 16px;
            color: var(--gray-500);
        }

        .empty-preview .empty-icon {
            font-size: 28px;
            color: var(--gray-300);
            margin-bottom: 6px;
        }

        .empty-preview p {
            font-size: 13px;
        }

        /* ========================================
           GALLERY GRID VIEW
           ======================================== */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .gallery-grid .gallery-thumb {
            aspect-ratio: 1;
            border-radius: var(--radius-sm);
            overflow: hidden;
            background: var(--gray-100);
            border: 1px solid rgba(0,0,0,0.04);
            transition: var(--transition);
        }

        .gallery-grid .gallery-thumb:active {
            transform: scale(0.94);
        }

        .gallery-grid .gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ========================================
           GUEST LIST GRID
           ======================================== */
        .guest-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .guest-card {
            background: var(--bg-white);
            border-radius: var(--radius-sm);
            padding: 12px;
            border: 1px solid rgba(0,0,0,0.03);
            box-shadow: var(--shadow-xs);
            transition: var(--transition);
            text-align: center;
        }

        .guest-card:active {
            transform: scale(0.96);
        }

        .guest-card .guest-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-bg);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            margin: 0 auto 6px;
        }

        .guest-card .guest-name {
            font-size: 13px;
            font-weight: 500;
            color: var(--dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .guest-card .guest-table {
            font-size: 10px;
            color: var(--gray-500);
            margin-top: 2px;
        }

        .guest-card .guest-table .table-badge {
            display: inline-block;
            padding: 1px 10px;
            border-radius: 12px;
            background: rgb(245 0 0 / 50%);;
            color: #fafafa;
            font-weight: 600;
            font-size: 10px;
        }

        /* ========================================
           NO INVITATION STATE
           ======================================== */
        .no-invitation {
            margin: 20px;
            background: var(--bg-white);
            border-radius: var(--radius-md);
            padding: 32px 20px;
            text-align: center;
            border: 1px solid rgba(0,0,0,0.03);
            box-shadow: var(--shadow-sm);
        }

        .no-invitation .no-inv-icon {
            font-size: 48px;
            color: var(--gray-300);
            margin-bottom: 12px;
        }

        .no-invitation h3 {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            color: var(--dark);
        }

        .no-invitation p {
            color: var(--gray-500);
            font-size: 14px;
            margin-top: 4px;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            background: var(--primary-gradient);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            box-shadow: 0 4px 16px rgba(108, 92, 231, 0.25);
            margin-top: 12px;
        }

        .btn-primary:active {
            transform: scale(0.94);
        }

        /* ========================================
           RESPONSIVE - TABLET & DESKTOP
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

            .greeting-section {
                padding: 24px 0 0;
            }

            .greeting-section .greeting-main h1 {
                font-size: 30px;
            }

            .stats-row {
                padding: 16px 0 0;
                gap: 12px;
            }

            .stat-mini {
                min-width: 120px;
                padding: 14px 20px;
            }

            .invitation-card {
                margin: 16px 0;
            }

            .action-grid {
                margin: 16px 0 0;
                gap: 12px;
            }

            .feature-row {
                padding: 0;
            }

            .content-section {
                padding: 16px 0 0;
            }

            .no-invitation {
                margin: 20px 0;
            }

            .content-item {
                padding: 14px 16px;
            }

            .action-btn {
                padding: 20px 16px;
            }

            .action-btn .action-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .action-btn .action-label {
                font-size: 13px;
            }

            .invitation-card .link-container {
                padding: 12px 16px;
            }

            .invitation-card .link-container .link-text {
                font-size: 14px;
            }

            .invitation-card .link-actions .btn-action {
                padding: 10px;
                font-size: 13px;
            }

            .stats-row {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                overflow-x: visible;
            }

            .gallery-grid {
                grid-template-columns: repeat(4, 1fr);
            }

            .guest-grid {
                grid-template-columns: repeat(4, 1fr);
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

            .greeting-section .greeting-main h1 {
                font-size: 34px;
            }

            .stat-mini .stat-info .stat-number {
                font-size: 22px;
            }

            .invitation-card .link-container {
                padding: 14px 20px;
            }

            .action-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 16px;
            }

            .stats-row {
                grid-template-columns: repeat(4, 1fr);
                gap: 16px;
            }

            .content-item {
                padding: 16px 18px;
            }

            .content-item .item-left .item-info .item-name {
                font-size: 14px;
            }

            .gallery-grid {
                grid-template-columns: repeat(5, 1fr);
            }

            .guest-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        @media (min-width: 1280px) {
            .app-container {
                max-width: 1200px;
            }

            .top-bar {
                margin: 0 -60px;
                padding: 16px 80px 12px;
            }

            .greeting-section {
                padding: 30px 0 0;
            }

            .stats-row {
                gap: 20px;
            }

            .stat-mini {
                padding: 16px 24px;
            }

            .gallery-grid {
                grid-template-columns: repeat(6, 1fr);
            }

            .guest-grid {
                grid-template-columns: repeat(6, 1fr);
            }
        }

        /* ========================================
           UTILITY
           ======================================== */
        .text-muted { color: var(--gray-500); }
        .text-primary { color: var(--primary); }
        .mt-8 { margin-top: 8px; }
        .mb-8 { margin-bottom: 8px; }
        .flex-center { display: flex; align-items: center; gap: 8px; }
        .gap-4 { gap: 4px; }
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
            <button class="notification-bell" aria-label="Notifications">
                <i class="fa-regular fa-bell"></i>
                <span class="dot"></span>
            </button>
            <a href="dashboard.php" class="avatar" title="Logout"><?php echo $initials; ?></a>
        </div>
    </header>

    <!-- ========================================
    GREETING - CLEAN & ELEGANT
    ======================================== -->
    <!-- Added display: flex and flex-direction: column to make align-items work -->
<section class="greeting-section" style="display: flex !important; flex-direction: column !important; align-items: center !important; text-align: center !important; width: 100% !important;">
    <div class="greeting-top" style="display: flex; align-items: center; justify-content: center; gap: 6px; margin-bottom: 4px;">
        <span class="greeting-emoji">👋</span>
        <span class="greeting-text">Good morning</span>
    </div>
    
    <div class="greeting-main">
        <div>
            <h1 style="margin: 0 0 4px 0;"><?php echo $full_name; ?></h1>
            <p class="greeting-sub" style="margin: 0; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <span class="couple-name"><?php echo htmlspecialchars($couple_name); ?></span>
                <span class="greeting-dot">•</span>
                <span class="greeting-status <?php echo $invitation ? 'live' : 'draft'; ?>">
                    <?php echo $invitation ? '● Live' : '○ Draft'; ?>
                </span>
            </p>
        </div>
    </div>
</section>


    <!-- ========================================
    STATS ROW
    ======================================== -->
    <div class="stats-row">
        <div class="stat-mini">
            <div class="stat-icon-wrap <?php echo $invitation ? 'green' : 'rose'; ?>">
                <i class="fa-solid <?php echo $invitation ? 'fa-check-circle' : 'fa-circle-exclamation'; ?>"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number"><?php echo $invitation ? 'Active' : 'Draft'; ?></div>
                <div class="stat-label">Invitation</div>
            </div>
        </div>
        <div class="stat-mini">
            <div class="stat-icon-wrap gold">
                <i class="fa-regular fa-calendar"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number"><?php echo count($schedule_items); ?></div>
                <div class="stat-label">Schedule</div>
            </div>
        </div>
        <?php if ($has_gallery): ?>
        <div class="stat-mini">
            <div class="stat-icon-wrap purple">
                <i class="fa-regular fa-image"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number"><?php echo count($gallery_images); ?></div>
                <div class="stat-label">Photos</div>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($has_table_finder): ?>
        <div class="stat-mini">
            <div class="stat-icon-wrap rose">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number"><?php echo count($table_entries); ?></div>
                <div class="stat-label">Guests</div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ========================================
    FEATURE PILLS
    ======================================== -->
    <div class="feature-row">
        <span class="feature-pill">
            <span class="pill-dot <?php echo $has_gallery ? 'active' : 'inactive'; ?>"></span>
            Gallery <?php echo $has_gallery ? '✓' : '✕'; ?>
        </span>
        <span class="feature-pill">
            <span class="pill-dot <?php echo $has_table_finder ? 'active' : 'inactive'; ?>"></span>
            Table Finder <?php echo $has_table_finder ? '✓' : '✕'; ?>
        </span>
    </div>

    <?php if (!$invitation): ?>
        <!-- ========================================
        NO INVITATION
        ======================================== -->
        <div class="no-invitation">
            <div class="no-inv-icon"><i class="fa-regular fa-envelope"></i></div>
            <h3>Create Your Invitation</h3>
            <p>Start designing your wedding invitation</p>
            <a href="edit-invitation.php" class="btn-primary">
                <i class="fa-solid fa-plus"></i> Get Started
            </a>
        </div>
    <?php else: ?>
        <!-- ========================================
        INVITATION LINK - PROFESSIONAL
        ======================================== -->
        <div class="invitation-card">
            <div class="card-header">
                <div class="header-icon"><i class="fa-solid fa-link"></i></div>
                <span class="header-title">Share Your Invitation</span>
                <span class="header-badge"><i class="fa-solid fa-check-circle"></i> Live</span>
            </div>
            <div class="link-container">
                <span class="link-icon"><i class="fa-solid fa-globe"></i></span>
                <a href="<?php echo $invitation_link; ?>" target="_blank" class="link-text" id="invitationLink">
                    <?php echo $invitation_link; ?>
                </a>
                <button class="copy-btn" onclick="copyLink()" id="copyBtn">
                    <i class="fa-regular fa-copy"></i> Copy
                </button>
            </div>
            <div class="link-actions">
                <a href="<?php echo $invitation_link; ?>" target="_blank" class="btn-action primary">
                    <i class="fa-regular fa-eye"></i> Preview
                </a>
                <a href="edit-invitation.php" class="btn-action">
                    <i class="fa-regular fa-pen-to-square"></i> Edit
                </a>
            </div>
        </div>

        <!-- ========================================
        QUICK ACTIONS
        ======================================== -->
        <div class="action-grid">
            <a href="edit-invitation.php" class="action-btn">
                <div class="action-icon"><i class="fa-regular fa-pen-to-square"></i></div>
                <span class="action-label">Edit</span>
                <span class="action-sub">Invitation</span>
            </a>
            <?php if ($has_gallery): ?>
            <a href="gallery.php" class="action-btn">
                <div class="action-icon gold-bg"><i class="fa-regular fa-images"></i></div>
                <span class="action-label">Gallery</span>
                <span class="action-sub">Manage photos</span>
            </a>
            <?php endif; ?>
            <?php if ($has_table_finder): ?>
            <a href="table-finder.php" class="action-btn">
                <div class="action-icon rose-bg"><i class="fa-solid fa-users"></i></div>
                <span class="action-label">Guest</span>
                <span class="action-sub">Guest seating</span>
            </a>
            <?php endif; ?>
        </div>

        <!-- ========================================
        GALLERY PREVIEW - WITH VIEW MORE
        ======================================== -->
        <?php if ($has_gallery): ?>
        <div class="content-section">
            <div class="section-header">
                <div class="section-title">
                    <span class="title-icon"><i class="fa-regular fa-image"></i></span>
                    Gallery
                </div>
                <span class="section-count"><?php echo count($gallery_images); ?> photos</span>
            </div>
            <?php if (empty($gallery_images)): ?>
                <div class="empty-preview">
                    <div class="empty-icon"><i class="fa-regular fa-image"></i></div>
                    <p>No photos uploaded yet</p>
                </div>
            <?php else: ?>
                <div class="gallery-grid">
                    <?php 
                    $display_images = array_slice($gallery_images, 0, 6);
                    foreach ($display_images as $img): 
                    ?>
                    <div class="gallery-thumb">
                        <img src="<?php echo getMediaUrl($_SESSION['username'] ?? 'default', $img['image_path'], 'images'); ?>" 
                             alt="<?php echo htmlspecialchars($img['caption'] ?? 'Gallery'); ?>" loading="lazy">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($gallery_images) > 6): ?>
                    <a href="gallery.php" class="view-more">
                        <i class="fa-regular fa-images"></i>
                        View All <?php echo count($gallery_images); ?> Photos
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ========================================
        GUEST LIST PREVIEW - GRID VIEW WITH VIEW MORE
        ======================================== -->
        <?php if ($has_table_finder): ?>
        <div class="content-section" style="padding-bottom:20px;">
            <div class="section-header">
                <div class="section-title">
                    <span class="title-icon"><i class="fa-solid fa-users"></i></span>
                    Guest List
                </div>
                <span class="section-count"><?php echo count($table_entries); ?> guests</span>
            </div>
            <?php if (empty($table_entries)): ?>
                <div class="empty-preview">
                    <div class="empty-icon"><i class="fa-regular fa-user"></i></div>
                    <p>No guests added yet</p>
                </div>
            <?php else: ?>
                <div class="guest-grid">
                    <?php 
                    $display_entries = array_slice($table_entries, 0, 6);
                    foreach ($display_entries as $entry): 
                        $guest_initials = strtoupper(substr($entry['guest_name'] ?? 'G', 0, 2));
                    ?>
                    <div class="guest-card">
                        <div class="guest-avatar"><?php echo $guest_initials; ?></div>
                        <div class="guest-name"><?php echo htmlspecialchars($entry['guest_name']); ?></div>
                        <div class="guest-table">
                            <span class="table-badge">Table <?php echo htmlspecialchars($entry['table_number']); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($table_entries) > 6): ?>
                    <a href="table-finder.php" class="view-more">
                        <i class="fa-regular fa-users"></i>
                        View All <?php echo count($table_entries); ?> Guests
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ========================================
    BOTTOM NAVIGATION
    ======================================== -->
    <?php include 'includes/bottom-nav.php'; ?>
</div>

<script>
    // ========================================
    // COPY LINK
    // ========================================
    function copyLink() {
        const link = document.getElementById('invitationLink');
        if (!link) return;
        const text = link.href || link.textContent;

        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                showCopyFeedback();
            }).catch(() => {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    }

    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        textarea.style.top = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            showCopyFeedback();
        } catch (err) {
            alert('Please copy the link manually: ' + text);
        }
        document.body.removeChild(textarea);
    }

    function showCopyFeedback() {
        const btn = document.getElementById('copyBtn');
        if (!btn) return;

        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
        btn.classList.add('copied');
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('copied');
        }, 2000);
    }

    // ========================================
    // GREETING TIME
    // ========================================
    (function updateGreeting() {
        const hour = new Date().getHours();
        const emojiEl = document.querySelector('.greeting-emoji');
        const textEl = document.querySelector('.greeting-text');
        if (!emojiEl || !textEl) return;
        
        let emoji = '🌙';
        let text = 'Good evening';
        if (hour < 12) { emoji = '☀️'; text = 'Good morning'; }
        else if (hour < 17) { emoji = '☀️'; text = 'Good afternoon'; }
        
        emojiEl.textContent = emoji;
        textEl.textContent = text;
    })();

    // ========================================
    // HAPTIC FEEDBACK
    // ========================================
    document.querySelectorAll('.action-btn, .content-item, .stat-mini, .btn-primary, .btn-copy, .gallery-thumb, .view-more, .guest-card').forEach(el => {
        el.addEventListener('touchstart', function() {
            if (navigator.vibrate) navigator.vibrate(6);
        }, { passive: true });
    });
</script>
</body>
</html>