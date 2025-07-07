<?php
session_start();

// Include database configuration
require_once '../database/config.php';

if (empty($_SESSION['logged_in'])) {
    header('Location: loginpage.php');
    exit;
}

echo "<h1>Simple Upload Test</h1>";

// Check if file was uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>POST Request Received</h2>";
    echo "<p>POST data: " . print_r($_POST, true) . "</p>";
    echo "<p>FILES data: " . print_r($_FILES, true) . "</p>";
    
    if (isset($_FILES['excel_file'])) {
        $file = $_FILES['excel_file'];
        echo "<h3>File Details:</h3>";
        echo "<p>Name: " . $file['name'] . "</p>";
        echo "<p>Size: " . $file['size'] . "</p>";
        echo "<p>Type: " . $file['type'] . "</p>";
        echo "<p>Error: " . $file['error'] . "</p>";
        echo "<p>Temp name: " . $file['tmp_name'] . "</p>";
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            echo "<p style='color: green;'>File uploaded successfully!</p>";
        } else {
            echo "<p style='color: red;'>Upload error: " . $file['error'] . "</p>";
        }
    } else {
        echo "<p style='color: red;'>No file found in request</p>";
    }
} else {
    echo "<h2>GET Request - Show Upload Form</h2>";
    ?>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="excel_file" accept=".xls,.xlsx,.csv">
        <button type="submit">Upload</button>
    </form>
    <?php
}
?> 