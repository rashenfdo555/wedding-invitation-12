<?php
// admin/edit-invitation.php - PREMIUM THEME

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
$username = $user['username'] ?? 'default';

// Get feature flags
$features = getUserFeatures($user_id);
$has_gallery = $features['has_gallery'] ?? 0;
$has_table_finder = $features['has_table_finder'] ?? 0;

// Set default values for color columns
if ($invitation) {
    $invitation['bg_main_color'] = $invitation['bg_main_color'] ?? '#f5f5fa';
    $invitation['bg_card_color'] = $invitation['bg_card_color'] ?? '#ffffff';
    $invitation['text_muted_color'] = $invitation['text_muted_color'] ?? '#6e6e6e';
    $invitation['text_light_gray'] = $invitation['text_light_gray'] ?? '#9e9e9e';
    $invitation['border_light_color'] = $invitation['border_light_color'] ?? '#eae5dd';
    $invitation['map_bg_color'] = $invitation['map_bg_color'] ?? '#e5e5e3';
}

$schedule_items = $invitation ? getScheduleItems($invitation['id']) : [];
$gallery_images = $invitation ? getGalleryImages($invitation['id']) : [];
$table_entries = $invitation ? getTableFinderEntries($invitation['id']) : [];
$gallery_count = $invitation ? countGalleryImages($invitation['id']) : 0;
$table_count = $invitation ? countTableFinderEntries($invitation['id']) : 0;
$error = '';
$success = '';

// Helper function to truncate text
function truncateText($text, $length = 10) {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}

// Handle Reset Colors
if (isset($_GET['reset_colors']) && $_GET['reset_colors'] == '1') {
    if (resetInvitationColorsToTheme($invitation['id'], $invitation['theme_id'])) {
        $success = 'Colors reset to default successfully!';
        $invitation = getInvitationByUserId($user_id);
        if ($invitation) {
            $invitation['bg_main_color'] = $invitation['bg_main_color'] ?? '#f5f5fa';
            $invitation['bg_card_color'] = $invitation['bg_card_color'] ?? '#ffffff';
            $invitation['text_muted_color'] = $invitation['text_muted_color'] ?? '#6e6e6e';
            $invitation['text_light_gray'] = $invitation['text_light_gray'] ?? '#9e9e9e';
            $invitation['border_light_color'] = $invitation['border_light_color'] ?? '#eae5dd';
            $invitation['map_bg_color'] = $invitation['map_bg_color'] ?? '#e5e5e3';
        }
    } else {
        $error = 'Failed to reset colors.';
    }
}

// Handle Clear Gallery
if (isset($_GET['clear_gallery']) && $_GET['clear_gallery'] == '1' && $invitation) {
    if (clearGalleryImages($invitation['id'])) {
        $success = 'All gallery images cleared successfully!';
        $gallery_images = getGalleryImages($invitation['id']);
        $gallery_count = countGalleryImages($invitation['id']);
    } else {
        $error = 'Failed to clear gallery images.';
    }
}

// Handle Clear Table Finder
if (isset($_GET['clear_tables']) && $_GET['clear_tables'] == '1' && $invitation) {
    if (clearTableFinderEntries($invitation['id'])) {
        $success = 'All table entries cleared successfully!';
        $table_entries = getTableFinderEntries($invitation['id']);
        $table_count = countTableFinderEntries($invitation['id']);
    } else {
        $error = 'Failed to clear table entries.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file uploads
    $upload_dir = '../uploads/';
    
    $hero_image = $_POST['hero_image_existing'] ?? ($invitation['hero_image'] ?? 'hero-couple.jpg');
    $bride_image = $_POST['bride_image_existing'] ?? ($invitation['bride_image'] ?? 'bride.jpg');
    $groom_image = $_POST['groom_image_existing'] ?? ($invitation['groom_image'] ?? 'groom.jpg');
    $intro_video = $_POST['intro_video_existing'] ?? ($invitation['intro_video'] ?? 'intro-open.mp4');
    $intro_audio = $_POST['intro_audio_existing'] ?? ($invitation['intro_audio'] ?? 'intro-music1.mp3');
    $bg_audio = $_POST['bg_audio_existing'] ?? ($invitation['bg_audio'] ?? 'intro-music.mp3');
    
    // Handle file uploads
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
        'couple_names' => $_POST['couple_names'],
        'bride_name' => $_POST['bride_name'],
        'groom_name' => $_POST['groom_name'],
        'couple_headline' => $_POST['couple_headline'],
        'wedding_date' => $_POST['wedding_date'],
        'month' => $_POST['month'],
        'day' => $_POST['day'],
        'year' => $_POST['year'],
        'countdown_target' => $_POST['countdown_target'],
        'sub_headline' => $_POST['sub_headline'],
        'venue_title' => $_POST['venue_title'],
        'venue_location' => $_POST['venue_location'],
        'venue_address' => $_POST['venue_address'],
        'google_maps_url' => $_POST['google_maps_url'],
        'event_time' => $_POST['event_time'],
        'whatsapp_phone' => $_POST['whatsapp_phone'],
        'primary_color' => $_POST['primary_color'] ?? '#6C5CE7',
        'secondary_color' => $_POST['secondary_color'] ?? '#f5f5fa',
        'text_color' => $_POST['text_color'] ?? '#1A1A2E',
        'bg_main_color' => $_POST['bg_main_color'] ?? '#f5f5fa',
        'bg_card_color' => $_POST['bg_card_color'] ?? '#ffffff',
        'text_muted_color' => $_POST['text_muted_color'] ?? '#6e6e6e',
        'text_light_gray' => $_POST['text_light_gray'] ?? '#9e9e9e',
        'border_light_color' => $_POST['border_light_color'] ?? '#eae5dd',
        'map_bg_color' => $_POST['map_bg_color'] ?? '#e5e5e3',
        'hero_image' => $hero_image,
        'bride_image' => $bride_image,
        'groom_image' => $groom_image,
        'intro_video' => $intro_video,
        'intro_audio' => $intro_audio,
        'bg_audio' => $bg_audio,
        'show_profiles' => isset($_POST['show_profiles']) ? 1 : 0
    ];
    
    if ($invitation) {
        if (updateInvitation($invitation['id'], $data)) {
            clearScheduleItems($invitation['id']);
            if (isset($_POST['schedule_time']) && is_array($_POST['schedule_time'])) {
                for ($i = 0; $i < count($_POST['schedule_time']); $i++) {
                    if (!empty($_POST['schedule_time'][$i]) && !empty($_POST['schedule_title'][$i])) {
                        addScheduleItem(
                            $invitation['id'],
                            $_POST['schedule_time'][$i],
                            $_POST['schedule_title'][$i],
                            $_POST['schedule_desc'][$i] ?? '',
                            $_POST['schedule_icon'][$i] ?? 'fa-star',
                            $i + 1
                        );
                    }
                }
            }
            $success = 'Invitation updated successfully! 🎉';
            $invitation = getInvitationByUserId($user_id);
            if ($invitation) {
                $invitation['bg_main_color'] = $invitation['bg_main_color'] ?? '#f5f5fa';
                $invitation['bg_card_color'] = $invitation['bg_card_color'] ?? '#ffffff';
                $invitation['text_muted_color'] = $invitation['text_muted_color'] ?? '#6e6e6e';
                $invitation['text_light_gray'] = $invitation['text_light_gray'] ?? '#9e9e9e';
                $invitation['border_light_color'] = $invitation['border_light_color'] ?? '#eae5dd';
                $invitation['map_bg_color'] = $invitation['map_bg_color'] ?? '#e5e5e3';
            }
            $schedule_items = getScheduleItems($invitation['id']);
            $gallery_images = getGalleryImages($invitation['id']);
            $table_entries = getTableFinderEntries($invitation['id']);
            $gallery_count = countGalleryImages($invitation['id']);
            $table_count = countTableFinderEntries($invitation['id']);
        } else {
            $error = 'Failed to update invitation.';
        }
    } else {
        // Create new invitation
        $slug = generateSlug($_POST['couple_names'] ?: $_SESSION['full_name']);
        $data['user_admin_id'] = $user_id;
        $data['slug'] = $slug;
        $data['theme_id'] = $user['theme_id'] ?? 1;
        
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
            $invitation = getInvitationByUserId($user_id);
            if ($invitation) {
                $invitation['bg_main_color'] = $invitation['bg_main_color'] ?? '#f5f5fa';
                $invitation['bg_card_color'] = $invitation['bg_card_color'] ?? '#ffffff';
                $invitation['text_muted_color'] = $invitation['text_muted_color'] ?? '#6e6e6e';
                $invitation['text_light_gray'] = $invitation['text_light_gray'] ?? '#9e9e9e';
                $invitation['border_light_color'] = $invitation['border_light_color'] ?? '#eae5dd';
                $invitation['map_bg_color'] = $invitation['map_bg_color'] ?? '#e5e5e3';
            }
            $schedule_items = getScheduleItems($invitation['id']);
            $gallery_images = getGalleryImages($invitation['id']);
            $table_entries = getTableFinderEntries($invitation['id']);
            $gallery_count = countGalleryImages($invitation['id']);
            $table_count = countTableFinderEntries($invitation['id']);
        } else {
            $error = 'Failed to create invitation.';
        }
    }
}

// Get icons for schedule dropdown
$icons = getScheduleIcons();
$initials = strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1));
$couple_name = $_SESSION['couple_name'] ?? 'No couple set';
$full_name = htmlspecialchars($_SESSION['full_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Edit Invitation - <?php echo $full_name; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ========================================
           PREMIUM EDIT INVITATION
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

        .btn-warning {
            background: var(--warning);
            color: #fff;
        }

        .btn-warning:hover {
            background: #D35400;
            transform: translateY(-2px);
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

        .btn-add:active {
            transform: scale(0.96);
        }

        /* ========================================
           FORM
           ======================================== */
        .admin-form {
            padding: 16px 20px 0;
        }

        .form-section {
            background: var(--bg-white);
            border-radius: var(--radius-md);
            border: 1px solid rgba(0,0,0,0.04);
            padding: 24px 20px;
            margin-bottom: 16px;
            transition: var(--transition);
            box-shadow: var(--shadow-xs);
        }

        .form-section:hover {
            border-color: var(--gray-200);
        }

        .form-section .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--gray-200);
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
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
        }

        .form-section .section-header .required-tag {
            margin-left: auto;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #fff;
            background: var(--primary-gradient);
            padding: 2px 12px;
            border-radius: 20px;
            font-family: 'Inter', sans-serif;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .form-grid-color {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 8px;
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
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: var(--transition);
            background: var(--gray-100);
            width: 100%;
            color: var(--dark);
            outline: none;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(108, 92, 231, 0.1);
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
            word-break: break-all;
        }

        /* ========================================
           IMAGE PREVIEW
           ======================================== */
        .image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .image-preview .preview-item {
            width: 60px;
            height: 60px;
            border-radius: var(--radius-sm);
            overflow: hidden;
            border: 1px solid var(--gray-200);
            transition: var(--transition);
            background: var(--gray-100);
        }

        .image-preview .preview-item:hover {
            border-color: var(--primary);
            transform: scale(1.05);
        }

        .image-preview .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ========================================
           SCHEDULE ITEMS
           ======================================== */
        .schedule-item {
            background: var(--gray-100);
            padding: 16px 18px;
            border-radius: var(--radius-sm);
            margin-bottom: 12px;
            border: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .schedule-item:hover {
            border-color: var(--primary-light);
        }

        .schedule-item .schedule-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .schedule-item .schedule-header .item-number {
            font-size: 13px;
            font-weight: 600;
            color: var(--primary-light);
        }

        .schedule-item .schedule-header .btn-remove {
            background: none;
            border: none;
            color: rgb(255 0 0 / 40%);
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
            gap: 12px;
            padding: 0 20px 20px;
        }

        .form-actions .btn {
            flex: 1;
            justify-content: center;
        }

        /* ========================================
           DANGER ZONE
           ======================================== */
        .danger-zone {
            margin: 0 20px 16px;
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
            margin-bottom: 12px;
            border: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .danger-zone .danger-item:last-child {
            margin-bottom: 0;
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

            .admin-form {
                padding: 20px 0 0;
            }

            .form-section {
                padding: 28px 24px;
                margin-bottom: 20px;
            }

            .form-actions {
                padding: 0 0 20px;
            }

            .alert {
                margin: 16px 0 0;
            }

            .danger-zone {
                margin: 0 0 16px;
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

            .form-grid {
                gap: 18px;
            }
        }

        @media (max-width: 480px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .form-grid-color {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

            .form-section {
                padding: 18px 14px;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
            }

            .schedule-item {
                padding: 12px 14px;
            }

            .schedule-item .form-grid {
                gap: 10px;
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
        <h1><?php echo $invitation ? 'Edit' : 'Create'; ?> Invitation</h1>
        <p><?php echo $invitation ? 'Update your wedding invitation details' : 'Design a beautiful wedding invitation'; ?></p>
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
    FORM
    ======================================== -->
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
                    <input type="text" name="couple_names" value="<?php echo htmlspecialchars($invitation['couple_names'] ?? ''); ?>" placeholder="e.g., John & Jane" required>
                </div>
                <div class="form-group">
                    <label>Headline</label>
                    <input type="text" name="couple_headline" value="<?php echo htmlspecialchars($invitation['couple_headline'] ?? ''); ?>" placeholder="e.g., John & Jane">
                </div>
                <div class="form-group">
                    <label>Bride Name <span class="required">*</span></label>
                    <input type="text" name="bride_name" value="<?php echo htmlspecialchars($invitation['bride_name'] ?? ''); ?>" placeholder="e.g., Jane" required>
                </div>
                <div class="form-group">
                    <label>Groom Name <span class="required">*</span></label>
                    <input type="text" name="groom_name" value="<?php echo htmlspecialchars($invitation['groom_name'] ?? ''); ?>" placeholder="e.g., John" required>
                </div>
                <div class="form-group full-width">
                    <label class="checkbox-group">
                        <input type="checkbox" name="show_profiles" value="1" <?php echo ($invitation['show_profiles'] ?? 1) ? 'checked' : ''; ?>>
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
                    <input type="text" name="wedding_date" value="<?php echo htmlspecialchars($invitation['wedding_date'] ?? ''); ?>" placeholder="e.g., JULY 11 2026" required>
                </div>
                <div class="form-group">
                    <label>Month <span class="required">*</span></label>
                    <input type="text" name="month" value="<?php echo htmlspecialchars($invitation['month'] ?? ''); ?>" placeholder="e.g., JULY" required>
                </div>
                <div class="form-group">
                    <label>Day <span class="required">*</span></label>
                    <input type="text" name="day" value="<?php echo htmlspecialchars($invitation['day'] ?? ''); ?>" placeholder="e.g., 11" required>
                </div>
                <div class="form-group">
                    <label>Year <span class="required">*</span></label>
                    <input type="text" name="year" value="<?php echo htmlspecialchars($invitation['year'] ?? ''); ?>" placeholder="e.g., 2026" required>
                </div>
                <div class="form-group full-width">
                    <label>Countdown Target <span class="required">*</span></label>
                    <input type="datetime-local" name="countdown_target" value="<?php echo $invitation ? date('Y-m-d\TH:i', strtotime($invitation['countdown_target'])) : ''; ?>" required>
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
                <textarea name="sub_headline" rows="4" placeholder="Tell your love story..."><?php echo htmlspecialchars($invitation['sub_headline'] ?? ''); ?></textarea>
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
                    <input type="text" name="venue_title" value="<?php echo htmlspecialchars($invitation['venue_title'] ?? ''); ?>" placeholder="e.g., Aldwark Manor Estate" required>
                </div>
                <div class="form-group">
                    <label>Location <span class="required">*</span></label>
                    <input type="text" name="venue_location" value="<?php echo htmlspecialchars($invitation['venue_location'] ?? ''); ?>" placeholder="e.g., York" required>
                </div>
                <div class="form-group full-width">
                    <label>Address <span class="required">*</span></label>
                    <input type="text" name="venue_address" value="<?php echo htmlspecialchars($invitation['venue_address'] ?? ''); ?>" placeholder="e.g., Aldwark, Alne, York" required>
                </div>
                <div class="form-group full-width">
                    <label>Google Maps URL <span class="required">*</span></label>
                    <input type="url" name="google_maps_url" value="<?php echo htmlspecialchars($invitation['google_maps_url'] ?? 'https://maps.google.com'); ?>" placeholder="https://maps.google.com" required>
                </div>
                <div class="form-group">
                    <label>Event Time <span class="required">*</span></label>
                    <input type="text" name="event_time" value="<?php echo htmlspecialchars($invitation['event_time'] ?? '5:00 PM To 11:00 PM'); ?>" placeholder="e.g., 5:00 PM To 11:00 PM" required>
                </div>
                <div class="form-group">
                    <label>WhatsApp Phone <span class="required">*</span></label>
                    <input type="text" name="whatsapp_phone" value="<?php echo htmlspecialchars($invitation['whatsapp_phone'] ?? ''); ?>" placeholder="e.g., 94771234567" required>
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
            <p style="font-size:13px; color:var(--gray-500); margin-bottom:16px;">Customize the color palette for your invitation</p>
            <div class="form-grid-color">
                <div class="form-group">
                    <label>Primary Color</label>
                    <input type="color" name="primary_color" value="<?php echo htmlspecialchars($invitation['primary_color'] ?? '#6C5CE7'); ?>">
                    <span class="helper-text">Main accent color</span>
                </div>
                <div class="form-group">
                    <label>Secondary Color</label>
                    <input type="color" name="secondary_color" value="<?php echo htmlspecialchars($invitation['secondary_color'] ?? '#f5f5fa'); ?>">
                    <span class="helper-text">Background & light accents</span>
                </div>
                <div class="form-group">
                    <label>Text Primary</label>
                    <input type="color" name="text_color" value="<?php echo htmlspecialchars($invitation['text_color'] ?? '#1A1A2E'); ?>">
                    <span class="helper-text">Main text color</span>
                </div>
                <div class="form-group">
                    <label>Text Muted</label>
                    <input type="color" name="text_muted_color" value="<?php echo htmlspecialchars($invitation['text_muted_color'] ?? '#6e6e6e'); ?>">
                    <span class="helper-text">Secondary/subtle text</span>
                </div>
                <div class="form-group">
                    <label>Text Light Gray</label>
                    <input type="color" name="text_light_gray" value="<?php echo htmlspecialchars($invitation['text_light_gray'] ?? '#9e9e9e'); ?>">
                    <span class="helper-text">Labels & hints</span>
                </div>
                <div class="form-group">
                    <label>Background Main</label>
                    <input type="color" name="bg_main_color" value="<?php echo htmlspecialchars($invitation['bg_main_color'] ?? '#f5f5fa'); ?>">
                    <span class="helper-text">Main page background</span>
                </div>
                <div class="form-group">
                    <label>Card Background</label>
                    <input type="color" name="bg_card_color" value="<?php echo htmlspecialchars($invitation['bg_card_color'] ?? '#ffffff'); ?>">
                    <span class="helper-text">Cards & sections background</span>
                </div>
                <div class="form-group">
                    <label>Border Light</label>
                    <input type="color" name="border_light_color" value="<?php echo htmlspecialchars($invitation['border_light_color'] ?? '#eae5dd'); ?>">
                    <span class="helper-text">Light border color</span>
                </div>
                <div class="form-group">
                    <label>Map Background</label>
                    <input type="color" name="map_bg_color" value="<?php echo htmlspecialchars($invitation['map_bg_color'] ?? '#e5e5e3'); ?>">
                    <span class="helper-text">Map placeholder background</span>
                </div>
            </div>

            <?php if ($invitation): ?>
            <div style="margin-top:20px; padding-top:16px; border-top:1px solid var(--gray-200);">
                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                    <span style="font-size:12px; color:var(--gray-500);">
                        <i class="fa-solid fa-triangle-exclamation" style="color:var(--warning);"></i>
                        Reset all colors to default values
                    </span>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <input type="checkbox" id="resetColorsCheck" style="width:16px; height:16px; accent-color:var(--warning); cursor:pointer;">
                        <label for="resetColorsCheck" style="font-size:12px; cursor:pointer;">I understand</label>
                    </div>
                    <a href="?reset_colors=1" id="resetColorsBtn" class="btn btn-warning btn-sm" style="pointer-events:none; opacity:0.5; text-decoration:none;">
                        <i class="fa-solid fa-rotate-right"></i> Reset Colors
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Images & Media -->
        <div class="form-section">
            <div class="section-header">
                <div class="icon-wrap"><i class="fa-solid fa-image"></i></div>
                <h2>Images & Media</h2>
            </div>
            <div class="form-grid">
    <!-- Hero Image Group -->
    <div class="form-group" style="margin-bottom: 20px; display: flex !important; flex-direction: row !important; align-items: flex-start !important; ">
        <!-- Left Side: Image Preview (In front) -->
        <?php if ($invitation && !empty($invitation['hero_image']) && file_exists('../uploads/images/' . $invitation['hero_image'])): ?>
        <div class="image-preview" style="margin: 0 !important; flex-shrink: 0 !important; width: 80px !important;">
            <div class="preview-item">
                <img src="../uploads/images/<?php echo htmlspecialchars($invitation['hero_image']); ?>" alt="Hero" style="width: 100% !important; max-height: 80px !important; display: block !important; border-radius: 4px !important; object-fit: cover !important;">
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Right Side: Text & Controls Section -->
        <div class="form-controls-wrapper" style="display: flex !important; flex-direction: column !important; flex-grow: 1 !important; gap: 5px !important;">
            <label style="margin: 0 !important; font-weight: bold !important; display: block !important;">Hero Image</label>
            <input type="file" name="hero_image" accept="image/*" style="width: 100% !important; display: block !important;">
            <span class="current-file" style="display: block !important;">Current: <?php echo truncateText(htmlspecialchars($invitation['hero_image'] ?? 'hero-couple.jpg'), 15); ?></span>
            <input type="hidden" name="hero_image_existing" value="<?php echo htmlspecialchars($invitation['hero_image'] ?? 'hero-couple.jpg'); ?>">
        </div>
    </div>

    <!-- Bride Image Group -->
    <div class="form-group" style="margin-bottom: 20px; display: flex !important; flex-direction: row !important; align-items: flex-start !important;">
        <!-- Left Side: Image Preview (In front) -->
        <?php if ($invitation && !empty($invitation['bride_image']) && file_exists('../uploads/images/' . $invitation['bride_image'])): ?>
        <div class="image-preview" style="margin: 0 !important; flex-shrink: 0 !important; width: 80px !important;">
            <div class="preview-item">
                <img src="../uploads/images/<?php echo htmlspecialchars($invitation['bride_image']); ?>" alt="Bride" style="width: 100% !important; max-height: 80px !important; display: block !important; border-radius: 4px !important; object-fit: cover !important;">
            </div>
        </div>
        <?php endif; ?>

        <!-- Right Side: Text & Controls Section -->
        <div class="form-controls-wrapper" style="display: flex !important; flex-direction: column !important; flex-grow: 1 !important; gap: 5px !important;">
            <label style="margin: 0 !important; font-weight: bold !important; display: block !important;">Bride Image</label>
            <input type="file" name="bride_image" accept="image/*" style="width: 100% !important; display: block !important;">
            <span class="current-file" style="display: block !important;">Current: <?php echo truncateText(htmlspecialchars($invitation['bride_image'] ?? 'bride.jpg'), 15); ?></span>
            <input type="hidden" name="bride_image_existing" value="<?php echo htmlspecialchars($invitation['bride_image'] ?? 'bride.jpg'); ?>">
        </div>
    </div>

    <!-- Groom Image Group -->
    <div class="form-group" style="margin-bottom: 20px; display: flex !important; flex-direction: row !important; align-items: flex-start !important;">
        <!-- Left Side: Image Preview (In front) -->
        <?php if ($invitation && !empty($invitation['groom_image']) && file_exists('../uploads/images/' . $invitation['groom_image'])): ?>
        <div class="image-preview" style="margin: 0 !important; flex-shrink: 0 !important; width: 80px !important;">
            <div class="preview-item">
                <img src="../uploads/images/<?php echo htmlspecialchars($invitation['groom_image']); ?>" alt="Groom" style="width: 100% !important; max-height: 80px !important; display: block !important; border-radius: 4px !important; object-fit: cover !important;">
            </div>
        </div>
        <?php endif; ?>

        <!-- Right Side: Text & Controls Section -->
        <div class="form-controls-wrapper" style="display: flex !important; flex-direction: column !important; flex-grow: 1 !important; gap: 5px !important;">
            <label style="margin: 0 !important; font-weight: bold !important; display: block !important;">Groom Image</label>
            <input type="file" name="groom_image" accept="image/*" style="width: 100% !important; display: block !important;">
            <span class="current-file" style="display: block !important;">Current: <?php echo truncateText(htmlspecialchars($invitation['groom_image'] ?? 'groom.jpg'), 15); ?></span>
            <input type="hidden" name="groom_image_existing" value="<?php echo htmlspecialchars($invitation['groom_image'] ?? 'groom.jpg'); ?>">
        </div>
    </div>

    <!-- Intro Video Group -->
    <div class="form-group">
        <label>Intro Video</label>
        <input type="file" name="intro_video" accept="video/mp4,video/webm">
        <span class="current-file">Current: <?php echo truncateText(htmlspecialchars($invitation['intro_video'] ?? 'intro-open.mp4'), 15); ?></span>
        <input type="hidden" name="intro_video_existing" value="<?php echo htmlspecialchars($invitation['intro_video'] ?? 'intro-open.mp4'); ?>">
    </div>

    <!-- Intro Audio Group -->
    <div class="form-group">
        <label>Intro Audio</label>
        <input type="file" name="intro_audio" accept="audio/mp3,audio/wav">
        <span class="current-file">Current: <?php echo truncateText(htmlspecialchars($invitation['intro_audio'] ?? 'intro-music1.mp3'), 15); ?></span>
        <input type="hidden" name="intro_audio_existing" value="<?php echo htmlspecialchars($invitation['intro_audio'] ?? 'intro-music1.mp3'); ?>">
    </div>

    <!-- Background Audio Group -->
    <div class="form-group">
        <label>Background Audio</label>
        <input type="file" name="bg_audio" accept="audio/mp3,audio/wav">
        <span class="current-file">Current: <?php echo truncateText(htmlspecialchars($invitation['bg_audio'] ?? 'intro-music.mp3'), 15); ?></span>
        <input type="hidden" name="bg_audio_existing" value="<?php echo htmlspecialchars($invitation['bg_audio'] ?? 'intro-music.mp3'); ?>">
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
                <?php if (empty($schedule_items)): ?>
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
                <?php else: ?>
                    <?php foreach ($schedule_items as $item): ?>
                        <div class="schedule-item">
                            <div class="schedule-header">
                                <span class="item-number">Item <?php echo $item['sort_order']; ?></span>
                                <button type="button" class="btn-remove" onclick="this.closest('.schedule-item').remove()">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Time</label>
                                    <input type="text" name="schedule_time[]" value="<?php echo htmlspecialchars($item['time']); ?>" placeholder="e.g., 5:00 PM">
                                </div>
                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="schedule_title[]" value="<?php echo htmlspecialchars($item['title']); ?>" placeholder="e.g., Guest Arrival">
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <input type="text" name="schedule_desc[]" value="<?php echo htmlspecialchars($item['description']); ?>" placeholder="e.g., Welcome & seating">
                                </div>
                                <div class="form-group">
                                    <label>Icon</label>
                                    <select name="schedule_icon[]">
                                        <?php foreach ($icons as $value => $label): ?>
                                            <option value="<?php echo $value; ?>" <?php echo $item['icon'] === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button type="button" class="btn-add" onclick="addScheduleItem()">
                <i class="fa-solid fa-plus"></i> Add Schedule Item
            </button>
        </div>

        <!-- ===== DANGER ZONE ===== -->
        <!-- <?php if ($invitation): ?>
        <div class="danger-zone">
            
            <div class="danger-body">
                <?php if ($gallery_count > 0): ?>
                <div class="danger-item">
                    <div class="item-top">
                        <span class="item-label">
                            <i class="fa-solid fa-images"></i>
                            Gallery Images
                        </span>
                        <span class="item-count"><?php echo $gallery_count; ?> images</span>
                    </div>
                    <p class="item-desc">Permanently delete all <?php echo $gallery_count; ?> gallery images from your invitation.</p>
                    <div class="warning-box">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span><strong>Warning:</strong> This action cannot be undone!</span>
                    </div>
                    <div class="confirm-row">
                        <input type="checkbox" id="clearGalleryCheck">
                        <label for="clearGalleryCheck">I understand, delete all <?php echo $gallery_count; ?> images</label>
                    </div>
                    <a href="?clear_gallery=1" id="clearGalleryBtn" class="btn btn-danger btn-sm" style="pointer-events:none; opacity:0.5; text-decoration:none;">
                        <i class="fa-solid fa-trash-can"></i> Clear Gallery
                    </a>
                </div>
                <?php endif; ?>

                <?php if ($table_count > 0): ?>
                <div class="danger-item">
                    <div class="item-top">
                        <span class="item-label">
                            <i class="fa-solid fa-users"></i>
                            Table Entries
                        </span>
                        <span class="item-count"><?php echo $table_count; ?> entries</span>
                    </div>
                    <p class="item-desc">Permanently delete all <?php echo $table_count; ?> table entries from your invitation.</p>
                    <div class="warning-box">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span><strong>Warning:</strong> This action cannot be undone!</span>
                    </div>
                    <div class="confirm-row">
                        <input type="checkbox" id="clearTablesCheck">
                        <label for="clearTablesCheck">I understand, delete all <?php echo $table_count; ?> entries</label>
                    </div>
                    <a href="?clear_tables=1" id="clearTablesBtn" class="btn btn-danger btn-sm" style="pointer-events:none; opacity:0.5; text-decoration:none;">
                        <i class="fa-solid fa-trash-can"></i> Clear Tables
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?> -->

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-check"></i> <?php echo $invitation ? 'Update' : 'Create'; ?> Invitation
            </button>
        </div>
    </form>

    <!-- ========================================
    BOTTOM NAVIGATION
    ======================================== -->
    <?php include 'includes/bottom-nav.php'; ?>
</div>

<script>
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

    // ========================================
    // DANGER ZONE CONFIRMATIONS
    // ========================================
    document.getElementById('resetColorsCheck')?.addEventListener('change', function() {
        const btn = document.getElementById('resetColorsBtn');
        if (this.checked) {
            btn.style.pointerEvents = 'auto';
            btn.style.opacity = '1';
        } else {
            btn.style.pointerEvents = 'none';
            btn.style.opacity = '0.5';
        }
    });

    document.getElementById('clearGalleryCheck')?.addEventListener('change', function() {
        const btn = document.getElementById('clearGalleryBtn');
        if (this.checked) {
            btn.style.pointerEvents = 'auto';
            btn.style.opacity = '1';
        } else {
            btn.style.pointerEvents = 'none';
            btn.style.opacity = '0.5';
        }
    });

    document.getElementById('clearTablesCheck')?.addEventListener('change', function() {
        const btn = document.getElementById('clearTablesBtn');
        if (this.checked) {
            btn.style.pointerEvents = 'auto';
            btn.style.opacity = '1';
        } else {
            btn.style.pointerEvents = 'none';
            btn.style.opacity = '0.5';
        }
    });

    // ========================================
    // HAPTIC FEEDBACK
    // ========================================
    document.querySelectorAll('.btn, .btn-add, .form-section, .schedule-item, .danger-item').forEach(el => {
        el.addEventListener('touchstart', function() {
            if (navigator.vibrate) navigator.vibrate(6);
        }, { passive: true });
    });
</script>
</body>
</html>