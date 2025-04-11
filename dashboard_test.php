<?php
// This file helps diagnose dashboard access issues

// Log server information
$log = "=== " . date('Y-m-d H:i:s') . " ===\n";
$log .= "SERVER_NAME: " . $_SERVER['SERVER_NAME'] . "\n";
$log .= "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
$log .= "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
$log .= "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";

// Check if dashboard files exist
$files_to_check = [
    'adminDashboard.php',
    'admindashboard.php',
    'AdminDashboard.php',
    'ADMINDASHBOARD.PHP'
];

$log .= "\nChecking dashboard files:\n";
foreach ($files_to_check as $file) {
    $exists = file_exists($file);
    $log .= "$file: " . ($exists ? "EXISTS" : "NOT FOUND") . "\n";
    
    if ($exists) {
        $log .= "  Size: " . filesize($file) . " bytes\n";
        $log .= "  Permissions: " . substr(sprintf('%o', fileperms($file)), -4) . "\n";
    }
}

// Log PHP session information
$log .= "\nSession Information:\n";
$log .= "Session active: " . (session_status() === PHP_SESSION_ACTIVE ? "Yes" : "No") . "\n";
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$log .= "Session ID: " . session_id() . "\n";
$log .= "Session variables: " . print_r($_SESSION, true) . "\n";

// Write log to file
file_put_contents('dashboard_test.log', $log, FILE_APPEND);

// Output HTML response
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
        }
        .test-section {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
        }
        .file-exists {
            color: green;
            font-weight: bold;
        }
        .file-missing {
            color: red;
            font-weight: bold;
        }
        pre {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>Dashboard Access Test</h1>
    
    <div class="test-section">
        <h2>Server Information</h2>
        <pre><?php
            echo "SERVER_NAME: " . htmlspecialchars($_SERVER['SERVER_NAME']) . "\n";
            echo "REQUEST_URI: " . htmlspecialchars($_SERVER['REQUEST_URI']) . "\n";
            echo "SCRIPT_NAME: " . htmlspecialchars($_SERVER['SCRIPT_NAME']) . "\n";
            echo "DOCUMENT_ROOT: " . htmlspecialchars($_SERVER['DOCUMENT_ROOT']) . "\n";
        ?></pre>
    </div>
    
    <div class="test-section">
        <h2>Dashboard Files</h2>
        <ul>
            <?php foreach ($files_to_check as $file): ?>
                <li>
                    <?php if (file_exists($file)): ?>
                        <span class="file-exists">EXISTS</span>: <?php echo htmlspecialchars($file); ?>
                        (<?php echo filesize($file); ?> bytes)
                    <?php else: ?>
                        <span class="file-missing">NOT FOUND</span>: <?php echo htmlspecialchars($file); ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <div class="test-section">
        <h2>Session Information</h2>
        <pre><?php
            echo "Session active: " . (session_status() === PHP_SESSION_ACTIVE ? "Yes" : "No") . "\n";
            echo "Session ID: " . session_id() . "\n";
            echo "Session variables: \n";
            print_r($_SESSION);
        ?></pre>
    </div>
    
    <div class="test-section">
        <h2>Try Dashboard Links</h2>
        <p>Click these links to test different dashboard URLs:</p>
        <a href="adminDashboard.php" class="btn">adminDashboard.php</a>
        <a href="admindashboard.php" class="btn">admindashboard.php</a>
        <a href="AdminDashboard.php" class="btn">AdminDashboard.php</a>
    </div>
</body>
</html>
