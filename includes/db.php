<?php
// includes/db.php - COMPLETE UPDATED VERSION FOR wedding_invitations04

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'wedding_invitations04');

// Create connection
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    } catch (Exception $e) {
        die("Database connection error: " . $e->getMessage());
    }
}

// =============================================
// MASTER ADMIN FUNCTIONS
// =============================================

// Get master admin by username
function getMasterAdminByUsername($username) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM master_admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// =============================================
// THEME FUNCTIONS
// =============================================

// Get all themes
function getAllThemes() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT * FROM themes ORDER BY name ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get active themes
function getActiveThemes() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT * FROM themes WHERE is_active = 1 ORDER BY name ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get theme by ID
function getThemeById($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM themes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Create theme
function createTheme($data) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO themes (name, file_name, description, preview_image, is_active) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", 
        $data['name'],
        $data['file_name'],
        $data['description'],
        $data['preview_image'],
        $data['is_active']
    );
    
    if ($stmt->execute()) {
        $theme_id = $conn->insert_id;
        // Create default colors for this theme
        createDefaultColorsForTheme($theme_id);
        return $theme_id;
    }
    return false;
}

// Update theme
function updateTheme($id, $data) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE themes SET name = ?, file_name = ?, description = ?, preview_image = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("ssssii", 
        $data['name'],
        $data['file_name'],
        $data['description'],
        $data['preview_image'],
        $data['is_active'],
        $id
    );
    return $stmt->execute();
}

// Delete theme
function deleteTheme($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM themes WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// =============================================
// DEFAULT COLORS FUNCTIONS
// =============================================

// Create default colors for a theme
function createDefaultColorsForTheme($theme_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO default_colors (theme_id) VALUES (?)");
    $stmt->bind_param("i", $theme_id);
    return $stmt->execute();
}

// Get default colors for a theme
function getDefaultColorsByTheme($theme_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM default_colors WHERE theme_id = ?");
    $stmt->bind_param("i", $theme_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Update default colors for a theme
function updateDefaultColors($theme_id, $colors) {
    $conn = getDBConnection();
    $sql = "UPDATE default_colors SET 
        primary_color = ?,
        secondary_color = ?,
        text_color = ?,
        bg_main_color = ?,
        bg_card_color = ?,
        text_muted_color = ?,
        text_light_gray = ?,
        border_light_color = ?,
        map_bg_color = ?
        WHERE theme_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssssi",
        $colors['primary_color'],
        $colors['secondary_color'],
        $colors['text_color'],
        $colors['bg_main_color'],
        $colors['bg_card_color'],
        $colors['text_muted_color'],
        $colors['text_light_gray'],
        $colors['border_light_color'],
        $colors['map_bg_color'],
        $theme_id
    );
    return $stmt->execute();
}

// =============================================
// USER ADMIN FUNCTIONS
// =============================================

// Get all user admins
function getAllUserAdmins() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT u.*, i.slug, i.is_active as invitation_active, i.id as invitation_id,
                                   t.name as theme_name, t.file_name as theme_file
                            FROM user_admins u 
                            LEFT JOIN invitations i ON u.id = i.user_admin_id 
                            LEFT JOIN themes t ON u.theme_id = t.id
                            ORDER BY u.created_at DESC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get user admin by ID
function getUserAdminById($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM user_admins WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Get user admin by username
function getUserAdminByUsername($username) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM user_admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Create user admin - FIXED with default 'active'
function createUserAdmin($data) {
    $conn = getDBConnection();
    
    $sql = "INSERT INTO user_admins (username, password_hash, email, full_name, couple_name, wedding_date, theme_id, account_status, is_active, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active', 1, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssii", 
        $data['username'],
        $data['password_hash'],
        $data['email'],
        $data['full_name'],
        $data['couple_name'],
        $data['wedding_date'],
        $data['theme_id'],
        $data['created_by']
    );
    
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}




// Update user admin - COMPLETELY REWRITTEN
function updateUserAdmin($id, $data) {
    $conn = getDBConnection();
    
    // Force account_status to always have a valid value
    $account_status = 'active'; // Default
    
    // Check if account_status exists and is not empty
    if (isset($data['account_status']) && $data['account_status'] !== '' && $data['account_status'] !== null) {
        $account_status = $data['account_status'];
    }
    
    // Validate - only allow valid ENUM values
    $valid_statuses = ['active', 'suspended', 'pending'];
    if (!in_array($account_status, $valid_statuses)) {
        $account_status = 'active';
    }
    
    // Get other values with defaults
    $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;
    $theme_id = isset($data['theme_id']) ? (int)$data['theme_id'] : 1;
    
    // ============================================================
    // USE A SIMPLE UPDATE WITH CONCATENATED SQL
    // This bypasses bind_param issues entirely
    // ============================================================
    
    // Escape all values to prevent SQL injection
    $email = $conn->real_escape_string($data['email'] ?? '');
    $full_name = $conn->real_escape_string($data['full_name'] ?? '');
    $couple_name = $conn->real_escape_string($data['couple_name'] ?? '');
    $wedding_date = $data['wedding_date'] ?? null;
    
    // Build the SQL query directly
    $sql = "UPDATE user_admins SET 
            email = '$email',
            full_name = '$full_name',
            couple_name = '$couple_name',
            wedding_date = " . ($wedding_date ? "'$wedding_date'" : "NULL") . ",
            is_active = $is_active,
            account_status = '$account_status',
            theme_id = $theme_id
            WHERE id = $id";
    
    // Execute directly
    if ($conn->query($sql)) {
        error_log("User $id updated successfully. account_status = $account_status");
        return true;
    } else {
        error_log("Update failed: " . $conn->error);
        return false;
    }
}




// Change user admin password
function changeUserAdminPassword($id, $new_password_hash) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE user_admins SET password_hash = ? WHERE id = ?");
    $stmt->bind_param("si", $new_password_hash, $id);
    return $stmt->execute();
}

// Delete user admin
function deleteUserAdmin($id) {
    $conn = getDBConnection();
    
    // First, get the invitation_id to delete related data
    $stmt = $conn->prepare("SELECT id FROM invitations WHERE user_admin_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $invitation = $result->fetch_assoc();
    
    if ($invitation) {
        // Delete gallery images
        $stmt = $conn->prepare("DELETE FROM gallery_images WHERE invitation_id = ?");
        $stmt->bind_param("i", $invitation['id']);
        $stmt->execute();
        
        // Delete table finder
        $stmt = $conn->prepare("DELETE FROM table_finder WHERE invitation_id = ?");
        $stmt->bind_param("i", $invitation['id']);
        $stmt->execute();
        
        // Delete schedule items
        $stmt = $conn->prepare("DELETE FROM schedule_items WHERE invitation_id = ?");
        $stmt->bind_param("i", $invitation['id']);
        $stmt->execute();
        
        // Delete invitation
        $stmt = $conn->prepare("DELETE FROM invitations WHERE id = ?");
        $stmt->bind_param("i", $invitation['id']);
        $stmt->execute();
    }
    
    // Finally delete the user
    $stmt = $conn->prepare("DELETE FROM user_admins WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// =============================================
// INVITATION FUNCTIONS
// =============================================

// Get invitation by user admin ID
function getInvitationByUserId($user_admin_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM invitations WHERE user_admin_id = ?");
    $stmt->bind_param("i", $user_admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Get invitation by slug
function getInvitationBySlug($slug) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT i.*, u.username, u.theme_id, t.file_name as theme_file 
                            FROM invitations i 
                            JOIN user_admins u ON i.user_admin_id = u.id 
                            JOIN themes t ON u.theme_id = t.id
                            WHERE i.slug = ? AND i.is_active = 1");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Get all invitations (for master admin)
function getAllInvitations() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT i.*, u.username, u.full_name as user_name, t.name as theme_name
                            FROM invitations i 
                            JOIN user_admins u ON i.user_admin_id = u.id 
                            LEFT JOIN themes t ON u.theme_id = t.id
                            ORDER BY i.created_at DESC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Create invitation
// Create invitation - COMPLETE FIXED VERSION
function createInvitation($data) {
    $conn = getDBConnection();
    
    $sql = "INSERT INTO invitations (
        user_admin_id, theme_id, slug, couple_names, bride_name, groom_name, couple_headline,
        wedding_date, month, day, year, countdown_target,
        sub_headline, venue_title, venue_location, venue_address,
        google_maps_url, event_time, whatsapp_phone,
        primary_color, secondary_color, text_color,
        bg_main_color, bg_card_color, text_muted_color, text_light_gray, border_light_color, map_bg_color,
        hero_image, bride_image, groom_image, intro_video, intro_audio, bg_audio,
        show_profiles
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    // Get default colors for the theme
    $default_colors = getDefaultColorsByTheme($data['theme_id']);
    
    // 35 parameters: 2 ints (user_admin_id, theme_id) + 32 strings + 1 int (show_profiles)
    $types = 'ii' . str_repeat('s', 32) . 'i';
    
    $params = [
        (int)$data['user_admin_id'],
        (int)$data['theme_id'],
        (string)$data['slug'],
        (string)$data['couple_names'],
        (string)$data['bride_name'],
        (string)$data['groom_name'],
        (string)$data['couple_headline'],
        (string)$data['wedding_date'],
        (string)$data['month'],
        (string)$data['day'],
        (string)$data['year'],
        (string)$data['countdown_target'],
        (string)$data['sub_headline'],
        (string)$data['venue_title'],
        (string)$data['venue_location'],
        (string)$data['venue_address'],
        (string)$data['google_maps_url'],
        (string)$data['event_time'],
        (string)$data['whatsapp_phone'],
        (string)($default_colors['primary_color'] ?? '#bc9c6c'),
        (string)($default_colors['secondary_color'] ?? '#f4efe6'),
        (string)($default_colors['text_color'] ?? '#443838'),
        (string)($default_colors['bg_main_color'] ?? '#fbf9f6'),
        (string)($default_colors['bg_card_color'] ?? '#ffffff'),
        (string)($default_colors['text_muted_color'] ?? '#6e6e6e'),
        (string)($default_colors['text_light_gray'] ?? '#9e9e9e'),
        (string)($default_colors['border_light_color'] ?? '#eae5dd'),
        (string)($default_colors['map_bg_color'] ?? '#e5e5e3'),
        (string)$data['hero_image'],
        (string)$data['bride_image'],
        (string)$data['groom_image'],
        (string)$data['intro_video'],
        (string)$data['intro_audio'],
        (string)$data['bg_audio'],
        (int)($data['show_profiles'] ?? 1)
    ];
    
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}





// Update invitation
function updateInvitation($id, $data) {
    $conn = getDBConnection();
    $sql = "UPDATE invitations SET 
        couple_names = ?, bride_name = ?, groom_name = ?, couple_headline = ?,
        wedding_date = ?, month = ?, day = ?, year = ?, countdown_target = ?,
        sub_headline = ?, venue_title = ?, venue_location = ?, venue_address = ?,
        google_maps_url = ?, event_time = ?, whatsapp_phone = ?,
        primary_color = ?, secondary_color = ?, text_color = ?,
        bg_main_color = ?, bg_card_color = ?, text_muted_color = ?, 
        text_light_gray = ?, border_light_color = ?, map_bg_color = ?,
        hero_image = ?, bride_image = ?, groom_image = ?,
        intro_video = ?, intro_audio = ?, bg_audio = ?,
        show_profiles = ?
        WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssssssssssssssssssssssssssii",
        $data['couple_names'],
        $data['bride_name'],
        $data['groom_name'],
        $data['couple_headline'],
        $data['wedding_date'],
        $data['month'],
        $data['day'],
        $data['year'],
        $data['countdown_target'],
        $data['sub_headline'],
        $data['venue_title'],
        $data['venue_location'],
        $data['venue_address'],
        $data['google_maps_url'],
        $data['event_time'],
        $data['whatsapp_phone'],
        $data['primary_color'],
        $data['secondary_color'],
        $data['text_color'],
        $data['bg_main_color'],
        $data['bg_card_color'],
        $data['text_muted_color'],
        $data['text_light_gray'],
        $data['border_light_color'],
        $data['map_bg_color'],
        $data['hero_image'],
        $data['bride_image'],
        $data['groom_image'],
        $data['intro_video'],
        $data['intro_audio'],
        $data['bg_audio'],
        $data['show_profiles'],
        $id
    );
    
    return $stmt->execute();
}

// Toggle invitation active status
function toggleInvitationStatus($user_admin_id, $is_active) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE invitations SET is_active = ? WHERE user_admin_id = ?");
    $stmt->bind_param("ii", $is_active, $user_admin_id);
    return $stmt->execute();
}

// Reset invitation colors to theme defaults
function resetInvitationColorsToTheme($invitation_id, $theme_id) {
    $conn = getDBConnection();
    $defaults = getDefaultColorsByTheme($theme_id);
    
    $sql = "UPDATE invitations SET 
        primary_color = ?,
        secondary_color = ?,
        text_color = ?,
        bg_main_color = ?,
        bg_card_color = ?,
        text_muted_color = ?,
        text_light_gray = ?,
        border_light_color = ?,
        map_bg_color = ?
        WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssssi",
        $defaults['primary_color'],
        $defaults['secondary_color'],
        $defaults['text_color'],
        $defaults['bg_main_color'],
        $defaults['bg_card_color'],
        $defaults['text_muted_color'],
        $defaults['text_light_gray'],
        $defaults['border_light_color'],
        $defaults['map_bg_color'],
        $invitation_id
    );
    return $stmt->execute();
}

// =============================================
// SCHEDULE FUNCTIONS
// =============================================

// Get schedule items for invitation
function getScheduleItems($invitation_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM schedule_items WHERE invitation_id = ? ORDER BY sort_order");
    $stmt->bind_param("i", $invitation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Add schedule item
function addScheduleItem($invitation_id, $time, $title, $description, $icon, $sort_order) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO schedule_items (invitation_id, time, title, description, icon, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssi", $invitation_id, $time, $title, $description, $icon, $sort_order);
    return $stmt->execute();
}

// Clear schedule items
function clearScheduleItems($invitation_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM schedule_items WHERE invitation_id = ?");
    $stmt->bind_param("i", $invitation_id);
    return $stmt->execute();
}

// =============================================
// GALLERY FUNCTIONS
// =============================================

// Get gallery images for invitation - SORTED BY ORDER
function getGalleryImages($invitation_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM gallery_images WHERE invitation_id = ? ORDER BY sort_order ASC, id ASC");
    $stmt->bind_param("i", $invitation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Add gallery image
function addGalleryImage($invitation_id, $image_path, $caption, $sort_order) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO gallery_images (invitation_id, image_path, caption, sort_order) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $invitation_id, $image_path, $caption, $sort_order);
    return $stmt->execute();
}

// Delete gallery image
function deleteGalleryImage($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM gallery_images WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Clear all gallery images for an invitation
function clearGalleryImages($invitation_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM gallery_images WHERE invitation_id = ?");
    $stmt->bind_param("i", $invitation_id);
    return $stmt->execute();
}

// Get count of gallery images
function countGalleryImages($invitation_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM gallery_images WHERE invitation_id = ?");
    $stmt->bind_param("i", $invitation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] ?? 0;
}

// =============================================
// TABLE FINDER FUNCTIONS
// =============================================

// Get table finder entries for invitation
function getTableFinderEntries($invitation_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM table_finder WHERE invitation_id = ? ORDER BY guest_name");
    $stmt->bind_param("i", $invitation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Add table finder entry
function addTableFinderEntry($invitation_id, $guest_name, $table_number, $family_name) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO table_finder (invitation_id, guest_name, table_number, family_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $invitation_id, $guest_name, $table_number, $family_name);
    return $stmt->execute();
}

// Delete table finder entry
function deleteTableFinderEntry($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM table_finder WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Search table finder by guest name
function searchTableFinder($invitation_id, $guest_name) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM table_finder WHERE invitation_id = ? AND guest_name LIKE ?");
    $search_term = "%" . $guest_name . "%";
    $stmt->bind_param("is", $invitation_id, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Clear all table finder entries for an invitation
function clearTableFinderEntries($invitation_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM table_finder WHERE invitation_id = ?");
    $stmt->bind_param("i", $invitation_id);
    return $stmt->execute();
}

// Get count of table finder entries
function countTableFinderEntries($invitation_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM table_finder WHERE invitation_id = ?");
    $stmt->bind_param("i", $invitation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] ?? 0;
}

// =============================================
// FEATURE FLAG FUNCTIONS
// =============================================

// Update user feature flags
function updateUserFeatures($user_id, $has_gallery, $has_table_finder) {
    $conn = getDBConnection();
    $sql = "UPDATE user_admins SET has_gallery = ?, has_table_finder = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $has_gallery, $has_table_finder, $user_id);
    return $stmt->execute();
}

// Get user feature flags
function getUserFeatures($user_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT has_gallery, has_table_finder FROM user_admins WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// =============================================
// UTILITY FUNCTIONS
// =============================================

// Generate unique slug
function generateSlug($name) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    
    if (empty($slug)) {
        $slug = 'invitation';
    }
    
    $conn = getDBConnection();
    $original = $slug;
    $counter = 1;
    while (true) {
        $stmt = $conn->prepare("SELECT id FROM invitations WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            break;
        }
        $slug = $original . '-' . $counter;
        $counter++;
    }
    return $slug;
}

// Get all schedule icons
function getScheduleIcons() {
    return [
        'fa-location-dot' => 'Location',
        'fa-heart' => 'Heart',
        'fa-microphone' => 'Microphone',
        'fa-utensils' => 'Food',
        'fa-music' => 'Music',
        'fa-wand-magic-sparkles' => 'Magic',
        'fa-moon' => 'Moon',
        'fa-star' => 'Star',
        'fa-cake' => 'Cake',
        'fa-champagne-glasses' => 'Champagne',
        'fa-gift' => 'Gift',
        'fa-camera' => 'Camera',
        'fa-ring' => 'Ring',
        'fa-hands' => 'Hands'
    ];
}

// =============================================
// UPLOAD FUNCTIONS (NO RESIZE)
// =============================================

// Upload file with username folder support
// Upload file with username folder support (already in db.php)
function uploadFile($file, $target_dir, $username, $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mp3', 'webm', 'mov', 'avi']) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_types)) {
        return false;
    }
    
    // Create username-specific directory
    $user_dir = $target_dir . $username . '/';
    if (!file_exists($user_dir)) {
        mkdir($user_dir, 0777, true);
    }
    
    $new_filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($file['name']));
    $target_path = $user_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // Return path relative to uploads folder: username/filename
        return $username . '/' . $new_filename;
    }
    return false;
}

// Get user's media path
function getUserMediaPath($username, $type = 'images') {
    return '../uploads/' . $type . '/' . $username . '/';
}

// Get full media URL for display
function getMediaUrl($username, $filename, $type = 'images') {
    if (empty($filename)) {
        return '';
    }
    // If filename already contains username path, return as-is
    if (strpos($filename, '/') !== false) {
        return '../uploads/' . $type . '/' . $filename;
    }
    return '../uploads/' . $type . '/' . $username . '/' . $filename;
}

?>