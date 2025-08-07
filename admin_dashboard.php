<?php
session_start();
require 'config.php';

// Session timeout handling
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
$_SESSION['last_activity'] = time();

$conn = db_connect();

// Fetch user data
$user_data = null;
if (isset($_SESSION['user_id']) && $_SESSION['user_role']==='admin') {
    $stmt = $conn->prepare("SELECT user_id, name, email, user_role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();
    }
    $stmt->close();
}else{
    header("Location: login.php");
    exit;
}
$GLOBALS['user_data'] = $user_data;

// Dashboard metrics
$totalUsers = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="mstyles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #4a47a3, #6f9bb6);
            color: white;
            padding: 2rem;
            border-radius: 10px;
        }
        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.08);
            transition: 0.3s ease;
        }
        .card:hover {
            transform: translateY(-4px);
        }
        .section-title {
            margin-top: 3rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container mt-5 mb-5">
    <div class="dashboard-header mb-4">
        <h1 class="mb-1">Welcome, <?= htmlspecialchars($user_data['name'] ?? 'Admin') ?></h1>
        <p class="mb-0">Manage users, questions, and categories from one place.</p>
    </div>

    <!-- Stats -->
    <div class="row g-4">
        <div class="col-md-20">
            <div class="card text-center py-4">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="display-6"><?= $totalUsers ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories -->
    <h3 class="section-title">Manage Admin Pages</h3>
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card text-center py-4">
                <div class="card-body">
                    <h5 class="card-title">Forensics<br><small>(Shayaan)</small></h5>
                    <a href="forensics_admin_manage.php" class="btn btn-outline-primary mt-2">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center py-4">
                <div class="card-body">
                    <h5 class="card-title">MCQ Quiz<br><small>(Wei Hong)</small></h5>
                    <a href="mcq_quiz_admin.php" class="btn btn-outline-primary mt-2">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center py-4">
                <div class="card-body">
                    <h5 class="card-title">CTF<br><small>(Chee Chong)</small></h5>
                    <a href="admin_ctf.php" class="btn btn-outline-primary mt-2">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center py-4">
                <div class="card-body">
                    <h5 class="card-title">Python<br><small>(Shu Xuan)</small></h5>
                    <a href="admin_python.php" class="btn btn-outline-primary mt-2">Manage</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
