<?php
session_start();

// Destroy the session completely
session_unset();   // Unset all session variables
session_destroy(); // Destroy the session

// Redirect to login page
header("Location: main.php");
exit();
?>
