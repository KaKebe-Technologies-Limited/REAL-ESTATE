<?php
require_once 'config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Database Check</h1>";

// Check if property_manager table exists
$result = $conn->query("SHOW TABLES LIKE 'property_manager'");
if ($result->num_rows == 0) {
    echo "<p>Table 'property_manager' does not exist!</p>";
} else {
    echo "<p>Table 'property_manager' exists.</p>";
}

// Get all managers
$result = $conn->query("SELECT * FROM property_manager");
echo "<p>Total managers in database: " . $result->num_rows . "</p>";

if ($result->num_rows > 0) {
    echo "<h2>Manager details:</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Username</th><th>Name</th><th>Email</th><th>Phone</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["manager_id"] . "</td>";
        echo "<td>" . $row["username"] . "</td>";
        echo "<td>" . $row["first_name"] . " " . $row["last_name"] . "</td>";
        echo "<td>" . $row["email"] . "</td>";
        echo "<td>" . $row["phone"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No managers found in the database!</p>";
}

// Check if any managers are assigned to properties
$result = $conn->query("SELECT COUNT(*) as count FROM rental_property WHERE manager_id IS NOT NULL");
$row = $result->fetch_assoc();
echo "<p>Rental properties with managers assigned: " . $row["count"] . "</p>";

$result = $conn->query("SELECT COUNT(*) as count FROM sales_property WHERE manager_id IS NOT NULL");
$row = $result->fetch_assoc();
echo "<p>Sales properties with managers assigned: " . $row["count"] . "</p>";

// Test the search query directly
$searchTerm = "%";  // Search for everything
echo "<h2>Testing manager search query:</h2>";

$query = "SELECT
    m.*,
    (SELECT COUNT(*) FROM rental_property rp WHERE rp.manager_id = m.manager_id) +
    (SELECT COUNT(*) FROM sales_property sp WHERE sp.manager_id = m.manager_id) as property_count
FROM property_manager m
WHERE
    (m.username LIKE ? OR
    m.email LIKE ? OR
    m.phone LIKE ?)
GROUP BY m.manager_id
ORDER BY m.username
LIMIT 10";

$stmt = $conn->prepare($query);
$stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

echo "<p>Search query returned: " . $result->num_rows . " results</p>";

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Username</th><th>Name</th><th>Email</th><th>Phone</th><th>Property Count</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["manager_id"] . "</td>";
        echo "<td>" . $row["username"] . "</td>";
        echo "<td>" . $row["first_name"] . " " . $row["last_name"] . "</td>";
        echo "<td>" . $row["email"] . "</td>";
        echo "<td>" . $row["phone"] . "</td>";
        echo "<td>" . $row["property_count"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Search query returned no results!</p>";
}

$conn->close();
?>
