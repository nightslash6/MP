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

$message = [
    'successful' => $_SESSION['message']['successful'] ?? '',
    'unsuccessful' => $_SESSION['message']['unsuccessful'] ?? ''
];

// Clear the messages after displaying them
unset($_SESSION['message']);

// Delete logic
if (isset($_GET['delete'], $_GET['table'])) {
    if (in_array($_GET['table'], ['forensics', 'crypto'])) {
        $table = $_GET['table'] === 'crypto' ? 'my_crypto_questions' : 'my_forensics_questions';
        $id = (int)$_GET['delete'];
        
        try {
            $stmt = $conn->prepare("DELETE FROM `$table` WHERE question_id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = ['successful' => "Question deleted successfully."];
            } else {
                $_SESSION['message'] = ['unsuccessful' => "Error deleting question."];
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $_SESSION['message'] = ['unsuccessful' => "Database error: " . $e->getMessage()];
        }
        
        header("Location: forensics_admin_manage.php");
        exit;
    }
}

// Fetch questions
$forensics = $conn->query("SELECT * FROM my_forensics_questions ORDER BY question_id DESC")->fetch_all(MYSQLI_ASSOC);
$crypto = $conn->query("SELECT * FROM my_crypto_questions ORDER BY question_id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Forensics & Crypto Challenges</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="mstyles.css" />
    <style>
        body {
            background: #f5f7fa;
            padding-top: 70px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .section-header {
            background: linear-gradient(135deg, #4a47a3, #709fb0);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }
        .card {
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            border: none;
            border-radius: 10px;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        .table thead tr {
            background-color: #f8f9fa;
        }
        .table th, .table td {
            vertical-align: middle;
            padding: 12px 15px;
        }
        .nav-tabs .nav-link {
            border: none;
            color: #495057;
            font-weight: 500;
            padding: 10px 20px;
            cursor: pointer;
        }
        .nav-tabs .nav-link.active {
            color: #4a47a3;
            border-color: #4a47a3 #4a47a3 transparent;
            border-bottom: 3px solid #4a47a3;
            background-color: transparent;
        }
        .btn-purple {
            background-color: #5a3e9e;
            color: #fff;
            border: none;
        }
        .btn-purple:hover {
            background-color: #4a2f87;
        }
        .message {
            position: fixed;
            top: 0px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            max-width: 80%;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        @keyframes fadeInOut {
            0% { opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { opacity: 0; visibility: hidden; }
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
        }
        .question-preview {
            display: inline-block;
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .answer-preview {
            display: inline-block;
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .options-preview {
            display: inline-block;
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .modal-body .question-details {
            margin-bottom: 20px;
        }
        .modal-body .question-details h5 {
            margin-bottom: 10px;
            color: #4a47a3;
        }
        .option-item {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .option-item:last-child {
            border-bottom: none;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <nav class="fixed-top">
        <?php include 'navbar.php'; ?>
    </nav>

    <div class="admin-container">
        <div class="section-header">
            <h2>Forensics and Cryptography Learning Admin Panel</h2>
            <p class="mb-0">Manage all Forensics and Cryptography challenges here.</p>
        </div>

        <?php if ($message['successful']): ?>
            <div class="alert alert-success alert-dismissible fade show message" role="alert">
                <?= htmlspecialchars($message['successful']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($message['unsuccessful']): ?>
            <div class="alert alert-danger alert-dismissible fade show message" role="alert">
                <?= htmlspecialchars($message['unsuccessful']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <ul class="nav nav-tabs" id="adminTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="forensics-tab" data-bs-toggle="tab" data-bs-target="#forensics" type="button" role="tab" aria-controls="forensics" aria-selected="true">
                    🔍 Forensics
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="crypto-tab" data-bs-toggle="tab" data-bs-target="#crypto" type="button" role="tab" aria-controls="crypto" aria-selected="false">
                    🔐 Cryptography
                </button>
            </li>
        </ul>

        <div class="tab-content" id="adminTabContent">
            <div class="tab-pane fade show active" id="forensics" role="tabpanel" aria-labelledby="forensics-tab">
                <div class="d-flex justify-content-end my-3">
                    <a href="forensics_admin_edit.php?table=forensics" class="btn btn-primary">➕ Add Forensics Challenge</a>
                </div>
                <div class="card mt-3">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Question</th>
                                    <th>Difficulty</th>
                                    <th>Type</th>
                                    <th>Answer</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($forensics)): ?>
                                    <tr><td colspan="5" class="no-data">No forensics challenges found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($forensics as $q): ?>
                                        <tr>
                                            <td>
                                                <div class="question-preview">
                                                    <?= htmlspecialchars(substr($q['question_text'], 0, 50)) ?>
                                                    <?php if (strlen($q['question_text']) > 50): ?>...<?php endif; ?>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($q['difficulty']) ?></td>
                                            <td><?= htmlspecialchars($q['question_type']) ?></td>
                                            <td>
                                                <div class="answer-preview">
                                                    <?= htmlspecialchars(substr($q['correct_answer'], 0, 30)) ?>
                                                    <?php if (strlen($q['correct_answer']) > 30): ?>...<?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn btn-sm btn-info view-details" 
                                                        data-question="<?= htmlspecialchars($q['question_text']) ?>"
                                                        data-difficulty="<?= htmlspecialchars($q['difficulty']) ?>"
                                                        data-type="<?= htmlspecialchars($q['question_type']) ?>"
                                                        data-options='<?= htmlspecialchars($q['options'] ?? '[]', ENT_QUOTES) ?>'
                                                        data-answer="<?= htmlspecialchars($q['correct_answer']) ?>">
                                                        View
                                                    </button>
                                                    <a href="forensics_admin_edit.php?table=forensics&id=<?= $q['question_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                                    <a href="?table=forensics&delete=<?= $q['question_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this challenge?')">Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="crypto" role="tabpanel" aria-labelledby="crypto-tab">
                <div class="d-flex justify-content-end my-3">
                    <a href="forensics_admin_edit.php?table=crypto" class="btn btn-primary">➕ Add Crypto Challenge</a>
                </div>
                <div class="card mt-3">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Question</th>
                                    <th>Difficulty</th>
                                    <th>Type</th>
                                    <th>Answer</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($crypto)): ?>
                                    <tr><td colspan="5" class="no-data">No cryptography challenges found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($crypto as $q): ?>
                                        <tr>
                                            <td>
                                                <div class="question-preview">
                                                    <?= htmlspecialchars(substr($q['question_text'], 0, 50)) ?>
                                                    <?php if (strlen($q['question_text']) > 50): ?>...<?php endif; ?>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($q['difficulty']) ?></td>
                                            <td><?= htmlspecialchars($q['question_type']) ?></td>
                                            <td>
                                                <div class="answer-preview">
                                                    <?= htmlspecialchars(substr($q['correct_answer'], 0, 30)) ?>
                                                    <?php if (strlen($q['correct_answer']) > 30): ?>...<?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn btn-sm btn-info view-details" 
                                                        data-question="<?= htmlspecialchars($q['question_text']) ?>"
                                                        data-difficulty="<?= htmlspecialchars($q['difficulty']) ?>"
                                                        data-type="<?= htmlspecialchars($q['question_type']) ?>"
                                                        data-options='<?= htmlspecialchars($q['options'] ?? '[]', ENT_QUOTES) ?>'
                                                        data-answer="<?= htmlspecialchars($q['correct_answer']) ?>">
                                                        View
                                                    </button>
                                                    <a href="forensics_admin_edit.php?table=crypto&id=<?= $q['question_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                                    <a href="?table=crypto&delete=<?= $q['question_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this challenge?')">Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for viewing question details -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">Challenge Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailsModalBody">
                    <div class="question-details">
                        <h5>Question:</h5>
                        <p id="modal-question"></p>
                    </div>
                    <div class="question-details">
                        <h5>Difficulty:</h5>
                        <p id="modal-difficulty"></p>
                    </div>
                    <div class="question-details">
                        <h5>Type:</h5>
                        <p id="modal-type"></p>
                    </div>
                    <div class="question-details">
                        <h5>Options:</h5>
                        <div id="modal-options" class="options-container"></div>
                    </div>
                    <div class="question-details">
                        <h5>Answer:</h5>
                        <p id="modal-answer"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Remove message elements after animation completes
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                setTimeout(() => {
                    message.remove();
                }, 3000);
                const closeBtn = message.querySelector('.btn-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        message.style.animation = 'none';
                        message.remove();
                    });
                }
            });

            // Handle view details button clicks
            const detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'));
            const modalQuestion = document.getElementById('modal-question');
            const modalDifficulty = document.getElementById('modal-difficulty');
            const modalType = document.getElementById('modal-type');
            const modalOptions = document.getElementById('modal-options');
            const modalAnswer = document.getElementById('modal-answer');
            
            document.querySelectorAll('.view-details').forEach(button => {
                button.addEventListener('click', function() {
                    const question = this.getAttribute('data-question');
                    const difficulty = this.getAttribute('data-difficulty');
                    const type = this.getAttribute('data-type');
                    const options = JSON.parse(this.getAttribute('data-options'));
                    const answer = this.getAttribute('data-answer');
                    
                    modalQuestion.textContent = question;
                    modalDifficulty.textContent = difficulty;
                    modalType.textContent = type;
                    modalAnswer.textContent = answer;
                    
                    let optionsHtml = '';
                    if (options && options.length > 0 && type === 'MCQ') {
                        options.forEach((value, index) => {
                            optionsHtml += `<div class="option-item"><strong>${index + 1}:</strong> ${escapeHtml(value)}</div>`;
                        });
                    } else if (options && Object.keys(options).length > 0 && type === 'MCQ') {
                        Object.values(options).forEach((value, index) => {
                            optionsHtml += `<div class="option-item"><strong>${index + 1}:</strong> ${escapeHtml(value)}</div>`;
                        });
                    } else {
                        optionsHtml = '<em>No options available</em>';
                    }
                    modalOptions.innerHTML = optionsHtml;
                    detailsModal.show();
                });
            });
            
            function escapeHtml(str) {
                if (!str) return '';
                return String(str).replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;")
                        .replace(/"/g, "&quot;")
                        .replace(/'/g, "&#039;");
            }
        });
    </script>
</body>
</html>