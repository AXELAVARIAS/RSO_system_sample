<?php
session_start();

// Include database configuration
require_once '../database/config.php';

if (empty($_SESSION['logged_in'])) {
    header('Location: loginpage.php');
    exit;
}

$response = ['success' => false, 'message' => '', 'data' => []];

// Debug logging
error_log("Upload debug - Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("Upload debug - POST data: " . print_r($_POST, true));
error_log("Upload debug - FILES data: " . print_r($_FILES, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'File upload failed: ' . $file['error'];
        echo json_encode($response);
        exit;
    }
    
    // Validate file type
    $allowed_types = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['xls', 'xlsx', 'csv'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        $response['message'] = 'Invalid file type. Please upload an Excel file (.xls, .xlsx) or CSV file.';
        echo json_encode($response);
        exit;
    }
    
    // Additional MIME type validation for security
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    // Allow common MIME types for Excel and CSV files
    $allowed_mime_types = [
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
        'text/plain', // Some systems report CSV as text/plain
        'application/csv',
        'application/excel',
        'application/vnd.msexcel'
    ];
    
    if (!in_array($mime_type, $allowed_mime_types)) {
        $response['message'] = 'Invalid file format. Please upload a valid Excel or CSV file.';
        echo json_encode($response);
        exit;
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        $response['message'] = 'File size too large. Maximum size is 5MB.';
        echo json_encode($response);
        exit;
    }
    
    try {
        $db = getDB();
        
        // Read the uploaded file
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
            // Handle Excel files (.xls, .xlsx)
            $data = convertExcelToCSV($file['tmp_name']);
        }
        
        if (empty($data)) {
            $response['message'] = 'No data found in the uploaded file.';
            echo json_encode($response);
            exit;
        }
        
        // Validate header row
        $expected_headers = ['Protocol Number', 'Research Title', 'Department', 'Status', 'Action Taken'];
        $first_row = $data[0];
        // Remove BOM from first header if present
        if (isset($first_row[0])) {
            $first_row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $first_row[0]);
        }
        $first_row = array_map('trim', $first_row);
        // Map header names to their column index (case-insensitive)
        $header_map = [];
        foreach ($first_row as $idx => $header) {
            $header_clean = strtolower(str_replace([' ', '_'], '', $header));
            $header_map[$header_clean] = $idx;
        }
        // Check for at least 4/5 required headers
        $header_matches = 0;
        foreach ($expected_headers as $expected) {
            $expected_clean = strtolower(str_replace([' ', '_'], '', $expected));
            if (isset($header_map[$expected_clean])) {
                $header_matches++;
            }
        }
        if ($header_matches < 4) {
            $response['message'] = 'Invalid file format. Expected columns: Protocol Number, Research Title, Department, Status, Action Taken';
            echo json_encode($response);
            exit;
        }
        // Helper to get value by header name
        function get_col($row, $header_map, $name) {
            $key = strtolower(str_replace([' ', '_'], '', $name));
            return isset($header_map[$key]) ? trim($row[$header_map[$key]] ?? '') : '';
        }
        // Process data rows (skip header)
        $success_count = 0;
        $error_count = 0;
        $errors = [];
        $valid_statuses = ['Approved', 'Under Review', 'Pending'];
        foreach (array_slice($data, 1) as $i => $row) {
            // Skip empty rows
            if (empty(array_filter($row, function($v){return trim($v) !== '';}))) {
                continue;
            }
            // Get and trim all values
            $protocol_number = get_col($row, $header_map, 'Protocol Number');
            $title = get_col($row, $header_map, 'Research Title');
            $department = get_col($row, $header_map, 'Department');
            $status = get_col($row, $header_map, 'Status');
            $action_taken = get_col($row, $header_map, 'Action Taken');
            // Validate required fields
            if ($protocol_number === '' || $title === '' || $department === '' || $status === '' || $action_taken === '') {
                $error_count++;
                $errors[] = "Row " . ($i + 2) . ": Missing required fields.";
                continue;
            }
            // Validate status (case-insensitive, trim)
            $status_clean = ucwords(strtolower(trim($status)));
            if (!in_array($status_clean, $valid_statuses)) {
                $status_clean = 'Pending';
            }
            try {
                $db->query(
                    "INSERT INTO ethics_reviewed_protocols (protocol_number, title, department, status, action_taken) VALUES (?, ?, ?, ?, ?)",
                    [$protocol_number, $title, $department, $status_clean, $action_taken]
                );
                $success_count++;
            } catch (Exception $e) {
                $error_count++;
                $errors[] = "Row " . ($i + 2) . ": " . $e->getMessage();
            }
        }
        
        // Prepare response
        if ($success_count > 0) {
            $response['success'] = true;
            $response['message'] = "Successfully imported $success_count protocols.";
            if ($error_count > 0) {
                $response['message'] .= " $error_count rows had errors.";
            }
            $response['data'] = [
                'success_count' => $success_count,
                'error_count' => $error_count,
                'errors' => $errors
            ];
            
            // Log successful upload
            error_log("Excel upload successful: $success_count protocols imported, $error_count errors. File: " . $file['name']);
        } else {
            $response['message'] = "No protocols were imported. Please check your file format.";
            $response['data'] = ['errors' => $errors];
            
            // Log failed upload
            error_log("Excel upload failed: No protocols imported. File: " . $file['name'] . ". Errors: " . implode(', ', $errors));
        }
        
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'No file uploaded.';
}

error_log("Upload debug - Final response: " . json_encode($response));
echo json_encode($response);

// Function to convert Excel file to CSV format
function convertExcelToCSV($file_path) {
    $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    $data = [];
    
    if ($file_extension === 'xlsx') {
        // Handle .xlsx files (ZIP-based format)
        $zip = new ZipArchive;
        if ($zip->open($file_path) === TRUE) {
            // Read shared strings
            $shared_strings = [];
            $shared_strings_xml = $zip->getFromName('xl/sharedStrings.xml');
            if ($shared_strings_xml) {
                // Extract text from shared strings
                preg_match_all('/<t[^>]*>(.*?)<\/t>/s', $shared_strings_xml, $matches);
                $shared_strings = $matches[1];
            }
            
            // Read sheet data
            $sheet_xml = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($sheet_xml) {
                // Extract row data
                preg_match_all('/<row[^>]*>(.*?)<\/row>/s', $sheet_xml, $row_matches);
                
                foreach ($row_matches[1] as $row_xml) {
                    $row_data = [];
                    preg_match_all('/<c[^>]*><v>(.*?)<\/v><\/c>/s', $row_xml, $cell_matches);
                    
                    foreach ($cell_matches[1] as $cell_value) {
                        if (preg_match('/^(\d+)$/', $cell_value)) {
                            // Numeric value
                            $row_data[] = $cell_value;
                        } else {
                            // String value (reference to shared strings)
                            $index = (int)$cell_value;
                            $row_data[] = isset($shared_strings[$index]) ? $shared_strings[$index] : '';
                        }
                    }
                    
                    if (!empty($row_data) && !empty(array_filter($row_data))) {
                        $data[] = $row_data;
                    }
                }
            }
            
            $zip->close();
        }
    } elseif ($file_extension === 'xls') {
        // For .xls files, try to extract text content
        $handle = fopen($file_path, 'rb');
        if ($handle) {
            $content = fread($handle, filesize($file_path));
            fclose($handle);
            
            // Extract readable text from binary content
            $text_content = '';
            for ($i = 0; $i < strlen($content); $i++) {
                $char = $content[$i];
                if (ord($char) >= 32 && ord($char) <= 126) {
                    $text_content .= $char;
                }
            }
            
            // Try to find tabular data
            $lines = explode("\n", $text_content);
            foreach ($lines as $line) {
                $line = trim($line);
                if (strlen($line) > 5) {
                    // Split by common delimiters
                    $parts = preg_split('/[\t,;]+/', $line);
                    if (count($parts) >= 3) {
                        $data[] = $parts;
                    }
                }
            }
        }
    }
    
    return $data;
}
?> 