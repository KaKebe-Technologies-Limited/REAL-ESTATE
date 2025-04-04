<?php
header('Content-Type: application/json'); // Ensure JSON response
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);
class ImageHandler {
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    private $maxFileSize = 5242880; // 5MB
    private $maxFiles = 5;

    public function __construct($propertyType) {
        // Use DIRECTORY_SEPARATOR for cross-platform compatibility
        $baseDir = 'uploads' . DIRECTORY_SEPARATOR . $propertyType . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m');
        $this->uploadDir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'REAL-ESTATE' . DIRECTORY_SEPARATOR . $baseDir . DIRECTORY_SEPARATOR;
        
        // Create directory if it doesn't exist
        if (!file_exists($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0777, true)) {
                throw new Exception('Failed to create upload directory: ' . $this->uploadDir);
            }
            // Set proper permissions
            chmod($this->uploadDir, 0777);
        }
    }

    public function handleImageUploads($files) {
        if (!isset($files['images'])) {
            throw new Exception('No images were uploaded');
        }

        // Debug information
        error_log('Upload directory: ' . $this->uploadDir);
        error_log('Files received: ' . print_r($files['images'], true));

        if (count($files['images']['name']) > $this->maxFiles) {
            throw new Exception("Maximum {$this->maxFiles} images allowed");
        }

        $uploadedImages = [];
        $errors = [];

        foreach ($files['images']['tmp_name'] as $key => $tmp_name) {
            try {
                $fileName = $files['images']['name'][$key];
                $fileSize = $files['images']['size'][$key];
                $fileType = $files['images']['type'][$key];

                // Validate file
                $this->validateFile($fileName, $fileSize, $fileType, $key);

                // Generate unique filename
                $uniqueName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '_', basename($fileName));
                $targetPath = $this->uploadDir . $uniqueName;

                error_log('Attempting to move file to: ' . $targetPath);

                // Move file
                if (!move_uploaded_file($tmp_name, $targetPath)) {
                    throw new Exception("Failed to upload {$fileName}. PHP Error: " . error_get_last()['message']);
                }

                // Store relative path for database
                $propertyType = basename(dirname(dirname(dirname($targetPath))));
                $year = date('Y');
                $month = date('m');
                $relativePath = "uploads/{$propertyType}/{$year}/{$month}/{$uniqueName}";
                
                $uploadedImages[] = $relativePath;

                // Debug log
                error_log('Saved path: ' . $relativePath);

            } catch (Exception $e) {
                $errors[] = $e->getMessage();
                error_log('Upload error: ' . $e->getMessage());
            }
        }

        return [
            'success' => count($uploadedImages) > 0,
            'images' => $uploadedImages,
            'errors' => $errors
        ];
    }

    private function validateFile($fileName, $fileSize, $fileType, $key) {
        if ($fileSize > $this->maxFileSize) {
            throw new Exception("File {$fileName} exceeds maximum size of 5MB");
        }

        if (!in_array($fileType, $this->allowedTypes)) {
            throw new Exception("File {$fileName} has invalid type. Allowed types: JPG, JPEG, PNG");
        }

        $check = getimagesize($_FILES['images']['tmp_name'][$key]);
        if ($check === false) {
            throw new Exception("File {$fileName} is not a valid image");
        }
    }
}

?>