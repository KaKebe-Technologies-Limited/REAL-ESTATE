<?php
function logActivity($type, $title, $description, $icon_class = '', $icon_bg_class = '') {
    $conn = new mysqli('localhost', 'root', '', 'allea');
    
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