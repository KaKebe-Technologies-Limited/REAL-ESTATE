<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Set character set
$conn->set_charset("utf8mb4");

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate required fields
$required_fields = ['property_id', 'property_type', 'user_name', 'user_email', 'rating'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
        exit;
    }
}

// Sanitize and validate input
$property_id = intval($_POST['property_id']);
$property_type = $_POST['property_type'];
$user_name = $conn->real_escape_string($_POST['user_name']);
$user_email = $conn->real_escape_string($_POST['user_email']);
$rating = intval($_POST['rating']);
$review_text = isset($_POST['review_text']) ? $conn->real_escape_string($_POST['review_text']) : '';

// Validate property type
if ($property_type !== 'rental' && $property_type !== 'sale') {
    echo json_encode(['success' => false, 'message' => 'Invalid property type']);
    exit;
}

// Validate rating (1-5)
if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
    exit;
}

// Validate email
if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Check if the property exists
$property_table = $property_type === 'rental' ? 'rental_property' : 'sales_property';
$check_sql = "SELECT property_id FROM $property_table WHERE property_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param('i', $property_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Property not found']);
    $check_stmt->close();
    exit;
}
$check_stmt->close();

// Check if user has already rated this property
$check_rating_sql = "SELECT rating_id FROM property_ratings WHERE property_id = ? AND property_type = ? AND user_email = ?";
$check_rating_stmt = $conn->prepare($check_rating_sql);
$check_rating_stmt->bind_param('iss', $property_id, $property_type, $user_email);
$check_rating_stmt->execute();
$check_rating_result = $check_rating_stmt->get_result();

if ($check_rating_result->num_rows > 0) {
    // Update existing rating
    $update_sql = "UPDATE property_ratings SET rating = ?, review_text = ?, user_name = ?, created_at = CURRENT_TIMESTAMP WHERE property_id = ? AND property_type = ? AND user_email = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('ississ', $rating, $review_text, $user_name, $property_id, $property_type, $user_email);
    
    if ($update_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Your rating has been updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating rating: ' . $update_stmt->error]);
    }
    
    $update_stmt->close();
} else {
    // Insert new rating
    $insert_sql = "INSERT INTO property_ratings (property_id, property_type, user_name, user_email, rating, review_text) VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param('ississ', $property_id, $property_type, $user_name, $user_email, $rating, $review_text);
    
    if ($insert_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Thank you for your rating']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error submitting rating: ' . $insert_stmt->error]);
    }
    
    $insert_stmt->close();
}

$check_rating_stmt->close();
$conn->close();
?>
