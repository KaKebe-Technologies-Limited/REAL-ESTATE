<?php
session_start();
require_once 'pesapal_functions.php';
require_once 'log_activity.php';
require_once 'config.php';
require_once 'validate_subscription.php'; // Added for subscription functions
// Disable error display for production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL); // Still log errors, but don't display them

// Check if this is a POST or GET request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle POST request
    $postData = file_get_contents('php://input');
    $ipnData = json_decode($postData, true);

    $orderTrackingId = $ipnData['OrderTrackingId'] ?? '';
    $merchantReference = $ipnData['OrderMerchantReference'] ?? '';
    $notificationType = $ipnData['OrderNotificationType'] ?? '';
} else {
    // Handle GET request
    $orderTrackingId = $_GET['OrderTrackingId'] ?? '';
    $merchantReference = $_GET['OrderMerchantReference'] ?? '';
    $notificationType = $_GET['OrderNotificationType'] ?? '';
}

// Log all request data
error_log("IPN Request received - Method: " . $_SERVER['REQUEST_METHOD']);
error_log("GET data: " . print_r($_GET, true));
error_log("POST data: " . print_r($_POST, true));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Raw POST data: " . file_get_contents('php://input'));
}

// Validate that we have the required parameters
if (empty($orderTrackingId) || empty($merchantReference)) {
    error_log("Missing required parameters for IPN request");
    header('HTTP/1.1 400 Bad Request');
    echo json_encode([
        'orderNotificationType' => $notificationType,
        'orderTrackingId' => $orderTrackingId,
        'orderMerchantReference' => $merchantReference,
        'status' => 500,
        'message' => 'Invalid IPN request - missing required parameters'
    ]);
    exit;
}

// Log the IPN request
error_log("IPN Request received: OrderTrackingId=$orderTrackingId, MerchantReference=$merchantReference");

// Get Pesapal token and transaction status
try {
    $token = getPesapalToken();
    if (!$token) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode([
            'orderNotificationType' => $notificationType,
            'orderTrackingId' => $orderTrackingId,
            'orderMerchantReference' => $merchantReference,
            'status' => 500,
            'message' => 'Failed to authenticate with payment gateway'
        ]);
        exit;
    }

    // Get transaction status
    $transactionStatus = getTransactionStatus($orderTrackingId, $token);
} catch (Exception $e) {
    error_log("Exception during payment verification: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'orderNotificationType' => $notificationType,
        'orderTrackingId' => $orderTrackingId,
        'orderMerchantReference' => $merchantReference,
        'status' => 500,
        'message' => 'Error verifying payment: ' . $e->getMessage()
    ]);
    exit;
}

// Connect to database and process payment
try {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Check if this is a registration payment
    if (strpos($merchantReference, 'REG-') === 0) {
        // This is a registration payment

        // Find the owner by merchant reference
        $stmt = $conn->prepare("SELECT owner_id FROM owner_payments WHERE merchant_reference = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("s", $merchantReference);
        $stmt->execute();
        $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Payment record exists, update it
        $payment = $result->fetch_assoc();
        $ownerId = $payment['owner_id'];

        // Determine payment status
        $status = 'pending';
        if (isset($transactionStatus['payment_status_description'])) {
            if ($transactionStatus['payment_status_description'] == 'Completed') {
                $status = 'completed';
            } elseif ($transactionStatus['payment_status_description'] == 'Failed') {
                $status = 'failed';
            }
        }

        // Update payment record
        $updateStmt = $conn->prepare("UPDATE owner_payments SET
            payment_status = ?,
            payment_method = ?,
            transaction_id = ?
            WHERE merchant_reference = ?");

        $paymentMethod = $transactionStatus['payment_method'] ?? '';
        $confirmationCode = $transactionStatus['confirmation_code'] ?? '';

        $updateStmt->bind_param("ssss", $status, $paymentMethod, $confirmationCode, $merchantReference);
        $updateStmt->execute();

        // Update owner payment status
        $ownerStatus = ($status == 'completed') ? 'paid' : 'pending';
        $ownerUpdateStmt = $conn->prepare("UPDATE property_owner SET payment_status = ? WHERE owner_id = ?");
        $ownerUpdateStmt->bind_param("si", $ownerStatus, $ownerId);
        $ownerUpdateStmt->execute();

        // Log activity
        if ($status == 'completed') {
            // Get owner details
            $ownerStmt = $conn->prepare("SELECT first_name, last_name FROM property_owner WHERE owner_id = ?");
            $ownerStmt->bind_param("i", $ownerId);
            $ownerStmt->execute();
            $ownerResult = $ownerStmt->get_result();

            if ($ownerResult->num_rows > 0) {
                $owner = $ownerResult->fetch_assoc();
                $ownerName = "{$owner['first_name']} {$owner['last_name']}";

                // Set up initial subscription (registration fee acts as first subscription payment)
                $subscriptionMonths = 4; // Default subscription period
                $subscriptionResult = renewSubscription($ownerId, $subscriptionMonths, $confirmationCode, $paymentMethod);

                if ($subscriptionResult) {
                    error_log("Initial subscription set up successfully for owner ID: $ownerId");
                    logActivity('payment', 'Registration Fee Paid', "Owner $ownerName has paid registration fee. Initial subscription activated for $subscriptionMonths months.", 'fas fa-money-bill', 'bg-success');
                } else {
                    error_log("Failed to set up initial subscription for owner ID: $ownerId");
                    logActivity('payment', 'Registration Fee Paid', "Owner $ownerName has paid registration fee", 'fas fa-money-bill', 'bg-success');
                }
            }
        }
    } else {
        // Payment record doesn't exist yet, check if we have user data in session
        error_log("IPN received for unknown payment: $merchantReference, checking session data");

        // Check if we have user data in session
        $userData = null;
        if (isset($_SESSION['temp_owner_data'])) {
            $userData = $_SESSION['temp_owner_data'];
            error_log("Found user data in temp_owner_data session variable");
        } elseif (isset($_SESSION['pesapal_order']) && isset($_SESSION['pesapal_order']['user_data'])) {
            $userData = $_SESSION['pesapal_order']['user_data'];
            error_log("Found user data in pesapal_order session variable");
        }

        if ($userData) {
            error_log("User data found in session, attempting to register owner");

            // Determine payment status
            $status = 'pending';
            if (isset($transactionStatus['payment_status_description'])) {
                if ($transactionStatus['payment_status_description'] == 'Completed') {
                    $status = 'completed';

                    // Only register the user if payment is completed
                    $ownerId = completeOwnerRegistration($userData);

                    if ($ownerId) {
                        error_log("Owner registered successfully with ID: $ownerId");

                        // Save payment information
                        savePaymentInfo($ownerId, [
                            'payment_status_description' => $transactionStatus['payment_status_description'],
                            'confirmation_code' => $transactionStatus['confirmation_code'] ?? '',
                            'payment_method' => $transactionStatus['payment_method'] ?? '',
                            'order_tracking_id' => $orderTrackingId,
                            'merchant_reference' => $merchantReference
                        ]);

                        // Get the transaction ID/confirmation code from the payment
                        $confirmationCode = $transactionStatus['confirmation_code'] ?? $orderTrackingId;
                        $paymentMethod = $transactionStatus['payment_method'] ?? 'pesapal';

                        // Set up initial subscription (registration fee acts as first subscription payment)
                        $subscriptionMonths = 4; // Default subscription period
                        $subscriptionResult = renewSubscription($ownerId, $subscriptionMonths, $confirmationCode, $paymentMethod);

                        if ($subscriptionResult) {
                            error_log("Initial subscription set up successfully for owner ID: $ownerId");
                        } else {
                            error_log("Failed to set up initial subscription for owner ID: $ownerId");
                        }

                        // Log the activity
                        $owner_name = "{$userData['first_name']} {$userData['last_name']}";
                        logActivity('registration', 'New Owner Registered', "Owner $owner_name has registered and paid registration fee. Initial subscription activated for $subscriptionMonths months.", 'fas fa-user-plus', 'bg-success');

                        // Clear session data
                        unset($_SESSION['temp_owner_data']);
                        unset($_SESSION['pesapal_order']);
                    } else {
                        error_log("Failed to register owner");
                    }
                }
            }
        } else {
            error_log("No user data found in session");
        }
        }
    }

    // Send success response
    echo json_encode([
        'orderNotificationType' => $notificationType,
        'orderTrackingId' => $orderTrackingId,
        'orderMerchantReference' => $merchantReference,
        'status' => 200,
        'message' => 'IPN processed successfully'
    ]);
} catch (Exception $e) {
    error_log("Exception during IPN processing: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'orderNotificationType' => $notificationType,
        'orderTrackingId' => $orderTrackingId,
        'orderMerchantReference' => $merchantReference,
        'status' => 500,
        'message' => 'Error processing IPN: ' . $e->getMessage()
    ]);
}
