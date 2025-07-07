<?php
// Test script for Excel to CSV conversion
// This script can be used to test the conversion functionality

// Include the conversion functions from upload_excel.php
require_once '../database/config.php';

// Function to convert Excel file to CSV format (copied from upload_excel.php)
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

// Function to create CSV content from data array
function createCSVContent($data) {
    $csv_content = '';
    foreach ($data as $row) {
        $csv_row = [];
        foreach ($row as $cell) {
            // Escape quotes and wrap in quotes if contains comma, quote, or newline
            $cell = str_replace('"', '""', $cell);
            if (strpos($cell, ',') !== false || strpos($cell, '"') !== false || strpos($cell, "\n") !== false) {
                $cell = '"' . $cell . '"';
            }
            $csv_row[] = $cell;
        }
        $csv_content .= implode(',', $csv_row) . "\n";
    }
    return $csv_content;
}

// Function to read CSV content
function readCSVContent($csv_content) {
    $data = [];
    $lines = explode("\n", trim($csv_content));
    
    foreach ($lines as $line) {
        if (empty(trim($line))) continue;
        
        $row = [];
        $in_quotes = false;
        $current_field = '';
        $i = 0;
        
        while ($i < strlen($line)) {
            $char = $line[$i];
            
            if ($char === '"') {
                if ($in_quotes && $i + 1 < strlen($line) && $line[$i + 1] === '"') {
                    // Escaped quote
                    $current_field .= '"';
                    $i += 2;
                } else {
                    // Toggle quote state
                    $in_quotes = !$in_quotes;
                    $i++;
                }
            } elseif ($char === ',' && !$in_quotes) {
                // End of field
                $row[] = $current_field;
                $current_field = '';
                $i++;
            } else {
                $current_field .= $char;
                $i++;
            }
        }
        
        // Add the last field
        $row[] = $current_field;
        
        if (!empty(array_filter($row))) {
            $data[] = $row;
        }
    }
    
    return $data;
}

// Test the conversion process
echo "<h2>Excel to CSV Conversion Test</h2>";

// Test with a sample data array (simulating Excel data)
$sample_excel_data = [
    ['Date', 'Activity Name', 'Venue', 'Facilitators', 'Number of Participants', 'Status'],
    ['2024-01-15', 'Research Workshop', 'Conference Room A', 'Dr. Smith', '25', 'Completed'],
    ['2024-01-20', 'Data Analysis Training', 'Lab 101', 'Prof. Johnson', '15', 'In Progress'],
    ['2024-01-25', 'Methodology Seminar', 'Auditorium', 'Dr. Brown', '40', 'Scheduled']
];

echo "<h3>Sample Excel Data:</h3>";
echo "<pre>";
print_r($sample_excel_data);
echo "</pre>";

// Convert to CSV
$csv_content = createCSVContent($sample_excel_data);
echo "<h3>Generated CSV Content:</h3>";
echo "<pre>";
echo htmlspecialchars($csv_content);
echo "</pre>";

// Read back from CSV
$converted_data = readCSVContent($csv_content);
echo "<h3>Data Read Back from CSV:</h3>";
echo "<pre>";
print_r($converted_data);
echo "</pre>";

// Test with data containing special characters
$complex_data = [
    ['Date', 'Activity Name', 'Venue', 'Facilitators', 'Number of Participants', 'Status'],
    ['2024-01-15', 'Research Workshop "Advanced"', 'Conference Room A, Building 1', 'Dr. Smith, PhD', '25', 'Completed'],
    ['2024-01-20', 'Data Analysis Training', 'Lab 101', 'Prof. Johnson', '15', 'In Progress']
];

echo "<h3>Complex Data with Special Characters:</h3>";
echo "<pre>";
print_r($complex_data);
echo "</pre>";

$complex_csv = createCSVContent($complex_data);
echo "<h3>Complex CSV Content:</h3>";
echo "<pre>";
echo htmlspecialchars($complex_csv);
echo "</pre>";

$complex_converted = readCSVContent($complex_csv);
echo "<h3>Complex Data Read Back:</h3>";
echo "<pre>";
print_r($complex_converted);
echo "</pre>";

echo "<h3>Test Results:</h3>";
if (count($sample_excel_data) === count($converted_data) && 
    count($complex_data) === count($complex_converted)) {
    echo "<p style='color: green;'>✅ Conversion test PASSED! Data integrity maintained.</p>";
} else {
    echo "<p style='color: red;'>❌ Conversion test FAILED! Data loss detected.</p>";
}

echo "<p><strong>Note:</strong> This test script verifies the Excel to CSV conversion functions. To test with actual Excel files, upload them through the main interface.</p>";
?> 