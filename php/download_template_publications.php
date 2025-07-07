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
header('Content-Disposition: attachment; filename="publications_template.csv"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Create output stream
$output = fopen('php://output', 'w');

// Don't add BOM - it causes parsing issues
// fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write headers
$headers = [
    'Date OF Application',
    'Name(s) of faculty/research worker',
    'Title of Paper',
    'Department',
    'Research Subsidy',
    'Status',
    'Local/International'
];

fputcsv($output, $headers);

// Add sample data row
$sample_data = [
    '2025-01-15',
    'Dr. John Smith',
    'Sample Research Paper Title',
    'Computer Science',
    '₱500,000',
    'Draft',
    'Local'
];

fputcsv($output, $sample_data);

// Close the file
fclose($output);
exit;
?> 