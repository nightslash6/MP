<?php
session_start();
require 'config.php';

$conn = db_connect();

// Check if user is logged in and get user data
$user_data = null;
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
    $stmt = $conn->prepare("SELECT user_id, name, email, user_role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();
    }
    $stmt->close();
} else {
    header('Location: login.php');
    exit;
}

// Validate table parameter
if (!isset($_GET['table']) || !in_array($_GET['table'], ['crypto', 'forensics'])) {
    die('Invalid or missing table parameter.');
}
$table = $_GET['table'] === 'crypto' ? 'my_crypto_questions' : 'my_forensics_questions';

$is_edit = isset($_GET['id']);
$errors = [];
$message = "";

// Initialize fields
$question_text = "";
$description = "";
$question_type = "ShortAnswer";
$difficulty = "Beginner";
$correct_answer = "";
$options = ['Option1' => '', 'Option2' => '', 'Option3' => '', 'Option4' => ''];

// Validation functions
function validateQuestionText($input) {
    if (empty(trim($input))) {
        return "Question text is required.";
    }
    if (strlen($input) > 1000) {
        return "Question text must be 1000 characters or less.";
    }
    return null;
}

function validateCorrectAnswer($input) {
    if (empty(trim($input))) {
        return "Correct answer is required.";
    }
    if (strlen($input) > 500) {
        return "Correct answer must be 500 characters or less.";
    }
    return null;
}

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
        
        if ($question_type === 'MCQ' && !empty($row['options'])) {
            $options = json_decode($row['options'], true);
        }
    } else {
        $errors[] = "Question not found.";
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid CSRF token. Please try again.';
    } else {
        $question_text = trim($_POST['question_text']);
        $description = trim($_POST['description']);
        $question_type = $_POST['question_type'];
        $difficulty = $_POST['difficulty'];
        $correct_answer = trim($_POST['correct_answer']);

        // Validate inputs
        if ($error = validateQuestionText($question_text)) {
            $errors[] = $error;
        }
        if ($error = validateCorrectAnswer($correct_answer)) {
            $errors[] = $error;
        }

        // Handle MCQ options
        if ($question_type === 'MCQ') {
            $options = [];
            foreach ($_POST['mcq_options'] as $key => $value) {
                if (!empty(trim($value))) {
                    $options["Option" . ($key + 1)] = trim($value);
                }
            }
            
            if (count($options) < 2) {
                $errors[] = "At least 2 options are required for MCQ questions.";
            }
        }

        if (empty($errors)) {
            if ($is_edit) {
                if ($question_type === 'MCQ') {
                    $stmt = $conn->prepare("UPDATE `$table` SET question_text=?, description=?, question_type=?, difficulty=?, correct_answer=?, options=? WHERE question_id=?");
                    $stmt->bind_param("ssssssi", $question_text, $description, $question_type, $difficulty, $correct_answer, json_encode($options), $id);
                } else {
                    $stmt = $conn->prepare("UPDATE `$table` SET question_text=?, description=?, question_type=?, difficulty=?, correct_answer=? WHERE question_id=?");
                    $stmt->bind_param("sssssi", $question_text, $description, $question_type, $difficulty, $correct_answer, $id);
                }
            } else {
                if ($question_type === 'MCQ') {
                    $stmt = $conn->prepare("INSERT INTO `$table` (question_text, description, question_type, difficulty, correct_answer, options) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssss", $question_text, $description, $question_type, $difficulty, $correct_answer, json_encode($options));
                } else {
                    $stmt = $conn->prepare("INSERT INTO `$table` (question_text, description, question_type, difficulty, correct_answer) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssss", $question_text, $description, $question_type, $difficulty, $correct_answer);
                }
            }

            if ($stmt->execute()) {
                $_SESSION['message'] = ['successful' => "Question " . ($is_edit ? "updated" : "added") . " successfully!"];
                header("Location: forensics_admin_manage.php");
                exit;
            } else {
                $errors[] = "Error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $is_edit ? "Edit" : "Add" ?> <?= ucfirst($_GET['table']) ?> Challenge</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa; 
            padding-bottom: 60px;
        }
        .container { 
            max-width: 700px; 
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
        .remove-option {
            width: 40px;
            font-weight: bold;
        }
        .input-group-text {
            min-width: 80px;
        }
        #mcqOptionsContainer {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>
<?php include 'navbar.php' ?>
<div class="container">
    <h1 class="mb-4"><?= $is_edit ? "Edit" : "Add" ?> <?= ucfirst($_GET['table']) ?> Challenge</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        
        <div class="mb-3">
            <label class="form-label">Question Text *</label>
            <textarea name="question_text" class="form-control" rows="2" required maxlength="1000"><?= htmlspecialchars($question_text) ?></textarea>
            <div class="error-message"></div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Description (optional)</label>
            <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($description) ?>" maxlength="500">
            <div class="error-message"></div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Question Type *</label>
            <select name="question_type" id="questionType" class="form-select" required>
                <option value="ShortAnswer" <?= $question_type==="ShortAnswer"?"selected":"" ?>>Short Answer</option>
                <option value="LongAnswer" <?= $question_type==="LongAnswer"?"selected":"" ?>>Long Answer</option>
                <option value="MCQ" <?= $question_type==="MCQ"?"selected":"" ?>>MCQ</option>
            </select>
        </div>
        
        <div id="mcqOptionsContainer" style="display: <?= $question_type === 'MCQ' ? 'block' : 'none' ?>;">
            <div class="mb-3">
                <label class="form-label">MCQ Options *</label>
                <div id="mcqOptionsList">
                    <?php foreach ($options as $key => $value): ?>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><?= $key ?></span>
                            <input type="text" name="mcq_options[]" class="form-control mcq-option" value="<?= htmlspecialchars($value) ?>" maxlength="200">
                            <?php if ($key !== 'Option1' && $key !== 'Option2'): ?>
                                <button type="button" class="btn btn-outline-danger remove-option">×</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="addMcqOption" class="btn btn-sm btn-outline-primary mt-2">+ Add Option</button>
            </div>
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
            <input type="text" name="correct_answer" class="form-control" value="<?= htmlspecialchars($correct_answer) ?>" required maxlength="500">
            <div class="error-message"></div>
        </div>
        
        <button type="submit" class="btn btn-primary"><?= $is_edit ? "Update" : "Add" ?> Challenge</button>
        <a href="forensics_admin_manage.php" class="btn btn-secondary">Back to Manage</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const questionTypeSelect = document.getElementById('questionType');
    const mcqOptionsContainer = document.getElementById('mcqOptionsContainer');
    const mcqOptionsList = document.getElementById('mcqOptionsList');
    const addMcqOptionBtn = document.getElementById('addMcqOption');
    
    // Show/hide MCQ options based on question type
    function toggleMcqOptions() {
        mcqOptionsContainer.style.display = questionTypeSelect.value === 'MCQ' ? 'block' : 'none';
    }
    
    // Toggle when question type changes
    questionTypeSelect.addEventListener('change', toggleMcqOptions);
    
    // Add new MCQ option
    addMcqOptionBtn.addEventListener('click', function() {
        const optionCount = document.querySelectorAll('.mcq-option').length + 1;
        const newOption = document.createElement('div');
        newOption.className = 'input-group mb-2';
        newOption.innerHTML = `
            <span class="input-group-text">Option ${optionCount}</span>
            <input type="text" name="mcq_options[]" class="form-control mcq-option" maxlength="200">
            <button type="button" class="btn btn-outline-danger remove-option">×</button>
        `;
        mcqOptionsList.appendChild(newOption);
    });
    
    // Remove option (event delegation for dynamically added buttons)
    mcqOptionsList.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-option')) {
            e.target.closest('.input-group').remove();
            // Renumber remaining options
            document.querySelectorAll('.input-group-text').forEach((el, index) => {
                el.textContent = `Option ${index + 1}`;
            });
        }
    });
    
    // Client-side validation
    document.querySelector('form').addEventListener('submit', function(e) {
        let isValid = true;
        const questionText = document.querySelector('[name="question_text"]');
        const correctAnswer = document.querySelector('[name="correct_answer"]');
        
        // Clear previous error messages
        document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
        
        // Validate question text
        if (questionText.value.trim() === '') {
            questionText.nextElementSibling.textContent = 'Question text is required.';
            isValid = false;
        } else if (questionText.value.length > 1000) {
            questionText.nextElementSibling.textContent = 'Question text must be 1000 characters or less.';
            isValid = false;
        }
        
        // Validate correct answer
        if (correctAnswer.value.trim() === '') {
            correctAnswer.nextElementSibling.textContent = 'Correct answer is required.';
            isValid = false;
        } else if (correctAnswer.value.length > 500) {
            correctAnswer.nextElementSibling.textContent = 'Correct answer must be 500 characters or less.';
            isValid = false;
        }
        
        // Additional validation for MCQ questions
        if (questionTypeSelect.value === 'MCQ') {
            const options = document.querySelectorAll('.mcq-option');
            let filledOptions = 0;
            
            options.forEach(option => {
                if (option.value.trim() !== '') {
                    filledOptions++;
                }
            });
            
            if (filledOptions < 2) {
                alert('At least 2 MCQ options are required.');
                isValid = false;
            }
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });

    // Auto-dismiss alerts after 3 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 3000);
});
</script>
</body>
</html>