<?php
require 'config.php';
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$conn = db_connect();
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$required_fields = ['user_id', 'category_id', 'level_id', 'score', 'questions_answered', 'questions_correct', 'required_score'];
foreach ($required_fields as $field) {
    if (!isset($input[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing field: $field"]);
        exit;
    }
}

// Verify user_id matches session
if ($input['user_id'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'User ID mismatch']);
    exit;
}

$user_id = intval($input['user_id']);
$category_id = intval($input['category_id']);
$level_id = intval($input['level_id']);
$score = intval($input['score']);
$questions_answered = intval($input['questions_answered']);
$questions_correct = intval($input['questions_correct']);
$required_score = intval($input['required_score']);
$level_completed = $score >= $required_score;

try {
    $conn->begin_transaction();
    
    // Update or insert user progress
    $progressQuery = "
        INSERT INTO user_progress (user_id, level_id, current_score, questions_answered, questions_correct, level_completed, completion_time)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        current_score = GREATEST(current_score, VALUES(current_score)),
        questions_answered = IF(VALUES(current_score) > current_score, VALUES(questions_answered), questions_answered),
        questions_correct = IF(VALUES(current_score) > current_score, VALUES(questions_correct), questions_correct),
        level_completed = IF(GREATEST(current_score, VALUES(current_score)) >= VALUES(current_score), VALUES(level_completed), level_completed),
        completion_time = CASE 
            WHEN VALUES(level_completed) = TRUE AND VALUES(current_score) > current_score THEN VALUES(completion_time)
            ELSE completion_time
        END
    ";

    $completion_time = $level_completed ? date('Y-m-d H:i:s') : null;
    $progressStmt = $conn->prepare($progressQuery);
    $progressStmt->bind_param("iiiiiss", $user_id, $level_id, $score, $questions_answered, $questions_correct, $level_completed, $completion_time);
    $progressStmt->execute();

    // --- POINTS AWARD LOGIC (INSERTED HERE) ---
    $points_awarded = $questions_correct * 10; // 10 points per correct answer
    if ($points_awarded > 0) {
        $pointsStmt = $conn->prepare("INSERT INTO user_points (user_id, points) VALUES (?, ?) ON DUPLICATE KEY UPDATE points = points + ?");
        $pointsStmt->bind_param("iii", $user_id, $points_awarded, $points_awarded);
        $pointsStmt->execute();
        $pointsStmt->close();
    }
    // --- END POINTS AWARD LOGIC ---

    // Check if next level should be unlocked
    $level_unlocked = false;
    if ($level_completed && $score >= $required_score) {
        // Check if there's a next level in the same category
        $nextLevelQuery = "
            SELECT l.level_id 
            FROM levels l
            WHERE l.category_id = ? AND l.level_id = ?
            LIMIT 1
        ";
        $nextLevelStmt = $conn->prepare($nextLevelQuery);
        $next_level_id = $level_id + 1;
        $nextLevelStmt->bind_param("ii", $category_id, $next_level_id);
        $nextLevelStmt->execute();
        $nextLevelResult = $nextLevelStmt->get_result();
        
        if ($nextLevelResult->num_rows > 0) {
            $level_unlocked = true;
        }
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'level_completed' => $level_completed,
        'level_unlocked' => $level_unlocked,
        'score' => $score,
        'passed' => $level_completed,
        'points_awarded' => $points_awarded, // Return points awarded to frontend
        'message' => $level_completed ? 'Quiz completed successfully!' : 'Quiz completed. Keep practicing!'
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
