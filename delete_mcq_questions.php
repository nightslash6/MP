<?php
require 'config.php';
session_start();

// Check if user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate question ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid question ID']);
    exit;
}

$question_id = intval($_GET['id']);

$conn = db_connect();

// Delete question
$stmt = $conn->prepare("DELETE FROM questions WHERE question_id = ?");
$stmt->bind_param("i", $question_id);

if ($stmt->execute()) {
    $stmt->close();
    // Redirect back to admin list with success message
     $_SESSION['message'] = ['successful' => 'Question deleted successfully!'];
    header('Location: mcq_quiz_admin.php?msg=deleted');
    exit;
} else {
    $stmt->close();
    $_SESSION['message'] = ['unsuccessful' => 'Error deleting question: ' . $conn->error];
    header('Location: mcq_quiz_admin.php?msg=error');
    exit;
}
?>
