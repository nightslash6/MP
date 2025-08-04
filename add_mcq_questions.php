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

    if (empty($data['options']) || count($data['options']) < 2) {
        $errors['options'] = 'At least two options are required.';
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
        // Collect options from dynamic inputs
        $options = [];
        if (isset($_POST['mcq_options'])) {
            foreach ($_POST['mcq_options'] as $key => $value) {
                $opt = sanitize($value);
                if ($opt !== '') {
                    $options[] = $opt;
                }
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
            // Store options as JSON string in DB
            $options_json = json_encode($data['options']);

            $stmt = $conn->prepare("INSERT INTO questions (question_text, category_id, level_id, options, correct_answer) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("siiss", $data['question_text'], $data['category_id'], $data['level_id'], $options_json, $data['correct_answer']);

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
    <style>
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

<?php include 'navbar.php'; ?>

<div class="container mt-5 mb-5">
    <h2>Add New MCQ Question</h2>

    <?php if ($message): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" id="mcqForm">
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
            <label class="form-label">MCQ Options *</label>
            <div id="mcqOptionsList">
                <?php
                // Show existing options if form was submitted with errors
                if (!empty($_POST['mcq_options'])) {
                    foreach ($_POST['mcq_options'] as $index => $option) {
                        $optionNum = $index + 1;
                        echo '
                        <div class="input-group mb-2">
                            <span class="input-group-text">Option '.$optionNum.'</span>
                            <input type="text" name="mcq_options[]" class="form-control mcq-option" value="'.htmlspecialchars($option).'" maxlength="200" required>
                            '.($optionNum > 2 ? '<button type="button" class="btn btn-outline-danger remove-option">×</button>' : '').'
                        </div>';
                    }
                } else {
                    // Default 2 options
                    for ($i = 1; $i <= 2; $i++) {
                        echo '
                        <div class="input-group mb-2">
                            <span class="input-group-text">Option '.$i.'</span>
                            <input type="text" name="mcq_options[]" class="form-control mcq-option" maxlength="200" required>
                            '.($i > 2 ? '<button type="button" class="btn btn-outline-danger remove-option">×</button>' : '').'
                        </div>';
                    }
                }
                ?>
            </div>
            <button type="button" id="addMcqOption" class="btn btn-sm btn-outline-primary mt-2">+ Add Option</button>
            <?php if (isset($errors['options'])): ?>
                <div class="invalid-feedback d-block"><?= $errors['options'] ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="correct_answer" class="form-label">Correct Answer</label>
            <select id="correct_answer" name="correct_answer" class="form-select <?= isset($errors['correct_answer']) ? 'is-invalid' : '' ?>" required>
                <?php
                if (!empty($_POST['mcq_options'])) {
                    foreach ($_POST['mcq_options'] as $index => $option) {
                        $optionNum = $index + 1;
                        $selected = ($_POST['correct_answer'] ?? '') === $option ? 'selected' : '';
                        echo '<option value="'.htmlspecialchars($option).'" '.$selected.'>Option '.$optionNum.'</option>';
                    }
                } else {
                    for ($i = 1; $i <= 2; $i++) {
                        echo '<option value="">Option '.$i.'</option>';
                    }
                }
                ?>
            </select>
            <div class="invalid-feedback"><?= $errors['correct_answer'] ?? '' ?></div>
        </div>

        <button type="submit" class="btn btn-primary">Add Question</button>
        <a href="mcq_quiz_admin.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mcqOptionsList = document.getElementById('mcqOptionsList');
    const addMcqOptionBtn = document.getElementById('addMcqOption');
    const correctAnswerSelect = document.getElementById('correct_answer');
    
    // Add new MCQ option
    addMcqOptionBtn.addEventListener('click', function() {
        const optionCount = document.querySelectorAll('.mcq-option').length + 1;
        const newOption = document.createElement('div');
        newOption.className = 'input-group mb-2';
        newOption.innerHTML = `
            <span class="input-group-text">Option ${optionCount}</span>
            <input type="text" name="mcq_options[]" class="form-control mcq-option" maxlength="200" required>
            <button type="button" class="btn btn-outline-danger remove-option">×</button>
        `;
        mcqOptionsList.appendChild(newOption);
        
        // Add corresponding option to correct answer dropdown
        const newOptionElement = document.createElement('option');
        newOptionElement.value = '';
        newOptionElement.textContent = `Option ${optionCount}`;
        correctAnswerSelect.appendChild(newOptionElement);
    });
    
    // Remove option (event delegation for dynamically added buttons)
    mcqOptionsList.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-option')) {
            const optionGroup = e.target.closest('.input-group');
            const optionIndex = Array.from(mcqOptionsList.children).indexOf(optionGroup);
            
            // Remove the option
            optionGroup.remove();
            
            // Renumber remaining options
            document.querySelectorAll('.input-group-text').forEach((el, index) => {
                el.textContent = `Option ${index + 1}`;
            });
            
            // Update correct answer dropdown
            updateCorrectAnswerOptions();
        }
    });
    
    // Update correct answer options when MCQ options change
    function updateCorrectAnswerOptions() {
        const options = document.querySelectorAll('.mcq-option');
        correctAnswerSelect.innerHTML = '';
        
        options.forEach((option, index) => {
            const optionElement = document.createElement('option');
            optionElement.value = option.value;
            optionElement.textContent = `Option ${index + 1}`;
            correctAnswerSelect.appendChild(optionElement);
        });
    }
    
    // Update correct answer options when input values change
    mcqOptionsList.addEventListener('input', function(e) {
        if (e.target.classList.contains('mcq-option')) {
            updateCorrectAnswerOptions();
        }
    });
    
    // Form validation
    document.getElementById('mcqForm').addEventListener('submit', function(e) {
        const options = document.querySelectorAll('.mcq-option');
        let isValid = true;
        
        // Check at least 2 options are filled
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
        
        // Check correct answer is selected
        if (correctAnswerSelect.value === '') {
            alert('Please select the correct answer.');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
});
</script>

</body>
</html>