<?php
// Session Debug and Cleanup Script
// Save this as debug_session.php

// Start with fresh session handling
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

session_start();

echo "<h1>Session Debug Information</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .info-box { background: #e8f4fd; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .error-box { background: #ffeaea; padding: 15px; margin: 10px 0; border-radius: 5px; color: #d63031; }
    .success-box { background: #eafaf1; padding: 15px; margin: 10px 0; border-radius: 5px; color: #00b894; }
    .code { background: #f8f9fa; padding: 10px; font-family: monospace; margin: 10px 0; }
    .btn { padding: 10px 20px; margin: 5px; background: #0984e3; color: white; text-decoration: none; border-radius: 5px; display: inline-block; }
    .btn-danger { background: #d63031; }
    .btn-success { background: #00b894; }
</style>";

// Handle actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'destroy':
            session_destroy();
            echo "<div class='success-box'><strong>✓ Session Destroyed!</strong> All session data has been cleared.</div>";
            // Start a new session after destroying
            session_start();
            break;
            
        case 'clear_user':
            unset($_SESSION['user_id']);
            unset($_SESSION['name']);
            unset($_SESSION['email']);
            unset($_SESSION['login_time']);
            echo "<div class='success-box'><strong>✓ User Data Cleared!</strong> Login-related session data removed.</div>";
            break;
            
        case 'regenerate':
            session_regenerate_id(true);
            echo "<div class='success-box'><strong>✓ Session ID Regenerated!</strong> New session ID: " . session_id() . "</div>";
            break;
    }
}

echo "<div class='info-box'>";
echo "<h2>Current Session Status</h2>";
echo "<strong>Session ID:</strong> " . session_id() . "<br>";
echo "<strong>Session Name:</strong> " . session_name() . "<br>";
echo "<strong>Session Status:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "<br>";
echo "<strong>Session Save Path:</strong> " . session_save_path() . "<br>";
echo "</div>";

echo "<div class='info-box'>";
echo "<h2>Session Data</h2>";
if (empty($_SESSION)) {
    echo "<em>No session data found.</em>";
} else {
    echo "<div class='code'>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
    echo "</div>";
}
echo "</div>";

// Check for specific login-related data
echo "<div class='info-box'>";
echo "<h2>Login Status Check</h2>";
$is_logged_in = isset($_SESSION['user_id']);
echo "<strong>Is Logged In:</strong> " . ($is_logged_in ? "YES (This is why login.php redirects!)" : "NO") . "<br>";

if ($is_logged_in) {
    echo "<div class='error-box'>";
    echo "<strong>PROBLEM IDENTIFIED!</strong><br>";
    echo "Your session contains user_id: " . $_SESSION['user_id'] . "<br>";
    echo "This makes login.php think you're already logged in and redirects you to main.php<br>";
    echo "</div>";
}
echo "</div>";

// Check session file permissions and location
echo "<div class='info-box'>";
echo "<h2>Session Configuration</h2>";
echo "<strong>Session Cookie Params:</strong><br>";
$params = session_get_cookie_params();
echo "<div class='code'>";
foreach ($params as $key => $value) {
    echo "$key: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "<br>";
}
echo "</div>";
echo "</div>";

// Check for session files
echo "<div class='info-box'>";
echo "<h2>Session File Check</h2>";
$session_path = session_save_path();
if (empty($session_path)) {
    $session_path = sys_get_temp_dir();
}
echo "<strong>Session files location:</strong> $session_path<br>";

if (is_dir($session_path) && is_readable($session_path)) {
    $session_files = glob($session_path . '/sess_*');
    echo "<strong>Number of session files:</strong> " . count($session_files) . "<br>";
    
    // Check for current session file
    $current_session_file = $session_path . '/sess_' . session_id();
    if (file_exists($current_session_file)) {
        echo "<strong>Current session file exists:</strong> YES<br>";
        echo "<strong>File size:</strong> " . filesize($current_session_file) . " bytes<br>";
        echo "<strong>Last modified:</strong> " . date('Y-m-d H:i:s', filemtime($current_session_file)) . "<br>";
    } else {
        echo "<strong>Current session file exists:</strong> NO<br>";
    }
} else {
    echo "<em>Cannot access session directory</em><br>";
}
echo "</div>";

// Action buttons
echo "<div class='info-box'>";
echo "<h2>Session Management Actions</h2>";
echo "<a href='?action=clear_user' class='btn'>Clear User Login Data Only</a>";
echo "<a href='?action=regenerate' class='btn'>Regenerate Session ID</a>";
echo "<a href='?action=destroy' class='btn btn-danger'>Destroy Entire Session</a>";
echo "<a href='?' class='btn btn-success'>Refresh Page</a>";
echo "</div>";

echo "<div class='info-box'>";
echo "<h2>Quick Links</h2>";
echo "<a href='login.php' class='btn'>Go to Login Page</a>";
echo "<a href='main.php' class='btn'>Go to Main Page</a>";
echo "</div>";

// Recommendations
echo "<div class='info-box'>";
echo "<h2>Recommendations</h2>";
if ($is_logged_in) {
    echo "<div class='error-box'>";
    echo "<strong>TO FIX YOUR ISSUE:</strong><br>";
    echo "1. Click 'Clear User Login Data Only' above to remove login session data<br>";
    echo "2. OR click 'Destroy Entire Session' to completely reset<br>";
    echo "3. Then try accessing login.php again<br>";
    echo "</div>";
} else {
    echo "<div class='success-box'>";
    echo "<strong>SESSION LOOKS CLEAN!</strong><br>";
    echo "Your session doesn't contain login data. The redirect issue might be elsewhere.<br>";
    echo "Check your login.php file for other redirect logic.<br>";
    echo "</div>";
}
echo "</div>";

// PHP Info section
echo "<div class='info-box'>";
echo "<h2>PHP Session Configuration</h2>";
echo "<div class='code'>";
echo "session.auto_start: " . ini_get('session.auto_start') . "<br>";
echo "session.cookie_lifetime: " . ini_get('session.cookie_lifetime') . "<br>";
echo "session.gc_maxlifetime: " . ini_get('session.gc_maxlifetime') . "<br>";
echo "session.use_cookies: " . ini_get('session.use_cookies') . "<br>";
echo "session.use_only_cookies: " . ini_get('session.use_only_cookies') . "<br>";
echo "</div>";
echo "</div>";
?>

<script>
// Auto-refresh option
function autoRefresh() {
    if (confirm('Enable auto-refresh every 5 seconds?')) {
        setTimeout(function() {
            window.location.reload();
        }, 5000);
    }
}

// Add refresh button
document.addEventListener('DOMContentLoaded', function() {
    const refreshBtn = document.createElement('button');
    refreshBtn.textContent = 'Enable Auto-Refresh';
    refreshBtn.className = 'btn';
    refreshBtn.onclick = autoRefresh;
    document.body.appendChild(refreshBtn);
});
</script>