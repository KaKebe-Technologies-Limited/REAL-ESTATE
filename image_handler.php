<?php
header('Content-Type: application/json'); // Ensure JSON response
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
class ImageHandler {
    private $uploadDir;

    public function __construct($subDirectory = 'rentals') {
        // Create the uploads directory if it doesn't exist
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }

        // Create the subdirectory if it doesn't exist
        $this->uploadDir = 'uploads/' . $subDirectory;
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    private function ensureDirectoryExists() {
        // Create upload directory if it doesn't exist
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function handleImageUploads($files) {
        $uploadedImages = [];
        $errors = [];

        // Ensure upload directory exists
        $this->ensureDirectoryExists();

        // Check if files is a valid array with the expected structure
        if (!is_array($files) || empty($files)) {
            // No files uploaded, return success with empty images
            error_log('No files uploaded or empty files array');
            return [
                'success' => true,
                'images' => [],
                'errors' => []
            ];
        }

        // Check if the file input is empty (no files selected)
        if (isset($files['new_images']) &&
            isset($files['new_images']['error']) &&
            $files['new_images']['error'][0] === UPLOAD_ERR_NO_FILE) {
            // No files selected, return success with empty images
            error_log('No files selected (UPLOAD_ERR_NO_FILE)');
            return [
                'success' => true,
                'images' => [],
                'errors' => []
            ];
        }

        // Check if we have the expected structure for multiple file uploads
        if (!isset($files['name']) || !is_array($files['name'])) {
            // Check if this is a file upload with 'new_images' key
            if (isset($files['new_images']) && is_array($files['new_images'])) {
                // This is a file upload with a different structure
                error_log('Found new_images in files array');

                // Check if it's a single file or multiple files
                if (isset($files['new_images']['name']) && is_array($files['new_images']['name'])) {
                    // Multiple files
                    error_log('Processing multiple files in new_images');
                    $files = $files['new_images'];
                } else if (isset($files['new_images']['name'])) {
                    // Single file
                    error_log('Processing single file in new_images');
                    $files = $files['new_images'];
                } else {
                    error_log('Invalid structure in new_images: ' . print_r($files['new_images'], true));
                    return [
                        'success' => true,
                        'images' => [],
                        'errors' => ['Invalid file structure in new_images']
                    ];
                }
            } else {
                // Log the structure for debugging
                error_log('Invalid file structure: ' . print_r($files, true));
                return [
                    'success' => true, // Don't fail the whole operation just because of image issues
                    'images' => [],
                    'errors' => ['Invalid file upload structure']
                ];
            }
        }

        foreach ($files['name'] as $key => $name) {
            if ($files['error'][$key] === 0) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                $filepath = $this->uploadDir . '/' . $filename;

                if (move_uploaded_file($files['tmp_name'][$key], $filepath)) {
                    // Store the path relative to web root
                    $uploadedImages[] = $filepath;
                } else {
                    $errors[] = "Failed to move uploaded file: $name";
                }
            } else {
                $errors[] = "Error uploading file: $name";
            }
        }

        return [
            'success' => empty($errors),
            'images' => $uploadedImages,
            'errors' => $errors
        ];
    }

    public function deleteImages($imagePaths) {
        $deleted = [];
        $errors = [];

        foreach ($imagePaths as $path) {
            // Make sure we have the full path
            $fullPath = $path;
            if (file_exists($fullPath) && is_file($fullPath)) {
                if (unlink($fullPath)) {
                    $deleted[] = $path;
                } else {
                    $errors[] = "Failed to delete file: $path";
                }
            } else {
                $errors[] = "File not found: $path";
            }
        }

        return [
            'success' => empty($errors),
            'deleted' => $deleted,
            'errors' => $errors
        ];
    }
}
