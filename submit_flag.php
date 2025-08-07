<?php
session_start();
require 'config.php';
$conn = db_connect();

$cid = (int) ($_POST['cid'] ?? 0);
$flag = trim($_POST['flag'] ?? '');
$uid = $_SESSION['user_id'] ?? null;

if (!$uid) {
    die("Login required.");
}

$stmt = $conn->prepare("SELECT flag FROM challenges WHERE id = ?");
$stmt->bind_param("i", $cid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Challenge not found.");
}

$row = $result->fetch_assoc();
$correctFlag = $row['flag'];

$isCorrect = ($flag === $correctFlag);

if ($isCorrect) {
    $stmt = $conn->prepare("INSERT IGNORE INTO solves (user_id, challenge_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $uid, $cid);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE challenges SET solves = solves + 1 WHERE id = ?");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Flag Submission</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding-top: 100px;
            background: #f7f7f7;
        }
        .message {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: <?= $isCorrect ? 'green' : 'red' ?>;
        }
        .try-again {
            margin-top: 10px;
        }
        button {
            padding: 10px 20px;
            font-size: 1rem;
            cursor: pointer;
        }
        a {
            text-decoration: none;
        }
    </style>
</head>
<body>
    <nav class="fixed-top"><?php include 'navbar.php' ?></nav>
    <div class="message">
        <?= $isCorrect ? "✅ Correct! Flag captured." : "❌ Incorrect flag." ?>
    </div>

    <!-- Back Button -->
    <?php if ($isCorrect): ?>
        <a href="ctf_challenge.php" class="back-button">← Back to Challenges</a>
    <?php endif; ?>

    <?php if (!$isCorrect): ?>
        <div class="try-again">
            <form action="challenge.php?id=<?= $cid ?>" method="get">
                <input type="hidden" name="id" value="<?= $cid ?>">
                <button type="submit">🔁 Try Again</button>
            </form>
        </div>
    <?php endif; ?>
    <link rel="stylesheet" href="test.css">
</body>
</html>

