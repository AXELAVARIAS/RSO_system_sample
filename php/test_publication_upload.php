<?php
session_start();

// Include database configuration
require_once '../database/config.php';

if (empty($_SESSION['logged_in'])) {
    header('Location: loginpage.php');
    exit;
}

echo "<h1>Publication Upload Test</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];
    
    echo "<h2>File Upload Details:</h2>";
    echo "<p><strong>File Name:</strong> " . htmlspecialchars($file['name']) . "</p>";
    echo "<p><strong>File Size:</strong> " . number_format($file['size']) . " bytes</p>";
    echo "<p><strong>File Type:</strong> " . htmlspecialchars($file['type']) . "</p>";
    echo "<p><strong>Upload Error:</strong> " . $file['error'] . "</p>";
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        echo "<h2>File Content:</h2>";
        
        if ($file_extension === 'csv') {
            $handle = fopen($file['tmp_name'], 'r');
            if ($handle) {
                $row_count = 0;
                while (($row = fgetcsv($handle)) !== false) {
                    $row_count++;
                    echo "<p><strong>Row $row_count:</strong> " . implode(", ", array_map('htmlspecialchars', $row)) . "</p>";
                    
                    if ($row_count === 1) {
                        echo "<p><strong>Headers found:</strong> " . implode(", ", array_map('htmlspecialchars', $row)) . "</p>";
                        
                        // Test header mapping
                        $headers = array_map('trim', $row);
                        $header_map = [];
                        foreach ($headers as $idx => $header) {
                            $header_clean = strtolower(str_replace([' ', '_', '-', '(', ')', '/'], '', $header));
                            $header_map[$header_clean] = $idx;
                        }
                        
                        echo "<p><strong>Header map:</strong> " . json_encode($header_map) . "</p>";
                        
                        // Test expected headers
                        $expected_headers = ['Date OF Application', 'Name(s) of faculty/research worker', 'Title of Paper', 'Department', 'Research Subsidy', 'Status', 'Local/International'];
                        foreach ($expected_headers as $expected) {
                            $expected_clean = strtolower(str_replace([' ', '_', '-', '(', ')', '/'], '', $expected));
                            $found = isset($header_map[$expected_clean]) ? "YES (index " . $header_map[$expected_clean] . ")" : "NO";
                            echo "<p><strong>$expected:</strong> $found</p>";
                        }
                    }
                }
                fclose($handle);
            }
        } else {
            echo "<p>File is not CSV. Extension: $file_extension</p>";
        }
    }
} else {
    echo "<h2>Upload Form:</h2>";
    echo '<form method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="excel_file" accept=".csv,.xls,.xlsx" required>';
    echo '<button type="submit">Test Upload</button>';
    echo '</form>';
}
?> 