<?php
session_start();

// Include database configuration
require_once '../database/config.php';

if (empty($_SESSION['logged_in'])) {
    header('Location: loginpage.php');
    exit;
}

$response = ['success' => false, 'message' => '', 'data' => []];

// Log all request data for debugging
error_log("Upload debug - Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("Upload debug - POST data: " . print_r($_POST, true));
error_log("Upload debug - FILES data: " . print_r($_FILES, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];
    
    error_log("Upload debug - File received: " . $file['name']);
    error_log("Upload debug - File size: " . $file['size']);
    error_log("Upload debug - File error: " . $file['error']);
    error_log("Upload debug - File type: " . $file['type']);
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'File upload failed: ' . $file['error'];
        error_log("Upload debug - Upload error: " . $file['error']);
        echo json_encode($response);
        exit;
    }
    
    // Validate file type
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['xls', 'xlsx', 'csv'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        $response['message'] = 'Invalid file type. Please upload an Excel file (.xls, .xlsx) or CSV file.';
        error_log("Upload debug - Invalid file extension: " . $file_extension);
        echo json_encode($response);
        exit;
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        $response['message'] = 'File size too large. Maximum size is 5MB.';
        error_log("Upload debug - File too large: " . $file['size']);
        echo json_encode($response);
        exit;
    }
    
    try {
        $db = getDB();
        error_log("Upload debug - Database connection successful");
        
        // For testing, just read the file and return success
        $data = [];
        
        if ($file_extension === 'csv') {
            // Handle CSV files
            $handle = fopen($file['tmp_name'], 'r');
            if ($handle) {
                while (($row = fgetcsv($handle)) !== false) {
                    if (!empty(array_filter($row))) { // Skip empty rows
                        $data[] = $row;
                    }
                }
                fclose($handle);
            }
        } else {
            // For Excel files, just read as text for now
            $content = file_get_contents($file['tmp_name']);
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                if (trim($line)) {
                    $data[] = explode(',', $line);
                }
            }
        }
        
        error_log("Upload debug - Data rows read: " . count($data));
        
        if (empty($data)) {
            $response['message'] = 'No data found in the uploaded file.';
            error_log("Upload debug - No data found in file");
            echo json_encode($response);
            exit;
        }
        
        // For testing, just return success with file info
        $response['success'] = true;
        $response['message'] = "Test upload successful. File: " . $file['name'] . ", Rows: " . count($data);
        $response['data'] = [
            'filename' => $file['name'],
            'rows' => count($data),
            'sample_data' => array_slice($data, 0, 3) // First 3 rows for debugging
        ];
        
        error_log("Upload debug - Test upload successful");
        
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log("Upload debug - Database error: " . $e->getMessage());
    }
} else {
    $response['message'] = 'No file uploaded or invalid request method.';
    error_log("Upload debug - No file uploaded or invalid request");
}

echo json_encode($response);
?> 