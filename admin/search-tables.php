<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$invitation_id = (int)$_GET['invitation_id'] ?? 0;
$term = trim($_GET['term'] ?? '');

if (!$invitation_id || !$term) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

// Check if invitation exists and is active
$invitation = getInvitationById($invitation_id);
if (!$invitation || !$invitation['is_active']) {
    echo json_encode(['error' => 'Invitation not found or inactive']);
    exit;
}

$results = findGuestTable($invitation_id, $term);
echo json_encode(['results' => $results]);