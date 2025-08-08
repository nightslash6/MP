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

// Fetch user data
$user_data = null;
if (isset($_SESSION['user_id']) && $_SESSION['user_role']==='admin') {
    $stmt = $conn->prepare("SELECT user_id, name, email, user_role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();
    }
    $stmt->close();
}else{
    header("Location: login.php");
    exit;
}
$GLOBALS['user_data'] = $user_data;

// Dashboard metrics
$totalUsers = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="mstyles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #4a47a3, #6f9bb6);
            color: white;
            padding: 2rem;
            border-radius: 10px;
        }
        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.08);
            transition: 0.3s ease;
        }
        .card:hover {
            transform: translateY(-4px);
        }
        .section-title {
            margin-top: 3rem;
            margin-bottom: 1rem;
            font-weight: 600;
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

<?php include 'navbar.php'; ?>

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

<div class="container mt-5 mb-5">
    <div class="dashboard-header mb-4">
        <h1 class="mb-1">Welcome, <?= htmlspecialchars($user_data['name'] ?? 'Admin') ?></h1>
        <p class="mb-0">Manage users, questions, and categories from one place.</p>
    </div>

    <!-- Stats -->
    <div class="row g-4">
        <div class="col-md-20">
            <div class="card text-center py-4">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="display-6"><?= $totalUsers ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories -->
    <h3 class="section-title">Manage Admin Pages</h3>
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card text-center py-4">
                <div class="card-body">
                    <h5 class="card-title">Forensics<br><small>(Shayaan)</small></h5>
                    <a href="forensics_admin_manage.php" class="btn btn-outline-primary mt-2">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center py-4">
                <div class="card-body">
                    <h5 class="card-title">MCQ Quiz<br><small>(Wei Hong)</small></h5>
                    <a href="mcq_quiz_admin.php" class="btn btn-outline-primary mt-2">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center py-4">
                <div class="card-body">
                    <h5 class="card-title">CTF<br><small>(Chee Chong)</small></h5>
                    <a href="admin_ctf.php" class="btn btn-outline-primary mt-2">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center py-4">
                <div class="card-body">
                    <h5 class="card-title">Python<br><small>(Shu Xuan)</small></h5>
                    <a href="admin_python.php" class="btn btn-outline-primary mt-2">Manage</a>
                </div>
            </div>
        </div>
    </div>
</div>

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
