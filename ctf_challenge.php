<?php
include 'config.php';
$conn = db_connect();

$difficulty = $_GET['difficulty'] ?? 'All';
$category = $_GET['category'] ?? 'All';

$sql = "SELECT * FROM challenges WHERE 1=1";
if ($difficulty != 'All') {
    $sql .= " AND difficulty='$difficulty'";
}
if ($category != 'All') {
    $sql .= " AND category='$category'";
}
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CTF Challenges</title>
    <link rel="stylesheet" href="ctf.css">
    <!-- Optionally include icons from Font Awesome or Bootstrap -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <header class="top-header" id="topHeader">
        <h1>Welcome to CTF Challenges</h1>
    </header>
<nav class="fixed-top"><?php include 'navbar.php' ?></nav>

<div class="dashboard-container">
    <aside class="sidebar">
        <h3>Difficulty</h3>
        <form method="GET">
            <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
            <ul>
                <li><button type="submit" name="difficulty" value="All" class="<?= $difficulty == 'All' ? 'active' : '' ?>">All Difficulties</button></li>
                <li><button type="submit" name="difficulty" value="Easy" class="<?= $difficulty == 'Easy' ? 'active' : '' ?>">Easy</button></li>
                <li><button type="submit" name="difficulty" value="Medium" class="<?= $difficulty == 'Medium' ? 'active' : '' ?>">Medium</button></li>
                <li><button type="submit" name="difficulty" value="Hard" class="<?= $difficulty == 'Hard' ? 'active' : '' ?>">Hard</button></li>
            </ul>
        </form>

        <h3>Category</h3>
        <form method="GET">
            <input type="hidden" name="difficulty" value="<?= htmlspecialchars($difficulty) ?>">
            <ul>
                <li><button type="submit" name="category" value="All" class="<?= $category == 'All' ? 'active' : '' ?>">All Categories</button></li>
                <li><button type="submit" name="category" value="Web Exploitation" class="<?= $category == 'Web Exploitation' ? 'active' : '' ?>">Web Exploitation</button></li>
                <li><button type="submit" name="category" value="Cryptography" class="<?= $category == 'Cryptography' ? 'active' : '' ?>">Cryptography</button></li>
                <li><button type="submit" name="category" value="Reverse Engineering" class="<?= $category == 'Reverse Engineering' ? 'active' : '' ?>">Reverse Engineering</button></li>
                <li><button type="submit" name="category" value="Forensics" class="<?= $category == 'Forensics' ? 'active' : '' ?>">Forensics</button></li>
                <li><button type="submit" name="category" value="General Skills" class="<?= $category == 'General Skills' ? 'active' : '' ?>">General Skills</button></li>
            </ul>
        </form>
    </aside>

    <main class="challenges-grid">
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
                // Set rating based on difficulty if not already in database
                $difficulty = strtolower($row['difficulty']);
                switch ($difficulty) {
                    case 'easy':
                        $rating = rand(90, 100);
                        break;
                    case 'medium':
                        $rating = rand(75, 89);
                        break;
                    case 'hard':
                        $rating = rand(60, 74);
                        break;
                    default:
                    $rating = rand(70, 95);
                }
            ?>
            <div class="challenge-card">
                <div class="card-header">
                    <span class="category"><?= htmlspecialchars($row['category']) ?></span>
                    <span class="difficulty <?= $difficulty ?>"><?= htmlspecialchars($row['difficulty']) ?></span>
                </div>
                <h4><a href="challenge.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a></h4>
                <p><?= number_format($row['solves']) ?> solves</p>
                <p><?= $rating ?>% <i class="fa-solid fa-thumbs-up"></i></p>
            </div>
        <?php endwhile; ?>
    </main>
    
    <script>
    let lastScrollTop = 0;
    const header = document.getElementById("topHeader");

    window.addEventListener("scroll", function () {
        let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
        if (currentScroll > lastScrollTop) {
            // Scrolling down
            header.style.top = "-10px";
        } else {
            // Scrolling up
            header.style.top = "0";
        }
        lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
    }, false);
</script>
</div>
</body>
</html>
