<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

// Log all request information
$log = "=== " . date('Y-m-d H:i:s') . " ===\n";
$log .= "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
$log .= "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
$log .= "CONTENT_TYPE: " . (isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'Not set') . "\n";

// Log POST data
$log .= "POST data:\n";
foreach ($_POST as $key => $value) {
    $log .= "  $key: $value\n";
}

// Log GET data
$log .= "GET data:\n";
foreach ($_GET as $key => $value) {
    $log .= "  $key: $value\n";
}

// Log REQUEST data
$log .= "REQUEST data:\n";
foreach ($_REQUEST as $key => $value) {
    $log .= "  $key: $value\n";
}

// Log raw input
$rawInput = file_get_contents('php://input');
$log .= "Raw input: " . $rawInput . "\n";
$log .= "===============================\n\n";

file_put_contents('action_test.log', $log, FILE_APPEND);

// Get action from various sources
$action = '';
if (isset($_POST['action'])) {
    $action = $_POST['action'];
} elseif (isset($_GET['action'])) {
    $action = $_GET['action'];
} elseif (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
} else {
    // Try to parse from raw input
    $jsonData = json_decode($rawInput, true);
    if ($jsonData && isset($jsonData['action'])) {
        $action = $jsonData['action'];
    }
}

// Return response
echo json_encode([
    'success' => !empty($action),
    'message' => empty($action) ? 'Invalid action: ' : 'Action received: ' . $action,
    'action' => $action,
    'post' => $_POST,
    'get' => $_GET,
    'request' => $_REQUEST,
    'raw_input' => $rawInput,
    'server' => [
        'request_method' => $_SERVER['REQUEST_METHOD'],
        'content_type' => isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'Not set',
        'http_host' => $_SERVER['HTTP_HOST'],
        'request_uri' => $_SERVER['REQUEST_URI']
    ]
]);
