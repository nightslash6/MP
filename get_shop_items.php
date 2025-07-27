<?php
require 'config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}
$conn = db_connect();
$user_id = $_SESSION['user_id'];

// Get user points
$stmt = $conn->prepare("SELECT points FROM user_points WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_points);
$stmt->fetch();
$stmt->close();

// Get powerups
$powerups = [];
$res = $conn->query("SELECT powerup_id, name, description, cost FROM powerups");
while ($row = $res->fetch_assoc()) {
    $powerups[] = $row;
}
echo json_encode([
    'success' => true,
    'user_points' => $user_points,
    'powerups' => $powerups
]);
?>
