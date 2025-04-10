<?php
require_once 'config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Starting image migration script...\n";

// Create uploads directory if it doesn't exist
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
    echo "Created uploads directory\n";
}

// Create uploads/rentals directory if it doesn't exist
if (!file_exists('uploads/rentals')) {
    mkdir('uploads/rentals', 0777, true);
    echo "Created uploads/rentals directory\n";
}

// Create uploads/sales directory if it doesn't exist
if (!file_exists('uploads/sales')) {
    mkdir('uploads/sales', 0777, true);
    echo "Created uploads/sales directory\n";
}

// Connect to database
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process rental images
echo "\nProcessing rental images...\n";
$result = $conn->query("SELECT property_id, images FROM rental_property WHERE images IS NOT NULL AND images != ''");
$count = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $property_id = $row['property_id'];
        $images = explode(',', $row['images']);
        $new_images = [];
        
        foreach ($images as $image) {
            $image = trim($image);
            
            // Skip empty images
            if (empty($image)) continue;
            
            // Check if it's already in the correct format
            if (strpos($image, 'uploads/rentals/') === 0) {
                $new_images[] = $image;
                continue;
            }
            
            // Handle old format (rentals/filename.ext)
            if (strpos($image, 'rentals/') === 0) {
                $old_path = $image;
                $new_path = 'uploads/' . $image;
                
                // Move the file if it exists
                if (file_exists($old_path)) {
                    // Make sure the directory exists
                    if (!file_exists(dirname($new_path))) {
                        mkdir(dirname($new_path), 0777, true);
                    }
                    
                    if (copy($old_path, $new_path)) {
                        echo "Copied $old_path to $new_path\n";
                    } else {
                        echo "Failed to copy $old_path to $new_path\n";
                    }
                } else {
                    echo "Warning: File $old_path does not exist\n";
                }
                
                $new_images[] = $new_path;
            } else {
                // If it's just a filename, assume it's in rentals/
                if (strpos($image, '/') === false) {
                    $old_path = 'rentals/' . $image;
                    $new_path = 'uploads/rentals/' . $image;
                    
                    // Move the file if it exists
                    if (file_exists($old_path)) {
                        if (copy($old_path, $new_path)) {
                            echo "Copied $old_path to $new_path\n";
                        } else {
                            echo "Failed to copy $old_path to $new_path\n";
                        }
                    } else {
                        echo "Warning: File $old_path does not exist\n";
                    }
                    
                    $new_images[] = $new_path;
                } else {
                    // Unknown format, keep as is
                    $new_images[] = $image;
                    echo "Warning: Unknown image path format: $image\n";
                }
            }
        }
        
        // Update the database with new paths
        if (!empty($new_images)) {
            $new_images_str = implode(',', $new_images);
            $stmt = $conn->prepare("UPDATE rental_property SET images = ? WHERE property_id = ?");
            $stmt->bind_param("si", $new_images_str, $property_id);
            
            if ($stmt->execute()) {
                $count++;
                echo "Updated property ID $property_id with new image paths\n";
            } else {
                echo "Error updating property ID $property_id: " . $stmt->error . "\n";
            }
            
            $stmt->close();
        }
    }
}

echo "Updated $count rental properties\n";

// Process sales images
echo "\nProcessing sales images...\n";
$result = $conn->query("SELECT sale_id, images FROM sales_property WHERE images IS NOT NULL AND images != ''");
$count = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sale_id = $row['sale_id'];
        $images = explode(',', $row['images']);
        $new_images = [];
        
        foreach ($images as $image) {
            $image = trim($image);
            
            // Skip empty images
            if (empty($image)) continue;
            
            // Check if it's already in the correct format
            if (strpos($image, 'uploads/sales/') === 0) {
                $new_images[] = $image;
                continue;
            }
            
            // Handle old format (sales/filename.ext)
            if (strpos($image, 'sales/') === 0) {
                $old_path = $image;
                $new_path = 'uploads/' . $image;
                
                // Move the file if it exists
                if (file_exists($old_path)) {
                    // Make sure the directory exists
                    if (!file_exists(dirname($new_path))) {
                        mkdir(dirname($new_path), 0777, true);
                    }
                    
                    if (copy($old_path, $new_path)) {
                        echo "Copied $old_path to $new_path\n";
                    } else {
                        echo "Failed to copy $old_path to $new_path\n";
                    }
                } else {
                    echo "Warning: File $old_path does not exist\n";
                }
                
                $new_images[] = $new_path;
            } else {
                // If it's just a filename, assume it's in sales/
                if (strpos($image, '/') === false) {
                    $old_path = 'sales/' . $image;
                    $new_path = 'uploads/sales/' . $image;
                    
                    // Move the file if it exists
                    if (file_exists($old_path)) {
                        if (copy($old_path, $new_path)) {
                            echo "Copied $old_path to $new_path\n";
                        } else {
                            echo "Failed to copy $old_path to $new_path\n";
                        }
                    } else {
                        echo "Warning: File $old_path does not exist\n";
                    }
                    
                    $new_images[] = $new_path;
                } else {
                    // Unknown format, keep as is
                    $new_images[] = $image;
                    echo "Warning: Unknown image path format: $image\n";
                }
            }
        }
        
        // Update the database with new paths
        if (!empty($new_images)) {
            $new_images_str = implode(',', $new_images);
            $stmt = $conn->prepare("UPDATE sales_property SET images = ? WHERE sale_id = ?");
            $stmt->bind_param("si", $new_images_str, $sale_id);
            
            if ($stmt->execute()) {
                $count++;
                echo "Updated sale ID $sale_id with new image paths\n";
            } else {
                echo "Error updating sale ID $sale_id: " . $stmt->error . "\n";
            }
            
            $stmt->close();
        }
    }
}

echo "Updated $count sales properties\n";
echo "\nMigration completed!\n";

$conn->close();
?>
