<?php
// get_levels_with_progress.php
require 'config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user = intval($_SESSION['user_id']);
$conn = db_connect();

$sql = "
  SELECT
    l.category_id,
    c.category_name,
    l.level_id,
    l.level_name,
    l.level_description,
    l.required_score,
    COALESCE(up.level_completed, FALSE) AS level_completed,
    -- unlocked if first level in category OR previous level completed
    (l.level_id = 1
     OR EXISTS (
       SELECT 1
       FROM user_progress up2
       JOIN levels l2
         ON up2.level_id = l2.level_id
        AND l2.category_id = l.category_id
       WHERE up2.user_id        = ?
         AND up2.level_id       = l.level_id - 1
         AND up2.level_completed = TRUE
     )) AS is_unlocked
  FROM levels l
  JOIN categories c
    ON c.category_id = l.category_id
  LEFT JOIN user_progress up
    ON up.user_id    = ?
   AND up.level_id   = l.level_id
  ORDER BY c.category_name, l.level_id
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $user, $user);
$stmt->execute();
$result = $stmt->get_result();
$levels = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($levels);
