<?php
session_start();
require 'config.php';

// Session timeout handling - only if user is logged in
$timeout = 1800; //30 minutes
$timeout_warning = 1680; // 28 minutes ---- modal shows for 2 minutes

// Check if user is logged in
$user_logged_in = isset($_SESSION['user_id']);

// Check if session should be terminated (only if logged in)
if ($user_logged_in && isset($_SESSION['last_activity'])) {
    $elapsed_time = time() - $_SESSION['last_activity'];
    
    // If timeout reached, destroy session
    if ($elapsed_time > $timeout) {
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

// Update last activity time if logged in
if ($user_logged_in) {
    $_SESSION['last_activity'] = time();
}

$message = [
    'successful' => $_SESSION['message']['successful'] ?? '',
    'unsuccessful' => $_SESSION['message']['unsuccessful'] ?? ''
];

// Clear the messages after displaying them
unset($_SESSION['message']);

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

// Get all topics for the subtopic dropdown
$topics = [];
$stmt = $conn->query("SELECT python_id, topic, content, example, question, answer FROM python ORDER BY python_id ASC");
if ($stmt) {
    $topics = $stmt->fetch_all(MYSQLI_ASSOC);
}

// Get all subtopics for the edit subtopic dropdown
$subtopics = [];
$stmt = $conn->query("SELECT ps.subtopic_id, ps.subtopic_title, p.python_id, p.topic, ps.content, ps.example, ps.question, ps.answer 
                      FROM python_subtopics ps 
                      JOIN python p ON ps.python_id = p.python_id 
                      ORDER BY p.topic");
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        }
        
        .card {
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            border: none;
            border-radius: 10px;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .table td, .table th {
            vertical-align: middle;
            padding: 12px 15px;
        }
        
        .nav-tabs {
            border-bottom: 1px solid #dee2e6;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #495057;
            font-weight: 500;
            padding: 10px 20px;
        }
        
        .nav-tabs .nav-link.active {
            color: #4a47a3;
            background-color: transparent;
            border-bottom: 3px solid #4a47a3;
        }
        
        .tab-content {
            margin-top: 1.5rem;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 14px;
        }
        
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
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
        }

        #sessionTimeoutModal .countdown {
            font-weight: bold;
            color: #dc3545;
        }
        #sessionTimeoutModal {
            z-index: 99999; /* Ensure it's on top of everything */
        }
        .btn-close[disabled] {
            opacity: 0.5;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <nav class="fixed-top"><?php include 'navbar.php'; ?></nav>

    <!-- Session Timeout Modal -->
    <?php if ($user_logged_in): ?>
    <div class="modal fade" id="sessionTimeoutModal" tabindex="-1" aria-labelledby="sessionTimeoutModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="sessionTimeoutModalLabel">Session About to Expire</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" disabled></button>
                </div>
                <div class="modal-body">
                    <p>You have been inactive for 28 minutes. Your session will expire in <span id="countdown">120</span> seconds.</p>
                    <p>Would you like to continue your session?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="logoutBtn">Log Out</button>
                    <button type="button" class="btn btn-primary" id="stayLoggedInBtn">Stay Logged In</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="admin-container">
        <div class="section-header text-center">
            <h2>Python Learning Admin Panel</h2>
            <p class="mb-0">Manage all Python Topics and Subtopics here.</p>
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
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#topicsTab">📚 Topics</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#subtopicsTab">📝 Subtopics</button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Topics Tab -->
            <div class="tab-pane fade show active" id="topicsTab">
                <div class="d-flex justify-content-end mt-3">
                    <a href="add_topic.php" class="btn btn-primary">➕ Add Topic</a>
                </div>
                <div class="card mt-3 p-3">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Topic Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($topics)): ?>
                                <tr><td colspan="2" class="text-center no-data">No topics found.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($topics as $topic): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($topic['topic']); ?></td>
                                    <td>
                                        <a href="edit_topic.php?id=<?php echo $topic['python_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="delete_topic.php?id=<?php echo $topic['python_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this topic?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Subtopics Tab -->
            <div class="tab-pane fade" id="subtopicsTab">
                <div class="d-flex justify-content-end mt-3">
                    <a href="add_subtopic.php" class="btn btn-primary">➕ Add Subtopic</a>
                </div>
                <div class="card mt-3 p-3">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Topic Name</th>
                                <th>Subtopic Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($subtopics)): ?>
                                <tr><td colspan="3" class="text-center no-data">No subtopics found.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($subtopics as $subtopic): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($subtopic['topic']); ?></td>
                                    <td><?php echo htmlspecialchars($subtopic['subtopic_title']); ?></td>
                                    <td>
                                        <a href="edit_subtopic.php?id=<?php echo $subtopic['subtopic_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="delete_subtopic.php?id=<?php echo $subtopic['subtopic_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this subtopic?')">Delete</a>
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

    <!--Session Timeout-->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($user_logged_in): ?>
        // Time settings
        const totalTimeout = <?php echo $timeout; ?> * 1000; // Total session time in ms (1800 * 1000) = 30 min
        const countdownDuration = 120 * 1000; // Countdown in ms = 2 min
        const warningTime = totalTimeout - countdownDuration; // Will show at 28 minutes
        
        let warningTimer;
        let logoutTimer;
        let countdownInterval;
        let modalShown = false;
        
        // Modal elements
        const sessionTimeoutModal = new bootstrap.Modal(document.getElementById('sessionTimeoutModal'), {
            backdrop: 'static',
            keyboard: false
        });
        const countdownElement = document.getElementById('countdown');
        
        // Start timers
        startSessionTimer();
        
        // Activity listeners - won't hide modal once it's shown
        ['click', 'mousemove', 'keypress', 'scroll'].forEach(event => {
            document.addEventListener(event, resetSessionTimer, { passive: true });
        });
        
        function startSessionTimer() {
            warningTimer = setTimeout(showTimeoutWarning, warningTime);
            logoutTimer = setTimeout(forceLogout, totalTimeout);
        }
        
        function resetSessionTimer() {
            // Only reset timers if modal is NOT shown
            if (!modalShown) {
                clearTimeout(warningTimer);
                clearTimeout(logoutTimer);
                startSessionTimer();
                
                fetch('keepalive.php')
                    .then(response => response.json())
                    .then(console.log('Session extended'));
            }
        }
        
        function showTimeoutWarning() {
            modalShown = true;
            let countdown = countdownDuration / 1000;
            countdownElement.textContent = countdown;
            
            sessionTimeoutModal.show();
            
            countdownInterval = setInterval(() => {
                countdown--;
                countdownElement.textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    forceLogout();
                }
            }, 1000);
            
            document.getElementById('stayLoggedInBtn').onclick = () => {
                clearInterval(countdownInterval);
                sessionTimeoutModal.hide();
                modalShown = false;
                resetSessionTimer();
            };
            
            document.getElementById('logoutBtn').onclick = () => {
                clearInterval(countdownInterval);
                window.location.href = 'logout.php';
            };
        }
        
        function forceLogout() {
            window.location.href = 'logout.php?timeout=1';
        }
        <?php endif; ?>
    });
    </script>
</body>
</html>