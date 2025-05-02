<?php
/*session_start();
session_regenerate_id()
require 'config.php'*/

//test 

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.'); window.location.href = 'reset_password.php? ';</script>";
    } else {
        $conn = db_connect();

        // Update the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        $stmt->execute();

        header("Location: login.php?reset=success");
        exit();
    } 

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Cambria';
        }

        body {
            background: url('login_background.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            height: 100vh;
            backdrop-filter: blur(5px);
            padding-left: 190px;
        }

        .resetpassword-container {
            background: #088f8f; /*blue green*/
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 450px;
            left: 0;
        }

        h1 {
            font-size: 26px;
            font-weight: 600;
            color: #000;
            margin-bottom: 20px;
            max-width: 400px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 15px;
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
        }

        button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: #5F9EA0; /*cadet blue*/
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color:  #40826D; /*viridian*/
        }

        .links {
            margin-top: 15px;
            font-size: 14px;
        }

        .links a {
            color:#9FE2BF; /*seafoam green*/
            text-decoration: none;
            font-weight: bold;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="resetpassword-container">
        <h1>Reset Password</h1>
        <form action="" method="post">
            <div class="form-group">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_GET['user_id'] ?? ''); ?>"> <!--???-->
                <label for="old_password">Current Password:</label><br> <!--for verification-->
                <input type="old_password" name="old_password" id="old_password" required><br>
                <label for="password">New Password:</label><br>
                <input type="password" name="password" id="password" required><br>
                <label for="confirm_password">Confirm Password:</label><br>
                <input type="password" name="confirm_password" id="confirm_password" required><br>
            </div>
            <button type="submit" name="submit">Reset Password</button>
        </form>
        <div class="links">
            <p><a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>
