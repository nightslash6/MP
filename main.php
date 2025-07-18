<?php
session_start();
require 'config.php';
// Assuming you have a database connection set up in $conn
$conn = db_connect();


// Check if user is logged in and get user data
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
    <title>Cybersite</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="mstyles.css">
    <style>
        /* Additional styles for profile dropdown */
        .profile-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .profile-button {
            background: linear-gradient(135deg, #8a2be2 0%, #9932CC 100%);
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

        /* Loading overlay for logout */
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
</head>
<body>
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
            <a href="#" class="logo-text">Cybersite</a>
        </div>
        <div class="nav-links">
            <a href="#">Get Started</a>
            <a href="#">Learn</a>
            <div class="dropdown">
                <a class="dropdown-toggle" href="#" id="practiceDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Practice
                </a>
                <ul class="dropdown-menu" aria-labelledby="aboutDropdown">
                    <li><a class="dropdown-item" href="quiz.php">Mini Quiz</a></li>
                    <li><a class="dropdown-item" href="#">Python Quiz</a></li>
                    <li><a class="dropdown-item" href="#">Capture The Flag</a></li>
                    <li><a class="dropdown-item" href="#">Forensics Challenge</a></li>
                    
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
    
    <div class="hero">    
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" class="active" aria-current="true" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>
            
            <div class="carousel-inner">
                <div class="carousel-item">
                    <div class="hero-content">
                        <h1>Learn Cybersecurity</h1>
                        <p>Master the fundamentals with our interactive challenges</p>
                        <a href="#" class="read-more-btn">Read More</a>
                    </div>
                </div>
                
                <div class="carousel-item active">
                    <div class="hero-content">
                        <h1>How You Can Write CTF Challenges</h1>
                        <p>by Wei Hong</p>
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
                            <a href="question_1.php" class="btn btn-primary card-btn">Try Challenges</a>
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

    <script>
        // User data from PHP
        const userData = <?php echo json_encode($user_data); ?>;
        
        // Function to update navigation based on login status
        function updateNavigation() {
            const authSection = document.getElementById('auth-section');
            const welcomeMessage = document.getElementById('welcome-message');
            
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
                
                // Update welcome message
                welcomeMessage.textContent = `Welcome back, ${userData.name}! Ready to continue your cybersecurity journey?`;
            } else {
                // User is not logged in - show login button
                authSection.innerHTML = `<a href="login.php">Log In</a>`;
                welcomeMessage.textContent = "Your one-stop destination for learning and practicing cybersecurity skills.";
            }
        }
        
        // Function to handle logout with loading overlay
        function handleLogout(event) {
            event.preventDefault();
            
            // Show loading overlay
            const overlay = document.getElementById('logoutOverlay');
            overlay.style.display = 'flex';
            
            // Close dropdown
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown) {
                dropdown.classList.remove('show');
            }
            
            // Redirect to logout.php after a brief delay for better UX
            setTimeout(function() {
                window.location.href = 'logout.php';
            }, 500);
        }
        
        // Function to toggle profile dropdown
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });
        
        // Initialize navigation on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateNavigation();
        });
        
        // Optional: Add keyboard support for dropdown
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const dropdown = document.getElementById('profileDropdown');
                if (dropdown) {
                    dropdown.classList.remove('show');
                }
            }
        });
    </script>
</body>
</html>
