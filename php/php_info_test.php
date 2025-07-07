<?php
session_start();

// Include database configuration
require_once '../database/config.php';

if (empty($_SESSION['logged_in'])) {
    header('Location: loginpage.php');
    exit;
}

echo "<h1>PHP Upload Configuration Test</h1>";

// Check upload-related PHP settings
echo "<h2>Upload Settings:</h2>";
echo "<p>file_uploads: " . (ini_get('file_uploads') ? 'ON' : 'OFF') . "</p>";
echo "<p>upload_max_filesize: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>post_max_size: " . ini_get('post_max_size') . "</p>";
echo "<p>max_file_uploads: " . ini_get('max_file_uploads') . "</p>";
echo "<p>memory_limit: " . ini_get('memory_limit') . "</p>";

echo "<h2>Session Info:</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Logged in: " . (isset($_SESSION['logged_in']) ? 'Yes' : 'No') . "</p>";

echo "<h2>Request Info:</h2>";
echo "<p>Request Method: " . $_SERVER['REQUEST_METHOD'] . "</p>";
echo "<p>Content Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'Not set') . "</p>";
echo "<p>Content Length: " . ($_SERVER['CONTENT_LENGTH'] ?? 'Not set') . "</p>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>POST Data:</h2>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    echo "<h2>FILES Data:</h2>";
    echo "<pre>" . print_r($_FILES, true) . "</pre>";
}
?> 