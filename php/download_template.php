<?php
session_start();

// Include database configuration
require_once '../database/config.php';

if (empty($_SESSION['logged_in'])) {
    header('Location: loginpage.php');
    exit;
}

// Data Collection Tools template download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="data_collection_tools_template.csv"');
$output = fopen('php://output', 'w');
fputcsv($output, [
    'Faculty Name',
    'Degree',
    'Sex',
    'Research Title',
    'Ownership',
    'Presented At',
    'Published Date',
    'Journal/Publication'
]);
fputcsv($output, [
    'John Doe',
    'Ph.D.',
    'Male',
    'Sample Research Title',
    'Author',
    'International Conference 2024',
    '2024-05-01',
    'Journal of Research'
]);
fclose($output);
exit;
?> 