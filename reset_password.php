<?php
session_start();
session_regenerate_id(true);

require 'config.php';

$errors = ['general' => '', 'password' => ''];
$token = '';
$user_id = null;
$valid_token = false;

// Check if token is provided and validate it
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = trim($_GET['token']);
    
    $conn = db_connect();
    
    // Validate token
    $stmt = $conn->prepare("SELECT user_id, name FROM users WHERE reset_token = ? AND reset_token_expiration > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $user_id = $user['user_id'];
        $valid_token = true;
    } else {
        // Check if token exists but is expired
        $expiredCheck = $conn->prepare("SELECT user_id FROM users WHERE reset_token = ?");
        $expiredCheck->bind_param("s", $token);
        $expiredCheck->execute();
        $expiredResult = $expiredCheck->get_result();
        
        if ($expiredResult->num_rows === 1) {
            $errors['general'] = 'Reset token has expired. Please request a new password reset.';
        } else {
            $errors['general'] = 'Invalid reset token. Please request a new password reset.';
        }
        $expiredCheck->close();
    }
    
    $stmt->close();
    $conn->close();
} else {
    $errors['general'] = 'No reset token provided. Please use the link from your email.';
}

// Process password reset if token is valid and form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit']) && $valid_token) {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $submitted_token = trim($_POST['token']);
    
    // Verify token hasn't changed
    if ($submitted_token !== $token) {
        $errors['general'] = 'Security token mismatch. Please try again.';
    } elseif (empty($password)) {
        $errors['password'] = 'Password cannot be empty.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm_password) {
        $errors['password'] = 'Passwords do not match.';
    } else {
        $conn = db_connect();
        
        // Double-check token is still valid before updating
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE reset_token = ? AND reset_token_expiration > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // Update the password and clear the reset token
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $updateStmt = $conn->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expiration = NULL WHERE user_id = ?");
            $updateStmt->bind_param("si", $hashed_password, $user_id);
            $updateStmt->execute();

            if ($updateStmt->affected_rows === 1) {
                // Password updated successfully
                $updateStmt->close();
                $stmt->close();
                $conn->close();
                
                // Redirect with success message
                header("Location: login.php?reset=success");
                exit();
            } else {
                $errors['general'] = 'Failed to update password. Please try again.';
            }
            $updateStmt->close();
        } else {
            $errors['general'] = 'Reset token has expired. Please request a new password reset.';
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
    <title>Reset Password</title>
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

        .resetpassword-container {
            background: #088f8f;
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 450px;
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

        button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: #5F9EA0;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #40826D;
        }

        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .links {
            margin-top: 15px;
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
        }

        .general-error {
            color: #e3090c;
            font-size: 16px;
            margin-bottom: 20px;
            padding: 10px;
            background-color: rgba(227, 9, 12, 0.1);
            border-radius: 5px;
            text-align: center;
        }

        .password-requirements {
            font-size: 12px;
            color: #333;
            margin-top: -10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="resetpassword-container">
        <h1>Reset Password</h1>

        <?php if (!empty($errors['general'])): ?>
            <div class="general-error"><?php echo htmlspecialchars($errors['general']); ?></div>
            <div class="links">
                <p><a href="forgot_password.php">Request New Reset Link</a></p>
                <p><a href="login.php">Back to Login</a></p>
            </div>
        <?php elseif ($valid_token): ?>
            <form action="" method="post">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="form-group">
                    <label for="password">New Password:</label>
                    <input type="password" name="password" id="password" required>
                    <div class="password-requirements">
                        Password must be at least 8 characters long
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                    <?php if (!empty($errors['password'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['password']); ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" name="submit">Reset Password</button>
            </form>

            <div class="links">
                <p><a href="login.php">Back to Login</a></p>
            </div>
        <?php else: ?>
            <div class="general-error">Access denied. Please use a valid reset link.</div>
            <div class="links">
                <p><a href="forgot_password.php">Request Password Reset</a></p>
                <p><a href="login.php">Back to Login</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>