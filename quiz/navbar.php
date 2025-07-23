<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<?php
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="mstyles.css">
    <title>Document</title>
 </head>
    <style>
        body {
            background: #f3f4f6;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

       /* Profile dropdown styles */
        .profile-dropdown {
            position: relative;
            display: inline-block;
        }
        .profile-button {
            background: linear-gradient(135deg, rgba(138, 43, 226, 1) 0%, #9932CC 100%);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .profile-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            color: white;
        }
        .profile-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
        }
        .profile-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 200px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1000;
            border-radius: 8px;
            overflow: hidden;
            top: 100%;
            margin-top: 5px;
        }
        .profile-dropdown-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 0.3s;
        }
        .profile-dropdown-content a:hover {
            background-color: #f1f1f1;
        }
        .profile-dropdown-content .user-info {
            padding: 16px;
            border-bottom: 1px solid #eee;
            background-color: #f8f9fa;
        }
        .profile-dropdown-content .user-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 4px;
        }
        .profile-dropdown-content .user-email {
            font-size: 12px;
            color: #666;
        }
        .profile-dropdown.show .profile-dropdown-content {
            display: block;
        }
        .logout-link {
            color: #dc3545 !important;
            border-top: 1px solid #eee;
        }
        .logout-link:hover {
            background-color: #f8d7da !important;
        }
        .logout-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .logout-spinner {
            color: white;
            font-size: 18px;
        }
        </style>
    
 </body>
 <!-- Loading overlay for logout -->
    <div class="logout-overlay" id="logoutOverlay">
        <div class="logout-spinner">
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Logging out...</span>
            </div>
            <div class="mt-2">Logging out...</div>
        </div>
    </div>
    <nav class="navbar">
        <div class="logo">
            <svg height="40" width="40" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="40" fill="#8a2be2" />
                <circle cx="50" cy="50" r="30" fill="#9932CC" />
                <path d="M 30 50 L 70 50" stroke="white" stroke-width="5" />
                <path d="M 50 30 L 50 70" stroke="white" stroke-width="5" />
            </svg>
            <a href="main.php" class="logo-text">Cybersite</a>
        </div>
        <div class="nav-links">
            <a href="#">Get Started</a>
            <a href="#">Learn</a>
            <div class="dropdown">
                <a class="dropdown-toggle" href="#" id="practiceDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Practice
                </a>
                <ul class="dropdown-menu" aria-labelledby="aboutDropdown">
                    <li><a class="dropdown-item" href="mcq_quiz.php">Mini Quiz</a></li>
                    <li><a class="dropdown-item" href="basic_python.php">Python Quiz</a></li>
                    <li><a class="dropdown-item" href="ctf_challenge.php">Capture The Flag</a></li>
                    <li><a class="dropdown-item" href="forensics_challenge.php">Forensics Challenge</a></li>
                    
                </ul>
            </div>

            <a href="chat.php">AI Chatbot</a>

            <div class="dropdown">
                <a class="dropdown-toggle" href="#" id="aboutDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    About
                </a>
                <ul class="dropdown-menu" aria-labelledby="aboutDropdown">
                    <li><a class="dropdown-item" href="#">Contact Us</a></li>
                    <li><a class="dropdown-item" href="#">About Cybersite</a></li>
                </ul>
            </div>

            <!-- Login/Profile section - will be dynamically updated -->
            <div id="auth-section">
                <!-- Content will be populated by JavaScript -->
            </div>

         
        </div>
    </nav>
 </html>
 <script>
    const userData = <?php echo json_encode($user_data); ?>;
    function updateNavigation() {
            const authSection = document.getElementById('auth-section');
         
            
            if (userData && userData.user_id) {
                // User is logged in - show profile dropdown
            authSection.innerHTML = `
                <div class="profile-dropdown" id="profileDropdown">
                 <button class="profile-button" onclick="toggleProfileDropdown()">
                     <div class="profile-avatar">
                         ${userData.name.charAt(0).toUpperCase()}
                     </div>
                        ${userData.name}
                      <span style="font-size: 10px;">▼</span>
                   </button>
                   <div class="profile-dropdown-content" id="profileDropdownContent">
                       <div class="user-info">
                           <div class="user-name">${userData.name}</div>
                          <div class="user-email">${userData.email || 'ID: ' + userData.user_id}</div>
                       </div>
                      <a href="user_profile.php">My Profile</a>
                       <a href="dashboard.php">Dashboard</a>
                      <a href="settings.php">Settings</a>
                      <a href="progress.php">My Progress</a>
                       ${userData.user_role === 'admin' ? `<a href="admin_dashboard.php">Admin Dashboard</a>` : ''}
                       <a href="#" onclick="handleLogout(event)" class="logout-link">Logout</a>
                    </div>
              </div>
            `;
                
            
            } else {
                // User is not logged in - show login button
                authSection.innerHTML = `<a href="login.php">Log In</a>`;
              
            }
        }

        function handleLogout(event) {
            event.preventDefault();
            const overlay = document.getElementById('logoutOverlay');
            overlay.style.display = 'flex';
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown) {
                dropdown.classList.remove('show');
            }
            setTimeout(function() {
                window.location.href = 'logout.php';
            }, 500);
        }

        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }


        document.addEventListener('DOMContentLoaded', updateNavigation);

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

            // Keyboard support for dropdown
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const dropdown = document.getElementById('profileDropdown');
                if (dropdown) {
                    dropdown.classList.remove('show');
                }
            }
        });
</script>