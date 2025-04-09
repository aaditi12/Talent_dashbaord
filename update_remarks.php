<?php
require 'authentication.php'; // Ensure admin authentication

// Auth check
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['security_key'])) {
    http_response_code(403);
    exit('Unauthorized access.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'], $_POST['remarks'])) {
    $task_id = intval($_POST['task_id']);
    $remarks = trim($_POST['remarks']);

    if ($task_id <= 0 || empty($remarks)) {
        http_response_code(400);
        exit('Invalid input.');
    }

    // Check if task exists
    $sql_check = "SELECT task_id FROM task_info WHERE task_id = :task_id";
    $stmt_check = $obj_admin->db->prepare($sql_check);
    $stmt_check->execute(['task_id' => $task_id]);
    if (!$stmt_check->fetch()) {
        http_response_code(404);
        exit('Task not found.');
    }

    // Update remarks
    $sql_update = "UPDATE task_info SET remarks = :remarks WHERE task_id = :task_id";
    $stmt_update = $obj_admin->db->prepare($sql_update);
    $stmt_update->execute(['remarks' => $remarks, 'task_id' => $task_id]);

    echo htmlspecialchars($remarks, ENT_QUOTES, 'UTF-8');
    exit();
}

http_response_code(405);
echo 'Method not allowed.';
exit();
