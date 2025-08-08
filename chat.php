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
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT user_id, name, email, user_role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>AI Chatbot</title>
  <link rel="stylesheet" href="mstyles.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #121212; color: white; }
    .container { margin-top: 60px; }
    .chat-card {
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      border-radius: 10px;
      overflow: hidden;
      background-color: white;
    }
    iframe {
      border: none;
      width: 100%;
      height: 600px;
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

  <div class="container">
    <h1 class="mb-4 text-center text-dark">AI Chatbot Assistant</h1>
    <div class="chat-card">
      <iframe
        src="https://www.chatbase.co/chatbot-iframe/eL8-LhqxDLzhQl7IBXcFh"
        allow="clipboard-write">
      </iframe>
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
