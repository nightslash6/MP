<?php
session_start();
require 'config.php';
$conn = db_connect();

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

$resultUsers = $conn->query("SELECT COUNT(*) AS total_users FROM users");
$rowUsers = $resultUsers->fetch_assoc();
$totalUsers = $rowUsers['total_users'];

$resultQuestions = $conn->query("SELECT COUNT(*) AS total_questions FROM questions");
$rowQuestions = $resultQuestions->fetch_assoc();
$totalQuestions = $rowQuestions['total_questions'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="mstyles.css">
    <style>
        .navbar {
            background-color: #2c2f48;
            padding: 10px 20px;
            color: white;
        }
        .logo-text {
            color: white;
            font-weight: bold;
            text-decoration: none;
        }
        .nav-links a {
            color: white;
            margin-left: 15px;
            text-decoration: none;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
        .container { margin-top: 60px; }
        .card { border: none; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .card:hover { transform: translateY(-3px); transition: 0.3s; }
        .dashboard-header { background: linear-gradient(45deg, #4a47a3, #709fb0); color: white; padding: 2rem; border-radius: 8px; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <a href="main.php" class="logo-text">Cybersite Admin</a>
        </div>
        <div class="nav-links">
            <a href="main.php">Main Site</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-header mb-4">
            <h1>Welcome, Admin</h1>
            <p>Manage your platform easily from this dashboard.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <p class="display-6"><?= $totalUsers ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total Challenges</h5>
                        <p class="display-6"><?= $totalQuestions ?></p>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mt-5 mb-3">Category Admin Pages</h3>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Forensics (Shayaan)</h5>
                        <a href="shayaan_admin_manage.php" class="btn btn-secondary mt-2">Manage</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Crypto (Shayaan)</h5>
                        <a href="shayaan_admin_manage.php#cryptoTab" class="btn btn-secondary mt-2">Manage</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">CTF (Chee Chong)</h5>
                        <a href="chee_ctf_admin.php" class="btn btn-secondary mt-2">Manage</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Python (Shu Xuan)</h5>
                        <a href="shu_python_admin.php" class="btn btn-secondary mt-2">Manage</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
