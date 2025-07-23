<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
.navbar {
    background-color: #2c2c2c;
    padding: 10px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.logo {
    display: flex;
    align-items: center;
    gap: 10px;
}
.logo svg {
    background: #6f2cf3;
    border-radius: 50%;
    padding: 3px;
}
.logo-text {
    color: white;
    font-size: 22px;
    font-weight: 700;
    text-decoration: none;
}
.nav-links {
    display: flex;
    align-items: center;
    gap: 25px;
}
.nav-links a, .nav-links .dropdown-toggle {
    color: white;
    text-decoration: none;
}
.navbar .profile-dropdown {
    position: relative;
}
.profile-button {
    background: linear-gradient(135deg, #8a2be2, #9932cc);
    border: none;
    color: white;
    border-radius: 25px;
    padding: 5px 12px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
}
.profile-avatar {
    background-color: rgba(255,255,255,0.2);
    border-radius: 50%;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
}
.profile-dropdown-content {
    display: none;
    position: absolute;
    top: 110%;
    right: 0;
    background-color: white;
    min-width: 200px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    border-radius: 8px;
    z-index: 1000;
    overflow: hidden;
}
.profile-dropdown.show .profile-dropdown-content {
    display: block;
}
.profile-dropdown-content .user-info {
    padding: 12px 16px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #eee;
    color: #333;
}
.profile-dropdown-content .user-name {
    font-weight: bold;
    color: #333; /* or use #000 for pure black */
    margin-bottom: 4px;
}
.profile-dropdown-content a {
    display: block;
    padding: 10px 16px;
    color: #333;
    text-decoration: none;
}
.profile-dropdown-content a:hover {
    background-color: #f1f1f1;
}
.logout-link {
    color: #dc3545;
    border-top: 1px solid #eee;
}
</style>

<nav class="navbar">
    <div class="logo">
        <svg height="30" width="30" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="40" fill="#8a2be2" />
            <path d="M 30 50 L 70 50" stroke="white" stroke-width="5" />
            <path d="M 50 30 L 50 70" stroke="white" stroke-width="5" />
        </svg>
        <a href="main.php" class="logo-text">Cybersite</a>
    </div>

    <div class="nav-links">
        <a href="#">Get Started</a>
        <a href="#">Learn</a>

        <div class="dropdown">
            <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Practice</a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="mcq_quiz.php">Mini Quiz</a></li>
                <li><a class="dropdown-item" href="basic_python.php">Python Quiz</a></li>
                <li><a class="dropdown-item" href="ctf_challenge.php">CTF Challenge</a></li>
                <li><a class="dropdown-item" href="forensics_challenge.php">Forensics Challenge</a></li>
            </ul>
        </div>

        <a href="chat.php">AI Chatbot</a>

        <div class="dropdown">
            <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">About</a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Contact Us</a></li>
                <li><a class="dropdown-item" href="#">About Cybersite</a></li>
            </ul>
        </div>

        <div id="auth-section"></div>
    </div>
</nav>

<script>
const userData = <?= json_encode($GLOBALS['user_data'] ?? null); ?>;
function updateNavigation() {
    const authSection = document.getElementById('auth-section');
    if (userData && userData.user_id) {
        authSection.innerHTML = `
            <div class="profile-dropdown" id="profileDropdown">
                <button class="profile-button" onclick="toggleProfileDropdown()">
                    <div class="profile-avatar">${userData.name.charAt(0).toUpperCase()}</div>
                    ${userData.name}
                    <span style="font-size:10px;">▼</span>
                </button>
                <div class="profile-dropdown-content">
                    <div class="user-info">
                        <div class="user-name">${userData.name}</div>
                        <div class="user-email">${userData.email}</div>
                    </div>
                    <a href="user_profile.php">My Profile</a>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="settings.php">Settings</a>
                    <a href="progress.php">My Progress</a>
                    ${userData.user_role === 'admin' ? `<a href="admin_dashboard.php">Admin Dashboard</a>` : ''}
                    <a href="logout.php" class="logout-link">Logout</a>
                </div>
            </div>
        `;
    } else {
        authSection.innerHTML = `<a href="login.php" class="btn btn-outline-light btn-sm">Log In</a>`;
    }
}
function toggleProfileDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    dropdown.classList.toggle('show');
}
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('profileDropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        dropdown.classList.remove('show');
    }
});
document.addEventListener('DOMContentLoaded', updateNavigation);
</script>
