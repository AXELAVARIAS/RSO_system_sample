-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 07, 2025 at 08:25 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rso_system`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetDepartmentStats` (IN `dept_name` VARCHAR(100))   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetFacultyPerformance` (IN `faculty_name` VARCHAR(255))   BEGIN
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
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `data_collection_tools`
--

CREATE TABLE `data_collection_tools` (
  `id` int(11) NOT NULL,
  `researcher_name` varchar(255) NOT NULL,
  `degree` varchar(100) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `research_title` varchar(500) NOT NULL,
  `role` varchar(100) NOT NULL,
  `location` varchar(200) NOT NULL,
  `submission_date` date NOT NULL,
  `research_area` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_collection_tools`
--

INSERT INTO `data_collection_tools` (`id`, `researcher_name`, `degree`, `gender`, `research_title`, `role`, `location`, `submission_date`, `research_area`, `created_at`, `updated_at`) VALUES
(4, 'Alexander Lavarias', 'Ph.D.', 'Male', 'sad', 'Author', 'sdsa', '2025-07-31', 'dsad', '2025-07-07 18:12:46', '2025-07-07 18:12:46');

-- --------------------------------------------------------

--
-- Table structure for table `ethics_reviewed_protocols`
--

CREATE TABLE `ethics_reviewed_protocols` (
  `id` int(11) NOT NULL,
  `protocol_number` varchar(50) NOT NULL,
  `title` varchar(500) NOT NULL,
  `department` varchar(200) NOT NULL,
  `status` enum('Under Review','Approved','Rejected','Pending') DEFAULT 'Under Review',
  `action_taken` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ethics_reviewed_protocols`
--

INSERT INTO `ethics_reviewed_protocols` (`id`, `protocol_number`, `title`, `department`, `status`, `action_taken`, `created_at`, `updated_at`) VALUES
(2, 'EP-2025-003', '112', 'CAS', 'Pending', 'Additional Documentation Requested', '2025-07-07 18:13:09', '2025-07-07 18:13:09');

-- --------------------------------------------------------

--
-- Stand-in structure for view `faculty_publications`
-- (See below for the actual view)
--
CREATE TABLE `faculty_publications` (
`full_name` varchar(255)
,`department` varchar(100)
,`total_publications` bigint(21)
,`published_count` bigint(21)
,`international_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `kpi_performance_summary`
-- (See below for the actual view)
--
CREATE TABLE `kpi_performance_summary` (
`quarter` varchar(20)
,`total_faculty` bigint(21)
,`average_score` decimal(9,6)
,`high_performers` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `kpi_records`
--

CREATE TABLE `kpi_records` (
  `id` int(11) NOT NULL,
  `faculty_name` varchar(255) NOT NULL,
  `quarter` varchar(20) NOT NULL,
  `publications_count` int(11) DEFAULT 0,
  `presentations_count` int(11) DEFAULT 0,
  `research_projects_count` int(11) DEFAULT 0,
  `performance_score` decimal(5,2) DEFAULT 0.00,
  `performance_rating` enum('Poor','Fair','Good','Very Good','Excellent','Outstanding') DEFAULT 'Fair',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kpi_records`
--

INSERT INTO `kpi_records` (`id`, `faculty_name`, `quarter`, `publications_count`, `presentations_count`, `research_projects_count`, `performance_score`, `performance_rating`, `created_at`, `updated_at`) VALUES
(3, 'Alexander Lavarias', '2025 -Semester 3', 12, 12, 12, 11.00, 'Very Good', '2025-07-07 18:23:58', '2025-07-07 18:23:58');

-- --------------------------------------------------------

--
-- Table structure for table `publication_presentations`
--

CREATE TABLE `publication_presentations` (
  `id` int(11) NOT NULL,
  `application_date` date NOT NULL,
  `author_name` varchar(255) NOT NULL,
  `paper_title` varchar(500) NOT NULL,
  `department` varchar(100) NOT NULL,
  `research_subsidy` varchar(200) NOT NULL,
  `status` enum('Draft','Submitted','Under Review','Accepted','Published','Rejected') DEFAULT 'Draft',
  `scope` enum('Local','International') DEFAULT 'Local',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `publication_presentations`
--

INSERT INTO `publication_presentations` (`id`, `application_date`, `author_name`, `paper_title`, `department`, `research_subsidy`, `status`, `scope`, `created_at`, `updated_at`) VALUES
(5, '2025-07-05', 'asda', 'asdsa', 'asdas', 'asdas', 'Accepted', 'Local', '2025-07-07 18:20:18', '2025-07-07 18:20:18');

-- --------------------------------------------------------

--
-- Stand-in structure for view `research_activity_summary`
-- (See below for the actual view)
--
CREATE TABLE `research_activity_summary` (
`month` varchar(7)
,`total_activities` bigint(21)
,`completed_activities` bigint(21)
,`total_participants` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Table structure for table `research_capacity_activities`
--

CREATE TABLE `research_capacity_activities` (
  `id` int(11) NOT NULL,
  `activity_date` date NOT NULL,
  `activity_title` varchar(500) NOT NULL,
  `venue` varchar(200) NOT NULL,
  `organizer` varchar(255) NOT NULL,
  `participants_count` int(11) DEFAULT 0,
  `status` enum('Scheduled','In Progress','Completed','Cancelled') DEFAULT 'Scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `research_capacity_activities`
--

INSERT INTO `research_capacity_activities` (`id`, `activity_date`, `activity_title`, `venue`, `organizer`, `participants_count`, `status`, `created_at`, `updated_at`) VALUES
(5, '2025-07-11', 'asdasaa', 'asda', 'asda', 11, 'Completed', '2025-07-07 18:03:58', '2025-07-07 18:04:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_type` enum('rso','faculty','admin') NOT NULL DEFAULT 'faculty',
  `full_name` varchar(255) NOT NULL,
  `department` varchar(100) NOT NULL,
  `profile_picture` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `user_type`, `full_name`, `department`, `profile_picture`, `created_at`, `updated_at`) VALUES
(1, 'roel@gmail.com', '$2y$10$m/J8uOp/jaBI5CBXixZSz.8dTWshJAI80K3dTWrkCGtXne9RGRdHm', 'rso', 'Roel Admin', 'RSO', NULL, '2025-07-07 17:43:35', '2025-07-07 17:43:35'),
(2, 'alex@gmail.com', '$2y$10$Ck4siVO2OrdSZrtA1MeSUOhoJbUBThMS3BQTe1c9p/WcHHSY7M7Cu', 'faculty', 'Alexander Lavarias', 'CITCS', '../uploads/profile_pictures/abdul_gmail.com_1751719757.jpg', '2025-07-07 17:43:35', '2025-07-07 17:43:35'),
(3, 'stewie@gmail.com', '$2y$10$Dm0FMW.HZaGT67GyCgDRreRbO20Gw4EzEvVrQkciuR.KFjoBRUZSG', 'faculty', 'Stewie', 'CEA', '../uploads/profile_pictures/stewie_gmail.com_1751723523.png', '2025-07-07 17:43:35', '2025-07-07 17:43:35'),
(4, 'alexander@gmail.com', '$2y$10$uQr6O8/jtVi/ekQuorR.ceKShRFp1aGxTYZU5m35GswMwQ7LXtAfm', 'faculty', 'Alexander Lavarias', 'CITCS', NULL, '2025-07-07 17:51:41', '2025-07-07 17:51:41');

-- --------------------------------------------------------

--
-- Structure for view `faculty_publications`
--
DROP TABLE IF EXISTS `faculty_publications`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `faculty_publications`  AS SELECT `u`.`full_name` AS `full_name`, `u`.`department` AS `department`, count(`p`.`id`) AS `total_publications`, count(case when `p`.`status` = 'Published' then 1 end) AS `published_count`, count(case when `p`.`scope` = 'International' then 1 end) AS `international_count` FROM (`users` `u` left join `publication_presentations` `p` on(`u`.`full_name` = `p`.`author_name`)) WHERE `u`.`user_type` = 'faculty' GROUP BY `u`.`id`, `u`.`full_name`, `u`.`department` ;

-- --------------------------------------------------------

--
-- Structure for view `kpi_performance_summary`
--
DROP TABLE IF EXISTS `kpi_performance_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `kpi_performance_summary`  AS SELECT `kpi_records`.`quarter` AS `quarter`, count(0) AS `total_faculty`, avg(`kpi_records`.`performance_score`) AS `average_score`, count(case when `kpi_records`.`performance_rating` in ('Excellent','Outstanding') then 1 end) AS `high_performers` FROM `kpi_records` GROUP BY `kpi_records`.`quarter` ORDER BY `kpi_records`.`quarter` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `research_activity_summary`
--
DROP TABLE IF EXISTS `research_activity_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `research_activity_summary`  AS SELECT date_format(`research_capacity_activities`.`activity_date`,'%Y-%m') AS `month`, count(0) AS `total_activities`, count(case when `research_capacity_activities`.`status` = 'Completed' then 1 end) AS `completed_activities`, sum(`research_capacity_activities`.`participants_count`) AS `total_participants` FROM `research_capacity_activities` GROUP BY date_format(`research_capacity_activities`.`activity_date`,'%Y-%m') ORDER BY date_format(`research_capacity_activities`.`activity_date`,'%Y-%m') DESC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `data_collection_tools`
--
ALTER TABLE `data_collection_tools`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_data_collection_researcher` (`researcher_name`);

--
-- Indexes for table `ethics_reviewed_protocols`
--
ALTER TABLE `ethics_reviewed_protocols`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `protocol_number` (`protocol_number`),
  ADD KEY `idx_ethics_protocols_status` (`status`);

--
-- Indexes for table `kpi_records`
--
ALTER TABLE `kpi_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kpi_faculty` (`faculty_name`),
  ADD KEY `idx_kpi_quarter` (`quarter`);

--
-- Indexes for table `publication_presentations`
--
ALTER TABLE `publication_presentations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_publications_author` (`author_name`),
  ADD KEY `idx_publications_status` (`status`);

--
-- Indexes for table `research_capacity_activities`
--
ALTER TABLE `research_capacity_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_research_capacity_date` (`activity_date`),
  ADD KEY `idx_research_capacity_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_department` (`department`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `data_collection_tools`
--
ALTER TABLE `data_collection_tools`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ethics_reviewed_protocols`
--
ALTER TABLE `ethics_reviewed_protocols`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kpi_records`
--
ALTER TABLE `kpi_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `publication_presentations`
--
ALTER TABLE `publication_presentations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `research_capacity_activities`
--
ALTER TABLE `research_capacity_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
