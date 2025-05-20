<?php
// Start session and enable error reporting for debugging
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';

// Set Content-Type header for JSON responses
header('Content-Type: application/json');

// For testing purposes, we'll simulate a logged-in user
// In a real scenario, this would be set by the login process
$_SESSION['user_id'] = 1; // Assuming user ID 1 exists
$_SESSION['user_type'] = isset($_GET['user_type']) ? $_GET['user_type'] : 'owner';

// Get the API endpoint to test
$api = isset($_GET['api']) ? $_GET['api'] : 'owner';

// Redirect to the appropriate API endpoint
if ($api === 'owner') {
    // Test owner income API
    $_SESSION['user_type'] = 'owner';
    include 'get_owner_income.php';
} else if ($api === 'manager') {
    // Test manager income API
    $_SESSION['user_type'] = 'manager';
    include 'get_manager_income.php';
} else {
    // Invalid API
    echo json_encode([
        'success' => false,
        'message' => 'Invalid API endpoint. Use "owner" or "manager".'
    ]);
}
?>
