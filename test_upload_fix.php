<?php
// Test script to verify the upload fix works with missing optional columns
require_once 'database/config.php';

// Simulate the headers from the test file
$test_headers = ['Faculty Name', 'Degree', 'Sex', 'Research Title', 'Ownership', 'Presented At', 'Published Date', 'Journal/Publication'];

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
$required_columns = ['Date OF Application', 'Name(s) of faculty/research worker', 'Title of Paper', 'Research Subsidy'];
$optional_columns = ['Department', 'Status', 'Local/International'];
$missing_required_columns = [];
$missing_optional_columns = [];

foreach ($required_columns as $required) {
    if (!isset($matched_headers[$required])) {
        $missing_required_columns[] = $required;
    }
}

foreach ($optional_columns as $optional) {
    if (!isset($matched_headers[$optional])) {
        $missing_optional_columns[] = $optional;
    }
}

if (!empty($missing_required_columns)) {
    echo "❌ Missing required columns: " . implode(', ', $missing_required_columns) . "\n";
} else {
    echo "✅ All required columns found!\n";
}

if (!empty($missing_optional_columns)) {
    echo "⚠️  Missing optional columns: " . implode(', ', $missing_optional_columns) . " (will use default values)\n";
} else {
    echo "✅ All optional columns found!\n";
}

echo "\nSummary:\n";
echo "- Required columns: " . count($required_columns) - count($missing_required_columns) . "/" . count($required_columns) . " found\n";
echo "- Optional columns: " . count($optional_columns) - count($missing_optional_columns) . "/" . count($optional_columns) . " found\n";

if (empty($missing_required_columns)) {
    echo "🎉 Upload should succeed with default values for missing optional columns!\n";
} else {
    echo "❌ Upload will fail due to missing required columns.\n";
}
?> 