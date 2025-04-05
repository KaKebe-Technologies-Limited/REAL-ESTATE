<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'real_estate');

// Establish database connection
try {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset to UTF8
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die("Error: Unable to connect to the database. Please try again later.");
}

// Application configuration
define('SITE_URL', 'http://localhost/REAL-ESTATE');
define('UPLOAD_PATH', __DIR__ . '/uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB in bytes

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Time zone setting
date_default_timezone_set('UTC');

// Define allowed file types for property images
define('ALLOWED_FILE_TYPES', [
    'image/jpeg',
    'image/png',
    'image/webp'
]);

// Security functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

?>