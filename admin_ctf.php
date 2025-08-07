<?php
session_start();
require 'config.php';
$conn = db_connect();

    // Add challenge
    if (isset($_POST['add_challenge'])) {
        $title = $_POST['title'];
        $category = $_POST['category'];
        $difficulty = $_POST['difficulty'];

        $stmt = $conn->prepare("INSERT INTO challenges (title, category, difficulty, solves) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("sss", $title, $category, $difficulty);
        $stmt->execute();
        $stmt->close();
    }

    // Delete challenge
    if (isset($_POST['delete_challenge'])) {
        $id = $_POST['challenge_id'];
        $stmt = $conn->prepare("DELETE FROM challenges WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }


// Fetch all challenges
$allChallenges = $conn->query("SELECT * FROM challenges ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin CTF Panel</title>
    <link rel="stylesheet" href="admin_ctf.css">
</head>
<body>
    <nav class="fixed-top"><?php include 'navbar.php' ?></nav>
    <header>
        <h1>CTF Admin Panel</h1>
    </header>

    <!-- ADMIN PANEL START -->
    <section class="admin-panel">
        <h2>Add New Challenge</h2>
        <form method="POST" class="add-form">
            <input type="text" name="title" placeholder="Challenge Title" required>
            <select name="category" required>
                <option value="Web Exploitation">Web Exploitation</option>
                <option value="Cryptography">Cryptography</option>
                <option value="Reverse Engineering">Reverse Engineering</option>
                <option value="Forensics">Forensics</option>
                <option value="General Skills">General Skills</option>
            </select>
            <select name="difficulty" required>
                <option value="Easy">Easy</option>
                <option value="Medium">Medium</option>
                <option value="Hard">Hard</option>
            </select>
            <button type="submit" name="add_challenge">Add Challenge</button>
        </form>

        <h2>Delete Challenges</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Title</th><th>Category</th><th>Difficulty</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($ch = $allChallenges->fetch_assoc()): ?>
                <tr>
                    <td><?= $ch['id'] ?></td>
                    <td><?= htmlspecialchars($ch['title']) ?></td>
                    <td><?= htmlspecialchars($ch['category']) ?></td>
                    <td><?= htmlspecialchars($ch['difficulty']) ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Delete this challenge?');">
                            <input type="hidden" name="challenge_id" value="<?= $ch['id'] ?>">
                            <button type="submit" name="delete_challenge" class="delete-btn">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
    <!-- ADMIN PANEL END -->
</body>
</html>
