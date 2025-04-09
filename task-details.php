<?php
require 'authentication.php'; // Admin authentication check

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['security_key'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
$user_role = $_SESSION['user_role'];

$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : null;
if (!$task_id) {
    die("Task ID is missing.");
}

// Ensure required columns exist
$columns = ['remarks TEXT', 'extra_time_taken VARCHAR(255)'];
foreach ($columns as $col) {
    preg_match('/^(\w+)/', $col, $col_name);
    $check_sql = "SHOW COLUMNS FROM task_info LIKE '{$col_name[1]}'";
    $stmt = $obj_admin->db->query($check_sql);
    if (!$stmt->fetch()) {
        $obj_admin->db->query("ALTER TABLE task_info ADD COLUMN $col");
    }
}

// Fetch remarks history
$sql_history = "SELECT remarks FROM task_info WHERE task_id = :task_id";
$stmt_history = $obj_admin->db->prepare($sql_history);
$stmt_history->execute(['task_id' => $task_id]);
$remarks_history = $stmt_history->fetch(PDO::FETCH_ASSOC)['remarks'] ?? 'No previous remarks.';

// Handle form update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_progress'])) {
    $completion_percentage = intval($_POST['completion_percentage']);
    $extra_time_taken = trim($_POST['extra_time_taken']);
    $new_remarks = trim($_POST['remarks']);

    // Validate extra time format
    if (!preg_match('/^\d+\s+days\s+\d{1,2}:\d{2}$/', $extra_time_taken)) {
        die("Invalid format for Extra Time Taken. Use format like '2 days 04:30'");
    }

    $sql_update = "UPDATE task_info SET 
        completion_percentage = :completion_percentage, 
        extra_time_taken = :extra_time_taken,
        remarks = :remarks 
        WHERE task_id = :task_id";
    $stmt = $obj_admin->db->prepare($sql_update);
    $stmt->execute([
        'completion_percentage' => $completion_percentage,
        'extra_time_taken' => $extra_time_taken,
        'remarks' => $new_remarks,
        'task_id' => $task_id
    ]);

    header("Location: task-details.php?task_id=$task_id");
    exit();
}

// Fetch task details
$sql = "SELECT a.*, b.fullname FROM task_info a
        LEFT JOIN tbl_admin b ON a.t_user_id = b.user_id
        WHERE task_id = :task_id";
$stmt = $obj_admin->db->prepare($sql);
$stmt->execute(['task_id' => $task_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC) ?? [];

$status = $row['status'] ?? 0;
$completion_percentage = (int)($row['completion_percentage'] ?? 0);
$color = ($status == 1) ? "orange" : (($status == 2) ? "green" : "red");
$page_name = "Edit Task";

include("include/sidebar.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Employee Task Management System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="custom.css">
</head>
<body>
<div class="row" style="color: #F8F8F8;">
    <div class="col-md-12">
        <div class="well well-custom" style="background-color: #F8F8F8;">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="well" style="background-color: white; color: #E65200;">
                        <h3 class="text-center bg-primary" style="padding: 7px; background-color: #E65200; color: white;">Task Details</h3><br>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr><td>Task Title</td><td><?= htmlspecialchars($row['t_title'] ?? 'N/A') ?></td></tr>
                                    <tr><td>Description</td><td><?= htmlspecialchars($row['t_description'] ?? 'N/A') ?></td></tr>
                                    <tr><td>Start Time</td><td><?= $row['t_start_time'] ?? 'N/A' ?></td></tr>
                                    <tr><td>End Time</td><td><?= $row['t_end_time'] ?? 'N/A' ?></td></tr>
                                    <tr><td>Assign To</td><td><?= htmlspecialchars($row['fullname'] ?? 'N/A') ?></td></tr>
                                    <tr>
                                        <td>Status</td>
                                        <td><span style="color: <?= $color ?>;">
                                            <?php
                                            switch ($status) {
                                                case 1: echo "In Progress"; break;
                                                case 2: echo "Completed"; break;
                                                default: echo "Incomplete";
                                            }
                                            ?>
                                        </span></td>
                                    </tr>
                                    <tr>
                                        <td>Completion %</td>
                                        <td>
                                            <form method="post">
                                                <input type="number" name="completion_percentage" min="0" max="100" 
                                                       value="<?= $completion_percentage ?>" 
                                                       style="background-color: #F8F8F8; color: black;" required><br><br>
                                                <label><strong>Extra Time Taken (e.g. 2 days 04:30)</strong></label>
                                                <input type="text" name="extra_time_taken" 
                                                       value="<?= htmlspecialchars($row['extra_time_taken'] ?? '') ?>" 
                                                       placeholder="e.g. 1 days 02:30" 
                                                       style="background-color: #F8F8F8; color: black;" required><br><br>
                                                <input type="hidden" name="remarks" value="<?= htmlspecialchars($row['remarks'] ?? '') ?>">
                                                <button type="submit" name="update_progress" class="btn btn-primary btn-xs" style="background-color: #E65200; color: white;">Update</button>
                                            </form>
                                            <div class="progress" style="margin-top: 10px;">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: <?= $completion_percentage ?>%; background-color: #E65200; color: white;" >
                                                    <?= $completion_percentage ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Extra Time Taken</td>
                                        <td><?= htmlspecialchars($row['extra_time_taken'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <td>Remarks</td>
                                        <td>
                                            <div id="remarks-box"><?= htmlspecialchars($row['remarks'] ?? 'No remarks yet.') ?></div>
                                            <form id="remarks-form">
                                                <textarea name="remarks" id="remarks" rows="3" cols="50" required></textarea>
                                                <input type="hidden" name="task_id" value="<?= $task_id ?>">
                                            </form>
                                            <button type="button" id="history-remarks-btn" class="btn btn-info btn-xs" style="background-color: #E65200; color: white;">Previous remarks</button>
                                            <button type="button" id="update-remarks-btn" class="btn btn-primary btn-xs" style="background-color: #E65200; color: white;">Update</button>
                                            <div id="remarks-history" style="display: none; margin-top: 10px;">
                                                <strong>Previous Remarks:</strong>
                                                <p><?= nl2br(htmlspecialchars($remarks_history)) ?></p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <a href="task-info.php" class="btn btn-success-custom btn-xs" style="background-color: #E65200; color: white;">Go Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $("#update-remarks-btn").click(function(){
        var remarks = $("#remarks").val().trim();
        var task_id = $("input[name='task_id']").val();
        if (!remarks) return alert("Enter remarks before updating.");
        $.post("update_remarks.php", {remarks, task_id}, function(response){
            $("#remarks-box").text(response);
            $("#remarks").val("");
            alert("Remarks updated successfully!");
        }).fail(() => alert("Error updating remarks."));
    });
    $("#history-remarks-btn").click(() => $("#remarks-history").toggle());
});
</script>
</body>
</html>
<?php include("include/footer.php"); ?>

