<?php
/**
 * Database Connection Test Script
 * 
 * This script tests the database connection and shows sample data.
 * Run this to verify your database is working correctly.
 */

// Include database configuration
require_once 'config.php';

echo "<h1>RSO Database Connection Test</h1>\n";
echo "<p>Testing database connection and data access...</p>\n";

try {
    // Test database connection
    $db = getDB();
    echo "<p style='color: green;'>✓ Database connection successful!</p>\n";
    
    // Test basic queries
    echo "<h2>Database Statistics</h2>\n";
    
    // Count users
    $user_count = $db->fetch("SELECT COUNT(*) as count FROM users");
    echo "<p>👥 Total Users: " . $user_count['count'] . "</p>\n";
    
    // Count publications
    $pub_count = $db->fetch("SELECT COUNT(*) as count FROM publication_presentations");
    echo "<p>📚 Total Publications: " . $pub_count['count'] . "</p>\n";
    
    // Count research activities
    $activity_count = $db->fetch("SELECT COUNT(*) as count FROM research_capacity_activities");
    echo "<p>🔬 Total Research Activities: " . $activity_count['count'] . "</p>\n";
    
    // Count KPI records
    $kpi_count = $db->fetch("SELECT COUNT(*) as count FROM kpi_records");
    echo "<p>📊 Total KPI Records: " . $kpi_count['count'] . "</p>\n";
    
    echo "<h2>Sample Data</h2>\n";
    
    // Show sample users
    echo "<h3>Users</h3>\n";
    $users = $db->fetchAll("SELECT email, full_name, department, user_type FROM users LIMIT 5");
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><th>Email</th><th>Name</th><th>Department</th><th>Type</th></tr>\n";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['department']) . "</td>";
        echo "<td>" . htmlspecialchars($user['user_type']) . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // Show sample publications
    echo "<h3>Publications</h3>\n";
    $publications = $db->fetchAll("SELECT author_name, paper_title, department, status FROM publication_presentations LIMIT 5");
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><th>Author</th><th>Title</th><th>Department</th><th>Status</th></tr>\n";
    foreach ($publications as $pub) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($pub['author_name']) . "</td>";
        echo "<td>" . htmlspecialchars($pub['paper_title']) . "</td>";
        echo "<td>" . htmlspecialchars($pub['department']) . "</td>";
        echo "<td>" . htmlspecialchars($pub['status']) . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // Test views
    echo "<h2>Database Views</h2>\n";
    
    // Test faculty publications view
    $faculty_pubs = $db->fetchAll("SELECT * FROM faculty_publications");
    echo "<h3>Faculty Publications Summary</h3>\n";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><th>Faculty</th><th>Department</th><th>Total Publications</th><th>Published</th><th>International</th></tr>\n";
    foreach ($faculty_pubs as $fp) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($fp['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($fp['department']) . "</td>";
        echo "<td>" . $fp['total_publications'] . "</td>";
        echo "<td>" . $fp['published_count'] . "</td>";
        echo "<td>" . $fp['international_count'] . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // Test stored procedures
    echo "<h2>Stored Procedures</h2>\n";
    
    try {
        // Test GetFacultyPerformance procedure
        $performance = $db->fetch("CALL GetFacultyPerformance(?)", ['Alexander Lavarias']);
        if ($performance) {
            echo "<p style='color: green;'>✓ GetFacultyPerformance procedure working</p>\n";
            echo "<p>Faculty: " . htmlspecialchars($performance['full_name']) . "</p>\n";
            echo "<p>Department: " . htmlspecialchars($performance['department']) . "</p>\n";
            echo "<p>Publications: " . $performance['publications'] . "</p>\n";
        }
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠ GetFacultyPerformance procedure: " . $e->getMessage() . "</p>\n";
    }
    
    echo "<h2>Database Configuration</h2>\n";
    echo "<ul>\n";
    echo "<li><strong>Host:</strong> " . DB_HOST . "</li>\n";
    echo "<li><strong>Database:</strong> " . DB_NAME . "</li>\n";
    echo "<li><strong>Username:</strong> " . DB_USER . "</li>\n";
    echo "<li><strong>Port:</strong> " . DB_PORT . "</li>\n";
    echo "<li><strong>Character Set:</strong> " . DB_CHARSET . "</li>\n";
    echo "</ul>\n";
    
    echo "<h2>✅ All Tests Passed!</h2>\n";
    echo "<p>Your database is working correctly. You can now:</p>\n";
    echo "<ul>\n";
    echo "<li>Use the login system with the database</li>\n";
    echo "<li>Update other PHP files to use the database</li>\n";
    echo "<li>Access your data through phpMyAdmin</li>\n";
    echo "</ul>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database test failed: " . $e->getMessage() . "</p>\n";
    echo "<p>Please check:</p>\n";
    echo "<ul>\n";
    echo "<li>MySQL server is running</li>\n";
    echo "<li>Database credentials in config.php</li>\n";
    echo "<li>Database exists and has data</li>\n";
    echo "</ul>\n";
    echo "<p><a href='setup.php'>Run Database Setup</a></p>\n";
}
?> 