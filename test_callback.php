<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Test Pesapal Callback</h1>";

// Check if we have test user data
if (!isset($_SESSION['temp_owner_data']) && !isset($_SESSION['pesapal_order'])) {
    echo "<p>No test user data found. Please <a href='test_session.php'>set test user data</a> first.</p>";
    exit;
}

// Display current session data
echo "<h2>Current Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Create a form to simulate a callback
echo "<h2>Simulate Pesapal Callback</h2>";
echo "<form method='post'>";
echo "<input type='hidden' name='action' value='simulate_callback'>";

// Get merchant reference and order tracking ID from session if available
$merchantReference = '';
$orderTrackingId = '';

if (isset($_SESSION['pesapal_order'])) {
    $merchantReference = $_SESSION['pesapal_order']['merchant_reference'];
    $orderTrackingId = $_SESSION['pesapal_order']['order_tracking_id'];
}

echo "<div>";
echo "<label for='merchant_reference'>Merchant Reference:</label>";
echo "<input type='text' id='merchant_reference' name='merchant_reference' value='$merchantReference' required>";
echo "</div>";

echo "<div>";
echo "<label for='order_tracking_id'>Order Tracking ID:</label>";
echo "<input type='text' id='order_tracking_id' name='order_tracking_id' value='$orderTrackingId' required>";
echo "</div>";

echo "<div>";
echo "<label for='notification_type'>Notification Type:</label>";
echo "<select id='notification_type' name='notification_type'>";
echo "<option value='CALLBACKURL'>CALLBACKURL</option>";
echo "<option value='IPNCHANGE'>IPNCHANGE</option>";
echo "</select>";
echo "</div>";

echo "<div>";
echo "<label for='payment_status'>Payment Status:</label>";
echo "<select id='payment_status' name='payment_status'>";
echo "<option value='Completed'>Completed</option>";
echo "<option value='Failed'>Failed</option>";
echo "<option value='Pending'>Pending</option>";
echo "</select>";
echo "</div>";

echo "<button type='submit'>Simulate Callback</button>";
echo "</form>";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'simulate_callback') {
    $merchantReference = $_POST['merchant_reference'];
    $orderTrackingId = $_POST['order_tracking_id'];
    $notificationType = $_POST['notification_type'];
    $paymentStatus = $_POST['payment_status'];
    
    // Build the callback URL
    $callbackUrl = '';
    
    if ($notificationType === 'CALLBACKURL') {
        $callbackUrl = "owner_payment_callback.php?OrderTrackingId=$orderTrackingId&OrderMerchantReference=$merchantReference&OrderNotificationType=$notificationType";
    } else {
        $callbackUrl = "owner_payment_ipn.php?OrderTrackingId=$orderTrackingId&OrderMerchantReference=$merchantReference&OrderNotificationType=$notificationType";
    }
    
    // Mock the transaction status response
    $_SESSION['mock_transaction_status'] = [
        'payment_status_description' => $paymentStatus,
        'payment_method' => 'Credit Card',
        'confirmation_code' => 'MOCK-' . time() . '-' . rand(1000, 9999)
    ];
    
    echo "<h2>Callback URL</h2>";
    echo "<p><a href='$callbackUrl' target='_blank'>$callbackUrl</a></p>";
    
    echo "<p>Click the link above to simulate the callback. This will open in a new tab.</p>";
}

// Create a function to mock the Pesapal API responses
echo "<script>
function mockPesapalApi() {
    // Override the fetch function to intercept Pesapal API calls
    const originalFetch = window.fetch;
    window.fetch = function(url, options) {
        if (url.includes('pesapal')) {
            console.log('Intercepted Pesapal API call:', url, options);
            
            // Mock a successful response
            return Promise.resolve({
                ok: true,
                json: () => Promise.resolve({
                    success: true,
                    redirect_url: 'test_callback.php',
                    order_tracking_id: 'MOCK-TRACK-ID',
                    merchant_reference: 'MOCK-REF-ID'
                })
            });
        }
        
        // Pass through all other fetch calls
        return originalFetch(url, options);
    };
    
    console.log('Pesapal API mocking enabled');
}

// Call the function to enable mocking
mockPesapalApi();
</script>";
?>
