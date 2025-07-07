<?php
session_start();

// Include database configuration
require_once '../database/config.php';

if (empty($_SESSION['logged_in'])) {
    header('Location: loginpage.php');
    exit;
}

// Ethics Reviewed Protocols template download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="ethics_reviewed_protocols_template.csv"');
$output = fopen('php://output', 'w');
fputcsv($output, [
    'Protocol Number',
    'Research Title',
    'Department',
    'Status',
    'Action Taken'
]);
fputcsv($output, [
    'EP-2025-001',
    'Study on Ethics in Research',
    'Biology',
    'Approved',
    'Approval letter issued'
]);
fputcsv($output, [
    'EP-2025-002',
    'Social Media Impact Study',
    'Psychology',
    'Under Review',
    'Currently under review'
]);
fputcsv($output, [
    'EP-2025-003',
    'Environmental Assessment',
    'Environmental Science',
    'Pending',
    'Waiting for documentation'
]);
fputcsv($output, [
    'EP-2025-004',
    'Controversial Research',
    'Medicine',
    'Rejected',
    'Rejected due to ethical concerns'
]);
fclose($output);
exit; 