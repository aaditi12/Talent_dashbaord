<?php     
require 'authentication.php'; // Authentication check

date_default_timezone_set('Asia/Kolkata'); // Set default timezone to IST

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Auth check
$user_id = $_SESSION['admin_id'] ?? null;
$user_name = $_SESSION['name'] ?? null;
$security_key = $_SESSION['security_key'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;

if (!$user_id || !$security_key) {
    header('Location: index.php');
    exit();
}

// Handle Add/Edit Remarks
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_remarks'])) {
    $aten_id = $_POST['aten_id'];
    $remarks = trim($_POST['remarks']);

    $sql_remarks = "UPDATE attendance_info SET remarks = :remarks WHERE aten_id = :aten_id";
    $stmt_remarks = $obj_admin->db->prepare($sql_remarks);
    $stmt_remarks->bindParam(':remarks', $remarks, PDO::PARAM_STR);
    $stmt_remarks->bindParam(':aten_id', $aten_id, PDO::PARAM_INT);

    if ($stmt_remarks->execute()) {
        header("Location: attendance-info.php");
        exit();
    } else {
        die("Error updating remarks: " . implode(" | ", $stmt_remarks->errorInfo()));
    }
}

// Ensure database connection
if (!$obj_admin->db) {
    die("Database connection failed");
}

// Handle Clock Out
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['clock_out'])) {
    $out_time = date("Y-m-d H:i:s");
    $current_date = date("Y-m-d");

    $sql_check_out = "SELECT aten_id, in_time, out_time FROM attendance_info 
                      WHERE atn_user_id = :user_id AND DATE(in_time) = :current_date 
                      ORDER BY aten_id DESC LIMIT 1";
    $stmt_check_out = $obj_admin->db->prepare($sql_check_out);
    $stmt_check_out->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_check_out->bindParam(':current_date', $current_date, PDO::PARAM_STR);
    $stmt_check_out->execute();
    $row = $stmt_check_out->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        if (!empty($row['out_time'])) {
            echo "<script>alert('You have already clocked out today!'); window.location.href='attendance-info.php';</script>";
            exit();
        }

        $aten_id = $row['aten_id'];
        $in_time = strtotime($row['in_time']);
        $out_time_timestamp = strtotime($out_time);
        $hoursWorked = ($out_time_timestamp - $in_time) / 3600;

        $sql_update_out_time = "UPDATE attendance_info 
                                SET out_time = :out_time 
                                WHERE aten_id = :aten_id";
        $stmt_update_out_time = $obj_admin->db->prepare($sql_update_out_time);
        $stmt_update_out_time->bindParam(':out_time', $out_time, PDO::PARAM_STR);
        $stmt_update_out_time->bindParam(':aten_id', $aten_id, PDO::PARAM_INT);
        $stmt_update_out_time->execute();

        if ($hoursWorked < 8) {
            $_SESSION['underworked_alert'] = true;
        }

        header("Location: attendance-info.php");
        exit();
    } else {
        echo "<script>alert('Please clock in before trying to clock out.'); window.location.href='attendance-info.php';</script>";
        exit();
    }
}

// Handle Clock In
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['clock_in'])) {
    $status = $_POST['status'] ?? 'Work from Office';
    $in_time = date("Y-m-d H:i:s");
    $current_date = date("Y-m-d");

    $sql_check = "SELECT COUNT(*) FROM attendance_info WHERE atn_user_id = :user_id AND DATE(in_time) = :current_date";
    $stmt_check = $obj_admin->db->prepare($sql_check);
    $stmt_check->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_check->bindParam(':current_date', $current_date, PDO::PARAM_STR);
    $stmt_check->execute();
    $clock_in_count = $stmt_check->fetchColumn();

    if ($clock_in_count > 0) {
        echo "<script>alert('You have already clocked in today!'); window.location.href='attendance-info.php';</script>";
        exit();
    }

    $sql = "INSERT INTO attendance_info (atn_user_id, in_time, status) VALUES (:user_id, :in_time, :status)";
    $stmt = $obj_admin->db->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':in_time', $in_time, PDO::PARAM_STR);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        header("Location: attendance-info.php");
        exit();
    }
}

// Fetch Data
$status_filter = $_GET['status_filter'] ?? '';
$sql = "SELECT a.*, b.fullname FROM attendance_info a 
        LEFT JOIN tbl_admin b ON a.atn_user_id = b.user_id";

if (!empty($status_filter)) {
    $sql .= " WHERE a.status = :status_filter";
}

$sql .= " ORDER BY a.aten_id DESC";
$stmt = $obj_admin->db->prepare($sql);

if (!empty($status_filter)) {
    $stmt->bindParam(':status_filter', $status_filter, PDO::PARAM_STR);
}

$stmt->execute();
$page_name = "Attendance";
include("include/sidebar.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Management</title>
    <style>
        #remarksModal {
            display: none;
            position: fixed;
            z-index: 9999;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border: 1px solid #ccc;
            padding: 20px;
            width: 300px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Attendance Management</h2>

    <!-- Status Filter -->
    <form method="get">
        <select name="status_filter" class="form-control" onchange="this.form.submit()">
            <option value="">All</option>
            <option value="Work from Office" <?php echo ($status_filter == "Work from Office") ? 'selected' : ''; ?>>Work from Office</option>
            <option value="Work from Home" <?php echo ($status_filter == "Work from Home") ? 'selected' : ''; ?>>Work from Home</option>
            <option value="Leave" <?php echo ($status_filter == "Leave") ? 'selected' : ''; ?>>Leave</option>
        </select>
    </form>

    <!-- Clock In/Out -->
    <form method="post">
        <button type="submit" name="clock_in" class="btn btn-success">Clock In</button>
        <button type="submit" name="clock_out" class="btn btn-danger" style="background-color:#E65200;">Clock Out</button>
    </form>

    <!-- Attendance Table -->
    <table class="table">
        <thead>
            <tr>
                <th>S.N.</th>
                <th>Name</th>
                <th>In Time</th>
                <th>Out Time</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $serial = 1;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            ?>
            <tr>
                <td><?= $serial++; ?></td>
                <td><?= htmlspecialchars($row['fullname']); ?></td>
                <td><?= htmlspecialchars($row['in_time']); ?></td>
                <td><?= htmlspecialchars($row['out_time'] ?? '------'); ?></td>
                <td>
                    <form method="post" action="update_status.php">
                        <input type="hidden" name="aten_id" value="<?= $row['aten_id']; ?>">
                        <select name="status" class="form-control" onchange="this.form.submit()" id="statusSelect">
                            <option value="Work from Office" <?= ($row['status'] == "Work from Office") ? 'selected' : ''; ?> id="workFromOfficeOption">Work from Office</option>
                            <option value="Work from Home" <?= ($row['status'] == "Work from Home") ? 'selected' : ''; ?>>Work from Home</option>
                            <option value="Leave" <?= ($row['status'] == "Leave") ? 'selected' : ''; ?>>Leave</option>
                        </select>
                    </form>
                </td>
                <td><?= htmlspecialchars($row['remarks'] ?? 'No remarks'); ?></td>
                <td>
                    <?php if (empty($row['remarks'])): ?>
                        <button class="btn btn-success" onclick="openRemarksModal(<?= $row['aten_id']; ?>, '')">Add Remarks</button>
                    <?php else: ?>
                        <button class="btn btn-warning" onclick="openRemarksModal(<?= $row['aten_id']; ?>, '<?= htmlspecialchars(addslashes($row['remarks'])); ?>')">Edit Remarks</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Remarks Modal -->
<div id="remarksModal">
    <form id="remarksForm" method="post" action="">
        <input type="hidden" name="submit_remarks" value="1">
        <input type="hidden" name="aten_id" id="aten_id">
        <textarea name="remarks" id="remarksText" class="form-control" required></textarea>
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" onclick="closeRemarksModal()" class="btn btn-secondary">Cancel</button>
    </form>
</div>

<script>
    function openRemarksModal(aten_id, remarks) {
        document.getElementById("aten_id").value = aten_id;
        document.getElementById("remarksText").value = remarks;
        document.getElementById("remarksModal").style.display = "block";
    }

    function closeRemarksModal() {
        document.getElementById("remarksModal").style.display = "none";
    }
</script>

</body>
</html>
