<?php
include 'db_connection.php';

$assignedTo = $_GET['assignedTo'] ?? '';

$query = "SELECT * FROM tasks";
if (!empty($assignedTo)) {
    $query .= " WHERE assigned_to = ?";
}

$stmt = $conn->prepare($query);
if (!empty($assignedTo)) {
    $stmt->bind_param("s", $assignedTo);
}
$stmt->execute();
$result = $stmt->get_result();

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

echo json_encode($tasks);
?>
