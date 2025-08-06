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

$isEditing = isset($_GET['edit']) || isset($_POST['save']) || isset($_POST['cancel']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save'])) {
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
    } elseif (isset($_POST['cancel'])) {
        header("Location: user_profile.php");
        exit();
    }
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
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(#528BCD, #6B59BD);
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            padding: 20px;
        }

        .profile-container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        h1 {
            font-size: 28px;
            margin-bottom: 30px;
            color: #333;
            text-align: center;
        }

        .user-info {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }

        .form-control {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .form-control:disabled, .form-control[readonly] {
            background-color: #e9ecef;
            opacity: 1;
        }

        .btn-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .btn-home {
            background-color: #5F9EA0;
            border-color: #5F9EA0;
            color: white;
        }

        .btn-home:hover {
            background-color: #4c8b8d;
            border-color: #4c8b8d;
        }

        .btn-edit {
            background-color: #90EE90;
            border-color: #90EE90;
            color: #212529;
        }

        .btn-edit:hover {
            background-color: #7FDD7F;
            border-color: #7FDD7F;
        }

        .btn-cancel {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .btn-cancel:hover {
            background-color: #e6c2c7;
            border-color: #e6c2c7;
        }

        .alert {
            margin-bottom: 20px;
        }

        @media (max-width: 576px) {
            .profile-container {
                padding: 20px 15px;
                margin: 20px auto;
            }
            
            .btn-container {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-home, .btn-edit, .btn-cancel {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="profile-container">
        <h1>Personal Information</h1>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" id="msg-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($update_errors)): ?>
            <div class="alert alert-danger" id="msg-error">
                <?php foreach ($update_errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" onsubmit="return validateForm()">
            <div class="user-info">
                <div class="mb-3">
                    <label for="name" class="form-label">Username:</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($UserRecord['name']); ?>" <?php echo $isEditing ? '' : 'readonly'; ?>>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($UserRecord['email']); ?>" disabled>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number:</label>
                    <input type="text" class="form-control" id="phone" name="phone" maxlength="8" value="<?php echo htmlspecialchars($UserRecord['phone_number'] ?? ''); ?>" <?php echo $isEditing ? '' : 'readonly'; ?>>
                </div>
            </div>

            <div class="btn-container">
                <?php if ($isEditing): ?>
                    <button type="button" class="btn btn-home" onclick="goHome()">← Back to Home</button>
                    <button type="submit" name="cancel" class="btn btn-cancel">Cancel</button>
                    <button type="submit" name="save" class="btn btn-edit">Save</button>
                <?php else: ?>
                    <button type="button" class="btn btn-home" onclick="goHome()">← Back to Home</button>
                    <button type="button" class="btn btn-edit" onclick="enableEdit()">Edit Profile</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
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