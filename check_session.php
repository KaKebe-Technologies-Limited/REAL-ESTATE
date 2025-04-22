<?php
session_start();
header('Content-Type: application/json');

// Output all session data for debugging
echo json_encode([
    'session' => $_SESSION,
    'server' => [
        'script_name' => $_SERVER['SCRIPT_NAME'],
        'request_uri' => $_SERVER['REQUEST_URI'],
        'php_self' => $_SERVER['PHP_SELF']
    ]
]);
?>
