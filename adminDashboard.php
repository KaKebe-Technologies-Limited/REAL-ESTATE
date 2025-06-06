<?php
require_once 'config.php';
session_start();

// Make sure this matches your admin table's user_id and user_type
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.html');
    exit();
}

// Load user profile picture on page load
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$conn->connect_error) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT profile_picture FROM admin WHERE admin_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION['profile_picture'] = $row['profile_picture'] ?: 'uploads/profile_picture/default-profile.jpg';
    }
    $stmt->close();
    $conn->close();
}

// Initialize variables
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10; // Number of entries per page
$offset = ($page - 1) * $limit;

// Database connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Calculate summary data for dashboard
// Rental properties count and total income
$rental_summary_query = "SELECT
                            COUNT(*) as total_rentals,
                            IFNULL(SUM(price), 0) as total_rental_income
                        FROM rental_property";
$rental_summary_result = $conn->query($rental_summary_query);
$rental_summary = $rental_summary_result->fetch_assoc();
$total_rental_income = $rental_summary['total_rental_income'];

// Sales properties count and total income
$sales_summary_query = "SELECT
                            COUNT(*) as total_sales,
                            IFNULL(SUM(price), 0) as total_sales_income
                        FROM sales_property";
$sales_summary_result = $conn->query($sales_summary_query);
$sales_summary = $sales_summary_result->fetch_assoc();
$total_sales_income = $sales_summary['total_sales_income'];

// Subscription income
$subscription_summary_query = "SELECT
                                COUNT(*) as total_subscriptions,
                                IFNULL(SUM(amount), 0) as total_subscription_income
                            FROM owner_subscriptions
                            WHERE payment_status = 'completed'";
$subscription_summary_result = $conn->query($subscription_summary_query);
$subscription_summary = $subscription_summary_result->fetch_assoc();
$total_subscription_income = $subscription_summary['total_subscription_income'];

// Format currency values
function format_currency($amount) {
    return 'UGX ' . number_format($amount, 0, '.', ',');
}

// Fetch rentals data
try {
    // Get total count of rentals
    $count_query = "SELECT COUNT(*) as total FROM rental_property";
    $count_result = $conn->query($count_query);
    $total_rentals = $count_result->fetch_assoc()['total'];
    $total_pages_rentals = ceil($total_rentals / $limit);

    // Get rentals with pagination
    $query = "SELECT r.*,
                CONCAT(o.first_name, ' ', o.last_name) as owner_name,
                CONCAT(m.first_name, ' ', m.last_name) as manager_name,
                CONCAT(parish, ', ', ward) AS location,
                price AS rent,
                status
                FROM rental_property r
                LEFT JOIN property_owner o ON r.owner_id = o.owner_id
                LEFT JOIN property_manager m ON r.manager_id = m.manager_id
                LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $rentals = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $rentals = [];
    $total_rentals = 0;
    $total_pages_rentals = 1;
}

// Fetch sales data
try {
    // Get total count of sales
    $count_query = "SELECT COUNT(*) as total FROM sales_property";
    $count_result = $conn->query($count_query);
    $total_sales = $count_result->fetch_assoc()['total'];
    $total_pages_sales = ceil($total_sales / $limit);

    // Get sales with pagination
    $query = "SELECT s.*,
                CONCAT(o.first_name, ' ', o.last_name) as owner_name,
                CONCAT(m.first_name, ' ', m.last_name) as manager_name,
                CONCAT(parish, ', ', ward) AS location,
                price,
                status
                FROM sales_property s
                LEFT JOIN property_owner o ON s.owner_id = o.owner_id
                LEFT JOIN property_manager m ON s.manager_id = m.manager_id
                LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $sales = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $sales = [];
    $total_sales = 0;
    $total_pages_sales = 1;
}

// Fetch owners data
try {
    // Get total count of owners
    $count_query = "SELECT COUNT(*) as total FROM property_owner";
    $count_result = $conn->query($count_query);
    $total_owners = $count_result->fetch_assoc()['total'];
    $total_pages_owners = ceil($total_owners / $limit);

    // Get owners with pagination
    $query = "SELECT o.*,
                (SELECT COUNT(*) FROM rental_property WHERE owner_id = o.owner_id) as property_count, o.username AS owner_name
                FROM property_owner o
                LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $owners = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $owners = [];
    $total_owners = 0;
    $total_pages_owners = 1;
}

// Fetch managers data
try {
    // Get total count of managers
    $count_query = "SELECT COUNT(*) as total FROM property_manager";
    $count_result = $conn->query($count_query);
    $total_managers = $count_result->fetch_assoc()['total'];
    $total_pages_managers = ceil($total_managers / $limit);

    // Get managers with pagination
    $query = "SELECT m.*,
                (SELECT COUNT(*) FROM rental_property WHERE manager_id = m.manager_id) as property_count, m.username AS manager_name
                FROM property_manager m
                LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $managers = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $managers = [];
    $total_managers = 0;
    $total_pages_managers = 1;
}

// Close database connection
$conn->close();

// Set a consistent value for total_pages used in pagination
$total_pages = max($total_pages_rentals, $total_pages_sales, $total_pages_owners, $total_pages_managers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Admin Dashboard - ALLEA Properties</title>
    <link rel="icon" href="logo1.ico" sizes="any">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Add to the <head> section -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="assets/css/custom-loader.css">
    <link rel="stylesheet" href="assets/css/dashboard-responsive.css">
    <link rel="stylesheet" href="assets/css/sidebar-fix.css">
    <link rel="stylesheet" href="assets/css/mobile-sidebar-fix.css">
    <link rel="stylesheet" href="assets/css/responsive-tables.css">
    <link rel="stylesheet" href="assets/css/submenu-click-fix.css">
    <link rel="stylesheet" href="assets/css/direct-submenu-fix.css">
    <link rel="stylesheet" href="assets/css/profile-styles.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/custom-loader.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="assets/js/responsive-dashboard.js" defer></script>
    <script src="assets/js/responsive-tables.js" defer></script>
    <script src="assets/js/mobile-sidebar.js" defer></script>
    <script src="assets/js/sidebar-toggle-fix.js" defer></script>
    <script src="assets/js/mobile-submenu-fix.js" defer></script>
    <script src="assets/js/submenu-click-fix.js" defer></script>
    <script src="assets/js/direct-submenu-fix.js" defer></script>
    <script src="assets/js/report-generator.js" defer></script>
    <script src="assets/js/subscription-management.js" defer></script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <button type="button" class="btn btn-light" id="sidebar-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand ms-3" href="#">ALL-EA</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-ellipsis-v"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-globe"></i>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">New Message</a></li>
                            <li><a class="dropdown-item" href="#">Property Update</a></li>
                            <li><a class="dropdown-item" href="#">System Alert</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown">
                            <img id="selected-language-flag" src="https://flagcdn.com/w40/us.png" alt="Selected Language" style="width: 20px; height: 15px; margin-right: 5px;">
                            <span id="selected-language-name">en</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item language-option" href="#" data-lang="English" data-flag="https://flagcdn.com/w40/us.png">
                                    <img src="https://flagcdn.com/w40/us.png" alt="English" style="width: 20px; height: 15px; margin-right: 10px;"> en-us
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item language-option" href="#" data-lang="Spanish" data-flag="https://flagcdn.com/w40/es.png">
                                    <img src="https://flagcdn.com/w40/es.png" alt="Spanish" style="width: 20px; height: 15px; margin-right: 10px;"> Spanish
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item language-option" href="#" data-lang="French" data-flag="https://flagcdn.com/w40/fr.png">
                                    <img src="https://flagcdn.com/w40/fr.png" alt="French" style="width: 20px; height: 15px; margin-right: 10px;"> French
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown d-none d-lg-block">
                        <a class="nav-link profile-link" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="<?php echo $_SESSION['profile_picture'] ?? 'uploads/profile_picture/default-profile.jpeg'; ?>" alt="Profile" class="rounded-circle profile-picture" onclick="showProfile(); return false;">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"  onclick="showProfile()"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="#" onclick="showSettings()"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="login.html"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- Mobile Logout Button -->
    <a href="login.html" class="mobile-logout" title="Logout">
        <i class="fas fa-sign-out-alt"></i>
    </a>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="assets/images/logo1.png" alt="Logo" class="logo">
        </div>
        <!-- Profile section in sidebar for mobile -->
        <div class="sidebar-profile d-none d-lg-none">
            <a href="#" onclick="showProfile(); return false;">
                <img src="<?php echo $_SESSION['profile_picture'] ?? 'uploads/profile_picture/default-profile.jpeg'; ?>" alt="Profile" class="profile-picture">
            </a>
            <div class="profile-name">Admin User</div>
            <div class="profile-role">Administrator</div>
        </div>
        <ul class="sidebar-nav">
            <li class="nav-item active">
                <a href="#" class="nav-link"data-form="dashboard-content" onclick="showDashboard()">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link" data-toggle="submenu">
                    <i class="fas fa-building"></i>
                    <span>Properties</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="has-submenu">
                        <a href="#" class="submenu-link" data-toggle="property-submenu">
                            <i class="fas fa-key"></i>
                            <span>Rentals</span>
                            <i class="fas fa-chevron-right property-submenu-arrow"></i>
                        </a>
                        <ul class="property-submenu">
                            <li>
                                <a href="#" class="submenu-link" data-form="rental-listing" onclick="showRentalListing()">
                                    <i class="fas fa-list"></i>
                                    <span>Rental listing</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="submenu-link" data-form="add-rental" onclick="showAddRental()">
                                    <i class="fas fa-home"></i>
                                    <span>Add Rental</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="has-submenu">
                        <a href="#" class="submenu-link" data-toggle="property-submenu">
                            <i class="fas fa-home"></i>
                            <span>Sales</span>
                            <i class="fas fa-chevron-right property-submenu-arrow"></i>
                        </a>
                        <ul class="property-submenu">
                            <li>
                                <a href="#" class="submenu-link" data-form="sales-listing" onclick="showSalesListing()">
                                    <i class="fas fa-list"></i>
                                    <span>Sales listing</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="submenu-link" data-form="add-sale" onclick="showAddSale()">
                                    <i class="fas fa-home"></i>
                                    <span>Add Sales</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link" data-toggle="submenu">
                    <i class="fas fa-user-tie"></i>
                    <span>Manage Owners</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="#" class="submenu-link" data-form="owners-listing" onclick="showOwnersListing()">
                            <i class="fas fa-list"></i>
                            <span>Owner List</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="submenu-link" data-form="create-owner" onclick="showCreateOwner()">
                            <i class="fas fa-user-plus"></i>
                            <span>Create Owner</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="submenu-link" data-form="owner-subscriptions" onclick="showOwnerSubscriptions()">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Subscriptions</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link" data-toggle="submenu">
                    <i class="fas fa-user-cog"></i>
                    <span>Manage Managers</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="#" class="submenu-link" data-form="managers-listing" onclick="showManagersListing()">
                            <i class="fas fa-list"></i>
                            <span>All Managers</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="submenu-link" data-form="create-manager" onclick="showCreateManager()">
                            <i class="fas fa-user-plus"></i>
                            <span>Create Manager</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="showSettings()">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="main-content">
        <!-- Dashboard Content -->
        <div id="dashboard-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <h1 class="page-title">Dashboard Overview</h1>
                    </div>
                </div>
                <!-- First Row of Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="summary-card">
                            <div class="summary-content">
                                <div class="summary-icon bg-primary">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div class="summary-details">
                                    <h3 class="summary-title">Total Owners</h3>
                                    <p class="summary-number"><?php echo $total_owners; ?></p>
                                    <p class="summary-trend positive">
                                        <i class="fas fa-arrow-up"></i> 12% from last month
                                    </p>
                                </div>
                            </div>
                            <div class="summary-footer">
                                <a href="#" class="view-details" onclick="showOwnersListing()">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card">
                            <div class="summary-content">
                                <div class="summary-icon bg-success">
                                    <i class="fas fa-user-cog"></i>
                                </div>
                                <div class="summary-details">
                                    <h3 class="summary-title">Total Managers</h3>
                                    <p class="summary-number"><?php echo $total_managers; ?></p>
                                    <p class="summary-trend positive">
                                        <i class="fas fa-arrow-up"></i> 8% from last month
                                    </p>
                                </div>
                            </div>
                            <div class="summary-footer">
                                <a href="#" class="view-details" onclick="showManagersListing()">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card">
                            <div class="summary-content">
                                <div class="summary-icon" style="background-color: #673ab7;">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="summary-details">
                                    <h3 class="summary-title">Owner Subscriptions</h3>
                                    <p class="summary-number"><?php echo $subscription_summary['total_subscriptions']; ?></p>
                                    <p class="summary-trend positive">
                                        <i class="fas fa-money-bill-wave"></i> <?php echo format_currency($total_subscription_income); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="summary-footer">
                                <a href="#" class="view-details" onclick="showOwnerSubscriptions()">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Second Row of Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="summary-card">
                            <div class="summary-content">
                                <div class="summary-icon" style="background-color: #ff9800;">
                                    <i class="fas fa-key"></i>
                                </div>
                                <div class="summary-details">
                                    <h3 class="summary-title">Rental Properties</h3>
                                    <p class="summary-number"><?php echo $rental_summary['total_rentals']; ?></p>
                                    <p class="summary-trend positive">
                                        <i class="fas fa-money-bill-wave"></i> <?php echo format_currency($total_rental_income); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="summary-footer">
                                <a href="#" class="view-details" onclick="showRentalListing()">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card">
                            <div class="summary-content">
                                <div class="summary-icon" style="background-color: #e91e63;">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="summary-details">
                                    <h3 class="summary-title">Sales Properties</h3>
                                    <p class="summary-number"><?php echo $sales_summary['total_sales']; ?></p>
                                    <p class="summary-trend positive">
                                        <i class="fas fa-money-bill-wave"></i> <?php echo format_currency($total_sales_income); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="summary-footer">
                                <a href="#" class="view-details" onclick="showSalesListing()">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-card">
                            <div class="summary-content">
                                <div class="summary-icon" style="background-color: #009688;">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="summary-details">
                                    <h3 class="summary-title">Total Income</h3>
                                    <p class="summary-number"><?php echo format_currency($total_rental_income + $total_sales_income + $total_subscription_income); ?></p>
                                    <p class="summary-trend positive">
                                        <i class="fas fa-arrow-up"></i> Combined Revenue
                                    </p>
                                </div>
                            </div>
                            <div class="summary-footer">
                                <a href="#" class="view-details generate-report-btn">
                                    Generate Report <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Income Statistics -->
                <div class="row">
                    <div class="col-xl-8">
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h3>Income Statistics</h3>
                                <div class="card-actions">
                                    <select class="form-select form-select-sm" id="income-period-selector">
                                        <option value="6months">Last 6 months</option>
                                        <option value="3months">Last 3 months</option>
                                        <option value="1month">Last month</option>
                                    </select>
                                </div>
                            </div>
                            <div class="dashboard-card-body">
                                <canvas id="incomeChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h3>Income Distribution</h3>
                            </div>
                            <div class="dashboard-card-body">
                                <canvas id="distributionChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Performers -->
                <div class="row mt-4">
                    <div class="col-xl-6">
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h3>Top Owners by Income</h3>
                            </div>
                            <div class="dashboard-card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="top-owners-table">
                                        <thead>
                                            <tr>
                                                <th>Owner</th>
                                                <th>Rental Income</th>
                                                <th>Sales Income</th>
                                                <th>Total Income</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="4" class="text-center">Loading data...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h3>Top Managers by Income</h3>
                            </div>
                            <div class="dashboard-card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="top-managers-table">
                                        <thead>
                                            <tr>
                                                <th>Manager</th>
                                                <th>Rental Income</th>
                                                <th>Sales Income</th>
                                                <th>Total Income</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="4" class="text-center">Loading data...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities & Quick Actions -->
                <div class="row mt-4">
                    <div class="col-xl-8">
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h3>Recent Activities</h3>
                                <button class="btn btn-sm btn-light" onclick="viewAllActivities()">View All</button>
                            </div>
                            <div class="dashboard-card-body p-0">
                                <div class="activity-list">
                                    <?php
                                    require_once 'get_recent_activities.php';
                                    $activities = getRecentActivities(5);

                                    if (!empty($activities)):
                                        foreach ($activities as $activity):
                                    ?>
                                        <div class="activity-item">
                                            <div class="activity-icon <?php echo htmlspecialchars($activity['icon_bg_class']); ?>">
                                                <i class="<?php echo htmlspecialchars($activity['icon_class']); ?>"></i>
                                            </div>
                                            <div class="activity-content">
                                                <h4><?php echo htmlspecialchars($activity['title']); ?></h4>
                                                <p><?php echo htmlspecialchars($activity['description']); ?></p>
                                                <span class="activity-time"><?php echo getTimeAgo($activity['created_at']); ?></span>
                                            </div>
                                            <?php if ($activity['status'] == 'new'): ?>
                                            <div class="activity-status">
                                                <span class="badge bg-success">New</span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php
                                        endforeach;
                                    else:
                                    ?>
                                        <div class="activity-item">
                                            <p class="text-center py-3">No recent activities</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h3>Quick Actions</h3>
                            </div>
                            <div class="dashboard-card-body">
                                <div class="quick-actions">
                                    <a href="#" class="quick-action-btn" onclick="showCreateOwner()">
                                        <i class="fas fa-user-plus"></i>
                                        <span>Add Owner</span>
                                    </a>
                                    <a href="#" class="quick-action-btn" onclick="showCreateManager()">
                                        <i class="fas fa-user-cog"></i>
                                        <span>Add Manager</span>
                                    </a>
                                    <a href="#" class="quick-action-btn generate-report-btn">
                                        <i class="fas fa-file-alt"></i>
                                        <span>Generate Report</span>
                                    </a>
                                    <a href="#" class="quick-action-btn" onclick="showSettings()">
                                        <i class="fas fa-cog"></i>
                                        <span>Settings</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Content -->
        <div id="settings-content" style="display: none;">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <h1 class="page-title">Dashboard Settings</h1>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h3>General Settings</h3>
                            </div>
                            <div class="dashboard-card-body">
                                <form class="settings-form" onsubmit="saveSettings(event)">
                                    <div class="mb-4">
                                        <h5 class="settings-section-title">Site Information</h5>
                                        <div class="mb-3">
                                            <label class="form-label">Site Name</label>
                                            <input type="text" class="form-control" value="Rental Company">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Site Description</label>
                                            <textarea class="form-control" rows="3">Your trusted real estate partner</textarea>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <h5 class="settings-section-title">Email Settings</h5>
                                        <div class="mb-3">
                                            <label class="form-label">Contact Email</label>
                                            <input type="email" class="form-control" value="contact@example.com">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Email Notifications</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" checked>
                                                <label class="form-check-label">New Property Alerts</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" checked>
                                                <label class="form-check-label">Owner Registration</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <h5 class="settings-section-title">System Settings</h5>
                                        <div class="mb-3">
                                            <label class="form-label">Default Language</label>
                                            <select class="form-select">
                                                <option selected>English</option>
                                                <option>Spanish</option>
                                                <option>French</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Time Zone</label>
                                            <select class="form-select">
                                                <option selected>UTC+03:00 East Africa Time</option>
                                                <option>UTC+00:00 GMT</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <h5 class="settings-section-title">Theme Settings</h5>
                                        <div class="mb-3">
                                            <label class="form-label">Theme Mode</label>
                                            <div class="theme-options">
                                                <div class="theme-option" onclick="setTheme('light')" id="light-theme">
                                                    <i class="fas fa-sun"></i>
                                                    <span>Light</span>
                                                </div>
                                                <div class="theme-option" onclick="setTheme('dark')" id="dark-theme">
                                                    <i class="fas fa-moon"></i>
                                                    <span>Dark</span>
                                                </div>
                                                <div class="theme-option" onclick="setTheme('blue')" id="blue-theme">
                                                    <i class="fas fa-palette"></i>
                                                    <span>Blue</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h3>Quick Settings</h3>
                            </div>
                            <div class="dashboard-card-body">
                                <div class="settings-shortcuts">
                                    <a href="#" class="settings-shortcut-btn">
                                        <i class="fas fa-shield-alt"></i>
                                        <span>Security Settings</span>
                                    </a>
                                    <a href="#" class="settings-shortcut-btn">
                                        <i class="fas fa-bell"></i>
                                        <span>Notification Settings</span>
                                    </a>
                                    <a href="#" class="settings-shortcut-btn">
                                        <i class="fas fa-database"></i>
                                        <span>Backup Settings</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Owner Subscriptions Content -->
        <div id="owner-subscriptions" style="display: none;">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <h1 class="page-title">Owner Subscriptions</h1>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" id="subscription-search" class="form-control" placeholder="Search by name, email or username...">
                            <button class="btn btn-primary" id="subscription-search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-group">
                            <button class="btn btn-outline-primary" id="refresh-subscriptions">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h3>Subscription Status</h3>
                                <div class="card-actions">
                                    <select id="subscription-filter" class="form-select form-select-sm">
                                        <option value="all">All Statuses</option>
                                        <option value="active">Active</option>
                                        <option value="expired">Expired</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>
                            </div>
                            <div class="dashboard-card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Owner</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Days Left</th>
                                                <th>Properties</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="subscriptions-table-body">
                                            <!-- Subscription data will be loaded here -->
                                            <tr>
                                                <td colspan="8" class="text-center">Loading subscription data...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="dashboard-card-footer">
                                <nav aria-label="Subscription pagination">
                                    <ul class="pagination justify-content-center" id="subscription-pagination">
                                        <!-- Pagination will be generated here -->
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Extend Subscription Modal -->
        <div class="modal fade" id="extendSubscriptionModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Extend Subscription</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="extendSubscriptionForm">
                            <input type="hidden" id="extend-owner-id" name="owner_id">

                            <div class="mb-3">
                                <label class="form-label">Owner</label>
                                <input type="text" id="extend-owner-name" class="form-control" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Current Status</label>
                                <input type="text" id="extend-current-status" class="form-control" >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Current End Date</label>
                                <input type="text" id="extend-current-end-date" class="form-control" >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Extend By (Months)</label>
                                <select id="extend-months" name="months" class="form-select" required>
                                    <option value="1">1 Month</option>
                                    <option value="2">2 Months</option>
                                    <option value="3">3 Months</option>
                                    <option value="4" selected>4 Months</option>
                                    <option value="6">6 Months</option>
                                    <option value="8">8 Months</option>
                                    <option value="12">12 Months</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Admin Notes</label>
                                <textarea id="extend-notes" name="notes" class="form-control" rows="3" placeholder="Optional notes about this extension"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirm-extend-btn">Extend Subscription</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscription History Modal -->
        <div class="modal fade" id="subscriptionHistoryModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Subscription History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="owner-info mb-4">
                            <h6>Owner Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> <span id="history-owner-name"></span></p>
                                    <p><strong>Email:</strong> <span id="history-owner-email"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Status:</strong> <span id="history-owner-status"></span></p>
                                    <p><strong>Current End Date:</strong> <span id="history-owner-end-date"></span></p>
                                </div>
                            </div>
                        </div>

                        <h6>Payment History</h6>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Period</th>
                                        <th>Transaction ID</th>
                                    </tr>
                                </thead>
                                <tbody id="subscription-history-table">
                                    <!-- History data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add before closing main-content div -->
        <div id="create-manager-form" style="display: none;">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 grid-margin">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Create Manager</h4>
                                <form id="createManager" class="form-sample">
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Username</label>
                                                <div class="col-sm-9">
                                                    <input name="username" type="text" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">First Name</label>
                                                <div class="col-sm-9">
                                                    <input name="first_name" type="text" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Last Name</label>
                                                <div class="col-sm-9">
                                                    <input name="last_name" type="text" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Email</label>
                                                <div class="col-sm-9">
                                                    <input name="email" type="email" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Phone</label>
                                                <div class="col-sm-9">
                                                    <input name="phone" type="tel" placeholder="+code-0000000" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Password</label>
                                                <div class="col-sm-9">
                                                    <input name="password" type="password" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Experience (Years)</label>
                                                <div class="col-sm-9">
                                                    <input name="experience" type="number" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary me-3">Submit</button>
                                        <button type="button" class="btn btn-light">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Owner Form -->
        <div id="create-owner-form" style="display: none;">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 grid-margin">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Create Property Owner</h4>
                                <form id="createOwner" class="form-sample">
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">First Name</label>
                                                <div class="col-sm-9">
                                                    <input name="first_name" type="text" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Last Name</label>
                                                <div class="col-sm-9">
                                                    <input name="last_name" type="text" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Email</label>
                                                <div class="col-sm-9">
                                                    <input name="email" type="email" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Phone</label>
                                                <div class="col-sm-9">
                                                    <input name="phone" type="tel" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Username</label>
                                                <div class="col-sm-9">
                                                    <input name="username" type="text" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Password</label>
                                                <div class="col-sm-9">
                                                    <input name="password" type="password" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">ID Type</label>
                                                <div class="col-sm-9">
                                                    <select name="id_type" class="form-control" required>
                                                        <option>National ID</option>
                                                        <option>Passport</option>
                                                        <option>Driver's License</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">ID Number</label>
                                                <div class="col-sm-9">
                                                    <input name="id_num" type="text" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Address</label>
                                                <div class="col-sm-10">
                                                    <textarea name="address" class="form-control" rows="4" required></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary me-3">Submit</button>
                                        <button type="button" class="btn btn-light">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Sale Property Form -->
        <div id="add-sale-form" style="display: none;">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 grid-margin">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Add Property for Sale</h4>
                                <form id="addSales" class="form-sample">
                                    <!-- Basic Information -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Property Name</label>
                                                <div class="col-sm-9">
                                                    <input name="property_name" type="text" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Price</label>
                                                <div class="col-sm-9">
                                                    <select name="price" class="form-control" required>
                                                        <option value="">Select Method</option>
                                                        <option value="Cash">Cash</option>
                                                        <option value="Installments">Installments</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Property Description -->
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Description</label>
                                                <div class="col-sm-10">
                                                    <textarea name="description" class="form-control" rows="4" placeholder="Detailed description of the property" required></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Property Details -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Utilities</label>
                                                <div class="col-sm-9">
                                                    <select name="utilities" class="form-control" required>
                                                        <option value="">Select Utilities</option>
                                                        <option value="Included">Included</option>
                                                        <option value="Not Included">Not Included</option>
                                                        <option value="Partial">Partially Included</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Property Type</label>
                                                <div class="col-sm-9">
                                                    <select name="property_type" class="form-control" required>
                                                        <option value="">Select Property Type</option>
                                                        <option value="Land">Only Land</option>
                                                        <option value="Flat">Flat</option>
                                                        <option value="Permanent">Permanent</option>
                                                        <option value="Semi Permanent">Semi-permanent</option>
                                                        <option value="Ground">Grounds</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Title</label>
                                                <div class="col-sm-9">
                                                    <select name="title" class="form-control" required>
                                                        <option value="">Select Title</option>
                                                        <option value="Yes">Yes</option>
                                                        <option value="No">No</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Close Amenities</label>
                                                <div class="col-sm-9">
                                                    <select name="amenities[]" class="form-control select2-multiple" multiple="multiple" required>
                                                        <option value="Market">Market</option>
                                                        <option value="School">School</option>
                                                        <option value="Church">Church / Mosque</option>
                                                        <option value="Institution">Institution</option>
                                                        <option value="Hospital">Hospital</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Property Size</label>
                                                <div class="col-sm-9">
                                                    <input name="property_size" type="number" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Property Owner</label>
                                                <div class="col-sm-9">
                                                    <select name="owner_id" id="sale_owner_id" class="form-control" required>
                                                        <option value="">Select Owner</option>
                                                        <!-- Options will be populated dynamically -->
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Bedrooms</label>
                                                <div class="col-sm-9">
                                                    <input name="bedrooms" type="number" min="0" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Bathrooms</label>
                                                <div class="col-sm-9">
                                                    <input name="bathrooms" type="number" min="0" step="0.5" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Status</label>
                                                <div class="col-sm-9">
                                                    <select name="status" class="form-control" required>
                                                        <option value="">Select Status</option>
                                                        <option value="Available">Available</option>
                                                        <option value="Unavailable">Unavailable</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Property Manager</label>
                                                <div class="col-sm-9">
                                                    <select name="manager_id" id="sale_manager_id" class="form-control">
                                                        <option value="">Select Manager</option>
                                                        <!-- Options will be populated dynamically -->
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Location Information -->
                                    <h5 class="mb-4">Location Details</h5>
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Country</label>
                                                <div class="col-sm-9">
                                                    <select name="country" id="country" class="form-control" required>
                                                        <option value="">Select Country</option>
                                                        <option value="Uganda">Uganda</option>
                                                        <option value="Kenya">Kenya</option>
                                                        <option value="Tanzania">Tanzania</option>
                                                        <option value="Rwanda">Rwanda</option>
                                                        <option value="Burundi">Burundi</option>
                                                        <option value="South Sudan">South Sudan</option>
                                                        <option value="DR Congo">DR Congo</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label" id="region-label">Region</label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="region" id="region" class="form-control" required placeholder="Enter region" >
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label" id="subregion-label">Sub-Region</label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="subregion" id="subregion" class="form-control" required placeholder="Enter sub-region" >
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label" id="parish-label">Parish</label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="parish" id="parish" class="form-control" required placeholder="Enter parish" >
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label" id="ward-label">Ward</label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="ward" id="ward" class="form-control" required placeholder="Enter ward" >
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label" id="cell-label">Cell</label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="cell" id="cell" class="form-control" required placeholder="Enter cell" >
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Images -->
                                        <div class="row mb-4">
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="col-sm-2 col-form-label">Property Images</label>
                                                    <div class="col-sm-10">
                                                        <input type="file" name="images[]" id="sale-images" class="form-control" multiple accept="image/*" required />
                                                        <div id="sale-preview-container" class="mt-3 d-flex flex-wrap gap-3">
                                                            <!-- Image previews will be inserted here -->
                                                        </div>
                                                        <small class="text-muted mt-2">You can upload up to 5 images. Maximum size per image: 5MB</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <!-- Submit Buttons -->
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary me-3">Submit</button>
                                        <button type="button" class="btn btn-light">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Rental Property Form -->
        <div id="add-rental-form" style="display: none;">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 grid-margin">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Add Rental Property</h4>
                                <form id="addRental" class="form-sample">
                                    <!-- Basic Information -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Property Name</label>
                                                <div class="col-sm-9">
                                                    <input name="property_name" type="text" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Price</label>
                                                <div class="col-sm-9">
                                                    <input name="price" type="number" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Property Description -->
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Description</label>
                                                <div class="col-sm-10">
                                                    <textarea name="description" class="form-control" rows="4" placeholder="Detailed description of the property" required></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Property Details -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Landlord</label>
                                                <div class="col-sm-9">
                                                    <select name="landlord" class="form-control" required>
                                                        <option value="">Select Landlord</option>
                                                        <option value="Resident">Resident</option>
                                                        <option value="Non Resident">Non resident</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Security</label>
                                                <div class="col-sm-9">
                                                    <select name="security[]" class="form-control select2-multiple" multiple="multiple">
                                                        <option value="CCTV">CCTV</option>
                                                        <option value="Guards">Security Guards</option>
                                                        <option value="Electric Fence">Electric Fence</option>
                                                        <option value="Alarm">Alarm System</option>
                                                        <option value="Security Lights">Security Lights</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Utilities</label>
                                                <div class="col-sm-9">
                                                    <select name="utilities" class="form-control" required>
                                                        <option value="">Select Utilities</option>
                                                        <option value="Included">Included</option>
                                                        <option value="Not Included">Not Included</option>
                                                        <option value="Partially Included">Partially Included</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Property Type</label>
                                                <div class="col-sm-9">
                                                    <select name="property_type" class="form-control" required>
                                                        <option value="">Select Property Type</option>
                                                        <option value="Flat">Flat</option>
                                                        <option value="Permanent">Permanent</option>
                                                        <option value="Semi-permanent">Semi-permanent</option>
                                                        <option value="Grounds">Grounds</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Convenience</label>
                                                <div class="col-sm-9">
                                                    <select name="convenience" class="form-control" required>
                                                        <option value="">Select Convenience Level</option>
                                                        <option value="Crowded">Crowded</option>
                                                        <option value="Private">Private</option>
                                                        <option value="Self contained">Self contained</option>
                                                        <option value="Non self contained">Non self contained</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Property Class</label>
                                                <div class="col-sm-9">
                                                    <select name="property_class" class="form-control" required>
                                                        <option value="">Select Property Class</option>
                                                        <option value="Hall">Hall</option>
                                                        <option value="Commercial">Commercial</option>
                                                        <option value="Residential">Residential</option>
                                                        <option value="Hostel">Hostel</option>
                                                        <option value="Hotel">Hotel</option>
                                                        <option value="Lodge">Lodge</option>
                                                        <option value="Event space">Event space</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Property Size</label>
                                                <div class="col-sm-9">
                                                    <input name="property_size" type="number" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Parking Space</label>
                                                <div class="col-sm-9">
                                                    <select name="parking" class="form-control" required>
                                                        <option value="">Select Parking Type</option>
                                                        <option value="Yes">Yes</option>
                                                        <option value="No">No</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Bedrooms</label>
                                                <div class="col-sm-9">
                                                    <input name="bedrooms" type="number" min="0" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Bathrooms</label>
                                                <div class="col-sm-9">
                                                    <input name="bathrooms" type="number" min="0" step="0.5" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Close Amenities</label>
                                                <div class="col-sm-9">
                                                    <select name="amenities[]" class="form-control select2-multiple" multiple="multiple" required>
                                                        <option value="Market">Market</option>
                                                        <option value="School">School</option>
                                                        <option value="Church / Mosque">Church / Mosque</option>
                                                        <option value="Institution">Institution</option>
                                                        <option value="Hospital">Hospital</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Property Owner</label>
                                                <div class="col-sm-9">
                                                    <select name="owner_id" id="rental_owner_id" class="form-control" required>
                                                        <option value="">Select Owner</option>
                                                        <!-- Options will be populated dynamically -->
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Status</label>
                                                <div class="col-sm-9">
                                                    <select name="status" class="form-control" required>
                                                        <option value="">Select Status</option>
                                                        <option value="Available">Available</option>
                                                        <option value="Unavailable">Unavailable</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Property Manager</label>
                                                <div class="col-sm-9">
                                                    <select name="manager_id" id="rental_manager_id" class="form-control">
                                                        <option value="">Select Manager</option>
                                                        <!-- Options will be populated dynamically -->
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Location Information -->
                                    <h5 class="mb-4">Location Details</h5>
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Country</label>
                                                <div class="col-sm-9">
                                                    <select name="country" id="country" class="form-control" required>
                                                        <option value="">Select Country</option>
                                                        <option value="Uganda">Uganda</option>
                                                        <option value="Kenya">Kenya</option>
                                                        <option value="Tanzania">Tanzania</option>
                                                        <option value="Rwanda">Rwanda</option>
                                                        <option value="Burundi">Burundi</option>
                                                        <option value="South Sudan">South Sudan</option>
                                                        <option value="DR Congo">DR Congo</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label" id="region-label">Region</label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="region" id="region" class="form-control" required placeholder="Enter region" >
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label" id="subregion-label">Sub-Region</label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="subregion" id="subregion" class="form-control" required placeholder="Enter sub-region" >
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label" id="parish-label">Parish</label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="parish" id="parish" class="form-control" required placeholder="Enter parish" >
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label" id="ward-label">Ward</label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="ward" id="ward" class="form-control" required placeholder="Enter ward" >
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label" id="cell-label">Cell</label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="cell" id="cell" class="form-control" required placeholder="Enter cell" >
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Images -->
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Property Images</label>
                                                <div class="col-sm-10">
                                                    <input type="file" name="images[]" id="rental-images" class="form-control" multiple accept="image/*" required />
                                                    <div id="rental-preview-container" class="mt-3 d-flex flex-wrap gap-3">
                                                        <!-- Image previews will be inserted here -->
                                                    </div>
                                                    <small class="text-muted mt-2">You can upload up to 5 images. Maximum size per image: 5MB</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Submit Buttons -->
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary me-3">Submit</button>
                                        <button type="button" class="btn btn-light">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add this after the add-rental-form div -->
        <div id="rental-listing" style="display: none;">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 class="card-title">Rental Properties</h4>
                                    <div class="d-flex gap-2">
                                        <div class="search-box">
                                            <input type="text" class="form-control" placeholder="Search..." data-type="rentals">
                                        </div>
                                        <button class="btn btn-primary">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Property Name</th>
                                                <th>Property Owner</th>
                                                <th>Property Manager</th>
                                                <th>Location</th>
                                                <th>Rent (UGX)</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($rentals)): ?>
                                                <?php foreach ($rentals as $rental): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($rental['property_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($rental['owner_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($rental['manager_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($rental['location']); ?></td>
                                                        <td>UGX <?php echo htmlspecialchars($rental['rent']); ?></td>
                                                        <td>
                                                            <?php echo htmlspecialchars($rental['status']); ?>
                                                        </td>
                                                        <td>
                                                        <div class="btn-group">
                                                            <button class="btn btn-sm btn-outline-primary view-rental"
                                                                    data-id="<?php echo isset($rental['property_id']) ? htmlspecialchars($rental['property_id']) : ''; ?>"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#viewRentalModal">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-warning edit-rental"
                                                                    data-id="<?php echo isset($rental['property_id']) ? htmlspecialchars($rental['property_id']) : ''; ?>"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#editRentalModal">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-danger delete-rental"
                                                                    data-id="<?php echo isset($rental['property_id']) ? htmlspecialchars($rental['property_id']) : ''; ?>"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#deleteRentalModal">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No rental properties found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div class="showing-entries">
                                        Showing <?php echo ($page - 1) * $limit + 1; ?> to <?php echo min($page * $limit, $total_rentals); ?> of <?php echo $total_rentals; ?> entries
                                    </div>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination mb-0">
                                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>" tabindex="-1">Previous</a>
                                            </li>
                                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="sales-listing" style="display: none;">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 class="card-title">Sales Properties</h4>
                                    <div class="d-flex gap-2">
                                        <div class="search-box">
                                            <input type="text" class="form-control" placeholder="Search..." data-type="sales">
                                        </div>
                                        <button class="btn btn-primary">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Property Name</th>
                                                <th>Property Owner</th>
                                                <th>Property Manager</th>
                                                <th>Location</th>
                                                <th>Price (UGX)</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($sales)): ?>
                                                <?php foreach ($sales as $sale): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($sale['property_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($sale['owner_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($sale['manager_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($sale['location']); ?></td>
                                                        <td>UGX <?php echo htmlspecialchars($sale['price']); ?></td>
                                                        <td>
                                                            <?php echo htmlspecialchars($sale['status']); ?>
                                                        </td>
                                                        <td>
                                                        <div class="btn-group">
                                                            <button class="btn btn-sm btn-outline-primary view-sale"
                                                                    data-id="<?php echo isset($sale['property_id']) ? htmlspecialchars($sale['property_id']) : ''; ?>"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#viewSaleModal">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-warning edit-sale"
                                                                    data-id="<?php echo isset($sale['property_id']) ? htmlspecialchars($sale['property_id']) : ''; ?>"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#editSaleModal">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-danger delete-sale"
                                                                    data-id="<?php echo isset($sale['property_id']) ? htmlspecialchars($sale['property_id']) : ''; ?>"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#deleteSaleModal">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No sales properties found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div class="showing-entries">
                                        Showing <?php echo ($page - 1) * $limit + 1; ?> to <?php echo min($page * $limit, $total_sales); ?> of <?php echo $total_sales; ?> entries
                                    </div>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination mb-0">
                                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>" tabindex="-1">Previous</a>
                                            </li>
                                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="owners-listing" style="display: none;">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 class="card-title">Property Owners</h4>
                                    <div class="d-flex gap-2">
                                        <div class="search-box">
                                            <input type="text" class="form-control" placeholder="Search..." data-type="owners">
                                        </div>
                                        <button class="btn btn-primary">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Owner Name</th>
                                                <th>Number of Properties</th>
                                                <th>Subscription Due Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($owners)): ?>
                                                <?php foreach ($owners as $owner): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($owner['owner_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($owner['property_count']); ?></td>
                                                        <td><?php echo htmlspecialchars($owner['email']); ?></td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <button class="btn btn-sm btn-outline-primary view-owner"
                                                                        data-id="<?php echo isset($owner['owner_id']) ? htmlspecialchars($owner['owner_id']) : ''; ?>"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#viewOwnerModal">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-warning edit-owner"
                                                                        data-id="<?php echo isset($owner['owner_id']) ? htmlspecialchars($owner['owner_id']) : ''; ?>"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#editOwnerModal">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-danger delete-owner"
                                                                        data-id="<?php echo isset($owner['owner_id']) ? htmlspecialchars($owner['owner_id']) : ''; ?>"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#deleteOwnerModal">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No property owners found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div class="showing-entries">
                                        Showing <?php echo ($page - 1) * $limit + 1; ?> to <?php echo min($page * $limit, $total_owners); ?> of <?php echo $total_owners; ?> entries
                                    </div>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination mb-0">
                                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>" tabindex="-1">Previous</a>
                                            </li>
                                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Owner Modal -->
        <div class="modal fade" id="viewOwnerModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Owner Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-4">
                                <img id="view-profile-image" src="uploads/owners/default-profile.jpg"
                                    alt="Owner Profile"
                                    class="rounded-circle img-thumbnail"
                                    style="width: 150px; height: 150px; object-fit: cover;">
                            </div>
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>First Name:</strong> <span id="view-first-name"></span></p>
                                        <p><strong>Last Name:</strong> <span id="view-last-name"></span></p>
                                        <p><strong>Email:</strong> <span id="view-email"></span></p>
                                        <p><strong>Phone:</strong> <span id="view-phone"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Username:</strong> <span id="view-username"></span></p>
                                        <p><strong>Properties Owned:</strong> <span id="view-property-count"></span></p>
                                        <p><strong>ID Type:</strong> <span id="view-id-type"></span></p>
                                        <p><strong>ID Number:</strong> <span id="view-id-number"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <p><strong>Address:</strong></p>
                                <p id="view-address"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Owner Modal -->
        <div class="modal fade" id="editOwnerModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Owner</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editOwnerForm" enctype="multipart/form-data">
                            <input type="hidden" id="edit-owner-id" name="owner_id">

                            <!-- Profile Image -->
                            <div class="row mb-4">
                                <div class="col-md-4 text-center">
                                    <div class="profile-image-wrapper">
                                        <img id="edit-profile-preview"
                                            src="uploads/owners/default-profile.jpg"
                                            alt="Profile Picture"
                                            class="rounded-circle img-thumbnail mb-3"
                                            style="width: 150px; height: 150px; object-fit: cover; cursor: pointer;">
                                        <input type="file" id="profile-image-input"
                                            name="profile_picture"
                                            accept="image/*"
                                            style="display: none;">
                                        <button type="button"
                                                class="btn btn-outline-secondary btn-sm"
                                                id="change-profile-btn">
                                            Change Picture
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <!-- Basic Information -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>First Name</label>
                                                <input type="text" class="form-control" id="edit-first-name" name="first_name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Last Name</label>
                                                <input type="text" class="form-control" id="edit-last-name" name="last_name" required>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Contact Information -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Email</label>
                                                <input type="email" class="form-control" id="edit-email" name="email" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Phone</label>
                                                <input type="tel" class="form-control" id="edit-phone" name="phone" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <!-- Account Information -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Username</label>
                                        <input type="text" class="form-control" id="edit-username" name="username" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Subscription Status</label>
                                        <select class="form-control" id="edit-subscription-status" name="subscription_status">
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                            <option value="Pending">Pending</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Identification -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>ID Type</label>
                                        <select class="form-control" id="edit-id-type" name="id_type" required>
                                            <option value="National ID">National ID</option>
                                            <option value="Passport">Passport</option>
                                            <option value="Driver's License">Driver's License</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>ID Number</label>
                                        <input type="text" class="form-control" id="edit-id-number" name="id_num" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label>Address</label>
                                        <textarea class="form-control" id="edit-address" name="address" rows="3" required></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary px-4 py-2" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary px-4 py-2">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Owner Modal -->
        <div class="modal fade" id="deleteOwnerModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this owner?</p>
                        <p class="text-danger">Warning: This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteOwner">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="managers-listing" style="display: none;">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 class="card-title">Property Managers</h4>
                                    <div class="d-flex gap-2">
                                        <div class="search-box">
                                            <input type="text" class="form-control" placeholder="Search..." data-type="managers">
                                        </div>
                                        <button class="btn btn-primary">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Manager Name</th>
                                                <th>Number of Properties Managed</th>
                                                <th>Contact</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($managers)): ?>
                                                <?php foreach ($managers as $manager): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($manager['manager_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($manager['property_count']); ?></td>
                                                        <td><?php echo htmlspecialchars($manager['phone']); ?></td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <button class="btn btn-sm btn-outline-primary view-manager"
                                                                        data-id="<?php echo isset($manager['manager_id']) ? htmlspecialchars($manager['manager_id']) : ''; ?>"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#viewManagerModal">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-warning edit-manager"
                                                                        data-id="<?php echo isset($manager['manager_id']) ? htmlspecialchars($manager['manager_id']) : ''; ?>"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#editManagerModal">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-danger delete-manager"
                                                                        data-id="<?php echo isset($manager['manager_id']) ? htmlspecialchars($manager['manager_id']) : ''; ?>"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#deleteManagerModal">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No property managers found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div class="showing-entries">
                                        Showing <?php echo ($page - 1) * $limit + 1; ?> to <?php echo min($page * $limit, $total_managers); ?> of <?php echo $total_managers; ?> entries
                                    </div>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination mb-0">
                                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>" tabindex="-1">Previous</a>
                                            </li>
                                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Manager Modal -->
        <div class="modal fade" id="viewManagerModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Manager Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-4">
                                <img id="manager-view-profile-image" src="uploads/managers/default-profile.jpg"
                                    alt="Manager Profile"
                                    class="rounded-circle img-thumbnail"
                                    style="width: 150px; height: 150px; object-fit: cover;">
                            </div>
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>First Name:</strong> <span id="manager-view-first-name"></span></p>
                                        <p><strong>Last Name:</strong> <span id="manager-view-last-name"></span></p>
                                        <p><strong>Email:</strong> <span id="manager-view-email"></span></p>
                                        <p><strong>Phone:</strong> <span id="manager-view-phone"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Username:</strong> <span id="manager-view-username"></span></p>
                                        <p><strong>Properties Owned:</strong> <span id="manager-view-property-count"></span></p>
                                        <p><strong>ID Type:</strong> <span id="manager-view-id-type"></span></p>
                                        <p><strong>ID Number:</strong> <span id="manager-view-id-number"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <p><strong>Address:</strong></p>
                                <p id="manager-view-address"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Manager Modal -->
        <div class="modal fade" id="editManagerModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Manager</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editManagerForm" enctype="multipart/form-data">
                            <input type="hidden" id="manager-edit-manager-id" name="manager_id">

                            <!-- Profile Image -->
                            <div class="row mb-4">
                                <div class="col-md-4 text-center">
                                    <div class="profile-image-wrapper">
                                        <img id="manager-edit-profile-preview"
                                            src="uploads/managers/default-profile.jpg"
                                            alt="Profile Picture"
                                            class="rounded-circle img-thumbnail mb-3"
                                            style="width: 150px; height: 150px; object-fit: cover; cursor: pointer;">
                                        <input type="file" id="profile-image-input"
                                            name="profile_picture"
                                            accept="image/*"
                                            style="display: none;">
                                        <button type="button"
                                                class="btn btn-outline-secondary btn-sm"
                                                id="change-profile-btn">
                                            Change Picture
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <!-- Basic Information -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>First Name</label>
                                                <input type="text" class="form-control" id="manager-edit-first-name" name="first_name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Last Name</label>
                                                <input type="text" class="form-control" id="manager-edit-last-name" name="last_name" required>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Contact Information -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Email</label>
                                                <input type="email" class="form-control" id="manager-edit-email" name="email" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Phone</label>
                                                <input type="tel" class="form-control" id="manager-edit-phone" name="phone" placeholder="+256-700000000" required>
                                                <small class="text-muted">Format: +CountryCode-PhoneNumber (e.g., +256-700000000)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <!-- Account Information -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Username</label>
                                        <input type="text" class="form-control" id="manager-edit-username" name="username" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Subscription Status</label>
                                        <select class="form-control" id="manager-edit-subscription-status" name="subscription_status">
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                            <option value="Pending">Pending</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Identification -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>ID Type</label>
                                        <select class="form-control" id="manager-edit-id-type" name="id_type" required>
                                            <option value="National ID">National ID</option>
                                            <option value="Passport">Passport</option>
                                            <option value="Driver's License">Driver's License</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>ID Number</label>
                                        <input type="text" class="form-control" id="manager-edit-id-number" name="id_num" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label>Address</label>
                                        <textarea class="form-control" id="manager-edit-address" name="address" rows="3" required></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary px-4 py-2" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary px-4 py-2">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Manager Modal -->
        <div class="modal fade" id="deleteManagerModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this manager?</p>
                        <p class="text-danger">Warning: This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteManager">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="profile-content" style="display: none;">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 class="card-title">User Profile</h4>
                                    <button class="btn btn-primary" id="edit-profile-btn">Edit Profile</button>
                                </div>
                                <form id="profile-form" class="form-sample" style="display: none;" enctype="multipart/form-data">
                                    <div class="row mb-4">
                                        <div class="col-md-4 text-center">
                                            <div class="profile-picture-wrapper">
                                                <img id="profile-picture-preview"
                                                    src="<?php echo $_SESSION['profile_picture'] ?? 'uploads/profile_picture/default-profile.jpg'; ?>"
                                                    alt="Profile Picture"
                                                    class="rounded-circle img-thumbnail mb-3" style="width: 150px; height: 150px;">
                                                <input type="file" id="profile-picture-input" name="profile_picture" accept="image/*" style="display: none;">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" id="change-picture-btn">Change Picture</button>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">First Name</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" name="first_name" class="form-control" required />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Last Name</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" name="last_name" class="form-control" required />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Email</label>
                                                        <div class="col-sm-9">
                                                            <input type="email" name="email" class="form-control" required />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Phone</label>
                                                        <div class="col-sm-9">
                                                            <input type="tel" name="phone" class="form-control" placeholder="+256-700000000" required />
                                                            <small class="text-muted">Format: +CountryCode-PhoneNumber</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Username</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" name="username" class="form-control" required />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Password Change Section -->
                                            <div class="row mb-4">
                                                <div class="col-12">
                                                    <button type="button" class="btn btn-link ps-0" id="change-password-toggle">
                                                        <i class="fas fa-key"></i> Change Password
                                                    </button>
                                                </div>
                                            </div>

                                            <div id="password-change-fields" style="display: none;">
                                                <div class="row mb-4">
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <label class="col-sm-3 col-form-label">Current Password</label>
                                                            <div class="col-sm-9">
                                                                <input type="password" name="current_password" id="current_password" class="form-control">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mb-4">
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <label class="col-sm-3 col-form-label">New Password</label>
                                                            <div class="col-sm-9">
                                                                <input type="password" name="new_password" id="new_password" class="form-control">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <label class="col-sm-3 col-form-label">Confirm Password</label>
                                                            <div class="col-sm-9">
                                                                <input type="password" name="confirm_password" id="confirm_password" class="form-control">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-success me-3">Save Changes</button>
                                        <button type="button" class="btn btn-light" id="cancel-edit-btn">Cancel</button>
                                    </div>
                                </form>

                                <div id="profile-view">
                                    <div class="row mb-4">
                                    <div class="col-md-4 text-center">
                                        <img src="<?php echo $_SESSION['profile_picture'] ?? 'uploads/profile_picture/default-profile.jpg'; ?>"
                                            alt="Profile Picture"
                                            class="rounded-circle img-thumbnail profile-picture"
                                            style="width: 150px; height: 150px;">
                                        </div>
                                        <div class="col-md-8">
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <p><strong>First Name:</strong> <span data-field="first_name"></span></p>
                                                    <p><strong>Last Name:</strong> <span data-field="last_name"></span></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Email:</strong> <span data-field="email"></span></p>
                                                    <p><strong>Phone:</strong> <span data-field="phone"></span></p>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Username:</strong> <span data-field="username"></span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- View Rental Modal -->
    <div class="modal fade" id="viewRentalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rental Property Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Image Gallery -->
                    <div id="rentalImageCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <!-- Images will be inserted here -->
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#rentalImageCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#rentalImageCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>
                    <div class="property-thumbnails d-flex gap-2 mb-4 overflow-auto">
                        <!-- Thumbnails will be inserted here -->
                    </div>

                    <!-- Property Information -->
                    <div class="rental-details">
                        <h4 class="property-name mb-3"></h4>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Price:</strong> $<span class="property-price"></span></p>
                                <p><strong>Property Type:</strong> <span class="property-type"></span></p>
                                <p><strong>Property Class:</strong> <span class="property-class"></span></p>
                                <p><strong>Size:</strong> <span class="property-size"></span></p>
                                <p><strong>Location:</strong> <span class="property-location"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Owner:</strong> <span class="property-owner"></span></p>
                                <p><strong>Manager:</strong> <span class="property-manager"></span></p>
                                <p><strong>Utilities:</strong> <span class="property-utilities"></span></p>
                                <p><strong>Amenities:</strong> <span class="property-amenities"></span></p>
                                <p><strong>Security:</strong> <span class="property-security"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Rental Modal -->
    <div class="modal fade" id="editRentalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Rental Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editRentalForm" enctype="multipart/form-data">
                        <input type="hidden" name="rental_id" id="edit-rental-id">

                        <!-- Property Images -->
                        <div class="mb-4">
                            <label class="form-label">Current Images</label>
                            <div id="rental-current-images" class="d-flex gap-2 flex-wrap">
                                <!-- Existing images will be loaded here -->
                            </div>
                            <div class="mt-3">
                                <label class="form-label">Add New Images</label>
                                <input type="file" class="form-control" name="new_images[]" multiple accept="image/*">
                                <div id="rental-new-images-preview" class="mt-2 d-flex gap-2 flex-wrap">
                                    <!-- New image previews will be shown here -->
                                </div>
                                <small class="text-muted">You can upload up to 5 images. Maximum size per image: 5MB</small>
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Property Name</label>
                                    <div class="col-sm-9">
                                        <input name="property_name" type="text" class="form-control" required />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Price</label>
                                    <div class="col-sm-9">
                                        <input name="price" type="number" class="form-control" required />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Property Details -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Landlord</label>
                                    <div class="col-sm-9">
                                        <select name="landlord" class="form-control" required>
                                            <option value="">Select Landlord</option>
                                            <option value="Resident">Resident</option>
                                            <option value="Non resident">Non resident</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Security</label>
                                    <div class="col-sm-9">
                                        <select name="security[]" class="form-control select2-multiple" multiple="multiple">
                                            <option value="CCTV">CCTV</option>
                                            <option value="Security Guards">Security Guards</option>
                                            <option value="Electric Fence">Electric Fence</option>
                                            <option value="Alarm System">Alarm System</option>
                                            <option value="Security Lights">Security Lights</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Property Details -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Utilities</label>
                                    <div class="col-sm-9">
                                        <select name="utilities" class="form-control" required>
                                            <option value="">Select Utilities</option>
                                            <option value="Included">Included</option>
                                            <option value="Not Included">Not Included</option>
                                            <option value="Partially Included">Partially Included</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Property Type</label>
                                    <div class="col-sm-9">
                                        <select name="property_type" class="form-control" required>
                                            <option value="">Select Property Type</option>
                                            <option value="Flat">Flat</option>
                                            <option value="Permanent">Permanent</option>
                                            <option value="Semi-permanent">Semi-permanent</option>
                                            <option value="Grounds">Grounds</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Property Features -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Property Size</label>
                                    <div class="col-sm-9">
                                        <input name="property_size" type="number" class="form-control" required />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Parking Space</label>
                                    <div class="col-sm-9">
                                        <select name="parking" class="form-control" required>
                                            <option value="">Select Parking Type</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bedrooms and Bathrooms -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Bedrooms</label>
                                    <div class="col-sm-9">
                                        <input name="bedrooms" type="number" min="0" class="form-control" required />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Bathrooms</label>
                                    <div class="col-sm-9">
                                        <input name="bathrooms" type="number" min="0" step="0.5" class="form-control" required />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Description</label>
                                    <div class="col-sm-10">
                                        <textarea name="description" class="form-control" rows="4" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Property Classification -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Convenience</label>
                                    <div class="col-sm-9">
                                        <select name="convenience" class="form-control" required>
                                            <option value="">Select Convenience Level</option>
                                            <option value="Crowded">Crowded</option>
                                            <option value="Private">Private</option>
                                            <option value="Self contained">Self contained</option>
                                            <option value="Non self contained">Non self contained</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Property Class</label>
                                    <div class="col-sm-9">
                                        <select name="property_class" class="form-control" required>
                                            <option value="">Select Property Class</option>
                                            <option value="Hall">Hall</option>
                                            <option value="Commercial">Commercial</option>
                                            <option value="Residential">Residential</option>
                                            <option value="Hostel">Hostel</option>
                                            <option value="Hotel">Hotel</option>
                                            <option value="Lodge">Lodge</option>
                                            <option value="Event space">Event space</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Property Management -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Close Amenities</label>
                                    <div class="col-sm-9">
                                        <select name="amenities[]" class="form-control select2-multiple" multiple="multiple" required>
                                            <option value="Market">Market</option>
                                            <option value="School">School</option>
                                            <option value="Church">Church / Mosque</option>
                                            <option value="Institution">Institution</option>
                                            <option value="Hospital">Hospital</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Property Owner</label>
                                    <div class="col-sm-9">
                                        <select name="owner_id" id="rental_owner_id" class="form-control" required>
                                            <option value="">Select Owner</option>
                                            <!-- Options will be populated dynamically -->
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Status</label>
                                    <div class="col-sm-9">
                                        <select name="status" class="form-control" required>
                                            <option value="">Select Status</option>
                                            <option value="Available">Available</option>
                                            <option value="Unavailable">Unavailable</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Property Manager</label>
                                    <div class="col-sm-9">
                                        <select name="manager_id" id="rental_manager_id" class="form-control">
                                            <option value="">Select Manager</option>
                                            <!-- Options will be populated dynamically -->
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <h5 class="mb-4">Location Details</h5>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Country</label>
                                    <div class="col-sm-9">
                                        <select name="country" class="form-control" required>
                                            <option value="">Select Country</option>
                                            <option value="Uganda">Uganda</option>
                                            <option value="Kenya">Kenya</option>
                                            <option value="Tanzania">Tanzania</option>
                                            <option value="Rwanda">Rwanda</option>
                                            <option value="Burundi">Burundi</option>
                                            <option value="South Sudan">South Sudan</option>
                                            <option value="DR Congo">DR Congo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Region</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="region" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Sub-Region</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="subregion" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Parish</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="parish" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Ward</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="ward" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Cell</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="cell" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Rental Modal -->
    <div class="modal fade" id="deleteRentalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Rental Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this rental property?</p>
                    <div class="preview-container mb-3">
                        <img id="delete-rental-preview" src="" alt="Property" class="img-thumbnail" style="max-width: 200px;">
                        <p class="mt-2"><strong>Property Name: </strong><span id="delete-rental-name"></span></p>
                    </div>
                    <p class="text-danger">Warning: This action cannot be undone and will delete all associated images.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteRental">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Sale Modal -->
    <div class="modal fade" id="viewSaleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sale Property Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Image Gallery -->
                    <div id="saleImageCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <!-- Images will be inserted here -->
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#saleImageCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#saleImageCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>
                    <div class="property-thumbnails d-flex gap-2 mb-4 overflow-auto">
                        <!-- Thumbnails will be inserted here -->
                    </div>

                    <!-- Property Information -->
                    <div class="sale-details">
                        <h4 class="property-name mb-3"></h4>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Price:</strong> $<span class="property-price"></span></p>
                                <p><strong>Property Type:</strong> <span class="property-type"></span></p>
                                <p><strong>Title:</strong> <span class="property-title"></span></p>
                                <p><strong>Size:</strong> <span class="property-size"></span></p>
                                <p><strong>Location:</strong> <span class="property-location"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Owner:</strong> <span class="property-owner"></span></p>
                                <p><strong>Manager:</strong> <span class="property-manager"></span></p>
                                <p><strong>Utilities:</strong> <span class="property-utilities"></span></p>
                                <p><strong>Amenities:</strong> <span class="property-amenities"></span></p>
                                <p><strong>Payment Method:</strong> <span class="property-payment"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Sale Modal -->
    <div class="modal fade" id="editSaleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Sale Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editSaleForm" enctype="multipart/form-data">
                        <input type="hidden" name="sale_id" id="edit-sale-id">

                        <!-- Property Images -->
                        <div class="mb-4">
                            <label class="form-label">Current Images</label>
                            <div id="sale-current-images" class="d-flex gap-2 flex-wrap">
                                <!-- Existing images will be loaded here -->
                            </div>
                            <div class="mt-3">
                                <label class="form-label">Add New Images</label>
                                <input type="file" class="form-control" name="new_images[]" multiple accept="image/*">
                                <div id="sale-new-images-preview" class="mt-2 d-flex gap-2 flex-wrap">
                                    <!-- New image previews will be shown here -->
                                </div>
                                <small class="text-muted">You can upload up to 5 images. Maximum size per image: 5MB</small>
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Property Name</label>
                                    <div class="col-sm-9">
                                        <input name="property_name" type="text" class="form-control" required />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Price</label>
                                    <div class="col-sm-9">
                                        <select name="price" class="form-control" required>
                                            <option value="">Select Option</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Installments">Insatllments</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Property Details -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Title</label>
                                    <div class="col-sm-9">
                                        <select name="title" class="form-control" required>
                                            <option value="">Select Option</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Utilities</label>
                                    <div class="col-sm-9">
                                        <select name="utilities" class="form-control" required>
                                            <option value="">Select Utilities</option>
                                            <option value="Included">Included</option>
                                            <option value="Not Included">Not Included</option>
                                            <option value="Partially Included">Partially Included</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Property Type</label>
                                    <div class="col-sm-9">
                                        <select name="property_type" class="form-control" required>
                                            <option value="">Select Property Type</option>
                                            <option value="Flat">Flat</option>
                                            <option value="Permanent">Permanent</option>
                                            <option value="Semi-permanent">Semi-permanent</option>
                                            <option value="Grounds">Grounds</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Property Size</label>
                                    <div class="col-sm-9">
                                        <input name="property_size" type="number" class="form-control" required />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bedrooms and Bathrooms -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Bedrooms</label>
                                    <div class="col-sm-9">
                                        <input name="bedrooms" type="number" min="0" class="form-control" required />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Bathrooms</label>
                                    <div class="col-sm-9">
                                        <input name="bathrooms" type="number" min="0" step="0.5" class="form-control" required />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Description</label>
                                    <div class="col-sm-10">
                                        <textarea name="description" class="form-control" rows="4" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Property Features -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Close Amenities</label>
                                    <div class="col-sm-9">
                                        <select name="amenities[]" class="form-control select2-multiple" multiple="multiple" required>
                                            <option value="Market">Market</option>
                                            <option value="School">School</option>
                                            <option value="Church">Church / Mosque</option>
                                            <option value="Institution">Institution</option>
                                            <option value="Hospital">Hospital</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Property Manager</label>
                                    <div class="col-sm-9">
                                        <select name="manager_id" id="rental_manager_id" class="form-control">
                                            <option value="">Select Manager</option>
                                            <!-- Options will be populated dynamically -->
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Property Management -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Status</label>
                                    <div class="col-sm-9">
                                        <select name="status" class="form-control" required>
                                            <option value="">Select Status</option>
                                            <option value="Available">Available</option>
                                            <option value="Unavailable">Unavailable</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Property Owner</label>
                                    <div class="col-sm-9">
                                        <select name="owner_id" id="rental_owner_id" class="form-control" required>
                                            <option value="">Select Owner</option>
                                            <!-- Options will be populated dynamically -->
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <h5 class="mb-4">Location Details</h5>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Country</label>
                                    <div class="col-sm-9">
                                        <select name="country" class="form-control" required>
                                            <option value="">Select Country</option>
                                            <option value="Uganda">Uganda</option>
                                            <option value="Kenya">Kenya</option>
                                            <option value="Tanzania">Tanzania</option>
                                            <option value="Rwanda">Rwanda</option>
                                            <option value="Burundi">Burundi</option>
                                            <option value="South Sudan">South Sudan</option>
                                            <option value="DR Congo">DR Congo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Region</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="region" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Sub-Region</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="subregion" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Parish</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="parish" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Ward</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="ward" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Cell</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="cell" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Sale Modal -->
    <div class="modal fade" id="deleteSaleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Sale Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this sale property?</p>
                    <div class="preview-container mb-3">
                        <img id="delete-sale-preview" src="" alt="Property" class="img-thumbnail" style="max-width: 200px;">
                        <p class="mt-2"><strong>Property Name: </strong><span id="delete-sale-name"></span></p>
                    </div>
                    <p class="text-danger">Warning: This action cannot be undone and will delete all associated images.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteSale">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> -->
    <!-- Custom alerts must be loaded first to override alert() function -->
    <script src="assets/js/custom-alerts.js"></script>
    <script src="assets/js/admin-dashboard.js"></script>
    <script src="assets/js/register.js"></script>
    <script src="assets/js/search.js"></script>
    <script src="assets/js/dashboard-search.js"></script>
    <script src="assets/js/profile.js"></script>
    <script src="assets/js/profile-common.js"></script>
    <script src="assets/js/activity.js"></script>
    <script src="assets/js/imageupload.js"></script>
    <!-- <script src="assets/js/property-management.js"></script> -->
    <script src="assets/js/owner-management.js"></script>
    <script src="assets/js/manager-management.js"></script>
    <link rel="stylesheet" href="assets/css/modern-style.css">
    <script src="assets/js/property-images.js"></script>
    <script src="assets/js/rental-management.js"></script>
    <script src="assets/js/sales-management.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2-multiple').select2({
                placeholder: 'Select security features',
                allowClear: true,
                closeOnSelect: false,
                width: '100%',
                templateResult: function(data) {
                    if (!data.id) return data.text;

                    return $('<div><input type="checkbox" ' + (data.selected ? 'checked' : '') + '/> ' + data.text + '</div>');
                },
                templateSelection: function(data) {
                    if (!data.id) return data.text;
                    return data.text;
                }
            });

            // Update charts with real data
            // Income Statistics Chart
            if (typeof propertyChart !== 'undefined') {
                // Convert to bar chart for income data
                propertyChart.config.type = 'bar';

                // Set up datasets for different income sources
                propertyChart.data.datasets = [
                    {
                        label: 'Rental Income',
                        data: [<?php echo $total_rental_income; ?>],
                        backgroundColor: '#ff9800',
                        borderColor: '#e68a00',
                        borderWidth: 1
                    },
                    {
                        label: 'Sales Income',
                        data: [<?php echo $total_sales_income; ?>],
                        backgroundColor: '#e91e63',
                        borderColor: '#c2185b',
                        borderWidth: 1
                    },
                    {
                        label: 'Subscription Income',
                        data: [<?php echo $total_subscription_income; ?>],
                        backgroundColor: '#673ab7',
                        borderColor: '#512da8',
                        borderWidth: 1
                    }
                ];

                propertyChart.data.labels = ['Income Sources'];

                // Update chart options
                propertyChart.options = {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'UGX ' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += 'UGX ' + context.raw.toLocaleString();
                                    return label;
                                }
                            }
                        }
                    }
                };

                propertyChart.update();

                // Add event listener for period selector
                $('#income-period-selector').on('change', function() {
                    // In a real application, this would fetch data for the selected period
                    // For now, we'll just show a message
                    alert('This would fetch data for the ' + $(this).val() + ' period in a real application.');
                });
            }

            // Property Distribution Chart
            if (typeof distributionChart !== 'undefined') {
                distributionChart.data.datasets[0].data = [
                    <?php echo $sales_summary['total_sales']; ?>,
                    <?php echo $rental_summary['total_rentals']; ?>,
                    <?php echo $subscription_summary['total_subscriptions']; ?>
                ];
                distributionChart.data.labels = ['Sales Properties', 'Rental Properties', 'Subscriptions'];
                distributionChart.data.datasets[0].backgroundColor = ['#e91e63', '#ff9800', '#673ab7'];

                // Update chart options
                distributionChart.options = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                };

                distributionChart.update();
            }
        });
    </script>

    <!-- Report Selection Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel">Generate Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Select the type of report you want to generate:</p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary report-type-btn" data-report-type="rentals">
                            <i class="fas fa-home me-2"></i>Rental Properties Report
                        </button>
                        <button class="btn btn-success report-type-btn" data-report-type="sales">
                            <i class="fas fa-money-bill-wave me-2"></i>Sales Properties Report
                        </button>
                        <button class="btn btn-info report-type-btn" data-report-type="managers">
                            <i class="fas fa-user-tie me-2"></i>Property Managers Report
                        </button>
                        <button class="btn btn-warning report-type-btn" data-report-type="owners">
                            <i class="fas fa-users me-2"></i>Property Owners Report
                        </button>
                        <button class="btn btn-secondary report-type-btn" data-report-type="all">
                            <i class="fas fa-file-alt me-2"></i>Comprehensive Report (All)
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
