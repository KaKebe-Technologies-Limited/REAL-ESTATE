<?php
// Buffer output to prevent warnings from breaking JSON
ob_start();

require_once 'image_handler.php';
require_once 'log_activity.php';
header('Content-Type: application/json');

// Disable displaying errors in the output
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Still log errors to the error log
error_reporting(E_ALL);
require_once 'config.php';

// Add debugging
error_log("Received POST data: " . print_r($_POST, true));
error_log("Received FILES data: " . print_r($_FILES, true));

$response = ['success' => false, 'message' => ''];

try {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get action from POST or GET as a fallback
    $action = '';
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
    } elseif (isset($_GET['action'])) {
        $action = $_GET['action'];
    } elseif (isset($_REQUEST['action'])) {
        $action = $_REQUEST['action'];
    }

    // Log the action and the entire request for debugging
    error_log("Action received: " . $action);
    error_log("REQUEST data: " . print_r($_REQUEST, true));
    error_log("Raw POST data: " . file_get_contents('php://input'));

    switch($action) {
        case 'view':
            $rental_id = $_POST['rental_id'] ?? 0;
            $stmt = $conn->prepare("
                SELECT r.*, o.username as owner_name, m.username as manager_name
                FROM rental_property r
                LEFT JOIN property_owner o ON r.owner_id = o.owner_id
                LEFT JOIN property_manager m ON r.manager_id = m.manager_id
                WHERE r.property_id = ?
            ");
            $stmt->bind_param("i", $rental_id);
            $stmt->execute();
            $rental = $stmt->get_result()->fetch_assoc();

            if ($rental) {
                // Convert image paths to URLs
                if (!empty($rental['images'])) {
                    // Get the base URL for the current environment
                    $baseUrl = '';

                    // Check if we're on localhost or live site
                    $serverName = strtolower($_SERVER['SERVER_NAME']);
                    $isLocalhost = (strpos($serverName, 'localhost') !== false || $serverName === '127.0.0.1');

                    // Log server information for debugging
                    error_log("Server name: {$serverName}, Is localhost: " . ($isLocalhost ? 'true' : 'false'));

                    $rental['image_urls'] = array_map(function($img) use ($isLocalhost) {
                        // Clean up the image path
                        $img = trim($img);

                        // Remove any existing /REAL-ESTATE prefixes
                        $img = preg_replace('#^(/REAL-ESTATE)+/?#i', '/', $img);

                        // Normalize the path to ensure it starts with uploads/
                        if (strpos($img, 'uploads/') !== 0 && strpos($img, '/uploads/') !== 0) {
                            // If it's an old path (just 'rentals/'), update it
                            if (strpos($img, 'rentals/') === 0) {
                                $img = 'uploads/' . $img;
                            } else if (strpos($img, '/rentals/') === 0) {
                                $img = '/uploads' . $img;
                            }
                        }

                        // Remove any leading slash for consistency
                        $img = ltrim($img, '/');

                        // For localhost, we need to add the /REAL-ESTATE prefix
                        // For live site, we use the path as is
                        if ($isLocalhost) {
                            return '/REAL-ESTATE/' . $img;
                        } else {
                            return '/' . $img;
                        }
                    }, explode(',', $rental['images']));

                    // Log the image URLs for debugging
                    error_log("Image URLs: " . print_r($rental['image_urls'], true));
                }

                $response['success'] = true;
                $response['data'] = $rental;
            } else {
                $response['message'] = 'Rental property not found';
            }
            break;

        case 'edit':
            try {
                // Validate required fields
                $required_fields = ['property_id', 'property_name', 'price', 'property_type', 'utilities'];
                foreach ($required_fields as $field) {
                    if (empty($_POST[$field])) {
                        throw new Exception("Missing required field: $field");
                    }
                }

                $rental_id = intval($_POST['property_id']);
                error_log("Starting edit process for rental_id: $rental_id");
                error_log("POST data received: " . print_r($_POST, true));
                error_log("FILES data received: " . print_r($_FILES, true));

                // Verify the property exists
                $checkStmt = $conn->prepare("SELECT property_id FROM rental_property WHERE property_id = ?");
                $checkStmt->bind_param('i', $rental_id);
                $checkStmt->execute();
                $result = $checkStmt->get_result();

                if ($result->num_rows === 0) {
                    throw new Exception("Property with ID $rental_id not found");
                }

                // Handle images
                $allImages = [];
                if (isset($_POST['existing_images']) && is_array($_POST['existing_images'])) {
                    $allImages = array_merge($allImages, $_POST['existing_images']);
                }

                if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['name'][0])) {
                    $imageHandler = new ImageHandler('rentals');
                    $uploadResult = $imageHandler->handleImageUploads($_FILES);

                    // Log the upload result for debugging
                    error_log('Image upload result: ' . print_r($uploadResult, true));

                    if (!$uploadResult['success']) {
                        // Don't throw an exception, just log the error
                        error_log('Failed to upload images: ' . implode(', ', $uploadResult['errors']));
                        // Continue with the update process
                    }

                    $allImages = array_merge($allImages, $uploadResult['images']);
                }

                $allImages = array_filter(array_unique($allImages));
                $imagesString = implode(',', $allImages);

                // Prepare property data
                $propertyData = [
                    'property_name' => $_POST['property_name'],
                    'price' => floatval($_POST['price']),
                    'property_type' => $_POST['property_type'],
                    'utilities' => $_POST['utilities'],
                    'property_size' => $_POST['property_size'] ?? '',
                    'amenities' => is_array($_POST['amenities']) ? implode(',', $_POST['amenities']) : '',
                    'security' => is_array($_POST['security']) ? implode(',', $_POST['security']) : '',
                    'country' => $_POST['country'] ?? '',
                    'region' => $_POST['region'] ?? '',
                    'subregion' => $_POST['subregion'] ?? '',
                    'parish' => $_POST['parish'] ?? '',
                    'ward' => $_POST['ward'] ?? '',
                    'cell' => $_POST['cell'] ?? '',
                    'owner_id' => !empty($_POST['owner_id']) ? intval($_POST['owner_id']) : null,
                    'manager_id' => !empty($_POST['manager_id']) ? intval($_POST['manager_id']) : null,
                    'images' => $imagesString
                ];

                error_log("Property data to be saved: " . print_r($propertyData, true));

                $conn->begin_transaction();

                $query = "UPDATE rental_property SET
                            property_name = ?,
                            price = ?,
                            property_type = ?,
                            utilities = ?,
                            property_size = ?,
                            amenities = ?,
                            security = ?,
                            country = ?,
                            region = ?,
                            subregion = ?,
                            parish = ?,
                            ward = ?,
                            cell = ?,
                            owner_id = ?,
                            manager_id = ?,
                            images = ?
                        WHERE property_id = ?";

                $stmt = $conn->prepare($query);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                $stmt->bind_param(
                    'sdsssssssssssiisi',
                    $propertyData['property_name'],
                    $propertyData['price'],
                    $propertyData['property_type'],
                    $propertyData['utilities'],
                    $propertyData['property_size'],
                    $propertyData['amenities'],
                    $propertyData['security'],
                    $propertyData['country'],
                    $propertyData['region'],
                    $propertyData['subregion'],
                    $propertyData['parish'],
                    $propertyData['ward'],
                    $propertyData['cell'],
                    $propertyData['owner_id'],
                    $propertyData['manager_id'],
                    $propertyData['images'],
                    $rental_id
                );

                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }

                if ($stmt->affected_rows === 0) {
                    error_log("Warning: Update query succeeded but no rows were affected");
                }

                $conn->commit();

                $response['success'] = true;
                $response['message'] = 'Rental property updated successfully';
                $response['property_id'] = $rental_id;

            } catch (Exception $e) {
                error_log("Error in edit case: " . $e->getMessage());
                if (isset($conn)) {
                    $conn->rollback();
                }
                $response['success'] = false;
                $response['message'] = $e->getMessage();
            }
            break;

        case 'delete':
            $rental_id = $_POST['property_id'] ?? 0;

            $conn->begin_transaction();

            try {
                // Get images before deleting
                $stmt = $conn->prepare("SELECT images FROM rental_property WHERE property_id = ?");
                $stmt->bind_param("i", $rental_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $rental = $result->fetch_assoc();

                // Delete the property
                $stmt = $conn->prepare("DELETE FROM rental_property WHERE property_id = ?");
                $stmt->bind_param("i", $rental_id);

                if ($stmt->execute()) {
                    // Delete associated images
                    if (!empty($rental['images'])) {
                        $imageHandler = new ImageHandler('rentals');
                        $imageHandler->deleteImages(explode(',', $rental['images']));
                    }

                    $conn->commit();
                    $response['success'] = true;
                    $response['message'] = 'Rental property deleted successfully';
                    logPropertyDeleted($rental_id, 'rental');
                } else {
                    throw new Exception('Failed to delete rental property');
                }
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;

        case 'get_owners':
            $stmt = $conn->prepare("SELECT owner_id, username as name FROM property_owner ORDER BY username");
            if (!$stmt) {
                throw new Exception("Prepare failed for owners: " . $conn->error);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $owners = $result->fetch_all(MYSQLI_ASSOC);
            error_log("Owners found: " . print_r($owners, true)); // Debug log
            $response['success'] = true;
            $response['owners'] = $owners;
            break;

        case 'get_managers':
            $stmt = $conn->prepare("SELECT manager_id, username as name FROM property_manager ORDER BY username");
            if (!$stmt) {
                throw new Exception("Prepare failed for managers: " . $conn->error);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $managers = $result->fetch_all(MYSQLI_ASSOC);
            error_log("Managers found: " . print_r($managers, true)); // Debug log
            $response['success'] = true;
            $response['managers'] = $managers;
            break;

        default:
            $response['message'] = 'Invalid action: ' . $action;
            error_log("Invalid action received: " . $action); // Debug log
            break;
    }

} catch (Exception $e) {
    error_log("Error occurred: " . $e->getMessage());
    $response['success'] = false;
    $response['message'] = $e->getMessage();
} finally {
    if (isset($conn)) $conn->close();
}

error_log("Final response: " . print_r($response, true)); // Debug log

// Clear the output buffer and send only the JSON response
ob_end_clean();
echo json_encode($response);
