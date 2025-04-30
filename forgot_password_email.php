<?php
/* figure out CSRF token
session_start();
require 'config.php'; (need to create)
require 'csrf.php'; (need to create)*/

// Load PHPMailer
require 'PHPMailer/PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer/PHPMailer-master/src/SMTP.php';
require 'PHPMailer/PHPMailer-master/src/Exception.php';
 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

 /*
// Generate CSRF token
$csrfToken = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
*/

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $email = trim($_POST['email']); //sanitize user input

    $conn = db_connect();
    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?"); //select name? any others?
    $stmt->bind_param("s", $email);
    $stmt->execute();
     $result = $stmt->store_result();

    if ($result->num_rows === 1) {
        /*$token = bin2hex(random_bytes(50)); // Generate a secure token
        $stmt->bind_result($user_id);
        $stmt->fetch();*/
        $user = $result->fetch_assoc();
        
        /* // Store token in database
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
        $stmt = $conn->prepare("UPDATE users SET reset_token=?, reset_token_expiration=? WHERE user_id=?");
        $stmt->bind_param("ssi", $token, $expiry, $user_id);
        $stmt->execute();*/

        // Build the reset link
        $resetLink = "http://localhost/MajorProject/reset_password.php" /*?token=" . urlencode($resetToken)*/;
        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'chiashuxuan76@gmail.com'; // My Gmail
            $mail->Password = 'juod eqrr mpup lzqk'; // Gmail app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('chiashuxuan76@gmail.com', 'Reset password link');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "
                        <h1>Password Reset Request</h1>
                        <p>Hi " . htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') . ",</p>
                        <p>Click the link below to reset your password:</p>
                        <p><a href='$resetLink'>$resetLink</a></p>
                        <p>This link will expire in 1 hour.</p>";
                    $mail->AltBody = "Use the following link to reset your password: $resetLink";

                    $mail->send();
                    echo "<script>alert('Email sent successfully!')</script>";
                    exit();
                } catch (Exception $e) {
                    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
                $stmt->close();
                $conn->close();
    } else {
        echo "<script>alert('Email not found!'); window.location.href = 'forgot_password_email.php';</script>";
    }
} 

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['submit'])){ //if input is blank
    echo "<script>alert('Enter email')</script>"; //doesn't work
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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

        .forgotpassword-container {
            background:  #088f8f; /*blue green*/
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
    <div class="forgotpassword-container">
        <h1>Forgot Password?</h1>
        <form action="" method="POST">
            <div class="form-group">
                <label for="email">Enter your email address:</label>
                <input type="text" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <button type="submit">Send reset link</button>
        </form>
        <div class="links">
            <p><a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>
