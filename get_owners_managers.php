<?php 
header('Content-Type: application/json'); // Ensure JSON response
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);
require_once 'config.php'; // Include database configuration

$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Fetch property owners
$owners_query = "SELECT owner_id, username FROM property_owner";
$owners_result = $conn->query($owners_query);
$owners = [];
while ($row = $owners_result->fetch_assoc()) {
    $owners[] = $row;
}

// Fetch property managers
$managers_query = "SELECT manager_id, username FROM property_manager";
$managers_result = $conn->query($managers_query);
$managers = [];
while ($row = $managers_result->fetch_assoc()) {
    $managers[] = $row;
}

echo json_encode([
    'success' => true,
    'owners' => $owners,
    'managers' => $managers
]);

$conn->close();

?>