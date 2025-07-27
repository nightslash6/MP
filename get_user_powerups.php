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

$powerups = [];
$res = $conn->query("
    SELECT up.powerup_id, p.name, p.description, up.quantity
    FROM user_powerups up
    JOIN powerups p ON up.powerup_id = p.powerup_id
    WHERE up.user_id = $user_id AND up.quantity > 0
");
while ($row = $res->fetch_assoc()) {
    $powerups[] = $row;
}
echo json_encode(['success' => true, 'powerups' => $powerups]);
?>
