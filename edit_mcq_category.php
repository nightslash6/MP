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

// Only admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$conn = db_connect();

$category_id = $_GET['id'] ?? null;
if (!$category_id || !is_numeric($category_id)) {
    header('Location: mcq_quiz_admin.php');
    exit;
}

$category_id = intval($category_id);
$message = '';
$errors = [];

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch existing category data
$stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();
    header('Location: mcq_quiz_admin.php');
    exit;
}

$category = $result->fetch_assoc();
$stmt->close();

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateInput($title, &$errors) {
    if (empty($title)) {
        $errors['category_name'] = 'Category name is required.';
    } elseif (!preg_match("/^[a-zA-Z0-9\s\-.,:'()]+$/", $title)) {
        $errors['category_name'] = 'Invalid characters in category name.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = 'Invalid CSRF token.';
    } else {
        $category_name = sanitize($_POST['category_name'] ?? '');
        $category_description = sanitize($_POST['category_description'] ?? '');

        validateInput($category_name, $errors);

        if (empty($errors)) {
            $stmt = $conn->prepare("UPDATE categories SET category_name = ?, category_description = ? WHERE category_id = ?");
            $stmt->bind_param("ssi", $category_name, $category_description, $category_id);
            if ($stmt->execute()) {
                $_SESSION['message'] = ['successful' => 'Category updated successfully!'];
                $stmt->close();
                header('Location: mcq_quiz_admin.php');
                exit;
            } else {
                $message = 'Database error: ' . $stmt->error;
                $stmt->close();
            }
        }
    }
} else {
    // Pre-fill form for GET request
    $_POST['category_name'] = $category['category_name'];
    $_POST['category_description'] = $category['category_description'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Category - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="mstyles.css"/>
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
    <h2>Edit Category</h2>

    <?php if ($message): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>"/>

        <div class="mb-3">
            <label for="category_name" class="form-label">Category Name*</label>
            <input type="text" class="form-control <?= isset($errors['category_name']) ? 'is-invalid' : '' ?>"
                   id="category_name" name="category_name" required
                   value="<?= htmlspecialchars($_POST['category_name'] ?? '') ?>">
            <div class="invalid-feedback"><?= $errors['category_name'] ?? '' ?></div>
        </div>

        <div class="mb-3">
            <label for="category_description" class="form-label">Category Description</label>
            <textarea class="form-control" id="category_description"
                      name="category_description" rows="4"><?= htmlspecialchars($_POST['category_description'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Category</button>
        <a href="mcq_quiz_admin.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
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
