<?php
session_start();

// Include database configuration
require_once '../database/config.php';

if (empty($_SESSION['logged_in'])) {
    header('Location: loginpage.php');
    exit;
}

// Set headers for file download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="kpi_records_template.csv"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Create output stream
$output = fopen('php://output', 'w');

// Don't add BOM - it causes parsing issues
// fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write headers
$headers = [
    'Faculty Name',
    'Period',
    'Publications',
    'Presentations',
    'Research Projects',
    'KPI Score',
    'Performance Rating'
];

fputcsv($output, $headers);

// Add sample data row
$sample_data = [
    'Dr. John Smith',
    'Q1 2025',
    '3',
    '2',
    '1',
    '85.5',
    'Very Good'
];

fputcsv($output, $sample_data);

// Add another sample row
$sample_data2 = [
    'Dr. Jane Doe',
    'Q1 2025',
    '5',
    '3',
    '2',
    '92.0',
    'Excellent'
];

fputcsv($output, $sample_data2);

// Close the file
fclose($output);
exit;
?> 