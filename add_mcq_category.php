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

// Only admins allowed
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';
$errors = [];

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$conn = db_connect();

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateInput($title, &$errors) {
    if (empty($title)) {
        $errors['category_name'] = 'Category name is required.';
    } elseif (!preg_match("/^[a-zA-Z0-9\s\-.,:'()]+$/u", $title)) {
        $errors['category_name'] = 'Invalid characters in category name.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = 'Invalid CSRF token!';
    } else {
        $category_name = sanitize($_POST['category_name'] ?? '');
        $category_description = sanitize($_POST['category_description'] ?? '');

        validateInput($category_name, $errors);

        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO categories (category_name, category_description) VALUES (?, ?)");
            $stmt->bind_param("ss", $category_name, $category_description);

            if ($stmt->execute()) {
                $category_id = $conn->insert_id; // The new category's ID
                $stmt->close();

                // ==== Insert default levels for the new category ====
                $default_levels = [
                    [
                        'level_id' => 1,
                        'level_name' => 'Beginner',
                        'level_description' => 'Introduction to basic concepts.',
                        'required_score' => 40,
                        'questions_count' => 5,
                        'unlock_previous_level' => null,
                        'badge_icon' => 'beginner.png'
                    ],
                    [
                        'level_id' => 2,
                        'level_name' => 'Intermediate',
                        'level_description' => 'Intermediate level questions.',
                        'required_score' => 50,
                        'questions_count' => 7,
                        'unlock_previous_level' => 1,
                        'badge_icon' => 'intermediate.png'
                    ],
                    [
                        'level_id' => 3,
                        'level_name' => 'Advanced',
                        'level_description' => 'Challenging questions for advanced users.',
                        'required_score' => 60,
                        'questions_count' => 10,
                        'unlock_previous_level' => 2,
                        'badge_icon' => 'advanced.png'
                    ]
                ];

                $insertLevel = $conn->prepare("INSERT INTO levels 
                    (category_id, level_id, level_name, level_description, required_score, questions_count, unlock_previous_level, badge_icon)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

                foreach ($default_levels as $level) {
                    $insertLevel->bind_param(
                        "iissiiis",
                        $category_id,
                        $level['level_id'],
                        $level['level_name'],
                        $level['level_description'],
                        $level['required_score'],
                        $level['questions_count'],
                        $level['unlock_previous_level'],
                        $level['badge_icon']
                    );
                    $insertLevel->execute();
                }
                $insertLevel->close();
                // ==== End of default levels insertion ====

                $_SESSION['message'] = ['successful' => 'Category added successfully!'];
                header('Location: mcq_quiz_admin.php');
                exit;
            } else {
                $message = 'Database error: ' . $stmt->error;
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Category - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="mstyles.css" />
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container mt-5 mb-5" style="max-width: 600px;">
    <h2>Add New Category</h2>

    <?php if ($message): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>" />

        <div class="mb-3">
            <label for="category_name" class="form-label">Category Name*</label>
            <input type="text" class="form-control <?= isset($errors['category_name']) ? 'is-invalid' : '' ?>" id="category_name" name="category_name" required value="<?= $_POST['category_name'] ?? '' ?>" />
            <div class="invalid-feedback"><?= $errors['category_name'] ?? '' ?></div>
        </div>

        <div class="mb-3">
            <label for="category_description" class="form-label">Category Description (optional)</label>
            <textarea class="form-control" id="category_description" name="category_description" rows="3"><?= $_POST['category_description'] ?? '' ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Add Category</button>
        <a href="mcq_quiz_admin.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>

</body>
</html>
