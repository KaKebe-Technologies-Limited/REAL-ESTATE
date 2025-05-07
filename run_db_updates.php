<?php
require_once 'config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Database Update Script</h1>";

// Function to run SQL file
function runSqlFile($conn, $filename) {
    echo "<h2>Running $filename</h2>";
    
    // Read the SQL file
    $sql = file_get_contents($filename);
    
    // Split into individual statements
    $statements = explode(';', $sql);
    
    // Execute each statement
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            echo "<p>Executing: " . htmlspecialchars($statement) . "</p>";
            
            if ($conn->query($statement)) {
                echo "<p class='success'>Success!</p>";
            } else {
                echo "<p class='error'>Error: " . $conn->error . "</p>";
            }
        }
    }
}

// Run the SQL files in order
$files = [
    'fix_phone_field.sql',
    'fix_id_num_field.sql',
    'update_payment_tables.sql'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        runSqlFile($conn, $file);
    } else {
        echo "<p class='error'>File not found: $file</p>";
    }
}

$conn->close();
echo "<h2>Database update completed!</h2>";
?>
