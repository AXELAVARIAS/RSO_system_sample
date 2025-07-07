<?php
/**
 * RSO Database Setup Script
 * 
 * This script helps you set up the database and migrate from CSV files.
 * Run this script once to initialize your database.
 */

// Include database configuration
require_once 'config.php';

echo "<h1>RSO Database Setup</h1>\n";
echo "<p>This script will help you set up the database and migrate from CSV files.</p>\n";

// Step 1: Test database connection
echo "<h2>Step 1: Testing Database Connection</h2>\n";
try {
    $db = getDB();
    echo "<p style='color: green;'>✓ Database connection successful!</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>\n";
    echo "<p>Please check your database configuration in config.php</p>\n";
    exit;
}

// Step 2: Create database if it doesn't exist
echo "<h2>Step 2: Creating Database</h2>\n";
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color: green;'>✓ Database '" . DB_NAME . "' created successfully!</p>\n";
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Failed to create database: " . $e->getMessage() . "</p>\n";
    exit;
}

// Step 3: Import SQL structure
echo "<h2>Step 3: Importing Database Structure</h2>\n";
$sqlFile = __DIR__ . '/rso_database.sql';
if (file_exists($sqlFile)) {
    try {
        $sql = file_get_contents($sqlFile);
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        $db = getDB();
        $successCount = 0;
        $totalCount = count($statements);
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^(--|\/\*|\*)/', trim($statement))) {
                try {
                    $db->query($statement);
                    $successCount++;
                } catch (Exception $e) {
                    echo "<p style='color: orange;'>⚠ Warning: " . $e->getMessage() . "</p>\n";
                }
            }
        }
        
        echo "<p style='color: green;'>✓ Database structure imported successfully! ($successCount/$totalCount statements executed)</p>\n";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Failed to import database structure: " . $e->getMessage() . "</p>\n";
    }
} else {
    echo "<p style='color: red;'>✗ SQL file not found: $sqlFile</p>\n";
}

// Step 4: Verify tables
echo "<h2>Step 4: Verifying Tables</h2>\n";
try {
    $db = getDB();
    $tables = $db->fetchAll("SHOW TABLES");
    
    $expectedTables = [
        'users',
        'research_capacity_activities',
        'data_collection_tools',
        'ethics_reviewed_protocols',
        'publication_presentations',
        'kpi_records'
    ];
    
    $foundTables = array_column($tables, 'Tables_in_' . DB_NAME);
    
    foreach ($expectedTables as $table) {
        if (in_array($table, $foundTables)) {
            echo "<p style='color: green;'>✓ Table '$table' exists</p>\n";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' missing</p>\n";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Failed to verify tables: " . $e->getMessage() . "</p>\n";
}

// Step 5: Check data
echo "<h2>Step 5: Checking Data</h2>\n";
try {
    $db = getDB();
    
    $tables = [
        'users' => 'SELECT COUNT(*) as count FROM users',
        'research_capacity_activities' => 'SELECT COUNT(*) as count FROM research_capacity_activities',
        'data_collection_tools' => 'SELECT COUNT(*) as count FROM data_collection_tools',
        'ethics_reviewed_protocols' => 'SELECT COUNT(*) as count FROM ethics_reviewed_protocols',
        'publication_presentations' => 'SELECT COUNT(*) as count FROM publication_presentations',
        'kpi_records' => 'SELECT COUNT(*) as count FROM kpi_records'
    ];
    
    foreach ($tables as $table => $query) {
        $result = $db->fetch($query);
        $count = $result['count'];
        echo "<p>📊 Table '$table': $count records</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Failed to check data: " . $e->getMessage() . "</p>\n";
}

// Step 6: Test views
echo "<h2>Step 6: Testing Views</h2>\n";
try {
    $db = getDB();
    
    $views = [
        'faculty_publications' => 'SELECT COUNT(*) as count FROM faculty_publications',
        'research_activity_summary' => 'SELECT COUNT(*) as count FROM research_activity_summary',
        'kpi_performance_summary' => 'SELECT COUNT(*) as count FROM kpi_performance_summary'
    ];
    
    foreach ($views as $view => $query) {
        $result = $db->fetch($query);
        $count = $result['count'];
        echo "<p>📈 View '$view': $count records</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Failed to test views: " . $e->getMessage() . "</p>\n";
}

// Step 7: Test stored procedures
echo "<h2>Step 7: Testing Stored Procedures</h2>\n";
try {
    $db = getDB();
    
    // Test GetFacultyPerformance procedure
    $result = $db->fetch("CALL GetFacultyPerformance(?)", ['Alexander Lavarias']);
    if ($result) {
        echo "<p style='color: green;'>✓ GetFacultyPerformance procedure working</p>\n";
    }
    
    // Test GetDepartmentStats procedure
    $result = $db->fetch("CALL GetDepartmentStats(?)", ['CITCS']);
    if ($result) {
        echo "<p style='color: green;'>✓ GetDepartmentStats procedure working</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠ Warning: Some stored procedures may not be working: " . $e->getMessage() . "</p>\n";
}

echo "<h2>Setup Complete!</h2>\n";
echo "<p>Your RSO database has been set up successfully. You can now:</p>\n";
echo "<ul>\n";
echo "<li>Update your PHP files to use the database instead of CSV files</li>\n";
echo "<li>Use the database configuration in <code>database/config.php</code></li>\n";
echo "<li>Access your database through phpMyAdmin or any MySQL client</li>\n";
echo "</ul>\n";

echo "<h3>Next Steps:</h3>\n";
echo "<ol>\n";
echo "<li>Update your PHP files to use the Database class</li>\n";
echo "<li>Replace CSV file operations with SQL queries</li>\n";
echo "<li>Test all functionality with the new database</li>\n";
echo "<li>Backup your CSV files before removing them</li>\n";
echo "</ol>\n";

echo "<p><strong>Database Details:</strong></p>\n";
echo "<ul>\n";
echo "<li><strong>Host:</strong> " . DB_HOST . "</li>\n";
echo "<li><strong>Database:</strong> " . DB_NAME . "</li>\n";
echo "<li><strong>Username:</strong> " . DB_USER . "</li>\n";
echo "<li><strong>Port:</strong> " . DB_PORT . "</li>\n";
echo "</ul>\n";
?> 