<?php
session_start();
require 'config.php';

// Check if admin is logged in (uncomment when ready)
/*
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
*/

$message = [
    'successful' => '',
    'unsuccessful' => ''
];

$conn = db_connect();

// Get all topics for the subtopic dropdown
$topics = [];
$stmt = $conn->query("SELECT python_id, topic FROM python ORDER BY python_id ASC");
if ($stmt) {
    $topics = $stmt->fetch_all(MYSQLI_ASSOC);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message['unsuccessful'] = "Invalid CSRF token. Please try again.";
    } else {
        if (isset($_POST['add_topic'])) {
            // Validate and sanitize inputs
            $topic_title = trim($_POST['topic_title']);
            $topic_content = trim($_POST['topic_content']);
            $topic_example = trim($_POST['topic_example']);
            $topic_question = trim($_POST['topic_question']);
            $topic_answer = trim($_POST['topic_answer']);

            if (empty($topic_title) || empty($topic_content)) {
                $message['unsuccessful'] = "Topic title and content are required.";
            } else {
                $check_stmt = $conn->prepare("SELECT python_id FROM python WHERE topic = ?");
                $check_stmt->bind_param("s", $topic_title);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    $message['unsuccessful'] = "This topic already exists! Please choose a different title.";
                } else {
                    // Add new topic
                    $stmt = $conn->prepare("INSERT INTO python (topic, content, example, question, answer) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssss", $topic_title, $topic_content, $topic_example, $topic_question, $topic_answer);
                    
                    if ($stmt->execute()) {
                        /*$_SESSION['last_topic_id'] = $conn->insert_id;*/
                        $message['successful'] = "Topic added successfully!";
                    } else {
                        $message['unsuccessful'] = "Error adding topic: " . $conn->error;
                    }
                }
                $check_stmt->close();
            }
        } elseif (isset($_POST['add_subtopic'])) {
            // Validate and sanitize inputs
            $python_id = (int)$_POST['python_id'];
            $subtopic_title = trim($_POST['subtopic_title']);
            $subtopic_content = trim($_POST['subtopic_content']);
            $subtopic_example = trim($_POST['subtopic_example']);
            $subtopic_question = trim($_POST['subtopic_question']);
            $subtopic_answer = trim($_POST['subtopic_answer']);

            if (empty($python_id) || empty($subtopic_title) || empty($subtopic_content)) {
                $message['unsuccessful'] = "Topic selection, subtopic title and content are required.";
            } else {
                // Add new subtopic
                $stmt = $conn->prepare("INSERT INTO python_subtopics (python_id, subtopic_title, content, example, question, answer) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssss", $python_id, $subtopic_title, $subtopic_content, $subtopic_example, $subtopic_question, $subtopic_answer);
                
                if ($stmt->execute()) {
                    $message['successful'] = "Subtopic added successfully!";
                } else {
                    $message['unsuccessful'] = "Error adding subtopic: " . $conn->error;
                }
            }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Python Learning Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(rgb(82, 139, 205), rgb(107, 89, 189));
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .admin-container {
            max-width: 800px;
            width: 100%;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
        }

        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: none;
        }

        .form-section.active {
            display: block;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: 500;
            color: #555;
            margin-bottom: 8px;
            display: block;
        }

        input[type="text"], 
        textarea, 
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            margin-bottom: 5px;
            transition: border-color 0.3s ease;
        }

        textarea {
            min-height: 100px;
            font-family: inherit;
        }

        .code {
            font-family: monospace;
            background-color: #f5f5f5;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        button {
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
            margin: 5px;
        }

        .btn-primary {
            background: #5F9EA0; /* cadet blue */
        }

        .btn-primary:hover {
            background: #40826D; /* viridian */
        }

        .btn-secondary {
            background: #6c757d; /* gray */
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-group {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .message.successful {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }

        .message.unsuccessful {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }

        .back-button {
            background: #6c757d;
            margin-top: 20px;
        }

        .back-button:hover {
            background: #5a6268;
        }

        @media (max-width: 600px) {
            .btn-group {
                flex-direction: column;
                align-items: center;
            }
            
            button {
                width: 100%;
                max-width: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1>Python Learning Admin Panel</h1>

        <?php if (!empty($message['successful'])): ?>
            <div class="message successful"><?php echo $message['successful']; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($message['unsuccessful'])): ?>
            <div class="message unsuccessful"><?php echo $message['unsuccessful']; ?></div>
        <?php endif; ?>

        <div class="btn-group" id="main-buttons">
            <button type="button" class="btn-primary" id="show-topic-form">Add Topic</button>
            <button type="button" class="btn-secondary" id="show-subtopic-form">Add Subtopic</button>
        </div>
        
        <!-- Add Topic Form -->
        <div class="form-section" id="topic-form">
            <h2>Add New Topic</h2>
            <form method="POST" id="topic-form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <div class="form-group">
                    <label for="topic_title">Python Topic:</label>
                    <input type="text" id="topic_title" name="topic_title" required>
                
                    <label for="topic_content">Content:</label>
                    <textarea id="topic_content" name="topic_content" required></textarea>

                    <label for="topic_example">Example:</label>
                    <textarea id="topic_example" name="topic_example" class="code"></textarea>

                    <label for="topic_question">Question:</label>
                    <textarea id="topic_question" name="topic_question"></textarea>

                    <label for="topic_answer">Answer:</label>
                    <textarea id="topic_answer" name="topic_answer"></textarea>
                </div>
                <button type="submit" name="add_topic" class="btn-primary">Save Topic</button>
                <button type="button" class="back-button" id="back-from-topic">Back to Menu</button>
            </form>
        </div>
        
        <!-- Add Subtopic Form -->
        <div class="form-section" id="subtopic-form">
            <h2>Add Subtopic</h2>
            <form method="POST" id="subtopic-form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <div class="form-group">
                    <label for="python_id">Select Topic:</label>
                    <select id="python_id" name="python_id" required>
                        <option value="">-- Select a Topic --</option>
                        <?php foreach ($topics as $topic): ?>
                            <option value="<?php echo htmlspecialchars($topic['python_id']); ?>">
                                <?php echo htmlspecialchars($topic['topic']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                
                    <label for="subtopic_title">Subtopic Title:</label>
                    <input type="text" id="subtopic_title" name="subtopic_title" required>
            
                    <label for="subtopic_content">Content:</label>
                    <textarea id="subtopic_content" name="subtopic_content" required></textarea>
              
                    <label for="subtopic_example">Example:</label>
                    <textarea id="subtopic_example" name="subtopic_example" class="code"></textarea>
               
                    <label for="subtopic_question">Question:</label>
                    <textarea id="subtopic_question" name="subtopic_question"></textarea>

                    <label for="subtopic_answer">Answer:</label>
                    <textarea id="subtopic_answer" name="subtopic_answer"></textarea>
                </div>
                <button type="submit" name="add_subtopic" class="btn-primary">Save Subtopic</button>
                <button type="button" class="back-button" id="back-from-subtopic">Back to Menu</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainButtons = document.getElementById('main-buttons');
            const topicFormBtn = document.getElementById('show-topic-form');
            const subtopicFormBtn = document.getElementById('show-subtopic-form');
            const topicForm = document.getElementById('topic-form');
            const subtopicForm = document.getElementById('subtopic-form');
            const backFromTopic = document.getElementById('back-from-topic');
            const backFromSubtopic = document.getElementById('back-from-subtopic');
            
            let currentForm = null;
            let formHasChanges = false;
            
            // Show main buttons and hide all forms
            function showMainMenu() {
                mainButtons.style.display = 'flex';
                topicForm.classList.remove('active');
                subtopicForm.classList.remove('active');
                currentForm = null;
                formHasChanges = false;
            }
            
            // Show topic form
            function showTopicForm() {
                mainButtons.style.display = 'none';
                topicForm.classList.add('active');
                subtopicForm.classList.remove('active');
                currentForm = 'topic';
                formHasChanges = false;
                
                // Reset change tracking
                const inputs = topicForm.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    input.addEventListener('input', () => {
                        formHasChanges = true;
                    });
                });
            }
            
            // Show subtopic form
            function showSubtopicForm() {
                mainButtons.style.display = 'none';
                subtopicForm.classList.add('active');
                topicForm.classList.remove('active');
                currentForm = 'subtopic';
                formHasChanges = false;
                
                // Reset change tracking
                const inputs = subtopicForm.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    input.addEventListener('input', () => {
                        formHasChanges = true;
                    });
                });
            }
            
            // Check before leaving form
            function confirmLeaveForm() {
                if (!formHasChanges) return true;
                
                return confirm('You have unsaved changes. Are you sure you want to leave this form?');
            }
            
            // Event listeners
            topicFormBtn.addEventListener('click', showTopicForm);
            subtopicFormBtn.addEventListener('click', showSubtopicForm);
            
            backFromTopic.addEventListener('click', function() {
                if (confirmLeaveForm()) {
                    showMainMenu();
                }
            });
            
            backFromSubtopic.addEventListener('click', function() {
                if (confirmLeaveForm()) {
                    showMainMenu();
                }
            });
            
            // Initialize with main menu
            showMainMenu();
        });
    </script>
</body>
</html>