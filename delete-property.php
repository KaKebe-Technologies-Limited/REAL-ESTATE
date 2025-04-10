<?php
require_once 'log_activity.php';

header('Content-Type: application/json'); // Ensure JSON response
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);
require_once 'config.php'; // Include database configuration

$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    throw new Exception("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['property_id'])) {
    $property_id = intval($_POST['property_id']);
    
    // First, get property details before deletion
    $fetch_sql = "SELECT property_name, parish AS region FROM rental_property WHERE property_id = ?";
    $fetch_stmt = $conn->prepare($fetch_sql);
    $fetch_stmt->bind_param('i', $property_id);
    $fetch_stmt->execute();
    $result = $fetch_stmt->get_result();
    $propertyData = $result->fetch_assoc();
    $fetch_stmt->close();
    
    if ($propertyData) {
        // Now proceed with deletion
        $delete_sql = "DELETE FROM rental_property WHERE property_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param('i', $property_id);
        
        if ($delete_stmt->execute()) {
            // Log the activity with the retrieved property details
            logPropertyDeleted($propertyData['property_name'], $propertyData['region']);

            echo json_encode([
                'success' => true,
                'message' => 'Property deleted successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error deleting property'
            ]);
        }
        
        $delete_stmt->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Property not found'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}

$conn->close();
?>