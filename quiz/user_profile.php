<?php
session_start();
session_regenerate_id(true);

require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
$update_errors = [];

function getUserRecord($user_id) {
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT name, email, phone_number FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $record;
}

$isEditing = isset($_GET['edit']) || (isset($_POST['save']));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $conn = db_connect();
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $update_errors[] = 'Invalid email format.';
    }

    if (!preg_match('/^\d{8}$/', $phone)) {
        $update_errors[] = 'Phone number must be exactly 8 digits and contain only numbers.';
    }

    if (empty($update_errors)) {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone_number=? WHERE user_id=?");
        $stmt->bind_param("sssi", $name, $email, $phone, $user_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Profile updated successfully.';
            header("Location: user_profile.php");
            exit();
        } else {
            $update_errors[] = 'Failed to update profile.';
        }
        $stmt->close();
    }

    $conn->close();
}

$UserRecord = getUserRecord($user_id);

if (!$UserRecord) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Cybersite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: linear-gradient(#528BCD, #6B59BD);
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            margin: 0;
        }

        .profile-container {
            max-width: 500px;
            width: 100%;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 30px;
            color: #333;
        }

        .user-info {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[readonly] {
            background-color: #eee;
            cursor: not-allowed;
        }

        .button-container {
            margin-top: 20px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .home-button, .edit-button {
            padding: 12px 20px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .home-button {
            background: #5F9EA0;
            color: white;
        }

        .home-button:hover {
            background: #4c8b8d;
        }

        .edit-button {
            background: #90EE90;
            color: #333;
        }

        .edit-button:hover {
            background: #7FDD7F;
        }

        .success-message, .general-error {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            font-size: 14px;
            text-align: center;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
        }

        .general-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        @media (max-width: 480px) {
            .profile-container {
                padding: 30px 20px;
            }

            .button-container {
                flex-direction: column;
                align-items: center;
            }

            .home-button, .edit-button {
                width: 100%;
                max-width: 200px;
            }
        }
    </style>
</head>
<body>
<div class="profile-container">
    <h1>Personal Information</h1>

    <?php if (!empty($success_message)): ?>
        <div class="success-message" id="msg-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <?php if (!empty($update_errors)): ?>
        <div class="general-error" id="msg-error">
            <?php foreach ($update_errors as $error): ?>
                <div><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" onsubmit="return validateForm()">
        <div class="user-info">
            <label for="name">Username:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($UserRecord['name']); ?>" <?php echo $isEditing ? '' : 'readonly'; ?>>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($UserRecord['email']); ?>" <?php echo $isEditing ? '' : 'readonly'; ?>>

            <label for="phone">Phone Number:</label>
            <input type="text" id="phone" name="phone" maxlength="8" value="<?php echo htmlspecialchars($UserRecord['phone_number'] ?? ''); ?>" <?php echo $isEditing ? '' : 'readonly'; ?>>
        </div>

        <div class="button-container">
            <button type="button" class="home-button" onclick="goHome()">← Back to Home</button>
            <?php if ($isEditing): ?>
                <button type="submit" name="save" class="edit-button">Save</button>
            <?php else: ?>
                <button type="button" class="edit-button" onclick="enableEdit()">Edit Profile</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
    function enableEdit() {
        window.location.href = "user_profile.php?edit=1";
    }

    function goHome() {
        window.location.href = 'main.php';
    }

    setTimeout(() => {
        const msg = document.getElementById('msg-success') || document.getElementById('msg-error');
        if (msg) msg.style.display = 'none';
    }, 3000);

    function validateForm() {
        const phoneInput = document.getElementById("phone").value;
        const phonePattern = /^\d{8}$/;

        if (!phonePattern.test(phoneInput)) {
            alert("Phone number must be exactly 8 digits and contain only numbers.");
            return false;
        }

        return true;
    }
</script>
</body>
</html>