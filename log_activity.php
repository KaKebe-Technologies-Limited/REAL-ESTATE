<?php
header('Content-Type: application/json'); // Ensure JSON response
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);
require_once 'config.php'; // Include database configuration
function logActivity($type, $title, $description, $icon_class = '', $icon_bg_class = '') {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        return false;
    }

    $query = "INSERT INTO activities (activity_type, title, description, icon_class, icon_bg_class) 
                VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sssss', $type, $title, $description, $icon_class, $icon_bg_class);
    
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return $success;
}

// Usage examples:
// Log property addition
function logPropertyAdded($property_name, $location) {
    logActivity(
        'property_added',
        'New Property Added',
        "$property_name in $location",
        'fas fa-plus-circle',
        'bg-soft-primary'
    );
}

// Log owner registration
function logOwnerRegistered($owner_name) {
    logActivity(
        'owner_registered',
        'New Owner Registered',
        $owner_name,
        'fas fa-user-plus',
        'bg-soft-warning'
    );
}

// Log property deletion
function logPropertyDeleted($property_name, $location) {
    logActivity(
        'property_registered',
        'Property Deleted',
        "$property_name in $location",
        'fas fa-trash',
        'bg-soft-warning'
    );
}

// Log property updated
function logPropertyUpdated($property_name, $location) {
    logActivity(
        'property_registered',
        'Property Updated',
        "$property_name in $location",
        'fas fa-trash',
        'bg-soft-warning'
    );
}

// Log manager registration
function logManagerRegistered($manager_name) {
    logActivity(
        'manager_registered',
        'New Manager Registered',
        $manager_name,
        'fas fa-user-plus',
        'bg-soft-warning'
    );
}

// Log property verification
function logPropertyVerified($property_name, $location) {
    logActivity(
        'property_verified',
        'Property Verified',
        "$property_name in $location",
        'fas fa-check-circle',
        'bg-soft-success'
    );
}
?>