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

// User session check (edit as needed)
$user_data = null;
if (isset($_SESSION['user_id'])) {
    $user_stmt = $conn->prepare("SELECT user_id, name, email FROM users WHERE user_id = ?");
    $user_stmt->bind_param("i", $_SESSION['user_id']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    if ($user_result->num_rows === 1) {
        $user_data = $user_result->fetch_assoc();
    }
    $user_stmt->close();
} else {
    header('Location: login.php');
    exit;
}

// Fetch all challenges
$stmt = $conn->prepare("SELECT * FROM challenges ORDER BY id");
$stmt->execute();
$result = $stmt->get_result();
$challenges = [];
while ($row = $result->fetch_assoc()) $challenges[] = $row;

// Fetch unique difficulties
$difficulties = [];
$diffResult = $conn->query("SELECT DISTINCT difficulty FROM challenges");
while ($row = $diffResult->fetch_assoc()) $difficulties[] = $row['difficulty'];
sort($difficulties);

// Fetch unique categories
$categories = [];
$catResult = $conn->query("SELECT DISTINCT category FROM challenges");
while ($row = $catResult->fetch_assoc()) $categories[] = $row['category'];
sort($categories);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>CTF Challenges</title>
    <link rel="stylesheet" href="ctf.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="mstyles.css" />
    <script>
    (function(){if(!window.chatbase||window.chatbase("getState")!=="initialized"){window.chatbase=(...arguments)=>{if(!window.chatbase.q){window.chatbase.q=[]}window.chatbase.q.push(arguments)};window.chatbase=new Proxy(window.chatbase,{get(target,prop){if(prop==="q"){return target.q}return(...args)=>target(prop,...args)}})}const onLoad=function(){const script=document.createElement("script");script.src="https://www.chatbase.co/embed.min.js";script.id="eL8-LhqxDLzhQl7IBXcFh";script.domain="www.chatbase.co";document.body.appendChild(script)};if(document.readyState==="complete"){onLoad()}else{window.addEventListener("load",onLoad)}})();
    </script>
    <style>
        .sidebar {
            min-width: 200px;
            padding: 24px;
            background: #f3f4f6;
            color: #fff;
            border-radius: 14px;
            margin: 30px 25px 0 12px;
            height: fit-content;
        }
        .sidebar h3 {margin-top:10px;}
        .sidebar select {
            width: 96%;
            padding: 8px 10px;
            margin-bottom: 14px;
            border-radius: 7px;
            border: none;
        }
        .dashboard-container { display:flex; flex-direction:row;}
        .challenges-grid {
            flex:1;
            display: flex;
            flex-wrap: wrap;
            gap: 50px;
            align-items: flex-start;
            padding: 30px 16px 16px 0;
            min-width: 0;
        }
        .challenge-card {
            background: #fff;
            border-radius: 12px;
            padding: 22px 23px;
            width: 260px;
            margin-bottom: 12px;
            box-shadow: 0 2px 11px #0001;
            border: 2px solid #e1ebfa;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .challenge-card .card-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .category { font-size: 14px; color: #144669; }
        .difficulty { font-size: 14px; font-weight: bold;}
        .difficulty.easy    { color: #14ad4d; }
        .difficulty.medium  { color: #e3a500; }
        .difficulty.hard    { color: #e31b23; }
        .challenge-card h4 {margin: 10px 0 9px;}
        .challenge-card p {margin: 2px 0;}
        @media (max-width:900px){
            .dashboard-container {flex-direction:column;}
            .sidebar {margin-bottom:18px;}
            .challenges-grid {padding:0;}
            .challenge-card {width:100%; min-width:240px;}
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

    <header class="top-header" id="topHeader">
        <h1>Welcome to CTF Challenges</h1>
    </header>
<div class="dashboard-container">
    <aside class="sidebar">
        <h3>Difficulty</h3>
        <select id="difficultyFilter">
            <option value="All">All Difficulties</option>
            <?php foreach ($difficulties as $diff): ?>
                <option value="<?= htmlspecialchars($diff) ?>"><?= htmlspecialchars($diff) ?></option>
            <?php endforeach; ?>
        </select>

        <h3>Category</h3>
        <select id="categoryFilter">
            <option value="All">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
        </select>
    </aside>

    <main class="challenges-grid" id="challengeContainer">
        <!-- Cards are rendered here by JS -->
    </main>
</div>

<script>
const challenges = <?= json_encode($challenges) ?>;
const diffColors = { easy:'easy', medium:'medium', hard:'hard' };

function createChallengeCard(chal) {
    let dClass = diffColors[chal.difficulty.toLowerCase()] || '';
    let rating;
    switch (chal.difficulty.toLowerCase()) {
        case 'easy':   rating = Math.floor(Math.random()*11)+90; break;
        case 'medium': rating = Math.floor(Math.random()*15)+75; break;
        case 'hard':   rating = Math.floor(Math.random()*15)+60; break;
        default:       rating = Math.floor(Math.random()*26)+70;
    }
    const card = document.createElement('div');
    card.className = 'challenge-card';
    card.innerHTML = `
        <div class="card-header">
            <span class="category">${chal.category}</span>
            <span class="difficulty ${dClass}">${chal.difficulty}</span>
        </div>
        <h4><a href="challenge.php?id=${chal.id}">${chal.title}</a></h4>
        <p>${Number(chal.solves).toLocaleString()} solves</p>
        <p>${rating}% <i class="fa-solid fa-thumbs-up"></i></p>
    `;
    return card;
}

function filterChallenges() {
    const diff = document.getElementById('difficultyFilter').value;
    const cat  = document.getElementById('categoryFilter').value;
    const container = document.getElementById('challengeContainer');
    container.innerHTML = '';

    const filtered = challenges.filter(chal => {
        const diffMatch = (diff === 'All' || chal.difficulty === diff);
        const catMatch = (cat === 'All' || chal.category === cat);
        return diffMatch && catMatch;
    });
    if (filtered.length === 0) {
        container.innerHTML = '<p>No challenges found for selected filters.</p>';
        return;
    }
    filtered.forEach(chal => { container.appendChild(createChallengeCard(chal)); });
}

document.getElementById('difficultyFilter').addEventListener('change', filterChallenges);
document.getElementById('categoryFilter').addEventListener('change', filterChallenges);

filterChallenges();
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
</html>
