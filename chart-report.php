<?php
// Start the session if it's not already started
session_start();

// Include necessary files or database connection
// include('your_database_connection_file.php'); // Uncomment if you need DB connection

// Define the page name for active link
$page_name = "Chart Report";

// Optionally, fetch some data from the database for chart visualization
// Example: $tasksData = getChartDataFromDB(); // Uncomment if you fetch data from DB
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Chart Report - Employee Task Management System</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="./assets/css/bootstrap.theme.min.css">
  <link rel="stylesheet" href="./assets/css/custom.css">
  <style>
    .chart-container {
      margin-top: 100px;
      padding: 20px;
    }
  </style>

  <!-- Include Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script src="./assets/js/jquery.min.js"></script>
  <script src="./assets/js/bootstrap.min.js"></script>

  <script type="text/javascript">
    // Function to render chart
    function renderChart() {
      var ctx = document.getElementById('taskChart').getContext('2d');

      // Sample data for the chart
      var chartData = {
        labels: ['Task 1', 'Task 2', 'Task 3', 'Task 4', 'Task 5'], // Task names
        datasets: [{
          label: 'Task Completion (%)',
          data: [80, 55, 95, 60, 75], // Completion percentage (replace with your data)
          backgroundColor: 'rgba(54, 162, 235, 0.2)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        }]
      };

      var chartOptions = {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true
          }
        }
      };

      new Chart(ctx, {
        type: 'bar', // Bar chart type
        data: chartData,
        options: chartOptions
      });
    }

    // Load chart when page loads
    window.onload = function() {
      renderChart();
    };
  </script>
</head>

<body>
  <!-- Your Navbar -->
  <nav class="navbar navbar-inverse sidebar navbar-fixed-top" role="navigation">
    <div class="container-fluid">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-sidebar-navbar-collapse-1">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <div class="container-fluid">
          <div class="navbar-center">
            <a class="navbar-brand" href="task-info.php" style="color: #E65200;">
              <?php echo $_SESSION['name']; ?>
            </a>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <?php
      $user_role = $_SESSION['user_role'];
      if ($user_role == 1) { ?>
        <div class="collapse navbar-collapse" id="bs-sidebar-navbar-collapse-1">
          <ul class="nav navbar-nav navbar-nav-custom">
            <li <?php if($page_name == "Task Info"){ echo 'class="active"'; } ?>>
              <a href="task-info.php" style="background-color: #F8F8F8; color: #E65200;">
                <i class="fa fa-laptop"></i> Task Management
              </a>
            </li>
            <li <?php if($page_name == "Attendance"){ echo "class=\"active\""; } ?>>
              <a href="attendance-info.php" style="background-color: #F8F8F8; color: #E65200;">
                <i class="fa fa-calendar" aria-hidden="true"></i> Attendance
              </a>
            </li>
            <li <?php if($page_name == "Chart Report"){ echo "class=\"active\""; } ?>>
              <a href="chart-report.php" style="background-color: #F8F8F8; color: #E65200;">
                <i class="fa fa-chart-line" aria-hidden="true"></i> Charts
              </a>
            </li>
            <li><a href="?logout=logout" style="background-color: #F8F8F8; color: #E65200;">
              <i class="fa fa-sign-out" aria-hidden="true"></i> Logout
            </a></li>
          </ul>
        </div>
      <?php } ?>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="main">
    <div class="chart-container">
      <h2>Employee Task Completion Status - Chart Report</h2>
      <canvas id="taskChart" width="400" height="200"></canvas>
    </div>
  </div>

</body>
</html>
