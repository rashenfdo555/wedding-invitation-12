<?php
// reset_password.php
require_once 'includes/db.php';

// New password
$new_password = 'admin123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$conn = getDBConnection();

// Update admin password
$sql = "UPDATE admin_users SET password_hash = ? WHERE username = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hashed_password);

if ($stmt->execute()) {
    echo "✅ Password updated successfully!<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "<a href='admin/login.php'>Click here to login</a>";
} else {
    echo "❌ Error updating password: " . $conn->error;
}

$stmt->close();
$conn->close();
?>