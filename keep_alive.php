<?php
// Start the session
session_start();

// Set proper headers for JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

// Update the last activity time
$_SESSION['last_activity'] = time();

// Verify the update was successful
if ($_SESSION['last_activity'] === time()) {
    echo json_encode([
        'success' => true,
        'message' => 'Session extended',
        'new_time' => $_SESSION['last_activity']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to extend session'
    ]);
}
exit;