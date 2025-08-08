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

// Check if user is logged in and get user data
$user_data = null;
$user_id = null;
if ($user_logged_in) {
    $stmt = $conn->prepare("SELECT user_id, name, email, user_role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();
        $user_id = $user_data['user_id'];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cybersite</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="mstyles.css">
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
    <script>
    (function(){if(!window.chatbase||window.chatbase("getState")!=="initialized"){window.chatbase=(...arguments)=>{if(!window.chatbase.q){window.chatbase.q=[]}window.chatbase.q.push(arguments)};window.chatbase=new Proxy(window.chatbase,{get(target,prop){if(prop==="q"){return target.q}return(...args)=>target(prop,...args)}})}const onLoad=function(){const script=document.createElement("script");script.src="https://www.chatbase.co/embed.min.js";script.id="eL8-LhqxDLzhQl7IBXcFh";script.domain="www.chatbase.co";document.body.appendChild(script)};if(document.readyState==="complete"){onLoad()}else{window.addEventListener("load",onLoad)}})();
    </script>
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

    <div class="hero">    
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true"  aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
            </div>
            
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="hero-content">
                        <h1>Learn Cybersecurity</h1>
                        <p>Master the fundamentals with our interactive challenges</p>
                        <a href="#" class="read-more-btn">Read More</a>
                    </div>
                </div>
                
                <div class="carousel-item">
                    <div class="hero-content">
                        <h1>Learn Digital Forensics</h1>
                        <p>Contribute to a safer digital space</p>
                        <a href="#" class="read-more-btn">Read More</a>
                    </div>
                </div>
                
                <div class="carousel-item">
                    <div class="hero-content">
                        <h1>Join The Competition</h1>
                        <p>Test your skills against others in our global competitions</p>
                        <a href="ctf_info.php" class="read-more-btn">Read More</a>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="hero-content">
                        <h1>Learn how to code in python</h1>
                        <p>Start your coding journey with us</p>
                        <a href="ctf_info.php" class="read-more-btn">Read More</a>
                    </div>
                </div>
            </div>
            
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>

    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12 text-center mb-4">
                <h2 class="text-center">Welcome to Cybersite</h2>
                <p class="text-center" id="welcome-message">Your one-stop destination for learning and practicing cybersecurity skills.</p>
            </div>
        <div class="card-grid">
            <div class="row">
                <div class="col-md-6 col-lg-6">
                    <div class="card site-card mb-4">
                        <div class="card-body">
                            <h3 class="card-title">Learn</h3>
                            <p class="card-text">Access comprehensive resources and tutorials to build your cybersecurity knowledge from the ground up.</p>
                            <a href="#" class="btn btn-primary card-btn">Start Learning</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-6">
                    <div class="card site-card mb-4">
                        <div class="card-body">
                            <h3 class="card-title">Practice</h3>
                            <p class="card-text">Apply your skills with hands-on challenges designed to test and improve your abilities.</p>
                            <a href="#" class="btn btn-primary card-btn">Try Challenges</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-6">
                    <div class="card site-card mb-4">
                        <div class="card-body">
                            <h3 class="card-title">Compete</h3>
                            <p class="card-text">Join competitions and test your skills against others worldwide.</p>
                            <a href="#" class="btn btn-primary card-btn">View Competitions</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-6">
                    <div class="card site-card mb-4">
                        <div class="card-body">
                            <h3 class="card-title">Resources</h3>
                            <p class="card-text">Access tools, guides, and additional learning materials.</p>
                            <a href="#" class="btn btn-primary card-btn">Explore Resources</a>
                        </div>
                    </div>
                </div>

            </div>
            <!-- Center the Community card using offset in a new row -->
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="card site-card mb-4">
                        <div class="card-body">
                            <h3 class="card-title">Community</h3>
                            <p class="card-text">Connect with fellow cybersecurity enthusiasts and share knowledge.</p>
                            <a href="#" class="btn btn-primary card-btn">Join Forum</a>
                        </div>
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