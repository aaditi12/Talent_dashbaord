<?php 

ob_start();
session_start();
require 'classes/admin_class.php';
$obj_admin = new Admin_Class();

if(isset($_GET['logout'])){
	$obj_admin->admin_logout();
}

if (isset($_POST['login_btn'])) {
    $username = $_POST['username'];
    $password = $_POST['admin_password'];
    $work_mode = $_POST['work_mode'];

    if (empty($username) || empty($password)) {
        $info = "Username and Password are required.";
    } else {
        if ($work_mode === "office") {
            $office_wifi_ssid = "YourOfficeWiFiSSID"; // Replace with your actual office WiFi SSID
            $user_wifi_ssid = shell_exec("netsh wlan show interfaces | findstr SSID"); // Windows command

            if (strpos($user_wifi_ssid, $office_wifi_ssid) === false) {
                $info = "You must be connected to the office Wi-Fi to log in.";
            } else {
                $info = $obj_admin->admin_login_check($_POST);
            }
        } else {
            $info = $obj_admin->admin_login_check($_POST);
        }
    }
}
