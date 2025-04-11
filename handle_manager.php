<?php
header('Content-Type: application/json'); // Ensure JSON response
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php'; // Include database configuration

$response = ['success' => false, 'message' => ''];
$upload_dir = 'uploads/managers/'; // Create this directory and ensure it's writable

// Create upload directory if it doesn't exist
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
            $manager_id = $_POST['manager_id'] ?? 0;
            $stmt = $conn->prepare("SELECT *, CONCAT(?, profile_picture) as image_url FROM property_manager WHERE manager_id = ?");
            $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";
            $stmt->bind_param("si", $base_url, $manager_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $manager = $result->fetch_assoc();

            if ($manager) {
                $response['success'] = true;
                $response['data'] = $manager;
            } else {
                $response['message'] = 'Manager not found';
            }
            break;

        case 'edit':
            // Debug incoming data
            error_log("POST data: " . print_r($_POST, true));
            error_log("FILES data: " . print_r($_FILES, true));

            $manager_id = $_POST['manager_id'] ?? 0;
            $first_name = $_POST['first_name'] ?? '';
            $last_name = $_POST['last_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $username = $_POST['username'] ?? '';
            $id_type = $_POST['id_type'] ?? '';
            $id_num = $_POST['id_num'] ?? '';
            $address = $_POST['address'] ?? '';

            // Validate manager_id
            if (!$manager_id) {
                throw new Exception('Invalid manager ID');
            }

            // Handle profile picture upload
            $profile_picture_update = "";
            $profile_picture_params = [];

            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
                $upload_dir = 'uploads/managers/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                // Get file extension and create new filename
                $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                $file_name = 'manager_' . $manager_id . '_' . time() . '.' . $file_extension;
                $target_path = $upload_dir . $file_name;

                // Get old image path before uploading new one
                $stmt = $conn->prepare("SELECT profile_picture FROM property_manager WHERE manager_id = ?");
                $stmt->bind_param("i", $manager_id);
                $stmt->execute();
                $old_image = $stmt->get_result()->fetch_assoc()['profile_picture'];

                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
                    $profile_picture_update = ", profile_picture = ?";
                    $profile_picture_params[] = $target_path;

                    // Delete old image if it exists
                    if (!empty($old_image) && file_exists($old_image)) {
                        unlink($old_image);
                    }
                }
            }

            // Build query
            $query = "UPDATE property_manager SET
                first_name = ?,
                last_name = ?,
                email = ?,
                phone = ?,
                username = ?,
                id_type = ?,
                id_num = ?,
                address = ?
                $profile_picture_update
                WHERE manager_id = ?";

            // Prepare parameters
            $params = array_merge(
                [$first_name, $last_name, $email, $phone, $username, $id_type, $id_num, $address],
                $profile_picture_params,
                [$manager_id]
            );

            // Generate types string based on parameters
            $types = str_repeat('s', count($params) - 1) . 'i';

            // Debug information
            error_log("Query: " . $query);
            error_log("Types: " . $types);
            error_log("Params: " . print_r($params, true));

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

            if ($stmt->affected_rows > 0 || !empty($profile_picture_params)) {
                $response['success'] = true;
                $response['message'] = 'Manager updated successfully';
                $response['debug'] = [
                    'affected_rows' => $stmt->affected_rows,
                    'image_uploaded' => !empty($profile_picture_params),
                    'query' => $query,
                    'params' => $params,
                    'types' => $types
                ];
            } else {
                // Check if data is actually different
                $check_stmt = $conn->prepare("SELECT * FROM property_manager WHERE manager_id = ?");
                $check_stmt->bind_param("i", $manager_id);
                $check_stmt->execute();
                $current_data = $check_stmt->get_result()->fetch_assoc();

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
                    throw new Exception('Failed to update manager record');
                }
            }
            break;

        case 'delete':
            $manager_id = $_POST['manager_id'] ?? 0;

            // Start transaction
            $conn->begin_transaction();

            try {
                // Get image path before deleting
                $stmt = $conn->prepare("SELECT profile_picture FROM property_manager WHERE manager_id = ?");
                $stmt->bind_param("i", $manager_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $manager = $result->fetch_assoc();

                // Update properties to remove manager reference
                $stmt = $conn->prepare("UPDATE rental_property SET manager_id = NULL WHERE manager_id = ?");
                $stmt->bind_param("i", $manager_id);
                $stmt->execute();

                // Delete the manager
                $stmt = $conn->prepare("DELETE FROM property_manager WHERE manager_id = ?");
                $stmt->bind_param("i", $manager_id);
                $stmt->execute();

                // Delete the image file if it exists
                if ($manager && $manager['profile_picture'] && file_exists($manager['profile_picture'])) {
                    unlink($manager['profile_picture']);
                }

                $conn->commit();
                $response['success'] = true;
                $response['message'] = 'Manager deleted successfully';
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