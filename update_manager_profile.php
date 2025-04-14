<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'manager') {
    header('Location: login.html');
    exit();
}

// Include database connection
require_once 'config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get manager ID from session
$manager_id = $_SESSION['user_id'];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    // Validate input
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
        $_SESSION['error'] = "All fields are required";
        header('Location: managerDashboard.php');
        exit();
    }
    
    // Handle profile picture upload
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/images/profiles/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $filename = 'manager_' . $manager_id . '_' . time() . '.' . $file_extension;
        $target_file = $upload_dir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $profile_picture = $target_file;
        }
    }
    
    // Update manager information
    $query = "UPDATE property_manager SET 
              first_name = ?, 
              last_name = ?, 
              email = ?, 
              phone = ?";
    
    $params = [$first_name, $last_name, $email, $phone];
    $types = "ssss";
    
    // Add profile picture to update if uploaded
    if ($profile_picture) {
        $query .= ", profile_picture = ?";
        $params[] = $profile_picture;
        $types .= "s";
    }
    
    $query .= " WHERE manager_id = ?";
    $params[] = $manager_id;
    $types .= "i";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully";
    } else {
        $_SESSION['error'] = "Error updating profile: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
    
    // Redirect back to dashboard
    header('Location: managerDashboard.php');
    exit();
} else {
    // If not POST request, redirect to dashboard
    header('Location: managerDashboard.php');
    exit();
}
