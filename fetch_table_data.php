<?php
require 'authentication.php';

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$assignedTo = isset($_GET['assigned_to']) ? $_GET['assigned_to'] : '';

// Updated SQL to include completion_time
$sql = "SELECT a.*, b.fullname, a.completion_time 
        FROM task_info a
        INNER JOIN tbl_admin b ON a.t_user_id = b.user_id
        WHERE (:date BETWEEN DATE(a.t_start_time) AND DATE(a.t_end_time))";

if (!empty($assignedTo)) {
    $sql .= " AND b.fullname = :assignedTo";
}

$sql .= " ORDER BY a.task_id DESC";

$stmt = $obj_admin->db->prepare($sql);
$stmt->bindValue(':date', $date, PDO::PARAM_STR);

if (!empty($assignedTo)) {
    $stmt->bindValue(':assignedTo', $assignedTo, PDO::PARAM_STR);
}

$stmt->execute();
$info = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($info)) {
    echo '<tr><td colspan="9">No Data Found</td></tr>';
} else {
    $serial = 1;
    foreach ($info as $row) {
        $start_time = $row['t_start_time'];
        $end_time = $row['t_end_time'];
        $completion_time = $row['completion_time'];

        // Initialize values
        $timeTaken = 'N/A';
        $totalTimeTaken = 'N/A';

        // Time Taken = End Time - Start Time
        if (!empty($start_time) && !empty($end_time)) {
            $start = new DateTime($start_time);
            $end = new DateTime($end_time);
            $interval = $start->diff($end);

            $days = $interval->days;
            $hoursMinutesSeconds = $interval->format('%H:%I:%S');
            $timeTaken = "{$days} Day(s), {$hoursMinutesSeconds}";
        }

        // Total Time Taken = End Time + Completion Time
        if (!empty($end_time) && !empty($completion_time)) {
            try {
                $end = new DateTime($end_time);
                list($h, $m, $s) = explode(':', $completion_time);
                $interval = new DateInterval("PT{$h}H{$m}M{$s}S");
                $end->add($interval);
                $totalTimeTaken = $end->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                $totalTimeTaken = 'Invalid Completion Time';
            }
        }

        echo "<tr>
            <td>{$serial}</td>
            <td>" . htmlspecialchars($row['t_title']) . "</td>
            <td>" . htmlspecialchars($row['fullname']) . "</td>
            <td>" . htmlspecialchars($row['t_start_time']) . "</td>
            <td>" . htmlspecialchars($row['t_end_time']) . "</td>
            <td>";

        if ($row['status'] == 1) {
            echo '<small class="label label-warning">In Progress</small>';
        } elseif ($row['status'] == 2) {
            echo '<small class="label label-success">Completed</small>';
        } else {
            echo '<small class="label label-default">Incomplete</small>';
        }

        echo "</td>
            <td>{$timeTaken}</td>
            
            
        </tr>";

        $serial++;
    }
}
?>


