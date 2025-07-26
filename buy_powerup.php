<?php
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

$stmt = $conn->prepare("SELECT cost FROM powerups WHERE powerup_id = ?");
$stmt->bind_param("i", $powerup_id);
$stmt->execute();
$stmt->bind_result($cost);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("SELECT points FROM user_points WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($points);
$stmt->fetch();
$stmt->close();

if ($points >= $cost) {
    $conn->begin_transaction();
    $stmt = $conn->prepare("UPDATE user_points SET points = points - ? WHERE user_id = ?");
    $stmt->bind_param("ii", $cost, $user_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO user_powerups (user_id, powerup_id, quantity) VALUES (?, ?, 1)
        ON DUPLICATE KEY UPDATE quantity = quantity + 1");
    $stmt->bind_param("ii", $user_id, $powerup_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Insufficient points']);
}
?>
