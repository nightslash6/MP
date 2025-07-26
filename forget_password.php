<?php
// Session started in connect.php with secure cookies
require 'connect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    $conn = db_connect();
    //Checks if a password exists.  If they are doing this it is their first time logging in
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE contact_information = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        // Check if the user has a password set (initial setup case)
        $is_initial_setup = $user['first_login'] == 1;

        $token = bin2hex(random_bytes(32));
        $_SESSION['password_reset_token'] = $token;
        $_SESSION['password_reset_user'] = $user['id'];
        $_SESSION['initial_setup'] = $is_initial_setup;

        // Build Reset Link
        $reset_link = "http://localhost/empty/swap_project/reset_password.php?token=$token";
            
        // Email token to user using PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'crud5grp5@gmail.com';
            $mail->Password = 'sech ormk cpwz lnea';  
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
        
            // Sender and recipient
            $mail->setFrom('crud5grp5@gmail.com', 'AMC Cooperation');
            $mail->addAddress($email);
        
            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'AMC Cooperation - Password Reset Request';
        
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;'>
                    <h2 style='text-align: center; color: #2C3E50;'>Welcome to AMC Cooperation</h2>
                    <p style='color: #34495E;'>Dear User,</p>
                    <p style='color: #34495E;'>We received a request to " . ($is_initial_setup ? "set up" : "reset") . " your password. Please use the link below to proceed:</p>
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='$reset_link' style='display: inline-block; padding: 12px 25px; background-color: #2E86C1; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 5px;'>Reset Password</a>
                    </div>
                    <p style='color: #34495E;'>If you did not request for this, you can safely ignore this email.</p>
                    <p style='color: #34495E;'>Thank you,<br>AMC Cooperation Team</p>
                    <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
                    <p style='text-align: center; font-size: 0.9em; color: #7f8c8d;'>AMC Cooperation &copy; " . date('Y') . " | All Rights Reserved</p>
                </div>
            ";
        
    
            $mail->AltBody = "Dear User,\n\nWe received a request to " . ($is_initial_setup ? "set up" : "reset") . " your password. Please use the link below to proceed:\n$reset_link\n\nIf you did not request this, you can safely ignore this email.\n\nAMC Cooperation";
        
            // Send email
            $mail->send();
            $_SESSION['success_message'] = "A password reset link has been sent to your email address.";
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
        
    } else {
        $_SESSION['error_message'] = "No account found with that email.";
    }

    header("Location: forget_password.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="mstyles.css">
    <title>Forget Password</title>
    <style>
        body {
            font-family: 'Segoe UI';
            background-color: #1A1A2E;
            color: #F9F9F9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            position: relative;
            background-color: #16213E;
            padding: 30px 20px;
            border-radius: 15px;
            width: 100%;
            max-width: 500px;
            height: auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;
        }

        h1 {
            color: #F9F9F9;
            font-size: 2.2rem;
            margin-bottom: 20px;
        }

        h1 .highlight {
            color: #E94560;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        label {
            font-weight: bold;
            font-size: 1rem;
        }

        input, select {
            padding: 10px;
            border: 1px solid #444;
            border-radius: 6px;
            background-color: #1A1A2E;
            color: #F9F9F9;
            font-size: 1rem;
        }

        .btn {
            display: block;
            padding: 12px 20px;
            margin: 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            color: #fff;
            background-color: #2ECC71;
            width: 250px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .btn-red {
            background-color: #E74C3C;
            margin-top: 30px;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .error-message {
            color: #E74C3C;
            margin-bottom: 10px;
        }

        .success-message {
            color: #2ECC71;
            margin-bottom: 10px;
        }

        button[type="submit"] {
            display: block;
            padding: 12px 20px;
            margin: 10px auto;
            border-radius: 8px;
            border: none;
            background-color: #3498db;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 250px;
            text-align: center;
        }

        button[type="submit"]:hover {
            background-color: #2980b9;
        }
        .arrow {
            content:"\2190";
            position: absolute;
            font-size: 40px;
            margin-bottom: 275px;
            margin-right: 435px;
            color: #F9F9F9;
        }
        .arrow:hover{
            color: #404040; 
            transform: translateY(-0px);
        }
    </style>
<head>
<body>
    <div class="container">
        <a class='arrow' href="login.php">
        <span class='arrow'>&#8592;</span>
        </a>
        <h1>Forget <span class='highlight'>Password</span></h1>
        
        <?php
        if (isset($_SESSION['error_message'])) {
            echo '<div class="error-message">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }

        if (isset($_SESSION['success_message'])) {
            echo '<div class="success-message">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']);
        }
        ?>
        <form method="POST">
            <label for="email">Enter your email:</label>
            <input type="email" name="email" id="email" required>
            <button type="submit">Send Reset Link</button>
        </form>
    </div>
</body>
</html>