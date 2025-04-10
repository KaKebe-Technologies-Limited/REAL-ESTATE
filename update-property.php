<?php
require_once 'log_activity.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $property_id = intval($_POST['property_id']);
    $property_name = $_POST['property_name'];
    $price = floatval($_POST['price']);
    
    // First, get current property details
    $fetch_sql = "SELECT property_name, parish AS region FROM rental_property WHERE property_id = ?";
    $fetch_stmt = $conn->prepare($fetch_sql);
    $fetch_stmt->bind_param('i', $property_id);
    $fetch_stmt->execute();
    $propertyData = $fetch_stmt->get_result()->fetch_assoc();
    $fetch_stmt->close();
    
    if ($propertyData) {
        // Proceed with update
        $sql = "UPDATE rental_property SET property_name = ?, price = ? WHERE property_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sdi', $property_name, $price, $property_id);
        
        if ($stmt->execute()) {
            // Log the activity with the retrieved property details
            logPropertyUpdated($propertyData['property_name'], $propertyData['region']);

            echo json_encode([
                'success' => true,
                'message' => 'Property updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error updating property'
            ]);
        }
        
        $stmt->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Property not found'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

$conn->close();
?>