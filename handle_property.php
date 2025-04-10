<?php
header('Content-Type: application/json'); // Ensure JSON response
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);
require_once 'config.php'; // Include database configuration

if (isset($_GET['id'])) {
    error_log('Received ID: ' . $_GET['id']);
}

$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    throw new Exception("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $property_id = intval($_GET['id']);
    
    // Add debug logging
    error_log("Searching for property_id: " . $property_id);
    
    $sql = "SELECT property_id, property_name, owner_id, manager_id, CONCAT(parish, ', ', ward) AS location, price, property_class  FROM rental_property WHERE property_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Add debug logging for the query
    error_log("SQL Query: " . $sql . " with ID: " . $property_id);
    
    if ($property = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'property' => $property
        ]);
    } else {
        // Add debug logging for when no property is found
        error_log("No property found for ID: " . $property_id);
        echo json_encode([
            'success' => false,
            'message' => 'Property not found'
        ]);
    }
    
    $stmt->close();
} else {
    error_log("No ID provided in request");
    echo json_encode([
        'success' => false,
        'message' => 'No property ID provided'
    ]);
}

$conn->close();
?>