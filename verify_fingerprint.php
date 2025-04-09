<?php
require 'authentication.php'; // Ensure database connection

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data["fingerprint_data"])) {
    echo json_encode(["success" => false, "message" => "No fingerprint data received"]);
    exit;
}

$fingerprint_data = $data["fingerprint_data"];

// Query to find matching fingerprint
$query = "SELECT * FROM admin_users WHERE fingerprint_data = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $fingerprint_data);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Start session & login user
    session_start();
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_name'] = $user['username'];
    $_SESSION['security_key'] = md5(uniqid());

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Fingerprint not recognized"]);
}

?>
