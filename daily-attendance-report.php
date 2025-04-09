<?php 
require 'authentication.php'; // Admin authentication check 

// auth check
$user_id = $_SESSION['admin_id'] ?? null;
$user_name = $_SESSION['name'] ?? null;
$security_key = $_SESSION['security_key'] ?? null;

if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
    exit();
}

$page_name = "Task_Info";
include("include/sidebar.php");

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<style>
  td{
    color: yellow;

  }
</style>
<div class="row">
  <div class="col-md-12">
    <div class="well well-custom rounded-0" style="background-color: #F8F8F8;">
      <div class="row">
        <div class="col-md-4">
            <input type="text" id="date" value="<?= $date ?>" class="form-control rounded-0">
        </div>
        <div class="col-md-4">
              <button class="btn btn-primary btn-sm btn-menu" type="button" id="filter" style="background-color:#E65200;">Filter</button>
              <button class="btn btn-success btn-sm btn-menu" type="button" id="print">Print</button>
        </div>
      </div>
      <center><h3>Daily Attendance Report</h3></center>
      <div class="table-responsive" id="printout">
        <table class="table table-condensed table-custom">
          <thead>
            <tr>
              <th>S.N.</th>
              <th>Name</th>
              <th>In Time</th>
              <th>Out Time</th>
              <th>Total Duration</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody id="attendanceTableBody">
          <?php 
              $sql = "SELECT a.*, b.fullname, 
                      TIMEDIFF(a.out_time, a.in_time) AS total_duration 
                      FROM attendance_info a 
                      LEFT JOIN tbl_admin b ON a.atn_user_id = b.user_id 
                      WHERE DATE(a.in_time) = :date
                      ORDER BY a.aten_id DESC";
              
              $stmt = $obj_admin->db->prepare($sql);
              $stmt->bindParam(':date', $date, PDO::PARAM_STR);
              $stmt->execute();
              $serial = 1;
              $num_row = $stmt->rowCount();

              if ($num_row == 0) {
                  echo '<tr><td colspan="6">No Data found</td></tr>';
              } else {
                  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                      $duration = $row['total_duration'] ?? 'N/A';
                      $highlight = "";
                      if ($duration !== 'N/A' && strtotime($duration) < strtotime('08:00:00')) {
                          $highlight = "style='background-color: red;'  class='alert-user'";
                      }
          ?>
              <tr <?= $highlight ?>>
                  <td><?php echo $serial++; ?></td>
                  <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                  <td><?php echo htmlspecialchars($row['in_time']); ?></td>
                  <td><?php echo htmlspecialchars($row['out_time'] ?? '------'); ?></td>
                  <td><?php echo htmlspecialchars($duration); ?></td>
                  <td><?php echo htmlspecialchars($row['remarks'] ?? 'No remarks'); ?></td>
              </tr>
          <?php }} ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    flatpickr("#date", { dateFormat: "Y-m-d" });

    document.getElementById("filter").addEventListener("click", function() {
        let selectedDate = document.getElementById("date").value;
        if (selectedDate) {
            window.location.href = "?date=" + selectedDate;
        }
    });

    document.getElementById("print").addEventListener("click", function() {
        let printContent = document.getElementById("printout").innerHTML;
        let newWin = window.open("", "", "width=800,height=600");
        newWin.document.write(`
            <html>
                <head>
                    <title>Print Report</title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                    </style>
                </head>
                <body>
                    <h3 style="text-align: center;">Daily Attendance Report</h3>
                    ${printContent}
                </body>
            </html>
        `);
        newWin.document.close();
        newWin.focus();
        newWin.print();
        newWin.close();
    });

    let alertUsers = document.querySelectorAll(".alert-user");
    if (alertUsers.length > 0) {
        let audio = new Audio("videos/alarm.mp3"); // Ensure the file exists in your directory
        audio.play();
    }
});
</script>




