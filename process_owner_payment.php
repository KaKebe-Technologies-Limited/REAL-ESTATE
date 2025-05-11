<?php
session_start();
require_once 'pesapal_functions.php';
require_once 'log_activity.php';
header('Content-Type: application/json'); 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $requiredFields = ['username', 'password', 'email', 'first_name', 'last_name', 'id_type', 'id_num', 'address', 'phone'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            exit; 
        }
    }

    // Store user data in session
    $userData = [
        'username' => $_POST['username'],
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'id_type' => $_POST['id_type'],
        'id_num' => $_POST['id_num'],
        'address' => $_POST['address'],
        'phone' => $_POST['phone'],
        'password' => $_POST['password']
    ];
    
    // Store user data in session for later registration after payment
    registerTempUser($userData);
    
    // Get Pesapal token
    $token = getPesapalToken();
    if (!$token) {
        echo json_encode(['success' => false, 'message' => 'Failed to authenticate with payment gateway']);
        exit;
    }
    
    // Submit order request to Pesapal
    $orderResponse = submitOrderRequest($userData, $token);
    
    if (isset($orderResponse['redirect_url'])) {
        // Return success with redirect URL
        echo json_encode([
            'success' => true, 
            'message' => 'Please complete payment to finalize registration',
            'redirect_url' => $orderResponse['redirect_url'],
            'order_tracking_id' => $orderResponse['order_tracking_id'] ?? '',
            'merchant_reference' => $orderResponse['merchant_reference'] ?? ''
        ]);
    } else {
        // Return error
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to initiate payment process. Please try again.',
            'error' => $orderResponse['error'] ?? 'Unknown error'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}
