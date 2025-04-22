<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'owner') {
    header('Location: login.html');
    exit();
}

// Include database connection
require_once 'config.php';

// Initialize variables
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10; // Number of entries per page
$offset = ($page - 1) * $limit;

// Create connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get owner details
$owner_id = $_SESSION['user_id'];
$owner_query = "SELECT * FROM property_owner WHERE owner_id = ?";
$stmt = $conn->prepare($owner_query);
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$owner_result = $stmt->get_result();
$owner_data = $owner_result->fetch_assoc();
$stmt->close();

// Set profile picture
$profile_pic = !empty($owner_data['profile_picture']) ? $owner_data['profile_picture'] : 'assets/images/profile.jpg';

// Fetch rentals data with pagination
try {
    // Get total count of rentals for this owner
    $count_query = "SELECT COUNT(*) as total FROM rental_property WHERE owner_id = ?";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param("i", $owner_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_rentals = $count_result->fetch_assoc()['total'];
    $total_pages_rentals = ceil($total_rentals / $limit);
    $count_stmt->close();

    // Get rentals with pagination
    $query = "SELECT r.*,
                CONCAT(m.first_name, ' ', m.last_name) as manager_name,
                CONCAT(parish, ', ', ward) AS location,
                price AS rent,
                status
                FROM rental_property r
                LEFT JOIN property_manager m ON r.manager_id = m.manager_id
                WHERE r.owner_id = ?
                LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('iii', $owner_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $rentals = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $rentals = [];
    $total_rentals = 0;
    $total_pages_rentals = 1;
}

// Fetch sales data with pagination
try {
    // Get total count of sales for this owner
    $count_query = "SELECT COUNT(*) as total FROM sales_property WHERE owner_id = ?";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param("i", $owner_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_sales = $count_result->fetch_assoc()['total'];
    $total_pages_sales = ceil($total_sales / $limit);
    $count_stmt->close();

    // Get sales with pagination
    $query = "SELECT s.*,
                CONCAT(m.first_name, ' ', m.last_name) as manager_name,
                CONCAT(parish, ', ', ward) AS location,
                price,
                status
                FROM sales_property s
                LEFT JOIN property_manager m ON s.manager_id = m.manager_id
                WHERE s.owner_id = ?
                LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('iii', $owner_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $sales = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $sales = [];
    $total_sales = 0;
    $total_pages_sales = 1;
}

// Fetch managers data with pagination
try {
    // Get total count of managers
    $count_query = "SELECT COUNT(*) as total FROM property_manager m
                    INNER JOIN rental_property r ON m.manager_id = r.manager_id
                    WHERE r.owner_id = ?
                    GROUP BY m.manager_id";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param("i", $owner_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_managers = $count_result->num_rows;
    $total_pages_managers = ceil($total_managers / $limit);
    $count_stmt->close();

    // Get managers with pagination
    $query = "SELECT m.*,
                (SELECT COUNT(*) FROM rental_property WHERE manager_id = m.manager_id AND owner_id = ?) as property_count,
                m.username AS manager_name
                FROM property_manager m
                INNER JOIN rental_property r ON m.manager_id = r.manager_id
                WHERE r.owner_id = ?
                GROUP BY m.manager_id
                LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('iiii', $owner_id, $owner_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $managers = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $managers = [];
    $total_managers = 0;
    $total_pages_managers = 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Owner Dashboard - ALLEA Properties</title>
    <link rel="icon" href="logo1.ico" sizes="any">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/owner-dashboard.css">
    <link rel="stylesheet" href="assets/css/custom-loader.css">
    <link rel="stylesheet" href="assets/css/dashboard-responsive.css">
    <link rel="stylesheet" href="assets/css/sidebar-fix.css">
    <link rel="stylesheet" href="assets/css/mobile-sidebar-fix.css">
    <link rel="stylesheet" href="assets/css/responsive-tables.css">
    <link rel="stylesheet" href="assets/css/submenu-click-fix.css">
    <link rel="stylesheet" href="assets/css/direct-submenu-fix.css">
    <link rel="stylesheet" href="assets/css/profile-styles.css">

    <!-- JavaScript files with defer attribute -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>
    <script src="assets/js/custom-loader.js" defer></script>
    <!-- Custom alerts must be loaded first to override alert() function -->
    <script src="assets/js/custom-alerts.js" defer></script>
    <script src="assets/js/dashboard-functions.js" defer></script>
    <script src="assets/js/owner-dashboard-new.js" defer></script>
    <script src="assets/js/register.js" defer></script>
    <script src="assets/js/imageupload.js" defer></script>
    <script src="assets/js/dashboard-search.js" defer></script>
    <script src="assets/js/profile.js" defer></script>
    <script src="assets/js/profile-common.js" defer></script>
    <script src="assets/js/search.js" defer></script>
    <script src="assets/js/property-images.js"></script>
    <script src="assets/js/rental-management.js" defer></script>
    <script src="assets/js/sales-management.js" defer></script>
    <script src="assets/js/owner-manager-handlers.js" defer></script>
    <script src="assets/js/responsive-dashboard.js" defer></script>
    <script src="assets/js/responsive-tables.js" defer></script>
    <script src="assets/js/mobile-sidebar.js" defer></script>
    <script src="assets/js/sidebar-toggle-fix.js" defer></script>
    <script src="assets/js/mobile-submenu-fix.js" defer></script>
    <script src="assets/js/submenu-click-fix.js" defer></script>
    <script src="assets/js/direct-submenu-fix.js" defer></script>
    <script src="assets/js/report-generator.js" defer></script>

    <!-- Ensure critical sections are loaded -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Checking critical sections in ownerDashboard.php');
            console.log('Manager buttons should be handled by owner-manager-handlers.js');
            setTimeout(function() {
                // Check if managers-listing exists and is accessible
                const managersListing = document.getElementById('managers-listing');
                console.log('managers-listing direct check:', !!managersListing);

                // Check if profile-content exists and is accessible
                const profileContent = document.getElementById('profile-content');
                console.log('profile-content direct check:', !!profileContent);

                // Add direct event listeners
                const profileLink = document.getElementById('profile-link');
                if (profileLink) {
                    profileLink.addEventListener('click', function(e) {
                        e.preventDefault();
                        console.log('Profile link clicked directly');
                        window.showProfile();
                    });
                }

                const managersListingLink = document.getElementById('managers-listing-link');
                if (managersListingLink) {
                    managersListingLink.addEventListener('click', function(e) {
                        e.preventDefault();
                        console.log('Managers listing link clicked directly');
                        window.showManagersListing();
                    });
                }

                const managersCardLink = document.getElementById('managers-card-link');
                if (managersCardLink) {
                    managersCardLink.addEventListener('click', function(e) {
                        e.preventDefault();
                        console.log('Managers card link clicked directly');
                        window.showManagersListing();
                    });
                }
            }, 500);
        });
    </script>
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
                            <img src="<?php echo $_SESSION['profile_picture'] ?? 'uploads/profile_picture/default-profile.jpg'; ?>" alt="Profile" class="rounded-circle profile-picture" onclick="showProfile(); return false;">
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
            <a href="#" onclick="document.getElementById('profile-link').click(); return false;">
                <img src="<?php echo $profile_pic; ?>" alt="Profile" class="profile-picture">
            </a>
            <div class="profile-name"><?php echo $owner_data['first_name'] . ' ' . $owner_data['last_name']; ?></div>
            <div class="profile-role">Property Owner</div>
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
                    <i class="fas fa-user-cog"></i>
                    <span>Manage Managers</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="#" class="submenu-link" data-form="managers-listing" id="managers-listing-link">
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
                <div class="row" style="justify-content: space-between;">

                    <!-- Total Managers Card -->
                    <div class="col-md-6 col-xl-4">
                        <div class="summary-card">
                            <div class="summary-content">
                                <div class="summary-icon bg-success">
                                    <i class="fas fa-user-cog"></i>
                                </div>
                                <div class="summary-details">
                                    <h3 class="summary-title">Total Managers</h3>
                                    <p class="summary-number"><?php echo htmlspecialchars($total_managers); ?></p>
                                    <p class="summary-trend positive">
                                        <i class="fas fa-arrow-up"></i> 8% from last month
                                    </p>
                                </div>
                            </div>
                            <div class="summary-footer">
                                <a href="#" class="view-details" id="managers-card-link">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Total Rentals Card -->
                    <div class="col-md-6 col-xl-4">
                        <div class="summary-card">
                            <div class="summary-content">
                                <div class="summary-icon bg-warning">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="summary-details">
                                    <h3 class="summary-title">Total Rentals</h3>
                                    <p class="summary-number"><?php echo htmlspecialchars($total_rentals); ?></p>
                                    <p class="summary-trend positive">
                                        <i class="fas fa-arrow-up"></i> 15% from last month
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

                    <!-- Total Sales Card -->
                    <div class="col-md-6 col-xl-4">
                        <div class="summary-card">
                            <div class="summary-content">
                                <div class="summary-icon bg-danger">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="summary-details">
                                    <h3 class="summary-title">Total Sales</h3>
                                    <p class="summary-number"><?php echo htmlspecialchars($total_sales); ?></p>
                                    <p class="summary-trend positive">
                                        <i class="fas fa-arrow-up"></i> 10% from last month
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
                </div>
                <!-- Property Statistics -->
                <div class="row">
                    <div class="col-xl-8">
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h3>Property Statistics</h3>
                                <div class="card-actions">
                                    <select class="form-select form-select-sm">
                                        <option>Last 7 days</option>
                                        <option>Last 30 days</option>
                                        <option>Last 90 days</option>
                                    </select>
                                </div>
                            </div>
                            <div class="dashboard-card-body">
                                <canvas id="propertyChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h3>Property Distribution</h3>
                            </div>
                            <div class="dashboard-card-body">
                                <canvas id="distributionChart" height="300"></canvas>
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
                                <button class="btn btn-sm btn-light">View All</button>
                            </div>
                            <div class="dashboard-card-body p-0">
                                <div class="activity-list">
                                    <div class="activity-item">
                                        <div class="activity-icon bg-soft-primary">
                                            <i class="fas fa-plus-circle"></i>
                                        </div>
                                        <div class="activity-content">
                                            <h4>New Property Added</h4>
                                            <p>3 bedroom house in Wakiso</p>
                                            <span class="activity-time">2 hours ago</span>
                                        </div>
                                        <div class="activity-status">
                                            <span class="badge bg-success">New</span>
                                        </div>
                                    </div>
                                    <div class="activity-item">
                                        <div class="activity-icon bg-soft-warning">
                                            <i class="fas fa-user-plus"></i>
                                        </div>
                                        <div class="activity-content">
                                            <h4>New Owner Registered</h4>
                                            <p>John Doe</p>
                                            <span class="activity-time">5 hours ago</span>
                                        </div>
                                    </div>
                                    <div class="activity-item">
                                        <div class="activity-icon bg-soft-success">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="activity-content">
                                            <h4>Property Verified</h4>
                                            <p>2 bedroom apartment in Kira</p>
                                            <span class="activity-time">1 day ago</span>
                                        </div>
                                    </div>
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
                                    <a href="#" class="quick-action-btn" onclick="showAddRental()">
                                        <i class="fas fa-plus"></i>
                                        <span>Add Property</span>
                                    </a>
                                    <a href="#" class="quick-action-btn" onclick="showCreateManager()">
                                        <i class="fas fa-user-plus"></i>
                                        <span>Add Managers</span>
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
                                                <label class="form-check-label">Manager Registration</label>
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
                                                    <input name="phone" type="tel" class="form-control" required placeholder="+code-0000000"/>
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
                                                <th>Property Manager</th>
                                                <th>Location</th>
                                                <th>Rent (UGX)</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($rentals)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No rental properties found</td>
                                            </tr>
                                            <?php else: ?>
                                            <?php foreach ($rentals as $rental): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($rental['property_name']); ?></td>
                                                <td><?php echo htmlspecialchars($rental['manager_name'] ?? 'Not Assigned'); ?></td>
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
                                            <?php endif; ?>
                                            <!-- Add more rows as needed -->
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
                                            <?php for ($i = 1; $i <= $total_pages_rentals; $i++): ?>
                                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?php echo $page >= $total_pages_rentals ? 'disabled' : ''; ?>">
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
                                                <th>Property Manager</th>
                                                <th>Location</th>
                                                <th>Price (UGX)</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($sales)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No sales properties found</td>
                                            </tr>
                                            <?php else: ?>
                                            <?php foreach ($sales as $sale): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($sale['property_name']); ?></td>
                                                <td><?php echo htmlspecialchars($sale['manager_name'] ?? 'Not Assigned'); ?></td>
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
                                            <?php endif; ?>
                                            <!-- Add more rows as needed -->
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
                                            <?php for ($i = 1; $i <= $total_pages_sales; $i++): ?>
                                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?php echo $page >= $total_pages_sales ? 'disabled' : ''; ?>">
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
                                            <tr>
                                                <td>John Doe</td>
                                                <td>5</td>
                                                <td>2025-12-31</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Jane Smith</td>
                                                <td>3</td>
                                                <td>2025-11-15</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- Add more rows as needed -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div class="showing-entries">
                                        Showing 1 to 10 of 20 entries
                                    </div>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination mb-0">
                                            <li class="page-item disabled">
                                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                                            </li>
                                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                                            <li class="page-item">
                                                <a class="page-link" href="#">Next</a>
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

        <!-- Managers Listing Section -->
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
                                            <?php if (empty($managers)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No property managers found</td>
                                                </tr>
                                            <?php else: ?>
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
                                                                        data-bs-target="#viewManagerModal"
                                                                        onclick="console.log('View manager button clicked with ID: <?php echo isset($manager['manager_id']) ? htmlspecialchars($manager['manager_id']) : 'none'; ?>')">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-warning edit-manager"
                                                                        data-id="<?php echo isset($manager['manager_id']) ? htmlspecialchars($manager['manager_id']) : ''; ?>"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#editManagerModal"
                                                                        onclick="console.log('Edit manager button clicked with ID: <?php echo isset($manager['manager_id']) ? htmlspecialchars($manager['manager_id']) : 'none'; ?>')">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-danger delete-manager"
                                                                        data-id="<?php echo isset($manager['manager_id']) ? htmlspecialchars($manager['manager_id']) : ''; ?>"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#deleteManagerModal"
                                                                        onclick="console.log('Delete manager button clicked with ID: <?php echo isset($manager['manager_id']) ? htmlspecialchars($manager['manager_id']) : 'none'; ?>')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
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
                                            <?php for ($i = 1; $i <= $total_pages_managers; $i++): ?>
                                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?php echo $page >= $total_pages_managers ? 'disabled' : ''; ?>">
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
                                <form id="profile-form" class="form-sample" action="update_owner_profile.php" method="post" enctype="multipart/form-data" style="display: none;">
                                    <input type="hidden" name="owner_id" value="<?php echo $owner_data['owner_id']; ?>">
                                    <div class="row mb-4">
                                        <div class="col-md-4 text-center">
                                            <div class="profile-picture-wrapper">
                                                <?php
                                                $profile_pic = !empty($owner_data['profile_picture']) ? $owner_data['profile_picture'] : 'assets/images/profile.jpg';
                                                ?>
                                                <img id="profile-picture-preview" src="<?php echo $profile_pic; ?>" alt="Profile Picture" class="rounded-circle img-thumbnail mb-3" style="width: 150px; height: 150px;">
                                                <input type="file" id="profile-picture-input" name="profile_picture" class="form-control" accept="image/*" style="display: none;">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" id="change-picture-btn">Change Picture</button>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">First Name</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($owner_data['first_name']); ?>" required />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Last Name</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($owner_data['last_name']); ?>" required />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Email</label>
                                                        <div class="col-sm-9">
                                                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($owner_data['email']); ?>" required />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Phone</label>
                                                        <div class="col-sm-9">
                                                            <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($owner_data['phone']); ?>" placeholder="+256-700000000" required />
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
                                                            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($owner_data['username']); ?>" required />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <button type="button" class="btn btn-link ps-0" id="change-password-toggle">
                                                            <i class="fas fa-key"></i> Change Password
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Password Change Fields -->
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
                                            <img src="<?php echo $profile_pic; ?>" alt="Profile Picture" class="rounded-circle img-thumbnail" style="width: 150px; height: 150px;">
                                        </div>
                                        <div class="col-md-8">
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <p><strong>First Name:</strong> <?php echo htmlspecialchars($owner_data['first_name']); ?></p>
                                                    <p><strong>Last Name:</strong> <?php echo htmlspecialchars($owner_data['last_name']); ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($owner_data['email']); ?></p>
                                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($owner_data['phone']); ?></p>
                                                </div>
                                            </div>
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <p><strong>Username:</strong> <?php echo htmlspecialchars($owner_data['username']); ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Password:</strong> ********</p>
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

    <!-- Modals for View, Edit, Delete functionality -->

    <!-- View Rental Modal -->
    <div class="modal fade" id="viewRentalModal" tabindex="-1" aria-labelledby="viewRentalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewRentalModalLabel">Rental Property Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Content will be dynamically loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Rental Modal -->
    <div class="modal fade" id="editRentalModal" tabindex="-1" aria-labelledby="editRentalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRentalModalLabel">Edit Rental Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editRentalForm" enctype="multipart/form-data">
                        <input type="hidden" name="rental_id" value="">
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
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Rental Modal -->
    <div class="modal fade" id="deleteRentalModal" tabindex="-1" aria-labelledby="deleteRentalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteRentalModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this rental property? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger confirm-delete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Sale Modal -->
    <div class="modal fade" id="viewSaleModal" tabindex="-1" aria-labelledby="viewSaleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewSaleModalLabel">Sale Property Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Content will be dynamically loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Sale Modal -->
    <div class="modal fade" id="editSaleModal" tabindex="-1" aria-labelledby="editSaleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSaleModalLabel">Edit Sale Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editSaleForm" enctype="multipart/form-data">
                        <input type="hidden" name="sale_id" value="">
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
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Sale Modal -->
    <div class="modal fade" id="deleteSaleModal" tabindex="-1" aria-labelledby="deleteSaleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteSaleModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this sale property? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger confirm-delete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Manager Modal -->
    <div class="modal fade" id="viewManagerModal" tabindex="-1" aria-labelledby="viewManagerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewManagerModalLabel">Manager Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Content will be dynamically loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Manager Modal -->
    <div class="modal fade" id="editManagerModal" tabindex="-1" aria-labelledby="editManagerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editManagerModalLabel">Edit Manager</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editManagerForm" enctype="multipart/form-data">
                        <input type="hidden" name="manager_id" value="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control" id="phone" name="phone" placeholder="+256-700000000">
                                    <small class="text-muted">Format: +CountryCode-PhoneNumber (e.g., +256-700000000)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_type" class="form-label">ID Type</label>
                                    <select class="form-select" id="id_type" name="id_type">
                                        <option value="National ID">National ID</option>
                                        <option value="Passport">Passport</option>
                                        <option value="Driver's License">Driver's License</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_num" class="form-label">ID Number</label>
                                    <input type="text" class="form-control" id="id_num" name="id_num">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Profile Picture</label>
                            <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
                            <div class="mt-2">
                                <img id="manager-edit-profile-preview" src="" class="img-thumbnail" style="max-width: 150px; display: none;" alt="Profile Preview">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Manager Modal -->
    <div class="modal fade" id="deleteManagerModal" tabindex="-1" aria-labelledby="deleteManagerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteManagerModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this manager? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger confirm-delete">Delete</button>
                </div>
            </div>
        </div>
    </div>

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

    <!-- Scripts moved to head with defer attribute -->
</body>
</html>
