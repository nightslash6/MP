<?php
session_start();
require 'config.php';

$conn = db_connect();

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

// Fetch all Python topics from database
$stmt = $conn->prepare("SELECT * FROM python");
$stmt->execute();
$result = $stmt->get_result();

$topics = [];
while ($row = $result->fetch_assoc()) {
  $topics[] = $row;
}

//Fetch all Python subtopics from database
$sub_stmt = $conn->prepare("SELECT * FROM python_subtopics");
$sub_stmt->execute();
$sub_result = $sub_stmt->get_result();

$subtopics_by_topic = [];
while ($row = $sub_result->fetch_assoc()) {
  $subtopics_by_topic[$row['python_id']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Learn Python</title>
  <script src="https://cdn.jsdelivr.net/npm/skulpt@latest/dist/skulpt.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/skulpt@latest/dist/skulpt-stdlib.js"></script>
  <style>
    body {
      margin: 0;
      font-family: 'Arial';
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .main-container {
      display: flex;
      flex: 1;
      padding-top: 60px;
    }

    .sidebar {
      width: 250px;
      background-color: #2c3e50;
      color: white;
      padding: 1rem;
      box-sizing: border-box;
      position: fixed;
      top:  0;
      bottom: 0;
      left: 0;
      overflow-y: auto;
    }

    .sidebar h2 {
      font-size: 18px;
      margin-top: 1rem;
    }

    .sidebar button {
      background: none;
      border: none;
      color: white;
      font-size: 16px;
      text-align: left;
      width: 100%;
      padding: 10px 1rem;
      cursor: pointer;
      display: block;
      box-sizing: border-box;
    }

    .sidebar button:hover,
    .sidebar button.selected {
      background-color: #34495e;
    }

    .sidebar-dropdown-toggle {
    cursor: pointer;
    padding: 10px;
    font-size: 14px;
    user-select: none;
    transition: transform 0.2s; /* Smooth rotation animation */
    }

    /* Rotate arrow when subtopics are open */
    .sidebar-dropdown-toggle.active {
    transform: rotate(180deg);
    }

    .topic-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
    }
    
    .content {
      flex-grow: 1;
      padding: 2rem;
      max-width: 1000px;
      margin-left: 250px; /* Match sidebar width */
      overflow-y: auto; /* Enable scrolling */
    }

    .tab-content {
      display: none;
    }

    .active {
      display: block;
    }

    textarea {
      width: 100%;
      height: 150px;
      font-family: 'Consolas';
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      box-sizing: border-box;
    }

    button.run-btn {
      margin-top: 10px;
      background-color: #3498db;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 4px;
      cursor: pointer;
    }

    pre.output {
      background: #f4f4f4;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      margin-top: 10px;
      min-height: 40px;
    }

    #backbtn {
      text-decoration: none;
      font-size: 16px;
      color: white;
      display: block;
      padding: 5px 0;
    }
  
    .content-section {
      white-space: pre-wrap;
      word-wrap: break-word;
      margin-bottom: 1rem;
    }

    pre {
      background: #f4f4f4;
      padding: 1rem;
      border-radius: 4px;
      overflow-x: auto;
      white-space: pre;
    }

    code {
      font-family: 'Consolas', 'Monaco', monospace;
      font-size: 0.9em;
    }
  </style>
</head>
<body>
<nav class="fixed-top"><?php include 'navbar.php' ?></nav>

<div class="main-container">
  <div class="sidebar">
    <button><a href='main.php' id="backbtn">← Back to main</a></button>
    <h2>Topics</h2>
    <button onclick="showTab('tab-welcome')" class="selected">Welcome to Python</button>
    
    <?php foreach ($topics as $topic): ?>
        <div class="topic-header">
          <button onclick="showTab('tab<?= $topic['python_id'] ?>')"><?= htmlspecialchars($topic['topic']) ?></button>
          <?php if (!empty($subtopics_by_topic[$topic['python_id']])): ?>
            <span class="sidebar-dropdown-toggle" onclick="toggleSubtopics(this)">⌄</span>
          <?php endif; ?>
        </div>

        <?php if (!empty($subtopics_by_topic[$topic['python_id']])): ?>
          <div class="subtopics" style="display: none;">
            <?php foreach ($subtopics_by_topic[$topic['python_id']] as $sub): ?>
              <button onclick="showTab('subtab<?= $sub['subtopic_id'] ?>')" style="padding-left: 2rem; font-size: 14px;"><?= htmlspecialchars($sub['subtopic_title']) ?></button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
    <?php endforeach; ?> 
  </div>

  <div class="content">
    <div id="tab-welcome" class="tab-content active">
      <h1>Welcome to your Python learning journey!</h1>
      <p>Choose a topic to start.</p>
    </div>

    <?php foreach ($topics as $topic): ?>
      <div id="tab<?= $topic['python_id'] ?>" class="tab-content">
        <h1><?= htmlspecialchars($topic['topic']) ?></h1>
        
        <div class="content-section">
          <?= nl2br(htmlspecialchars($topic['content'])) ?>
        </div>

        <?php if (!empty($topic['example'])): ?>
          <h4>Example:</h4>
          <pre><code><?= htmlspecialchars($topic['example']) ?></code></pre>
        <?php endif; ?>

        <h4>Try it:</h4>
        <p><?= nl2br(htmlspecialchars($topic['question'])) ?></p>
        <textarea id="code-tab<?= $topic['python_id'] ?>"></textarea>
        <button class="run-btn" onclick="runCode('code-tab<?= $topic['python_id'] ?>', 'output-tab<?= $topic['python_id'] ?>')">Run Code</button>
        <pre id="output-tab<?= $topic['python_id'] ?>" class="output"></pre>
      </div>
    <?php endforeach; ?>

    <?php foreach ($subtopics_by_topic as $topic_id => $topic_subs): ?>
      <?php foreach ($topic_subs as $sub): ?>
        <div id="subtab<?= $sub['subtopic_id'] ?>" class="tab-content">
          <h2><?= htmlspecialchars($sub['subtopic_title']) ?></h2>

          <div class="content-section">
            <?= nl2br(htmlspecialchars($sub['content'])) ?>
          </div>

          <?php if (!empty($sub['example'])): ?>
            <h4>Example:</h4>
            <pre><code><?= htmlspecialchars($sub['example']) ?></code></pre>
          <?php endif; ?>

          <h4>Try it:</h4>
          <p><?= nl2br(htmlspecialchars($sub['question'])) ?></p>
          <textarea id="code-subtab<?= $sub['subtopic_id'] ?>"></textarea>
          <button class="run-btn" onclick="runCode('code-subtab<?= $sub['subtopic_id'] ?>', 'output-subtab<?= $sub['subtopic_id'] ?>')">Run Code</button>
          <pre id="output-subtab<?= $sub['subtopic_id'] ?>" class="output"></pre>
        </div>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </div>
</div>

<script>
function showTab(tabId) {
  const tabs = document.querySelectorAll('.tab-content');
  tabs.forEach(tab => tab.classList.remove('active'));
  document.getElementById(tabId).classList.add('active');

  const buttons = document.querySelectorAll('.sidebar button');
  buttons.forEach(btn => btn.classList.remove('selected'));

  buttons.forEach(btn => {
    if (btn.getAttribute('onclick')?.includes(`'${tabId}'`)) {
      btn.classList.add('selected');
    }
  });
}

function toggleSubtopics(toggle) {
  const subtopics = toggle.parentElement.nextElementSibling;
  const isVisible = subtopics.style.display === 'block';

  subtopics.style.display = isVisible ? 'none' : 'block';
  toggle.classList.toggle('active'); // Toggle rotation
}

function builtinRead(x) {
  if (Sk.builtinFiles === undefined || Sk.builtinFiles["files"][x] === undefined)
    throw "File not found: '" + x + "'";
  return Sk.builtinFiles["files"][x];
}

function runCode(codeId, outputId) {
  const code = document.getElementById(codeId).value;
  const output = document.getElementById(outputId);
  output.innerHTML = '';
  
  Sk.configure({ output: (text) => output.innerHTML += text, read: builtinRead });
  Sk.misceval.asyncToPromise(() => Sk.importMainWithBody("<stdin>", false, code, true))
    .catch(err => output.innerHTML = err.toString());
}
</script>

</body>
</html>