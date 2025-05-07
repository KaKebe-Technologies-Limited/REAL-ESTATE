<?php
require_once 'config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Database Tables Check</h1>";

// Get all tables
$result = $conn->query("SHOW TABLES");
$tables = [];
while($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

// Check each table for phone or id_num fields
foreach($tables as $table) {
    echo "<h2>Table: $table</h2>";
    
    $result = $conn->query("SHOW COLUMNS FROM $table");
    
    if (!$result) {
        echo "<p>Could not access table structure.</p>";
        continue;
    }
    
    $hasPhoneOrId = false;
    $columns = [];
    
    while($row = $result->fetch_assoc()) {
        $columns[] = $row;
        if ($row['Field'] == 'phone' || $row['Field'] == 'id_num' || strpos($row['Field'], 'phone') !== false || strpos($row['Field'], 'id_num') !== false) {
            $hasPhoneOrId = true;
        }
    }
    
    if ($hasPhoneOrId) {
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach($columns as $column) {
            // Highlight phone or id_num fields
            $style = '';
            if ($column['Field'] == 'phone' || $column['Field'] == 'id_num' || strpos($column['Field'], 'phone') !== false || strpos($column['Field'], 'id_num') !== false) {
                $style = ' style="background-color: yellow;"';
            }
            
            echo "<tr$style>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . $column['Default'] . "</td>";
            echo "<td>" . $column['Extra'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No phone or ID fields found in this table.</p>";
    }
}

$conn->close();
?>
