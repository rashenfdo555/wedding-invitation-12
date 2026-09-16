<?php
// invitation/index.php - UPDATED ROUTER
// This file loads the correct theme based on the user's assigned theme

require_once '../includes/db.php';
require_once '../includes/functions.php';

// Get slug from URL
$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    // Default to first invitation
    $conn = getDBConnection();
    $result = $conn->query("SELECT slug FROM invitations LIMIT 1");
    $row = $result->fetch_assoc();
    if ($row) {
        $slug = $row['slug'];
    } else {
        die('No invitation found. Please create one in the admin panel.');
    }
}

// Get invitation with theme information
$invitation = getInvitationBySlug($slug);
if (!$invitation) {
    die('Invitation not found.');
}

// Get the theme file name
$theme_file = $invitation['theme_file'] ?? 'index.php';

// Check if theme file exists
$theme_path = __DIR__ . '/' . $theme_file;
if (!file_exists($theme_path)) {
    die('Theme file "' . $theme_file . '" not found. Please check theme configuration.');
}

// Get user data for status check
$user = getUserAdminById($invitation['user_admin_id']);

// Check if user account is active
if (!$user || $user['account_status'] !== 'active' || $user['is_active'] != 1) {
    // Account is not active - show a friendly message
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Invitation Unavailable</title>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Montserrat', sans-serif;
                background: linear-gradient(135deg, #f5f0ea, #e8e0d6);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .message-container {
                background: #fff;
                border-radius: 20px;
                padding: 60px 40px;
                max-width: 500px;
                width: 100%;
                text-align: center;
                box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            }
            .message-container .icon {
                font-size: 64px;
                color: #bc9c6c;
                margin-bottom: 20px;
            }
            .message-container h1 {
                font-family: 'Playfair Display', serif;
                font-size: 28px;
                color: #1a1a2e;
                margin-bottom: 12px;
            }
            .message-container p {
                color: #666;
                font-size: 16px;
                line-height: 1.6;
                margin-bottom: 8px;
            }
            .message-container .sub-message {
                color: #999;
                font-size: 14px;
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid #e5e0d8;
            }
            .message-container .status-badge {
                display: inline-block;
                padding: 6px 20px;
                border-radius: 20px;
                font-size: 14px;
                font-weight: 600;
                margin-top: 16px;
            }
            .status-suspended {
                background: #f8d7da;
                color: #721c24;
            }
            .status-pending {
                background: #fff3cd;
                color: #856404;
            }
            .status-inactive {
                background: #e2e3e5;
                color: #383d41;
            }
        </style>
    </head>
    <body>
        <div class="message-container">
            <div class="icon">
                <i class="fa-regular fa-envelope"></i>
            </div>
            <h1>Invitation Unavailable</h1>
            <p>This wedding invitation is currently not available.</p>
            
            <?php if ($user): ?>
                <?php if ($user['account_status'] === 'suspended'): ?>
                    <div class="status-badge status-suspended">
                        <i class="fa-regular fa-ban"></i> Account Suspended
                    </div>
                    <p style="margin-top: 16px;">The couple's account has been suspended. Please contact them directly.</p>
                <?php elseif ($user['account_status'] === 'pending'): ?>
                    <div class="status-badge status-pending">
                        <i class="fa-regular fa-clock"></i> Account Pending
                    </div>
                    <p style="margin-top: 16px;">This invitation is not yet active. Please check back later.</p>
                <?php elseif ($user['is_active'] != 1): ?>
                    <div class="status-badge status-inactive">
                        <i class="fa-regular fa-circle"></i> Account Inactive
                    </div>
                    <p style="margin-top: 16px;">This invitation is currently inactive.</p>
                <?php endif; ?>
            <?php else: ?>
                <div class="status-badge status-inactive">
                    <i class="fa-regular fa-circle"></i> Account Not Found
                </div>
            <?php endif; ?>
            
            <div class="sub-message">
                <i class="fa-regular fa-heart" style="color: #bc9c6c;"></i>
                <p>If you have questions, please contact the couple directly.</p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// If we get here, the user is active - include the theme file
// The theme file will have access to all the invitation data
// Make invitation data available to the theme file
global $invitation_data;
$invitation_data = $invitation;

// Include the theme file
include $theme_path;
?>