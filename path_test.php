<?php
// Display server information
echo "<h2>Server Information</h2>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// Check if the uploads directory exists
echo "<h2>Directory Check</h2>";
$uploadsDir = __DIR__ . '/uploads';
$rentalsDir = $uploadsDir . '/rentals';

echo "<p>Current directory: " . __DIR__ . "</p>";
echo "<p>Uploads directory exists: " . (is_dir($uploadsDir) ? 'Yes' : 'No') . "</p>";
echo "<p>Rentals directory exists: " . (is_dir($rentalsDir) ? 'Yes' : 'No') . "</p>";

// Check for the specific file
$testFile = $rentalsDir . '/67f6a81a0cb3d.jpeg';
echo "<p>Test file path: " . $testFile . "</p>";
echo "<p>Test file exists: " . (file_exists($testFile) ? 'Yes' : 'No') . "</p>";
if (file_exists($testFile)) {
    echo "<p>File size: " . filesize($testFile) . " bytes</p>";
    echo "<p>File permissions: " . substr(sprintf('%o', fileperms($testFile)), -4) . "</p>";
}

// List all files in the rentals directory
echo "<h2>Files in rentals directory</h2>";
if (is_dir($rentalsDir)) {
    $files = scandir($rentalsDir);
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            echo "<li>" . $file . " - " . filesize($rentalsDir . '/' . $file) . " bytes</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p>Cannot list files: directory does not exist</p>";
}

// Test direct image access
echo "<h2>Test Image Access</h2>";
echo "<p>Try accessing the image directly:</p>";
echo "<img src='/uploads/rentals/67f6a81a0cb3d.jpeg' style='max-width: 300px;'>";
echo "<p>Image URL: /uploads/rentals/67f6a81a0cb3d.jpeg</p>";

// Test with different path formats
echo "<h2>Test Different Path Formats</h2>";
echo "<p>With relative path:</p>";
echo "<img src='uploads/rentals/67f6a81a0cb3d.jpeg' style='max-width: 300px;'>";
echo "<p>Image URL: uploads/rentals/67f6a81a0cb3d.jpeg</p>";

echo "<p>With full server path:</p>";
echo "<img src='" . $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/REAL-ESTATE/uploads/rentals/67f6a81a0cb3d.jpeg' style='max-width: 300px;'>";
echo "<p>Image URL: " . $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/REAL-ESTATE/uploads/rentals/67f6a81a0cb3d.jpeg</p>";
?>
