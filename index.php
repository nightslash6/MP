<!DOCTYPE html>
<html>
<head><title>Upload Madness</title></head>
<body>
    <h1>Upload Madness</h1>
    <p>You can upload files, but not all types. Can you upload something malicious?</p>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        Select a file to upload:
        <input type="file" name="file"><br><br>
        <input type="submit" value="Upload">
    </form>
</body>
</html>
