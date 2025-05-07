<?php
require_once 'pesapal_config.php';
require_once 'config.php';

/**
 * Get Pesapal authentication token
 *
 * @return string|null The authentication token or null on failure
 */
function getPesapalToken() {
    $data = [
        'consumer_key' => PESAPAL_CONSUMER_KEY,
        'consumer_secret' => PESAPAL_CONSUMER_SECRET
    ];

    $ch = curl_init(PESAPAL_AUTH_URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        $result = json_decode($response, true);
        if (isset($result['token'])) {
            return $result['token'];
        }
    }

    error_log("Pesapal Auth Error: " . $response);
    return null;
}

/**
 * Submit order request to Pesapal
 *
 * @param array $userData User registration data
 * @param string $token Pesapal authentication token
 * @return array Response from Pesapal
 */
function submitOrderRequest($userData, $token) {
    // Generate a unique merchant reference
    $merchantReference = 'REG-' . time() . '-' . rand(1000, 9999);

    // Construct the callback URL
    $callbackUrl = BASE_URL . '/REAL-ESTATE/owner_payment_callback.php';
    error_log("Using callback URL: $callbackUrl");

    $data = [
        'id' => $merchantReference,
        'currency' => 'UGX',
        'amount' => REGISTRATION_FEE,
        'description' => 'Property Owner Registration Fee',
        'callback_url' => $callbackUrl,
        'notification_id' => PESAPAL_NOTIFICATION_ID,
        'billing_address' => [
            'email_address' => $userData['email'],
            'phone_number' => $userData['phone'],
            'country_code' => 'UG',
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'line_1' => $userData['address']
        ]
    ];

    error_log("Submitting order request to: " . PESAPAL_SUBMIT_ORDER_URL);
    error_log("Order request data: " . json_encode($data));

    $ch = curl_init(PESAPAL_SUBMIT_ORDER_URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    error_log("Order request response code: $httpCode");
    error_log("Order request response: $response");

    if ($ch === false || curl_errno($ch)) {
        error_log("Curl error: " . curl_error($ch));
    }

    curl_close($ch);

    $result = json_decode($response, true);
    error_log("Parsed order response: " . print_r($result, true));

    // Store the order data in session for later use
    $_SESSION['pesapal_order'] = [
        'merchant_reference' => $merchantReference,
        'user_data' => $userData
    ];

    if (isset($result['order_tracking_id'])) {
        $_SESSION['pesapal_order']['order_tracking_id'] = $result['order_tracking_id'];
    }

    return $result;
}

/**
 * Get transaction status from Pesapal
 *
 * @param string $orderTrackingId The order tracking ID from Pesapal
 * @param string $token Pesapal authentication token
 * @return array Transaction status details
 */
function getTransactionStatus($orderTrackingId, $token) {
    $url = PESAPAL_TRANSACTION_STATUS_URL . '?orderTrackingId=' . $orderTrackingId;

    error_log("Getting transaction status from URL: $url");

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    error_log("Transaction status response code: $httpCode");
    error_log("Transaction status response: $response");

    if ($ch === false || curl_errno($ch)) {
        error_log("Curl error: " . curl_error($ch));
    }

    curl_close($ch);

    if ($httpCode == 200) {
        $result = json_decode($response, true);
        error_log("Parsed transaction status: " . print_r($result, true));
        return $result;
    }

    error_log("Pesapal Transaction Status Error: " . $response);
    return ['error' => 'Failed to get transaction status', 'http_code' => $httpCode];
}

/**
 * Save payment information to database
 *
 * @param int $ownerId Owner ID
 * @param array $paymentData Payment data from Pesapal
 * @return bool Success status
 */
function savePaymentInfo($ownerId, $paymentData) {
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

    $registration_fee = REGISTRATION_FEE;
    $currency = 'UGX';

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
        $registration_fee,
        $currency,
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
    global $conn;

    error_log("Starting owner registration with data: " . print_r($userData, true));

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
    $query = "INSERT INTO property_owner (
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
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'paid')";

    error_log("Preparing SQL query: $query");

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }

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

    error_log("Executing SQL statement");

    if ($stmt->execute()) {
        $ownerId = $conn->insert_id;
        error_log("Owner registered successfully with ID: $ownerId");
        return $ownerId;
    }

    error_log("Error registering owner: " . $stmt->error);
    return false;
}

