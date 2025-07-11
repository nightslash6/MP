<?php
session_start();

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$show_form = true;

// Form submitted?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_email = $_POST['email'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    echo "<h3>✅ Email changed to: <code>" . htmlspecialchars($submitted_email) . "</code></h3>";

    // Flag condition: email is hacked@example.com AND CSRF token is MISSING or INVALID
    if ($submitted_email === 'hacked@example.com') {
        if (empty($csrf_token) || $csrf_token !== $_SESSION['csrf_token']) {
            echo "<p style='color:green;'>🎉 Flag: <strong>cybersite{csrf_token_not_verified}</strong></p>";
        } else {
            echo "<p style='color:orange;'>🔒 But you don't get the flag when CSRF is verified.</p>";
        }
    } else {
        echo "<p style='color:gray;'>🔒 No flag for this email.</p>";
    }

    $show_form = false;
}
?>

<?php if ($show_form): ?>
    <h2>Change Your Email Address</h2>

    <!-- 🕵️ Hint hidden in source: -->
    <!-- You forgot something? Check your token... -->

    <form method="POST">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>
        
        <!-- CSRF Token (Legit form only) -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <button type="submit">Update Email</button>
    </form>
<?php endif; ?>

