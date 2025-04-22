<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'manager') {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

// Include database connection
require_once 'config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
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
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate input
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
        exit();
    }

    // Handle profile picture upload
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/managers/';

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
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload profile picture']);
            exit();
        }
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update manager information
        $query = "UPDATE property_manager SET
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

        // Update password if provided
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query .= ", password = ?";
            $params[] = $hashed_password;
            $types .= "s";
        }

        $query .= " WHERE manager_id = ?";
        $params[] = $manager_id;
        $types .= "i";

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);

        if (!$stmt->execute()) {
            throw new Exception("Error updating profile: " . $conn->error);
        }

        $stmt->close();

        // Commit transaction
        $conn->commit();

        // Return success response
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $conn->close();
} else {
    // If not POST request, return error
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
