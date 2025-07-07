<?php
session_start();

// Set JSON content type
header('Content-Type: application/json');

// Include database configuration
require_once '../database/config.php';

$debug_info = [
    'session' => [
        'logged_in' => !empty($_SESSION['logged_in']),
        'user_type' => $_SESSION['user_type'] ?? 'unknown'
    ],
    'request' => [
        'method' => $_SERVER['REQUEST_METHOD'],
        'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
        'content_length' => $_SERVER['CONTENT_LENGTH'] ?? 'not set'
    ],
    'server' => [
        'php_version' => PHP_VERSION,
        'max_file_size' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_execution_time' => ini_get('max_execution_time')
    ],
    'database' => [
        'connection' => 'unknown'
    ],
    'upload_directory' => [
        'exists' => false,
        'writable' => false
    ],
    'extensions' => [
        'zip' => extension_loaded('zip'),
        'json' => extension_loaded('json'),
        'mbstring' => extension_loaded('mbstring')
    ]
];

// Test database connection
try {
    $db = getDB();
    $debug_info['database']['connection'] = 'success';
} catch (Exception $e) {
    $debug_info['database']['connection'] = 'failed: ' . $e->getMessage();
}

// Check upload directory
$upload_dir = '../uploads/';
$debug_info['upload_directory']['exists'] = is_dir($upload_dir);
$debug_info['upload_directory']['writable'] = is_writable($upload_dir);

echo json_encode($debug_info, JSON_PRETTY_PRINT);
?> 