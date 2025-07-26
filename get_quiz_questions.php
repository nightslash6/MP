<?php
require 'config.php';
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

try {
    $conn = db_connect();
    if (!$conn) throw new Exception('Database connection failed');

    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['category_id'], $input['level_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters: category_id and level_id']);
        exit;
    }

    $category_id = intval($input['category_id']);
    $level_id    = intval($input['level_id']);

    // Get questions_count for this level
    $stmt = $conn->prepare(
        "SELECT questions_count 
           FROM levels 
          WHERE category_id = ? 
            AND level_id    = ?"
    );
    $stmt->bind_param("ii", $category_id, $level_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Level not found']);
        exit;
    }
    $questionsNeeded = $res->fetch_assoc()['questions_count'];
    $stmt->close();

    // Fetch level-specific questions
    $stmt = $conn->prepare(
        "SELECT question_id, question_text, description, question_type, options, correct_answer
           FROM questions
          WHERE category_id = ?
            AND level_id    = ?
          ORDER BY RAND()
          LIMIT ?"
    );
    $stmt->bind_param("iii", $category_id, $level_id, $questionsNeeded);
    $stmt->execute();
    $questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Fallback to level-1 if not enough
    if (count($questions) < $questionsNeeded) {
        $remaining = $questionsNeeded - count($questions);
        $stmt = $conn->prepare(
            "SELECT question_id, question_text, description, question_type, options, correct_answer
               FROM questions
              WHERE category_id = ?
                AND level_id IN (0,1)
              ORDER BY RAND()
              LIMIT ?"
        );
        $stmt->bind_param("ii", $category_id, $remaining);
        $stmt->execute();
        $questions = array_merge(
            $questions,
            $stmt->get_result()->fetch_all(MYSQLI_ASSOC)
        );
        $stmt->close();
    }

    if (empty($questions)) {
        echo json_encode(['success' => false, 'message' => 'No questions found for this level']);
        exit;
    }

    echo json_encode([
        'success'   => true,
        'questions' => $questions,
        'total'     => count($questions),
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
    ]);
}
