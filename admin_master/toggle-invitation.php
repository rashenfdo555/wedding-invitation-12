<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['master_logged_in']) || $_SESSION['master_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$user_id = $_GET['user_id'] ?? 0;
$status = $_GET['status'] ?? 0;

if ($user_id) {
    toggleInvitationStatus($user_id, $status);
}
header('Location: index.php');
exit;