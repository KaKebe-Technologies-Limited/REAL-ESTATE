<?php
require_once 'log_activity.php';
header('Content-Type: application/json'); // Ensure JSON response
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php'; // Include database configuration

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
        exit;
    }

    // Validate required fields
    $requiredFields = ['username', 'password', 'email', 'first_name', 'last_name', 'id_type', 'id_num', 'address', 'phone'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            exit;
        }
    }

    // Sanitize inputs
    $username = $conn->real_escape_string($_POST['username']);
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $id_type = $conn->real_escape_string($_POST['id_type']);
    $id_num = $conn->real_escape_string($_POST['id_num']);
    $address = $conn->real_escape_string($_POST['address']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = $_POST['password'];

    // Check if the username or email already exists
    $stmt = $conn->prepare("SELECT * FROM property_owner WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new property owner
    $stmt = $conn->prepare("INSERT INTO property_owner (username, first_name, last_name, email, password, phone, id_type, id_num, address, date_created) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssssssss", $username, $first_name, $last_name, $email, $hashedPassword, $phone, $id_type, $id_num, $address);

    if ($stmt->execute()) {
        // Log the activity after successful owner registration
        $owner_name = "$first_name $last_name";
        logActivity('registration', 'New Owner Registered', "Owner $owner_name has registered", 'fas fa-user-plus', 'bg-success');
        echo json_encode(['success' => true, 'message' => 'Registration successful! You can now login with your credentials.']);
    } else {
        echo json_encode(['success' => false, 'message' => "Error: {$stmt->error}"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}