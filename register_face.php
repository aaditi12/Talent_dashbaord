<?php
require 'authentication.php';
require 'db_connection.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->face_image) && !empty($data->user_id)) {
    $image_data = $data->face_image;
    $image_data = str_replace('data:image/png;base64,', '', $image_data);
    $image_data = base64_decode($image_data);
    
    $user_id = intval($data->user_id);
    $image_path = "faces/user_" . $user_id . ".png";
    file_put_contents($image_path, $image_data);

    // Store face reference in DB
    $query = "UPDATE admin_users SET face_image = '$image_path' WHERE id = $user_id";
    mysqli_query($conn, $query);

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}
?>
