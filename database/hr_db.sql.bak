-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2025 at 06:27 PM
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
-- Database: `hr_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `attendance_date` date NOT NULL DEFAULT curdate(),
  `date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` varchar(20) DEFAULT 'present',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_logs`
--

CREATE TABLE `attendance_logs` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL,
  `status` enum('present','absent','late','early_leave') DEFAULT 'present',
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `attendance_logs`
--
DELIMITER $$
CREATE TRIGGER `after_attendance_log` AFTER INSERT ON `attendance_logs` FOR EACH ROW BEGIN
    -- Update employee record with latest attendance
    UPDATE employees
    SET 
        time_in = NEW.time_in,
        time_out = NEW.time_out,
        updated_at = CURRENT_TIMESTAMP
    WHERE employee_id = NEW.employee_id;
    
    -- If status is absent, increment the absents counter
    IF NEW.status = 'absent' THEN
        UPDATE employees
        SET absents = absents + 1
        WHERE employee_id = NEW.employee_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `disbursements`
--

CREATE TABLE `disbursements` (
  `id` int(11) NOT NULL,
  `payroll_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `disbursement_date` datetime NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `employee_name` varchar(100) NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `absents` int(11) DEFAULT 0,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_id`, `employee_name`, `time_in`, `time_out`, `basic_salary`, `absents`, `date`, `created_at`, `updated_at`) VALUES
(6, '677423465234', 'Test', '06:54:00', '17:50:00', 40000.00, 4, '2025-05-13', '2025-05-13 13:54:58', '2025-05-13 13:54:58');

-- --------------------------------------------------------

--
-- Stand-in structure for view `employee_attendance_summary`
-- (See below for the actual view)
--
CREATE TABLE `employee_attendance_summary` (
`employee_id` varchar(50)
,`employee_name` varchar(100)
,`basic_salary` decimal(10,2)
,`days_present` bigint(21)
,`absents` int(11)
,`avg_hours_per_day` decimal(19,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `employee_benefits`
--

CREATE TABLE `employee_benefits` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `benefit_type` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `effective_date` date NOT NULL DEFAULT curdate(),
  `end_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_benefits`
--

INSERT INTO `employee_benefits` (`id`, `employee_id`, `benefit_type`, `amount`, `start_date`, `effective_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(2, '677423465234', 'Meal Allowance', 1000.00, '0000-00-00', '2025-05-20', NULL, 'active', '2025-05-13 14:41:11', '2025-05-13 14:41:11');

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `employee_name` varchar(100) NOT NULL,
  `attendance` int(11) DEFAULT 0,
  `basic_salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deductions` decimal(10,2) DEFAULT 0.00,
  `retirement_contribution` decimal(10,2) DEFAULT 0.00,
  `thirteenth_month` decimal(10,2) DEFAULT 0.00,
  `total_salary` decimal(10,2) DEFAULT 0.00,
  `payroll_date` date DEFAULT curdate(),
  `payment_status` enum('paid','unpaid','pending') DEFAULT 'unpaid',
  `is_archived` tinyint(1) DEFAULT 0,
  `archive_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `disbursement_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll`
--

INSERT INTO `payroll` (`id`, `employee_id`, `employee_name`, `attendance`, `basic_salary`, `deductions`, `retirement_contribution`, `thirteenth_month`, `total_salary`, `payroll_date`, `payment_status`, `is_archived`, `archive_date`, `created_at`, `updated_at`, `disbursement_date`) VALUES
(2, '677423465234', 'Test', 18, 40000.00, 7272.73, 2000.00, 0.00, 30727.27, '2025-05-13', 'unpaid', 0, NULL, '2025-05-13 13:55:04', '2025-05-13 13:55:04', NULL);

-- --------------------------------------------------------

--
-- Structure for view `employee_attendance_summary`
--
DROP TABLE IF EXISTS `employee_attendance_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `employee_attendance_summary`  AS SELECT `e`.`employee_id` AS `employee_id`, `e`.`employee_name` AS `employee_name`, `e`.`basic_salary` AS `basic_salary`, count(distinct `al`.`date`) AS `days_present`, `e`.`absents` AS `absents`, round(avg(time_to_sec(timediff(`al`.`time_out`,`al`.`time_in`)) / 3600),2) AS `avg_hours_per_day` FROM (`employees` `e` left join `attendance_logs` `al` on(`e`.`employee_id` = `al`.`employee_id`)) GROUP BY `e`.`employee_id`, `e`.`employee_name`, `e`.`basic_salary`, `e`.`absents` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `disbursements`
--
ALTER TABLE `disbursements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payroll_id` (`payroll_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`);

--
-- Indexes for table `employee_benefits`
--
ALTER TABLE `employee_benefits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_payroll_date` (`payroll_date`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_is_archived` (`is_archived`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `disbursements`
--
ALTER TABLE `disbursements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `employee_benefits`
--
ALTER TABLE `employee_benefits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD CONSTRAINT `attendance_logs_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `disbursements`
--
ALTER TABLE `disbursements`
  ADD CONSTRAINT `disbursements_ibfk_1` FOREIGN KEY (`payroll_id`) REFERENCES `payroll` (`id`);

--
-- Constraints for table `employee_benefits`
--
ALTER TABLE `employee_benefits`
  ADD CONSTRAINT `employee_benefits_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
