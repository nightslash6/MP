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
    // Delete all questions under this category
    $stmt1 = $conn->prepare("DELETE FROM questions WHERE category_id = ?");
    $stmt1->bind_param("i", $category_id);
    $stmt1->execute();
    $stmt1->close();

    // Delete the category itself
    $stmt2 = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt2->bind_param("i", $category_id);
    $stmt2->execute();
    $stmt2->close();

    // Commit transaction
    $conn->commit();

    $_SESSION['message'] = ['successful' => 'Category and all related questions deleted successfully!'];
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
