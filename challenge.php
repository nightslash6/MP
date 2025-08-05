<?php
session_start();
include 'config.php';
$conn = db_connect();
if (!$conn) {
    die("Database connection failed.");
}

$user_data = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT user_id, name, email FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();
    }
    $stmt->close();
}else{
    header('Location: login.php');
    exit;
}

$id = (int) $_GET['id'] ?? 'All';
$result = $conn->query("SELECT * FROM challenges WHERE id = $id");
$challenge = $result->fetch_assoc();

if (!$challenge) {
    echo "<p>Challenge not found.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($challenge['title']) ?> - Cybersite</title>
    <link rel="stylesheet" href="test.css">
</head>
<body>
    <nav class="fixed-top"><?php include 'navbar.php' ?></nav>

    <a href="ctf_challenge.php" class="back-button">← Back to Challenges</a>

    <h2><?= htmlspecialchars($challenge['title']) ?></h2>
    <p><?= nl2br(htmlspecialchars($challenge['description'])) ?></p>
    <p><?= nl2br(htmlspecialchars($challenge['question'])) ?></p>

    <form method="POST" action="submit_flag.php">
        <input type="hidden" name="cid" value="<?= $challenge['id'] ?>">
        <input name="flag" placeholder="Enter flag..." required>
        <input type="submit" value="Submit">
    </form>

    <?php if ($challenge['id'] == 29): ?>
        <p><strong>Challenge Files:</strong></p>
        <ul>
            <li><a href="csrf_token_bypass.php" target="_blank">🔒 CSRF-Protected Email Change Page</a></li>
            <li><a href="csrf_attack.html" target="_blank">🎯 Simulated CSRF Attack Page</a></li>
        </ul>

    <?php elseif ($challenge['id'] == 32): ?>
        <p><strong>Challenge Files:</strong></p>
        <ul>
            <li><a href="index.php" target="_blank">📤 Upload Madness Page</a></li>
        </ul>

    <?php elseif ($challenge['id'] == 33): ?>
        <p><strong>Challenge Files:</strong></p>
        <ul>
            <li><a href="supersecret" target="_blank">🤖 Robots hide things in plain text...</a></li>
            <li><a href="robots.txt" target="_blank">🎯 File path of the challenges flag...</a></li>
        </ul>
        <p>🕵️‍♂️ Hint: Bots have their own roadmap... maybe in the /flag.txt?</p>

    <?php else: ?>
        <p><em>No downloadable files for this challenge.</em></p>
    <?php endif; ?>
</body>
</html>
