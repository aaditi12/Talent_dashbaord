<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "etms_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get employee ID from session (Assume session is active)
session_start();
$employee_id = $_SESSION['employee_id'] ?? null;
$ip_address = $_SERVER['REMOTE_ADDR'];  // Get client IP

if (!$employee_id) {
    die("Error: Employee not logged in.");
}

// Validate IP Address
$sql = "SELECT allowed_ips FROM employees WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row) {
    $allowed_ips = explode(',', $row['allowed_ips']);
    if (!in_array($ip_address, $allowed_ips)) {
        die("Unauthorized access detected!");
    }
}

// Log Activity
$action = $_POST['action'] ?? 'LOGIN';  // Default action is LOGIN
$sql = "INSERT INTO activity_logs (employee_id, ip_address, action) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $employee_id, $ip_address, $action);
$stmt->execute();

echo "Activity logged successfully!";
$conn->close();
?>
