<?php
session_start();
require 'config.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$conn = db_connect();

// Get subtopic ID from URL
$subtopic_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if subtopic exists
$stmt = $conn->prepare("SELECT subtopic_id FROM python_subtopics WHERE subtopic_id = ?");
$stmt->bind_param("i", $subtopic_id);
$stmt->execute();
$result = $stmt->get_result();
$exists = $result->num_rows > 0;
$stmt->close();

if (!$exists) {
    $_SESSION['message'] = ['unsuccessful' => 'Subtopic not found.'];
    header('Location: admin_python.php');
    exit;
}

// Delete the subtopic
$stmt = $conn->prepare("DELETE FROM python_subtopics WHERE subtopic_id = ?");
$stmt->bind_param("i", $subtopic_id);

if ($stmt->execute()) {
    $_SESSION['message'] = ['successful' => 'Subtopic deleted successfully!'];
} else {
    $_SESSION['message'] = ['unsuccessful' => 'Error deleting subtopic: ' . $conn->error];
}

$stmt->close();
header('Location: admin_python.php');
exit;
?>