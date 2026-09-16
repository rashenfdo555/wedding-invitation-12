<?php
// admin/gallery.php - PREMIUM THEME WITH MULTIPLE IMAGE UPLOAD

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
$username = $user['username'] ?? 'default';
$invitation = getInvitationByUserId($user_id);

if (!$invitation) {
    header('Location: dashboard.php');
    exit;
}

// Get feature flags
$features = getUserFeatures($user_id);
$has_gallery = $features['has_gallery'] ?? 0;
$has_table_finder = $features['has_table_finder'] ?? 0;

$gallery_images = getGalleryImages($invitation['id']);
$gallery_count = countGalleryImages($invitation['id']);
$max_images = 20;
$error = '';
$success = '';

// Helper function to truncate text
function truncateText($text, $length = 10) {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}

// Handle Clear Gallery
if (isset($_POST['clear_gallery']) && $_POST['clear_gallery'] == '1') {
    if (isset($_POST['confirm_clear']) && $_POST['confirm_clear'] == 'yes') {
        if (clearGalleryImages($invitation['id'])) {
            $success = 'All gallery images cleared successfully!';
            $gallery_images = getGalleryImages($invitation['id']);
            $gallery_count = countGalleryImages($invitation['id']);
        } else {
            $error = 'Failed to clear gallery images.';
        }
    } else {
        $error = 'Please confirm that you want to clear all gallery images.';
    }
}

// Handle reorder
if (isset($_POST['reorder_gallery']) && $_POST['reorder_gallery'] == '1') {
    if (isset($_POST['image_order']) && !empty($_POST['image_order'])) {
        $order_ids = explode(',', $_POST['image_order']);
        $conn = getDBConnection();
        $success_count = 0;
        
        foreach ($order_ids as $index => $image_id) {
            $sort_order = $index + 1;
            $stmt = $conn->prepare("UPDATE gallery_images SET sort_order = ? WHERE id = ? AND invitation_id = ?");
            $stmt->bind_param("iii", $sort_order, $image_id, $invitation['id']);
            if ($stmt->execute()) {
                $success_count++;
            }
        }
        
        if ($success_count > 0) {
            $success = "Gallery images reordered successfully! ($success_count images updated)";
            $gallery_images = getGalleryImages($invitation['id']);
            $gallery_count = countGalleryImages($invitation['id']);
        } else {
            $error = 'Failed to reorder images. Please try again.';
        }
    } else {
        $error = 'No image order data received.';
    }
}

// Multiple Image Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_images'])) {
    if ($gallery_count >= $max_images) {
        $error = "Maximum {$max_images} images reached. Please delete some images first.";
    } elseif (!empty($_FILES['gallery_images']['name'][0])) {
        $upload_dir = '../uploads/images/';
        $uploaded_count = 0;
        $failed_count = 0;
        $errors = [];
        
        $files = $_FILES['gallery_images'];
        $file_count = count($files['name']);
        
        if ($gallery_count + $file_count > $max_images) {
            $error = "Cannot upload {$file_count} images. You already have {$gallery_count} images. Maximum is {$max_images}.";
        } else {
            for ($i = 0; $i < $file_count; $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    $failed_count++;
                    $errors[] = "File '{$files['name'][$i]}' upload error.";
                    continue;
                }
                
                if ($files['size'][$i] > 20 * 1024 * 1024) {
                    $failed_count++;
                    $errors[] = "File '{$files['name'][$i]}' is too large. Maximum size is 20MB.";
                    continue;
                }
                
                $file_data = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                
                $uploaded = uploadFile(
                    $file_data, 
                    $upload_dir, 
                    $username,
                    ['jpg', 'jpeg', 'png', 'gif', 'webp']
                );
                
                if ($uploaded) {
                    $caption = $_POST['caption_' . $i] ?? '';
                    $sort_order = $gallery_count + $uploaded_count + 1;
                    if (addGalleryImage($invitation['id'], $uploaded, $caption, $sort_order)) {
                        $uploaded_count++;
                    } else {
                        $failed_count++;
                        $errors[] = "Failed to save '{$files['name'][$i]}' to database.";
                    }
                } else {
                    $failed_count++;
                    $errors[] = "Failed to upload '{$files['name'][$i]}'. Invalid file format.";
                }
            }
            
            if ($uploaded_count > 0) {
                $success = "Successfully uploaded {$uploaded_count} image(s)!";
                if ($failed_count > 0) {
                    $success .= " {$failed_count} file(s) failed.";
                }
                $gallery_images = getGalleryImages($invitation['id']);
                $gallery_count = countGalleryImages($invitation['id']);
            } else {
                $error = "Failed to upload any images. " . implode(" ", $errors);
            }
        }
    } else {
        $error = 'Please select at least one image to upload.';
    }
}

// Delete image
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (deleteGalleryImage($id)) {
        $success = 'Image deleted successfully!';
        $gallery_images = getGalleryImages($invitation['id']);
        $gallery_count = countGalleryImages($invitation['id']);
    }
}

$max_reached = ($gallery_count >= $max_images);
$initials = strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1));
$couple_name = $_SESSION['couple_name'] ?? 'No couple set';
$full_name = htmlspecialchars($_SESSION['full_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Gallery - <?php echo $full_name; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ========================================
           PREMIUM GALLERY
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

        .page-header .header-stats {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 4px;
            color: var(--gray-500);
            font-size: 14px;
        }

        .page-header .header-stats .stat-dot {
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: var(--gray-300);
        }

        .page-header .header-stats .stat-count {
            font-weight: 600;
            color: var(--dark);
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
           UPLOAD ZONE
           ======================================== */
        .upload-zone {
            border: 2px dashed var(--gray-300);
            border-radius: var(--radius-sm);
            padding: 32px 20px;
            text-align: center;
            transition: var(--transition);
            background: var(--gray-100);
            cursor: pointer;
        }

        .upload-zone:hover {
            border-color: var(--primary);
            background: var(--primary-bg);
        }

        .upload-zone.dragover {
            border-color: var(--primary);
            background: var(--primary-bg);
        }

        .upload-zone .icon {
            font-size: 36px;
            color: var(--gray-400);
            margin-bottom: 8px;
        }

        .upload-zone h4 {
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            color: var(--dark-700);
        }

        .upload-zone p {
            font-size: 13px;
            color: var(--gray-500);
        }

        .upload-zone .sub-text {
            font-size: 12px;
            color: var(--gray-400);
        }

        .upload-zone .file-input {
            display: none;
        }

        .file-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .file-list .file-tag {
            background: var(--gray-200);
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            color: var(--dark-700);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .file-list .file-tag i {
            color: var(--success);
        }

        .caption-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 16px;
        }

        .caption-group .form-group {
            margin-bottom: 0;
        }

        .caption-group .form-group label {
            font-size: 12px;
            font-weight: 600;
            color: var(--dark-700);
            margin-bottom: 4px;
            display: block;
        }

        .caption-group .form-group input {
            padding: 8px 12px;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            background: var(--gray-100);
            width: 100%;
            transition: var(--transition);
            outline: none;
        }

        .caption-group .form-group input:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(108, 92, 231, 0.1);
        }

        /* ========================================
           MAX REACHED
           ======================================== */
        .max-reached {
            background: var(--warning-bg);
            border-radius: var(--radius-md);
            padding: 24px 20px;
            text-align: center;
            border: 1px solid #F5C6A0;
            margin: 16px 20px 0;
        }

        .max-reached .icon {
            font-size: 32px;
            color: var(--warning);
            margin-bottom: 8px;
        }

        .max-reached h4 {
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            color: var(--dark-800);
        }

        .max-reached p {
            font-size: 13px;
            color: var(--gray-600);
        }

        /* ========================================
           GALLERY GRID
           ======================================== */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 12px;
            min-height: 100px;
        }

        .gallery-item {
            background: #fff;
            border-radius: var(--radius-sm);
            border: 1px solid var(--gray-200);
            overflow: hidden;
            position: relative;
            cursor: grab;
            transition: var(--transition);
            touch-action: none;
            user-select: none;
            -webkit-user-select: none;
        }

        .gallery-item:active {
            cursor: grabbing;
        }

        .gallery-item:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
        }

        .gallery-item.dragging {
            opacity: 0.4;
            transform: scale(0.95);
            z-index: 100;
        }

        .gallery-item.drag-over {
            border-color: var(--primary);
            border-style: dashed;
            background: var(--primary-bg);
        }

        .gallery-item img {
            width: 100%;
            aspect-ratio: 1;
            object-fit: cover;
            pointer-events: none;
            user-select: none;
            -webkit-user-select: none;
            display: block;
        }

        .gallery-item .info {
            padding: 8px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--gray-100);
            border-top: 1px solid var(--gray-200);
        }

        .gallery-item .info .caption {
            font-size: 11px;
            color: var(--dark-700);
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .gallery-item .info .order {
            font-size: 10px;
            font-weight: 600;
            color: var(--gray-400);
            background: var(--gray-200);
            padding: 1px 10px;
            border-radius: 20px;
            flex-shrink: 0;
        }

        .gallery-item .actions {
            position: absolute;
            top: 8px;
            right: 8px;
            display: flex;
            gap: 4px;
            opacity: 0;
            transition: var(--transition);
        }

        .gallery-item:hover .actions {
            opacity: 1;
        }

        .gallery-item .actions a {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: none;
            background: rgba(0,0,0,0.6);
            color: #fff;
            cursor: pointer;
            transition: var(--transition);
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .gallery-item .actions a:hover {
            background: var(--danger);
        }

        .gallery-item .drag-handle {
            position: absolute;
            top: 8px;
            left: 8px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(0,0,0,0.5);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            cursor: grab;
            opacity: 0;
            transition: var(--transition);
            touch-action: none;
        }

        .gallery-item:hover .drag-handle {
            opacity: 1;
        }

        .gallery-item .drag-handle:active {
            cursor: grabbing;
        }

        /* ========================================
           REORDER BAR
           ======================================== */
        .reorder-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            background: var(--gray-100);
            border-radius: var(--radius-sm);
            margin-bottom: 14px;
            border: 1px solid var(--gray-200);
            flex-wrap: wrap;
        }

        .reorder-bar .hint {
            font-size: 12px;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .reorder-bar .hint i {
            color: var(--primary);
        }

        .reorder-bar .btn {
            margin-left: auto;
        }

        /* ========================================
           EMPTY STATE
           ======================================== */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray-500);
        }

        .empty-state .icon {
            font-size: 48px;
            color: var(--gray-300);
            margin-bottom: 12px;
        }

        .empty-state h4 {
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            color: var(--dark-700);
        }

        .empty-state p {
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

            .card {
                margin: 16px 0 0;
            }

            .alert {
                margin: 16px 0 0;
            }

            .max-reached {
                margin: 16px 0 0;
            }

            .danger-zone {
                margin: 16px 0 0;
            }

            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                gap: 14px;
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

            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 16px;
            }
        }

        @media (max-width: 480px) {
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
                gap: 8px;
            }

            .gallery-item .info {
                padding: 6px 8px;
                flex-wrap: wrap;
                gap: 2px;
            }

            .gallery-item .info .caption {
                font-size: 10px;
            }

            .gallery-item .info .order {
                font-size: 9px;
            }

            .gallery-item .actions {
                opacity: 1;
            }

            .gallery-item .actions a {
                width: 24px;
                height: 24px;
                font-size: 10px;
            }

            .gallery-item .drag-handle {
                opacity: 1;
            }

            .caption-group {
                grid-template-columns: 1fr;
            }

            .card-header {
                padding: 12px 14px;
                flex-direction: column;
                align-items: stretch;
            }

            .card-body {
                padding: 14px;
            }

            .upload-zone {
                padding: 24px 16px;
            }

            .upload-zone .icon {
                font-size: 28px;
            }

            .reorder-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .reorder-bar .btn {
                margin-left: 0;
                width: 100%;
                justify-content: center;
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

            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
                gap: 6px;
            }
        }

        /* ========================================
           UTILITY           ======================================== */
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
        <h1>Gallery</h1>
        <div class="header-stats">
            <span class="stat-count"><?php echo $gallery_count; ?></span>
            <span class="stat-dot"></span>
            <span>Max <?php echo $max_images; ?> images</span>
            <?php if ($gallery_count > 0): ?>
            <span class="stat-dot"></span>
            <span><?php echo round(($gallery_count / $max_images) * 100); ?>% used</span>
            <?php endif; ?>
        </div>
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
    UPLOAD SECTION
    ======================================== -->
    <?php if (!$max_reached): ?>
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-cloud-arrow-up"></i> Upload Images</h3>
            <span class="card-badge">JPG, PNG, GIF, WEBP • Max 20MB</span>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="upload-zone" id="uploadZone">
                    <div class="icon"><i class="fa-solid fa-image"></i></div>
                    <h4>Drop your images here</h4>
                    <p>or click to browse</p>
                    <p class="sub-text">You can select multiple images at once</p>
                    <input type="file" class="file-input" id="fileInput" name="gallery_images[]" accept="image/*" multiple onchange="updateFileList(this)">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('fileInput').click()" style="margin-top:12px;">
                        <i class="fa-solid fa-folder-open"></i> Choose Images
                    </button>
                    <span id="fileCount" style="display:none; font-size:12px; color:var(--gray-500); margin-left:8px;"></span>
                </div>
                
                <div class="file-list" id="fileList"></div>
                
                <div class="caption-group" id="captionGroup" style="display: none;">
                    <!-- Captions will be added dynamically by JavaScript -->
                </div>
                
                <button type="submit" name="upload_images" class="btn btn-primary" style="margin-top:16px; width:100%; justify-content:center;" id="uploadBtn">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Upload Selected Images
                </button>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="max-reached">
        <div class="icon"><i class="fa-solid fa-images"></i></div>
        <h4>Maximum Images Reached</h4>
        <p>You have uploaded the maximum of <?php echo $max_images; ?> images.</p>
        <p style="font-size:13px; margin-top:4px;">Please delete some images to upload more.</p>
    </div>
    <?php endif; ?>

    <!-- ========================================
    GALLERY GRID
    ======================================== -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-images"></i> Gallery</h3>
            <?php if ($gallery_count > 0): ?>
            <span class="card-badge">
                <i class="fa-solid fa-arrows-up-down"></i> Drag to reorder
            </span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (empty($gallery_images)): ?>
                <div class="empty-state">
                    <div class="icon"><i class="fa-solid fa-image"></i></div>
                    <h4>No Images Yet</h4>
                    <p>Upload your first image to get started</p>
                </div>
            <?php else: ?>
                <form method="POST" id="reorderForm">
                    <input type="hidden" name="reorder_gallery" value="1">
                    <input type="hidden" name="image_order" id="imageOrder" value="">
                    
                    <div class="reorder-bar">
                        <span class="hint"><i class="fa-solid fa-arrows-up-down"></i> Drag and drop images to reorder</span>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-floppy-disk"></i> Save Order
                        </button>
                    </div>

                    <div class="gallery-grid" id="galleryGrid">
                        <?php foreach ($gallery_images as $index => $img): ?>
                            <div class="gallery-item" data-id="<?php echo $img['id']; ?>" draggable="true">
                                <div class="drag-handle">
                                    <i class="fa-solid fa-grip-lines"></i>
                                </div>
                                <img src="<?php echo getMediaUrl($username, $img['image_path'], 'images'); ?>" 
                                     alt="<?php echo htmlspecialchars($img['caption'] ?? 'Gallery image'); ?>" loading="lazy">
                                <div class="info">
                                    <span class="caption"><?php echo htmlspecialchars($img['caption'] ?? 'Untitled'); ?></span>
                                    <span class="order">#<?php echo $index + 1; ?></span>
                                </div>
                                <div class="actions">
                                    <a href="?delete=<?php echo $img['id']; ?>" onclick="return confirm('Delete this image?')" title="Delete">
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========================================
    DANGER ZONE
    ======================================== -->
    <?php if ($gallery_count > 0): ?>
    <div class="danger-zone">
        
        <div class="danger-body">
            <div class="danger-item">
                <div class="item-top">
                    <span class="item-label">
                        <i class="fa-solid fa-images"></i>
                        Gallery Images
                    </span>
                    <span class="item-count"><?php echo $gallery_count; ?> images</span>
                </div>
                <p class="item-desc">Permanently delete all <?php echo $gallery_count; ?> gallery images from your invitation.</p>
                
                <form method="POST" onsubmit="return confirmClearGallery();">
                    <input type="hidden" name="clear_gallery" value="1">
                    <div class="confirm-row">
                        <input type="checkbox" id="confirmClear" name="confirm_clear" value="yes" required>
                        <label for="confirmClear">I understand, delete all <?php echo $gallery_count; ?> images</label>
                    </div>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-trash-can"></i> Clear All Images
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
    // SIDEBAR TOGGLE
    // ========================================
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (menuToggle && sidebar && overlay) {
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
    }

    // ========================================
    // FILE LIST & CAPTIONS
    // ========================================
    function updateFileList(input) {
        const fileList = document.getElementById('fileList');
        const captionGroup = document.getElementById('captionGroup');
        const fileCount = document.getElementById('fileCount');
        const files = input.files;
        
        fileList.innerHTML = '';
        
        if (files.length === 0) {
            captionGroup.style.display = 'none';
            fileCount.style.display = 'none';
            return;
        }
        
        // Show file names
        for (let i = 0; i < files.length; i++) {
            const tag = document.createElement('span');
            tag.className = 'file-tag';
            const sizeMB = (files[i].size / 1024 / 1024).toFixed(1);
            const fileName = files[i].name.length > 20 ? files[i].name.substring(0, 20) + '...' : files[i].name;
            tag.innerHTML = `<i class="fa-solid fa-file-image"></i> ${fileName} (${sizeMB}MB)`;
            fileList.appendChild(tag);
        }
        
        // Show file count
        fileCount.textContent = `${files.length} file(s) selected`;
        fileCount.style.display = 'inline';
        
        // Generate caption inputs
        captionGroup.innerHTML = '';
        for (let i = 0; i < files.length; i++) {
            const div = document.createElement('div');
            div.className = 'form-group';
            const fileName = files[i].name.length > 25 ? files[i].name.substring(0, 25) + '...' : files[i].name;
            div.innerHTML = `
                <label for="caption_${i}">Caption for ${fileName}</label>
                <input type="text" id="caption_${i}" name="caption_${i}" placeholder="Enter caption (optional)">
            `;
            captionGroup.appendChild(div);
        }
        captionGroup.style.display = 'grid';
    }

    // ========================================
    // DRAG AND DROP FOR UPLOAD ZONE
    // ========================================
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('fileInput');

    if (uploadZone && fileInput) {
        uploadZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            if (e.dataTransfer.files && e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                updateFileList(fileInput);
            }
        });

        uploadZone.addEventListener('click', function(e) {
            if (e.target === this || e.target.closest('.upload-zone')) {
                fileInput.click();
            }
        });
    }

    // ========================================
    // CONFIRM CLEAR GALLERY
    // ========================================
    function confirmClearGallery() {
        const checkbox = document.getElementById('confirmClear');
        if (!checkbox.checked) {
            alert('Please check the confirmation box to proceed.');
            return false;
        }
        return confirm('⚠️ WARNING: This will permanently delete ALL gallery images!\n\nAre you sure you want to continue?');
    }

    // ========================================
    // GALLERY DRAG AND DROP REORDER (Desktop + Mobile)
    // ========================================
    let dragItem = null;
    let touchDragItem = null;
    let isDragging = false;
    let touchStartX = 0;
    let touchStartY = 0;

    const grid = document.getElementById('galleryGrid');

    if (grid) {
        // Desktop drag events
        grid.addEventListener('dragstart', function(e) {
            const item = e.target.closest('.gallery-item');
            if (!item) return;
            dragItem = item;
            item.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', item.dataset.id);
        });

        grid.addEventListener('dragend', function(e) {
            const item = e.target.closest('.gallery-item');
            if (item) item.classList.remove('dragging');
            grid.querySelectorAll('.gallery-item').forEach(el => el.classList.remove('drag-over'));
        });

        grid.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            const target = e.target.closest('.gallery-item');
            if (!target || target === dragItem) return;
            grid.querySelectorAll('.gallery-item').forEach(el => el.classList.remove('drag-over'));
            target.classList.add('drag-over');
        });

        grid.addEventListener('dragleave', function(e) {
            const target = e.target.closest('.gallery-item');
            if (target) target.classList.remove('drag-over');
        });

        grid.addEventListener('drop', function(e) {
            e.preventDefault();
            const target = e.target.closest('.gallery-item');
            if (!target || target === dragItem) {
                grid.querySelectorAll('.gallery-item').forEach(el => el.classList.remove('drag-over'));
                return;
            }

            const items = Array.from(grid.querySelectorAll('.gallery-item'));
            const dragIndex = items.indexOf(dragItem);
            const targetIndex = items.indexOf(target);

            if (dragIndex < targetIndex) {
                target.parentNode.insertBefore(dragItem, target.nextSibling);
            } else {
                target.parentNode.insertBefore(dragItem, target);
            }

            updateOrderNumbers();
            grid.querySelectorAll('.gallery-item').forEach(el => el.classList.remove('drag-over'));
            dragItem = null;
        });

        // Mobile touch events
        const items = grid.querySelectorAll('.gallery-item');
        
        items.forEach(function(item) {
            item.addEventListener('touchstart', function(e) {
                const target = e.target.closest('.drag-handle') || e.target.closest('.gallery-item');
                if (!target) return;
                
                const touch = e.touches[0];
                touchStartX = touch.clientX;
                touchStartY = touch.clientY;
                touchDragItem = item;
                isDragging = false;
            }, { passive: true });

            item.addEventListener('touchmove', function(e) {
                if (!touchDragItem) return;
                
                const touch = e.touches[0];
                const deltaX = touch.clientX - touchStartX;
                const deltaY = touch.clientY - touchStartY;
                
                if (!isDragging && (Math.abs(deltaX) > 10 || Math.abs(deltaY) > 10)) {
                    isDragging = true;
                    touchDragItem.classList.add('dragging');
                }
                
                if (isDragging) {
                    e.preventDefault();
                    const elementAtPoint = document.elementFromPoint(touch.clientX, touch.clientY);
                    if (elementAtPoint) {
                        const target = elementAtPoint.closest('.gallery-item');
                        if (target && target !== touchDragItem) {
                            grid.querySelectorAll('.gallery-item').forEach(el => el.classList.remove('drag-over'));
                            target.classList.add('drag-over');
                        } else {
                            grid.querySelectorAll('.gallery-item').forEach(el => el.classList.remove('drag-over'));
                        }
                    }
                }
            }, { passive: false });

            item.addEventListener('touchend', function(e) {
                if (!touchDragItem) return;
                
                touchDragItem.classList.remove('dragging');
                
                if (isDragging) {
                    const touch = e.changedTouches[0];
                    const elementAtPoint = document.elementFromPoint(touch.clientX, touch.clientY);
                    
                    if (elementAtPoint) {
                        const target = elementAtPoint.closest('.gallery-item');
                        if (target && target !== touchDragItem) {
                            const items = Array.from(grid.querySelectorAll('.gallery-item'));
                            const dragIndex = items.indexOf(touchDragItem);
                            const targetIndex = items.indexOf(target);
                            
                            if (dragIndex < targetIndex) {
                                target.parentNode.insertBefore(touchDragItem, target.nextSibling);
                            } else {
                                target.parentNode.insertBefore(touchDragItem, target);
                            }
                            
                            updateOrderNumbers();
                        }
                    }
                }
                
                grid.querySelectorAll('.gallery-item').forEach(el => el.classList.remove('drag-over'));
                touchDragItem = null;
                isDragging = false;
            }, { passive: true });
        });
    }

    function updateOrderNumbers() {
        const items = document.querySelectorAll('.gallery-item');
        items.forEach((item, index) => {
            const orderSpan = item.querySelector('.order');
            if (orderSpan) {
                orderSpan.textContent = '#' + (index + 1);
            }
        });
    }

    // Save order on form submit
    document.getElementById('reorderForm')?.addEventListener('submit', function(e) {
        const items = document.querySelectorAll('.gallery-item');
        const order = [];
        items.forEach(item => order.push(item.dataset.id));
        document.getElementById('imageOrder').value = order.join(',');
    });

    // ========================================
    // HAPTIC FEEDBACK
    // ========================================
    document.querySelectorAll('.btn, .gallery-item, .upload-zone, .danger-item').forEach(el => {
        el.addEventListener('touchstart', function() {
            if (navigator.vibrate) navigator.vibrate(6);
        }, { passive: true });
    });
</script>
</body>
</html>