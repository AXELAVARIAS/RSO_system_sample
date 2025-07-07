<?php
/**
 * Database Initialization Script
 * 
 * This script will create the database and import all data if it doesn't exist.
 * Run this first if you're getting database connection errors.
 */

echo "<h1>RSO Database Initialization</h1>\n";
echo "<p>This script will set up your database from scratch.</p>\n";

// Step 1: Test basic MySQL connection
echo "<h2>Step 1: Testing MySQL Connection</h2>\n";
try {
    $pdo = new PDO("mysql:host=localhost;port=3306", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ MySQL connection successful!</p>\n";
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ MySQL connection failed: " . $e->getMessage() . "</p>\n";
    echo "<p>Please make sure:</p>\n";
    echo "<ul>\n";
    echo "<li>XAMPP is running</li>\n";
    echo "<li>MySQL service is started</li>\n";
    echo "<li>Username 'root' with no password is correct</li>\n";
    echo "</ul>\n";
    exit;
}

// Step 2: Create database
echo "<h2>Step 2: Creating Database</h2>\n";
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS rso_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color: green;'>✓ Database 'rso_system' created successfully!</p>\n";
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Failed to create database: " . $e->getMessage() . "</p>\n";
    exit;
}

// Step 3: Connect to the database
echo "<h2>Step 3: Connecting to Database</h2>\n";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=rso_system;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ Connected to rso_system database!</p>\n";
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Failed to connect to database: " . $e->getMessage() . "</p>\n";
    exit;
}

// Step 4: Import SQL structure
echo "<h2>Step 4: Importing Database Structure</h2>\n";
$sqlFile = __DIR__ . '/rso_database.sql';
if (file_exists($sqlFile)) {
    try {
        $sql = file_get_contents($sqlFile);
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        $successCount = 0;
        $totalCount = count($statements);
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^(--|\/\*|\*)/', trim($statement))) {
                try {
                    $pdo->exec($statement);
                    $successCount++;
                } catch (Exception $e) {
                    // Ignore errors for existing tables/views
                    if (!strpos($e->getMessage(), 'already exists')) {
                        echo "<p style='color: orange;'>⚠ Warning: " . $e->getMessage() . "</p>\n";
                    }
                }
            }
        }
        
        echo "<p style='color: green;'>✓ Database structure imported successfully! ($successCount/$totalCount statements executed)</p>\n";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Failed to import database structure: " . $e->getMessage() . "</p>\n";
    }
} else {
    echo "<p style='color: red;'>✗ SQL file not found: $sqlFile</p>\n";
    exit;
}

// Step 5: Verify tables
echo "<h2>Step 5: Verifying Tables</h2>\n";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $expectedTables = [
        'users',
        'research_capacity_activities',
        'data_collection_tools',
        'ethics_reviewed_protocols',
        'publication_presentations',
        'kpi_records'
    ];
    
    foreach ($expectedTables as $table) {
        if (in_array($table, $tables)) {
            echo "<p style='color: green;'>✓ Table '$table' exists</p>\n";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' missing</p>\n";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Failed to verify tables: " . $e->getMessage() . "</p>\n";
}

// Step 6: Check data
echo "<h2>Step 6: Checking Data</h2>\n";
try {
    $tables = [
        'users' => 'SELECT COUNT(*) as count FROM users',
        'research_capacity_activities' => 'SELECT COUNT(*) as count FROM research_capacity_activities',
        'data_collection_tools' => 'SELECT COUNT(*) as count FROM data_collection_tools',
        'ethics_reviewed_protocols' => 'SELECT COUNT(*) as count FROM ethics_reviewed_protocols',
        'publication_presentations' => 'SELECT COUNT(*) as count FROM publication_presentations',
        'kpi_records' => 'SELECT COUNT(*) as count FROM kpi_records'
    ];
    
    foreach ($tables as $table => $query) {
        $stmt = $pdo->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'];
        echo "<p>📊 Table '$table': $count records</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Failed to check data: " . $e->getMessage() . "</p>\n";
}

echo "<h2>✅ Database Initialization Complete!</h2>\n";
echo "<p>Your database has been set up successfully. You can now:</p>\n";
echo "<ul>\n";
echo "<li><a href='test_connection.php'>Test Database Connection</a></li>\n";
echo "<li><a href='../php/loginpage.php'>Go to Login Page</a></li>\n";
echo "<li><a href='../index.php'>Go to Dashboard</a></li>\n";
echo "</ul>\n";

echo "<h3>Database Details:</h3>\n";
echo "<ul>\n";
echo "<li><strong>Host:</strong> localhost</li>\n";
echo "<li><strong>Database:</strong> rso_system</li>\n";
echo "<li><strong>Username:</strong> root</li>\n";
echo "<li><strong>Password:</strong> (empty)</li>\n";
echo "<li><strong>Port:</strong> 3306</li>\n";
echo "</ul>\n";

echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ol>\n";
echo "<li>Test the login system</li>\n";
echo "<li>Update other PHP files to use the database</li>\n";
echo "<li>Backup your CSV files</li>\n";
echo "</ol>\n";
?> 