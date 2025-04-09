<?php
require 'authentication.php'; // Ensure authentication is included

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $task_id = $_POST['task_id'] ?? null;
    $completion_percentage = $_POST['completion_percentage'] ?? null;

    if (!$task_id || $completion_percentage === null) {
        echo json_encode(["success" => false, "message" => "Invalid data received."]);
        exit;
    }

    try {
        // Prepare and execute the update query
        $stmt = $obj_admin->db->prepare("UPDATE task_info SET completion_percentage = :completion WHERE task_id = :task_id");
        $stmt->execute([
            'completion' => $completion_percentage,
            'task_id' => $task_id
        ]);

        echo json_encode(["success" => true]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}
?>

