<?php 
header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli('localhost', 'root', '', 'allea');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Retrieve form data
$property_name = $_POST['property_name'] ?? '';
$price = $_POST['price'] ?? '';
$utilities = $_POST['utilities'] ?? '';
$property_type = $_POST['property_type'] ?? '';
$title = $_POST['title'] ?? '';
$amenities = isset($_POST['amenities']) ? implode(',', $_POST['amenities']) : '';
$property_size = $_POST['property_size'] ?? '';
$owner_id = $_POST['owner_id'] ?? null;
$manager_id = $_POST['manager_id'] ?? null;
$country = $_POST['country'] ?? '';
$region = $_POST['region'] ?? '';
$subregion = $_POST['subregion'] ?? '';
$parish = $_POST['parish'] ?? '';
$ward = $_POST['ward'] ?? '';
$cell = $_POST['cell'] ?? '';

// Handle file uploads
$uploaded_images = [];
if (isset($_FILES['images'])) {
    $upload_dir = 'uploads/sales';
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        $file_name = basename($_FILES['images']['name'][$key]);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($tmp_name, $target_file)) {
            $uploaded_images[] = $target_file;
        }
    }
}

// Insert data into the database
$query = "INSERT INTO sales_property (property_name, price, utilities, property_type, title, amenities, property_size, owner_id, manager_id, country, region, subregion, parish, ward, cell, images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);
$images = implode(',', $uploaded_images);
$stmt->bind_param(
    'sssssssiisssssss',
    $property_name,
    $price,
    $utilities,
    $property_type,
    $title,
    $amenities,
    $property_size,
    $owner_id,
    $manager_id,
    $country,
    $region,
    $subregion,
    $parish,
    $ward,
    $cell,
    $images
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Property added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add property']);
}

$stmt->close();
$conn->close();
?>