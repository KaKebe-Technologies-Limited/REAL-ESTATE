<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: {$conn->connect_error}");
}

// Function to get all properties without filtering by owner status
function getAllProperties($conn) {
    $properties = [];
    
    // Fetch rental properties
    $sql = "SELECT 
        r.property_id, 
        r.property_name, 
        o.subscription_status,
        'rental' as property_type
        FROM rental_property r
        LEFT JOIN property_owner o ON r.owner_id = o.owner_id";
    
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }
    } else {
        echo "Error: " . $conn->error;
    }
    
    // Fetch sales properties
    $sql = "SELECT 
        s.property_id, 
        s.property_name, 
        o.subscription_status,
        'sale' as property_type
        FROM sales_property s
        LEFT JOIN property_owner o ON s.owner_id = o.owner_id";
    
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }
    } else {
        echo "Error: " . $conn->error;
    }
    
    return $properties;
}

// Function to get only properties from active owners
function getActiveOwnerProperties($conn) {
    $properties = [];
    
    // Fetch rental properties
    $sql = "SELECT 
        r.property_id, 
        r.property_name, 
        o.subscription_status,
        'rental' as property_type
        FROM rental_property r
        LEFT JOIN property_owner o ON r.owner_id = o.owner_id
        WHERE (o.subscription_status = 'active' OR o.owner_id IS NULL)";
    
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }
    } else {
        echo "Error: " . $conn->error;
    }
    
    // Fetch sales properties
    $sql = "SELECT 
        s.property_id, 
        s.property_name, 
        o.subscription_status,
        'sale' as property_type
        FROM sales_property s
        LEFT JOIN property_owner o ON s.owner_id = o.owner_id
        WHERE (o.subscription_status = 'active' OR o.owner_id IS NULL)";
    
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }
    } else {
        echo "Error: " . $conn->error;
    }
    
    return $properties;
}

// Get all properties
$allProperties = getAllProperties($conn);
echo "<h2>All Properties</h2>";
echo "<p>Total: " . count($allProperties) . "</p>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Owner Status</th></tr>";
foreach ($allProperties as $property) {
    echo "<tr>";
    echo "<td>" . $property['property_id'] . "</td>";
    echo "<td>" . $property['property_name'] . "</td>";
    echo "<td>" . $property['property_type'] . "</td>";
    echo "<td>" . ($property['subscription_status'] ?? 'No Owner') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Get active owner properties
$activeProperties = getActiveOwnerProperties($conn);
echo "<h2>Properties from Active Owners</h2>";
echo "<p>Total: " . count($activeProperties) . "</p>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Owner Status</th></tr>";
foreach ($activeProperties as $property) {
    echo "<tr>";
    echo "<td>" . $property['property_id'] . "</td>";
    echo "<td>" . $property['property_name'] . "</td>";
    echo "<td>" . $property['property_type'] . "</td>";
    echo "<td>" . ($property['subscription_status'] ?? 'No Owner') . "</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();
?>
