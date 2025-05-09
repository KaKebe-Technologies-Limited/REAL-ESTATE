<?php
header('Content-Type: application/json'); // Ensure JSON response
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php'; // Include database configuration
require_once 'validate_subscription.php'; // Include subscription validation

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
        exit;
    }

    // Check if username and password are set
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        echo json_encode(['success' => false, 'message' => 'Username or password not provided']);
        exit;
    }

    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM property_owner WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Check subscription status
            $subscription_valid = false;
            $subscription_expired = false;

            // Check if subscription_end_date exists and is in the future
            if (isset($user['subscription_end_date']) && !empty($user['subscription_end_date'])) {
                if (strtotime($user['subscription_end_date']) > time()) {
                    $subscription_valid = true;
                } else {
                    $subscription_expired = true;
                    // Update subscription status to expired
                    $update_stmt = $conn->prepare("UPDATE property_owner SET subscription_status = 'expired' WHERE owner_id = ?");
                    $update_stmt->bind_param("i", $user['owner_id']);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
            }

            session_start();
            $_SESSION['user_id'] = $user['owner_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = 'owner';

            // Add subscription info to session
            $_SESSION['subscription_status'] = $user['subscription_status'] ?? 'pending';
            $_SESSION['subscription_end_date'] = $user['subscription_end_date'] ?? null;

            if ($subscription_expired) {
                // Return success but with subscription expired flag
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'subscription_expired' => true,
                    'redirect' => 'renew_subscription.php'
                ]);
            } else {
                echo json_encode(['success' => true, 'message' => 'Login successful']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid password']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>