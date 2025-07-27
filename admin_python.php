<?php
session_start();
require 'config.php';

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

$message = [
    'successful' => '',
    'unsuccessful' => ''
];

$conn = db_connect();

// Get all topics for the subtopic dropdown
$topics = [];
$stmt = $conn->query("SELECT python_id, topic, content, example, question, answer FROM python ORDER BY python_id ASC");
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
                        $message['successful'] = "Topic added successfully!";
                        // Refresh topics list
                        $stmt = $conn->query("SELECT python_id, topic, content, example, question, answer FROM python ORDER BY python_id ASC");
                        $topics = $stmt->fetch_all(MYSQLI_ASSOC);
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
                // Check for duplicate subtopic under the same topic
                $check_stmt = $conn->prepare("SELECT subtopic_id FROM python_subtopics WHERE subtopic_title = ? AND python_id = ?");
                $check_stmt->bind_param("si", $subtopic_title, $python_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    $message['unsuccessful'] = "This subtopic already exists under the selected topic! Please choose a different title.";
                } else {
                    // Add new subtopic
                    $stmt = $conn->prepare("INSERT INTO python_subtopics (python_id, subtopic_title, content, example, question, answer) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("isssss", $python_id, $subtopic_title, $subtopic_content, $subtopic_example, $subtopic_question, $subtopic_answer);
                    
                    if ($stmt->execute()) {
                        $message['successful'] = "Subtopic added successfully!";
                        // Refresh subtopics list
                        $stmt = $conn->query("SELECT ps.subtopic_id, ps.subtopic_title, p.python_id, p.topic, ps.content, ps.example, ps.question, ps.answer 
                                              FROM python_subtopics ps 
                                              JOIN python p ON ps.python_id = p.python_id 
                                              ORDER BY p.python_id ASC, ps.subtopic_id ASC");
                        $subtopics = $stmt->fetch_all(MYSQLI_ASSOC);
                    } else {
                        $message['unsuccessful'] = "Error adding subtopic: " . $conn->error;
                    }
                }
                $check_stmt->close();
            }
        } elseif (isset($_POST['edit_topic'])) {
            // Validate and sanitize inputs
            $python_id = (int)$_POST['edit_python_id'];
            $topic_title = trim($_POST['topic_title']);
            $topic_content = trim($_POST['topic_content']);
            $topic_example = trim($_POST['topic_example']);
            $topic_question = trim($_POST['topic_question']);
            $topic_answer = trim($_POST['topic_answer']);

            if (empty($python_id) || empty($topic_title) || empty($topic_content)) {
                $message['unsuccessful'] = "Topic ID, title and content are required.";
            } else {
                // Check if another topic already has this title
                $check_stmt = $conn->prepare("SELECT python_id FROM python WHERE topic = ? AND python_id != ?");
                $check_stmt->bind_param("si", $topic_title, $python_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    $message['unsuccessful'] = "Another topic already has this title! Please choose a different title.";
                } else {
                    // Update topic
                    $stmt = $conn->prepare("UPDATE python SET topic = ?, content = ?, example = ?, question = ?, answer = ? WHERE python_id = ?");
                    $stmt->bind_param("sssssi", $topic_title, $topic_content, $topic_example, $topic_question, $topic_answer, $python_id);
                    
                    if ($stmt->execute()) {
                        $message['successful'] = "Topic updated successfully!";
                        // Refresh topics list
                        $stmt = $conn->query("SELECT python_id, topic, content, example, question, answer FROM python ORDER BY python_id ASC");
                        $topics = $stmt->fetch_all(MYSQLI_ASSOC);
                    } else {
                        $message['unsuccessful'] = "Error updating topic: " . $conn->error;
                    }
                }
                $check_stmt->close();
            }
        } elseif (isset($_POST['edit_subtopic'])) {
            // Validate and sanitize inputs
            $subtopic_id = (int)$_POST['edit_subtopic_id'];
            $python_id = (int)$_POST['python_id'];
            $subtopic_title = trim($_POST['subtopic_title']);
            $subtopic_content = trim($_POST['subtopic_content']);
            $subtopic_example = trim($_POST['subtopic_example']);
            $subtopic_question = trim($_POST['subtopic_question']);
            $subtopic_answer = trim($_POST['subtopic_answer']);

            if (empty($subtopic_id) || empty($python_id) || empty($subtopic_title) || empty($subtopic_content)) {
                $message['unsuccessful'] = "Subtopic ID, topic selection, title and content are required.";
            } else {
                // Check if another subtopic already has this title under the same topic
                $check_stmt = $conn->prepare("SELECT subtopic_id FROM python_subtopics WHERE subtopic_title = ? AND python_id = ? AND subtopic_id != ?");
                $check_stmt->bind_param("sii", $subtopic_title, $python_id, $subtopic_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    $message['unsuccessful'] = "Another subtopic under this topic already has this title! Please choose a different title.";
                } else {
                    // Update subtopic
                    $stmt = $conn->prepare("UPDATE python_subtopics SET python_id = ?, subtopic_title = ?, content = ?, example = ?, question = ?, answer = ? WHERE subtopic_id = ?");
                    $stmt->bind_param("isssssi", $python_id, $subtopic_title, $subtopic_content, $subtopic_example, $subtopic_question, $subtopic_answer, $subtopic_id);
                    
                    if ($stmt->execute()) {
                        $message['successful'] = "Subtopic updated successfully!";
                        // Refresh subtopics list
                        $stmt = $conn->query("SELECT ps.subtopic_id, ps.subtopic_title, p.python_id, p.topic, ps.content, ps.example, ps.question, ps.answer 
                                              FROM python_subtopics ps 
                                              JOIN python p ON ps.python_id = p.python_id 
                                              ORDER BY p.python_id ASC, ps.subtopic_id ASC");
                        $subtopics = $stmt->fetch_all(MYSQLI_ASSOC);
                    } else {
                        $message['unsuccessful'] = "Error updating subtopic: " . $conn->error;
                    }
                }
                $check_stmt->close();
            }
        }
    } 
}

// Get all subtopics for the edit subtopic dropdown
$subtopics = [];
$stmt = $conn->query("SELECT ps.subtopic_id, ps.subtopic_title, p.python_id, p.topic, ps.content, ps.example, ps.question, ps.answer 
                      FROM python_subtopics ps 
                      JOIN python p ON ps.python_id = p.python_id 
                      ORDER BY p.python_id ASC, ps.subtopic_id ASC");
if ($stmt) {
    $subtopics = $stmt->fetch_all(MYSQLI_ASSOC);
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
            padding-top: 90px; /*makes admin container fit in the page, with a little space at the top (below navbar)*/ 
            padding-bottom: 30px; /*and bottom*/
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
            background: #5F9EA0;
        }

        .btn-primary:hover {
            background: #40826D;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-group {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .button-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .button-row {
            display: flex;
            justify-content: center;
            gap: 15px;
            width: 100%;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            position: fixed;
            top: 90px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            max-width: 80%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; top: 0; }
            to { opacity: 1; top: 20px; }
        }

        @keyframes fadeOut {
            from { opacity: 1; top: 20px; }
            to { opacity: 0; top: 0; }
        }

        .message.fade-out {
            animation: fadeOut 0.3s ease-in-out;
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

        .cancel-button {
            background: #dc3545;
        }

        .cancel-button:hover {
            background: #c82333;
        }

        @media (max-width: 600px) {
            .btn-group, .button-row {
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
    <nav class="fixed-top"><?php include 'navbar.php'; ?></nav>

    <div class="admin-container">

        <h1>Python Learning Admin Panel</h1>

        <?php if (!empty($message['successful'])): ?>
            <div class="message successful" id="success-message"><?php echo $message['successful']; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($message['unsuccessful'])): ?>
            <div class="message unsuccessful" id="error-message"><?php echo $message['unsuccessful']; ?></div>
        <?php endif; ?>

        <div class="button-container" id="main-buttons">
            <div class="button-row">
                <button type="button" class="btn-primary" id="show-topic-form">Add Topic</button>
                <button type="button" class="btn-secondary" id="show-subtopic-form">Add Subtopic</button>
            </div>
            <div class="button-row">
                <button type="button" class="btn-warning" id="show-edit-topic-form">Edit Topic</button>
                <button type="button" class="btn-warning" id="show-edit-subtopic-form">Edit Subtopic</button>
            </div>
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
                <button type="button" class="cancel-button" id="cancel-topic">Cancel</button>
            </form>
        </div>
        
        <!-- Edit Topic Form -->
        <div class="form-section" id="edit-topic-form">
            <h2>Edit Topic</h2>
            <form method="POST" id="edit-topic-form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="edit_python_id" id="edit_python_id">
                
                <div class="form-group">
                    <label for="edit_topic_select">Select Topic to Edit:</label>
                    <select id="edit_topic_select" name="edit_topic_select" required>
                        <option value="">-- Select a Topic --</option>
                        <?php foreach ($topics as $topic): ?>
                            <option value="<?php echo htmlspecialchars($topic['python_id']); ?>">
                                <?php echo htmlspecialchars($topic['topic']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="button" class="back-button" id="back-from-edit-topic-dropdown" style="margin-top: 10px; display: block;">Back</button>
                </div>
                
                <div id="edit-topic-details" style="display: none;">
                    <div class="form-group">
                        <label for="edit_topic_title">Python Topic:</label>
                        <input type="text" id="edit_topic_title" name="topic_title" required>
                    
                        <label for="edit_topic_content">Content:</label>
                        <textarea id="edit_topic_content" name="topic_content" required></textarea>

                        <label for="edit_topic_example">Example:</label>
                        <textarea id="edit_topic_example" name="topic_example" class="code"></textarea>

                        <label for="edit_topic_question">Question:</label>
                        <textarea id="edit_topic_question" name="topic_question"></textarea>

                        <label for="edit_topic_answer">Answer:</label>
                        <textarea id="edit_topic_answer" name="topic_answer"></textarea>
                    </div>
                    <button type="submit" name="edit_topic" class="btn-primary">Save Changes</button>
                    <button type="button" class="back-button" id="back-from-edit-topic">Back</button>
                    <button type="button" class="cancel-button" id="cancel-edit-topic">Cancel</button>
                </div>
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
                <button type="button" class="cancel-button" id="cancel-subtopic">Cancel</button>
            </form>
        </div>
        
        <!-- Edit Subtopic Form -->
        <div class="form-section" id="edit-subtopic-form">
            <h2>Edit Subtopic</h2>
            <form method="POST" id="edit-subtopic-form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="edit_subtopic_id" id="edit_subtopic_id">
                
                <div class="form-group">
                    <label for="edit_subtopic_select">Select Subtopic to Edit:</label>
                    <select id="edit_subtopic_select" name="edit_subtopic_select" required>
                        <option value="">-- Select a Subtopic --</option>
                        <?php foreach ($subtopics as $subtopic): ?>
                            <option value="<?php echo htmlspecialchars($subtopic['subtopic_id']); ?>" 
                                    data-python-id="<?php echo htmlspecialchars($subtopic['python_id']); ?>"
                                    data-topic="<?php echo htmlspecialchars($subtopic['topic']); ?>">
                                <?php echo htmlspecialchars($subtopic['topic'] . " - " . $subtopic['subtopic_title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="button" class="back-button" id="back-from-edit-subtopic-dropdown" style="margin-top: 10px; display: block;">Back</button>
                </div>
                
                <div id="edit-subtopic-details" style="display: none;">
                    <div class="form-group">
                        <label for="edit_subtopic_python_id">Topic:</label>
                        <select id="edit_subtopic_python_id" name="python_id" required>
                            <option value="">-- Select a Topic --</option>
                            <?php foreach ($topics as $topic): ?>
                                <option value="<?php echo htmlspecialchars($topic['python_id']); ?>">
                                    <?php echo htmlspecialchars($topic['topic']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    
                        <label for="edit_subtopic_title">Subtopic Title:</label>
                        <input type="text" id="edit_subtopic_title" name="subtopic_title" required>
                
                        <label for="edit_subtopic_content">Content:</label>
                        <textarea id="edit_subtopic_content" name="subtopic_content" required></textarea>
                  
                        <label for="edit_subtopic_example">Example:</label>
                        <textarea id="edit_subtopic_example" name="subtopic_example" class="code"></textarea>
                   
                        <label for="edit_subtopic_question">Question:</label>
                        <textarea id="edit_subtopic_question" name="subtopic_question"></textarea>

                        <label for="edit_subtopic_answer">Answer:</label>
                        <textarea id="edit_subtopic_answer" name="subtopic_answer"></textarea>
                    </div>
                    <button type="submit" name="edit_subtopic" class="btn-primary">Save Changes</button>
                    <button type="button" class="back-button" id="back-from-edit-subtopic">Back</button>
                    <button type="button" class="cancel-button" id="cancel-edit-subtopic">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Pass PHP data to JavaScript
        const allTopics = <?php echo json_encode($topics); ?>;
        const allSubtopics = <?php echo json_encode($subtopics); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            const mainButtons = document.getElementById('main-buttons');
            const topicFormBtn = document.getElementById('show-topic-form');
            const editTopicFormBtn = document.getElementById('show-edit-topic-form');
            const subtopicFormBtn = document.getElementById('show-subtopic-form');
            const editSubtopicFormBtn = document.getElementById('show-edit-subtopic-form');
            
            const topicForm = document.getElementById('topic-form');
            const editTopicForm = document.getElementById('edit-topic-form');
            const subtopicForm = document.getElementById('subtopic-form');
            const editSubtopicForm = document.getElementById('edit-subtopic-form');
            
            const cancelTopicBtn = document.getElementById('cancel-topic');
            const cancelEditTopicBtn = document.getElementById('cancel-edit-topic');
            const backFromEditTopicBtn = document.getElementById('back-from-edit-topic');
            const cancelSubtopicBtn = document.getElementById('cancel-subtopic');
            const cancelEditSubtopicBtn = document.getElementById('cancel-edit-subtopic');
            const backFromEditSubtopicBtn = document.getElementById('back-from-edit-subtopic');
            
            const editTopicSelect = document.getElementById('edit_topic_select');
            const editTopicDetails = document.getElementById('edit-topic-details');
            const editSubtopicSelect = document.getElementById('edit_subtopic_select');
            const editSubtopicDetails = document.getElementById('edit-subtopic-details');
            
            let currentForm = null;
            let formHasChanges = false;
            
            // Auto-hide messages after 5 seconds
            const successMessage = document.getElementById('success-message');
            const errorMessage = document.getElementById('error-message');
            
            function hideMessage(element) {
                if (element) {
                    element.classList.add('fade-out');
                    setTimeout(() => {
                        element.style.display = 'none';
                    }, 300);
                }
            }
            
            if (successMessage) {
                setTimeout(() => {
                    hideMessage(successMessage);
                }, 5000);
            }
            
            if (errorMessage) {
                setTimeout(() => {
                    hideMessage(errorMessage);
                }, 5000);
            }
            
            // Show main buttons and hide all forms
            function showMainMenu() {
                mainButtons.style.display = 'flex';
                topicForm.classList.remove('active');
                editTopicForm.classList.remove('active');
                subtopicForm.classList.remove('active');
                editSubtopicForm.classList.remove('active');
                currentForm = null;
                formHasChanges = false;
            }
            
            // Show topic form
            function showTopicForm() {
                mainButtons.style.display = 'none';
                topicForm.classList.add('active');
                editTopicForm.classList.remove('active');
                subtopicForm.classList.remove('active');
                editSubtopicForm.classList.remove('active');
                currentForm = 'topic';
                formHasChanges = false;
                
                // Reset form
                document.getElementById('topic-form-data').reset();
                
                // Reset change tracking
                const inputs = topicForm.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    input.addEventListener('input', () => {
                        formHasChanges = true;
                    });
                });
            }
            
            // Show edit topic form
            function showEditTopicForm() {
                mainButtons.style.display = 'none';
                editTopicForm.classList.add('active');
                topicForm.classList.remove('active');
                subtopicForm.classList.remove('active');
                editSubtopicForm.classList.remove('active');
                currentForm = 'edit-topic';
                formHasChanges = false;
                
                // Reset form
                document.getElementById('edit-topic-form-data').reset();
                editTopicDetails.style.display = 'none';
                
                // Reset change tracking
                const inputs = editTopicForm.querySelectorAll('input, textarea, select');
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
                editTopicForm.classList.remove('active');
                editSubtopicForm.classList.remove('active');
                currentForm = 'subtopic';
                formHasChanges = false;
                
                // Reset form
                document.getElementById('subtopic-form-data').reset();
                
                // Reset change tracking
                const inputs = subtopicForm.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    input.addEventListener('input', () => {
                        formHasChanges = true;
                    });
                });
            }
            
            // Show edit subtopic form
            function showEditSubtopicForm() {
                mainButtons.style.display = 'none';
                editSubtopicForm.classList.add('active');
                topicForm.classList.remove('active');
                editTopicForm.classList.remove('active');
                subtopicForm.classList.remove('active');
                currentForm = 'edit-subtopic';
                formHasChanges = false;
                
                // Reset form
                document.getElementById('edit-subtopic-form-data').reset();
                editSubtopicDetails.style.display = 'none';
                
                // Reset change tracking
                const inputs = editSubtopicForm.querySelectorAll('input, textarea, select');
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
            
            // Event listeners for main buttons
            topicFormBtn.addEventListener('click', showTopicForm);
            editTopicFormBtn.addEventListener('click', showEditTopicForm);
            subtopicFormBtn.addEventListener('click', showSubtopicForm);
            editSubtopicFormBtn.addEventListener('click', showEditSubtopicForm);
            
            // Event listeners for cancel buttons
            cancelTopicBtn.addEventListener('click', function() {
                if (confirmLeaveForm()) {
                    showMainMenu();
                }
            });
            
            cancelEditTopicBtn.addEventListener('click', function() {
                if (confirmLeaveForm()) {
                    showMainMenu();
                }
            });
            
            backFromEditTopicBtn.addEventListener('click', function() {
                if (confirmLeaveForm()) {
                    editTopicDetails.style.display = 'none';
                    document.getElementById('edit_topic_select').value = '';
                    formHasChanges = false;
                }
            });
            
            cancelSubtopicBtn.addEventListener('click', function() {
                if (confirmLeaveForm()) {
                    showMainMenu();
                }
            });
            
            cancelEditSubtopicBtn.addEventListener('click', function() {
                if (confirmLeaveForm()) {
                    showMainMenu();
                }
            });
            
            backFromEditSubtopicBtn.addEventListener('click', function() {
                if (confirmLeaveForm()) {
                    editSubtopicDetails.style.display = 'none';
                    document.getElementById('edit_subtopic_select').value = '';
                    formHasChanges = false;
                }
            });
            
            //buttons that are below the dropdown list from edit topics and edit subtopics
            function showDropdownBackButton() {
                document.getElementById('back-from-edit-topic-dropdown').style.display = 'block';
                document.getElementById('back-from-edit-subtopic-dropdown').style.display = 'block';
            }

            function hideDropdownBackButton() {
                document.getElementById('back-from-edit-topic-dropdown').style.display = 'none';
                document.getElementById('back-from-edit-subtopic-dropdown').style.display = 'none';
            }

            document.getElementById('back-from-edit-topic-dropdown').addEventListener('click', function() {
                if (confirmLeaveForm()) {
                    showMainMenu();
                }
            });

            document.getElementById('back-from-edit-subtopic-dropdown').addEventListener('click', function() {
                if (confirmLeaveForm()) {
                    showMainMenu();
                }
            });
            
            // Load topic data when selected for editing
            editTopicSelect.addEventListener('change', function() {
                const pythonId = parseInt(this.value);
                
                if (!pythonId) {
                    editTopicDetails.style.display = 'none';
                    showDropdownBackButton(); 
                    return;
                }
                
                // Find the topic in our preloaded data
                const topic = allTopics.find(t => t.python_id == pythonId);
                
                if (!topic) {
                    alert('Topic not found in local data');
                    return;
                }
                
                // Populate form fields
                document.getElementById('edit_python_id').value = topic.python_id;
                document.getElementById('edit_topic_title').value = topic.topic;
                document.getElementById('edit_topic_content').value = topic.content || '';
                document.getElementById('edit_topic_example').value = topic.example || '';
                document.getElementById('edit_topic_question').value = topic.question || '';
                document.getElementById('edit_topic_answer').value = topic.answer || '';
                
                // Show the form
                editTopicDetails.style.display = 'block';
                hideDropdownBackButton(); 
                formHasChanges = false;
            });

            backFromEditTopicBtn.addEventListener('click', function() {
                if (confirmLeaveForm()) {
                    editTopicDetails.style.display = 'none';
                    document.getElementById('edit_topic_select').value = '';
                    showDropdownBackButton();
                    formHasChanges = false;
                }
            });

            // Load subtopic data when selected for editing
            editSubtopicSelect.addEventListener('change', function() {
                const subtopicId = parseInt(this.value);
                
                if (!subtopicId) {
                    editSubtopicDetails.style.display = 'none';
                    showDropdownBackButton(); 
                    return;
                }
                
                // Find the subtopic in our preloaded data
                const subtopic = allSubtopics.find(s => s.subtopic_id == subtopicId);
                
                if (!subtopic) {
                    alert('Subtopic not found in local data');
                    return;
                }
                
                // Populate form fields
                document.getElementById('edit_subtopic_id').value = subtopic.subtopic_id;
                document.getElementById('edit_subtopic_python_id').value = subtopic.python_id;
                document.getElementById('edit_subtopic_title').value = subtopic.subtopic_title;
                document.getElementById('edit_subtopic_content').value = subtopic.content || '';
                document.getElementById('edit_subtopic_example').value = subtopic.example || '';
                document.getElementById('edit_subtopic_question').value = subtopic.question || '';
                document.getElementById('edit_subtopic_answer').value = subtopic.answer || '';
                
                // Show the form
                editSubtopicDetails.style.display = 'block';
                hideDropdownBackButton(); 
                formHasChanges = false;
            });

            backFromEditSubtopicBtn.addEventListener('click', function() {
                if (confirmLeaveForm()) {
                    editSubtopicDetails.style.display = 'none';
                    document.getElementById('edit_subtopic_select').value = '';
                    showDropdownBackButton();
                    formHasChanges = false;
                }
            });
            
            // Initialize with main menu
            showMainMenu();
        });
    </script>
</body>
</html>