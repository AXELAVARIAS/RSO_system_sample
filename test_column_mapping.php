<?php
// Simple test script to verify column mapping
require_once 'database/config.php';

// Test data with the problematic headers
$test_headers = ['Faculty Name', 'Degree', 'Sex', 'Research Title', 'Department', 'Ownership', 'Presented At', 'Published Date', 'Journal/Publication'];

// Define column mapping for common variations
$column_mapping = [
    'Date OF Application' => [
        'date of application', 'application date', 'date', 'submission date', 'published date', 'presented at', 'published date'
    ],
    'Name(s) of faculty/research worker' => [
        'name(s) of faculty/research worker', 'faculty name', 'author name', 'researcher name', 'name', 'author', 'faculty/research worker', 'faculty/research worker'
    ],
    'Title of Paper' => [
        'title of paper', 'research title', 'paper title', 'title', 'research paper title', 'publication title'
    ],
    'Department' => [
        'department', 'faculty', 'school', 'college', 'institution'
    ],
    'Research Subsidy' => [
        'research subsidy', 'subsidy', 'funding', 'grant amount', 'research funding', 'ownership'
    ],
    'Status' => [
        'status', 'publication status', 'paper status', 'submission status'
    ],
    'Local/International' => [
        'local/international', 'scope', 'local or international', 'publication scope', 'journal/publication', 'journal/publication'
    ]
];

// Map header names to their column index (case-insensitive and flexible)
$matched_headers = [];
$unmatched_headers = [];

foreach ($test_headers as $idx => $header) {
    $header_lower = strtolower(trim($header));
    $matched = false;
    
    // Try exact match first
    foreach ($column_mapping as $expected => $variations) {
        if (strcasecmp($header_lower, strtolower($expected)) === 0) {
            $matched_headers[$expected] = $idx;
            echo "Exact match found for '$expected' at index $idx\n";
            $matched = true;
            break;
        }
    }
    
    // Try variations if no exact match
    if (!$matched) {
        foreach ($column_mapping as $expected => $variations) {
            foreach ($variations as $variation) {
                if (strcasecmp($header_lower, $variation) === 0) {
                    $matched_headers[$expected] = $idx;
                    echo "Variation match found: '$header' -> '$expected' at index $idx\n";
                    $matched = true;
                    break 2;
                }
            }
        }
    }
    
    // Try partial matching for common patterns
    if (!$matched) {
        foreach ($column_mapping as $expected => $variations) {
            foreach ($variations as $variation) {
                if (strpos($header_lower, $variation) !== false || strpos($variation, $header_lower) !== false) {
                    $matched_headers[$expected] = $idx;
                    echo "Partial match found: '$header' -> '$expected' at index $idx\n";
                    $matched = true;
                    break 2;
                }
            }
        }
    }
    
    if (!$matched) {
        $unmatched_headers[] = $header;
    }
}

echo "\nTest Results:\n";
echo "Found headers: " . implode(", ", $test_headers) . "\n";
echo "Matched headers: " . json_encode($matched_headers) . "\n";
echo "Unmatched headers: " . implode(", ", $unmatched_headers) . "\n";

// Check if we have the minimum required columns
$required_columns = ['Date OF Application', 'Name(s) of faculty/research worker', 'Title of Paper', 'Department', 'Research Subsidy'];
$missing_columns = [];

foreach ($required_columns as $required) {
    if (!isset($matched_headers[$required])) {
        $missing_columns[] = $required;
    }
}

if (!empty($missing_columns)) {
    echo "Missing required columns: " . implode(', ', $missing_columns) . "\n";
} else {
    echo "All required columns found!\n";
}
?> 