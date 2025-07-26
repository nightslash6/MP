<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}
$conn = db_connect();
$input = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$powerup_id = intval($input['powerup_id'] ?? 0);

// Check if user owns the powerup
$stmt = $conn->prepare("SELECT quantity FROM user_powerups WHERE user_id = ? AND powerup_id = ?");
$stmt->bind_param("ii", $user_id, $powerup_id);
$stmt->execute();
$stmt->bind_result($quantity);
$stmt->fetch();
$stmt->close();

if ($quantity > 0) {
    // Deduct one powerup
    $stmt = $conn->prepare("UPDATE user_powerups SET quantity = quantity - 1 WHERE user_id = ? AND powerup_id = ?");
    $stmt->bind_param("ii", $user_id, $powerup_id);
    $stmt->execute();
    $stmt->close();

    // Optionally: Log usage or trigger effect (handled in frontend)
    echo json_encode(['success' => true, 'powerup_id' => $powerup_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'You do not own this powerup.']);
}
?>
