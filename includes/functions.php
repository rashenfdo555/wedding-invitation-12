<?php
// includes/functions.php - UPDATED VERSION

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Redirect if not logged in
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Check if user admin is logged in and account is active
function requireUserAdmin() {
    if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
    
    // Check account status from database
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT account_status, is_active FROM user_admins WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
    if ($user['account_status'] === 'suspended') {
        session_destroy();
        header('Location: login.php?error=suspended');
        exit;
    }
    
    if ($user['account_status'] === 'pending') {
        session_destroy();
        header('Location: login.php?error=pending');
        exit;
    }
    
    if ($user['is_active'] != 1) {
        session_destroy();
        header('Location: login.php?error=inactive');
        exit;
    }
}

// Get invitation URL
function getInvitationUrl($slug) {
    return '/invitation/index.php?slug=' . $slug;
}

// Get user's theme file
function getUserThemeFile($user_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT t.file_name FROM user_admins u JOIN themes t ON u.theme_id = t.id WHERE u.id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? $row['file_name'] : 'index.php';
}
?>