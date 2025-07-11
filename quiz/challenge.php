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

<h2><?= htmlspecialchars($challenge['title']) ?></h2>
<p><?= nl2br(htmlspecialchars($challenge['description'])) ?></p>
<p><?= nl2br(htmlspecialchars($challenge['question'])) ?></p>

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

<?php else: ?>
    <p><em>No downloadable files for this challenge.</em></p>
<?php endif; ?>
