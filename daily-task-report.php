<?php 
require 'authentication.php'; // Admin authentication check 

// Auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: index.php');
}

// Check admin role
$user_role = $_SESSION['user_role'];

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

$page_name = "Task_Info";
include("include/sidebar.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Task Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        canvas {
            width: 300px !important;
            height: 250px !important;
            font-size: 18px;
            color: #B35C1E;
        }
        button {
            padding: 10px 20px;
            background-color: #B35C1E;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #924A18;
        }
    </style>
</head>
<body>
<div class="row">
    <div class="col-md-12">
        <div class="well well-custom rounded-0" style="background-color: #F8F8F8;">
            <div class="row">
                <div class="col-md-4">
                    <input type="date" id="date" value="<?= $date ?>" class="form-control rounded-0">
                </div>

                <div class="col-md-4">
                    <select id="assignedTo" class="form-control rounded-0">
                        <option value="">All Employees</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <button id="filter">Filter</button>
                    <button id="resetFilter">Reset Filter</button>
                </div>
            </div>
            <center><h3>Daily Task Report</h3></center>
            <div class="table-responsive" id="printout">
                <table class="table table-condensed table-custom">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Task Title</th>
                            <th>Assigned To</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Status</th>
                            <th>Time taken</th>
                            
                            
                        </tr>
                    </thead>
                    <tbody id="taskTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart Section -->
<canvas id="taskBarChart"></canvas>
<canvas id="taskPieChart"></canvas>

<script>
$(document).ready(function () {
    let barChart, pieChart;

    function fetchEmployees() {
        $.getJSON("fetch_employees.php", function (data) {
            $("#assignedTo").html('<option value="">All Employees</option>');
            data.forEach(employee => {
                $("#assignedTo").append(`<option value="${employee.fullname}">${employee.fullname}</option>`);
            });
        });
    }

    function fetchTableData(date = '', assignedTo = '') {
        $.ajax({
            url: "fetch_table_data.php",
            type: "GET",
            data: { date: date, assigned_to: assignedTo },
            success: function (response) {
                $("#taskTableBody").html(response);
            }
        });
    }

    function fetchChartData(date = '', assignedTo = '') {
        $.ajax({
            url: "fetch_chart_data.php",
            type: "GET",
            data: { date: date, assigned_to: assignedTo },
            dataType: "json",
            success: function (data) {
                updateCharts(data);
            },
            error: function () {
                console.error("Error fetching chart data.");
            }
        });
    }

    function updateCharts(data) {
        let ctxBar = document.getElementById("taskBarChart").getContext("2d");
        let ctxPie = document.getElementById("taskPieChart").getContext("2d");

        if (barChart) barChart.destroy();
        if (pieChart) pieChart.destroy();

        barChart = new Chart(ctxBar, {
            type: "bar",
            data: {
                labels: data.labels,
                datasets: [{
                    label: "Tasks Completed",
                    data: data.values,
                    backgroundColor: "#B35C1E"
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        pieChart = new Chart(ctxPie, {
            type: "pie",
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.values,
                    backgroundColor: ["#B35C1E", "#924A18", "#F8B400"]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    $("#filter").click(function () {
        let date = $("#date").val();
        let assignedTo = $("#assignedTo").val();
        fetchTableData(date, assignedTo);
        fetchChartData(date, assignedTo);
    });

    $("#resetFilter").click(function () {
        $("#date").val("");
        $("#assignedTo").val("");
        fetchTableData();
        fetchChartData();
    });

    $(document).on('click', '.add-completion-time-btn', function () {
        const taskId = $(this).data('task-id');
        const newTime = prompt("Enter Completion Time (HH:MM format):");

        if (newTime) {
            $.ajax({
                url: 'update_completion_time.php',
                type: 'POST',
                data: {
                    task_id: taskId,
                    completion_time: newTime
                },
                success: function (response) {
                    alert(response);
                    let date = $("#date").val();
                    let assignedTo = $("#assignedTo").val();
                    fetchTableData(date, assignedTo);
                }
            });
        }
    });

    fetchEmployees();
    fetchTableData();
    fetchChartData();
});
</script>
</body>
</html>




