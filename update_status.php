<?php
require 'authentication.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['aten_id'], $_POST['status'])) {
    $aten_id = $_POST['aten_id'];
    $status = $_POST['status'];

    // Update the status in the database
    $sql = "UPDATE attendance_info SET status = :status WHERE aten_id = :aten_id";
    $stmt = $obj_admin->db->prepare($sql);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':aten_id', $aten_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header("Location: attendance-info.php");
        exit();
    } else {
        die("Error updating status: " . implode(" | ", $stmt->errorInfo()));
    }
}
?>
