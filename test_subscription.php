<?php
session_start();
require_once 'config.php';
require_once 'validate_subscription.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to test subscription functionality.";
    exit;
}

// Get user ID and type
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'] ?? 'unknown';

// Create database connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user details
if ($user_type === 'owner') {
    $query = "SELECT * FROM property_owner WHERE owner_id = ?";
} else {
    echo "This test is only for owner accounts.";
    exit;
}

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

// Check if subscription fields exist
$subscription_fields_exist = isset($user_data['subscription_status']);

// Display user information
echo "<h1>Subscription Test</h1>";
echo "<h2>User Information</h2>";
echo "<p><strong>User ID:</strong> " . $user_id . "</p>";
echo "<p><strong>User Type:</strong> " . $user_type . "</p>";
echo "<p><strong>Username:</strong> " . ($user_data['username'] ?? 'N/A') . "</p>";
echo "<p><strong>Name:</strong> " . ($user_data['first_name'] ?? 'N/A') . " " . ($user_data['last_name'] ?? 'N/A') . "</p>";

// Display subscription information if fields exist
if ($subscription_fields_exist) {
    echo "<h2>Subscription Information</h2>";
    echo "<p><strong>Status:</strong> " . ($user_data['subscription_status'] ?? 'N/A') . "</p>";
    echo "<p><strong>Start Date:</strong> " . ($user_data['subscription_start_date'] ?? 'N/A') . "</p>";
    echo "<p><strong>End Date:</strong> " . ($user_data['subscription_end_date'] ?? 'N/A') . "</p>";
    echo "<p><strong>Last Renewal:</strong> " . ($user_data['last_renewal_date'] ?? 'N/A') . "</p>";
    
    // Check if subscription is valid
    $is_valid = isSubscriptionValid($user_id);
    echo "<p><strong>Is Valid:</strong> " . ($is_valid ? 'Yes' : 'No') . "</p>";
    
    // Calculate days remaining
    if (isset($user_data['subscription_end_date']) && !empty($user_data['subscription_end_date'])) {
        $end_date = new DateTime($user_data['subscription_end_date']);
        $today = new DateTime();
        $interval = $today->diff($end_date);
        $days_remaining = $interval->invert ? -$interval->days : $interval->days;
        echo "<p><strong>Days Remaining:</strong> " . $days_remaining . "</p>";
    } else {
        echo "<p><strong>Days Remaining:</strong> N/A</p>";
    }
} else {
    echo "<h2>Subscription Fields Not Found</h2>";
    echo "<p>The subscription fields do not exist in the database. Please run the create_subscription_tables.php script first.</p>";
}

// Display test actions
echo "<h2>Test Actions</h2>";
echo "<ul>";
echo "<li><a href='create_subscription_tables.php'>Create Subscription Tables</a></li>";
echo "<li><a href='renew_subscription.php'>Go to Subscription Renewal Page</a></li>";
echo "<li><a href='test_payment_processing.php'>Test Payment Processing</a></li>";
echo "<li><a href='ownerDashboard.php'>Return to Dashboard</a></li>";
echo "</ul>";

// Close connection
$conn->close();
?>
