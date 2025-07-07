<?php
session_start();

// Include database configuration
require_once '../database/config.php';

if (empty($_SESSION['logged_in'])) {
    header('Location: loginpage.php');
    exit;
}

echo "<h1>Database Connection Test</h1>";

try {
    $db = getDB();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Test if we can query the ethics_reviewed_protocols table
    $result = $db->fetchAll("SELECT COUNT(*) as count FROM ethics_reviewed_protocols");
    echo "<p>✓ Ethics protocols table accessible. Current count: " . $result[0]['count'] . "</p>";
    
    // Test table structure
    $columns = $db->fetchAll("DESCRIBE ethics_reviewed_protocols");
    echo "<h3>Table Structure:</h3>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>{$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']}</li>";
    }
    echo "</ul>";
    
    // Test insert (will be rolled back)
    $db->beginTransaction();
    try {
        $db->query(
            "INSERT INTO ethics_reviewed_protocols (protocol_number, title, department, status, action_taken) VALUES (?, ?, ?, ?, ?)",
            ['TEST-001', 'Test Protocol', 'Test Department', 'Pending', 'Test Action']
        );
        echo "<p style='color: green;'>✓ Insert test successful</p>";
        $db->rollback();
        echo "<p>✓ Rollback successful</p>";
    } catch (Exception $e) {
        $db->rollback();
        echo "<p style='color: red;'>✗ Insert test failed: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}
?> 