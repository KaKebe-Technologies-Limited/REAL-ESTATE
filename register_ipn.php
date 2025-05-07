<?php
require_once 'pesapal_config.php';
require_once 'pesapal_functions.php';

// Get authentication token first
$token = getPesapalToken();

if (!$token) {
    die("Failed to get authentication token");
}

// Set your IPN callback URL - this is where Pesapal will send notifications
$ipnUrl = BASE_URL . '/owner_payment_ipn.php';

// Prepare the request data
$data = [
    'url' => $ipnUrl,
    'ipn_notification_type' => 'GET' // Can be GET or POST depending on how you want to receive notifications
];

// Make the API request to register IPN
$ch = curl_init(PESAPAL_REGISTER_IPN_URL);
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
curl_close($ch);

// Process the response
if ($httpCode == 200) {
    $result = json_decode($response, true);
    if (isset($result['ipn_id'])) {
        echo "IPN registered successfully. Your notification ID is: " . $result['ipn_id'];
        echo "<p>Update your pesapal_config.php file with this ID.</p>";
    } else {
        echo "Failed to get IPN ID from response: " . $response;
    }
} else {
    echo "Failed to register IPN. Response: " . $response;
}
?>