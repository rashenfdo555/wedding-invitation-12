<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

$id = $_GET['id'] ?? 0;
if ($id) {
    deleteInvitation($id);
}
header('Location: index.php');
exit;
?>