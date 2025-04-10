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
    $requiredFields = ['username', 'first_name', 'last_name', 'email', 'password', 'phone', 'experience'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            exit;
        }
    }

    // Sanitize inputs
    $username = $conn->real_escape_string($_POST['username']);
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $experience = $conn->real_escape_string($_POST['experience']);
    $password = $_POST['password'];

    // Check if the username or email already exists
    $stmt = $conn->prepare("SELECT * FROM property_manager WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new manager
    $stmt = $conn->prepare("INSERT INTO property_manager (username, first_name, last_name, email, password, phone, experience, date_created) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssssi", $username, $first_name, $last_name, $email, $hashedPassword, $phone, $experience);

    if ($stmt->execute()) {
        // Log the activity after successful manager registration
        logManagerRegistered($manager_name);
        echo json_encode(['success' => true, 'message' => 'Manager registration successful!']);
    } else {
        error_log('SQL Error: ' . $stmt->error); // Log the SQL error
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}
?>