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

$conn = db_connect();

    // Add challenge
    if (isset($_POST['add_challenge'])) {
        $title = $_POST['title'];
        $category = $_POST['category'];
        $difficulty = $_POST['difficulty'];

        $stmt = $conn->prepare("INSERT INTO challenges (title, category, difficulty, solves) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("sss", $title, $category, $difficulty);
        $stmt->execute();
        $stmt->close();
    }

    // Delete challenge
    if (isset($_POST['delete_challenge'])) {
        $id = $_POST['challenge_id'];
        $stmt = $conn->prepare("DELETE FROM challenges WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }


// Fetch all challenges
$allChallenges = $conn->query("SELECT * FROM challenges ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin CTF Panel</title>
    <link rel="stylesheet" href="admin_ctf.css">
    <style>
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
    <nav class="fixed-top"><?php include 'navbar.php' ?></nav>
    
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

    <header>
        <h1>CTF Admin Panel</h1>
    </header>

    <!-- ADMIN PANEL START -->
    <section class="admin-panel">
        <h2>Add New Challenge</h2>
        <form method="POST" class="add-form">
            <input type="text" name="title" placeholder="Challenge Title" required>
            <select name="category" required>
                <option value="Web Exploitation">Web Exploitation</option>
                <option value="Cryptography">Cryptography</option>
                <option value="Reverse Engineering">Reverse Engineering</option>
                <option value="Forensics">Forensics</option>
                <option value="General Skills">General Skills</option>
            </select>
            <select name="difficulty" required>
                <option value="Easy">Easy</option>
                <option value="Medium">Medium</option>
                <option value="Hard">Hard</option>
            </select>
            <button type="submit" name="add_challenge">Add Challenge</button>
        </form>

        <h2>Delete Challenges</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Title</th><th>Category</th><th>Difficulty</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($ch = $allChallenges->fetch_assoc()): ?>
                <tr>
                    <td><?= $ch['id'] ?></td>
                    <td><?= htmlspecialchars($ch['title']) ?></td>
                    <td><?= htmlspecialchars($ch['category']) ?></td>
                    <td><?= htmlspecialchars($ch['difficulty']) ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Delete this challenge?');">
                            <input type="hidden" name="challenge_id" value="<?= $ch['id'] ?>">
                            <button type="submit" name="delete_challenge" class="delete-btn">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
    <!-- ADMIN PANEL END -->

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
