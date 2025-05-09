<?php
session_start();
require_once 'config.php';
require_once 'validate_subscription.php';
require_once 'pesapal_functions.php';
require_once 'log_activity.php';
ini_set('display_errors', 0); // Disable error display for production
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Immediately redirect to a temporary loading page if this is the first load
// This avoids showing any PHP code and ensures we process in the background 
if (!isset($_GET['processing'])) {
    // Redirect to a simple loading page first
    header('Location: payment_processing.php?redirect_back=1&subscription=1');
    exit;
}

// Get parameters from the callback URL
$orderTrackingId = $_GET['OrderTrackingId'] ?? '';
$merchantReference = $_GET['OrderMerchantReference'] ?? '';

// If we're processing but don't have the required parameters, try to get them from the session
if (isset($_GET['processing']) && (empty($orderTrackingId) || empty($merchantReference))) {
    if (isset($_SESSION['pesapal_subscription_order']) && isset($_SESSION['pesapal_subscription_order']['order_tracking_id'])) {
        $orderTrackingId = $_SESSION['pesapal_subscription_order']['order_tracking_id'];
        $merchantReference = $_SESSION['pesapal_subscription_order']['merchant_reference'] ?? '';
        error_log("Retrieved subscription tracking ID from session: $orderTrackingId");
    }
}

// Validate that we have the required parameters
if (empty($orderTrackingId) || empty($merchantReference)) {
    error_log("Missing required parameters - redirecting to error page");
    header('Location: renew_subscription.php?error=invalid_callback');
    exit;
}

// Get Pesapal token
try {
    $token = getPesapalToken();
    if (!$token) {
        error_log("Failed to get Pesapal token");
        header('Location: renew_subscription.php?error=payment_verification_failed');
        exit;
    }

    // Get transaction status
    $transactionStatus = getTransactionStatus($orderTrackingId, $token);
} catch (Exception $e) {
    error_log("Exception during payment verification: " . $e->getMessage());
    header('Location: payment_error.php?error=api_error&subscription=1');
    exit;
}

// Check if we have subscription data in session
$subscriptionData = null;

try {
    if (isset($_SESSION['subscription_renewal'])) {
        $subscriptionData = $_SESSION['subscription_renewal'];
        error_log("Found subscription data in session variable");
    } else {
        error_log("No subscription data found in session");
        header('Location: renew_subscription.php?error=session_expired');
        exit;
    }

    // Check if payment was successful
    if (isset($transactionStatus['payment_status_description']) && ($transactionStatus['payment_status_description'] === 'Completed' || $transactionStatus['payment_status_description'] === 'Completed')) {
        error_log("Payment successful: " . json_encode($transactionStatus));
        
        // Get owner ID from session
        $owner_id = $subscriptionData['owner_id'];
        
        // Renew subscription
        $months = $subscriptionData['months'];
        $transaction_id = $transactionStatus['payment_method_reference'] ?? $orderTrackingId;
        $payment_method = $transactionStatus['payment_method'] ?? $subscriptionData['payment_method'];
        
        $renewal_result = renewSubscription($owner_id, $months, $transaction_id, $payment_method);
        
        if ($renewal_result) {
            // Save payment information
            saveSubscriptionPaymentInfo($owner_id, $transactionStatus, $subscriptionData);
            
            // Clear session data
            unset($_SESSION['subscription_renewal']);
            unset($_SESSION['pesapal_subscription_order']);
            
            // Redirect to success page
            $redirect_url = isset($_SESSION['redirect_after_renewal']) ? $_SESSION['redirect_after_renewal'] : 'ownerDashboard.php';
            unset($_SESSION['redirect_after_renewal']);
            
            header('Location: subscription_success.php?redirect=' . urlencode($redirect_url));
            exit;
        } else {
            error_log("Subscription renewal failed");
            header('Location: subscription_failed.php');
            exit;
        }
    } else {
        // Payment failed or is pending
        $status = $transactionStatus['payment_status_description'] ?? 'UNKNOWN';
        error_log("Payment not completed. Status: $status");
        header('Location: subscription_failed.php?status=' . urlencode($status));
        exit;
    }
} catch (Exception $e) {
    error_log("Exception during subscription renewal: " . $e->getMessage());
    header('Location: subscription_failed.php?error=processing_error');
    exit;
}

/**
 * Save subscription payment information
 * 
 * @param int $owner_id Owner ID
 * @param array $transactionStatus Transaction status from payment gateway
 * @param array $subscriptionData Subscription data from session
 * @return bool True if successful, false otherwise
 */
function saveSubscriptionPaymentInfo($owner_id, $transactionStatus, $subscriptionData) {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        return false;
    }
    
    // Extract payment details
    $amount = $subscriptionData['price'];
    $currency = 'UGX';
    $transaction_id = $transactionStatus['payment_method_reference'] ?? '';
    $order_tracking_id = $transactionStatus['order_tracking_id'] ?? '';
    $merchant_reference = $transactionStatus['merchant_reference'] ?? '';
    $payment_method = $transactionStatus['payment_method'] ?? '';
    $status = strtolower($transactionStatus['status'] ?? 'pending');
    $payment_status = ($status === 'completed') ? 'completed' : 'pending';
    $months = $subscriptionData['months'];
    
    // Get subscription dates from owner record
    $query = "SELECT subscription_start_date, subscription_end_date FROM property_owner WHERE owner_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $owner = $result->fetch_assoc();
    $stmt->close();
    
    $subscription_start_date = $owner['subscription_start_date'] ?? date('Y-m-d H:i:s');
    $subscription_end_date = $owner['subscription_end_date'] ?? date('Y-m-d H:i:s', strtotime("+$months months"));
    
    // Insert payment record
    $query = "INSERT INTO owner_subscriptions (
                owner_id,
                amount,
                currency,
                transaction_id,
                order_tracking_id,
                merchant_reference,
                payment_method,
                payment_status,
                subscription_months,
                subscription_start_date,
                subscription_end_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        'idssssssiss',
        $owner_id,
        $amount,
        $currency,
        $transaction_id,
        $order_tracking_id,
        $merchant_reference,
        $payment_method,
        $payment_status,
        $months,
        $subscription_start_date,
        $subscription_end_date
    );
    
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $result;
}
?>
