<?php
session_start();
session_regenerate_id(true);

require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$UserRecord = null;

function getUserRecord($user_id){
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT name, email, phone_number FROM users WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $record = null;
    if ($result->num_rows > 0) {
        $record = $result->fetch_assoc();
    }
    
    $stmt->close();
    $conn->close();
    
    return $record;
}

// Fetch user record
$UserRecord = getUserRecord($user_id);

// If no record found, redirect to login
if (!$UserRecord) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Cybersite</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(rgb(82, 139, 205), rgb(107, 89, 189));
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .profile-container {
            max-width: 500px;
            width: 100%;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #5F9EA0 0%, #40826D 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 32px;
            color: white;
            margin: 0 auto 20px auto;
        }

        h1 {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }

        .user-info {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .profile_display {
            text-align: left;
        }

        label {
            font-weight: 500;
            color: #555;
            margin-bottom: 8px;
            display: block;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            margin-bottom: 15px;
            transition: border-color 0.3s ease;
        }

        input[readonly] {
            background-color: #f5f5f5;
            cursor: not-allowed;
            color: #333;
        }

        .button-container {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .home-button, .edit-button {
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .home-button {
            background: #5F9EA0; /* cadet blue */
            color: white;
        }

        .home-button:hover {
            background: #4c8b8d;
        }

        .edit-button {
            background: #90EE90; /* light green */
            color: #333;
        }

        .edit-button:hover {
            background: #7FDD7F;
        }

        @media (max-width: 480px) {
            .profile-container {
                padding: 30px 20px;
            }
            
            .button-container {
                flex-direction: column;
                align-items: center;
            }
            
            .home-button, .edit-button {
                width: 100%;
                max-width: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-avatar">
            <?php echo strtoupper(substr($UserRecord['name'], 0, 1)); ?>
        </div>
        
        <h1>Personal Information</h1>

        <div class="user-info">
            <div class="profile_display">
                <label for="name">Username:</label>
                <input type="text" id="name" name="name" readonly value="<?php echo htmlspecialchars($UserRecord['name']); ?>">
        
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" readonly value="<?php echo htmlspecialchars($UserRecord['email']); ?>">
            
                <label for="phone">Phone Number:</label>
                <input type="text" id="phone" name="phone" readonly value="<?php echo htmlspecialchars($UserRecord['phone_number'] ?? 'Not provided'); ?>">
            </div>
        </div>

        <div class="button-container">
            <a href="main.php" class="home-button">← Back to Home</a>
            <button type="button" class="edit-button" onclick="enableEdit()">Edit Profile</button>
        </div>
    </div>

    <script>
        function enableEdit() {
            // This function can be expanded to allow editing
            alert('Edit functionality can be implemented here');
            // You could redirect to an edit profile page or enable inline editing
            // window.location.href = 'edit_profile.php';
        }
    </script>
</body>
</html>