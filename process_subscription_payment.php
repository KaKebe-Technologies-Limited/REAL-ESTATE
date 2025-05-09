<?php
session_start();
require_once 'config.php';
require_once 'validate_subscription.php';
require_once 'pesapal_functions.php';
require_once 'log_activity.php';

// Check if user is logged in as owner
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'owner') {
    header('Location: login.html');
    exit();
}

// Get owner details
$owner_id = $_SESSION['user_id'];
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$owner_query = "SELECT * FROM property_owner WHERE owner_id = ?";
$stmt = $conn->prepare($owner_query);
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$owner_result = $stmt->get_result();
$owner_data = $owner_result->fetch_assoc();
$stmt->close();

// Set default subscription values
$plan = 'standard';
$months = 4;
$price = REGISTRATION_FEE;
$payment_method = 'pesapal';

// Store subscription details in session
$_SESSION['subscription_renewal'] = [
    'plan' => $plan,
    'months' => $months,
    'price' => $price,
    'payment_method' => $payment_method,
    'owner_id' => $owner_id
];

// Prepare user data for payment
$userData = [
    'username' => $owner_data['username'],
    'first_name' => $owner_data['first_name'],
    'last_name' => $owner_data['last_name'],
    'email' => $owner_data['email'],
    'phone' => $owner_data['phone'],
    'id_type' => $owner_data['id_type'],
    'id_num' => $owner_data['id_num'],
    'address' => $owner_data['address'],
    'subscription_plan' => $plan,
    'subscription_months' => $months,
    'subscription_price' => $price
];

// For testing purposes, let's create a simple test payment flow
if (isset($_GET['test']) && $_GET['test'] == 1) {
    // Simulate a successful payment
    $result = renewSubscription($owner_id, $months, 'TEST-' . time(), 'test_payment');

    if ($result) {
        // Redirect to success page
        $redirect_url = $_SESSION['redirect_after_renewal'] ?? 'ownerDashboard.php';
        header('Location: subscription_success.php?redirect=' . urlencode($redirect_url));
        exit();
    } else {
        // Redirect to error page
        header('Location: subscription_failed.php?error=test_renewal_failed');
        exit();
    }
}

// Get Pesapal token
$token = getPesapalToken();
if (!$token) {
    error_log("Failed to get Pesapal token. Check your API credentials.");
    header('Location: renew_subscription.php?error=payment_gateway_error');
    exit();
}

// Log the token for debugging
error_log("Pesapal token obtained: " . substr($token, 0, 10) . "...");

try {
    // Submit order request to Pesapal
    $orderResponse = submitSubscriptionOrderRequest($userData, $token, $price);

    // Log the full response for debugging
    error_log("Full order response: " . json_encode($orderResponse));

    if (isset($orderResponse['redirect_url'])) {
        // Store order details in session
        $_SESSION['pesapal_subscription_order'] = [
            'order_tracking_id' => $orderResponse['order_tracking_id'] ?? '',
            'merchant_reference' => $orderResponse['merchant_reference'] ?? '',
            'user_data' => $userData
        ];

        // Log the redirect URL
        error_log("Redirecting to Pesapal payment page: " . $orderResponse['redirect_url']);

        // Redirect to Pesapal payment page
        header('Location: ' . $orderResponse['redirect_url']);
        exit();
    } else {
        // Log detailed error
        error_log("Failed to initiate subscription payment. Response: " . json_encode($orderResponse));

        // Check for specific error messages
        $errorMessage = $orderResponse['error'] ?? 'Unknown error';

        // Convert array to string if necessary
        if (is_array($errorMessage)) {
            $errorMessage = json_encode($errorMessage);
        }

        error_log("Error message: $errorMessage");

        // Redirect with more specific error
        header('Location: renew_subscription.php?error=payment_initiation_failed&message=' . urlencode($errorMessage));
        exit();
    }
} catch (Exception $e) {
    // Log exception
    error_log("Exception during payment initiation: " . $e->getMessage());
    header('Location: renew_subscription.php?error=payment_exception&message=' . urlencode($e->getMessage()));
    exit();
}

/**
 * Submit subscription order request to Pesapal
 *
 * @param array $userData User data
 * @param string $token Pesapal authentication token
 * @param int $amount Payment amount
 * @return array Response from Pesapal
 */
function submitSubscriptionOrderRequest($userData, $token, $amount) {
    // Generate a unique merchant reference
    $merchantReference = 'SUB-' . time() . '-' . rand(1000, 9999);

    // Construct the callback URL
    $callbackUrl = BASE_URL . '/subscription_payment_callback.php';
    error_log("Using callback URL: $callbackUrl");

    // Prepare the order data
    $data = [
        'id' => $merchantReference,
        'currency' => 'UGX',
        'amount' => $amount,
        'description' => 'Subscription Renewal - ' . $userData['subscription_plan'] . ' Plan (' . $userData['subscription_months'] . ' months)',
        'callback_url' => $callbackUrl,
        'notification_id' => PESAPAL_NOTIFICATION_ID,
        'billing_address' => [
            'email_address' => $userData['email'],
            'phone_number' => $userData['phone'],
            'country_code' => 'UG',
            'first_name' => $userData['first_name'],
            'middle_name' => '',
            'last_name' => $userData['last_name'],
            'line_1' => $userData['address'],
            'line_2' => '',
            'city' => '',
            'state' => '',
            'postal_code' => '',
            'zip_code' => ''
        ]
    ];

    error_log("Submitting subscription order request to: " . PESAPAL_SUBMIT_ORDER_URL);
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

    if ($httpCode == 200) {
        $responseData = json_decode($response, true);

        // Store order details in session
        $_SESSION['pesapal_order'] = [
            'order_tracking_id' => $responseData['order_tracking_id'] ?? '',
            'merchant_reference' => $merchantReference,
            'user_data' => $userData
        ];

        return $responseData;
    } else {
        return ['error' => "Failed to submit order request. HTTP Code: $httpCode"];
    }
}
