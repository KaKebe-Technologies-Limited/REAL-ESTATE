<?php
header('Content-Type: text/html');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

// Define the directory to scan
$js_dir = __DIR__ . '/assets/js';

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

// Function to scan JS files and fix fetch URLs
function scan_and_fix_js_files($directory, $endpoints) {
    $results = [];
    $files = glob($directory . '/*.js');
    
    foreach ($files as $file) {
        $filename = basename($file);
        $content = file_get_contents($file);
        $original_content = $content;
        $matches = [];
        $fixed = false;
        
        // Look for fetch calls
        foreach ($endpoints as $endpoint) {
            // Pattern to match fetch('endpoint.php') or fetch("endpoint.php") or fetch(`endpoint.php`)
            $pattern = "/fetch\s*\(\s*['\"`](" . preg_quote($endpoint, '/') . ")['\"`]\s*\)/";
            if (preg_match($pattern, $content)) {
                // Replace with AppConfig.getApiUrl
                $content = preg_replace($pattern, "fetch(AppConfig.getApiUrl('$1'))", $content);
                $fixed = true;
            }
            
            // Pattern to match fetch('endpoint.php', {
            $pattern = "/fetch\s*\(\s*['\"`](" . preg_quote($endpoint, '/') . ")['\"`]\s*,\s*\{/";
            if (preg_match($pattern, $content)) {
                // Replace with AppConfig.getApiUrl
                $content = preg_replace($pattern, "fetch(AppConfig.getApiUrl('$1'), {", $content);
                $fixed = true;
            }
        }
        
        // Save changes if any were made
        if ($fixed && $content !== $original_content) {
            file_put_contents($file, $content);
            $results[] = [
                'file' => $filename,
                'status' => 'Fixed',
                'message' => 'Updated fetch calls to use AppConfig.getApiUrl'
            ];
        } else {
            $results[] = [
                'file' => $filename,
                'status' => 'No changes',
                'message' => 'No matching fetch calls found or already using AppConfig'
            ];
        }
    }
    
    return $results;
}

// Run the fix
$results = scan_and_fix_js_files($js_dir, $php_endpoints);

// Output HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix JavaScript Paths</title>
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
        .fixed {
            color: green;
            font-weight: bold;
        }
        .no-changes {
            color: #888;
        }
    </style>
</head>
<body>
    <h1>JavaScript Path Fix Results</h1>
    <p>This script scans JavaScript files and updates fetch calls to use AppConfig.getApiUrl.</p>
    
    <h2>Results</h2>
    <table>
        <tr>
            <th>File</th>
            <th>Status</th>
            <th>Message</th>
        </tr>
        <?php foreach ($results as $result): ?>
        <tr>
            <td><?php echo htmlspecialchars($result['file']); ?></td>
            <td class="<?php echo $result['status'] === 'Fixed' ? 'fixed' : 'no-changes'; ?>">
                <?php echo htmlspecialchars($result['status']); ?>
            </td>
            <td><?php echo htmlspecialchars($result['message']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <h2>Next Steps</h2>
    <ol>
        <li>Make sure you've included <code>config.js</code> in your HTML files before other JavaScript files.</li>
        <li>Upload all modified JavaScript files to your server.</li>
        <li>Test your application to ensure everything works correctly.</li>
    </ol>
</body>
</html>
