<?php
require_once 'config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to create property_ratings table
$sql = "
CREATE TABLE IF NOT EXISTS property_ratings (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    property_type ENUM('rental', 'sale') NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    user_email VARCHAR(100) NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (property_id, property_type),
    INDEX (rating)
);
";

// Execute SQL
if ($conn->multi_query($sql)) {
    echo "Ratings table created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
