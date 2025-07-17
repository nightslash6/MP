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

// Step 1: Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process password reset if token is valid and form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit']) && $valid_token) {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $submitted_token = trim($_POST['token']);
    
    // Verify token hasn't changed
    // ✅ STEP 3: CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $errors['general'] = 'Invalid CSRF token. Please refresh the page and try again.';
    } elseif ($submitted_token !== $token) {
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

                // ✅ STEP 4: Invalidate CSRF token after use
                unset($_SESSION['csrf_token']);
                
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
        }

        body {
            min-height: 100vh;
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(rgb(82, 139, 205), rgb(107, 89, 189));
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .resetpassword-container {
            max-width: 500px;
            margin: 20px;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
        }

        h1 {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: 500;
            color: #555;
            margin-bottom: 8px;
            display: block;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            margin-bottom: 5px;
            transition: border-color 0.3s ease;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background: #5F9EA0; /* cadet blue */
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #40826D; /* viridian */
        }

        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .links {
            margin-top: 20px;
            font-size: 14px;
            text-align: center;
        }

        .links a {
            color: rgb(20, 117, 67); /* seafoam green */
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
            color: #666;
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
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
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