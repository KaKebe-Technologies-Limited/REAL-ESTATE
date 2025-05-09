<?php
session_start();
require_once 'config.php';
require_once 'validate_subscription.php';

// Check if user is logged in as owner
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'owner') {
    header('Location: login.html');
    exit();
}

// Get owner details
$owner_id = $_SESSION['user_id'];
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$owner_query = "SELECT * FROM property_owner WHERE owner_id = ?";
$stmt = $conn->prepare($owner_query);
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$owner_result = $stmt->get_result();
$owner_data = $owner_result->fetch_assoc();
$stmt->close();

// Set profile picture (check if the field exists)
$profile_pic = 'assets/images/profile.jpg';
if (isset($owner_data['profile_picture']) && !empty($owner_data['profile_picture'])) {
    $profile_pic = $owner_data['profile_picture'];
}

// Get subscription status
$subscription_status = $owner_data['subscription_status'] ?? 'pending';
$subscription_end_date = $owner_data['subscription_end_date'] ?? null;

// Format subscription end date
$formatted_end_date = $subscription_end_date ? date('F j, Y', strtotime($subscription_end_date)) : 'Not set';

// Check if subscription is active but expired
$subscription_expired = false;
if ($subscription_status === 'active' && $subscription_end_date && strtotime($subscription_end_date) <= time()) {
    $subscription_expired = true;
    // Update status to expired
    updateSubscriptionStatus($owner_id, 'expired');
    $subscription_status = 'expired';
}

// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renew Subscription - ALLEA Properties</title>
    <link rel="icon" href="logo1.ico" sizes="any">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/custom-loader.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .subscription-container {
            max-width: 800px;
            margin: 80px auto 30px;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .subscription-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .subscription-header img {
            max-width: 150px;
            margin-bottom: 20px;
        }
        .subscription-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .subscription-status {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .status-active {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .status-expired {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        .status-pending {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
        }
        .subscription-details {
            margin-bottom: 30px;
        }
        .subscription-details p {
            margin-bottom: 10px;
            font-size: 16px;
        }
        .subscription-info {
            margin-bottom: 30px;
        }
        .display-4 {
            font-size: 2.5rem;
            font-weight: 300;
            line-height: 1.2;
        }
        .action-buttons {
            display: flex;
            justify-content: space-between;
        }
        @media (max-width: 768px) {
            .subscription-container {
                margin: 20px auto;
                padding: 20px;
            }
            .display-4 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="subscription-container">
        <div class="subscription-header">
            <img src="assets/images/logo1.png" alt="ALLEA Properties Logo">
            <h1>Subscription Renewal</h1>
            <p>Your account requires renewal to continue accessing owner features</p>
        </div>

        <div class="subscription-status status-<?php echo strtolower($subscription_status); ?>">
            <h3>
                <i class="fas <?php echo $subscription_status === 'active' ? 'fa-check-circle' : ($subscription_status === 'expired' ? 'fa-times-circle' : 'fa-clock'); ?>"></i>
                Subscription Status: <?php echo ucfirst($subscription_status); ?>
            </h3>
            <?php if ($subscription_status === 'active'): ?>
                <p>Your subscription is active until <?php echo $formatted_end_date; ?></p>
            <?php elseif ($subscription_status === 'expired'): ?>
                <p>Your subscription expired on <?php echo $formatted_end_date; ?>. Please renew to continue using owner features.</p>
            <?php else: ?>
                <p>Your subscription is pending. Please complete payment to activate your account.</p>
            <?php endif; ?>
        </div>

        <div class="subscription-details">
            <h3>Account Information</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($owner_data['first_name'] . ' ' . $owner_data['last_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($owner_data['email']); ?></p>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($owner_data['username']); ?></p>
        </div>

        <div class="subscription-info">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Standard Subscription</h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <span class="display-4">UGX 50,000</span>
                        <p class="text-muted">4 Months Access</p>
                    </div>
                    <div class="alert alert-info">
                        <p class="mb-0"><i class="fas fa-info-circle me-2"></i> Your account will be renewed for 4 months after successful payment.</p>
                    </div>
                    <div class="text-center mt-3">
                        <p>Payment will be processed through our secure payment gateway.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="<?php echo $_SESSION['redirect_after_renewal'] ?? 'ownerDashboard.php'; ?>" class="btn btn-outline-secondary">Cancel</a>
            <button id="proceedToPayment" class="btn btn-success">Proceed to Payment</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/custom-loader.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Proceed to payment
            document.getElementById('proceedToPayment').addEventListener('click', function() {
                // Show loading indicator
                const button = this;
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

                // Check if there's an error message in the URL
                const urlParams = new URLSearchParams(window.location.search);
                const errorParam = urlParams.get('error');
                const messageParam = urlParams.get('message');

                if (errorParam) {
                    // Display error message if present
                    let errorMessage = 'An error occurred during payment processing.';
                    if (messageParam) {
                        errorMessage += ' ' + messageParam;
                    }
                    alert(errorMessage);
                    button.disabled = false;
                    button.innerHTML = 'Proceed to Payment';
                    return;
                }

                // Add test parameter for testing without actual payment gateway
                const useTestMode = false; // Set to false for real payments
                const testParam = useTestMode ? '?test=1' : '';

                // Redirect to payment processing after a short delay
                setTimeout(function() {
                    window.location.href = `process_subscription_payment.php${testParam}`;
                }, 500);
            });
        });
    </script>
</body>
</html>
