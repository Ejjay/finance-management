-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2025 at 01:26 PM
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
-- Database: `collection_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `due_records`
--

CREATE TABLE `due_records` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `fee_schedule_id` int(11) NOT NULL,
  `amount_due` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('pending','partial','paid') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `due_records`
--

INSERT INTO `due_records` (`id`, `student_id`, `fee_schedule_id`, `amount_due`, `due_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 's22016816', 1, 5000.00, '2025-05-23', 'paid', '2025-05-14 19:10:39', '2025-05-15 10:52:00'),
(2, 's22016619', 2, 14000.00, '2025-05-31', 'pending', '2025-05-15 10:53:53', '2025-05-15 10:53:53');

-- --------------------------------------------------------

--
-- Table structure for table `fee_schedules`
--

CREATE TABLE `fee_schedules` (
  `id` int(11) NOT NULL,
  `fee_name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fee_schedules`
--

INSERT INTO `fee_schedules` (`id`, `fee_name`, `amount`, `due_date`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Test', 5000.00, '2025-05-16', 'test', '2025-05-14 19:10:16', '2025-05-14 19:10:16'),
(2, 'test2', 14000.00, '2025-05-31', 'tour', '2025-05-15 10:53:05', '2025-05-15 10:53:05');

-- --------------------------------------------------------

--
-- Table structure for table `payment_receipts`
--

CREATE TABLE `payment_receipts` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `generated_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_payments`
--

CREATE TABLE `student_payments` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `fee_schedule_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `transaction_reference` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `due_records`
--
ALTER TABLE `due_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fee_schedule_id` (`fee_schedule_id`),
  ADD KEY `idx_due_records_student_id` (`student_id`),
  ADD KEY `idx_due_records_status` (`status`);

--
-- Indexes for table `fee_schedules`
--
ALTER TABLE `fee_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fee_schedules_due_date` (`due_date`);

--
-- Indexes for table `payment_receipts`
--
ALTER TABLE `payment_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `student_payments`
--
ALTER TABLE `student_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fee_schedule_id` (`fee_schedule_id`),
  ADD KEY `idx_student_payments_student_id` (`student_id`),
  ADD KEY `idx_student_payments_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `due_records`
--
ALTER TABLE `due_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fee_schedules`
--
ALTER TABLE `fee_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_receipts`
--
ALTER TABLE `payment_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_payments`
--
ALTER TABLE `student_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `due_records`
--
ALTER TABLE `due_records`
  ADD CONSTRAINT `due_records_ibfk_1` FOREIGN KEY (`fee_schedule_id`) REFERENCES `fee_schedules` (`id`);

--
-- Constraints for table `payment_receipts`
--
ALTER TABLE `payment_receipts`
  ADD CONSTRAINT `payment_receipts_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `student_payments` (`id`);

--
-- Constraints for table `student_payments`
--
ALTER TABLE `student_payments`
  ADD CONSTRAINT `student_payments_ibfk_1` FOREIGN KEY (`fee_schedule_id`) REFERENCES `fee_schedules` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
