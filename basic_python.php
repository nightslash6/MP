<?php
session_start();
require 'config.php';

$conn = db_connect();

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
      display: flex;
      margin: 0;
      font-family: 'Arial';
      height: 100%;
    }

    .sidebar {
      width: 200px;
      background-color: #2c3e50;
      color: white;
      min-height: 100vh;
      height: auto;
      padding: 1rem;
      box-sizing: border-box; /*Keeps layout consistent when adding padding or borders*/
    }

    .sidebar h2 {
      font-size: 18px;
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
      position: relative;
    }

    .sidebar button:hover,
    .sidebar button.selected {
      background-color: #34495e;
      padding-left: 1rem;
      padding-right: 1rem;
      box-sizing: border-box;
    }

     .topic-header {
      display: flex;
      justify-content: space-between; /*what this for*/
      align-items: center;
      width: 100%;
      position: relative;
    }

    .dropdown-toggle {
      cursor: pointer;
      padding: 10px 1rem;
      font-size: 14px;
      user-select: none;
      position: absolute;
      right: 0;
      top: 0;
      height: 100%;
      display: flex;
      align-items: center;
    }

    .content {
      flex-grow: 1;
      padding: 2rem;
      max-width: 1000px;
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
  
  </style>
</head>
<body>

<div class="sidebar">
  <button><a href='main.php' id="backbtn">← Back to main</a></button>
  <h2>Topics</h2>
  <button onclick="showTab('tab-welcome')" class="selected">Welcome to Python</button>
  
  <?php foreach ($topics as $topic): ?>
      <div class="topic-header">
        <button onclick="showTab('tab<?= $topic['python_id'] ?>')"><?= htmlspecialchars($topic['topic']) ?></button>
        <?php if (!empty($subtopics_by_topic[$topic['python_id']])): ?>
          <span class="dropdown-toggle" onclick="toggleSubtopics(this)">▼</span>
        <?php endif; ?>
      </div>

      <?php if (!empty($subtopics_by_topic[$topic['python_id']])): ?>
        <div class="subtopics" style="display: none;"> <!--Hides the subtopics by default--> 
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
      <?php
      $content_lines = explode('//', $topic['content']); //Split the examples by "//" which are in the db  (e.g. age = 30//name = "John")
      foreach ($content_lines as $c_line) {
        echo htmlspecialchars($c_line)  .  "<br>";
      }
      ?>

      <h4>Example:</h4>
      <?php
      $example_lines = explode('//', $topic['example']); 
      foreach ($example_lines as $line) {
        echo htmlspecialchars($line)  .  "<br>";
      }
      ?>

      <h4>Try it:</h4>
      <p><?= htmlspecialchars($topic['question']) ?></p>
      <textarea id="code-tab<?= $topic['python_id'] ?>"></textarea>
      <button class="run-btn" onclick="runCode('code-tab<?= $topic['python_id'] ?>', 'output-tab<?= $topic['python_id'] ?>')">Run Code</button>
      <pre id="output-tab<?= $topic['python_id'] ?>" class="output"></pre>

    </div>
  <?php endforeach; ?>

  <?php foreach ($subtopics_by_topic as $topic_subs): ?>
    <?php foreach ($topic_subs as $sub): ?>
      <div id="subtab<?= $sub['subtopic_id'] ?>" class="tab-content">
        <h2><?= htmlspecialchars($sub['subtopic_title']) ?></h2>
        <?php
        $content_lines = explode('//', $sub['content']); //Split the examples by "//" which are in the db  (e.g. age = 30//name = "John")
        foreach ($content_lines as $c_line) {
          echo htmlspecialchars($c_line)  .  "<br>";
        }
        ?>

        <h4>Example:</h4>
        <?php
        $example_lines = explode('//', $sub['example']);
        foreach ($example_lines as $line) {
          echo htmlspecialchars($line) . "<br>";
        }
        ?>

        <h4>Try it:</h4>
        <p><?= htmlspecialchars($sub['question']) ?></p>
        <textarea id="code-subtab<?= $sub['subtopic_id'] ?>"></textarea>
        <button class="run-btn" onclick="runCode('code-subtab<?= $sub['subtopic_id'] ?>', 'output-subtab<?= $sub['subtopic_id'] ?>')">Run Code</button>
        <pre id="output-subtab<?= $sub['subtopic_id'] ?>" class="output"></pre>
      </div>
    <?php endforeach; ?>
  <?php endforeach; ?>
</div>

<script>
function showTab(tabId) {
  const tabs = document.querySelectorAll('.tab-content');
  tabs.forEach(tab => tab.classList.remove('active')); //Hide all
  document.getElementById(tabId).classList.add('active'); //Show selected

  const buttons = document.querySelectorAll('.sidebar button');
  buttons.forEach(btn => btn.classList.remove('selected')); //Remove 'selected' class 

  //Add 'selected' class to the clicked button
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
  toggle.innerHTML = isVisible ? '▼' : '▲';
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