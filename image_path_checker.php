<?php
// This script checks image paths and helps diagnose path issues
header('Content-Type: application/json');

// Function to check if a file exists and get its details
function checkFile($path) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($path, '/');
    $exists = file_exists($fullPath);
    $size = $exists ? filesize($fullPath) : 0;
    $mime = $exists ? mime_content_type($fullPath) : '';

    return [
        'path' => $path,
        'full_path' => $fullPath,
        'exists' => $exists,
        'size' => $size,
        'mime_type' => $mime,
        'readable' => $exists ? is_readable($fullPath) : false
    ];
}

// Function to check a directory
function checkDirectory($path) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($path, '/');
    $exists = is_dir($fullPath);

    return [
        'path' => $path,
        'full_path' => $fullPath,
        'exists' => $exists,
        'is_dir' => $exists,
        'readable' => $exists ? is_readable($fullPath) : false,
        'writable' => $exists ? is_writable($fullPath) : false
    ];
}

// List of image paths to check
$imagePaths = [
    // Rental images
    'uploads/rentals/67f8daff523e0.jpeg',
    'uploads/rentals/67f8281b6d455.png',
    'uploads/rentals/67f6ecc38100d.jpeg',
    'uploads/rentals/67f6ad3f017d1.jpeg',
    'REAL-ESTATE/uploads/rentals/67f8daff523e0.jpeg',
    'REAL-ESTATE/uploads/rentals/67f8281b6d455.png',
    'REAL-ESTATE/uploads/rentals/67f6ecc38100d.jpeg',
    'REAL-ESTATE/uploads/rentals/67f6ad3f017d1.jpeg',

    // Sales images
    'uploads/sales/67f6e34582b9a.png',
    'uploads/sales/67f6e378e40e7.png',
    'uploads/sales/67f6ed2c5550e.jpeg',
    'REAL-ESTATE/uploads/sales/67f6e34582b9a.png',
    'REAL-ESTATE/uploads/sales/67f6e378e40e7.png',
    'REAL-ESTATE/uploads/sales/67f6ed2c5550e.jpeg'
];

// Directories to check
$directories = [
    'uploads',
    'uploads/rentals',
    'uploads/sales',
    'REAL-ESTATE',
    'REAL-ESTATE/uploads',
    'REAL-ESTATE/uploads/rentals',
    'REAL-ESTATE/uploads/sales'
];

// Check each image path
$imageResults = [];
foreach ($imagePaths as $path) {
    $imageResults[$path] = checkFile($path);
}

// Check each directory
$directoryResults = [];
foreach ($directories as $dir) {
    $directoryResults[$dir] = checkDirectory($dir);
}

// Get server information
$serverInfo = [
    'document_root' => $_SERVER['DOCUMENT_ROOT'],
    'script_filename' => $_SERVER['SCRIPT_FILENAME'],
    'server_name' => $_SERVER['SERVER_NAME'],
    'request_uri' => $_SERVER['REQUEST_URI'],
    'http_host' => $_SERVER['HTTP_HOST'],
    'is_localhost' => (strpos(strtolower($_SERVER['HTTP_HOST']), 'localhost') !== false || $_SERVER['HTTP_HOST'] === '127.0.0.1'),
    'php_version' => phpversion(),
    'os' => PHP_OS
];

// Check for .htaccess file
$htaccessPath = $_SERVER['DOCUMENT_ROOT'] . '/.htaccess';
$htaccessExists = file_exists($htaccessPath);
$htaccessContent = $htaccessExists && is_readable($htaccessPath) ? file_get_contents($htaccessPath) : 'Not readable';

// Return the results
echo json_encode([
    'server_info' => $serverInfo,
    'directories' => $directoryResults,
    'images' => $imageResults,
    'htaccess' => [
        'exists' => $htaccessExists,
        'path' => $htaccessPath,
        'content' => $htaccessContent
    ]
], JSON_PRETTY_PRINT);
?>
