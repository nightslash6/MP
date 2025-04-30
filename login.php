<?php
/*session_start();
require 'config.php';
session_regenerate_id(true);*/

function login_user($email, $password) {
    $conn = db_connect();
    
    // Sanitize and validate the email
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email'); window.location.href = 'login.php';</script>";
        exit();
    }

    // Fetch user data from the database
    $stmt = $conn->prepare("SELECT user_id, name, password, role FROM users WHERE email = ?"); //can remove role
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result(); // Store the result to check the number of rows

    // Check if a user was found
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $name, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) { //USE PASSWORD HASH AND PASSWORD VERIFY FOR SECURITY
            $_SESSION['user_id'] = $user_id;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;
            $_SESSION['email'] = $email;

            $stmt->close();
            $conn->close();

            if($role === 'student') {
                header("Location: main.php");
            } elseif ($role === 'admin' || $role === 'faculty') {
                header("Location: CRUD2_group_login/home_AdmFac.php"); //remove
            } else {
                echo "<script>alert('Unknown'); window.location.href = 'login.php';</script>";
            }
            exit();
        } else {
            echo "<script>alert('Invalid email or password'); window.location.href = 'login.php';</script>";
            $stmt->close();
            $onn->close();
            exit();
        }
    } else {
        // No user found with the provided email
        echo "<script>alert('Email is not registered'); window.location.href = 'login.php';</script>";
        exit();
        $stmt->close();
        $conn->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    // Retrieve and sanitize the email and password
    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']); // Password does not need htmlspecialchars

    // Call the login function
    login_user($email, $password);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <style>
        * {
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

        .login-container {
            background: #088f8f; /*blue green*/
            padding: 70px;
            border-radius: 15px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.2);
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

        .btn {
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

        .btn:hover {
            background: #40826D; /*viridian*/
        }

        .links {
            margin-top: 15px;
            font-size: 14px;
        }

        .links a {
            color: #9FE2BF; /*seafoam green*/
            text-decoration: none;
            font-weight: bold;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>   
</head>
<body>
    <div class="login-container">
            <h1>Login</h1>
            <form action="" method="post">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="text" name="email" id="email" autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" autocomplete="off" required>
                </div>
                <input type="submit" class="btn" name="submit" value="Login">
            </form>
            <div class="links">
                <p><a href="forgot_password_email.php">Forgot password?</a></p>
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
</body>
</html>