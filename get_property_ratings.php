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

// Check if property ID and type are provided
if (!isset($_GET['property_id']) || empty($_GET['property_id']) || !isset($_GET['property_type']) || empty($_GET['property_type'])) {
    echo json_encode(['success' => false, 'message' => 'Property ID and type are required']);
    exit;
}

$property_id = intval($_GET['property_id']);
$property_type = $_GET['property_type'];

// Validate property type
if (!in_array($property_type, ['rental', 'sale', 'rent'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid property type: ' . $property_type]);
    exit;
}

// Normalize property type (convert 'rent' to 'rental')
if ($property_type === 'rent') {
    $property_type = 'rental';
}

// Get average rating
$avg_sql = "SELECT AVG(rating) as average_rating, COUNT(*) as rating_count FROM property_ratings WHERE property_id = ? AND property_type = ?";
$avg_stmt = $conn->prepare($avg_sql);
$avg_stmt->bind_param('is', $property_id, $property_type);
$avg_stmt->execute();
$avg_result = $avg_stmt->get_result();
$avg_data = $avg_result->fetch_assoc();

$average_rating = $avg_data['average_rating'] ? round($avg_data['average_rating'], 1) : 0;
$rating_count = $avg_data['rating_count'];

// Get rating distribution
$dist_sql = "SELECT rating, COUNT(*) as count FROM property_ratings WHERE property_id = ? AND property_type = ? GROUP BY rating ORDER BY rating DESC";
$dist_stmt = $conn->prepare($dist_sql);
$dist_stmt->bind_param('is', $property_id, $property_type);
$dist_stmt->execute();
$dist_result = $dist_stmt->get_result();

$distribution = [
    5 => 0,
    4 => 0,
    3 => 0,
    2 => 0,
    1 => 0
];

while ($row = $dist_result->fetch_assoc()) {
    $distribution[$row['rating']] = $row['count'];
}

// Get recent reviews
$reviews_sql = "SELECT user_name, rating, review_text, DATE_FORMAT(created_at, '%M %d, %Y') as formatted_date
                FROM property_ratings
                WHERE property_id = ? AND property_type = ? AND review_text != ''
                ORDER BY created_at DESC
                LIMIT 5";
$reviews_stmt = $conn->prepare($reviews_sql);
$reviews_stmt->bind_param('is', $property_id, $property_type);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();

$reviews = [];
while ($row = $reviews_result->fetch_assoc()) {
    $reviews[] = $row;
}

// Log request details for debugging
error_log("Rating request for property ID: $property_id, type: $property_type");
error_log("Found $rating_count ratings with average: $average_rating");

// Prepare response
$response = [
    'success' => true,
    'average_rating' => $average_rating,
    'rating_count' => $rating_count,
    'distribution' => $distribution,
    'reviews' => $reviews,
    'property_id' => $property_id,
    'property_type' => $property_type
];

echo json_encode($response);

// Close statements and connection
$avg_stmt->close();
$dist_stmt->close();
$reviews_stmt->close();
$conn->close();
?>
