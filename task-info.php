<?php
require 'authentication.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['security_key'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$user_role = $_SESSION['user_role']; // 1 = Admin, 2 = Employee

if (isset($_GET['delete_task']) && isset($_GET['task_id'])) {
    $action_id = intval($_GET['task_id']);
    $sql = "DELETE FROM task_info WHERE task_id = :id";
    $sent_po = "task-info.php";
    $obj_admin->delete_data_by_this_method($sql, $action_id, $sent_po);
}

if (isset($_POST['add_task_post'])) {
    $obj_admin->add_new_task($_POST);
}

$page_name = "Task_Info";
include("include/sidebar.php");

$page_size = isset($_GET['page_size']) ? (int)$_GET['page_size'] : 10;
$page_size = in_array($page_size, [10, 25, 50]) ? $page_size : 10;

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $page_size;

$base_sql = "FROM task_info a INNER JOIN tbl_admin b ON a.t_user_id = b.user_id";
$conditions = [];

if ($user_role == 2) {
    $conditions[] = "a.t_user_id = $user_id";
} else {
    if (!empty($_GET['employee_id'])) {
        $conditions[] = "a.t_user_id = " . intval($_GET['employee_id']);
    }
    if (!empty($_GET['start_date'])) {
        $conditions[] = "DATE(a.t_start_time) >= '" . $_GET['start_date'] . "'";
    }
    if (!empty($_GET['end_date'])) {
        $conditions[] = "DATE(a.t_end_time) <= '" . $_GET['end_date'] . "'";
    }
}

$where_clause = (!empty($conditions)) ? " WHERE " . implode(" AND ", $conditions) : "";

$count_sql = "SELECT COUNT(*) AS total " . $base_sql . $where_clause;
$count_stmt = $obj_admin->manage_all_info($count_sql);
$total_rows = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_rows / $page_size);

$sql = "SELECT a.*, b.fullname " . $base_sql . $where_clause . " ORDER BY a.task_id DESC LIMIT $page_size OFFSET $offset";
$info = $obj_admin->manage_all_info($sql);
?>

<!-- External CSS & Fonts -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
.btn-orange {
    background-color: #F8F8F8;
    color: #E65200;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 1rem;
    transition: background-color 0.3s ease, transform 0.2s ease;
}
.btn-orange:hover {
    background-color: #F8F8F8;
    transform: translateY(-2px);
}
tr {
    background-color: #F8F8F8;
    color: #E65200;
}
th {
    color: #E65200;
    padding: 12px;
    text-align: left;
    font-weight: bold;
    background-color: #F8F8F8;
}
tr:hover {
    background-color: #F8F8F8;
    transition: 0.3s ease-in-out;
}
</style>



<!-- Filter Form -->
<div class="container mt-3">
    <form class="row g-2 mb-4" method="get">
        <div class="col-md-3">
            <label for="employee_id">Filter by Employee:</label>
            <select name="employee_id" class="form-control">
                <option value="">All Employees</option>
                <?php 
                $users = $obj_admin->manage_all_info("SELECT user_id, fullname FROM tbl_admin");
                while ($row_user = $users->fetch(PDO::FETCH_ASSOC)) {
                    $selected = (isset($_GET['employee_id']) && $_GET['employee_id'] == $row_user['user_id']) ? 'selected' : '';
                    echo "<option value='{$row_user['user_id']}' $selected>{$row_user['fullname']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" class="form-control" value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : '' ?>">
        </div>
        <div class="col-md-3">
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" class="form-control" value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : '' ?>">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
            <a href="task-info.php" class="btn btn-secondary">Reset Filters</a>
        </div>
    </form>
</div>

<div class="container">
    <div class="d-flex justify-content-end">
        <div class="btn-group">
            <button class="btn btn-orange btn-menu" data-toggle="modal" data-target="#myModal">
                Assign New Task <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
</div>

<!-- Page Size Selector -->
<div class="d-flex justify-content-between align-items-center mb-2 px-2">
    <form method="get" class="form-inline">
        <?php 
        foreach ($_GET as $key => $value) {
            if (!in_array($key, ['page_size', 'page'])) {
                echo "<input type='hidden' name='$key' value='" . htmlspecialchars($value) . "' />";
            }
        }
        ?>
        <label for="page_size" class="me-2">Show</label>
        <select name="page_size" id="page_size" class="form-control form-control-sm" onchange="this.form.submit()">
            <option value="10" <?= $page_size == 10 ? 'selected' : '' ?>>10</option>
            <option value="25" <?= $page_size == 25 ? 'selected' : '' ?>>25</option>
            <option value="50" <?= $page_size == 50 ? 'selected' : '' ?>>50</option>
        </select>
        <span class="ms-2">rows per page</span>
    </form>
</div>

<!-- Task Table -->
<div class="table-responsive">
    <table class="table table-condensed table-custom">
        <thead>
            <tr>
                <th>#</th>
                <th>Task Title</th>
                <th>Assigned To</th>
                <th>Start Time</th>
                <th>End Time</th>

                <th>Status</th>
                
                <th>Action</th>

                

            </tr>
        </thead>
        <tbody>
            <?php 
            $serial = $offset + 1;
            while ($row = $info->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td><?= $serial++ ?></td>
                <td><?= htmlspecialchars($row['t_title']) ?></td>
                <td><?= htmlspecialchars($row['fullname']) ?></td>
                <td><?= $row['t_start_time'] ?></td>
                <td><?= $row['t_end_time'] ?></td>
                <td>
                    <?= ($row['status'] == 1) ? '<small class="label label-warning">In Progress</small>' : '<small class="label label-success">Completed</small>' ?>
                </td>
                <td>
                    <a href="edit-task.php?task_id=<?= $row['task_id'] ?>"><i class="fas fa-edit"></i></a> 
                    <a href="task-details.php?task_id=<?= $row['task_id'] ?>"><i class="fas fa-eye"></i></a>
                    <a href="task-info.php?delete_task=1&task_id=<?= $row['task_id'] ?>" onclick="return confirm('Are you sure you want to delete this task?');">
                        <i class="fas fa-trash-alt text-danger"></i>
                    </a>
                </td>
                
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog add-category-modal">
        <div class="modal-content rounded-0">
            <div class="modal-header rounded-0 d-flex">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h2 class="modal-title ms-auto" style="margin-top;">Assign New Task</h2>
            </div>
            <div class="modal-body rounded-0">
                <div class="row">
                    <div class="col-md-12">
                        <form role="form" action="" method="post" autocomplete="off">
                            <div class="form-horizontal">
                                <div class="form-group">
                                    <label class="control-label text-p-reset">Task Title</label>
                                    <input type="text" placeholder="Task Title" name="task_title" class="form-control rounded-0" required>
                                </div>
                                <div class="form-group">
                                    <label class="control-label text-p-reset">Task Description</label>
                                    <textarea name="task_description" placeholder="Task Description" class="form-control rounded-0" rows="5"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="control-label text-p-reset">Start Time</label>
                                    <input type="text" name="t_start_time" id="t_start_time" class="form-control rounded-0">
                                </div>
                                <div class="form-group">
                                    <label class="control-label text-p-reset">End Time</label>
                                    <input type="text" name="t_end_time" id="t_end_time" class="form-control rounded-0">
                                </div>
                                 <div class="form-group">
                                    <label class="control-label text-p-reset">completion time</label>
                                    <input type="text" name="completion_time" id="completion_time" class="form-control rounded-0">
                                </div>
                            </div>
                            <div class="form-group">
                                    <label class="control-label text-p-reset">Assign To</label>
                                    <div class="">
                                        <?php 
                                            // Fetch all users (admins + employees)
                                            $sql = "SELECT user_id, fullname FROM tbl_admin";
                                            $info = $obj_admin->manage_all_info($sql);
                                        ?>
                                        <select class="form-control rounded-0" name="assign_to" required>
                                            <option value="">Select a User...</option>
                                            <?php while ($row_user = $info->fetch(PDO::FETCH_ASSOC)) { ?>
                                                <option value="<?php echo $row_user['user_id']; ?>">
                                                    <?php echo $row_user['fullname']; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="submit" name="add_task_post" class="btn btn-primary rounded-0 btn-sm" style="background-color: #E65200; color:">Assign Task</button>
                                    <button type="button" class="btn btn-default rounded-0 btn-sm" data-dismiss="modal">Cancel</button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<nav aria-label="Task pagination">
    <ul class="pagination justify-content-center mt-4">
        <?php 
        $query_string = $_GET;
        $query_string['page_size'] = $page_size;
        unset($query_string['page']);
        $query_base = http_build_query($query_string);

        if ($page > 1) {
            $prev_page = $page - 1;
            echo "<li class='page-item'><a class='page-link' href='?{$query_base}&page={$prev_page}'>Previous</a></li>";
        }

        for ($i = 1; $i <= $total_pages; $i++) {
            $active = ($i == $page) ? "active" : "";
            echo "<li class='page-item $active'><a class='page-link' href='?{$query_base}&page={$i}'>{$i}</a></li>";
        }

        if ($page < $total_pages) {
            $next_page = $page + 1;
            echo "<li class='page-item'><a class='page-link' href='?{$query_base}&page={$next_page}'>Next</a></li>";
        }
        ?>
    </ul>
</nav>
<?php endif; ?>

<p class="text-center mt-2">
    Showing <?= ($offset + 1) ?>–<?= min($offset + $page_size, $total_rows) ?> of <?= $total_rows ?> tasks
</p>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
flatpickr('#t_start_time', { enableTime: true });
flatpickr('#t_end_time', { enableTime: true });

</script>

