<?php
session_start();
require 'config.php';

$conn = db_connect();

// Check if user is logged in and get user data
$user_data = null;
if (isset($_SESSION['user_id']) &&  $_SESSION['user_role']==='admin') {
    $stmt = $conn->prepare("SELECT user_id, name, email, user_role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();
    }
    $stmt->close();
}else{
    header('Location: login.php');
    exit;
}

// Validate table parameter
if (!isset($_GET['table']) || !in_array($_GET['table'], ['crypto', 'forensics'])) {
    die('Invalid or missing table parameter.');
}
$table = $_GET['table'] === 'crypto' ? 'my_crypto_questions' : 'my_forensics_questions';

$is_edit = isset($_GET['id']);
$message = "";

// Initialize fields
$question_text = "";
$description = "";
$question_type = "ShortAnswer";
$difficulty = "Beginner";
$correct_answer = "";

// If editing, load existing data
if ($is_edit) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM `$table` WHERE question_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $question_text = $row['question_text'];
        $description = $row['description'];
        $question_type = $row['question_type'];
        $difficulty = $row['difficulty'];
        $correct_answer = $row['correct_answer'];
    } else {
        $message = "Question not found.";
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = trim($_POST['question_text']);
    $description = trim($_POST['description']);
    $question_type = $_POST['question_type'];
    $difficulty = $_POST['difficulty'];
    $correct_answer = trim($_POST['correct_answer']);

    if ($is_edit) {
        $stmt = $conn->prepare("UPDATE `$table` SET question_text=?, description=?, question_type=?, difficulty=?, correct_answer=? WHERE question_id=?");
        $stmt->bind_param("sssssi", $question_text, $description, $question_type, $difficulty, $correct_answer, $id);
        if ($stmt->execute()) {
            header("Location: shayaan_admin_manage.php");
            exit;
        } else {
            $message = "Error updating question.";
        }
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO `$table` (question_text, description, question_type, difficulty, correct_answer) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $question_text, $description, $question_type, $difficulty, $correct_answer);
        if ($stmt->execute()) {
            header("Location: forensics_admin_manage.php");
            exit;
        } else {
            $message = "Error adding question.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $is_edit ? "Edit" : "Add" ?> <?= ucfirst($_GET['table']) ?> Challenge</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background: #f8f9fa; }
        .container { max-width: 700px; }
    </style>
</head>
<body>
<div class="container">
    <h1 class="mb-4"><?= $is_edit ? "Edit" : "Add" ?> <?= ucfirst($_GET['table']) ?> Challenge</h1>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Question Text *</label>
            <textarea name="question_text" class="form-control" rows="2" required><?= htmlspecialchars($question_text) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Description (optional)</label>
            <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($description) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Question Type *</label>
            <select name="question_type" class="form-select" required>
                <option value="ShortAnswer" <?= $question_type==="ShortAnswer"?"selected":"" ?>>Short Answer</option>
                <option value="LongAnswer" <?= $question_type==="LongAnswer"?"selected":"" ?>>Long Answer</option>
                <option value="MCQ" <?= $question_type==="MCQ"?"selected":"" ?>>MCQ</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Difficulty *</label>
            <select name="difficulty" class="form-select" required>
                <option value="Beginner" <?= $difficulty==="Beginner"?"selected":"" ?>>Beginner</option>
                <option value="Intermediate" <?= $difficulty==="Intermediate"?"selected":"" ?>>Intermediate</option>
                <option value="Advanced" <?= $difficulty==="Advanced"?"selected":"" ?>>Advanced</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Correct Answer *</label>
            <input type="text" name="correct_answer" class="form-control" value="<?= htmlspecialchars($correct_answer) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary"><?= $is_edit ? "Update" : "Add" ?> Challenge</button>
        <a href="forensics_admin_manage.php" class="btn btn-secondary">Back to Manage</a>
    </form>
</div>
</body>
</html>
