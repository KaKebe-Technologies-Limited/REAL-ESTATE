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

    // Process images with environment detection
    // Check if we're on localhost or live site
    $serverName = strtolower($_SERVER['SERVER_NAME']);
    $isLocalhost = strpos($serverName, 'localhost') !== false || $serverName === '127.0.0.1';

    echo "<p>Server: $serverName, Is localhost: " . ($isLocalhost ? 'Yes' : 'No') . "</p>";

    $image_urls = array_map(function($img) use ($isLocalhost) {
        // Clean up the image path
        $img = trim($img);

        // Remove any existing /REAL-ESTATE prefixes
        $img = preg_replace('#^(/REAL-ESTATE)+/?#i', '/', $img);

        // Normalize the path to ensure it starts with uploads/
        if (strpos($img, 'uploads/') !== 0 && strpos($img, '/uploads/') !== 0) {
            // If it's an old path (just 'rentals/'), update it
            if (strpos($img, 'rentals/') === 0) {
                $img = 'uploads/' . $img;
            } else if (strpos($img, '/rentals/') === 0) {
                $img = '/uploads' . $img;
            }
        }

        // Remove any leading slash for consistency
        $img = ltrim($img, '/');

        // For localhost, we need to add the /REAL-ESTATE prefix
        // For live site, we use the path as is
        if ($isLocalhost) {
            return '/REAL-ESTATE/' . $img;
        } else {
            return '/' . $img;
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
