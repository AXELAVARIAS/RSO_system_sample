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
        $expected_headers = ['Faculty Name', 'Degree', 'Sex', 'Research Title', 'Ownership', 'Presented At', 'Published Date', 'Journal/Publication'];
        $first_row = $data[0];
        // Remove BOM from first header if present
        if (isset($first_row[0])) {
            $first_row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $first_row[0]);
        }
        $first_row = array_map('trim', $first_row);
        // Map header names to their column index (case-insensitive)
        $header_map = [];
        foreach ($first_row as $idx => $header) {
            $header_clean = strtolower(str_replace([' ', '_', '/'], '', $header));
            $header_map[$header_clean] = $idx;
        }
        // Check for at least 6/8 required headers
        $header_matches = 0;
        foreach ($expected_headers as $expected) {
            $expected_clean = strtolower(str_replace([' ', '_', '/'], '', $expected));
            if (isset($header_map[$expected_clean])) {
                $header_matches++;
            }
        }
        if ($header_matches < 6) {
            $response['message'] = 'Invalid file format. Expected columns: Faculty Name, Degree, Sex, Research Title, Ownership, Presented At, Published Date, Journal/Publication';
            echo json_encode($response);
            exit;
        }
        // Helper to get value by header name
        function get_col($row, $header_map, $name) {
            $key = strtolower(str_replace([' ', '_', '/'], '', $name));
            return isset($header_map[$key]) ? trim($row[$header_map[$key]] ?? '') : '';
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
            $faculty = get_col($row, $header_map, 'Faculty Name');
            $degree = get_col($row, $header_map, 'Degree');
            $sex = get_col($row, $header_map, 'Sex');
            $title = get_col($row, $header_map, 'Research Title');
            $ownership = get_col($row, $header_map, 'Ownership');
            $presented = get_col($row, $header_map, 'Presented At');
            $published = get_col($row, $header_map, 'Published Date');
            $journal = get_col($row, $header_map, 'Journal/Publication');
            // Validate required fields
            if ($faculty === '' || $degree === '' || $sex === '' || $title === '' || $ownership === '' || $presented === '' || $published === '' || $journal === '') {
                $error_count++;
                $errors[] = "Row " . ($i + 2) . ": Missing required fields.";
                continue;
            }
            // Validate published date: accept YYYY-MM-DD, DD/MM/YYYY, MM/DD/YYYY, etc.
            $published = trim($published);
            $published_parsed = false;
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $published)) {
                $published_parsed = $published;
            } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $published)) {
                $parts = explode('/', $published);
                if ((int)$parts[0] > 12) {
                    $published_parsed = $parts[2] . '-' . str_pad($parts[1],2,'0',STR_PAD_LEFT) . '-' . str_pad($parts[0],2,'0',STR_PAD_LEFT);
                } else {
                    $published_parsed = $parts[2] . '-' . str_pad($parts[0],2,'0',STR_PAD_LEFT) . '-' . str_pad($parts[1],2,'0',STR_PAD_LEFT);
                }
            } else {
                $timestamp = strtotime($published);
                if ($timestamp !== false) {
                    $published_parsed = date('Y-m-d', $timestamp);
                }
            }
            if (!$published_parsed) {
                $error_count++;
                $errors[] = "Row " . ($i + 2) . ": Invalid published date format (got '$published').";
                continue;
            }
            try {
                $db->query(
                    "INSERT INTO data_collection_tools (researcher_name, degree, gender, research_title, role, location, submission_date, research_area) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [$faculty, $degree, $sex, $title, $ownership, $presented, $published_parsed, $journal]
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