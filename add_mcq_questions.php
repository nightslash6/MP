<?php
session_start();
require 'config.php';

// Only admins allowed
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';
$errors = [];

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$conn = db_connect();

$categories = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name")->fetch_all(MYSQLI_ASSOC);
$levels = $conn->query("SELECT MIN(level_id) as level_id, level_name FROM levels GROUP BY level_name ORDER BY level_id")->fetch_all(MYSQLI_ASSOC);

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateInput($data, &$errors) {
    if (empty($data['question_text'])) {
        $errors['question_text'] = 'Question text is required.';
    } elseif (!preg_match("/^[\w\s\-.,?!:'\"()]+$/u", $data['question_text'])) {
        $errors['question_text'] = 'Invalid characters in question.';
    }

    if (empty($data['category_id']) || !is_numeric($data['category_id'])) {
        $errors['category_id'] = 'Please select a category.';
    }

    if (empty($data['level_id']) || !is_numeric($data['level_id'])) {
        $errors['level_id'] = 'Please select a level.';
    }

    if (empty($data['options']) || count($data['options']) < 1) {
        $errors['options'] = 'At least one option is required.';
    }

    if (empty($data['correct_answer'])) {
        $errors['correct_answer'] = 'Please specify the correct answer.';
    }
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = 'Invalid CSRF token!';
    } else {
        $number_of_options = intval($_POST['number_of_options'] ?? 1);

        // Collect options from option inputs dynamically
        $options = [];
        for ($i = 1; $i <= $number_of_options; $i++) {
            $opt = sanitize($_POST["option$i"] ?? '');
            if ($opt !== '') {
                $options[] = $opt;
            }
        }

        $data = [
            'question_text' => sanitize($_POST['question_text'] ?? ''),
            'category_id' => $_POST['category_id'] ?? '',
            'level_id' => $_POST['level_id'] ?? '',
            'options' => $options,
            'correct_answer' => sanitize($_POST['correct_answer'] ?? ''),
        ];

        validateInput($data, $errors);

        if (empty($errors)) {
            // Extract correct answer text from selected 'optionX'
            $correct_answer_index = intval(substr($data['correct_answer'], 6)) - 1; // e.g. 'option1' -> index 0
            $correct_answer_text = $data['options'][$correct_answer_index] ?? '';

            // Store options as JSON string in DB
            $options_json = json_encode($data['options']);

            $stmt = $conn->prepare("INSERT INTO questions (question_text, category_id, level_id, options, correct_answer) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("siiss", $data['question_text'], $data['category_id'], $data['level_id'], $options_json, $correct_answer_text);

            if ($stmt->execute()) {
                $_SESSION['message'] = ['successful' => 'Question added successfully!'];
                $stmt->close();
                header('Location: mcq_quiz_admin.php');
                exit;
            } else {
                $message = 'Database error: ' . $stmt->error;
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add MCQ Question - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="mstyles.css" />
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container mt-5 mb-5">
    <h2>Add New MCQ Question</h2>

    <?php if ($message): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" onsubmit="return validateForm()">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>" />

        <div class="mb-3">
            <label for="question_text" class="form-label">Question Text*</label>
            <textarea class="form-control <?= isset($errors['question_text']) ? 'is-invalid' : '' ?>" id="question_text" name="question_text" rows="3" required><?= $_POST['question_text'] ?? '' ?></textarea>
            <div class="invalid-feedback"><?= $errors['question_text'] ?? '' ?></div>
        </div>

        <div class="mb-3">
            <label for="category_id" class="form-label">Category</label>
            <select id="category_id" class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" name="category_id" required>
                <option value="" disabled selected>Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?=$cat['category_id']?>" <?= (isset($_POST['category_id']) && $_POST['category_id']==$cat['category_id']) ? 'selected' : '' ?>><?=htmlspecialchars($cat['category_name'])?></option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback"><?= $errors['category_id'] ?? '' ?></div>
        </div>

        <div class="mb-3">
            <label for="level_id" class="form-label">Level</label>
            <select id="level_id" class="form-select <?= isset($errors['level_id']) ? 'is-invalid' : '' ?>" name="level_id" required>
                <option value="" disabled selected>Select Level</option>
                <?php foreach ($levels as $lvl): ?>
                    <option value="<?=$lvl['level_id']?>" <?= (isset($_POST['level_id']) && $_POST['level_id']==$lvl['level_id']) ? 'selected' : '' ?>><?=htmlspecialchars($lvl['level_name'])?></option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback"><?= $errors['level_id'] ?? '' ?></div>
        </div>

        <div class="mb-3">
            <label for="number_of_options" class="form-label">Number of Options</label>
            <select id="number_of_options" name="number_of_options" class="form-select" onchange="populateOptions()" required>
                <?php
                $selectedCount = isset($_POST['number_of_options']) ? intval($_POST['number_of_options']) : 1;
                for ($i=1; $i<=5; $i++): ?>
                    <option value="<?=$i?>" <?= $selectedCount==$i ? 'selected' : '' ?>><?=$i?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div id="optionsContainer"></div>

        <div class="mb-3">
            <label for="correct_answer" class="form-label">Correct Answer</label>
            <select id="correct_answer" name="correct_answer" class="form-select <?= isset($errors['correct_answer']) ? 'is-invalid' : '' ?>" required>
                <!-- JS will populate options here -->
            </select>
            <div class="invalid-feedback"><?= $errors['correct_answer'] ?? '' ?></div>
        </div>

        <button type="submit" class="btn btn-primary">Add Question</button>
        <a href="mcq_quiz_admin.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>

<script>
function updateCorrectAnswerOptions() {
    const numberOfOptions = parseInt(document.getElementById('number_of_options').value);
    const correctAnswerSelect = document.getElementById('correct_answer');
    const previousSelection = correctAnswerSelect.value;

    correctAnswerSelect.innerHTML = '';

    for (let i = 1; i <= numberOfOptions; i++) {
        const optionText = `Option ${i}`;
        const option = document.createElement('option');
        option.value = `option${i}`;
        option.textContent = optionText;

        if (previousSelection === option.value) {
            option.selected = true;
        }
        correctAnswerSelect.appendChild(option);
    }

    if (!correctAnswerSelect.value && numberOfOptions > 0) {
        correctAnswerSelect.selectedIndex = 0;
    }
}

function populateOptions() {
    const container = document.getElementById('optionsContainer');
    const count = parseInt(document.getElementById('number_of_options').value);
    container.innerHTML = '';

    for (let i = 1; i <= count; i++) {
        let val = '';
        <?php if (!empty($_POST)): ?>
        val = <?= json_encode($_POST) ?>[`option${i}`] || '';
        <?php endif; ?>

        const inputGroup = document.createElement('div');
        inputGroup.className = 'mb-3';
        inputGroup.innerHTML = `
            <label for="option${i}" class="form-label">Option ${i}</label>
            <input type="text" id="option${i}" name="option${i}" class="form-control" value="${val}" required />
        `;
        container.appendChild(inputGroup);
    }
    updateCorrectAnswerOptions();
}

document.addEventListener('DOMContentLoaded', () => {
    populateOptions();
});

document.getElementById('number_of_options').addEventListener('change', () => {
    populateOptions();
});
</script>

</body>
</html>
