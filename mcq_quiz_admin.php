<?php
session_start();
require 'config.php';

$message = [
    'successful' => $_SESSION['message']['successful'] ?? '',
    'unsuccessful' => $_SESSION['message']['unsuccessful'] ?? ''
];

// Clear messages after displaying
unset($_SESSION['message']);

$conn = db_connect();

// Security: Only admins allowed
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$user_data = null;
if (isset($_SESSION['user_id'])) {
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

// Fetch all questions, categories, and levels
$questions = $conn->query("
    SELECT q.*, c.category_name 
    FROM questions q
    LEFT JOIN categories c ON q.category_id = c.category_id
    ORDER BY q.question_id DESC
")->fetch_all(MYSQLI_ASSOC);

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name")->fetch_all(MYSQLI_ASSOC);
$levels = $conn->query("SELECT level_id, level_name FROM levels GROUP BY level_id, level_name ORDER BY level_id")->fetch_all(MYSQLI_ASSOC);

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>MCQ Learning Admin Panel</title>
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
            10% { opacity: 1; }  /* Quickly fade in */
            90% { opacity: 1; }  /* Stay visible */
            100% { opacity: 0; visibility: hidden; } /* Fade out */
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
        }

        /* Content preview styles */
        .question-preview {
            display: inline-block;
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: middle;
            margin-right: 8px;
        }

        .view-content, .view-options {
            vertical-align: middle;
            margin-left: 5px;
        }

        /* Modal content styles */
        .options-container {
            padding: 10px;
        }

        .options-preview {
            display: inline-block;
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: middle;
            margin-right: 8px;
        }
        .option-item {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .option-item:last-child {
            border-bottom: none;
        }

        .modal-body pre {
            white-space: pre-wrap;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <nav class="fixed-top">
        <?php include 'navbar.php'; ?>
    </nav>

    <div class="admin-container">
        <div class="section-header">
            <h2>MCQ Learning Admin Panel</h2>
            <p class="mb-0">Manage all MCQ Questions and Categories here.</p>
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
                <button class="nav-link active" id="questions-tab" data-bs-toggle="tab" data-bs-target="#questions" type="button" role="tab" aria-controls="questions" aria-selected="true">
                    📚 Questions
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories" type="button" role="tab" aria-controls="categories" aria-selected="false">
                    📝 Categories
                </button>
            </li>
        </ul>

        <div class="tab-content" id="adminTabContent">
            <div class="tab-pane fade show active" id="questions" role="tabpanel" aria-labelledby="questions-tab">
                <div class="row align-items-center my-3">
                    <div class="col-md-5">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search questions..." />
                    </div>
                    <div class="col-md-4">
                        <select id="categoryFilter" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['category_name']) ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 text-end">
                        <a href="add_mcq_questions.php" class="btn btn-primary" style="min-width:100px;">➕ Add Topic</a>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="questionsTable">
                            <thead>
                                <tr>
                                    <th>Question Name</th>
                                    <th>Question Category</th>
                                    <th>Options</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($questions)): ?>
                                    <tr><td colspan="3" class="no-data">No questions found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($questions as $question): ?>
                                        <tr>
                                            <td>
                                                <div class="question-preview">
                                                    <?= htmlspecialchars(substr($question['question_text'], 0, 50)) ?>
                                                    <?php if (strlen($question['question_text']) > 50): ?>...<?php endif; ?>
                                                </div>
                                                <button class="btn btn-sm btn-info view-content" 
                                                        data-title="Question" 
                                                        data-content="<?= htmlspecialchars($question['question_text']) ?>">
                                                    View
                                                </button>
                                            </td>
                                            <td><?= htmlspecialchars($question['category_name'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php if (!empty($question['options'])): ?>
                                                    <?php 
                                                        $options = json_decode($question['options'], true);
                                                        if (is_array($options) && !empty($options)):
                                                    ?>
                                                        <div class="options-preview">
                                                            <?= htmlspecialchars(substr(implode(', ', $options), 0, 30)) ?>
                                                            <?php if (strlen(implode(', ', $options)) > 30): ?>...<?php endif; ?>
                                                        </div>
                                                        <button class="btn btn-sm btn-info view-options" 
                                                                data-options='<?= htmlspecialchars($question['options'], ENT_QUOTES) ?>'>
                                                            View
                                                        </button>
                                                    <?php else: ?>
                                                        <em>No options</em>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <em>No options</em>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="edit_mcq_questions.php?id=<?= $question['question_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                                <a href="delete_mcq_questions.php?id=<?= $question['question_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this question?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="categories" role="tabpanel" aria-labelledby="categories-tab">
                <div class="d-flex justify-content-end my-3">
                    <a href="add_mcq_category.php" class="btn btn-primary">➕ Add Category</a>
                </div>

                <div class="card mt-3">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="categoriesTable">
                            <thead>
                                <tr>
                                    <th>Category Name</th>
                                    <th>Category Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categories)): ?>
                                    <tr><td colspan="3" class="no-data">No categories found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($category['category_name']) ?></td>
                                            <td><?= htmlspecialchars($category['category_description']) ?></td>
                                            <td>
                                                <a href="edit_mcq_category.php?id=<?= $category['category_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                                <a href="delete_mcq_category.php?id=<?= $category['category_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
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

    <!-- Modal for viewing content -->
    <div class="modal fade" id="contentModal" tabindex="-1" aria-labelledby="contentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contentModalLabel">Content Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contentModalBody">
                    <!-- Content will be inserted here by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Optional JavaScript -->

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const table = document.getElementById('questionsTable');
            
            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase();
                const categoryValue = categoryFilter.value.toLowerCase();
                const tbody = table.tBodies[0];
                Array.from(tbody.rows).forEach(row => {
                    const questionText = row.cells[0].textContent.toLowerCase();
                    const categoryText = row.cells[1].textContent.toLowerCase();
                    
                    const matchesSearch = questionText.includes(searchTerm);
                    const matchesCategory = categoryValue === '' || categoryText === categoryValue;
                    
                    row.style.display = matchesSearch && matchesCategory ? '' : 'none';
                });
            }
            
            searchInput.addEventListener('input', filterTable);
            categoryFilter.addEventListener('change', filterTable);
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Remove message elements after animation completes
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                // Auto-remove after animation
                setTimeout(() => {
                    message.remove();
                }, 3000);
               
                // Handle manual close
                const closeBtn = message.querySelector('.btn-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        message.style.animation = 'none';
                        message.remove();
                    });
                }
            });
        });

        // Handle view content button clicks
        document.addEventListener('DOMContentLoaded', function() {
            const contentModal = new bootstrap.Modal(document.getElementById('contentModal'));
            const contentModalBody = document.getElementById('contentModalBody');
            const contentModalLabel = document.getElementById('contentModalLabel');
            
            // View question text
            document.querySelectorAll('.view-content').forEach(button => {
                button.addEventListener('click', function() {
                    const title = this.getAttribute('data-title');
                    const content = this.getAttribute('data-content');
                    
                    contentModalLabel.textContent = title;
                    contentModalBody.innerHTML = `<p>${content}</p>`;
                    contentModal.show();
                });
            });
            
            // View options (if you have MCQ options to display)
            document.querySelectorAll('.view-options').forEach(button => {
                button.addEventListener('click', function() {
                    const options = JSON.parse(this.getAttribute('data-options'));
                    let html = '<div class="options-container">';
                    
                    for (const [label, value] of Object.entries(options)) {
                        html += `<div class="option-item"><strong>${label}:</strong> ${htmlspecialchars(value)}</div>`;
                    }
                    
                    html += '</div>';
                    contentModalLabel.textContent = 'Question Options';
                    contentModalBody.innerHTML = html;
                    contentModal.show();
                });
            });
            
            // Simple HTML entities encoder for JavaScript
            function htmlspecialchars(str) {
                return str.replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;")
                        .replace(/"/g, "&quot;")
                        .replace(/'/g, "&#039;");
            }
        });
    </script>
</body>
</html>
