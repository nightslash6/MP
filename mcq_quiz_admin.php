<?php
// Secure access: Redirect if not admin (implement your admin check)
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Load categories and levels for dropdowns
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

$categories = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name")->fetch_all(MYSQLI_ASSOC);
$levels = $conn->query("SELECT level_id, level_name FROM levels GROUP BY level_id, level_name ORDER BY level_id")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Create MCQ</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="mstyles.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<!-- Main Page Container -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow rounded">
                <div class="card-body">
                    <h3 class="mb-4 text-center text-primary">Create New Quiz Question</h3>
                    <form action="process_mcq_create.php" method="post">

                        <div class="mb-3">
                            <label class="form-label">Question Text</label>
                            <textarea name="question_text" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description (optional)</label>
                            <input type="text" name="description" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select name="question_type" class="form-select" required>
                                <option value="MCQ">MCQ</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>"><?= htmlentities($cat['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Level</label>
                            <select name="level_id" class="form-select" required>
                                <?php foreach ($levels as $lvl): ?>
                                    <option value="<?= $lvl['level_id'] ?>"><?= htmlentities($lvl['level_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Options (one per line)</label>
                            <textarea name="options" class="form-control" rows="4" required></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Correct Answer</label>
                            <input type="text" name="correct_answer" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-purple w-100">Create Question</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Logout Overlay (shared JS feature) -->
<div id="logoutOverlay" class="logout-overlay">
    <div class="logout-spinner">Logging out...</div>
</div>
</html>