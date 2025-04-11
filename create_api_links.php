<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

// Define the PHP files that are accessed via AJAX
$php_endpoints = [
    'get_user_profile.php',
    'update_user_profile.php',
    'get_owners_managers.php',
    'handle_rental.php',
    'handle_sale.php',
    'handle_owner.php',
    'handle_manager.php',
    'get_rentals.php',
    'get_sales.php',
    'get_owners.php',
    'get_managers.php',
    'login.php',
    'owner_login.php',
    'manager_login.php',
    'register.php',
    'owner_registration.php',
    'manager_registration.php',
    'update_property.php',
    'delete_property.php',
    'search.php',
    'search_all.php',
    'get_owner_details.php',
    'get_manager_details.php',
    'update_owner.php',
    'update_manager.php',
];

// Current directory
$current_dir = __DIR__;

// Function to create a PHP proxy file
function create_proxy_file($endpoint, $directory) {
    $proxy_content = <<<EOT
<?php
// This is a proxy file for $endpoint
// It forwards requests to the actual file in the REAL-ESTATE directory

// Include the actual file
require_once __DIR__ . '/REAL-ESTATE/$endpoint';
EOT;

    $proxy_path = $directory . '/' . $endpoint;
    
    // Only create if the file doesn't already exist
    if (!file_exists($proxy_path)) {
        file_put_contents($proxy_path, $proxy_content);
        return [
            'endpoint' => $endpoint,
            'status' => 'Created',
            'path' => $proxy_path
        ];
    } else {
        return [
            'endpoint' => $endpoint,
            'status' => 'Already exists',
            'path' => $proxy_path
        ];
    }
}

// Create proxy files
$results = [];
foreach ($php_endpoints as $endpoint) {
    $results[] = create_proxy_file($endpoint, $current_dir);
}

// Output HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create API Proxy Files</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .created {
            color: green;
            font-weight: bold;
        }
        .exists {
            color: #888;
        }
    </style>
</head>
<body>
    <h1>API Proxy Files Creation Results</h1>
    <p>This script creates proxy PHP files in the root directory that forward requests to the actual files in the REAL-ESTATE directory.</p>
    
    <h2>Results</h2>
    <table>
        <tr>
            <th>Endpoint</th>
            <th>Status</th>
            <th>Path</th>
        </tr>
        <?php foreach ($results as $result): ?>
        <tr>
            <td><?php echo htmlspecialchars($result['endpoint']); ?></td>
            <td class="<?php echo $result['status'] === 'Created' ? 'created' : 'exists'; ?>">
                <?php echo htmlspecialchars($result['status']); ?>
            </td>
            <td><?php echo htmlspecialchars($result['path']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <h2>Next Steps</h2>
    <ol>
        <li>Upload these proxy files to the root directory of your website (not in the REAL-ESTATE directory).</li>
        <li>Make sure the REAL-ESTATE directory is in the same parent directory as these proxy files.</li>
        <li>Test your application to ensure everything works correctly.</li>
    </ol>
</body>
</html>
