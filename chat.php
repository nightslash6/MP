


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>AI Chatbot</title>
  <link rel="stylesheet" href="mstyles.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
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
  <!-- Navbar -->
  <nav class="navbar">
    <div class="logo">
      <a href="main.php" class="logo-text">CyberCTF</a>
    </div>
    <div class="nav-links">
      <a href="main.php">Dashboard</a>
      <a href="logout.php">Logout</a>
    </div>
  </nav>

  <div class="container">
    <h1 class="mb-4 text-center">AI Chatbot Assistant</h1>
    <div class="chat-card">
      <iframe
        src="https://www.chatbase.co/chatbot-iframe/eL8-LhqxDLzhQl7IBXcFh"
        allow="clipboard-write">
      </iframe>
    </div>
  </div>
</body>
</html>