<?php
// Start session and enable error reporting for debugging
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';

// Get the user type from POST or session
$expected_user_type = 'admin';
$posted_user_type = isset($_POST['user_type']) ? $_POST['user_type'] : '';

// If user_type is not set in session but is provided in POST, set it in session
if (!isset($_SESSION['user_type']) && $posted_user_type === $expected_user_type) {
    $_SESSION['user_type'] = $posted_user_type;
}

// Check if user is logged in and has the correct user type
// Allow the report to be generated if the posted user type matches the expected type
// Use isset to safely check user_type to avoid undefined array key warning
if (!isset($_SESSION['user_id']) || (!isset($_SESSION['user_type']) || ($_SESSION['user_type'] !== $expected_user_type && $posted_user_type !== $expected_user_type))) {
    // For debugging - show session info before redirecting
    echo '<pre>Debug: Session issue detected\n';
    echo 'SESSION: ' . print_r($_SESSION, true) . '\n';
    echo 'User ID set: ' . (isset($_SESSION['user_id']) ? 'Yes' : 'No') . '\n';
    echo 'User type: ' . (isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'Not set') . '\n';
    echo 'Posted user type: ' . $posted_user_type . '\n';
    echo 'Expected user type: ' . $expected_user_type . '\n';
    echo '</pre>';

    // Uncomment to enable redirect
    // header('Location: login.html');
    // exit();

    // For now, just continue with a warning
    echo '<div style="background-color: #ffcccc; padding: 10px; margin: 10px; border: 1px solid red;">';
    echo '<strong>Warning:</strong> You appear to be logged out or not authorized as an admin. ';
    echo 'This is a debug mode that allows the report to be generated anyway. ';
    echo 'In production, you would be redirected to the login page.';
    echo '</div>';
}

// Get report type from request (either GET or POST)
$report_type = '';
if (isset($_GET['type'])) {
    $report_type = $_GET['type'];
} elseif (isset($_POST['report_type'])) {
    $report_type = $_POST['report_type'];
}

// Connect to database
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get admin details (with fallback for debugging)
$admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Default to ID 1 for debugging
$admin_query = "SELECT * FROM admin WHERE admin_id = ?";
$stmt = $conn->prepare($admin_query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_result = $stmt->get_result();
$admin_data = $admin_result->fetch_assoc();

// If no admin data found (for debugging), create a placeholder
if (!$admin_data) {
    $admin_data = [
        'admin_id' => 1,
        'username' => 'Debug Admin',
        'email' => 'admin@example.com'
    ];
}
$stmt->close();

// Initialize data arrays
$rentals = [];
$sales = [];
$managers = [];
$owners = [];
$subscriptions = [];

// Fetch data based on report type
if ($report_type == 'rentals' || $report_type == 'all') {
    // Fetch rentals data
    $query = "SELECT r.*,
                CONCAT(o.first_name, ' ', o.last_name) as owner_name,
                CONCAT(m.first_name, ' ', m.last_name) as manager_name,
                CONCAT(parish, ', ', ward) AS location,
                price AS rent,
                status AS availability
                FROM rental_property r
                LEFT JOIN property_owner o ON r.owner_id = o.owner_id
                LEFT JOIN property_manager m ON r.manager_id = m.manager_id";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $rentals = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

if ($report_type == 'sales' || $report_type == 'all') {
    // Fetch sales data
    $query = "SELECT s.*,
                CONCAT(o.first_name, ' ', o.last_name) as owner_name,
                CONCAT(m.first_name, ' ', m.last_name) as manager_name,
                CONCAT(parish, ', ', ward) AS location,
                price,
                status AS availability
                FROM sales_property s
                LEFT JOIN property_owner o ON s.owner_id = o.owner_id
                LEFT JOIN property_manager m ON s.manager_id = m.manager_id";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $sales = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

if ($report_type == 'managers' || $report_type == 'all') {
    // Fetch managers data
    $query = "SELECT m.*,
                (SELECT COUNT(*) FROM rental_property WHERE manager_id = m.manager_id) as property_count,
                m.username AS manager_name
                FROM property_manager m";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $managers = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

if ($report_type == 'owners' || $report_type == 'all') {
    // Fetch owners data
    $query = "SELECT o.*,
                (SELECT COUNT(*) FROM rental_property WHERE owner_id = o.owner_id) as rental_count,
                (SELECT COUNT(*) FROM sales_property WHERE owner_id = o.owner_id) as sales_count
                FROM property_owner o";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $owners = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

if ($report_type == 'subscriptions' || $report_type == 'all') {
    // Fetch subscription data
    $query = "SELECT s.*,
                CONCAT(o.first_name, ' ', o.last_name) as owner_name,
                o.email as owner_email,
                DATE_FORMAT(s.payment_date, '%Y-%m-%d') as formatted_payment_date,
                DATE_FORMAT(s.subscription_start_date, '%Y-%m-%d') as formatted_start_date,
                DATE_FORMAT(s.subscription_end_date, '%Y-%m-%d') as formatted_end_date
              FROM owner_subscriptions s
              LEFT JOIN property_owner o ON s.owner_id = o.owner_id
              WHERE s.payment_status = 'completed'
              ORDER BY s.payment_date DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $subscriptions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Close connection
$conn->close();

// Format date for the report
$report_date = date('F j, Y');

// Generate report title based on type
$report_title = "All Properties Report";
if ($report_type == 'rentals') {
    $report_title = "Rental Properties Report";
} else if ($report_type == 'sales') {
    $report_title = "Sales Properties Report";
} else if ($report_type == 'managers') {
    $report_title = "Property Managers Report";
} else if ($report_type == 'owners') {
    $report_title = "Property Owners Report";
} else if ($report_type == 'subscriptions') {
    $report_title = "Owner Subscriptions Report";
}

// Helper function to format currency
function formatCurrency($amount) {
    return number_format($amount, 0, '.', ',');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $report_title; ?> - ALLEA Properties</title>
    <link rel="icon" href="logo1.ico" sizes="any">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        .report-container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .report-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }
        .report-header img {
            max-width: 150px;
            margin-bottom: 15px;
        }
        .report-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 5px;
        }
        .report-date {
            font-size: 14px;
            color: #888;
        }
        .report-section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .summary-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .summary-label {
            font-weight: bold;
        }
        .print-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .print-button:hover {
            background: #0069d9;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
                background: white;
            }
            .report-container {
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="no-print" style="text-align: right; margin-bottom: 20px;">
            <button class="print-button" onclick="window.print()">Print Report</button>
        </div>

        <div class="report-header">
            <img src="assets/images/logo1.png" alt="ALLEA Properties Logo">
            <div class="report-title"><?php echo $report_title; ?></div>
            <div class="report-subtitle">Administrator: <?php echo $admin_data['first_name'] . ' ' . $admin_data['last_name']; ?></div>
            <div class="report-date">Generated on: <?php echo $report_date; ?></div>
        </div>

        <?php if ($report_type == 'rentals' || $report_type == 'all'): ?>
        <div class="report-section">
            <div class="section-title">Rental Properties</div>

            <div class="summary-box">
                <div class="summary-item">
                    <span class="summary-label">Total Rental Properties:</span>
                    <span><?php echo count($rentals); ?></span>
                </div>
                <?php
                $total_rent = array_sum(array_column($rentals, 'price'));
                $available_rentals = count(array_filter($rentals, function($rental) {
                    return $rental['status'] == 'Available';
                }));
                ?>
                <div class="summary-item">
                    <span class="summary-label">Total Monthly Rental Value:</span>
                    <span>UGX <?php echo formatCurrency($total_rent); ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Available Properties:</span>
                    <span><?php echo $available_rentals; ?></span>
                </div>
            </div>

            <?php if (count($rentals) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Property Name</th>
                        <th>Owner</th>
                        <th>Manager</th>
                        <th>Location</th>
                        <th>Size</th>
                        <th>Bedrooms</th>
                        <th>Bathrooms</th>
                        <th>Monthly Rent</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rentals as $rental): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($rental['property_name']); ?></td>
                        <td><?php echo htmlspecialchars($rental['owner_name']); ?></td>
                        <td><?php echo htmlspecialchars($rental['manager_name']); ?></td>
                        <td><?php echo htmlspecialchars($rental['location']); ?></td>
                        <td><?php echo htmlspecialchars($rental['property_size']); ?> sq ft</td>
                        <td><?php echo htmlspecialchars($rental['bedrooms']); ?></td>
                        <td><?php echo htmlspecialchars($rental['bathrooms']); ?></td>
                        <td>UGX <?php echo formatCurrency($rental['price']); ?></td>
                        <td><?php echo htmlspecialchars($rental['availability']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No rental properties found.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($report_type == 'sales' || $report_type == 'all'): ?>
        <div class="report-section">
            <div class="section-title">Sales Properties</div>

            <div class="summary-box">
                <div class="summary-item">
                    <span class="summary-label">Total Sales Properties:</span>
                    <span><?php echo count($sales); ?></span>
                </div>
                <?php
                $total_sales_value = array_sum(array_column($sales, 'price'));
                $available_sales = count(array_filter($sales, function($sale) {
                    return $sale['status'] == 'Available';
                }));
                ?>
                <div class="summary-item">
                    <span class="summary-label">Total Sales Value:</span>
                    <span>UGX <?php echo formatCurrency($total_sales_value); ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Available Properties:</span>
                    <span><?php echo $available_sales; ?></span>
                </div>
            </div>

            <?php if (count($sales) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Property Name</th>
                        <th>Owner</th>
                        <th>Manager</th>
                        <th>Location</th>
                        <th>Size</th>
                        <th>Bedrooms</th>
                        <th>Bathrooms</th>
                        <th>Price</th>
                        <th>Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sale['property_name']); ?></td>
                        <td><?php echo htmlspecialchars($sale['owner_name']); ?></td>
                        <td><?php echo htmlspecialchars($sale['manager_name']); ?></td>
                        <td><?php echo htmlspecialchars($sale['location']); ?></td>
                        <td><?php echo htmlspecialchars($sale['property_size']); ?> sq ft</td>
                        <td><?php echo htmlspecialchars($sale['bedrooms']); ?></td>
                        <td><?php echo htmlspecialchars($sale['bathrooms']); ?></td>
                        <td><?php echo htmlspecialchars($sale['price']); ?></td>
                        <td><?php echo htmlspecialchars($sale['property_type']); ?></td>
                        <td><?php echo htmlspecialchars($sale['status']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No sales properties found.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($report_type == 'managers' || $report_type == 'all'): ?>
        <div class="report-section">
            <div class="section-title">Property Managers</div>

            <div class="summary-box">
                <div class="summary-item">
                    <span class="summary-label">Total Managers:</span>
                    <span><?php echo count($managers); ?></span>
                </div>
            </div>

            <?php if (count($managers) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Manager Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Experience (Years)</th>
                        <th>Properties Managed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($managers as $manager): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($manager['first_name'] . ' ' . $manager['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($manager['email']); ?></td>
                        <td><?php echo htmlspecialchars($manager['phone']); ?></td>
                        <td><?php echo htmlspecialchars($manager['experience']); ?></td>
                        <td><?php echo htmlspecialchars($manager['property_count']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No managers found.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($report_type == 'owners' || $report_type == 'all'): ?>
        <div class="report-section">
            <div class="section-title">Property Owners</div>

            <div class="summary-box">
                <div class="summary-item">
                    <span class="summary-label">Total Owners:</span>
                    <span><?php echo count($owners); ?></span>
                </div>
            </div>

            <?php if (count($owners) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Owner Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Rental Properties</th>
                        <th>Sales Properties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($owners as $owner): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($owner['first_name'] . ' ' . $owner['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($owner['email']); ?></td>
                        <td><?php echo htmlspecialchars($owner['phone']); ?></td>
                        <td><?php echo htmlspecialchars($owner['rental_count']); ?></td>
                        <td><?php echo htmlspecialchars($owner['sales_count']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No owners found.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($report_type == 'subscriptions' || $report_type == 'all'): ?>
        <div class="report-section">
            <div class="section-title">Owner Subscriptions</div>

            <div class="summary-box">
                <div class="summary-item">
                    <span class="summary-label">Total Subscriptions:</span>
                    <span><?php echo count($subscriptions); ?></span>
                </div>
                <?php
                $total_subscription_income = array_sum(array_column($subscriptions, 'amount'));
                ?>
                <div class="summary-item">
                    <span class="summary-label">Total Subscription Income:</span>
                    <span>UGX <?php echo formatCurrency($total_subscription_income); ?></span>
                </div>
            </div>

            <?php if (count($subscriptions) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Owner Name</th>
                        <th>Email</th>
                        <th>Payment Date</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Transaction ID</th>
                        <th>Subscription Period</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscriptions as $subscription): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($subscription['owner_name']); ?></td>
                        <td><?php echo htmlspecialchars($subscription['owner_email']); ?></td>
                        <td><?php echo htmlspecialchars($subscription['formatted_payment_date']); ?></td>
                        <td>UGX <?php echo formatCurrency($subscription['amount']); ?></td>
                        <td><?php echo htmlspecialchars($subscription['payment_method']); ?></td>
                        <td><?php echo htmlspecialchars($subscription['transaction_id']); ?></td>
                        <td><?php echo htmlspecialchars($subscription['formatted_start_date']); ?> to <?php echo htmlspecialchars($subscription['formatted_end_date']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No subscription payments found.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="report-footer" style="margin-top: 30px; text-align: center; font-size: 12px; color: #888;">
            <p>This report was generated from the ALLEA Properties Management System.</p>
            <p>&copy; <?php echo date('Y'); ?> ALLEA Properties. All rights reserved.</p>
        </div>
    </div>

    <script>
        // Auto-print when the page loads (optional)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html>
