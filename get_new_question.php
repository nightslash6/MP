<?php
require 'config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$conn = db_connect();
$input = json_decode(file_get_contents('php://input'), true);

$category_id = intval($input['category_id'] ?? 0);
$level_id = intval($input['level_id'] ?? 0);
$exclude_ids = $input['exclude_ids'] ?? []; // array of already used question IDs

if (!$category_id || !$level_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid params']);
    exit;
}

$exclude_sql = '';
if (!empty($exclude_ids)) {
    $placeholders = implode(',', array_fill(0, count($exclude_ids), '?'));
    $exclude_sql = "AND question_id NOT IN ($placeholders)";
}

$sql = "SELECT * FROM questions WHERE category_id = ? AND level_id = ? $exclude_sql ORDER BY RAND() LIMIT 1";
$stmt = $conn->prepare($sql);

$params = [$category_id, $level_id];
$types = "ii";
if (!empty($exclude_ids)) {
    $types .= str_repeat("i", count($exclude_ids));
    $params = array_merge($params, $exclude_ids);
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // You may need to format this to match your quiz structure
    echo json_encode(['success' => true, 'question' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'No more questions available']);
}
?>
