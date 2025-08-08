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

// Get user data
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
}else{
    header('Location: login.php');
    exit;
}

// Share it globally for navbar
$GLOBALS['user_data'] = $user_data;

// Fetch Questions
$forensics_questions = $conn->query("SELECT * FROM my_forensics_questions ORDER BY difficulty, question_id")->fetch_all(MYSQLI_ASSOC);
$crypto_questions = $conn->query("SELECT * FROM my_crypto_questions ORDER BY difficulty, question_id")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Forensics Challenge</title>
    <link rel="stylesheet" href="mstyles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    (function(){if(!window.chatbase||window.chatbase("getState")!=="initialized"){window.chatbase=(...arguments)=>{if(!window.chatbase.q){window.chatbase.q=[]}window.chatbase.q.push(arguments)};window.chatbase=new Proxy(window.chatbase,{get(target,prop){if(prop==="q"){return target.q}return(...args)=>target(prop,...args)}})}const onLoad=function(){const script=document.createElement("script");script.src="https://www.chatbase.co/embed.min.js";script.id="eL8-LhqxDLzhQl7IBXcFh";script.domain="www.chatbase.co";document.body.appendChild(script)};if(document.readyState==="complete"){onLoad()}else{window.addEventListener("load",onLoad)}})();
    </script>
    <style>
        .modal.fade .modal-dialog {
            transform: translateY(-50px);
            transition: transform 0.3s ease-out, opacity 0.3s ease-out;
        }
        .modal.show .modal-dialog {
            transform: translateY(0);
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

<div class="container mt-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Forensics & Cryptography Lab</h1>
        <p class="text-muted">Click a challenge to open it in a popup and submit your solution.</p>
    </div>

    <!-- Forensics Section -->
    <h2 class="mb-4">🔍 Forensics Challenges</h2>
    <div class="row">
        <?php foreach ($forensics_questions as $index => $q): ?>
            <?php
                $key = "forensics_" . $q['question_id'];
                $isSolved = isset($submission_message[$key]) && str_contains($submission_message[$key], '✅');
            ?>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100 <?= $isSolved ? 'border-success bg-light' : '' ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($q['question_text']) ?></h5>
                        <p class="text-muted">Difficulty: <?= htmlspecialchars($q['difficulty']) ?></p>
                        <button class="btn <?= $isSolved ? 'btn-success' : 'btn-outline-primary' ?> w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#modal-forensics-<?= $index ?>">
                            <?= $isSolved ? '✅ Completed' : 'View Challenge' ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modal-forensics-<?= $index ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title"><?= htmlspecialchars($q['question_text']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p><?= nl2br(htmlspecialchars($q['description'])) ?></p>
                                <input type="hidden" name="category" value="forensics">
                                <input type="hidden" name="question_id" value="<?= $q['question_id'] ?>">

                                <?php if ($isSolved): ?>
                                    <div class="alert alert-success">✅ You already solved this challenge correctly.</div>
                                <?php elseif ($q['question_type'] === 'MCQ' && !empty($q['options'])):
                                    $options = json_decode($q['options'], true);
                                    if (is_array($options)):
                                        foreach ($options as $opt): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="user_flag" value="<?= htmlspecialchars($opt) ?>" required>
                                                <label class="form-check-label"><?= htmlspecialchars($opt) ?></label>
                                            </div>
                                        <?php endforeach; endif;
                                else: ?>
                                    <div class="mb-3">
                                        <label class="form-label">Enter Flag</label>
                                        <input type="text" name="user_flag" class="form-control" required>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <?php if (!$isSolved): ?>
                                    <button type="submit" name="submit_flag" class="btn btn-primary">Submit</button>
                                <?php endif; ?>
                                <?= $submission_message[$key] ?? '' ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <hr class="my-5">

    <!-- Crypto Section -->
    <h2 class="mb-4">🔐 Cryptography Challenges</h2>
    <div class="row">
        <?php foreach ($crypto_questions as $index => $q): ?>
            <?php
                $key = "crypto_" . $q['question_id'];
                $isSolved = isset($submission_message[$key]) && str_contains($submission_message[$key], '✅');
            ?>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100 <?= $isSolved ? 'border-success bg-light' : '' ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($q['question_text']) ?></h5>
                        <p class="text-muted">Difficulty: <?= htmlspecialchars($q['difficulty']) ?></p>
                        <button class="btn <?= $isSolved ? 'btn-success' : 'btn-outline-primary' ?> w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#modal-crypto-<?= $index ?>">
                            <?= $isSolved ? '✅ Completed' : 'View Challenge' ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modal-crypto-<?= $index ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title"><?= htmlspecialchars($q['question_text']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p><?= nl2br(htmlspecialchars($q['description'])) ?></p>
                                <input type="hidden" name="category" value="crypto">
                                <input type="hidden" name="question_id" value="<?= $q['question_id'] ?>">

                                <?php if ($isSolved): ?>
                                    <div class="alert alert-success">✅ You already solved this challenge correctly.</div>
                                <?php elseif ($q['question_type'] === 'MCQ' && !empty($q['options'])):
                                    $options = json_decode($q['options'], true);
                                    if (is_array($options)):
                                        foreach ($options as $opt): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="user_flag" value="<?= htmlspecialchars($opt) ?>" required>
                                                <label class="form-check-label"><?= htmlspecialchars($opt) ?></label>
                                            </div>
                                        <?php endforeach; endif;
                                else: ?>
                                    <div class="mb-3">
                                        <label class="form-label">Enter Flag</label>
                                        <input type="text" name="user_flag" class="form-control" required>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <?php if (!$isSolved): ?>
                                    <button type="submit" name="submit_flag" class="btn btn-primary">Submit</button>
                                <?php endif; ?>
                                <?= $submission_message[$key] ?? '' ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
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
