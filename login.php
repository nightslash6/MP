<?php
session_start();
session_regenerate_id(true);

require 'config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [
    'email' => '',
    'general' => ''
];

function login_user($email, $password, &$errors) {
    $conn = db_connect();

    // Sanitize and validate the email
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
        $conn->close();
        return;
    }

    // Fetch user data from the database
    $stmt = $conn->prepare("SELECT user_id, name,  password_hash FROM users WHERE email = ?"); 
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result(); // Store the result to check the number of rows

    // Check if a user was found
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $name, $password_hash);
        $stmt->fetch();

        if (password_verify($password, $password_hash)) { //USE PASSWORD HASH AND PASSWORD VERIFY FOR SECURITY
            $_SESSION['user_id'] = $user_id;
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;

            $stmt->close();
            $conn->close();
            header("Location: main.php");
            exit();
        } else {
            $errors['general'] = 'Invalid email or password';
        }
    } else {
        // No user found with the provided email
       $errors['general'] = 'User is not registered';
    }

    $stmt->close();
    $conn->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors['general'] = 'Invalid CSRF token';
    } else {
    // Retrieve and sanitize the email and password
        $email = htmlspecialchars(trim($_POST['email']));
        $password = trim($_POST['password']); // Password does not need htmlspecialchars 

    // Call the login function
        login_user($email, $password, $errors);
    }
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
            margin-bottom: 15px;
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

         .error {
            color: #e3090c;
            font-size: 14px;
            margin-top: -10px;
            margin-bottom: 10px;
            display: block;
        }
    </style>   
</head>
<body>
    <div class="login-container">
            <h1>Login</h1>

            <?php if(!empty($errors['general'])): ?>
                <div class="error"><?php echo $errors['general']; ?></div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="text" name="email" id="email" placeholder="Enter your email" maxlength="255" autocomplete="off" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                    <?php if(!empty($errors['email'])): ?>
                        <span class="error"><?php echo $errors['email']; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" placeholder="Enter your password" maxlength="255" autocomplete="off" required>
                </div>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="submit" class="btn" name="submit" value="Login">
            </form>

            <div class="links">
                <p><a href="forgot_password_email.php">Forgot password?</a></p>
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
</body>
</html>