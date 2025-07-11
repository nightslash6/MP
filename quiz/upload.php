<?php
$uploadDir = "C:/xampp/htdocs/GitHub/MP/";
$uploadFile = $uploadDir . basename($_FILES["file"]["name"]);
$fileType = pathinfo($uploadFile, PATHINFO_EXTENSION);

// Restrict disallowed types
$disallowed = ['php', 'phtml', 'php3', 'php4'];
if (in_array(strtolower($fileType), $disallowed)) {
    echo "This file type is not allowed.";
    exit;
}

// Move the uploaded file
if (move_uploaded_file($_FILES["file"]["tmp_name"], $uploadFile)) {
    echo "File uploaded successfully: <a href='$uploadFile'>$uploadFile</a>";
} else {
    echo "File upload failed.";
}
?>
