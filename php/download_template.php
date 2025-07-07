<?php
session_start();

// Include database configuration
require_once '../database/config.php';

if (empty($_SESSION['logged_in'])) {
    header('Location: loginpage.php');
    exit;
}

// Research Capacity Building Activities template download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="research_capacity_building_activities_template.csv"');
$output = fopen('php://output', 'w');
fputcsv($output, [
    'Date',
    'Activity Name',
    'Venue',
    'Facilitators',
    'Number of Participants',
    'Status'
]);
fputcsv($output, [
    '2024-07-01',
    'Workshop on Research Methods',
    'Main Hall',
    'Dr. Smith, Prof. Lee',
    '50',
    'Completed'
]);
fclose($output);
exit;
?> 