<?php 
require_once 'log_activity.php';
header('Content-Type: application/json');
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
$landlord = $_POST['landlord'] ?? '';
$security = isset($_POST['security']) ? implode(',', $_POST['security']) : '';
$utilities = $_POST['utilities'] ?? '';
$property_type = $_POST['property_type'] ?? '';
$convenience = $_POST['convenience'] ?? '';
$property_class = $_POST['property_class'] ?? '';
$property_size = $_POST['property_size'] ?? '';
$parking = $_POST['parking'] ?? '';
$amenities = isset($_POST['amenities']) ? implode(',', $_POST['amenities']) : '';
$country = $_POST['country'] ?? '';
$region = $_POST['region'] ?? '';
$subregion = $_POST['subregion'] ?? '';
$parish = $_POST['parish'] ?? '';
$ward = $_POST['ward'] ?? '';
$cell = $_POST['cell'] ?? '';
$owner_id = $_POST['owner_id'] ?? null;
$manager_id = $_POST['manager_id'] ?? null;

// Handle file uploads
$uploaded_images = [];
if (isset($_FILES['images'])) {
    $upload_dir = 'uploads/';
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        $file_name = basename($_FILES['images']['name'][$key]);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($tmp_name, $target_file)) {
            $uploaded_images[] = $target_file;
        }
    }
}

// Insert data into the database
$query = "INSERT INTO rental_property (property_name, price, landlord, security, utilities, property_type, convenience, property_class, property_size, parking, amenities, country, region, subregion, parish, ward, cell, owner_id, manager_id, images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);
$images = implode(',', $uploaded_images);
$stmt->bind_param(
    'sdssssssissssssssiis',
    $property_name,
    $price,
    $landlord,
    $security,
    $utilities,
    $property_type,
    $convenience,
    $property_class,
    $property_size,
    $parking,
    $amenities,
    $country,
    $region,
    $subregion,
    $parish,
    $ward,
    $cell,
    $owner_id,
    $manager_id,
    $images
);

if ($stmt->execute()) {
    // Log the activity after successful property addition
    logPropertyAdded($property_name, $location);
    echo json_encode(['success' => true, 'message' => 'Rental property added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add rental property']);
}

$stmt->close();
$conn->close();
?>