<?php
session_start();
session_regenerate_id(true);

require 'config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'password' => '',
    'general' => ''
];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $errors['general'] = 'Invalid CSRF token. Please try again.';
} else {
    // Proceed with registration logic
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $password = htmlspecialchars(trim($_POST['password']));

    $conn = db_connect();
    $stmt = $conn->prepare("INSERT INTO users (name, email, phone_number, password_hash) VALUES (?, ?, ?, ?)"); 
    $stmt->bind_param("ssss", $name, $email, $phone, $hashedpassword,);

     //Check for duplicate name
    $CheckDupName=$conn->prepare("SELECT user_id FROM users WHERE name= ?");
    $CheckDupName->bind_param("s", $name);
    $CheckDupName->execute();
    $CheckDupName->store_result();

    //Check for duplicate email
    $CheckDupEmail=$conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $CheckDupEmail->bind_param("s", $email);
    $CheckDupEmail->execute();
    $CheckDupEmail->store_result();

    //Check for duplicate phone number
    $CheckDupPhone=$conn->prepare("SELECT user_id FROM users WHERE phone_number = ?");
    $CheckDupPhone->bind_param("s", $phone);
    $CheckDupPhone->execute();
    $CheckDupPhone->store_result();

    if (empty($name)) $errors['name'] = 'Username is required';
    if (empty($email)) $errors['email'] = 'Email is required';
    if (empty($phone)) $errors['phone'] = 'Phone Number is required';
    if (empty($password)) $errors['password'] = 'Password is required';

    if($CheckDupName->num_rows > 0){
       $errors['name'] = 'Username already taken';
    }
   if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors['email'] = 'Invalid email format';
    } 
    if($CheckDupEmail->num_rows > 0){
        $errors['email'] = 'Email already registered';
    }
    if($CheckDupPhone->num_rows > 0){
       $errors['phone'] = 'Phone number already registered';
    }
    if(!empty($phone) && !is_numeric($phone)){
        $errors['phone'] = 'Phone number should be digits only';
    }

    if(empty(array_filter($errors))){
        $hashedpassword = password_hash($password, PASSWORD_BCRYPT);

        if($stmt->execute()){
            unset($_SESSION['csrf_token']); // or regenerate it
            header("Location: login.php?registration=success");
            exit();
        }else{
            $errors['general'] = 'Error: ' . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }
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

        input{
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
    <div class="register-container">
        <h1>Create an Account</h1>

         <?php if(!empty($errors['general'])): ?>
            <div class="error"><?php echo $errors['general']; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="name">Username:</label>
                <input type="text" id="name" name="name" placeholder="Enter your username" maxlength="255" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                <?php if(!empty($errors['name'])): ?>
                    <span class="error"><?php echo $errors['name']; ?></span>
                <?php endif; ?>

                <label for="email">Email:</label>
                                <input type="text" id="email" name="email" placeholder="Enter your email" maxlength="255" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                <?php if(!empty($errors['email'])): ?>
                    <span class="error"><?php echo $errors['email']; ?></span>
                <?php endif; ?>

                <label for="phone">Phone Number:</label>
                <input type="text" id="phone" name="phone" placeholder="Enter your phone number" maxlength="8" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
                <?php if(!empty($errors['phone'])): ?>
                    <span class="error"><?php echo $errors['phone']; ?></span>
                <?php endif; ?>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" maxlength="255" required>
                <?php if(!empty($errors['password'])): ?>
                    <span class="error"><?php echo $errors['password']; ?></span>
                <?php endif; ?>

                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <button type="submit" name="action" value="create">Register</button>
            </div>
        </form>

        <div class="links">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>