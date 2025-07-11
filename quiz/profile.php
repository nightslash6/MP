<?php
session_start();
require 'config.php'; // your DB connection

// Assume the user is logged in
if (!isset($_SESSION['user1_id'])) {
    die('Please log in first.');
}

$view_id = $_GET['id'] ?? $_SESSION['user1_id']; // vulnerable line

// Insecure: anyone can change ?id= to another user's ID
$stmt = $pdo->prepare("SELECT username, email, flag FROM users1 WHERE id = ?");
$stmt->execute([$view_id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

echo "<h2>Profile of: " . htmlspecialchars($user['username']) . "</h2>";
echo "<p>Email: " . htmlspecialchars($user['email']) . "</p>";

if ($user['flag']) {
    echo "<p><strong>Flag:</strong> " . htmlspecialchars($user['flag']) . "</p>";
}
?>
