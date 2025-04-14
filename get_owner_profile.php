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
    error_log("Database connection error: " . $conn->connect_error);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'] ?? 'unknown';

// Log debugging information
error_log("Loading profile for user_id: {$user_id}, user_type: {$user_type}");

// Check if we're using the correct table based on user type
$table = 'property_owner';
$id_field = 'owner_id';

$query = "SELECT first_name, last_name, email, username, profile_picture, phone, id_type, id_num, address
            FROM {$table} WHERE {$id_field} = ?";

error_log("Query: {$query}");

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    // Store profile picture path in session
    $_SESSION['profile_picture'] = $user['profile_picture'] ?: 'assets/images/profile.jpg';
    error_log("User found: " . json_encode($user));
    echo json_encode(['success' => true, 'user' => $user]);
} else {
    error_log("User not found with ID: {$user_id} in table: {$table}");
    echo json_encode(['success' => false, 'message' => 'User not found']);
}

$stmt->close();
$conn->close();
?>
