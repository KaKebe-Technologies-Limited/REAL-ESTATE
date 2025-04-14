<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'owner') {
    header('Location: login.html');
    exit();
}

// Include database connection
require_once 'config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get owner ID from session
$owner_id = $_SESSION['user_id'];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';

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