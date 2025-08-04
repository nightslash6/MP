<?php
session_start();
require 'config.php';

// Only admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
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
</head>
<body>
<?php include 'navbar.php'; ?>

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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
