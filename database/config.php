<?php
/**
 * Database Configuration for RSO Research Management System
 * 
 * This file contains the database connection settings.
 * Update these values according to your MySQL server configuration.
 */

// Database configuration
define('DB_HOST', 'localhost');           // Database host (usually localhost)
define('DB_NAME', 'rso_system');          // Database name
define('DB_USER', 'root');                // Database username (default: root for XAMPP)
define('DB_PASS', '');                    // Database password (empty for XAMPP default)
define('DB_CHARSET', 'utf8mb4');          // Character set
define('DB_PORT', 3306);                  // Database port (default: 3306)

// PDO connection options
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
]);

/**
 * Database connection class
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET . ";port=" . DB_PORT;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, DB_OPTIONS);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die("Query failed: " . $e->getMessage());
        }
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
}

/**
 * Helper function to get database instance
 */
function getDB() {
    return Database::getInstance();
}

/**
 * Test database connection
 */
function testDatabaseConnection() {
    try {
        $db = getDB();
        $result = $db->fetch("SELECT 1 as test");
        return $result['test'] == 1;
    } catch (Exception $e) {
        return false;
    }
}

// Example usage:
// $db = getDB();
// $users = $db->fetchAll("SELECT * FROM users WHERE user_type = ?", ['faculty']);
?> 