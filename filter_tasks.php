<?php
require 'authentication.php'; // Ensure authentication is included

$start_date = $_POST['start_date'] ?? null;
$end_date = $_POST['end_date'] ?? null;

$sql = "SELECT a.*, b.fullname FROM task_info a 
        INNER JOIN tbl_admin b ON a.t_user_id = b.user_id WHERE 1 ";

if ($start_date && $end_date) {
    $sql .= " AND a.t_start_time >= :start_date AND a.t_end_time <= :end_date";
}

$sql .= " ORDER BY a.task_id DESC";
$stmt = $obj_admin->db->prepare($sql);

if ($start_date && $end_date) {
    $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
} else {
    $stmt->execute();
}

$serial = 1;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>
            <td>{$serial}</td>
            <td>{$row['t_title']}</td>
            <td>{$row['fullname']}</td>
            <td>{$row['t_start_time']}</td>
            <td>{$row['t_end_time']}</td>
            <td>";
    
    if ($row['status'] == 1) {
        echo '<small class="label label-warning">In Progress</small>';
    } elseif ($row['status'] == 2) {
        echo '<small class="label label-success">Completed</small>';
    } else {
        echo '<small class="label label-danger">Incomplete</small>';
    }

    echo "</td>
          <td>
            <a href='edit-task.php?task_id={$row['task_id']}'><i class='fas fa-edit'></i></a> 
            <a href='task-details.php?task_id={$row['task_id']}'><i class='fas fa-eye'></i></a>
            <a href='task-info.php?delete_task=1&task_id={$row['task_id']}' onclick='return confirm(\"Are you sure you want to delete this task?\");'>
                <i class='fas fa-trash-alt text-danger'></i>
            </a>
          </td>
        </tr>";
    $serial++;
}
?>
