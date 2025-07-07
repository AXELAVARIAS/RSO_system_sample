<?php
session_start();

// Include database configuration
require_once '../database/config.php';

if (empty($_SESSION['logged_in'])) {
    header('Location: loginpage.php');
    exit;
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="research_capacity_template.csv"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Create output stream
$output = fopen('php://output', 'w');

// Write header row (no BOM, no extra spaces)
fputcsv($output, [
    'Date',
    'Activity Name',
    'Venue',
    'Facilitators',
    'Number of Participants',
    'Status'
]);

// Write sample data rows (both YYYY-MM-DD and DD/MM/YYYY formats)
fputcsv($output, [
    '2024-01-15',
    'Research Methodology Workshop',
    'Conference Room A',
    'Dr. John Smith',
    '25',
    'Completed'
]);
fputcsv($output, [
    '15/01/2024',
    'Data Analysis Training',
    'Computer Lab 101',
    'Prof. Jane Doe',
    '15',
    'In Progress'
]);
fputcsv($output, [
    '2024-01-25',
    'Academic Writing Seminar',
    'Auditorium',
    'Dr. Robert Johnson',
    '40',
    'Scheduled'
]);
fputcsv($output, [
    '01/02/2024',
    'Research Ethics Training',
    'Seminar Room 3',
    'Dr. Sarah Wilson',
    '30',
    'Scheduled'
]);

// Close the file
fclose($output);
?> 