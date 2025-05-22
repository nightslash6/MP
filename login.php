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

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit();
}

function login_user($email, $password, &$errors) {
    $conn = db_connect();

    // Sanitize and validate the email
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
        $conn->close();
        return false;
    }

    // Fetch user data from the database
    $stmt = $conn->prepare("SELECT user_id, name, password_hash FROM users WHERE email = ?"); 
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // Check if a user was found
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $name, $password_hash);
        $stmt->fetch();

        if (password_verify($password, $password_hash)) {
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user_id;
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['login_time'] = time();

            $stmt->close();
            $conn->close();
            
            // Redirect to intended page or main page
            $redirect_url = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'main.php';
            unset($_SESSION['redirect_url']);
            
            header("Location: " . $redirect_url);
            exit();
        } else {
            $errors['general'] = 'Invalid email or password';
        }
    } else {
        // Don't reveal whether email exists or not for security
        $errors['general'] = 'Invalid email or password';
    }

    $stmt->close();
    $conn->close();
    return false;
}

// Handle form submission
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
    // Basic rate limiting (simple approach)
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt'] = time();
    }

    // Reset attempts if more than 15 minutes have passed
    if (time() - $_SESSION['last_attempt'] > 900) {
        $_SESSION['login_attempts'] = 0;
    }

    // Check if too many attempts
    if ($_SESSION['login_attempts'] >= 5) {
        $errors['general'] = 'Too many login attempts. Please try again in 15 minutes.';
    } else {
        // Retrieve and sanitize the email and password
        $email = htmlspecialchars(trim($_POST['email']));
        $password = trim($_POST['password']);

        if (empty($email) || empty($password)) {
            $errors['general'] = 'Please fill in all fields';
        } else {
            // Attempt login
            $login_success = login_user($email, $password, $errors);
            
            if (!$login_success) {
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt'] = time();
            }
        }
    }
}

// Handle success messages
$success_message = '';
if (isset($_GET['reset']) && $_GET['reset'] === 'success') {
    $success_message = 'Password reset successfully! You can now log in with your new password.';
}
if (isset($_GET['registered']) && $_GET['registered'] === 'success') {
    $success_message = 'Registration successful! You can now log in.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Your App Name</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Cambria', serif;
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
            background: #088f8f;
            padding: 70px;
            border-radius: 15px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 450px;
            position: relative;
        }

        h1 {
            font-size: 26px;
            font-weight: 600;
            color: #000;
            margin-bottom: 20px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 15px;
        }

        label {
            font-size: 14px;
            font-weight: 600;
            color: #000;
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            margin-bottom: 15px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #5F9EA0;
            box-shadow: 0 0 5px rgba(95, 158, 160, 0.3);
        }

        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background: #5F9EA0;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: #40826D;
        }

        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .links {
            margin-top: 20px;
            font-size: 14px;
        }

        .links a {
            color: #9FE2BF;
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
            text-align: left;
        }

        .general-error {
            color: #e3090c;
            font-size: 14px;
            margin-bottom: 15px;
            padding: 10px;
            background-color: rgba(227, 9, 12, 0.1);
            border-radius: 5px;
            text-align: center;
        }

        .success-message {
            color: #28a745;
            font-size: 14px;
            margin-bottom: 15px;
            padding: 10px;
            background-color: rgba(40, 167, 69, 0.1);
            border-radius: 5px;
            text-align: center;
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            color: #666;
        }

        .forgot-link {
            text-align: right;
            margin-top: -10px;
            margin-bottom: 15px;
        }

        .forgot-link a {
            font-size: 12px;
            color: #9FE2BF;
            text-decoration: none;
        }

        .forgot-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            body {
                padding-left: 20px;
                padding-right: 20px;
                justify-content: center;
            }
            
            .login-container {
                width: 100%;
                max-width: 400px;
                padding: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Welcome Back</h1>

        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="general-error"><?php echo htmlspecialchars($errors['general']); ?></div>
        <?php endif; ?>

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
        <form action="" method="post">
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" name="email" id="email" placeholder="Enter your email" 
                       maxlength="255" autocomplete="email" 
                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                <?php if (!empty($errors['email'])): ?>
                    <span class="error"><?php echo htmlspecialchars($errors['email']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <div class="password-toggle">
                    <input type="password" name="password" id="password" 
                           placeholder="Enter your password" maxlength="255" 
                           autocomplete="current-password" required>
                    <button type="button" onclick="togglePassword()" tabindex="-1">👁️</button>
                </div>
                <div class="forgot-link">
                    <a href="forgot_password_email.php">Forgot password?</a>
                </div>
            </div>

            <input type="submit" class="btn" name="submit" value="Sign In" 
                   <?php echo (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) ? 'disabled' : ''; ?>>
        </form>

        <div class="links">
            <p>Don't have an account? <a href="register.php">Create one here</a></p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleButton = passwordField.nextElementSibling;
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleButton.textContent = '🙈';
            } else {
                passwordField.type = 'password';
                toggleButton.textContent = '👁️';
            }
        }

        // Auto-focus email field if empty
        document.addEventListener('DOMContentLoaded', function() {
            const emailField = document.getElementById('email');
            if (emailField.value === '') {
                emailField.focus();
            }
        });
    </script>
</body>
</html>