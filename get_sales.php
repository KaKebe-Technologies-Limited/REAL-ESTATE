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

// Fetch total number of sales properties
$total_query = "SELECT COUNT(*) AS total FROM sales_property";
$total_result = $conn->query($total_query);
$total_sales = $total_result->fetch_assoc()['total'];

// Fetch sales properties with pagination
$query = "SELECT property_name, owner_id, manager_id, CONCAT(parish, ', ', ward) AS location, price, property_type 
            FROM sales_property 
            LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

$sales = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Fetch owner name using owner_id
        $owner_query = "SELECT username FROM property_owner WHERE owner_id = " . intval($row['owner_id']);
        $owner_result = $conn->query($owner_query);
        $owner_name = $owner_result->num_rows > 0 ? $owner_result->fetch_assoc()['username'] : 'N/A';

        // Fetch manager name using manager_id
        $manager_query = "SELECT username FROM property_manager WHERE manager_id = " . intval($row['manager_id']);
        $manager_result = $conn->query($manager_query);
        $manager_name = $manager_result->num_rows > 0 ? $manager_result->fetch_assoc()['username'] : 'N/A';

        // Combine data
        $sales[] = [
            'property_name' => $row['property_name'],
            'owner_name' => $owner_name,
            'manager_name' => $manager_name,
            'location' => $row['location'],
            'price' => $row['price'],
            'availability' => $row['property_type']
        ];
    }
}

// Return data as JSON
echo json_encode([
    'success' => true,
    'sales' => $sales,
    'total' => $total_sales,
    'page' => $page,
    'limit' => $limit
]);

$conn->close();
?>