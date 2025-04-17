<?php
require_once 'image_handler.php';
require_once 'log_activity.php';

header('Content-Type: application/json'); // Ensure JSON response
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php'; // Include database configuration

try {
    // Database connection
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }

    // Debug log the FILES array
    error_log('FILES array in add_sales.php: ' . print_r($_FILES, true));

    // Handle image uploads
    $imageHandler = new ImageHandler('sales');
    $uploadResult = $imageHandler->handleImageUploads($_FILES);

    // Debug log the upload result
    error_log('Upload result: ' . print_r($uploadResult, true));

    if (!$uploadResult['success']) {
        throw new Exception('Failed to upload images: ' . implode(', ', $uploadResult['errors']));
    }

    // Prepare property data
    $propertyData = [
        'property_name' => $_POST['property_name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'price' => $_POST['price'] ?? '',
        'property_type' => $_POST['property_type'] ?? '',
        'title' => $_POST['title'] ?? '',
        'utilities' => $_POST['utilities'] ?? '',
        'property_size' => $_POST['property_size'] ?? '',
        'bedrooms' => $_POST['bedrooms'] ?? 0,
        'bathrooms' => $_POST['bathrooms'] ?? 0,
        'status' => $_POST['status'] ?? '',
        'amenities' => isset($_POST['amenities']) ? implode(',', $_POST['amenities']) : '',
        'country' => $_POST['country'] ?? '',
        'region' => $_POST['region'] ?? '',
        'subregion' => $_POST['subregion'] ?? '',
        'parish' => $_POST['parish'] ?? '',
        'ward' => $_POST['ward'] ?? '',
        'cell' => $_POST['cell'] ?? '',
        'owner_id' => $_POST['owner_id'] ?? null,
        'manager_id' => $_POST['manager_id'] ?? null,
        'images' => implode(',', $uploadResult['images'])
    ];

    // Insert into database
    $query = "INSERT INTO sales_property (property_name, description, title, utilities, price, property_type,
                property_size, bedrooms, bathrooms, status, amenities, country, region, subregion,
                parish, ward, cell, owner_id, manager_id, images)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        'ssssssiiissssssssiis',
        $propertyData['property_name'],
        $propertyData['description'],
        $propertyData['title'],
        $propertyData['utilities'],
        $propertyData['price'],
        $propertyData['property_type'],
        $propertyData['property_size'],
        $propertyData['bedrooms'],
        $propertyData['bathrooms'],
        $propertyData['status'],
        $propertyData['amenities'],
        $propertyData['country'],
        $propertyData['region'],
        $propertyData['subregion'],
        $propertyData['parish'],
        $propertyData['ward'],
        $propertyData['cell'],
        $propertyData['owner_id'],
        $propertyData['manager_id'],
        $propertyData['images']
    );

    if (!$stmt->execute()) {
        throw new Exception('Failed to add sales property: ' . $stmt->error);
    }

    // Log the activity
    logPropertyAdded($propertyData['property_name'], $propertyData['region']);

    echo json_encode([
        'success' => true,
        'message' => 'Sales property added successfully',
        'images' => $uploadResult['images']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}

?>