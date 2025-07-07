-- RSO Research Management System Database
-- This file contains all the data converted from CSV files into a structured SQL database

-- Create database
CREATE DATABASE IF NOT EXISTS rso_system;
USE rso_system;

-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS kpi_records;
DROP TABLE IF EXISTS publication_presentations;
DROP TABLE IF EXISTS ethics_reviewed_protocols;
DROP TABLE IF EXISTS data_collection_tools;
DROP TABLE IF EXISTS research_capacity_activities;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('rso', 'faculty', 'admin') NOT NULL DEFAULT 'faculty',
    full_name VARCHAR(255) NOT NULL,
    department VARCHAR(100) NOT NULL,
    profile_picture VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create research capacity activities table
CREATE TABLE research_capacity_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_date DATE NOT NULL,
    activity_title VARCHAR(500) NOT NULL,
    venue VARCHAR(200) NOT NULL,
    organizer VARCHAR(255) NOT NULL,
    participants_count INT DEFAULT 0,
    status ENUM('Scheduled', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create data collection tools table
CREATE TABLE data_collection_tools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    researcher_name VARCHAR(255) NOT NULL,
    degree VARCHAR(100) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    research_title VARCHAR(500) NOT NULL,
    role VARCHAR(100) NOT NULL,
    location VARCHAR(200) NOT NULL,
    submission_date DATE NOT NULL,
    research_area VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create ethics reviewed protocols table
CREATE TABLE ethics_reviewed_protocols (
    id INT AUTO_INCREMENT PRIMARY KEY,
    protocol_number VARCHAR(50) UNIQUE NOT NULL,
    title VARCHAR(500) NOT NULL,
    department VARCHAR(200) NOT NULL,
    status ENUM('Under Review', 'Approved', 'Rejected', 'Pending') DEFAULT 'Under Review',
    action_taken VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create publication presentations table
CREATE TABLE publication_presentations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_date DATE NOT NULL,
    author_name VARCHAR(255) NOT NULL,
    paper_title VARCHAR(500) NOT NULL,
    department VARCHAR(100) NOT NULL,
    research_subsidy VARCHAR(200) NOT NULL,
    status ENUM('Draft', 'Submitted', 'Under Review', 'Accepted', 'Published', 'Rejected') DEFAULT 'Draft',
    scope ENUM('Local', 'International') DEFAULT 'Local',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create KPI records table
CREATE TABLE kpi_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_name VARCHAR(255) NOT NULL,
    quarter VARCHAR(20) NOT NULL,
    publications_count INT DEFAULT 0,
    presentations_count INT DEFAULT 0,
    research_projects_count INT DEFAULT 0,
    performance_score DECIMAL(5,2) DEFAULT 0.00,
    performance_rating ENUM('Poor', 'Fair', 'Good', 'Very Good', 'Excellent', 'Outstanding') DEFAULT 'Fair',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert data from users.csv
INSERT INTO users (email, password_hash, user_type, full_name, department, profile_picture) VALUES
('roel@gmail.com', '$2y$10$m/J8uOp/jaBI5CBXixZSz.8dTWshJAI80K3dTWrkCGtXne9RGRdHm', 'rso', 'Roel Admin', 'RSO', NULL),
('alex@gmail.com', '$2y$10$Ck4siVO2OrdSZrtA1MeSUOhoJbUBThMS3BQTe1c9p/WcHHSY7M7Cu', 'faculty', 'Alexander Lavarias', 'CITCS', '../uploads/profile_pictures/abdul_gmail.com_1751719757.jpg'),
('stewie@gmail.com', '$2y$10$Dm0FMW.HZaGT67GyCgDRreRbO20Gw4EzEvVrQkciuR.KFjoBRUZSG', 'faculty', 'Stewie', 'CEA', '../uploads/profile_pictures/stewie_gmail.com_1751723523.png');

-- Insert data from research_capacity_data.csv
INSERT INTO research_capacity_activities (activity_date, activity_title, venue, organizer, participants_count, status) VALUES
('2025-03-12', 'Capsule Proposal Submission Workshop', 'U401', 'CON, CCJE, CAS, CBA, CHTM, CEA, InTTO, CITCS, & RSO', 14, 'Completed'),
('2025-07-08', 'Data Analysis Training', 'GIS LAB', 'Alexander Lavarias', 30, 'Scheduled'),
('2025-07-09', 'Apple', 'Lecture Room 201', 'Alexander Lavarias', 23, 'In Progress');

-- Insert data from data_collection_tools.csv
INSERT INTO data_collection_tools (researcher_name, degree, gender, research_title, role, location, submission_date, research_area) VALUES
('Lavarias, Alexander F.', 'Ph.D.', 'Male', 'Pay-GIS: A Mobile Application Using GIS to Track Residents with Unpaid Building Permits', 'Co-Author', 'Baguio City', '2025-07-22', 'Dance Fashion');

-- Insert data from ethics_reviewed_protocols.csv
INSERT INTO ethics_reviewed_protocols (protocol_number, title, department, status, action_taken) VALUES
('1', 'Pay-GIS: A Mobile Application Using GIS to Track Residents with Unpaid Building Permits', 'CON Undergrad February 17, 2025', 'Under Review', 'Forwarded to the Ethics Chair');

-- Insert data from publication_presentation.csv
INSERT INTO publication_presentations (application_date, author_name, paper_title, department, research_subsidy, status, scope) VALUES
('2025-07-07', 'Alexander Lavarias', 'FINAL GISTINATION', 'CITCS', '₱500,000', 'Approved', 'International');

-- Insert data from kpi_records.csv
INSERT INTO kpi_records (faculty_name, quarter, publications_count, presentations_count, research_projects_count, performance_score, performance_rating) VALUES
('Dr. Sarah Johnson', 'Q1 2025', 3, 2, 1, 95.00, 'Excellent'),
('Dr. Emily Rodriguez', 'Q1 2025', 4, 3, 2, 99.00, 'Outstanding');

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_department ON users(department);
CREATE INDEX idx_research_capacity_date ON research_capacity_activities(activity_date);
CREATE INDEX idx_research_capacity_status ON research_capacity_activities(status);
CREATE INDEX idx_data_collection_researcher ON data_collection_tools(researcher_name);
CREATE INDEX idx_ethics_protocols_status ON ethics_reviewed_protocols(status);
CREATE INDEX idx_publications_author ON publication_presentations(author_name);
CREATE INDEX idx_publications_status ON publication_presentations(status);
CREATE INDEX idx_kpi_faculty ON kpi_records(faculty_name);
CREATE INDEX idx_kpi_quarter ON kpi_records(quarter);

-- Create views for common queries
CREATE VIEW faculty_publications AS
SELECT 
    u.full_name,
    u.department,
    COUNT(p.id) as total_publications,
    COUNT(CASE WHEN p.status = 'Published' THEN 1 END) as published_count,
    COUNT(CASE WHEN p.scope = 'International' THEN 1 END) as international_count
FROM users u
LEFT JOIN publication_presentations p ON u.full_name = p.author_name
WHERE u.user_type = 'faculty'
GROUP BY u.id, u.full_name, u.department;

CREATE VIEW research_activity_summary AS
SELECT 
    DATE_FORMAT(activity_date, '%Y-%m') as month,
    COUNT(*) as total_activities,
    COUNT(CASE WHEN status = 'Completed' THEN 1 END) as completed_activities,
    SUM(participants_count) as total_participants
FROM research_capacity_activities
GROUP BY DATE_FORMAT(activity_date, '%Y-%m')
ORDER BY month DESC;

CREATE VIEW kpi_performance_summary AS
SELECT 
    quarter,
    COUNT(*) as total_faculty,
    AVG(performance_score) as average_score,
    COUNT(CASE WHEN performance_rating IN ('Excellent', 'Outstanding') THEN 1 END) as high_performers
FROM kpi_records
GROUP BY quarter
ORDER BY quarter DESC;

-- Create stored procedures for common operations
DELIMITER //

CREATE PROCEDURE GetFacultyPerformance(IN faculty_name VARCHAR(255))
BEGIN
    SELECT 
        u.full_name,
        u.department,
        COUNT(DISTINCT p.id) as publications,
        COUNT(DISTINCT d.id) as data_collection_tools,
        COUNT(DISTINCT e.id) as ethics_protocols,
        k.performance_score,
        k.performance_rating
    FROM users u
    LEFT JOIN publication_presentations p ON u.full_name = p.author_name
    LEFT JOIN data_collection_tools d ON u.full_name = d.researcher_name
    LEFT JOIN ethics_reviewed_protocols e ON u.full_name = e.title
    LEFT JOIN kpi_records k ON u.full_name = k.faculty_name
    WHERE u.full_name = faculty_name AND u.user_type = 'faculty'
    GROUP BY u.id, u.full_name, u.department, k.performance_score, k.performance_rating;
END //

CREATE PROCEDURE GetDepartmentStats(IN dept_name VARCHAR(100))
BEGIN
    SELECT 
        dept_name as department,
        COUNT(DISTINCT u.id) as faculty_count,
        COUNT(DISTINCT p.id) as publications_count,
        COUNT(DISTINCT r.id) as research_activities_count,
        AVG(k.performance_score) as avg_performance_score
    FROM users u
    LEFT JOIN publication_presentations p ON u.department = p.department
    LEFT JOIN research_capacity_activities r ON u.full_name = r.organizer
    LEFT JOIN kpi_records k ON u.full_name = k.faculty_name
    WHERE u.department = dept_name AND u.user_type = 'faculty'
    GROUP BY dept_name;
END //

DELIMITER ;

-- Insert sample data for testing (optional)
-- You can uncomment these lines to add more sample data

/*
INSERT INTO research_capacity_activities (activity_date, activity_title, venue, organizer, participants_count, status) VALUES
('2025-08-15', 'Research Methodology Workshop', 'Room 301', 'Dr. Sarah Johnson', 25, 'Scheduled'),
('2025-08-20', 'Statistical Analysis Training', 'Computer Lab', 'Prof. Michael Chen', 18, 'Scheduled');

INSERT INTO publication_presentations (application_date, author_name, paper_title, department, research_subsidy, status, scope) VALUES
('2025-08-10', 'Dr. Sarah Johnson', 'AI in Healthcare: A Comprehensive Review', 'Health Sciences', '₱750,000', 'Under Review', 'International'),
('2025-08-12', 'Prof. Michael Chen', 'Urban Energy Systems Optimization', 'Engineering', '₱600,000', 'Accepted', 'International'),
('2025-08-14', 'Dr. Emily Rodriguez', 'EdTech Impact on Student Learning', 'Education', '₱400,000', 'Published', 'Local');
*/

-- Grant permissions (adjust as needed for your setup)
-- GRANT ALL PRIVILEGES ON rso_system.* TO 'your_username'@'localhost';
-- FLUSH PRIVILEGES;

-- Show table information
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'rso_system'
ORDER BY TABLE_NAME; 