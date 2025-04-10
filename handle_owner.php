<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);
require_once 'config.php';

$response = ['success' => false, 'message' => ''];
$upload_dir = 'uploads/owners/'; // Changed directory

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

try {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'view':
            $owner_id = $_POST['owner_id'] ?? 0;
            $stmt = $conn->prepare("SELECT *, CONCAT(?, profile_picture) as image_url FROM property_owner WHERE owner_id = ?");
            $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";
            $stmt->bind_param("si", $base_url, $owner_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $owner = $result->fetch_assoc();
            
            if ($owner) {
                $response['success'] = true;
                $response['data'] = $owner;
            } else {
                $response['message'] = 'Owner not found';
            }
            break;

        // ... existing code ...

        case 'edit':
            error_log("POST data: " . print_r($_POST, true));
            error_log("FILES data: " . print_r($_FILES, true));

            $owner_id = $_POST['owner_id'] ?? 0;
            
            // Validate owner exists first
            $check_owner = $conn->prepare("SELECT * FROM property_owner WHERE owner_id = ?");
            $check_owner->bind_param("i", $owner_id);
            $check_owner->execute();
            if (!$check_owner->get_result()->fetch_assoc()) {
                throw new Exception('Owner not found');
            }

            // Get and sanitize input data
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $id_type = trim($_POST['id_type'] ?? '');
            $id_num = trim($_POST['id_num'] ?? '');
            $address = trim($_POST['address'] ?? '');

            if (!$owner_id) {
                throw new Exception('Invalid owner ID');
            }

            $profile_picture_update = "";
            $profile_picture_params = [];
            
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                $file_name = 'owner_' . $owner_id . '_' . time() . '.' . $file_extension;
                $target_path = $upload_dir . $file_name;
                
                $stmt = $conn->prepare("SELECT profile_picture FROM property_owner WHERE owner_id = ?");
                $stmt->bind_param("i", $owner_id);
                $stmt->execute();
                $old_image = $stmt->get_result()->fetch_assoc()['profile_picture'];
                
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
                    $profile_picture_update = ", profile_picture = ?";
                    $profile_picture_params[] = $target_path;
                    
                    if (!empty($old_image) && file_exists($old_image)) {
                        unlink($old_image);
                    }
                }
            }

            $query = "UPDATE property_owner SET 
                first_name = ?,
                last_name = ?,
                email = ?,
                phone = ?,
                username = ?,
                id_type = ?,
                id_num = ?,
                address = ?
                $profile_picture_update
                WHERE owner_id = ?";

            $params = array_merge(
                [$first_name, $last_name, $email, $phone, $username, $id_type, $id_num, $address],
                $profile_picture_params,
                [$owner_id]
            );

            $types = str_repeat('s', count($params) - 1) . 'i';

            error_log("Update Query: " . $query);
            error_log("Parameter Types: " . $types);
            error_log("Parameters: " . print_r($params, true));

            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $bind_result = $stmt->bind_param($types, ...$params);
            if (!$bind_result) {
                throw new Exception("Bind failed: " . $stmt->error);
            }

            $execute_result = $stmt->execute();
            if (!$execute_result) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            // Check if update was successful
            if ($stmt->affected_rows > 0 || !empty($profile_picture_params)) {
                $response['success'] = true;
                $response['message'] = 'Owner updated successfully';
            } else {
                // Compare old and new values
                $check_stmt = $conn->prepare("SELECT * FROM property_owner WHERE owner_id = ?");
                $check_stmt->bind_param("i", $owner_id);
                $check_stmt->execute();
                $current_data = $check_stmt->get_result()->fetch_assoc();
                
                error_log("Current data: " . print_r($current_data, true));
                error_log("New data comparison: " . 
                    "first_name: " . ($current_data['first_name'] === $first_name) . 
                    ", last_name: " . ($current_data['last_name'] === $last_name) . 
                    ", email: " . ($current_data['email'] === $email) . 
                    ", phone: " . ($current_data['phone'] === $phone) . 
                    ", username: " . ($current_data['username'] === $username) . 
                    ", id_type: " . ($current_data['id_type'] === $id_type) . 
                    ", id_num: " . ($current_data['id_num'] === $id_num) . 
                    ", address: " . ($current_data['address'] === $address)
                );
                
                if ($current_data['first_name'] === $first_name && 
                    $current_data['last_name'] === $last_name && 
                    $current_data['email'] === $email && 
                    $current_data['phone'] === $phone && 
                    $current_data['username'] === $username && 
                    $current_data['id_type'] === $id_type && 
                    $current_data['id_num'] === $id_num && 
                    $current_data['address'] === $address) {
                    $response['success'] = true;
                    $response['message'] = 'No changes were necessary';
                } else {
                    throw new Exception('Failed to update owner record. No rows were affected.');
                }
            }
            break;

        case 'delete':
            $owner_id = $_POST['owner_id'] ?? 0;
            
            $conn->begin_transaction();
            
            try {
                $stmt = $conn->prepare("SELECT profile_picture FROM property_owner WHERE owner_id = ?");
                $stmt->bind_param("i", $owner_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $owner = $result->fetch_assoc();
                
                // Update properties to remove owner reference
                $stmt = $conn->prepare("UPDATE rental_property SET owner_id = NULL WHERE owner_id = ?");
                $stmt->bind_param("i", $owner_id);
                $stmt->execute();
                
                // Delete the owner
                $stmt = $conn->prepare("DELETE FROM property_owner WHERE owner_id = ?");
                $stmt->bind_param("i", $owner_id);
                $stmt->execute();
                
                if ($owner && $owner['profile_picture'] && file_exists($owner['profile_picture'])) {
                    unlink($owner['profile_picture']);
                }
                
                $conn->commit();
                $response['success'] = true;
                $response['message'] = 'Owner deleted successfully';
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;

        default:
            $response['message'] = 'Invalid action';
            break;
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    if (isset($conn)) $conn->close();
}

echo json_encode($response);