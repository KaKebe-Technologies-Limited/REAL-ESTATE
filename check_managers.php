<?php
require_once 'config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if property_manager table exists
$result = $conn->query("SHOW TABLES LIKE 'property_manager'");
if ($result->num_rows == 0) {
    echo "Table 'property_manager' does not exist!\n";
    exit;
}

// Get all managers
$result = $conn->query("SELECT * FROM property_manager");
echo "Total managers in database: " . $result->num_rows . "\n";

if ($result->num_rows > 0) {
    echo "\nManager details:\n";
    echo "--------------------\n";
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["manager_id"] . 
             " | Username: " . $row["username"] . 
             " | Name: " . $row["first_name"] . " " . $row["last_name"] . 
             " | Email: " . $row["email"] . 
             " | Phone: " . $row["phone"] . "\n";
    }
} else {
    echo "No managers found in the database!\n";
}

// Check if any managers are assigned to properties
$result = $conn->query("SELECT COUNT(*) as count FROM rental_property WHERE manager_id IS NOT NULL");
$row = $result->fetch_assoc();
echo "\nRental properties with managers assigned: " . $row["count"] . "\n";

$result = $conn->query("SELECT COUNT(*) as count FROM sales_property WHERE manager_id IS NOT NULL");
$row = $result->fetch_assoc();
echo "Sales properties with managers assigned: " . $row["count"] . "\n";

$conn->close();
?>
