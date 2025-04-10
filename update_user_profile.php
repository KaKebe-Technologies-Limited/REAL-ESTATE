<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];
$username = $_POST['username'];
$phone = $_POST['phone'];

// Handle password change if provided
$password_update = "";
$password_params = [];
if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
    // Verify current password
    $verify_query = "SELECT password FROM admin WHERE admin_id = ?";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (password_verify($_POST['current_password'], $user['password'])) {
        $new_password_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $password_update = ", password = ?";
        $password_params[] = $new_password_hash;
    } else {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit();
    }
}

// Handle profile picture upload
$profile_picture_update = "";
$profile_picture_params = [];
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
    $upload_dir = 'uploads/profile_picture/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
    $file_name = $user_id . '_' . time() . '.' . $file_extension;
    $target_path = $upload_dir . $file_name;
    
    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
        $profile_picture_update = ", profile_picture = ?";
        $profile_picture_params[] = $target_path;
    }
}

$query = "UPDATE admin SET 
            first_name = ?,
            last_name = ?,
            email = ?,
            username = ?,
            phone = ?
            $password_update
            $profile_picture_update
            WHERE admin_id = ?";

$params = array_merge(
    [$first_name, $last_name, $email, $username, $phone],
    $password_params,
    $profile_picture_params,
    [$user_id]
);

$types = str_repeat('s', count($params) - 1) . 'i';
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating profile']);
}

$stmt->close();
$conn->close();
?>