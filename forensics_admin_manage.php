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
    <link rel="stylesheet" href="mstyles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa;  padding-top: 70px;}
        .table td, .table th { vertical-align: middle; }
        .section-header {
            background: linear-gradient(135deg, #4a47a3, #709fb0);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .card { box-shadow: 0 3px 8px rgba(0,0,0,0.1); border: none; }
        .tab-content { margin-top: 2rem; }

        .message {
            position: fixed;
            top: 0px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            max-width: 80%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            animation: fadeInOut 3s ease-in-out forwards;
        }
        
        @keyframes fadeInOut {
            0% { opacity: 0; }
            10% { opacity: 1; }  /* Quickly fade in */
            90% { opacity: 1; }  /* Stay visible */
            100% { opacity: 0; visibility: hidden; } /* Fade out */
        }
    </style>
</head>
<body>

<?php
// Fetch user data for navbar
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
}
?>

<nav class="fixed-top">
    <?php include 'navbar.php'; ?>
</nav>

<div class="container mt-4">
    <div class="section-header text-center">
        <h2>Admin Challenge Management</h2>
        <p class="mb-0">Manage all Forensics and Cryptography questions here.</p>
    </div>

   <?php if (!empty($message['successful'])): ?>
        <div class="alert alert-success alert-dismissible fade show message" role="alert">
            <?php echo htmlspecialchars($message['successful']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($message['unsuccessful'])): ?>
        <div class="alert alert-danger alert-dismissible fade show message" role="alert">
            <?php echo htmlspecialchars($message['unsuccessful']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#forensicsTab">🔍 Forensics</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#cryptoTab">🔐 Cryptography</button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Forensics Tab -->
        <div class="tab-pane fade show active" id="forensicsTab">
            <div class="d-flex justify-content-end mt-3">
                <a href="forensics_admin_edit.php?table=forensics" class="btn btn-primary">➕ Add Forensics Challenge</a>
            </div>
            <div class="card mt-3 p-3">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Question</th>
                            <th>Difficulty</th>
                            <th>Type</th>
                            <th>Options</th>
                            <th>Answer</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($forensics)): ?>
                        <tr><td colspan="6" class="text-center">No forensics challenges found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($forensics as $q): ?>
                        <tr>
                            <td><?= htmlspecialchars($q['question_text']) ?></td>
                            <td><?= htmlspecialchars($q['difficulty']) ?></td>
                            <td><?= htmlspecialchars($q['question_type']) ?></td>
                            <td>
                                <?php if ($q['question_type'] === 'MCQ' && !empty($q['options'])):
                                    $opts = json_decode($q['options'], true);
                                    if (is_array($opts)):
                                        foreach ($opts as $label => $opt):
                                            echo "<strong>$label:</strong> " . htmlspecialchars($opt) . "<br>";
                                        endforeach;
                                    endif;
                                else: ?>
                                    <em>N/A</em>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($q['correct_answer']) ?></td>
                            <td>
                                <a href="forensics_admin_edit.php?table=forensics&id=<?= $q['question_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="?table=forensics&delete=<?= $q['question_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this challenge?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Crypto Tab -->
        <div class="tab-pane fade" id="cryptoTab">
            <div class="d-flex justify-content-end mt-3">
                <a href="forensics_admin_edit.php?table=crypto" class="btn btn-primary">➕ Add Crypto Challenge</a>
            </div>
            <div class="card mt-3 p-3">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Question</th>
                            <th>Difficulty</th>
                            <th>Type</th>
                            <th>Options</th>
                            <th>Answer</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($crypto)): ?>
                        <tr><td colspan="6" class="text-center">No cryptography challenges found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($crypto as $q): ?>
                        <tr>
                            <td><?= htmlspecialchars($q['question_text']) ?></td>
                            <td><?= htmlspecialchars($q['difficulty']) ?></td>
                            <td><?= htmlspecialchars($q['question_type']) ?></td>
                            <td>
                                <?php if ($q['question_type'] === 'MCQ' && !empty($q['options'])):
                                    $opts = json_decode($q['options'], true);
                                    if (is_array($opts)):
                                        foreach ($opts as $label => $opt):
                                            echo "<strong>$label:</strong> " . htmlspecialchars($opt) . "<br>";
                                        endforeach;
                                    endif;
                                else: ?>
                                    <em>N/A</em>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($q['correct_answer']) ?></td>
                            <td>
                                <a href="forensics_admin_edit.php?table=crypto&id=<?= $q['question_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="?table=crypto&delete=<?= $q['question_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this challenge?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
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
</script>
</body>
</html>
