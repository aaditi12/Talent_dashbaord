<?php
require 'db_connection.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if a file has been uploaded
    if (isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['error'] == 0) {
        $fileName = $_FILES['fileToUpload']['name'];
        $fileTmpPath = $_FILES['fileToUpload']['tmp_name'];
        
        // Move the uploaded file to a designated folder (e.g., 'uploads/')
        $uploadFileDir = './uploads/';
        $dest_path = $uploadFileDir . $fileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // Insert file information into the database
            $stmt = $conn->prepare("INSERT INTO uploaded_files (file_name, user_id) VALUES (?, ?)");
            $stmt->execute([$fileName, $_SESSION['admin_id']]);
            echo "File is successfully uploaded.";
        } else {
            echo "There was an error moving the uploaded file.";
        }
    } else {
        echo "No file uploaded or there was an upload error.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload File</title>
</head>
<body>
    <h2>Upload File</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="file" name="fileToUpload" required>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
