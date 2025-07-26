<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = $_POST['question_text'] ?? '';
    $description = $_POST['description'] ?? null;
    $question_type = $_POST['question_type'] ?? '';
    $category_id = intval($_POST['category_id']);
    $level_id = intval($_POST['level_id']);
    $options = $_POST['options'] ?? '';
    $correct_answer = $_POST['correct_answer'] ?? '';

    // Convert options to JSON array
    $options_arr = array_filter(array_map('trim', explode("\n", $options)));
    $options_json = json_encode($options_arr);

    // Basic validation (expand as needed)
    if (!$question_text || !$question_type || !$category_id || !$level_id || !$options_json || !$correct_answer) {
        die('Missing required fields. <a href="mcq_quiz_admin.php">Back</a>');
    }

    $conn = db_connect();
    $stmt = $conn->prepare(
        "INSERT INTO questions 
        (question_text, description, question_type, options, correct_answer, level_id, category_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('sssssis', $question_text, $description, $question_type, $options_json, $correct_answer, $level_id, $category_id);

    if ($stmt->execute()) {
        echo "Question created successfully! <a href='mcq_quiz_admin.php'>Add another</a>";
    } else {
        echo "Error: " . $stmt->error . " <a href='mcq_quiz_admin.php'>Back</a>";
    }
    $stmt->close();
}
?>
