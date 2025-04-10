<?php
header('Content-Type: application/json'); // Ensure JSON response
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);
require_once 'config.php'; // Include database configuration

// Database connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get pagination parameters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

// Fetch total number of owners
$total_query = "SELECT COUNT(*) AS total FROM property_owner";
$total_result = $conn->query($total_query);
$total_owners = $total_result->fetch_assoc()['total'];

// Fetch owners with pagination
$query = "SELECT po.owner_id AS owner_id, po.username AS owner_name, 
            COUNT(rp.owner_id) AS property_count, 
            po.email 
            FROM property_owner po
            LEFT JOIN rental_property rp ON po.owner_id = rp.owner_id
            GROUP BY po.owner_id
            LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

$owners = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $owners[] = [
            'owner_id' => $row['owner_id'],
            'owner_name' => $row['owner_name'],
            'property_count' => $row['property_count'],
            'email' => $row['email']
        ];
    }
}

// Return data as JSON
echo json_encode([
    'success' => true,
    'owners' => $owners,
    'total' => $total_owners,
    'page' => $page,
    'limit' => $limit
]);

$conn->close();
?>