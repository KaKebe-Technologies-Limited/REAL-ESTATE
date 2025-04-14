<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'owner') {
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

// Get owner ID from session
$owner_id = $_SESSION['user_id'];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $username = $_POST['username'] ?? '';

    // Password change fields
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate input
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
        $_SESSION['error'] = "All fields are required";
        header('Location: ownerDashboard.php');
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
        $filename = 'owner_' . $owner_id . '_' . time() . '.' . $file_extension;
        $target_file = $upload_dir . $filename;

        // Move uploaded file
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $profile_picture = $target_file;
        }
    }

    // Handle password update if provided
    $password_update = "";
    $password_params = [];

    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        if ($new_password !== $confirm_password) {
            $_SESSION['error'] = "New passwords do not match";
            header('Location: ownerDashboard.php');
            exit();
        }

        // Verify current password
        $verify_query = "SELECT password FROM property_owner WHERE owner_id = ?";
        $verify_stmt = $conn->prepare($verify_query);
        $verify_stmt->bind_param('i', $owner_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();

        if ($verify_result->num_rows > 0) {
            $owner = $verify_result->fetch_assoc();

            if (password_verify($current_password, $owner['password'])) {
                // Current password is correct, update with new password
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $password_update = ", password = ?";
                $password_params[] = $new_password_hash;
            } else {
                $_SESSION['error'] = "Current password is incorrect";
                header('Location: ownerDashboard.php');
                exit();
            }
        }

        $verify_stmt->close();
    }

    // Update owner information
    $query = "UPDATE property_owner SET
              first_name = ?,
              last_name = ?,
              email = ?,
              phone = ?,
              username = ?";

    $params = [$first_name, $last_name, $email, $phone, $username];
    $types = "sssss";

    // Add profile picture to update if uploaded
    if ($profile_picture) {
        $query .= ", profile_picture = ?";
        $params[] = $profile_picture;
        $types .= "s";
    }

    // Add password update if provided
    if (!empty($password_update)) {
        $query .= $password_update;
        $params = array_merge($params, $password_params);
        $types .= "s";
    }

    $query .= " WHERE owner_id = ?";
    $params[] = $owner_id;
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
    header('Location: ownerDashboard.php');
    exit();
} else {
    // If not POST request, redirect to dashboard
    header('Location: ownerDashboard.php');
    exit();
}
