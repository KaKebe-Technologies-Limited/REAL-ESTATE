<?php
session_start();
header('Content-Type: application/json'); // Ensure JSON response
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);
require_once 'config.php'; // Include database configuration


try {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized access');
    }

    if (!isset($_GET['owner_id'])) {
        throw new Exception('No owner ID provided');
    }

    $owner_id = $_GET['owner_id'];
    
    // Debug: Log the owner_id
    error_log("Searching for owner_id: " . $owner_id);

    $query = "SELECT o.*, 
                        COUNT(p.property_id) as property_count 
                        FROM property_owner o 
                        LEFT JOIN rental_property p ON o.owner_id = p.owner_id 
                        WHERE o.owner_id = ?
                        GROUP BY o.owner_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($owner = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'data' => $owner
        ]);
    } else {
        // Debug: Log the SQL error if any
        error_log("SQL Error: " . $conn->error);
        echo json_encode([
            'success' => false,
            'message' => 'Owner not found',
            'owner_id' => $owner_id
        ]);
    }
    
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>


