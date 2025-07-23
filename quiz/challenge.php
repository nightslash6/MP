<?php
include 'config.php';
$conn = db_connect(); // ✅ Ensure DB connection

$id = (int) $_GET['id']; // ✅ Sanitize input
$result = $conn->query("SELECT * FROM challenges WHERE id=$id");
$challenge = $result->fetch_assoc();

if (!$challenge) {
    echo "<p>Challenge not found.</p>";
    exit;
}
?>

<a href="ctf_challenge.php" class="back-button">← Back to Challenges</a>

<h2><?= htmlspecialchars($challenge['title']) ?></h2>
<p><?= nl2br(htmlspecialchars($challenge['description'])) ?></p>
<p><?= nl2br(htmlspecialchars($challenge['question'])) ?></p>
<link rel="stylesheet" href="test.css">

<form method="POST" action="submit_flag.php">
    <input type="hidden" name="cid" value="<?= $challenge['id'] ?>">
    <input name="flag" placeholder="Enter flag..." required>
    <input type="submit" value="Submit">
</form>

<!-- 🔗 Challenge-related files -->
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
        ("🕵️‍♂️ Hint: Bots have their own roadmap... maybe in the /flag.txt?")
    </ul>

<?php else: ?>
    <p><em>No downloadable files for this challenge.</em></p>   
<?php endif; ?>
