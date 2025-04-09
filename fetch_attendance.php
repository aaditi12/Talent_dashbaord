<?php
require 'authentication.php';

if (!isset($_GET['date'])) {
    echo json_encode([]);
    exit;
}

$date = $_GET['date'];
$sql = "SELECT a.*, b.fullname FROM attendance_info a 
        LEFT JOIN tbl_admin b ON a.atn_user_id = b.user_id 
        WHERE '{$date}' BETWEEN DATE(a.in_time) AND DATE(a.out_time) 
        ORDER BY a.aten_id DESC";

$info = $obj_admin->manage_all_info($sql);
$result = [];

while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
    $result[] = [
        'fullname' => $row['fullname'],
        'in_time' => $row['in_time'],
        'out_time' => $row['out_time'],
        'total_duration' => $row['total_duration'] ?: 'N/A',
        'duration' => $row['total_duration'] ? (int)substr($row['total_duration'], 0, 2) : 0
    ];
}

echo json_encode($result);
?>
