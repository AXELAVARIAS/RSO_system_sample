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
            // Handle CSV files with better parsing
            $handle = fopen($file['tmp_name'], 'r');
            if ($handle) {
                // Try to detect the delimiter
                $first_line = fgets($handle);
                rewind($handle);
                
                // Count commas and semicolons to determine delimiter
                $comma_count = substr_count($first_line, ',');
                $semicolon_count = substr_count($first_line, ';');
                $tab_count = substr_count($first_line, "\t");
                
                $delimiter = ',';
                if ($semicolon_count > $comma_count && $semicolon_count > $tab_count) {
                    $delimiter = ';';
                } elseif ($tab_count > $comma_count && $tab_count > $semicolon_count) {
                    $delimiter = "\t";
                }
                
                error_log("Detected delimiter: " . ($delimiter === "\t" ? "TAB" : $delimiter));
                
                while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
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
        
        // Debug: Log the raw data structure
        error_log("Raw data structure: " . json_encode($data));
        error_log("Number of rows: " . count($data));
        if (!empty($data)) {
            error_log("First row column count: " . count($data[0]));
            error_log("First row raw: " . json_encode($data[0]));
            
            // If first row has only one column, try manual parsing
            if (count($data[0]) === 1 && $file_extension === 'csv') {
                error_log("Detected single column - attempting manual parsing");
                $data = manualCSVParsing($file['tmp_name']);
                error_log("After manual parsing - rows: " . count($data));
                if (!empty($data)) {
                    error_log("First row after manual parsing: " . json_encode($data[0]));
                }
            }
        }
        
        // Validate header row
        $expected_headers = ['Date OF Application', 'Name(s) of faculty/research worker', 'Title of Paper', 'Department', 'Research Subsidy', 'Status', 'Local/International'];
        $first_row = $data[0];
        
        // Clean headers - remove BOM, quotes, and extra whitespace
        $first_row = array_map(function($header) {
            // Remove various BOM characters
            $header = preg_replace('/^\xEF\xBB\xBF/', '', $header); // UTF-8 BOM
            $header = preg_replace('/^\xFE\xFF/', '', $header);     // UTF-16 BE BOM
            $header = preg_replace('/^\xFF\xFE/', '', $header);     // UTF-16 LE BOM
            $header = preg_replace('/^\x00\x00\xFE\xFF/', '', $header); // UTF-32 BE BOM
            $header = preg_replace('/^\xFF\xFE\x00\x00/', '', $header); // UTF-32 LE BOM
            
            // Remove quotes and extra whitespace
            $header = trim($header, '"\'');
            $header = trim($header);
            
            // Remove any remaining invisible characters
            $header = preg_replace('/[\x00-\x1F\x7F]/', '', $header);
            
            return $header;
        }, $first_row);
        
        // Debug: Log the actual headers found
        error_log("Found headers: " . implode(", ", $first_row));
        error_log("Cleaned headers: " . json_encode($first_row));
        
        // Map header names to their column index (case-insensitive and more flexible)
        $header_map = [];
        foreach ($first_row as $idx => $header) {
            $header_clean = strtolower(str_replace([' ', '_', '-', '(', ')', '/'], '', $header));
            $header_map[$header_clean] = $idx;
            // Also store the original cleaned header for debugging
            $header_map['original_' . $idx] = $header;
        }
        
        // Simple direct header matching
        $required_headers = [
            'Date OF Application',
            'Name(s) of faculty/research worker', 
            'Title of Paper',
            'Department',
            'Research Subsidy'
        ];
        
        $header_matches = 0;
        $matched_headers = [];
        
        foreach ($required_headers as $expected) {
            $found = false;
            foreach ($first_row as $idx => $header) {
                if (strcasecmp(trim($header), trim($expected)) === 0) {
                    $header_matches++;
                    $matched_headers[$expected] = $idx;
                    error_log("Found exact match for '$expected' at index $idx");
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                error_log("No match found for '$expected'");
            }
        }
        
        error_log("Header matches: $header_matches, Matched headers: " . json_encode($matched_headers));
        
        if ($header_matches < 5) {
            $response['message'] = 'Invalid file format. Required columns: Date OF Application, Name(s) of faculty/research worker, Title of Paper, Department, Research Subsidy. Found: ' . implode(", ", $first_row) . '. Matched: ' . $header_matches . ' out of 5 required.';
            echo json_encode($response);
            exit;
        }
        
        // Helper to get value by header name
        function get_col($row, $matched_headers, $name) {
            if (isset($matched_headers[$name])) {
                return trim($row[$matched_headers[$name]] ?? '');
            }
            return '';
        }
        // Process data rows (skip header)
        $success_count = 0;
        $error_count = 0;
        $errors = [];
        $valid_statuses = ['Draft', 'Submitted', 'Under Review', 'Accepted', 'Published', 'Rejected'];
        $valid_scopes = ['Local', 'International'];
        foreach (array_slice($data, 1) as $i => $row) {
            // Skip empty rows
            if (empty(array_filter($row, function($v){return trim($v) !== '';}))) {
                continue;
            }
            // Get and trim all values
            $date = get_col($row, $matched_headers, 'Date OF Application');
            $author = get_col($row, $matched_headers, 'Name(s) of faculty/research worker');
            $title = get_col($row, $matched_headers, 'Title of Paper');
            $department = get_col($row, $matched_headers, 'Department');
            $subsidy = get_col($row, $matched_headers, 'Research Subsidy');
            $status = get_col($row, $matched_headers, 'Status');
            $scope = get_col($row, $matched_headers, 'Local/International');
            // Debug: Log the extracted values
            error_log("Row " . ($i + 2) . " values - Date: '$date', Author: '$author', Title: '$title', Department: '$department', Subsidy: '$subsidy'");
            
            // Validate required fields
            if ($date === '' || $author === '' || $title === '' || $department === '' || $subsidy === '') {
                $error_count++;
                $errors[] = "Row " . ($i + 2) . ": Missing required fields. Date: '$date', Author: '$author', Title: '$title', Department: '$department', Subsidy: '$subsidy'";
                continue;
            }
            // Handle date: accept YYYY-MM-DD, DD/MM/YYYY, MM/DD/YYYY, etc.
            $date = trim($date);
            $date_parsed = false;
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $date_parsed = $date;
            } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
                // Convert DD/MM/YYYY or MM/DD/YYYY to YYYY-MM-DD
                $parts = explode('/', $date);
                // Try DD/MM/YYYY first (common outside US)
                if ((int)$parts[0] > 12) {
                    $date_parsed = $parts[2] . '-' . str_pad($parts[1],2,'0',STR_PAD_LEFT) . '-' . str_pad($parts[0],2,'0',STR_PAD_LEFT);
                } else {
                    // Try MM/DD/YYYY (US style)
                    $date_parsed = $parts[2] . '-' . str_pad($parts[0],2,'0',STR_PAD_LEFT) . '-' . str_pad($parts[1],2,'0',STR_PAD_LEFT);
                }
            } else {
                $timestamp = strtotime($date);
                if ($timestamp !== false) {
                    $date_parsed = date('Y-m-d', $timestamp);
                }
            }
            if (!$date_parsed) {
                $error_count++;
                $errors[] = "Row " . ($i + 2) . ": Invalid date format (got '$date').";
                continue;
            }
            // Validate status (case-insensitive, trim)
            $status_clean = ucwords(strtolower(trim($status)));
            if (!in_array($status_clean, $valid_statuses)) {
                $status_clean = 'Draft';
            }
            // Validate scope (case-insensitive, trim)
            $scope_clean = ucwords(strtolower(trim($scope)));
            if (!in_array($scope_clean, $valid_scopes)) {
                $scope_clean = 'Local';
            }
            try {
                $db->query(
                    "INSERT INTO publication_presentations (application_date, author_name, paper_title, department, research_subsidy, status, scope) VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$date_parsed, $author, $title, $department, $subsidy, $status_clean, $scope_clean]
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
            $response['message'] = "Successfully imported $success_count publications.";
            if ($error_count > 0) {
                $response['message'] .= " $error_count rows had errors.";
            }
            $response['data'] = [
                'success_count' => $success_count,
                'error_count' => $error_count,
                'errors' => array_slice($errors, 0, 10) // Limit to first 10 errors
            ];
        } else {
            $response['message'] = "No publications were imported. $error_count rows had errors.";
            $response['data'] = [
                'success_count' => 0,
                'error_count' => $error_count,
                'errors' => array_slice($errors, 0, 10)
            ];
        }
        
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'No file uploaded or invalid request method.';
}

echo json_encode($response);

// Function to convert Excel files to CSV format
function convertExcelToCSV($file_path) {
    $data = [];
    
    // Check if PhpSpreadsheet is available
    if (class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();
        } catch (Exception $e) {
            // Fallback to manual parsing
            $data = manualExcelParse($file_path);
        }
    } else {
        // Manual parsing for .xls files
        $data = manualExcelParse($file_path);
    }
    
    return $data;
}

// Manual Excel parsing function
function manualExcelParse($file_path) {
    $data = [];
    $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    
    if ($file_extension === 'xlsx') {
        // For .xlsx files, try to read as ZIP and extract XML
        $zip = new ZipArchive;
        if ($zip->open($file_path) === TRUE) {
            $xml_string = $zip->getFromName('xl/worksheets/sheet1.xml');
            $zip->close();
            
            if ($xml_string) {
                $xml = simplexml_load_string($xml_string);
                if ($xml) {
                    foreach ($xml->sheetData->row as $row) {
                        $row_data = [];
                        foreach ($row->c as $cell) {
                            $value = (string)$cell->v;
                            $row_data[] = $value;
                        }
                        if (!empty(array_filter($row_data))) {
                            $data[] = $row_data;
                        }
                    }
                }
            }
        }
    } elseif ($file_extension === 'xls') {
        // For .xls files, try to read as binary
        $handle = fopen($file_path, 'rb');
        if ($handle) {
            $content = fread($handle, filesize($file_path));
            fclose($handle);
            
            // Simple parsing - look for printable characters
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                $row_data = [];
                // Split by tabs or other delimiters
                $cells = preg_split('/[\t,;]/', $line);
                foreach ($cells as $cell) {
                    $cell = trim($cell);
                    // Remove non-printable characters
                    $cell = preg_replace('/[^\x20-\x7E]/', '', $cell);
                    if (!empty($cell)) {
                        $row_data[] = $cell;
                    }
                }
                if (!empty($row_data)) {
                    $data[] = $row_data;
                }
            }
        }
    }
    
    return $data;
}

// Manual CSV parsing function for problematic files
function manualCSVParsing($file_path) {
    $data = [];
    $content = file_get_contents($file_path);
    
    // Remove BOM if present
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
    
    // Split into lines
    $lines = explode("\n", $content);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Try different delimiters
        $delimiters = [',', ';', "\t"];
        $best_delimiter = ',';
        $max_columns = 1;
        
        foreach ($delimiters as $delimiter) {
            $columns = str_getcsv($line, $delimiter);
            if (count($columns) > $max_columns) {
                $max_columns = count($columns);
                $best_delimiter = $delimiter;
            }
        }
        
        // Parse with best delimiter
        $row = str_getcsv($line, $best_delimiter);
        if (!empty(array_filter($row))) {
            $data[] = $row;
        }
    }
    
    return $data;
}
?> 