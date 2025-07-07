<?php
session_start();

// Include database configuration
require_once '../database/config.php';

if (empty($_SESSION['logged_in'])) {
    header('Location: loginpage.php');
    exit;
}

$response = ['success' => false, 'message' => '', 'data' => []];

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
        // Remove BOM and quotes from first header if present
        foreach ($first_row as &$header) {
            $header = preg_replace('/^\xEF\xBB\xBF/', '', $header); // Remove BOM
            $header = trim($header, " \t\n\r\0\x0B\""); // Remove spaces and quotes
        }
        unset($header);
        $first_row = array_map('trim', $first_row);
        // Map header names to their column index (case-insensitive, ignore spaces, underscores, slashes, and quotes)
        $header_map = [];
        foreach ($first_row as $idx => $header) {
            $header_clean = strtolower(str_replace([' ', '_', '/', '"', "'"], '', $header));
            $header_map[$header_clean] = $idx;
        }
        // Check for at least 4/5 required headers (allow some flexibility, partial match)
        $header_matches = 0;
        foreach ($expected_headers as $expected) {
            $expected_clean = strtolower(str_replace([' ', '_', '/', '"', "'"], '', $expected));
            // Allow partial match (e.g., 'departme' for 'department')
            foreach ($header_map as $header_key => $idx) {
                if (strpos($header_key, $expected_clean) !== false || strpos($expected_clean, $header_key) !== false) {
                    $header_matches++;
                    break;
                }
            }
        }
        if ($header_matches < 4) {
            $response['message'] = 'Invalid file format. Expected columns: Protocol Number, Research Title, Department, Status, Action Taken';
            echo json_encode($response);
            exit;
        }
        // Helper to get value by header name (use partial match)
        function get_col($row, $header_map, $name) {
            $key = strtolower(str_replace([' ', '_', '/', '"', "'"], '', $name));
            foreach ($header_map as $header_key => $idx) {
                if (strpos($header_key, $key) !== false || strpos($key, $header_key) !== false) {
                    return isset($row[$idx]) ? trim($row[$idx]) : '';
                }
            }
            return '';
        }
        // Process data rows (skip header)
        $success_count = 0;
        $error_count = 0;
        $errors = [];
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
            try {
                $db->query(
                    "INSERT INTO ethics_reviewed_protocols (protocol_number, title, department, status, action_taken) VALUES (?, ?, ?, ?, ?)",
                    [$protocol_number, $title, $department, $status, $action_taken]
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
            $response['message'] = "Successfully imported $success_count entries.";
            if ($error_count > 0) {
                $response['message'] .= " $error_count rows had errors.";
            }
        } else {
            $response['message'] = $error_count > 0 ? "All rows had errors." : "No valid data found.";
        }
        $response['data'] = ['errors' => $errors];
        echo json_encode($response);
        exit;
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        echo json_encode($response);
        exit;
    }
}

// Helper: Excel to CSV conversion using PhpSpreadsheet if available
function convertExcelToCSV($file_path) {
    if (!class_exists('PhpOffice\\PhpSpreadsheet\\IOFactory')) {
        require_once __DIR__ . '/../vendor/autoload.php';
    }
    $data = [];
    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
        $worksheet = $spreadsheet->getActiveSheet();
        foreach ($worksheet->toArray() as $row) {
            $data[] = $row;
        }
    } catch (Exception $e) {
        // fallback: return empty
    }
    return $data;
} 