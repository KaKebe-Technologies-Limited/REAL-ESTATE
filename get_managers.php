<?php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli('localhost', 'root', '', 'allea');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get pagination parameters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

// Fetch total number of managers
$total_query = "SELECT COUNT(*) AS total FROM property_manager";
$total_result = $conn->query($total_query);
$total_managers = $total_result->fetch_assoc()['total'];

// Fetch managers with pagination
$query = "SELECT po.username AS manager_name, 
            COUNT(rp.manager_id) AS property_count, 
            po.phone 
            FROM property_manager po
            LEFT JOIN rental_property rp ON po.manager_id = rp.manager_id
            GROUP BY po.manager_id
            LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

$managers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $managers[] = [
            'manager_name' => $row['manager_name'],
            'property_count' => $row['property_count'],
            'phone' => $row['phone']
        ];
    }
}

// Return data as JSON
echo json_encode([
    'success' => true,
    'managers' => $managers,
    'total' => $total_managers,
    'page' => $page,
    'limit' => $limit
]);

$conn->close();
?>