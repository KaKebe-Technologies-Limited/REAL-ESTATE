<?php
session_start();
require_once 'pesapal_functions.php';
require_once 'log_activity.php';
ini_set('display_errors', 0); // Disable error display for production
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Immediately redirect to a temporary loading page if this is the first load
// This avoids showing any PHP code and ensures we process in the background
if (!isset($_GET['processing'])) {
    // Redirect to a simple loading page first
    header('Location: payment_processing.php?redirect_back=1');
    exit;
}

// Get parameters from the callback URL
$orderTrackingId = $_GET['OrderTrackingId'] ?? '';
$merchantReference = $_GET['OrderMerchantReference'] ?? '';
$notificationType = $_GET['OrderNotificationType'] ?? '';

// Debug log
error_log("Callback received: OrderTrackingId=$orderTrackingId, MerchantReference=$merchantReference, NotificationType=$notificationType");
error_log("GET params: " . print_r($_GET, true));
error_log("Session data: " . print_r($_SESSION, true));

// If we're processing but don't have the required parameters, try to get them from the session
if (isset($_GET['processing']) && (empty($orderTrackingId) || empty($merchantReference))) {
    if (isset($_SESSION['pesapal_order']) && isset($_SESSION['pesapal_order']['order_tracking_id'])) {
        $orderTrackingId = $_SESSION['pesapal_order']['order_tracking_id'];
        $merchantReference = $_SESSION['pesapal_order']['merchant_reference'] ?? '';
        error_log("Retrieved tracking ID from session: $orderTrackingId");
    }
}

// Validate that we have the required parameters
if (empty($orderTrackingId) || empty($merchantReference)) {
    error_log("Missing required parameters - redirecting to error page");
    header('Location: register.html?error=invalid_callback');
    exit;
}

// Check if this is a notification rather than a callback
if ($notificationType === 'IPNCHANGE') {
    // This is an IPN notification, not a callback
    // We should handle this in owner_payment_ipn.php
    error_log("This is an IPN notification, not a callback. Redirecting to IPN handler.");
    header('Location: owner_payment_ipn.php?' . http_build_query($_GET));
    exit;
}

// Get Pesapal token
try {
    $token = getPesapalToken();
    if (!$token) {
        error_log("Failed to get Pesapal token");
        header('Location: register.html?error=payment_verification_failed');
        exit;
    }

    // Get transaction status
    $transactionStatus = getTransactionStatus($orderTrackingId, $token);
} catch (Exception $e) {
    error_log("Exception during payment verification: " . $e->getMessage());
    header('Location: payment_error.php?error=api_error');
    exit;
}

// Check if we have user data in session or in pesapal_order
$userData = null;

try {
    if (isset($_SESSION['temp_owner_data'])) {
        $userData = $_SESSION['temp_owner_data'];
        error_log("Found user data in temp_owner_data session variable");
    } elseif (isset($_SESSION['pesapal_order']) && isset($_SESSION['pesapal_order']['user_data'])) {
        $userData = $_SESSION['pesapal_order']['user_data'];
        error_log("Found user data in pesapal_order session variable");
    } else {
        error_log("No user data found in session");
        header('Location: register.html?error=session_expired');
        exit;
    }

    // Validate that we have all required user data
    $requiredFields = ['first_name', 'last_name', 'email', 'phone', 'username', 'password'];
    foreach ($requiredFields as $field) {
        if (!isset($userData[$field]) || empty($userData[$field])) {
            error_log("Missing required user data field: $field");
            header('Location: register.html?error=incomplete_data');
            exit;
        }
    }

    error_log("User data: " . print_r($userData, true));
} catch (Exception $e) {
    error_log("Exception during user data processing: " . $e->getMessage());
    header('Location: register.html?error=data_processing_error');
    exit;
}

// Process based on payment status
try {
    if (isset($transactionStatus['payment_status_description'])) {
        $paymentStatus = $transactionStatus['payment_status_description'];
        error_log("Payment status: $paymentStatus");

        if ($paymentStatus === 'Completed') {
            // Payment successful, register the user
            $ownerId = completeOwnerRegistration($userData);

            if ($ownerId) {
                // Save payment information
                $transactionStatus['order_tracking_id'] = $orderTrackingId;
                $transactionStatus['merchant_reference'] = $merchantReference;
                savePaymentInfo($ownerId, $transactionStatus);

                // Log the activity
                $owner_name = "{$userData['first_name']} {$userData['last_name']}";
                logActivity('registration', 'New Owner Registered', "Owner $owner_name has registered and paid registration fee", 'fas fa-user-plus', 'bg-success');

                // Clear session data
                unset($_SESSION['temp_owner_data']);
                unset($_SESSION['pesapal_order']);

                // Redirect to success page
                header('Location: payment_success.php');
                exit;
            } else {
                error_log("Owner registration failed");
                header('Location: registration_failed.php');
                exit;
            }
        } else if ($paymentStatus === 'Failed') {
            // Payment failed
            error_log("Payment failed");
            header('Location: payment_failed.php');
            exit;
        } else {
            // Payment pending or other status
            error_log("Payment status: $paymentStatus");
            header('Location: payment_pending.php?status=' . urlencode($paymentStatus));
            exit;
        }
    } else {
        // Could not determine payment status
        error_log("Could not determine payment status from response: " . print_r($transactionStatus, true));
        header('Location: payment_error.php?error=unknown_status');
        exit;
    }
} catch (Exception $e) {
    error_log("Exception during payment processing: " . $e->getMessage());
    header('Location: payment_error.php?error=processing_error');
    exit;
}

