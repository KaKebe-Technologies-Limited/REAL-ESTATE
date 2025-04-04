<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Rental Company</title>
    <link rel="icon" href="logo1.ico" sizes="any">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/manager-dashboard.css">
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
                            <img src="assets/images/profile.jpg" alt="Profile" class="rounded-circle">
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
                
                    <!-- Total Rentals Card -->
                    <div class="col-md-6 col-xl-4">
                        <div class="summary-card">
                            <div class="summary-content">
                                <div class="summary-icon bg-warning">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="summary-details">
                                    <h3 class="summary-title">Total Rentals</h3>
                                    <p class="summary-number">320</p>
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
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="summary-details">
                                    <h3 class="summary-title">Total Sales</h3>
                                    <p class="summary-number">150</p>
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
                                    <a href="#" class="quick-action-btn" onclick="showCreateOwner()">
                                        <i class="fas fa-user-plus"></i>
                                        <span>Add Owner</span>
                                    </a>
                                    <a href="#" class="quick-action-btn">
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

        <!-- Add Sale Property Form -->
        <div id="add-sale-form" style="display: none;">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 grid-margin">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Add Property for Sale</h4>
                                <form class="form-sample">
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Title</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Price</label>
                                                <div class="col-sm-9">
                                                    <input type="number" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Bedrooms</label>
                                                <div class="col-sm-8">
                                                    <input type="number" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Bathrooms</label>
                                                <div class="col-sm-8">
                                                    <input type="number" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Area (sq ft)</label>
                                                <div class="col-sm-8">
                                                    <input type="number" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Address</label>
                                                <div class="col-sm-10">
                                                    <textarea class="form-control" rows="4" required></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Description</label>
                                                <div class="col-sm-10">
                                                    <textarea class="form-control" rows="4" required></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Images</label>
                                                <div class="col-sm-10">
                                                    <input type="file" class="form-control" multiple required />
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

        <!-- Add Rental Property Form -->
        <div id="add-rental-form" style="display: none;">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 grid-margin">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Add Rental Property</h4>
                                <form class="form-sample">
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Title</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Monthly Rent</label>
                                                <div class="col-sm-9">
                                                    <input type="number" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Bedrooms</label>
                                                <div class="col-sm-8">
                                                    <input type="number" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Bathrooms</label>
                                                <div class="col-sm-8">
                                                    <input type="number" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Area (sq ft)</label>
                                                <div class="col-sm-8">
                                                    <input type="number" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Address</label>
                                                <div class="col-sm-10">
                                                    <textarea class="form-control" rows="4" required></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Description</label>
                                                <div class="col-sm-10">
                                                    <textarea class="form-control" rows="4" required></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Available From</label>
                                                <div class="col-sm-9">
                                                    <input type="date" class="form-control" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Images</label>
                                                <div class="col-sm-9">
                                                    <input type="file" class="form-control" multiple required />
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
                                            <tr>
                                                <td>Luxury Apartment</td>
                                                <td>John Doe</td>
                                                <td>Sarah Smith</td>
                                                <td>Kira, Wakiso</td>
                                                <td>$1,200</td>
                                                <td><span class="badge bg-success">Available</span></td>
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
                                            <!-- Add more rows as needed -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div class="showing-entries">
                                        Showing 1 to 10 of 50 entries
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
                                            <tr>
                                                <td>Modern Villa</td>
                                                <td>Jane Doe</td>
                                                <td>Michael Brown</td>
                                                <td>Kololo, Kampala</td>
                                                <td>$350,000</td>
                                                <td><span class="badge bg-success">Available</span></td>
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
                                            <!-- Add more rows as needed -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div class="showing-entries">
                                        Showing 1 to 10 of 30 entries
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
                                            <tr>
                                                <td>Michael Brown</td>
                                                <td>12</td>
                                                <td>+256 751 123 456</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Sarah Johnson</td>
                                                <td>8</td>
                                                <td>+256 772 987 654</td>
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
                                        Showing 1 to 10 of 15 entries
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
                                <form id="profile-form" class="form-sample" style="display: none;">
                                    <div class="row mb-4">
                                        <div class="col-md-4 text-center">
                                            <div class="profile-picture-wrapper">
                                                <img id="profile-picture-preview" src="assets/images/profile.jpg" alt="Profile Picture" class="rounded-circle img-thumbnail mb-3" style="width: 150px; height: 150px;">
                                                <input type="file" id="profile-picture-input" class="form-control" accept="image/*" style="display: none;">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" id="change-picture-btn">Change Picture</button>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">First Name</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control" value="John" required />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Last Name</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control" value="Doe" required />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Email</label>
                                                        <div class="col-sm-9">
                                                            <input type="email" class="form-control" value="john.doe@example.com" required />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Phone</label>
                                                        <div class="col-sm-9">
                                                            <input type="tel" class="form-control" value="+256 751 123 456" required />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Username</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control" value="johndoe" required />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-sm-3 col-form-label">Password</label>
                                                        <div class="col-sm-9">
                                                            <input type="password" class="form-control" value="password123" required />
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
                                            <img src="assets/images/profile.jpg" alt="Profile Picture" class="rounded-circle img-thumbnail" style="width: 150px; height: 150px;">
                                        </div>
                                        <div class="col-md-8">
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <p><strong>First Name:</strong> John</p>
                                                    <p><strong>Last Name:</strong> Doe</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Email:</strong> john.doe@example.com</p>
                                                    <p><strong>Phone:</strong> +256 751 123 456</p>
                                                </div>
                                            </div>
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <p><strong>Username:</strong> johndoe</p>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/manager-dashboard.js"></script>
    <script src="assets/js/register.js"></script>
</body>
</html>
