<?php
require_once 'includes/db.php';

// Redirect to first invitation or admin
$conn = getDBConnection();
$result = $conn->query("SELECT slug FROM invitations LIMIT 1");
$row = $result->fetch_assoc();

if ($row) {
    header('Location: intro/index.php?slug=' . $row['slug']);
} else {
    header('Location: admin/login.php');
}
exit;
?>