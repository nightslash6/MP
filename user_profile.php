<?php
session_start();
session_regenerate_id(true);

require 'config.php';

$user_id = $_SESSION['user_id'] ?? '';
$UserRecord = [];

function getUserRecord($user_id){
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT name, email, phone_number FROM users WHERE user_id=?");
    $stmt->bind_param("i",$user_id);
    $stmt->execute();
    $Record = $stmt->get_result();
    $stmt->close();
    $conn->close();
    
    return $Record->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET'){
    $UserRecord = getUserRecord($user_id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            font-family: Cambria, serif;
            background: #FFE4E1;
        }
        
         .profile-container {
            background: #a0a0a0; /*grey*/
            margin: auto;
            position: relative;
            top: 80px;
            padding: 60px;
            border-radius: 15px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 400px;
        }

        .profile_display {
            align-items: center;
        }

        h1 {
            font-size: 26px;
            font-weight: 600;
            color: #000; 
            margin-bottom: 20px;
            max-width: 400px;
        }

        label {
            font-size: 14px;
            font-weight: 600;
            color: #000;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            margin-top: 5px;
            margin-bottom: 15px;
        }

        .home-button {
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
            font-family: Cambria, serif;
            font-size: 14px;
            color: black;
            background: #A7C7E7;
            margin-right: auto;
        }

        .home-button:hover {
            background: #78b0ec;
        }

    </style>
</head>
<body>
    <div class="profile-container">
        <h1>Personal Information</h1>

        <form action="" method="get">
            <?php if (!empty($studentRecords)): ?>
                <?php foreach ($UserRecords as $row): ?>
                <div class="profile_display">
                    <label for="name">Username:</label>
                    <input type="text" id="name" name="name" readonly <?php echo htmlspecialchars(trim($row['name'])); ?> ><br>
            
                    <label for="email">Email:</label>
                    <input type="text" id="email" name="email" readonly <?php echo htmlspecialchars(trim($row['email'])); ?> ><br>
                
                    <label for="phone">Phone Number:</label>
                    <input type="text" id="phone" name="phone" readonly <?php echo htmlspecialchars(trim($row['phone_number'])); ?> ><br>
                </div>
                <?php endforeach; ?>
             <?php else: 
                echo ("No records found");
             endif; ?>
        </form>

    </div>
</body>
</html>