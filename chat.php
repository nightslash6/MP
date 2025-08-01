<?php
session_start();
require 'config.php';
$conn = db_connect();

// Fetch user data
$user_data = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT user_id, name, email, user_role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>AI Chatbot</title>
  <link rel="stylesheet" href="mstyles.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body { background-color: #121212; color: white; }
    .container { margin-top: 60px; }
    .chat-card {
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      border-radius: 10px;
      overflow: hidden;
      background-color: white;
    }
    iframe {
      border: none;
      width: 100%;
      height: 600px;
    }
  </style>
</head>
<body>

  <?php include 'navbar.php'; ?>

  <div class="container">
    <h1 class="mb-4 text-center text-dark">AI Chatbot Assistant</h1>
    <div class="chat-card">
      <iframe
        src="https://www.chatbase.co/chatbot-iframe/eL8-LhqxDLzhQl7IBXcFh"
        allow="clipboard-write">
      </iframe>
    </div>
  </div>

</body>
</html>
