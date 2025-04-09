<?php
require 'db_connection.php'; // Include database connection

// Fetch recently uploaded files
$stmt = $conn->prepare("SELECT * FROM uploaded_files ORDER BY upload_date DESC");
$stmt->execute();
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recently Uploaded Files</title>
</head>
<body>
    <h2>Recently Uploaded Files</h2>
    <table border="1">
        <tr>
            <th>File Name</th>
            <th>Upload Date</th>
        </tr>
        <?php foreach ($files as $file): ?>
        <tr>
            <td><?php echo htmlspecialchars($file['file_name']); ?></td>
            <td><?php echo htmlspecialchars($file['upload_date']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
