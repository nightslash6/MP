<?php
session_start();
session_regenerate_id(true);

require 'config.php';

date_default_timezone_set("Asia/Singapore");

// Load PHPMailer
require 'PHPMailer/PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer/PHPMailer-master/src/SMTP.php';
require 'PHPMailer/PHPMailer-master/src/Exception.php';
 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$notification = ['email' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $notification['email'] = 'Invalid email format.';
    } else {
        $conn = db_connect();
        // Check if email exists in the database
        $stmt = $conn->prepare("SELECT user_id, name FROM users WHERE email = ?"); 
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($user_id, $name);
            $stmt->fetch();
            
            // Generate a secure token for password reset
            $token = bin2hex(random_bytes(50));
            
            // Store token in database with expiration
            $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
            $updateStmt = $conn->prepare("UPDATE users SET reset_token=?, reset_token_expiration=? WHERE user_id=?");
            $updateStmt->bind_param("ssi", $token, $expiry, $user_id);
            $updateStmt->execute();
            $updateStmt->close();

            // Build the reset link with token
            $resetLink = "http://localhost//empty/MP/reset_password.php?token=$token";
            
            // Send email
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'mporganization66@gmail.com'; // Your Gmail
                $mail->Password = 'jarx gctl nhsb ivra'; // Use App Password, not regular password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                
                // Enable debugging (remove in production)
            

                // Recipients
                $mail->setFrom('mporganization66@gmail.com', 'Support Team');
                $mail->addAddress($email, $name);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = "
                    <h1>Password Reset Request</h1>
                    <p>Hi " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ",</p>
                    <p>You requested a password reset. Click the link below to reset your password:</p>
                    <p><a href='$resetLink'>Reset Password</a></p>
                    <p>Or copy and paste this link: $resetLink</p>
                    <p>This link will expire in 1 hour.</p>
                    <p>If you didn't request this, please ignore this email.</p>";
                    
                $mail->AltBody = "Hi $name, Use the following link to reset your password: $resetLink This link will expire in 1 hour.";

                $mail->send();
                $notification['email'] = 'Email sent successfully! Check your inbox.';
                
            } catch (Exception $e) {
                $notification['email'] = 'Email could not be sent. Error: ' . $mail->ErrorInfo;
                // Log the full error for debugging
                error_log("PHPMailer Error: " . $e->getMessage());
            }
            
        } else {
            $notification['email'] = 'Email not found in our records.';
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
    <link rel="stylesheet" href="mstyles.css">
    <title>Forgot Password</title>
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
            background: linear-gradient(rgb(82, 139, 205),rgb(107, 89, 189));
        }

        .forgotpassword-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 40px;
            background: white;           
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
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

        .links {
            margin-top: 15px;
            font-size: 14px;
        }

        .links a {
            color: rgb(20, 117, 67);
            text-decoration: none;
            font-weight: bold;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .notification {
            color: #e3090c;
            font-size: 14px;
            margin-top: -10px;
            margin-bottom: 10px;
            display: block;
        }

        .notification.success {
            color: #004526; 
        }
    </style>
</head>
<body>
    <div class="forgotpassword-container">
        <h1>Forgot Password?</h1>

        <form action="" method="POST">
            <div class="form-group">
                <label for="email">Enter your email address:</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" 
                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                <?php if(!empty($notification['email'])): ?>
                    <div class="notification <?php echo (strpos($notification['email'], 'successfully') !== false) ? 'success' : ''; ?>">
                        <?php echo htmlspecialchars($notification['email']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <button type="submit" name="submit">Send Reset Link</button>
        </form>

        <div class="links">
            <p><a href="login.php">Back to Login</a></p>
        </div>
    </div>
</body>
</html>