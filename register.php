<?php
session_start();
session_regenerate_id(true);

require 'config.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $role = htmlspecialchars(trim($_POST['role'])); //remove

    if(empty($name) || empty($password) || empty($role)){ //remove role
        echo "<script>alert('All fields are required'); window.location.href = 'register.php';</script>";
    }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo "<script>alert('Invalid email format.');</script>"; //need window.location.href? (test)
    }else{
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $conn = db_connect();
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"); //remove role
        $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);

        if($stmt->execute()){
            echo "<script>alert('Registration successful!'); window.location.href = 'login.php';</script>";
        }else{
            echo "<script>alert('Error: " . $stmt->error . "';</script>";
        }

        $stmt->close();
        $conn->close();
    }
}    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Cambria';
        }

        body{
            background: url('login_background.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            height: 100vh;
            backdrop-filter: blur(5px);
            padding-left: 190px;
        }

        .register-container{
            background: #088f8f; /*blue green*/
            padding: 70px;
            border-radius: 15px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 450px;
        }

        h1{
            font-size: 26px;
            font-weight: 600;
            color: #000;
            margin-bottom: 20px;
            max-width: 400px;
        }

        .form-group{
            text-align: left;
            margin-bottom: 15px;
        }

        label{
            font-size: 14px;
            font-weight: 600;
            color: #000;
        }

        input, select{
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            margin-top: 5px;
            margin-bottom: 15px;
        }

        button{
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

        button:hover{
            background-color: #40826D; /*viridian*/
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
    <div class="register-container">
        <h1>Create an Account</h1>
        <form action="" method="POST">
            <div class="form-group">
                <label for="name">Username:</label>
                <input type="text" id="name" name="name" placeholder="Enter your username" maxlength="100" required>
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" placeholder="Enter your email" maxlength="200" required>
                <label for="password">Password:</label>
                <input type="text" id="password" name="password" placeholder="Enter your password" maxlength="255" required>
                <button type="submit" name="action" value="create">Register</button>
            </div>
        </form>
        <div class="links">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>