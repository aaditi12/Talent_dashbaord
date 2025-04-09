<?php
require 'authentication.php';

$sql = "SELECT DISTINCT fullname FROM tbl_admin";
$stmt = $obj_admin->db->query($sql);
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($employees);
?>
