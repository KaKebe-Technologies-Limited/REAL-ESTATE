<?php
/**
 * Mock functions for Pesapal API
 * 
 * This file provides mock implementations of the Pesapal API functions
 * for testing purposes. It should be included instead of pesapal_functions.php
 * when testing the payment flow.
 */

// Include the original functions file
require_once 'pesapal_config.php';
require_once 'config.php';

/**
 * Mock Pesapal authentication token
 * 
 * @return string A mock authentication token
 */
function getPesapalToken() {
    error_log("MOCK: Getting Pesapal token");
    return 'mock-token-' . time();
}

/**
 * Mock submit order request to Pesapal
 * 
 * @param array $userData User registration data
 * @param string $token Pesapal authentication token
 * @return array Mock response from Pesapal
 */
function submitOrderRequest($userData, $token) {
    error_log("MOCK: Submitting order request with data: " . print_r($userData, true));
    
    // Generate a unique merchant reference
    $merchantReference = 'REG-' . time() . '-' . rand(1000, 9999);
    
    // Store the order data in session for later use
    $_SESSION['pesapal_order'] = [
        'merchant_reference' => $merchantReference,
        'user_data' => $userData,
        'order_tracking_id' => 'TRACK-' . time() . '-' . rand(1000, 9999)
    ];
    
    return [
        'redirect_url' => 'test_callback.php',
        'order_tracking_id' => $_SESSION['pesapal_order']['order_tracking_id'],
        'merchant_reference' => $merchantReference
    ];
}

/**
 * Mock get transaction status from Pesapal
 * 
 * @param string $orderTrackingId The order tracking ID from Pesapal
 * @param string $token Pesapal authentication token
 * @return array Mock transaction status details
 */
function getTransactionStatus($orderTrackingId, $token) {
    error_log("MOCK: Getting transaction status for order: $orderTrackingId");
    
    // Check if we have a mock transaction status in session
    if (isset($_SESSION['mock_transaction_status'])) {
        return $_SESSION['mock_transaction_status'];
    }
    
    // Default mock response
    return [
        'payment_status_description' => 'Completed',
        'payment_method' => 'Credit Card',
        'confirmation_code' => 'MOCK-' . time() . '-' . rand(1000, 9999)
    ];
}

/**
 * Save payment information to database
 * 
 * @param int $ownerId Owner ID
 * @param array $paymentData Payment data from Pesapal
 * @return bool Success status
 */
function savePaymentInfo($ownerId, $paymentData) {
    error_log("MOCK: Saving payment info for owner ID: $ownerId with data: " . print_r($paymentData, true));
    
    global $conn;
    
    if (!isset($conn)) {
        $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            return false;
        }
    }
    
    // Determine payment status
    $status = 'pending';
    if (isset($paymentData['payment_status_description'])) {
        if ($paymentData['payment_status_description'] == 'Completed') {
            $status = 'completed';
        } elseif ($paymentData['payment_status_description'] == 'Failed') {
            $status = 'failed';
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO owner_payments (
        owner_id, 
        amount, 
        currency, 
        transaction_id, 
        order_tracking_id, 
        merchant_reference, 
        payment_method, 
        payment_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $confirmationCode = $paymentData['confirmation_code'] ?? '';
    $orderTrackingId = $paymentData['order_tracking_id'] ?? '';
    $merchantReference = $paymentData['merchant_reference'] ?? '';
    $paymentMethod = $paymentData['payment_method'] ?? '';
    
    $stmt->bind_param(
        "idssssss",
        $ownerId,
        REGISTRATION_FEE,
        'UGX',
        $confirmationCode,
        $orderTrackingId,
        $merchantReference,
        $paymentMethod,
        $status
    );
    
    $result = $stmt->execute();
    
    if ($result) {
        // Update owner payment status
        $ownerStatus = ($status == 'completed') ? 'paid' : 'pending';
        $updateStmt = $conn->prepare("UPDATE property_owner SET payment_status = ? WHERE owner_id = ?");
        $updateStmt->bind_param("si", $ownerStatus, $ownerId);
        $updateStmt->execute();
    }
    
    return $result;
}

/**
 * Register temporary user data in session
 * 
 * @param array $userData User registration data
 * @return void
 */
function registerTempUser($userData) {
    error_log("MOCK: Registering temp user with data: " . print_r($userData, true));
    // Store user data in session for later registration after payment
    $_SESSION['temp_owner_data'] = $userData;
}

/**
 * Complete user registration after successful payment
 * 
 * @param array $userData User registration data
 * @return int|bool Owner ID on success, false on failure
 */
function completeOwnerRegistration($userData) {
    error_log("MOCK: Completing owner registration with data: " . print_r($userData, true));
    
    global $conn;
    
    if (!isset($conn)) {
        $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            return false;
        }
    }
    
    // Check if username or email already exists
    $checkStmt = $conn->prepare("SELECT * FROM property_owner WHERE username = ? OR email = ?");
    $checkStmt->bind_param("ss", $userData['username'], $userData['email']);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        error_log("Username or email already exists");
        return false;
    }
    
    // Hash the password
    $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
    
    // Insert the new property owner
    $stmt = $conn->prepare("INSERT INTO property_owner (
        username, 
        first_name, 
        last_name, 
        email, 
        password, 
        phone, 
        id_type, 
        id_num, 
        address, 
        date_created,
        payment_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'paid')");
    
    $stmt->bind_param(
        "sssssssss",
        $userData['username'],
        $userData['first_name'],
        $userData['last_name'],
        $userData['email'],
        $hashedPassword,
        $userData['phone'],
        $userData['id_type'],
        $userData['id_num'],
        $userData['address']
    );
    
    if ($stmt->execute()) {
        $ownerId = $conn->insert_id;
        error_log("Owner registered successfully with ID: $ownerId");
        return $ownerId;
    }
    
    error_log("Error registering owner: " . $stmt->error);
    return false;
}
