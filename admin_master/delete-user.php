<?php
// admin_master/delete-user.php - UPDATED with folder deletion

session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['master_logged_in']) || $_SESSION['master_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;

if ($id) {
    // Get user info before deleting
    $user = getUserAdminById($id);
    $username = $user['username'] ?? '';
    
    // Delete user (this will cascade delete invitations, gallery, table_finder, schedule_items)
    deleteUserAdmin($id);
    
    // Delete user's upload folders
    if (!empty($username)) {
        $base_path = '../uploads/';
        $folders = ['images', 'audio', 'video'];
        
        foreach ($folders as $folder) {
            $user_folder = $base_path . $folder . '/' . $username;
            if (is_dir($user_folder)) {
                // Delete all files in the folder
                $files = glob($user_folder . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                // Remove the folder
                rmdir($user_folder);
            }
        }
    }
}

header('Location: index.php');
exit;
?>