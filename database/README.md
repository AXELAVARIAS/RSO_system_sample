# RSO Database Migration Guide

This guide will help you migrate from CSV files to a structured MySQL database for the RSO Research Management System.

## 📁 Files Created

- `rso_database.sql` - Complete database structure and data
- `config.php` - Database configuration and connection class
- `setup.php` - Setup script to initialize the database
- `README.md` - This guide

## 🚀 Quick Setup

### 1. Prerequisites
- XAMPP, WAMP, or similar local server with MySQL
- PHP with PDO extension enabled
- MySQL server running

### 2. Database Setup
1. Start your MySQL server (XAMPP Control Panel → Start MySQL)
2. Open your browser and navigate to: `http://localhost/RSO_system_sample/database/setup.php`
3. The setup script will:
   - Test database connection
   - Create the database
   - Import all tables and data
   - Verify everything is working

### 3. Configuration
The database is configured for XAMPP by default:
- **Host**: localhost
- **Database**: rso_system
- **Username**: root
- **Password**: (empty)
- **Port**: 3306

If you need to change these settings, edit `config.php`.

## 📊 Database Structure

### Tables Created

1. **users** - User accounts and profiles
   - id, email, password_hash, user_type, full_name, department, profile_picture

2. **research_capacity_activities** - Research capacity building activities
   - id, activity_date, activity_title, venue, organizer, participants_count, status

3. **data_collection_tools** - Data collection tools and research
   - id, researcher_name, degree, gender, research_title, role, location, submission_date, research_area

4. **ethics_reviewed_protocols** - Ethics review protocols
   - id, protocol_number, title, department, status, action_taken

5. **publication_presentations** - Publications and presentations
   - id, application_date, author_name, paper_title, department, research_subsidy, status, scope

6. **kpi_records** - KPI performance records
   - id, faculty_name, quarter, publications_count, presentations_count, research_projects_count, performance_score, performance_rating

### Views Created

- **faculty_publications** - Summary of faculty publications
- **research_activity_summary** - Monthly research activity summary
- **kpi_performance_summary** - Quarterly KPI performance summary

### Stored Procedures

- **GetFacultyPerformance(faculty_name)** - Get comprehensive faculty performance data
- **GetDepartmentStats(dept_name)** - Get department statistics

## 🔄 Migration Steps

### Step 1: Update PHP Files
Replace CSV file operations with database queries. Example:

**Before (CSV):**
```php
$users = [];
if (file_exists('users.csv')) {
    $file = fopen('users.csv', 'r');
    while (($data = fgetcsv($file)) !== false) {
        $users[] = $data;
    }
    fclose($file);
}
```

**After (Database):**
```php
require_once '../database/config.php';
$db = getDB();
$users = $db->fetchAll("SELECT * FROM users WHERE user_type = ?", ['faculty']);
```

### Step 2: Update Login System
```php
// In loginpage.php
require_once 'database/config.php';

$db = getDB();
$user = $db->fetch("SELECT * FROM users WHERE email = ?", [$email]);

if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['logged_in'] = true;
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_full_name'] = $user['full_name'];
    $_SESSION['user_department'] = $user['department'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['profile_picture'] = $user['profile_picture'];
}
```

### Step 3: Update CRUD Operations
```php
// Add new publication
$db = getDB();
$db->query("INSERT INTO publication_presentations (application_date, author_name, paper_title, department, research_subsidy, status, scope) VALUES (?, ?, ?, ?, ?, ?, ?)", 
    [$date, $author, $title, $department, $subsidy, $status, $scope]);

// Get all publications
$publications = $db->fetchAll("SELECT * FROM publication_presentations ORDER BY application_date DESC");

// Update publication
$db->query("UPDATE publication_presentations SET application_date = ?, author_name = ?, paper_title = ?, department = ?, research_subsidy = ?, status = ?, scope = ? WHERE id = ?",
    [$date, $author, $title, $department, $subsidy, $status, $scope, $id]);

// Delete publication
$db->query("DELETE FROM publication_presentations WHERE id = ?", [$id]);
```

## 🛠️ Database Class Usage

### Basic Operations
```php
require_once 'database/config.php';
$db = getDB();

// Fetch all records
$users = $db->fetchAll("SELECT * FROM users");

// Fetch single record
$user = $db->fetch("SELECT * FROM users WHERE id = ?", [$id]);

// Insert record
$db->query("INSERT INTO users (email, password_hash, user_type, full_name, department) VALUES (?, ?, ?, ?, ?)",
    [$email, $password_hash, $user_type, $full_name, $department]);

// Update record
$db->query("UPDATE users SET full_name = ?, department = ? WHERE id = ?",
    [$full_name, $department, $id]);

// Delete record
$db->query("DELETE FROM users WHERE id = ?", [$id]);

// Get last insert ID
$newId = $db->lastInsertId();
```

### Transactions
```php
$db = getDB();
try {
    $db->beginTransaction();
    
    // Multiple operations
    $db->query("INSERT INTO publications (...) VALUES (...)", [...]);
    $db->query("UPDATE users SET publications_count = publications_count + 1 WHERE id = ?", [$userId]);
    
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    throw $e;
}
```

## 📈 Advanced Queries

### Using Views
```php
// Get faculty publications summary
$summary = $db->fetchAll("SELECT * FROM faculty_publications");

// Get monthly research activity
$monthly = $db->fetchAll("SELECT * FROM research_activity_summary");
```

### Using Stored Procedures
```php
// Get faculty performance
$performance = $db->fetch("CALL GetFacultyPerformance(?)", ['Dr. Sarah Johnson']);

// Get department stats
$stats = $db->fetch("CALL GetDepartmentStats(?)", ['CITCS']);
```

## 🔒 Security Features

- **Prepared Statements**: All queries use prepared statements to prevent SQL injection
- **Password Hashing**: Passwords are hashed using PHP's password_hash()
- **Input Validation**: Use proper validation before database operations
- **Error Handling**: Comprehensive error handling with try-catch blocks

## 📋 Data Migration Status

All CSV data has been converted to SQL:

- ✅ users.csv → users table
- ✅ research_capacity_data.csv → research_capacity_activities table
- ✅ data_collection_tools.csv → data_collection_tools table
- ✅ ethics_reviewed_protocols.csv → ethics_reviewed_protocols table
- ✅ publication_presentation.csv → publication_presentations table
- ✅ kpi_records.csv → kpi_records table

## 🚨 Important Notes

1. **Backup**: Always backup your CSV files before removing them
2. **Testing**: Test all functionality after migration
3. **Permissions**: Ensure your web server has read/write permissions
4. **Connection**: Make sure MySQL is running before accessing the system

## 🆘 Troubleshooting

### Common Issues

1. **Connection Failed**
   - Check if MySQL is running
   - Verify database credentials in config.php
   - Ensure PDO extension is enabled

2. **Permission Denied**
   - Check file permissions
   - Ensure web server can access database files

3. **Data Not Showing**
   - Run setup.php to verify data import
   - Check database connection
   - Verify table structure

### Getting Help

If you encounter issues:
1. Check the error messages in setup.php
2. Verify your MySQL server is running
3. Check the database configuration
4. Ensure all required PHP extensions are enabled

## 📞 Support

For additional help with the database migration, refer to:
- MySQL documentation
- PHP PDO documentation
- Your local server documentation (XAMPP/WAMP) 