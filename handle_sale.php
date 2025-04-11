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

    $action = $_POST['action'] ?? '';
    error_log("Action received: " . $action);

    switch($action) {
        case 'view':
            $sale_id = $_POST['sale_id'] ?? $_POST['property_id'] ?? 0;
            $stmt = $conn->prepare("
                SELECT s.*, o.username as owner_name, m.username as manager_name
                FROM sales_property s
                LEFT JOIN property_owner o ON s.owner_id = o.owner_id
                LEFT JOIN property_manager m ON s.manager_id = m.manager_id
                WHERE s.property_id = ?
            ");
            $stmt->bind_param("i", $sale_id);
            $stmt->execute();
            $sale = $stmt->get_result()->fetch_assoc();

            if ($sale) {
                // Convert image paths to URLs
                if (!empty($sale['images'])) {
                    // Get the base URL for the current environment
                    $baseUrl = '';

                    // Check if we're on localhost or live site
                    $serverName = strtolower($_SERVER['SERVER_NAME']);
                    $isLocalhost = strpos($serverName, 'localhost') !== false || $serverName === '127.0.0.1';

                    // Log server information for debugging
                    error_log("Server name: {$serverName}, Is localhost: " . ($isLocalhost ? 'true' : 'false'));

                    $sale['image_urls'] = array_map(function($img) use ($isLocalhost) {
                        // Clean up the image path
                        $img = trim($img);

                        // Remove any existing /REAL-ESTATE prefixes
                        $img = preg_replace('#^(/REAL-ESTATE)+/?#i', '/', $img);

                        // Normalize the path to ensure it starts with uploads/
                        if (strpos($img, 'uploads/') !== 0 && strpos($img, '/uploads/') !== 0) {
                            // If it's an old path (just 'sales/'), update it
                            if (strpos($img, 'sales/') === 0) {
                                $img = 'uploads/' . $img;
                            } else if (strpos($img, '/sales/') === 0) {
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
                    }, explode(',', $sale['images']));

                    // Log the image URLs for debugging
                    error_log("Image URLs: " . print_r($sale['image_urls'], true));
                }
                $response['success'] = true;
                $response['data'] = $sale;
            } else {
                $response['message'] = 'Sale property not found';
            }
            break;

        case 'edit':
            $sale_id = $_POST['property_id'] ?? $_POST['sale_id'] ?? 0;

            // Handle image uploads if any
            $imageHandler = new ImageHandler('sales');
            $uploadResult = $imageHandler->handleImageUploads($_FILES);

            // Log the upload result for debugging
            error_log('Image upload result: ' . print_r($uploadResult, true));

            if (!$uploadResult['success']) {
                // Don't throw an exception, just log the error
                error_log('Failed to upload images: ' . implode(', ', $uploadResult['errors']));
                // Continue with the update process
            }

            // Get existing images
            $stmt = $conn->prepare("SELECT images FROM sales_property WHERE property_id = ?");
            $stmt->bind_param("i", $sale_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing = $result->fetch_assoc();

            // Combine existing and new images
            $existingImages = !empty($existing['images']) ? explode(',', $existing['images']) : [];
            $newImages = $uploadResult['images'];
            $allImages = array_merge($existingImages, $newImages);

            // Update property data
            $propertyData = [
                'property_name' => $_POST['property_name'] ?? '',
                'price' => $_POST['price'] ?? '',
                'property_type' => $_POST['property_type'] ?? '',
                'title' => $_POST['title'] ?? '',
                'utilities' => $_POST['utilities'] ?? '',
                'property_size' => $_POST['property_size'] ?? '',
                'amenities' => isset($_POST['amenities']) ? implode(',', $_POST['amenities']) : '',
                'country' => $_POST['country'] ?? '',
                'region' => $_POST['region'] ?? '',
                'subregion' => $_POST['subregion'] ?? '',
                'parish' => $_POST['parish'] ?? '',
                'ward' => $_POST['ward'] ?? '',
                'cell' => $_POST['cell'] ?? '',
                'owner_id' => $_POST['owner_id'] ?? null,
                'manager_id' => $_POST['manager_id'] ?? null,
                'images' => implode(',', $allImages)
            ];

            $query = "UPDATE sales_property SET
                        property_name=?, title=?, utilities=?, price=?, property_type=?,
                        property_size=?, amenities=?, country=?, region=?, subregion=?,
                        parish=?, ward=?, cell=?, owner_id=?, manager_id=?, images=?
                        WHERE property_id=?";

            $stmt = $conn->prepare($query);
            $stmt->bind_param(
                'sssssisssssssiisi',
                $propertyData['property_name'],
                $propertyData['title'],
                $propertyData['utilities'],
                $propertyData['price'],
                $propertyData['property_type'],
                $propertyData['property_size'],
                $propertyData['amenities'],
                $propertyData['country'],
                $propertyData['region'],
                $propertyData['subregion'],
                $propertyData['parish'],
                $propertyData['ward'],
                $propertyData['cell'],
                $propertyData['owner_id'],
                $propertyData['manager_id'],
                $propertyData['images'],
                $sale_id
            );

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Sale property updated successfully';
                $response['images'] = $allImages;
                logPropertyUpdated($propertyData['property_name'], $propertyData['region']);
            } else {
                throw new Exception('Failed to update sale property');
            }
            break;

        case 'delete':
            $sale_id = $_POST['property_id'] ?? $_POST['sale_id'] ?? 0;

            $conn->begin_transaction();

            try {
                // Get images before deleting
                $stmt = $conn->prepare("SELECT images FROM sales_property WHERE property_id = ?");
                $stmt->bind_param("i", $sale_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $sale = $result->fetch_assoc();

                // Delete the property
                $stmt = $conn->prepare("DELETE FROM sales_property WHERE property_id = ?");
                $stmt->bind_param("i", $sale_id);

                if ($stmt->execute()) {
                    // Delete associated images
                    if (!empty($sale['images'])) {
                        $imageHandler = new ImageHandler('sales');
                        $imageHandler->deleteImages(explode(',', $sale['images']));
                    }

                    $conn->commit();
                    $response['success'] = true;
                    $response['message'] = 'Sale property deleted successfully';
                    logPropertyDeleted($sale_id, 'sale');
                } else {
                    throw new Exception('Failed to delete sale property');
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
