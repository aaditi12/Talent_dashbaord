<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "etms_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch activity logs
$sql = "SELECT e.name, a.ip_address, a.action, a.timestamp FROM activity_logs a 
        JOIN employees e ON a.employee_id = e.id ORDER BY a.timestamp DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Employee Activity Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</head>
<body>

<h2>Employee Activity Logs</h2>
<table id="activityTable">
    <thead>
        <tr>
            <th>Employee</th>
            <th>IP Address</th>
            <th>Action</th>
            <th>Timestamp</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['name'] ?></td>
                <td><?= $row['ip_address'] ?></td>
                <td><?= $row['action'] ?></td>
                <td><?= $row['timestamp'] ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Chart -->
<canvas id="activityChart"></canvas>

<script>
$(document).ready( function () {
    $('#activityTable').DataTable();

    // Fetch chart data
    $.ajax({
        url: 'fetch_chart_data.php',
        method: 'GET',
        success: function(data) {
            let ctx = document.getElementById('activityChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Activity Count',
                        data: data.counts,
                        backgroundColor: 'blue'
                    }]
                }
            });
        }
    });
});
</script>

</body>
</html>
