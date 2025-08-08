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
if (isset($_SESSION['user_id'])) {
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



// Fetch user points
$user_points = 0;
if ($user_id) {
    $pointsStmt = $conn->prepare("SELECT points FROM user_points WHERE user_id = ?");
    $pointsStmt->bind_param("i", $user_id);
    $pointsStmt->execute();
    $pointsStmt->bind_result($user_points);
    $pointsStmt->fetch();
    $pointsStmt->close();
}

// Redirect to login if not authenticated
if (!$user_data) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Fetch all categories
$categories = [];
$catStmt = $conn->prepare("SELECT category_id, category_name FROM categories ORDER BY category_name");
$catStmt->execute();
$catResult = $catStmt->get_result();
while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row;
}
$catStmt->close();

// Determine selected category
$selectedCategoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : ($categories[0]['category_id'] ?? null);

// Get user's overall progress for the selected category
$progressQuery = "
    SELECT 
        COUNT(*) as total_levels,
        SUM(CASE WHEN up.level_completed = 1 THEN 1 ELSE 0 END) as completed_levels,
        AVG(CASE WHEN up.level_completed = 1 THEN up.current_score ELSE NULL END) as avg_score
    FROM levels l
    LEFT JOIN user_progress up ON l.level_id = up.level_id AND up.user_id = ?
    WHERE l.category_id = ?
";
$progressStmt = $conn->prepare($progressQuery);
$progressStmt->bind_param("ii", $user_id, $selectedCategoryId);
$progressStmt->execute();
$progressResult = $progressStmt->get_result();
$progressData = $progressResult->fetch_assoc();

$totalLevels = (int)$progressData['total_levels'];
$completedLevels = (int)$progressData['completed_levels'];
$avgScore = is_null($progressData['avg_score']) ? null : round((float)$progressData['avg_score'], 1);
$progressPercentage = $totalLevels > 0 ? round(($completedLevels / $totalLevels) * 100) : 0;

// Fetch roadmap levels for the selected category
$roadmapQuery = "
    SELECT 
        l.category_id,
        l.level_id,
        l.level_name,
        l.level_description,
        c.category_name,
        COALESCE(up.level_completed, FALSE) as completed,
        COALESCE(up.current_score, 0) as user_score,
        up.completion_time,
        l.required_score
    FROM levels l
    JOIN categories c ON l.category_id = c.category_id
    LEFT JOIN user_progress up ON l.level_id = up.level_id AND up.user_id = ?
    WHERE l.category_id = ?
    ORDER BY l.level_id
";
$roadmapStmt = $conn->prepare($roadmapQuery);
$roadmapStmt->bind_param("ii", $user_id, $selectedCategoryId);
$roadmapStmt->execute();
$roadmapResult = $roadmapStmt->get_result();
$roadmapLevels = $roadmapResult->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Learning Roadmap - Cybersite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="mstyles.css">
    <script>
    (function(){if(!window.chatbase||window.chatbase("getState")!=="initialized"){window.chatbase=(...arguments)=>{if(!window.chatbase.q){window.chatbase.q=[]}window.chatbase.q.push(arguments)};window.chatbase=new Proxy(window.chatbase,{get(target,prop){if(prop==="q"){return target.q}return(...args)=>target(prop,...args)}})}const onLoad=function(){const script=document.createElement("script");script.src="https://www.chatbase.co/embed.min.js";script.id="eL8-LhqxDLzhQl7IBXcFh";script.domain="www.chatbase.co";document.body.appendChild(script)};if(document.readyState==="complete"){onLoad()}else{window.addEventListener("load",onLoad)}})();
    </script>
    <style>
        body {
            background: #f3f4f6;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .roadmap-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 32px;
            margin: 40px auto;
            max-width: 900px;
        }
        .roadmap-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .roadmap-progress-bar {
            width: 100%;
            height: 12px;
            background: #ede9fe;
            border-radius: 6px;
            margin: 24px 0 36px 0;
            position: relative;
        }
        .roadmap-progress {
            height: 100%;
            background: linear-gradient(90deg, #a78bfa, #6366f1);
            border-radius: 6px;
            transition: width 0.5s;
        }
        .roadmap-levels-arrows {
            display: flex;
            align-items: stretch;
            justify-content: center;
            gap: 0;
        }
        .roadmap-level {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #f9fafb;
            border: 2px solid #a5b4fc;
            border-radius: 12px;
            min-width: 190px;
            max-width: 240px;
            min-height: 180px;
            height: 200px;
            text-align: center;
            margin: 0 8px;
            padding: 18px 10px;
            position: relative;
            transition: box-shadow 0.2s;
            box-sizing: border-box;
        }
        .roadmap-level.completed {
            border-color: #4ade80;
            background: #ecfdf5;
        }
        .roadmap-level.available {
            border-color: #60a5fa;
            background: #eff6ff;
        }
        .roadmap-level.locked {
            border-color: #d1d5db;
            background: #f3f4f6;
            opacity: 0.6;
        }
        .roadmap-level.locked .locked-message {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1em;
            color: #999;
            margin-top: 10px;
            min-height: 40px;
        }
        .roadmap-level .level-title {
            font-weight: bold;
            font-size: 1.15em;
            margin-bottom: 2px;
        }
        .roadmap-level .level-category {
            font-size: 0.95em;
            color: #666;
            margin-bottom: 8px;
        }
        .roadmap-level .level-date, .roadmap-level .level-score {
            font-size: 0.95em;
            margin: 2px 0;
        }
        .start-level-btn {
            margin-top: 10px;
            padding: 7px 18px;
            background: #6366f1;
            color: #fff;
            border: none;
            border-radius: 7px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
        }
        .start-level-btn:hover {
            background: #4f46e5;
        }
        .roadmap-arrow {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .roadmap-stats {
            display: flex;
            justify-content: space-around;
            text-align: center;
            margin-top: 30px;
        }
        .roadmap-stat h4 {
            color: #7c3aed;
            margin-bottom: 5px;
        }
        .roadmap-stat p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        @media (max-width: 900px) {
            .roadmap-levels-arrows { flex-direction: column; }
            .roadmap-arrow { transform: rotate(90deg); }
            .roadmap-level { margin: 12px 0; }
        }
        /* Quiz Modal Styles */
        .quiz-modal .modal-dialog {
            max-width: 700px;
        }

        .question-container {
            display: none;
        }
        .question-container.active {
            display: block;
        }
        .quiz-progress {
            height: 10px;
            border-radius: 5px;
        }
        .quiz-modal .option-btn {
            width: 100%;
            text-align: left;
            margin-bottom: 10px;
            padding: 15px;
            border: 2px solid #e9ecef;
            background: white;
            transition: all 0.2s ease;
            
        }
        .quiz-modal .option-btn:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        .quiz-modal .option-btn.selected {
            border-color: #0d6efd;
            background-color: #e7f3ff;
        }
        .quiz-result {
            text-align: center;
            padding: 30px;
        }
        .score-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            font-weight: bold;
            color: white;
        }
        .score-perfect {
            background: linear-gradient(45deg, #28a745, #20c997);
        }
        .score-good {
            background: linear-gradient(45deg, #fd7e14, #ffc107);
        }
        .score-poor {
            background: linear-gradient(45deg, #dc3545, #e83e8c);
        }
        
        /* Shop Float Button */
        .shop-float-btn {
            position: fixed;
            top: 25%;
            left: 0;
            z-index: 9999;
            background: #f6f6fc;
            color: #624db1;
            padding: 16px 18px 16px 28px;
            border: 2px dashed #000000ff;
            border-radius: 0px 30px 30px 0px;
            font-size: 1.2em;
            font-weight: bold;
            box-shadow: 0 4px 14px rgba(0,0,0,0.13);
            text-decoration: none;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .shop-float-btn:hover {
            background: linear-gradient(135deg, #624db1 0%, #624db1 100%);
            color: #fff;
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
            text-decoration: none;
        }
        .points-badge-main {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #f6f6fc;
            color: #624db1;
            border-radius: 18px;
            padding: 5px 15px;
            font-weight: 600;
            font-size: 15px;
            box-shadow: none;
            border: 1px solid #eceaf3;
            letter-spacing: 0.01em;
            margin-left: 175px;
        }

        .points-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #f6f6fc;
            color: #624db1;
            border-radius: 18px;
            padding: 5px 15px;
            font-weight: 600;
            font-size: 15px;
            box-shadow: none;
            border: 1px solid #eceaf3;
            letter-spacing: 0.01em;
        }

        .points-badge i {
            font-size: 1.1em;
            color: #dbcff7;
        }

        /* Powerup Bar Styles */
        #powerupBar {
            display: flex;
            gap: 12px;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .powerup-btn {
            background: linear-gradient(135deg, #8a2be2ff 0%, #9932CC 100%);
            border: none;
            border-radius: 8px;
            padding: 8px 14px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
            color: #333;
            font-size: 0.9em;
        }
        .powerup-btn:hover {
            background: linear-gradient(135deg, rgba(138, 43, 130, 1) 0%, #9932CC 100%);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .powerup-btn:disabled {
            background: #f1f1f1;
            color: #bbb;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .powerup-empty {
            color: #6c757d;
            font-style: italic;
            padding: 8px 14px;
        }
        .btn-purple {
            background: linear-gradient(90deg, #a78bfa, #6366f1);  /* Soft purple gradient */
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            transition: background 0.2s;
            padding: 5px 15px;
            width: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;

        }

        .btn-purple:hover, .btn-purple:focus {
            background: linear-gradient(90deg, #7c3aed 0%, #624db1 100%);
            color: #fff;
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

    <!-- Floating Shop Button -->
    <a href="#" class="shop-float-btn" title="Open Shop" data-bs-toggle="modal" data-bs-target="#shopModal">
        🛒 Shop
    </a>

    <!-- Shop Modal -->
    <div class="modal fade" id="shopModal" tabindex="-1" aria-labelledby="shopModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-md">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="shopModalLabel">Powerup Shop</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="shopModalBody">
            <div class="text-center my-3">
              <div class="spinner-border text-primary" role="status"></div>
              <div class="mt-2">Loading shop items...</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close Shop</button>
          </div>
        </div>
      </div>
    </div>

    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <div class="roadmap-container">
            <!-- Category Selector -->
            <div class="category-selector mb-4">
                <label for="categorySelect"><b>Select Roadmap Category:</b></label>
                <select id="categorySelect" class="form-select" style="max-width:300px;display:inline-block;">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['category_id'] ?>" <?= $cat['category_id'] == $selectedCategoryId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <!-- Points Display -->
                    <span class="points-badge-main" style="font-size:1em; ">
                        <svg style="vertical-align:middle;" width="20" height="20" fill="currentColor" class="bi bi-coin" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>
                            <path d="M8 13a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0 1A6 6 0 1 0 8 2a6 6 0 0 0 0 12z"/>
                            <path d="M8 4.5a.5.5 0 0 1 .5.5v2.5h2a.5.5 0 0 1 0 1h-2.5a.5.5 0 0 1-.5-.5V5a.5.5 0 0 1 .5-.5z"/>
                        </svg>
                        <?= $user_points ?> Points
                    </span>
            </div>
            <div class="roadmap-header">
                <h2><?= htmlspecialchars($roadmapLevels[0]['category_name'] ?? 'Roadmap') ?></h2>
                <span class="overall-progress-label">Overall Progress: <?= $progressPercentage ?>%</span>
            </div>
            <div class="roadmap-progress-bar">
                <div class="roadmap-progress" style="width: <?= $progressPercentage ?>%"></div>
            </div>
            <div class="roadmap-levels-arrows">
                <?php foreach ($roadmapLevels as $i => $level): 
                    $isCompleted = (bool)$level['completed'];
                    $isUnlocked = $i === 0 || (isset($roadmapLevels[$i-1]) && $roadmapLevels[$i-1]['completed']);
                    $levelClass = $isCompleted ? 'completed' : ($isUnlocked ? 'available' : 'locked');
                ?>
                <div class="roadmap-level <?= $levelClass ?>">
                    <div class="level-title"><?= htmlspecialchars($level['level_name']) ?></div>
                    <div class="level-category"><?= htmlspecialchars($level['category_name']) ?></div>
                    <?php if ($isCompleted): ?>
                        <div class="level-date">✔ <?= date('M j', strtotime($level['completion_time'])) ?></div>
                        <div class="level-score">Score: <?= $level['user_score'] ?>%</div>
                        <!-- Retake Level button for completed levels -->
                        <button class="start-level-btn"
                                data-category-id="<?= $level['category_id'] ?>"
                                data-level-id="<?= $level['level_id'] ?>"
                                data-level-name="<?= htmlspecialchars($level['level_name']) ?>"
                                data-category-name="<?= htmlspecialchars($level['category_name']) ?>"
                                data-required-score="<?= $level['required_score'] ?>">
                            Retake Level
                        </button>
                    <?php elseif ($isUnlocked): ?>
                        <button class="start-level-btn"
                                data-category-id="<?= $level['category_id'] ?>"
                                data-level-id="<?= $level['level_id'] ?>"
                                data-level-name="<?= htmlspecialchars($level['level_name']) ?>"
                                data-category-name="<?= htmlspecialchars($level['category_name']) ?>"
                                data-required-score="<?= $level['required_score'] ?>">
                            Start Level
                        </button>
                    <?php endif; ?>

                    <?php if ($levelClass === 'locked'): ?>
                        <div class="locked-message">
                            <span class="text-muted">🔒 Complete the previous level to unlock this</span>
                        </div>
                    <?php endif; ?>

                </div>
                <?php if ($i < count($roadmapLevels)-1): ?>
                    <div class="roadmap-arrow">
                        <svg width="40" height="40" viewBox="0 0 40 40">
                         <line x1="10" y1="20" x2="30" y2="20" stroke="#a78bfa" stroke-width="3"/>
                         <polygon points="30,20 24,16 24,24" fill="#a78bfa"/>
                        </svg>
                    </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="roadmap-stats">
                <div class="roadmap-stat">
                    <h4><?= $completedLevels ?>/<?= $totalLevels ?></h4>
                    <p>Levels Completed</p>
                </div>
                <div class="roadmap-stat">
                    <h4><?= $progressPercentage ?>%</h4>
                    <p>Overall Progress</p>
                </div>
                <div class="roadmap-stat">
                    <h4><?= $avgScore !== null ? $avgScore.'%' : 'N/A' ?></h4>
                    <p>Average Score</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quiz Modal -->
    <div class="modal fade quiz-modal" id="quizModal" tabindex="-1" aria-labelledby="quizModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quizModalLabel">Quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Powerup Bar - CRITICAL: This must be here -->
                    <div id="powerupBar" class="mb-3">
                        <div class="powerup-empty">Loading powerups...</div>
                    </div>
                    <!-- Progress Bar -->
                    <div class="quiz-progress-container mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Question <span id="currentQuestion">1</span> of <span id="totalQuestions">5</span></span>
                            <span>Time: <span id="timeRemaining">00:00</span></span>
                        </div>
                        <div class="progress quiz-progress">
                            <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                    <!-- Quiz Content -->
                    <div id="quizContent"></div>
                    <!-- Quiz Result -->
                    <div id="quizResult" class="quiz-result" style="display: none;">
                        <div class="score-circle" id="scoreCircle">
                            <span id="scorePercentage">0%</span>
                        </div>
                        <h3 id="resultTitle">Quiz Complete!</h3>
                        <p id="resultMessage">You scored <span id="finalScore">0</span> out of <span id="totalScore">0</span> questions correctly.</p>
                        <div id="levelUnlockMessage" style="display: none;" class="alert alert-success">
                            <h5><i class="bi bi-unlock"></i> Congratulations!</h5>
                            <p>You've unlocked the next level!</p>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="location.reload()">Close</button>
                            <button type="button" class="btn btn-primary" id="retakeQuizBtn">Retake Quiz</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" id="quizFooter">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="nextQuestionBtn">Next Question</button>
                    <button type="button" class="btn btn-success" id="submitQuizBtn" style="display: none;">Submit Quiz</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // GLOBAL VARIABLES
        let currentQuizManager = null;
        let powerupManager = null;

        // POWERUP MANAGER - Handles all powerup functionality
        class PowerupManager {
            constructor() {
                this.userPowerups = [];
                this.isLoading = false;
                this.initEventListeners();
            }

            initEventListeners() {
                // Powerup bar click handler
                document.addEventListener('click', (e) => {
                    if (e.target.classList.contains('powerup-btn') && !e.target.disabled) {
                        this.usePowerup(e.target.getAttribute('data-id'));
                    }
                });
            }

            async loadUserPowerups() {
                if (this.isLoading) return;
                this.isLoading = true;
                    
                try {
                    const response = await fetch('get_user_powerups.php');
                    const data = await response.json();
                    
                    if (data.success) {
                        this.userPowerups = data.powerups || [];
                        this.renderPowerupBar();
                    } else {
                        console.error('Failed to load powerups:', data.message);
                        this.renderEmptyPowerupBar();
                    }
                } catch (error) {
                    console.error('Error loading powerups:', error);
                    this.renderEmptyPowerupBar();
                } finally {
                    this.isLoading = false;
                }
            }

            renderPowerupBar() {
                const bar = document.getElementById('powerupBar');
                if (!bar) return;

                if (this.userPowerups.length > 0) {
                    bar.innerHTML = this.userPowerups.map(pu => 
                        `<button class="powerup-btn" data-id="${pu.powerup_id}" title="${pu.description}">
                            ${pu.name} (${pu.quantity})
                        </button>`
                    ).join('');
                } else {
                    this.renderEmptyPowerupBar();
                }
            }

            renderEmptyPowerupBar() {
                const bar = document.getElementById('powerupBar');
                if (bar) {
                    bar.innerHTML = '<div class="powerup-empty">No powerups available</div>';
                }
            }

            async usePowerup(powerupId) {
                try {
                    const response = await fetch('use_powerup.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ powerup_id: powerupId })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.applyPowerupEffect(parseInt(powerupId));
                        await this.loadUserPowerups(); // Refresh powerup display
                    } else {
                        alert(data.message || 'Could not use powerup.');
                    }
                } catch (error) {
                    console.error('Error using powerup:', error);
                    alert('Error using powerup. Please try again.');
                }
            }

            applyPowerupEffect(powerupId) {
                switch (powerupId) {
                    case 1: // 50/50
                        this.removeTwoIncorrectOptions();
                        break;
                    case 2: // Second Chance
                        this.allowSecondChance();
                        break;
                    case 3: // Skip Question
                        this.skipCurrentQuestion();
                        break;
                    case 4: // Time Freeze
                        this.freezeTimer(10);
                        break;
                    case 5: // Reveal Hint
                        this.showHint();
                        break;
                    default:
                        alert('Powerup effect not implemented yet.');
                }
            }

            // Powerup effect implementations
            removeTwoIncorrectOptions() {
                if (!currentQuizManager) return;
                
                const options = Array.from(document.querySelectorAll('.option-btn'));
                const currentQuestion = currentQuizManager.questions[currentQuizManager.currentQuestionIndex];
                
                if (!currentQuestion || options.length < 3) return;
                
                const wrongOptions = options.filter(btn => 
                    btn.dataset.optionValue !== currentQuestion.correct_answer
                );
                
                // Remove two wrong options
                for (let i = 0; i < 2 && wrongOptions.length > 0; i++) {
                    const idx = Math.floor(Math.random() * wrongOptions.length);
                    wrongOptions[idx].style.display = 'none';
                    wrongOptions[idx].disabled = true;
                    wrongOptions.splice(idx, 1);
                }
                
                alert('50/50 used! Two incorrect options have been removed.');
            }

            allowSecondChance() {
                if (currentQuizManager) {
                    currentQuizManager.secondChanceAvailable = true;
                    alert('Second Chance activated! You can retry if you get the next answer wrong.');
                }
            }

            skipCurrentQuestion() {
                if (currentQuizManager) {
                    currentQuizManager.skipCurrentQuestion();
                    alert('Question skipped!');
                }
            }

            freezeTimer(seconds) {
                if (currentQuizManager) {
                    currentQuizManager.freezeTimer(seconds);
                    alert(`Timer frozen for ${seconds} seconds!`);
                }
            }

            showHint() {
                const currentQuestion = currentQuizManager?.questions[currentQuizManager.currentQuestionIndex];
                if (currentQuestion && currentQuestion.hint) {
                    alert('Hint: ' + currentQuestion.hint);
                } else {
                    alert('No hint available for this question.');
                }
            }
        }

        // QUIZ MANAGER - Your existing quiz logic with powerup integration
        class QuizManager {
            constructor() {
                this.currentCategoryId = null;
                this.currentLevelId = null;
                this.currentCategoryName = null;
                this.currentLevelName = null;
                this.requiredScore = 40;
                this.questions = [];
                this.currentQuestionIndex = 0;
                this.userAnswers = [];
                this.score = 0;
                this.timer = null;
                this.timeLimit = 1200; // 20 minutes in seconds
                this.timeRemaining = this.timeLimit;
                this.secondChanceAvailable = false;
                this.timerFrozen = false;
                this.initEventListeners();
            }

            initEventListeners() {
                document.addEventListener('click', (e) => {
                    if (e.target.classList.contains('start-level-btn')) {
                        this.startQuiz(e.target);
                    }
                });

                document.getElementById('nextQuestionBtn').addEventListener('click', () => {
                    if (!this.saveCurrentAnswer()) return;
                    this.currentQuestionIndex++;
                    this.displayQuestion();
                });

                document.getElementById('submitQuizBtn').addEventListener('click', () => {
                    if (!this.saveCurrentAnswer()) return;
                    this.stopTimer();
                    this.submitQuiz();
                });

                document.getElementById('retakeQuizBtn').addEventListener('click', () => {
                    this.retakeQuiz();
                });

                document.addEventListener('click', (e) => {
                    if (e.target.classList.contains('option-btn')) {
                        this.selectOption(e.target);
                    }
                });
            }

            async startQuiz(button) {
                this.currentCategoryId = button.dataset.categoryId;
                this.currentLevelId = button.dataset.levelId;
                this.currentLevelName = button.dataset.levelName;
                this.currentCategoryName = button.dataset.categoryName;
                this.requiredScore = parseInt(button.dataset.requiredScore);
                this.currentQuestionIndex = 0;
                this.userAnswers = [];
                this.score = 0;
                this.timeRemaining = this.timeLimit;
                this.secondChanceAvailable = false;
                this.timerFrozen = false;

                document.getElementById('quizModalLabel').textContent = this.currentLevelName + ' Quiz - ' + this.currentCategoryName;
                
                await this.loadQuestions();
                
                const modal = new bootstrap.Modal(document.getElementById('quizModal'));
                modal.show();
                
                // Load powerups AFTER modal is shown
                setTimeout(() => {
                    powerupManager.loadUserPowerups();
                }, 100);
                
                this.displayQuestion();
                this.startTimer();
            }

            async loadQuestions() {
                try {
                    const response = await fetch('get_quiz_questions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            category_id: this.currentCategoryId,
                            level_id: this.currentLevelId
                        })
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.questions = data.questions;
                        document.getElementById('totalQuestions').textContent = this.questions.length;
                    } else {
                        throw new Error(data.message || 'Failed to load questions');
                    }
                } catch (error) {
                    console.error('Error loading questions:', error);
                    alert('Failed to load quiz questions. Please try again.');
                }
            }

            displayQuestion() {
                if (this.currentQuestionIndex >= this.questions.length) {
                    this.showResults();
                    return;
                }

                const question = this.questions[this.currentQuestionIndex];
                const options = question.options ? JSON.parse(question.options) : [];

                document.getElementById('currentQuestion').textContent = this.currentQuestionIndex + 1;
                const progress = ((this.currentQuestionIndex + 1) / this.questions.length) * 100;
                document.getElementById('progressBar').style.width = progress + '%';

                let questionHtml = `
                    <div class="question-container active">
                        <h4 class="question-title mb-4">${question.question_text}</h4>
                        ${question.description ? `<p class="question-description text-muted mb-4">${question.description}</p>` : ''}
                        <div class="options-container">
                `;

                if (question.question_type === 'MCQ' && options.length > 0) {
                    options.forEach((option, index) => {
                        questionHtml += `
                            <button class="btn option-btn" data-option-value="${option}" data-question-id="${question.question_id}">
                                ${String.fromCharCode(65 + index)}. ${option}
                            </button>
                        `;
                    });
                } else {
                    questionHtml += `
                        <textarea class="form-control" id="textAnswer" placeholder="Enter your answer here..." rows="4"></textarea>
                    `;
                }

                questionHtml += `
                        </div>
                    </div>
                `;

                document.getElementById('quizContent').innerHTML = questionHtml;
                document.getElementById('quizResult').style.display = 'none';
                document.getElementById('quizFooter').style.display = 'flex';

                const nextBtn = document.getElementById('nextQuestionBtn');
                const submitBtn = document.getElementById('submitQuizBtn');

                if (this.currentQuestionIndex === this.questions.length - 1) {
                    nextBtn.style.display = 'none';
                    submitBtn.style.display = 'inline-block';
                } else {
                    nextBtn.style.display = 'inline-block';
                    submitBtn.style.display = 'none';
                }
            }

            selectOption(button) {
                document.querySelectorAll('.option-btn').forEach(btn => {
                    btn.classList.remove('selected');
                });
                button.classList.add('selected');
            }

            saveCurrentAnswer() {
                const question = this.questions[this.currentQuestionIndex];
                let userAnswer = '';

                if (question.question_type === 'MCQ') {
                    const selectedOption = document.querySelector('.option-btn.selected');
                    userAnswer = selectedOption ? selectedOption.dataset.optionValue : '';
                } else {
                    const textArea = document.getElementById('textAnswer');
                    userAnswer = textArea ? textArea.value.trim() : '';
                }

                if (!userAnswer) {
                    alert('Please select or enter an answer before proceeding.');
                    return false;
                }

                const isCorrect = this.checkAnswer(userAnswer, question.correct_answer);
                
                // Handle second chance
                if (!isCorrect && this.secondChanceAvailable) {
                    const retry = confirm('Wrong answer! You have a second chance. Would you like to try again?');
                    if (retry) {
                        this.secondChanceAvailable = false;
                        return false; // Don't save answer, let user try again
                    }
                }

                if (!this.userAnswers[this.currentQuestionIndex]) {
                    this.userAnswers[this.currentQuestionIndex] = {
                        question_id: question.question_id,
                        user_answer: userAnswer,
                        correct_answer: question.correct_answer,
                        is_correct: isCorrect
                    };
                    if (isCorrect) {
                        this.score++;
                    }
                } else {
                    const wasCorrect = this.userAnswers[this.currentQuestionIndex].is_correct;
                    this.userAnswers[this.currentQuestionIndex] = {
                        question_id: question.question_id,
                        user_answer: userAnswer,
                        correct_answer: question.correct_answer,
                        is_correct: isCorrect
                    };
                    if (!wasCorrect && isCorrect) this.score++;
                    if (wasCorrect && !isCorrect) this.score--;
                }

                return true;
            }

            checkAnswer(userAnswer, correctAnswer) {
                return userAnswer.toLowerCase().trim() === correctAnswer.toLowerCase().trim();
            }

            skipCurrentQuestion() {
                this.userAnswers[this.currentQuestionIndex] = {
                    question_id: this.questions[this.currentQuestionIndex].question_id,
                    user_answer: '',
                    correct_answer: this.questions[this.currentQuestionIndex].correct_answer,
                    is_correct: false
                };
                
                this.currentQuestionIndex++;
                this.displayQuestion();
            }

            freezeTimer(seconds) {
                if (this.timerFrozen) return;
                
                this.timerFrozen = true;
                setTimeout(() => {
                    this.timerFrozen = false;
                }, seconds * 1000);
            }

            async submitQuiz() {
                this.stopTimer();
                const scorePercentage = Math.round((this.score / this.questions.length) * 100);
                const saveResult = await this.saveQuizResults(scorePercentage);
                this.showResults(saveResult);
            }

            async saveQuizResults(scorePercentage) {
                try {
                    const response = await fetch('save_quiz_results.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            user_id: <?= $user_id ?>,
                            category_id: this.currentCategoryId,
                            level_id: this.currentLevelId,
                            score: scorePercentage,
                            questions_answered: this.questions.length,
                            questions_correct: this.score,
                            user_answers: this.userAnswers,
                            required_score: this.requiredScore
                        })
                    });
                    const data = await response.json();
                    return data;
                } catch (error) {
                    console.error('Error saving quiz results:', error);
                    return { success: false };
                }
            }

            showResults(saveResult = {}) {
                const scorePercentage = Math.round((this.score / this.questions.length) * 100);
                document.getElementById('quizContent').style.display = 'none';
                document.getElementById('quizFooter').style.display = 'none';
                document.getElementById('quizResult').style.display = 'block';
                document.getElementById('scorePercentage').textContent = scorePercentage + '%';
                document.getElementById('finalScore').textContent = this.score;
                document.getElementById('totalScore').textContent = this.questions.length;

                // Show points awarded
                if (saveResult && saveResult.points_awarded !== undefined) {
                    const pointsDiv = document.createElement('div');
                    pointsDiv.className = 'alert alert-info mt-3';
                    pointsDiv.innerHTML = `<b>+${saveResult.points_awarded} Points</b> earned for this quiz!`;
                    document.getElementById('quizResult').appendChild(pointsDiv);
                }

                if (saveResult.success && saveResult.level_unlocked) {
                    document.getElementById('levelUnlockMessage').style.display = 'block';
                } else {
                    document.getElementById('levelUnlockMessage').style.display = 'none';
                }

                const scoreCircle = document.getElementById('scoreCircle');
                const resultTitle = document.getElementById('resultTitle');
                const resultMessage = document.getElementById('resultMessage');

                if (scorePercentage >= this.requiredScore) {
                    scoreCircle.className = 'score-circle score-perfect';
                    resultTitle.textContent = 'Excellent Work!';
                    if (scorePercentage === 100) {
                        resultMessage.innerHTML = `Perfect score! You answered all ${this.questions.length} questions correctly!`;
                    } else {
                        resultMessage.innerHTML = `Great job! You scored ${this.score} out of ${this.questions.length} questions correctly (${scorePercentage}%).`;
                    }
                } else if (scorePercentage >= 60) {
                    scoreCircle.className = 'score-circle score-good';
                    resultTitle.textContent = 'Good Job!';
                    resultMessage.innerHTML = `Good effort! You scored ${this.score} out of ${this.questions.length} questions correctly (${scorePercentage}%). You need ${this.requiredScore}% to pass.`;
                } else {
                    scoreCircle.className = 'score-circle score-poor';
                    resultTitle.textContent = 'Keep Practicing!';
                    resultMessage.innerHTML = `You scored ${this.score} out of ${this.questions.length} questions correctly (${scorePercentage}%). You need ${this.requiredScore}% to pass. Keep studying and try again!`;
                }
            }

            retakeQuiz() {
                document.getElementById('quizResult').style.display = 'none';
                document.getElementById('quizContent').style.display = 'block';
                document.getElementById('quizFooter').style.display = 'flex';
                document.getElementById('levelUnlockMessage').style.display = 'none';

                this.currentQuestionIndex = 0;
                this.userAnswers = [];
                this.score = 0;
                this.timeRemaining = this.timeLimit;
                this.secondChanceAvailable = false;
                this.timerFrozen = false;

                powerupManager.loadUserPowerups();
                this.displayQuestion();
                this.startTimer();
            }

            startTimer() {
                this.timer = setInterval(() => {
                    if (!this.timerFrozen) {
                        this.timeRemaining--;
                    }
                    
                    const minutes = Math.floor(this.timeRemaining / 60);
                    const seconds = this.timeRemaining % 60;
                    document.getElementById('timeRemaining').textContent =
                        `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                    if (this.timeRemaining <= 0) {
                        this.submitQuiz();
                    }
                }, 1000);
            }

            stopTimer() {
                if (this.timer) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
            }
        }

        
        // SHOP MODAL MANAGEMENT
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize navigation
            updateNavigation();
            
            // Initialize managers
            powerupManager = new PowerupManager();
            currentQuizManager = new QuizManager();

            // Category selector
            document.getElementById('categorySelect').addEventListener('change', function() {
                window.location.href = '?category_id=' + this.value;
            });

            // Shop modal event listener
            var shopModal = document.getElementById('shopModal');
            shopModal.addEventListener('show.bs.modal', function () {
                fetch('get_shop_items.php')
                    .then(response => response.json())
                    .then(data => {
                        const shopBody = document.getElementById('shopModalBody');
                        if (data.success && data.powerups && data.powerups.length > 0) {
                            let html = `<div class="mb-3"><b>Your Points:</b> <span class="points-badge
                            ">${data.user_points}</span></div>`;
                            html += `<div class="list-group">`;
                            data.powerups.forEach(item => {
                                html += `
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <b>${item.name}</b><br>
                                        <small>${item.description}</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-secondary mb-2" style="width: 50px;">${item.cost} pts</span><br>
                                        <button class="btn btn-sm btn-purple buy-powerup-btn" data-id="${item.powerup_id}" ${data.user_points < item.cost ? 'disabled' : ''}>Buy</button>
                                    </div>
                                </div>`;
                            });
                            html += `</div>`;
                            shopBody.innerHTML = html;
                        } else {
                            shopBody.innerHTML = `<div class="alert alert-info">No powerups available right now.</div>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading shop:', error);
                        document.getElementById('shopModalBody').innerHTML = `<div class="alert alert-danger">Error loading shop items.</div>`;
                    });
            });

            // Buy powerup handler
            document.body.addEventListener('click', function(e) {
                if (e.target.classList.contains('buy-powerup-btn')) {
                    const powerupId = e.target.getAttribute('data-id');
                    fetch('buy_powerup.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ powerup_id: powerupId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Powerup purchased successfully!');
                            var shopModal = bootstrap.Modal.getInstance(document.getElementById('shopModal'));
                            shopModal.hide();
                            // Refresh page to update points display
                            setTimeout(() => location.reload(), 500);
                        } else {
                            alert(data.message || 'Purchase failed.');
                        }
                    })
                    .catch(error => {
                        console.error('Error purchasing powerup:', error);
                        alert('Error purchasing powerup. Please try again.');
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
