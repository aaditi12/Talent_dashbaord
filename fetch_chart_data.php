<?php
// fetch_chart_data.php
// Assuming you have a database connection here

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$assigned_to = isset($_GET['assigned_to']) ? $_GET['assigned_to'] : '';

// Query the database and fetch task-related data, for example:
$sql = "SELECT task_title, status, COUNT(*) as task_count 
        FROM tasks 
        WHERE date = '$date'";

if ($assigned_to) {
    $sql .= " AND assigned_to = '$assigned_to'";
}

$sql .= " GROUP BY status"; // Group by task status (or any other criteria)

$result = mysqli_query($conn, $sql);

// Prepare data for charts
$labels = [];
$values = [];

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['task_title'];  // You can customize this to be based on the task status
    $values[] = $row['task_count'];
}

echo json_encode(['labels' => $labels, 'values' => $values]);
?>

