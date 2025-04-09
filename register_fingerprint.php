<?php
require 'authentication.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $fingerprint_data = $_POST['fingerprint_data']; // Captured from scanner

    $query = "UPDATE admin_users SET fingerprint_data=? WHERE username=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $fingerprint_data, $username);

    if ($stmt->execute()) {
        echo "Fingerprint registered successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
