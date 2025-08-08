<?php
session_start();
include 'config.php';

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
if (!$conn) {
    die("Database connection failed.");
}

$user_data = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT user_id, name, email FROM users WHERE user_id = ?");
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

$id = (int) $_GET['id'] ?? 'All';
$result = $conn->query("SELECT * FROM challenges WHERE id = $id");
$challenge = $result->fetch_assoc();

if (!$challenge) {
    echo "<p>Challenge not found.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($challenge['title']) ?> - Cybersite</title>
    <link rel="stylesheet" href="test.css">
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

    <a href="ctf_challenge.php" class="back-button">← Back to Challenges</a>

    <h2><?= htmlspecialchars($challenge['title']) ?></h2>
    <p><?= nl2br(htmlspecialchars($challenge['description'])) ?></p>
    <p><?= nl2br(htmlspecialchars($challenge['question'])) ?></p>

    <form method="POST" action="submit_flag.php">
        <input type="hidden" name="cid" value="<?= $challenge['id'] ?>">
        <input name="flag" placeholder="Enter flag..." required>
        <input type="submit" value="Submit">
    </form>

    <?php if ($challenge['id'] == 29): ?>
        <p><strong>Challenge Files:</strong></p>
        <ul>
            <li><a href="csrf_token_bypass.php" target="_blank">🔒 CSRF-Protected Email Change Page</a></li>
            <li><a href="csrf_attack.html" target="_blank">🎯 Simulated CSRF Attack Page</a></li>
        </ul>

    <?php elseif ($challenge['id'] == 32): ?>
        <p><strong>Challenge Files:</strong></p>
        <ul>
            <li><a href="index.php" target="_blank">📤 Upload Madness Page</a></li>
        </ul>

    <?php elseif ($challenge['id'] == 33): ?>
        <p><strong>Challenge Files:</strong></p>
        <ul>
            <li><a href="supersecret" target="_blank">🤖 Robots hide things in plain text...</a></li>
            <li><a href="robots.txt" target="_blank">🎯 File path of the challenges flag...</a></li>
        </ul>
        <p>🕵️‍♂️ Hint: Bots have their own roadmap... maybe in the /flag.txt?</p>

    <?php else: ?>
        <p><em>No downloadable files for this challenge.</em></p>
    <?php endif; ?>

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