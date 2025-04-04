<?php
session_start();
// Make sure this matches your admin table's user_id
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

// Load user profile picture on page load
$conn = new mysqli('localhost', 'root', '', 'allea');
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

// Fetch data from get_rentals.php
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10; // Number of entries per page
$data = file_get_contents("http://localhost/REAL-ESTATE/get_rentals.php?page=$page&limit=$limit");
$response = json_decode($data, true); // Decode JSON into an associative array

$rentals = $response['success'] ? $response['rentals'] : [];
$total_rentals = $response['total'];
$total_pages = ceil($total_rentals / $limit);

// Fetch data from get_sales.php
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10; // Number of entries per page
$data = file_get_contents("http://localhost/REAL-ESTATE/get_sales.php?page=$page&limit=$limit");
$response = json_decode($data, true); // Decode JSON into an associative array

$sales = $response['success'] ? $response['sales'] : [];
$total_sales = $response['total'];
$total_pages = ceil($total_sales / $limit);

// Fetch data from get_owners.php
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10; // Number of entries per page
$data = file_get_contents("http://localhost/REAL-ESTATE/get_owners.php?page=$page&limit=$limit");
$response = json_decode($data, true); // Decode JSON into an associative array

$owners = $response['success'] ? $response['owners'] : [];
$total_owners = $response['total'];
$total_pages = ceil($total_owners / $limit);

// Fetch data from get_managers.php
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10; // Number of entries per page
$data = file_get_contents("http://localhost/REAL-ESTATE/get_managers.php?page=$page&limit=$limit");
$response = json_decode($data, true); // Decode JSON into an associative array

$managers = $response['success'] ? $response['managers'] : [];
$total_managers = $response['total'];
$total_pages = ceil($total_managers / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - East African Land and Rentals</title>
    <link rel="icon" href="logo1.ico" sizes="any">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Add to the <head> section -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="assets/css/admin-dashboard.css">
    
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <button class="btn btn-light" id="sidebar-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand ms-3" href="#">ALL-EA</a>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html"> 
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
                    <li class="nav-item dropdown">
                        <a class="nav-link profile-link" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="<?php echo isset($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'uploads/profile_picture/default-profile.jpg'; ?>" alt="Profile" class="rounded-circle img-thumbnai profile-picture">
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

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="assets/images/logo1.png" alt="Logo" class="logo">
        </div>
        <ul class="sidebar-nav">
            <li class="nav-item active">
                <a href="#" class="nav-link"data-form="dashboard-content" onclick="showDashboard()">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link" onclick="toggleSubmenu(this)">
                    <i class="fas fa-building"></i>
                    <span>Properties</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="has-submenu">
                        <a href="#" class="submenu-link" onclick="togglePropertySubmenu(this)">
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
                        <a href="#" class="submenu-link" onclick="togglePropertySubmenu(this)">
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
                <a href="#" class="nav-link" onclick="toggleSubmenu(this)">
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
                </ul>
            </li>
            <li class="nav-item has-submenu">
                <a href="#" class="nav-link" onclick="toggleSubmenu(this)">
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
                <div class="row" style="justify-content: space-between;">
                    <div class="col-md-6 col-xl-4">
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
                    <div class="col-md-6 col-xl-4">
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
                        <!-- Quick Actions section remains the same -->
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
                                                    <input name="phone" type="tel" class="form-control" required />
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
                                                        <option value="cash">Cash</option>
                                                        <option value="installments">Installments</option>
                                                    </select>
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
                                                        <option value="included">Included</option>
                                                        <option value="not_included">Not Included</option>
                                                        <option value="partial">Partially Included</option>
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
                                                        <option value="land">Only Land</option>
                                                        <option value="flat">Flat</option>
                                                        <option value="permanent">Permanent</option>
                                                        <option value="semi_permanent">Semi-permanent</option>
                                                        <option value="ground">Grounds</option>
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
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Close Amenities</label>
                                                <div class="col-sm-9">
                                                    <select name="amenities[]" class="form-control select2-multiple" multiple="multiple" required>
                                                        <option value="market">Market</option>
                                                        <option value="school">School</option>
                                                        <option value="church">Church / Mosque</option>
                                                        <option value="institution">Institution</option>
                                                        <option value="hospital">Hospital</option>
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
                                                        <option value="uganda">Uganda</option>
                                                        <option value="kenya">Kenya</option>
                                                        <option value="tanzania">Tanzania</option>
                                                        <option value="rwanda">Rwanda</option>
                                                        <option value="burundi">Burundi</option>
                                                        <option value="south_sudan">South Sudan</option>
                                                        <option value="drc">DR Congo</option>
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

                                    <!-- Property Details -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Landlord</label>
                                                <div class="col-sm-9">
                                                    <select name="landlord" class="form-control" required>
                                                        <option value="">Select Landlord</option>
                                                        <option value="resident">Resident</option>
                                                        <option value="non_resident">Non resident</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Security</label>
                                                <div class="col-sm-9">
                                                    <select name="security[]" class="form-control select2-multiple" multiple="multiple">
                                                        <option value="cctv">CCTV</option>
                                                        <option value="guards">Security Guards</option>
                                                        <option value="electric_fence">Electric Fence</option>
                                                        <option value="alarm">Alarm System</option>
                                                        <option value="security_lights">Security Lights</option>
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
                                                        <option value="included">Included</option>
                                                        <option value="not_included">Not Included</option>
                                                        <option value="partial">Partially Included</option>
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
                                                        <option value="flat">Flat</option>
                                                        <option value="permanent">Permanent</option>
                                                        <option value="semi_permanent">Semi-permanent</option>
                                                        <option value="ground">Grounds</option>
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
                                                        <option value="crowded">Crowded</option>
                                                        <option value="private">Private</option>
                                                        <option value="self_contained">Self contained</option>
                                                        <option value="non_self_contained">Non self contained</option>
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
                                                        <option value="hall">Hall</option>
                                                        <option value="commercial">Commercial</option>
                                                        <option value="residential">Residential</option>
                                                        <option value="hostel">Hostel</option>
                                                        <option value="hotel">Hotel</option>
                                                        <option value="lodge">Lodge</option>
                                                        <option value="event_space">Event space</option>
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
                                                        <option value="yse">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
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
                                                        <option value="market">Market</option>
                                                        <option value="school">School</option>
                                                        <option value="church">Church / Mosque</option>
                                                        <option value="institution">Institution</option>
                                                        <option value="hospital">Hospital</option>
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
                                                        <option value="uganda">Uganda</option>
                                                        <option value="kenya">Kenya</option>
                                                        <option value="tanzania">Tanzania</option>
                                                        <option value="rwanda">Rwanda</option>
                                                        <option value="burundi">Burundi</option>
                                                        <option value="south_sudan">South Sudan</option>
                                                        <option value="drc">DR Congo</option>
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
                                            <input type="text" class="form-control" placeholder="Search properties...">
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
                                                <th>Rent (USD)</th>
                                                <th>Availability</th>
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
                                                        <td>$<?php echo htmlspecialchars($rental['rent']); ?></td>
                                                        <td>
                                                            <span class="badge <?php echo $rental['availability'] === 'Available' ? 'bg-success' : 'bg-danger'; ?>">
                                                                <?php echo htmlspecialchars($rental['availability']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <button class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-warning">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-danger">
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
                                            <input type="text" class="form-control" placeholder="Search properties...">
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
                                                <th>Price (USD)</th>
                                                <th>Availability</th>
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
                                                        <td>$<?php echo htmlspecialchars($sale['price']); ?></td>
                                                        <td>
                                                            <span class="badge <?php echo $sale['availability'] === 'Available' ? 'bg-success' : 'bg-danger'; ?>">
                                                                <?php echo htmlspecialchars($sale['availability']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <button class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-warning">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-danger">
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
                                            <input type="text" class="form-control" placeholder="Search owners...">
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
                                                                <button class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-eye"></i> View
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
                                            <input type="text" class="form-control" placeholder="Search managers...">
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
                                                                <button class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-eye"></i> View
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
                                                    src="<?php echo isset($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'uploads/profile_picture/default-profile.jpg'; ?>" 
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
                                                            <input type="tel" name="phone" class="form-control" required />
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
                                        <img src="<?php echo isset($_SESSION['profile_picture']) ? $_SESSION['profile_picture'] : 'uploads/profile_picture/default-profile.jpg'; ?>" 
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> -->
    <script src="assets/js/admin-dashboard.js"></script>
    <script src="assets/js/register.js"></script>
    <!-- <script src="assets/js/property.js"></script> -->
    <script src="assets/js/profile.js"></script>
    <script src="assets/js/activity.js"></script>
    <script src="assets/js/imageupload.js"></script>
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
        });
    </script>
</body>
</html>
