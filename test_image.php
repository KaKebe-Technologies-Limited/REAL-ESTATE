<?php
require_once 'config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connect to database
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get a rental property with images
$result = $conn->query("SELECT property_id, images FROM rental_property WHERE images IS NOT NULL AND images != '' LIMIT 1");

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $property_id = $row['property_id'];
    $images = $row['images'];

    echo "<h2>Property ID: $property_id</h2>";
    echo "<p>Raw image paths from database: $images</p>";

    // Process images like in handle_rental.php
    $image_urls = array_map(function($img) {
        // Make sure the path starts with 'uploads/'
        $img = trim($img);
        if (strpos($img, 'uploads/') !== 0 && strpos($img, '/uploads/') !== 0) {
            // If it's an old path (just 'rentals/'), update it
            if (strpos($img, 'rentals/') === 0) {
                $img = 'uploads/' . $img;
            } else if (strpos($img, '/rentals/') === 0) {
                $img = '/uploads' . $img;
            }
        }
        // Ensure it has the correct URL format for the REAL-ESTATE project
        if (strpos($img, '/') === 0) {
            // If it starts with a slash, add the project name
            return '/REAL-ESTATE' . $img;
        } else {
            // Otherwise add both the project name and a slash
            return '/REAL-ESTATE/' . $img;
        }
    }, explode(',', $images));

    echo "<h2>Processed Image URLs</h2>";
    echo "<ul>";
    foreach ($image_urls as $url) {
        echo "<li>$url</li>";
    }
    echo "</ul>";

    echo "<h2>Image Display Test</h2>";
    foreach ($image_urls as $url) {
        echo "<div style='margin-bottom: 20px;'>";
        echo "<p>URL: $url</p>";
        echo "<img src='$url' style='max-width: 300px;'>";
        echo "</div>";
    }
} else {
    echo "<p>No rental properties with images found</p>";
}

$conn->close();
?>
