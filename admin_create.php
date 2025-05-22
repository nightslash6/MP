<?php
session_start();
require 'config.php';

$conn = db_connect(); // Get MySQLi connection from config.php
$message = "";

// Handle new question submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = $_POST['QuestionText'] ?? '';
    $desc = $_POST['Description'] ?? '';
    $type = $_POST['QuestionType'] ?? '';
    $answer = $_POST['CorrectAnswer'] ?? '';
    $options = isset($_POST['Options']) ? json_encode(array_map('trim', explode(',', $_POST['Options']))) : null;

    if (!empty($text) && !empty($type)) {
        $stmt = $conn->prepare("INSERT INTO questions (question_text, description, question_type, options, correct_answer) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $text, $desc, $type, $options, $answer);
        $stmt->execute();
        $stmt->close();
        $message = "Question added successfully.";
    } else {
        $message = "Please fill in required fields.";
    }
}

// Handle deletion
if (isset($_GET['delete'])) {
    $qid = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM questions WHERE question_id = ?");
    $stmt->bind_param("i", $qid);
    $stmt->execute();
    $stmt->close();
    $message = "Question deleted.";
}

// Fetch all questions
$questions = [];
$result = $conn->query("SELECT * FROM questions ORDER BY question_id DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Question Manager</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; }
        th { background: #f4f4f4; }
    </style>
</head>
<body>
    <h1>Admin - Manage Questions</h1>
    <?php if (!empty($message)) echo "<p><b>$message</b></p>"; ?>

    <h2>Add New Question</h2>
    <form method="POST">
        <label>Question Text:</label><br>
        <textarea name="QuestionText" rows="2" cols="70" required></textarea><br><br>

        <label>Description (Optional):</label><br>
        <input type="text" name="Description" size="70"><br><br>

        <label>Type:</label>
        <select name="QuestionType" required onchange="toggleOptions(this.value)">
            <option value="">--Select--</option>
            <option value="MCQ">MCQ</option>
            <option value="ShortAnswer">Short Answer</option>
            <option value="LongAnswer">Long Answer</option>
        </select><br><br>

        <div id="options-div" style="display:none;">
            <label>Options (comma separated for MCQ):</label><br>
            <input type="text" name="Options" size="70"><br><br>
        </div>

        <label>Correct Answer:</label><br>
        <input type="text" name="CorrectAnswer" size="70"><br><br>

        <input type="submit" value="Add Question">
    </form>

    <h2>Existing Questions</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Text</th>
            <th>Type</th>
            <th>Answer</th>
            <th>Action</th>
        </tr>
        <?php foreach ($questions as $q): ?>
            <tr>
                <td><?= $q['question_id'] ?></td>
                <td><?= htmlspecialchars($q['question_text']) ?></td>
                <td><?= $q['question_type'] ?></td>
                <td><?= htmlspecialchars($q['correct_answer']) ?></td>
                <td><a href="?delete=<?= $q['question_id'] ?>" onclick="return confirm('Delete this question?')">Delete</a></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <script>
        function toggleOptions(type) {
            document.getElementById('options-div').style.display = (type === 'MCQ') ? 'block' : 'none';
        }
    </script>
</body>
</html>
