<?php
require 'authentication.php';
require 'db_connect.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->face_image)) {
    $image_data = $data->face_image;
    $image_data = str_replace('data:image/png;base64,', '', $image_data);
    $image_data = base64_decode($image_data);
    file_put_contents('uploads/temp_face.png', $image_data);

    // Run Python script for face recognition
    $output = shell_exec("python3 face_recognition.py uploads/temp_face.png");

    if (trim($output) !== "NO_MATCH") {
        $user_id = intval($output);
        $query = "SELECT * FROM admin_users WHERE id = $user_id";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_name'] = $row['username'];
            $_SESSION['security_key'] = md5($row['id'] . time());

            echo json_encode(["success" => true]);
            exit;
        }
    }
}

echo json_encode(["success" => false]);
?>
