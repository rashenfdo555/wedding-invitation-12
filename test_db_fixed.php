<?php
// test_db_fixed.php - Test the fixed database functions
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/db.php';

echo "<h1>Testing Fixed Database Functions</h1>";

// Test 1: Check database connection
echo "<h2>Test 1: Database Connection</h2>";
try {
    $conn = getDBConnection();
    echo "✅ Database connected successfully!<br>";
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Create a test invitation
echo "<h2>Test 2: Create Test Invitation</h2>";

$test_data = [
    'slug' => 'test-' . time(),
    'couple_names' => 'Test & Couple',
    'bride_name' => 'Test Bride',
    'groom_name' => 'Test Groom',
    'couple_headline' => 'Test & Couple',
    'wedding_date' => 'DECEMBER 25 2026',
    'month' => 'DECEMBER',
    'day' => '25',
    'year' => '2026',
    'countdown_target' => '2026-12-25 17:00:00',
    'sub_headline' => 'Test wedding invitation created at ' . date('Y-m-d H:i:s'),
    'venue_title' => 'Test Venue',
    'venue_location' => 'Test Location',
    'venue_address' => '123 Test Street',
    'google_maps_url' => 'https://maps.google.com',
    'event_time' => '5:00 PM To 11:00 PM',
    'whatsapp_phone' => '1234567890',
    'primary_color' => '#bc9c6c',
    'secondary_color' => '#f4efe6',
    'text_color' => '#443838',
    'hero_image' => 'hero-couple.jpg',
    'bride_image' => 'bride.jpg',
    'groom_image' => 'groom.jpg',
    'intro_video' => 'intro-open.mp4',
    'intro_audio' => 'intro-music1.mp3',
    'bg_audio' => 'intro-music.mp3'
];

echo "<pre>";
echo "Data being sent:\n";
print_r($test_data);
echo "</pre>";

$result = createInvitation($test_data);

if ($result) {
    echo "<h3 style='color:green'>✅ Success! Invitation created with ID: " . $result . "</h3>";
    
    // Test 3: Retrieve the invitation
    echo "<h2>Test 3: Retrieve Invitation</h2>";
    $inv = getInvitationById($result);
    if ($inv) {
        echo "<pre>";
        echo "Retrieved invitation:\n";
        print_r($inv);
        echo "</pre>";
        echo "<h3 style='color:green'>✅ Invitation retrieved successfully!</h3>";
    }
    
    // Test 4: View the invitation
    echo "<h2>Test 4: View Invitation</h2>";
    echo "<a href='intro/index.php?slug=" . $test_data['slug'] . "' target='_blank'>View Invitation</a><br>";
    echo "<a href='admin/index.php'>Go to Admin Dashboard</a>";
    
} else {
    echo "<h3 style='color:red'>❌ Failed to create invitation</h3>";
    echo "Check the database connection and table structure.";
}

// Show all invitations
echo "<h2>All Invitations</h2>";
$invitations = getAllInvitations();
if (count($invitations) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Slug</th><th>Couple Names</th><th>Wedding Date</th><th>Created</th></tr>";
    foreach ($invitations as $inv) {
        echo "<tr>";
        echo "<td>" . $inv['id'] . "</td>";
        echo "<td>" . htmlspecialchars($inv['slug']) . "</td>";
        echo "<td>" . htmlspecialchars($inv['couple_names']) . "</td>";
        echo "<td>" . htmlspecialchars($inv['wedding_date']) . "</td>";
        echo "<td>" . htmlspecialchars($inv['created_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No invitations found.";
}
?>