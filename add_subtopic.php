<?php
session_start();
require 'config.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';
$conn = db_connect();

// Validation functions
function validateTitle($input) {
    // Allows letters, numbers, spaces, and basic punctuation
    return preg_match('/^[a-zA-Z0-9\s\-.,?!:;\'"()]+$/', $input);
}

function validateContent($input) {
    // More permissive for content that might contain code examples
    return !empty(trim($input));
}

// Fetch all topics for dropdown
$topics = [];
$stmt = $conn->query("SELECT python_id, topic FROM python ORDER BY topic ASC");
if ($stmt) {
    $topics = $stmt->fetch_all(MYSQLI_ASSOC);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = 'Invalid CSRF token. Please try again.';
    } else {
        // Validate and sanitize inputs
        $python_id = (int)($_POST['python_id'] ?? 0);
        $subtopic_title = trim($_POST['subtopic_title'] ?? '');
        $subtopic_content = trim($_POST['subtopic_content'] ?? '');
        $subtopic_example = trim($_POST['subtopic_example'] ?? '');
        $subtopic_question = trim($_POST['subtopic_question'] ?? '');
        $subtopic_answer = trim($_POST['subtopic_answer'] ?? '');

        // Validate required fields
        if (empty($python_id)) {
            $message = 'Please select a topic.';
        } elseif (empty($subtopic_title) || empty($subtopic_content)) {
            $message = 'Subtopic title and content are required.';
        } 
        // Validate title format
        elseif (!validateTitle($subtopic_title)) {
            $message = 'Subtopic title can only contain letters, numbers, spaces, and basic punctuation.';
        }
        // Validate length (255 characters max)
        elseif (strlen($subtopic_title) > 255) {
            $message = 'Subtopic title must be 255 characters or less.';
        }
        // Validate content length (assuming your DB column can handle it)
        elseif (strlen($subtopic_content) > 65535) { // Typical TEXT field limit
            $message = 'Content is too long. Please shorten it.';
        }
        // Validate example length if provided
        elseif (!empty($subtopic_example) && strlen($subtopic_example) > 65535) {
            $message = 'Example code is too long. Please shorten it.';
        }
        // Validate question length if provided
        elseif (!empty($subtopic_question) && strlen($subtopic_question) > 65535) {
            $message = 'Question is too long. Please shorten it.';
        }
        // Validate answer length if provided
        elseif (!empty($subtopic_answer) && strlen($subtopic_answer) > 65535) {
            $message = 'Answer is too long. Please shorten it.';
        } else {
            // Check for duplicate subtopic under the same topic
            $stmt = $conn->prepare("SELECT subtopic_id FROM python_subtopics WHERE subtopic_title = ? AND python_id = ?");
            $stmt->bind_param("si", $subtopic_title, $python_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $message = 'This subtopic already exists under the selected topic! Please choose a different title.';
            } else {
                // Insert new subtopic
                $stmt = $conn->prepare("INSERT INTO python_subtopics (python_id, subtopic_title, content, example, question, answer) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssss", $python_id, $subtopic_title, $subtopic_content, $subtopic_example, $subtopic_question, $subtopic_answer);
                
                if ($stmt->execute()) {
                    $_SESSION['message'] = ['successful' => 'Subtopic added successfully!'];
                    header('Location: admin_python.php');
                    exit;
                } else {
                    $message = 'Error adding subtopic: ' . $conn->error;
                }
            }
            $stmt->close();
        }
    }
}

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Subtopic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container mt-5 mb-5">
        <h2>Add New Subtopic</h2>

        <?php if ($message): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
            
        <form method="POST" onsubmit="return validateForm()">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <div class="mb-3">
                <label for="python_id" class="form-label">Select Topic*</label>
                <select class="form-select" id="python_id" name="python_id" required>
                    <option value="">-- Select a Topic --</option>
                    <?php foreach ($topics as $topic): ?>
                        <option value="<?= htmlspecialchars($topic['python_id']) ?>">
                            <?= htmlspecialchars($topic['topic']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="subtopic_title" class="form-label">Subtopic Title* (max 255 characters)</label>
                <input type="text" class="form-control" id="subtopic_title" name="subtopic_title" required
                        maxlength="255" pattern="[a-zA-Z0-9\s\-.,?!:;'&quot;()]+"
                        title="Only letters, numbers, spaces, and basic punctuation are allowed">
            </div>
            
            <div class="mb-3">
                <label for="subtopic_content" class="form-label">Content*</label>
                <textarea class="form-control" id="subtopic_content" name="subtopic_content" rows="5" required></textarea>
            </div>
            
            <div class="mb-3">
                <label for="subtopic_example" class="form-label">Example Code</label>
                <textarea class="form-control" id="subtopic_example" name="subtopic_example" rows="5"></textarea>
            </div>
            
            <div class="mb-3">
                <label for="subtopic_question" class="form-label">Question</label>
                <textarea class="form-control" id="subtopic_question" name="subtopic_question" rows="3"></textarea>
            </div>
            
            <div class="mb-3">
                <label for="subtopic_answer" class="form-label">Answer</label>
                <textarea class="form-control" id="subtopic_answer" name="subtopic_answer" rows="3"></textarea>
            </div>
            
            <button type="submit" name="add_subtopic" class="btn btn-primary">Save Subtopic</button>
            <a href="admin_python.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script>
    // Auto-dismiss alerts after 3 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 3000);
    </script>
</body>
</html>