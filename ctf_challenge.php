<?php
session_start();
include 'config.php';
$conn = db_connect();

// User session check (edit as needed)
$user_data = null;
if (isset($_SESSION['user_id'])) {
    $user_stmt = $conn->prepare("SELECT user_id, name, email FROM users WHERE user_id = ?");
    $user_stmt->bind_param("i", $_SESSION['user_id']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    if ($user_result->num_rows === 1) {
        $user_data = $user_result->fetch_assoc();
    }
    $user_stmt->close();
} else {
    header('Location: login.php');
    exit;
}

// Fetch all challenges
$stmt = $conn->prepare("SELECT * FROM challenges ORDER BY id");
$stmt->execute();
$result = $stmt->get_result();
$challenges = [];
while ($row = $result->fetch_assoc()) $challenges[] = $row;

// Fetch unique difficulties
$difficulties = [];
$diffResult = $conn->query("SELECT DISTINCT difficulty FROM challenges");
while ($row = $diffResult->fetch_assoc()) $difficulties[] = $row['difficulty'];
sort($difficulties);

// Fetch unique categories
$categories = [];
$catResult = $conn->query("SELECT DISTINCT category FROM challenges");
while ($row = $catResult->fetch_assoc()) $categories[] = $row['category'];
sort($categories);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>CTF Challenges</title>
    <link rel="stylesheet" href="ctf.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="mstyles.css" />
    <style>
        .sidebar {
            min-width: 200px;
            padding: 24px;
            background: #161622;
            color: #fff;
            border-radius: 14px;
            margin: 30px 25px 0 12px;
            height: fit-content;
        }
        .sidebar h3 {margin-top:10px;}
        .sidebar select {
            width: 96%;
            padding: 8px 10px;
            margin-bottom: 14px;
            border-radius: 7px;
            border: none;
        }
        .dashboard-container { display:flex; flex-direction:row;}
        .challenges-grid {
            flex:1;
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
            align-items: flex-start;
            padding: 30px 16px 16px 0;
            min-width: 0;
        }
        .challenge-card {
            background: #fff;
            border-radius: 12px;
            padding: 22px 23px;
            width: 290px;
            margin-bottom: 12px;
            box-shadow: 0 2px 11px #0001;
            border: 2px solid #e1ebfa;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .challenge-card .card-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .category { font-size: 14px; color: #144669; }
        .difficulty { font-size: 14px; font-weight: bold;}
        .difficulty.easy    { color: #14ad4d; }
        .difficulty.medium  { color: #e3a500; }
        .difficulty.hard    { color: #e31b23; }
        .challenge-card h4 {margin: 10px 0 9px;}
        .challenge-card p {margin: 2px 0;}
        @media (max-width:900px){
            .dashboard-container {flex-direction:column;}
            .sidebar {margin-bottom:18px;}
            .challenges-grid {padding:0;}
            .challenge-card {width:100%; min-width:240px;}
        }
    </style>
</head>
<body>
    <nav class="fixed-top"><?php include 'navbar.php' ?></nav>
    <header class="top-header" id="topHeader">
        <h1>Welcome to CTF Challenges</h1>
    </header>
<div class="dashboard-container">
    <aside class="sidebar">
        <h3>Difficulty</h3>
        <select id="difficultyFilter">
            <option value="All">All Difficulties</option>
            <?php foreach ($difficulties as $diff): ?>
                <option value="<?= htmlspecialchars($diff) ?>"><?= htmlspecialchars($diff) ?></option>
            <?php endforeach; ?>
        </select>

        <h3>Category</h3>
        <select id="categoryFilter">
            <option value="All">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
        </select>
    </aside>

    <main class="challenges-grid" id="challengeContainer">
        <!-- Cards are rendered here by JS -->
    </main>
</div>

<script>
const challenges = <?= json_encode($challenges) ?>;
const diffColors = { easy:'easy', medium:'medium', hard:'hard' };

function createChallengeCard(chal) {
    let dClass = diffColors[chal.difficulty.toLowerCase()] || '';
    let rating;
    switch (chal.difficulty.toLowerCase()) {
        case 'easy':   rating = Math.floor(Math.random()*11)+90; break;
        case 'medium': rating = Math.floor(Math.random()*15)+75; break;
        case 'hard':   rating = Math.floor(Math.random()*15)+60; break;
        default:       rating = Math.floor(Math.random()*26)+70;
    }
    const card = document.createElement('div');
    card.className = 'challenge-card';
    card.innerHTML = `
        <div class="card-header">
            <span class="category">${chal.category}</span>
            <span class="difficulty ${dClass}">${chal.difficulty}</span>
        </div>
        <h4><a href="challenge.php?id=${chal.id}">${chal.title}</a></h4>
        <p>${Number(chal.solves).toLocaleString()} solves</p>
        <p>${rating}% <i class="fa-solid fa-thumbs-up"></i></p>
    `;
    return card;
}

function filterChallenges() {
    const diff = document.getElementById('difficultyFilter').value;
    const cat  = document.getElementById('categoryFilter').value;
    const container = document.getElementById('challengeContainer');
    container.innerHTML = '';

    const filtered = challenges.filter(chal => {
        const diffMatch = (diff === 'All' || chal.difficulty === diff);
        const catMatch = (cat === 'All' || chal.category === cat);
        return diffMatch && catMatch;
    });
    if (filtered.length === 0) {
        container.innerHTML = '<p>No challenges found for selected filters.</p>';
        return;
    }
    filtered.forEach(chal => { container.appendChild(createChallengeCard(chal)); });
}

document.getElementById('difficultyFilter').addEventListener('change', filterChallenges);
document.getElementById('categoryFilter').addEventListener('change', filterChallenges);

filterChallenges();
</script>
</body>
</html>
