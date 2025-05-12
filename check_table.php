<?php
require_once 'config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get property_owner table structure
$result = $conn->query("SHOW COLUMNS FROM property_owner");

if ($result) {
    echo "Property Owner Table Structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo "Field: " . $row['Field'] . ", Type: " . $row['Type'] . ", Null: " . $row['Null'] . "\n";
    }
} else {
    echo "Error getting table structure: " . $conn->error;
}

$conn->close();
?>
