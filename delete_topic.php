<?php
session_start();
require 'config.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$conn = db_connect();

// Get topic ID from URL
$topic_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if topic exists
$stmt = $conn->prepare("SELECT python_id FROM python WHERE python_id = ?");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();
$exists = $result->num_rows > 0;
$stmt->close();

if (!$exists) {
    $_SESSION['message'] = ['unsuccessful' => 'Topic not found.'];
    header('Location: admin_python.php');
    exit;
}

// First, delete all subtopics under this topic
$stmt = $conn->prepare("DELETE FROM python_subtopics WHERE python_id = ?");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$stmt->close();

// Then delete the topic
$stmt = $conn->prepare("DELETE FROM python WHERE python_id = ?");
$stmt->bind_param("i", $topic_id);

if ($stmt->execute()) {
    $_SESSION['message'] = ['successful' => 'Topic and its subtopics deleted successfully!'];
} else {
    $_SESSION['message'] = ['unsuccessful' => 'Error deleting topic: ' . $conn->error];
}

$stmt->close();
header('Location: admin_python.php');
exit;
?>