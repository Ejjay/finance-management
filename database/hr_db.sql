SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(50) NOT NULL,
  `date` date NOT NULL,  -- Removed `attendance_date` to avoid duplication
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` varchar(20) DEFAULT 'present',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `attendance_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(50) NOT NULL,
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL,
  `status` enum('present','absent','late','early_leave') DEFAULT 'present',
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `attendance_logs_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

CREATE TABLE `disbursements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payroll_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `disbursement_date` datetime NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `payroll_id` (`payroll_id`),
  CONSTRAINT `disbursements_ibfk_1` FOREIGN KEY (`payroll_id`) REFERENCES `payroll` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(50) NOT NULL UNIQUE,
  `employee_name` varchar(100) NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `absents` int(11) DEFAULT 0,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `employees` (`id`, `employee_id`, `employee_name`, `time_in`, `time_out`, `basic_salary`, `absents`, `date`, `created_at`, `updated_at`) VALUES
(6, '677423465234', 'Test', '06:54:00', '17:50:00', 40000.00, 4, '2025-05-13', '2025-05-13 13:54:58', '2025-05-13 13:54:58');

CREATE TABLE `employee_benefits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(50) NOT NULL,
  `benefit_type` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `effective_date` date NOT NULL DEFAULT curdate(),
  `end_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `employee_benefits_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `employee_benefits` (`id`, `employee_id`, `benefit_type`, `amount`, `start_date`, `effective_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(2, '677423465234', 'Meal Allowance', 1000.00, '2025-05-01', '2025-05-20', NULL, 'active', '2025-05-13 14:41:11', '2025-05-13 14:41:11');

CREATE TABLE `payroll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `disbursement_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_employee_id` (`employee_id`),
  KEY `idx_payroll_date` (`payroll_date`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_is_archived` (`is_archived`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `payroll` (`id`, `employee_id`, `employee_name`, `attendance`, `basic_salary`, `deductions`, `retirement_contribution`, `thirteenth_month`, `total_salary`, `payroll_date`, `payment_status`, `is_archived`, `archive_date`, `created_at`, `updated_at`, `disbursement_date`) VALUES
(2, '677423465234', 'Test', 18, 40000.00, 7272.73, 2000.00, 0.00, 30727.27, '2025-05-13', 'unpaid', 0, NULL, '2025-05-13 13:55:04', '2025-05-13 13:55:04', NULL);

DROP TABLE IF EXISTS `employee_attendance_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `employee_attendance_summary` AS 
SELECT `e`.`employee_id` AS `employee_id`, `e`.`employee_name` AS `employee_name`, `e`.`basic_salary` AS `basic_salary`, count(distinct `al`.`date`) AS `days_present`, `e`.`absents` AS `absents`, round(avg(time_to_sec(timediff(`al`.`time_out`,`al`.`time_in`)) / 3600),2) AS `avg_hours_per_day` 
FROM (`employees` `e` left join `attendance_logs` `al` on(`e`.`employee_id` = `al`.`employee_id`)) 
GROUP BY `e`.`employee_id`, `e`.`employee_name`, `e`.`basic_salary`, `e`.`absents`;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;