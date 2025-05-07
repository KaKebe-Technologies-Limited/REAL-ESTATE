<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include mock Pesapal API functions
require_once 'mock_pesapal_api.php';
require_once 'log_activity.php';

echo "<h1>Test Registration Flow</h1>";

// Function to generate a random user
function generateRandomUser() {
    $randomId = rand(1000, 9999);
    return [
        'username' => 'user' . $randomId,
        'first_name' => 'Test',
        'last_name' => 'User' . $randomId,
        'email' => 'test' . $randomId . '@example.com',
        'password' => 'password123',
        'phone' => '+256700' . $randomId,
        'id_type' => 'National ID',
        'id_num' => '12345' . $randomId,
        'address' => 'Test Address ' . $randomId
    ];
}

// Display current session data
echo "<h2>Current Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Create a form to simulate registration
echo "<h2>Simulate Registration</h2>";
echo "<form method='post'>";
echo "<input type='hidden' name='action' value='simulate_registration'>";

// Generate a random user if not already in session
if (!isset($_SESSION['temp_owner_data'])) {
    $randomUser = generateRandomUser();
} else {
    $randomUser = $_SESSION['temp_owner_data'];
}

echo "<div>";
echo "<label for='username'>Username:</label>";
echo "<input type='text' id='username' name='username' value='" . $randomUser['username'] . "' required>";
echo "</div>";

echo "<div>";
echo "<label for='first_name'>First Name:</label>";
echo "<input type='text' id='first_name' name='first_name' value='" . $randomUser['first_name'] . "' required>";
echo "</div>";

echo "<div>";
echo "<label for='last_name'>Last Name:</label>";
echo "<input type='text' id='last_name' name='last_name' value='" . $randomUser['last_name'] . "' required>";
echo "</div>";

echo "<div>";
echo "<label for='email'>Email:</label>";
echo "<input type='email' id='email' name='email' value='" . $randomUser['email'] . "' required>";
echo "</div>";

echo "<div>";
echo "<label for='password'>Password:</label>";
echo "<input type='password' id='password' name='password' value='" . $randomUser['password'] . "' required>";
echo "</div>";

echo "<div>";
echo "<label for='phone'>Phone:</label>";
echo "<input type='text' id='phone' name='phone' value='" . $randomUser['phone'] . "' required>";
echo "</div>";

echo "<div>";
echo "<label for='id_type'>ID Type:</label>";
echo "<select id='id_type' name='id_type'>";
echo "<option value='National ID'" . ($randomUser['id_type'] === 'National ID' ? ' selected' : '') . ">National ID</option>";
echo "<option value='Passport'" . ($randomUser['id_type'] === 'Passport' ? ' selected' : '') . ">Passport</option>";
echo "<option value='Driving License'" . ($randomUser['id_type'] === 'Driving License' ? ' selected' : '') . ">Driving License</option>";
echo "</select>";
echo "</div>";

echo "<div>";
echo "<label for='id_num'>ID Number:</label>";
echo "<input type='text' id='id_num' name='id_num' value='" . $randomUser['id_num'] . "' required>";
echo "</div>";

echo "<div>";
echo "<label for='address'>Address:</label>";
echo "<input type='text' id='address' name='address' value='" . $randomUser['address'] . "' required>";
echo "</div>";

echo "<button type='submit'>Simulate Registration</button>";
echo "</form>";

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'simulate_registration') {
    // Get form data
    $userData = [
        'username' => $_POST['username'],
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'password' => $_POST['password'],
        'phone' => $_POST['phone'],
        'id_type' => $_POST['id_type'],
        'id_num' => $_POST['id_num'],
        'address' => $_POST['address']
    ];
    
    // Store user data in session
    registerTempUser($userData);
    
    // Get Pesapal token
    $token = getPesapalToken();
    
    // Submit order request to Pesapal
    $orderResponse = submitOrderRequest($userData, $token);
    
    echo "<h2>Registration Processed</h2>";
    echo "<p>User data has been stored in session.</p>";
    
    echo "<h3>Order Response</h3>";
    echo "<pre>";
    print_r($orderResponse);
    echo "</pre>";
    
    echo "<h3>Next Steps</h3>";
    echo "<p>1. <a href='test_callback.php'>Simulate Payment Callback</a></p>";
    echo "<p>2. <a href='test_registration_flow.php'>Refresh this page</a> to see updated session data</p>";
}

// Create a form to simulate payment completion
echo "<h2>Simulate Payment Completion</h2>";
echo "<form method='post'>";
echo "<input type='hidden' name='action' value='simulate_payment'>";

echo "<div>";
echo "<label for='payment_status'>Payment Status:</label>";
echo "<select id='payment_status' name='payment_status'>";
echo "<option value='Completed'>Completed</option>";
echo "<option value='Failed'>Failed</option>";
echo "<option value='Pending'>Pending</option>";
echo "</select>";
echo "</div>";

echo "<button type='submit'>Simulate Payment</button>";
echo "</form>";

// Process payment form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'simulate_payment') {
    // Check if we have user data in session
    if (!isset($_SESSION['temp_owner_data']) && !isset($_SESSION['pesapal_order'])) {
        echo "<p>No user data found in session. Please simulate registration first.</p>";
        exit;
    }
    
    // Get user data from session
    $userData = null;
    if (isset($_SESSION['temp_owner_data'])) {
        $userData = $_SESSION['temp_owner_data'];
    } elseif (isset($_SESSION['pesapal_order']) && isset($_SESSION['pesapal_order']['user_data'])) {
        $userData = $_SESSION['pesapal_order']['user_data'];
    }
    
    // Set mock transaction status
    $_SESSION['mock_transaction_status'] = [
        'payment_status_description' => $_POST['payment_status'],
        'payment_method' => 'Credit Card',
        'confirmation_code' => 'MOCK-' . time() . '-' . rand(1000, 9999)
    ];
    
    // Get Pesapal token
    $token = getPesapalToken();
    
    // Get order tracking ID and merchant reference
    $orderTrackingId = '';
    $merchantReference = '';
    if (isset($_SESSION['pesapal_order'])) {
        $orderTrackingId = $_SESSION['pesapal_order']['order_tracking_id'];
        $merchantReference = $_SESSION['pesapal_order']['merchant_reference'];
    } else {
        $orderTrackingId = 'TRACK-' . time() . '-' . rand(1000, 9999);
        $merchantReference = 'REG-' . time() . '-' . rand(1000, 9999);
    }
    
    // Get transaction status
    $transactionStatus = getTransactionStatus($orderTrackingId, $token);
    
    echo "<h2>Payment Processed</h2>";
    echo "<p>Payment status: " . $transactionStatus['payment_status_description'] . "</p>";
    
    // Process based on payment status
    if ($transactionStatus['payment_status_description'] === 'Completed') {
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
            
            echo "<p>User registered successfully with ID: $ownerId</p>";
            
            // Clear session data
            unset($_SESSION['temp_owner_data']);
            unset($_SESSION['pesapal_order']);
            unset($_SESSION['mock_transaction_status']);
            
            echo "<p>Session data cleared.</p>";
        } else {
            echo "<p>Registration failed.</p>";
        }
    } else {
        echo "<p>Payment not completed. User not registered.</p>";
    }
    
    echo "<h3>Next Steps</h3>";
    echo "<p><a href='test_registration_flow.php'>Refresh this page</a> to start over</p>";
}

// Create a form to clear session data
echo "<h2>Clear Session Data</h2>";
echo "<form method='post'>";
echo "<input type='hidden' name='action' value='clear_session'>";
echo "<button type='submit'>Clear Session Data</button>";
echo "</form>";

// Process clear session form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear_session') {
    session_unset();
    session_destroy();
    echo "<p>Session data has been cleared. <a href='test_registration_flow.php'>Refresh</a> to start a new session.</p>";
}
?>
