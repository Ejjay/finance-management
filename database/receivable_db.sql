-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2025 at 01:27 PM
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
-- Database: `receivable_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `aging_brackets`
--

CREATE TABLE `aging_brackets` (
  `id` int(11) NOT NULL,
  `bracket_name` varchar(50) NOT NULL,
  `days_from` int(11) NOT NULL,
  `days_to` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aging_brackets`
--

INSERT INTO `aging_brackets` (`id`, `bracket_name`, `days_from`, `days_to`) VALUES
(1, 'Current', 0, 30),
(2, '31-60 Days', 31, 60),
(3, '61-90 Days', 61, 90),
(4, 'Over 90 Days', 91, 999);

-- --------------------------------------------------------

--
-- Table structure for table `billing_items`
--

CREATE TABLE `billing_items` (
  `id` int(11) NOT NULL,
  `billing_id` int(11) NOT NULL,
  `item_description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing_items`
--

INSERT INTO `billing_items` (`id`, `billing_id`, `item_description`, `amount`) VALUES
(1, 1, 'Misc.', 5000.00),
(2, 1, 'Tour', 10000.00);

-- --------------------------------------------------------

--
-- Table structure for table `collection_followups`
--

CREATE TABLE `collection_followups` (
  `id` int(11) NOT NULL,
  `billing_id` int(11) NOT NULL,
  `followup_date` date NOT NULL,
  `followup_type` enum('email','phone','letter') NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `response` text DEFAULT NULL,
  `next_followup_date` date DEFAULT NULL,
  `status` enum('pending','responded','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_records`
--

CREATE TABLE `payment_records` (
  `id` int(11) NOT NULL,
  `billing_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','check','online') NOT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_records`
--

INSERT INTO `payment_records` (`id`, `billing_id`, `payment_date`, `payment_amount`, `payment_method`, `reference_number`, `notes`, `created_at`) VALUES
(1, 1, '2025-05-14', 15000.00, 'online', '85635362', 'test', '2025-05-14 19:13:35');

-- --------------------------------------------------------

--
-- Table structure for table `student_billing`
--

CREATE TABLE `student_billing` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `billing_date` date NOT NULL,
  `due_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `balance_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','partial','paid') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_billing`
--

INSERT INTO `student_billing` (`id`, `student_id`, `student_name`, `billing_date`, `due_date`, `total_amount`, `balance_amount`, `status`, `created_at`, `updated_at`) VALUES
(1, 's22016816', 'test', '2025-05-14', '2025-05-31', 15000.00, 0.00, 'paid', '2025-05-14 15:52:45', '2025-05-14 19:13:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aging_brackets`
--
ALTER TABLE `aging_brackets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `billing_items`
--
ALTER TABLE `billing_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `billing_id` (`billing_id`);

--
-- Indexes for table `collection_followups`
--
ALTER TABLE `collection_followups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `billing_id` (`billing_id`);

--
-- Indexes for table `payment_records`
--
ALTER TABLE `payment_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `billing_id` (`billing_id`);

--
-- Indexes for table `student_billing`
--
ALTER TABLE `student_billing`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aging_brackets`
--
ALTER TABLE `aging_brackets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `billing_items`
--
ALTER TABLE `billing_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `collection_followups`
--
ALTER TABLE `collection_followups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_records`
--
ALTER TABLE `payment_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_billing`
--
ALTER TABLE `student_billing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `billing_items`
--
ALTER TABLE `billing_items`
  ADD CONSTRAINT `billing_items_ibfk_1` FOREIGN KEY (`billing_id`) REFERENCES `student_billing` (`id`);

--
-- Constraints for table `collection_followups`
--
ALTER TABLE `collection_followups`
  ADD CONSTRAINT `collection_followups_ibfk_1` FOREIGN KEY (`billing_id`) REFERENCES `student_billing` (`id`);

--
-- Constraints for table `payment_records`
--
ALTER TABLE `payment_records`
  ADD CONSTRAINT `payment_records_ibfk_1` FOREIGN KEY (`billing_id`) REFERENCES `student_billing` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
