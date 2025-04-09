<?php
require 'authentication.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $task_id = $_POST['task_id'];
    $days = $_POST['days'];
    $status = $_POST['status'];

    try {
        $sql = "UPDATE task_info SET days_taken = :days, status = :status WHERE task_id = :task_id";
        $stmt = $obj_admin->db->prepare($sql);
        
        // Bind values properly
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->bindValue(':status', $status, PDO::PARAM_INT);
        $stmt->bindValue(':task_id', $task_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            echo "Task updated successfully!";
        } else {
            echo "Failed to update task.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
