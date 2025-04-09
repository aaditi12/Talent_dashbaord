<?php
include 'db_connection.php';

$query = "SELECT DISTINCT assigned_to FROM tasks ORDER BY assigned_to ASC";
$result = $conn->query($query);

$names = [];
while ($row = $result->fetch_assoc()) {
    $names[] = $row['assigned_to'];
}

echo json_encode($names);
?>
