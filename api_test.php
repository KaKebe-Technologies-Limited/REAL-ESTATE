<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

// Log request information
$log = "=== " . date('Y-m-d H:i:s') . " ===\n";
$log .= "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
$log .= "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
$log .= "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
$log .= "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";

// Check if API files exist
$api_files = [
    'get_user_profile.php',
    'update_user_profile.php',
    'get_owners_managers.php',
    'handle_rental.php',
    'handle_sale.php',
    'handle_owner.php',
    'handle_manager.php'
];

$log .= "\nChecking API files:\n";
foreach ($api_files as $file) {
    $exists = file_exists($file);
    $log .= "$file: " . ($exists ? "EXISTS" : "NOT FOUND") . "\n";
    
    if ($exists) {
        $log .= "  Size: " . filesize($file) . " bytes\n";
        $log .= "  Permissions: " . substr(sprintf('%o', fileperms($file)), -4) . "\n";
    }
}

// Log session information
$log .= "\nSession Information:\n";
$log .= "Session active: " . (session_status() === PHP_SESSION_ACTIVE ? "Yes" : "No") . "\n";
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$log .= "Session ID: " . session_id() . "\n";
$log .= "Session variables: " . print_r($_SESSION, true) . "\n";

// Write log to file
file_put_contents('api_test.log', $log, FILE_APPEND);

// Return test data
echo json_encode([
    'success' => true,
    'message' => 'API test successful',
    'server' => [
        'request_method' => $_SERVER['REQUEST_METHOD'],
        'request_uri' => $_SERVER['REQUEST_URI'],
        'script_name' => $_SERVER['SCRIPT_NAME'],
        'document_root' => $_SERVER['DOCUMENT_ROOT'],
        'server_name' => $_SERVER['SERVER_NAME'],
        'http_host' => $_SERVER['HTTP_HOST']
    ],
    'files' => array_map(function($file) {
        return [
            'name' => $file,
            'exists' => file_exists($file),
            'size' => file_exists($file) ? filesize($file) : null,
            'permissions' => file_exists($file) ? substr(sprintf('%o', fileperms($file)), -4) : null
        ];
    }, $api_files),
    'session' => [
        'active' => session_status() === PHP_SESSION_ACTIVE,
        'id' => session_id(),
        'user_id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null
    ]
]);
