<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Session Test</h1>";

// Display all session data
echo "<h2>Current Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Set some test data
if (!isset($_SESSION['test_data'])) {
    $_SESSION['test_data'] = [
        'timestamp' => time(),
        'random' => rand(1000, 9999)
    ];
    echo "<p>Test data has been set. Refresh to see if it persists.</p>";
} else {
    echo "<p>Test data was set at: " . date('Y-m-d H:i:s', $_SESSION['test_data']['timestamp']) . "</p>";
    echo "<p>Random value: " . $_SESSION['test_data']['random'] . "</p>";
}

// Check PHP session configuration
echo "<h2>PHP Session Configuration</h2>";
echo "<table border='1'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>session.save_path</td><td>" . ini_get('session.save_path') . "</td></tr>";
echo "<tr><td>session.name</td><td>" . ini_get('session.name') . "</td></tr>";
echo "<tr><td>session.save_handler</td><td>" . ini_get('session.save_handler') . "</td></tr>";
echo "<tr><td>session.gc_maxlifetime</td><td>" . ini_get('session.gc_maxlifetime') . " seconds</td></tr>";
echo "<tr><td>session.cookie_lifetime</td><td>" . ini_get('session.cookie_lifetime') . " seconds</td></tr>";
echo "<tr><td>session.cookie_path</td><td>" . ini_get('session.cookie_path') . "</td></tr>";
echo "<tr><td>session.cookie_domain</td><td>" . ini_get('session.cookie_domain') . "</td></tr>";
echo "<tr><td>session.cookie_secure</td><td>" . ini_get('session.cookie_secure') . "</td></tr>";
echo "<tr><td>session.cookie_httponly</td><td>" . ini_get('session.cookie_httponly') . "</td></tr>";
echo "<tr><td>session.use_cookies</td><td>" . ini_get('session.use_cookies') . "</td></tr>";
echo "<tr><td>session.use_only_cookies</td><td>" . ini_get('session.use_only_cookies') . "</td></tr>";
echo "</table>";

// Display session ID
echo "<h2>Session ID</h2>";
echo "<p>Current Session ID: " . session_id() . "</p>";

// Display cookie information
echo "<h2>Cookie Information</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

// Create a form to set test user data
echo "<h2>Set Test User Data</h2>";
echo "<form method='post'>";
echo "<input type='hidden' name='action' value='set_test_user'>";
echo "<button type='submit'>Set Test User Data</button>";
echo "</form>";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'set_test_user') {
    $_SESSION['temp_owner_data'] = [
        'username' => 'testuser',
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'phone' => '+256700000000',
        'id_type' => 'National ID',
        'id_num' => '12345678',
        'address' => 'Test Address'
    ];
    
    $_SESSION['pesapal_order'] = [
        'merchant_reference' => 'REG-' . time() . '-' . rand(1000, 9999),
        'order_tracking_id' => 'TRACK-' . time() . '-' . rand(1000, 9999),
        'user_data' => $_SESSION['temp_owner_data']
    ];
    
    echo "<p>Test user data has been set. <a href='test_session.php'>Refresh</a> to see the data.</p>";
}

// Create a form to clear session data
echo "<h2>Clear Session Data</h2>";
echo "<form method='post'>";
echo "<input type='hidden' name='action' value='clear_session'>";
echo "<button type='submit'>Clear Session Data</button>";
echo "</form>";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear_session') {
    session_unset();
    session_destroy();
    echo "<p>Session data has been cleared. <a href='test_session.php'>Refresh</a> to start a new session.</p>";
}
?>
