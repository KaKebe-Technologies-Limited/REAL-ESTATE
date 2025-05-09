<?php
// Pesapal API Configuration

// Set to true for sandbox/testing, false for production
define('PESAPAL_IS_DEMO', false);

// Pesapal API credentials - Replace with your actual credentials
define('PESAPAL_CONSUMER_KEY', 'HZzvECTOC3KlHU+FJ9AyWB9uLslsKz00');
define('PESAPAL_CONSUMER_SECRET', 'OAHwYOSP8gFJKJr83CaZdup8ss4=');

// Registration fee amount in UGX
define('REGISTRATION_FEE', 500); // 500 UGX

// API URLs
if (PESAPAL_IS_DEMO) {
    // Sandbox URLs
    define('PESAPAL_AUTH_URL', 'https://cybqa.pesapal.com/pesapalv3/api/Auth/RequestToken');
    define('PESAPAL_SUBMIT_ORDER_URL', 'https://cybqa.pesapal.com/pesapalv3/api/Transactions/SubmitOrderRequest');
    define('PESAPAL_TRANSACTION_STATUS_URL', 'https://cybqa.pesapal.com/pesapalv3/api/Transactions/GetTransactionStatus');
    define('PESAPAL_REGISTER_IPN_URL', 'https://cybqa.pesapal.com/pesapalv3/api/URLSetup/RegisterIPN');
} else {
    // Production URLs
    define('PESAPAL_AUTH_URL', 'https://pay.pesapal.com/v3/api/Auth/RequestToken');
    define('PESAPAL_SUBMIT_ORDER_URL', 'https://pay.pesapal.com/v3/api/Transactions/SubmitOrderRequest');
    define('PESAPAL_TRANSACTION_STATUS_URL', 'https://pay.pesapal.com/v3/api/Transactions/GetTransactionStatus');
    define('PESAPAL_REGISTER_IPN_URL', 'https://pay.pesapal.com/v3/api/URLSetup/RegisterIPN');
}

// IPN Notification ID - This should be registered once and stored
define('PESAPAL_NOTIFICATION_ID', '17a2fbfc-f44d-427c-b3a3-dbd7f8956042');

// Base URL of your website (no trailing slash)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . $host;

// For local development, just use the host with REAL-ESTATE directory
if ($host === 'localhost' || strpos($host, '127.0.0.1') !== false) {
    $baseUrl = $protocol . $host . '/REAL-ESTATE';
} else {
    // If running in a subdirectory, add it to the base URL
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    if ($scriptDir !== '/' && $scriptDir !== '\\') {
        // Only add the directory if it's not the root
        if ($scriptDir !== '/' && $scriptDir !== '\\' && $scriptDir !== '') {
            $baseUrl .= $scriptDir;
        }
    }
}

define('BASE_URL', $baseUrl);
error_log("Base URL set to: " . BASE_URL);
