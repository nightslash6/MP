<?php
require 'config.php';
session_start();

// Check if user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Validate category ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
    exit;
}

$category_id = intval($_GET['id']);
$conn = db_connect();

// Begin transaction
$conn->begin_transaction();

try {
    // 1. Delete all user progress for levels in this category
    $stmt_progress = $conn->prepare(
        "DELETE FROM user_progress 
         WHERE level_id IN 
         (SELECT level_id FROM levels WHERE category_id = ?)"
    );
    $stmt_progress->bind_param("i", $category_id);
    $stmt_progress->execute();
    $stmt_progress->close();

    // 2. Delete all questions under this category
    $stmt1 = $conn->prepare("DELETE FROM questions WHERE category_id = ?");
    $stmt1->bind_param("i", $category_id);
    $stmt1->execute();
    $stmt1->close();

    // 3. Delete all levels under this category
    $stmt_levels = $conn->prepare("DELETE FROM levels WHERE category_id = ?");
    $stmt_levels->bind_param("i", $category_id);
    $stmt_levels->execute();
    $stmt_levels->close();

    // 4. Delete the category itself
    $stmt2 = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt2->bind_param("i", $category_id);
    $stmt2->execute();
    $stmt2->close();

    // Commit transaction
    $conn->commit();

    $_SESSION['message'] = ['successful' => 'Category, all related levels, questions, and user progress deleted successfully!'];
    header('Location: mcq_quiz_admin.php?msg=deleted');
    exit;
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();

    $_SESSION['message'] = ['unsuccessful' => 'Error deleting category: ' . $e->getMessage()];
    header('Location: mcq_quiz_admin.php?msg=error');
    exit;
}
?>
