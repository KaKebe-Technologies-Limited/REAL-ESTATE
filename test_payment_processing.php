<?php
// This is a test script to check if payment_processing.php is working correctly

// Start session
session_start();

// Set up test data
$_SESSION['subscription_renewal'] = [
    'plan' => 'standard',
    'months' => 4,
    'price' => 50000,
    'payment_method' => 'mobile-money',
    'owner_id' => $_SESSION['user_id'] ?? 1
];

$_SESSION['pesapal_subscription_order'] = [
    'order_tracking_id' => 'TEST-' . time(),
    'merchant_reference' => 'TEST-REF-' . time(),
    'user_data' => [
        'username' => 'testuser',
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com'
    ]
];

// Store the current URL in session to redirect back after renewal
$_SESSION['redirect_after_renewal'] = 'ownerDashboard.php';

// Redirect to payment processing page with subscription flag
header('Location: payment_processing.php?redirect_back=1&subscription=1');
exit;
?>
