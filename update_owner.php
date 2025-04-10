<?php
session_start();
header('Content-Type: application/json'); // Ensure JSON response
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);
require_once 'config.php'; // Include database configuration

$conn = new mysqli('localhost', 'root', '', 'allea');

// Check connection
if ($conn->connect_error) {
    throw new Exception("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $owner_id = $_POST['owner_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $id_type = $_POST['id_type'];
    $id_number = $_POST['id_number'];
    $address = $_POST['address'];

    $query = "UPDATE property_owners SET 
              first_name = ?, 
              last_name = ?, 
              email = ?, 
              phone = ?, 
              username = ?, 
              id_type = ?, 
              id_number = ?, 
              address = ? 
              WHERE owner_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssssssi', 
        $first_name, 
        $last_name, 
        $email, 
        $phone, 
        $username, 
        $id_type, 
        $id_number, 
        $address, 
        $owner_id
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>