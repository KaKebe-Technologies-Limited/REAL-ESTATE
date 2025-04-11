<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

// Get server information
$server_info = [
    'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? 'Not set',
    'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'Not set',
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'Not set',
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'Not set',
    'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? 'Not set',
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'Not set',
    'PHP_SELF' => $_SERVER['PHP_SELF'] ?? 'Not set',
    'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? 'Not set',
    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'Not set',
    'QUERY_STRING' => $_SERVER['QUERY_STRING'] ?? 'Not set',
    'HTTP_REFERER' => $_SERVER['HTTP_REFERER'] ?? 'Not set',
];

// Get directory structure
$current_dir = __DIR__;
$parent_dir = dirname($current_dir);
$dir_structure = [
    'Current directory' => $current_dir,
    'Parent directory' => $parent_dir,
    'Files in current directory' => scandir($current_dir),
];

// Check for specific files
$files_to_check = [
    'get_user_profile.php',
    'update_user_profile.php',
    'get_owners_managers.php',
    'handle_rental.php',
    'handle_sale.php',
    'handle_owner.php',
    'handle_manager.php',
    'adminDashboard.php',
    'admindashboard.php',
    'AdminDashboard.php',
];

$file_status = [];
foreach ($files_to_check as $file) {
    $absolute_path = $current_dir . '/' . $file;
    $file_status[$file] = [
        'exists' => file_exists($absolute_path),
        'absolute_path' => $absolute_path,
        'readable' => is_readable($absolute_path),
        'size' => file_exists($absolute_path) ? filesize($absolute_path) : 'N/A',
        'permissions' => file_exists($absolute_path) ? substr(sprintf('%o', fileperms($absolute_path)), -4) : 'N/A',
    ];
}

// Check for .htaccess
$htaccess_path = $current_dir . '/.htaccess';
$htaccess_content = file_exists($htaccess_path) ? file_get_contents($htaccess_path) : 'Not found';

// Check for real estate directory
$real_estate_dir = $parent_dir . '/REAL-ESTATE';
$real_estate_exists = is_dir($real_estate_dir);
$real_estate_files = $real_estate_exists ? scandir($real_estate_dir) : 'Directory not found';

// Return all information
echo json_encode([
    'success' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'server_info' => $server_info,
    'directory_structure' => $dir_structure,
    'file_status' => $file_status,
    'htaccess_content' => $htaccess_content,
    'real_estate_directory' => [
        'path' => $real_estate_dir,
        'exists' => $real_estate_exists,
        'files' => $real_estate_files,
    ],
], JSON_PRETTY_PRINT);
