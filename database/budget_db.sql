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
-- Database: `budget_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `annual_budget_plans`
--

CREATE TABLE `annual_budget_plans` (
  `plan_id` int(11) NOT NULL,
  `fiscal_year` int(4) NOT NULL,
  `total_budget` decimal(15,2) NOT NULL,
  `status` enum('draft','approved','active','closed') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `annual_budget_plans`
--

INSERT INTO `annual_budget_plans` (`plan_id`, `fiscal_year`, `total_budget`, `status`, `created_at`, `approved_at`, `approved_by`) VALUES
(1, 2024, 5000000.00, 'draft', '2025-05-13 15:29:18', NULL, NULL),
(2, 2024, 5000000.00, 'draft', '2025-05-13 15:29:31', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `budget_allocation`
--

CREATE TABLE `budget_allocation` (
  `allocation_id` int(11) NOT NULL,
  `fiscal_year` int(11) NOT NULL,
  `total_budget` decimal(15,2) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget_allocation`
--

INSERT INTO `budget_allocation` (`allocation_id`, `fiscal_year`, `total_budget`, `last_updated`) VALUES
(1, 2024, 10000000.00, '2025-05-07 01:49:42'),
(2, 2024, 10000000.00, '2025-05-07 01:49:47'),
(3, 2024, 10000000.00, '2025-05-07 01:49:50'),
(4, 2024, 10000000.00, '2025-05-07 01:50:00'),
(5, 2024, 10000000.00, '2025-05-07 01:50:12'),
(6, 2024, 10000000.00, '2025-05-07 01:52:32'),
(7, 2024, 10000000.00, '2025-05-07 01:55:21'),
(8, 2024, 10000000.00, '2025-05-07 01:56:57'),
(9, 2024, 10000000.00, '2025-05-07 01:57:00'),
(10, 2024, 10000000.00, '2025-05-07 01:57:08'),
(11, 2024, 10000000.00, '2025-05-07 01:58:36'),
(12, 2024, 10000000.00, '2025-05-07 02:02:35'),
(13, 2024, 10000000.00, '2025-05-07 02:07:19'),
(14, 2024, 10000000.00, '2025-05-07 02:07:31'),
(15, 2024, 10000000.00, '2025-05-07 02:14:36'),
(16, 2024, 10000000.00, '2025-05-07 02:16:41'),
(17, 2024, 10000000.00, '2025-05-07 02:18:58'),
(18, 2024, 10000000.00, '2025-05-07 02:19:23'),
(19, 2024, 10000000.00, '2025-05-07 02:22:06'),
(20, 2024, 10000000.00, '2025-05-07 02:22:16'),
(21, 2024, 10000000.00, '2025-05-07 02:22:22'),
(22, 2024, 10000000.00, '2025-05-07 02:26:16'),
(23, 2024, 10000000.00, '2025-05-07 02:26:17'),
(24, 2025, 500000.00, '2025-05-07 02:26:38'),
(25, 2024, 10000000.00, '2025-05-07 02:26:38'),
(26, 2024, 10000000.00, '2025-05-07 02:29:23'),
(27, 2024, 10000000.00, '2025-05-07 02:29:59'),
(28, 2024, 10000000.00, '2025-05-07 02:30:10'),
(29, 2024, 10000000.00, '2025-05-07 02:30:12'),
(30, 2024, 10000000.00, '2025-05-07 02:31:48'),
(31, 2024, 10000000.00, '2025-05-07 02:31:49'),
(32, 2024, 10000000.00, '2025-05-07 02:31:51'),
(33, 2024, 10000000.00, '2025-05-07 02:31:51'),
(34, 2025, 700000.00, '2025-05-07 02:32:10'),
(35, 2024, 10000000.00, '2025-05-07 02:32:10'),
(36, 2024, 10000000.00, '2025-05-07 02:32:18'),
(37, 2024, 10000000.00, '2025-05-07 02:32:24'),
(38, 2024, 10000000.00, '2025-05-07 02:36:55'),
(39, 2024, 10000000.00, '2025-05-07 02:38:02'),
(40, 2024, 10000000.00, '2025-05-07 02:40:05'),
(41, 2024, 10000000.00, '2025-05-07 02:40:06'),
(42, 2024, 10000000.00, '2025-05-07 02:40:09'),
(43, 2024, 10000000.00, '2025-05-07 02:40:12'),
(44, 2024, 10000000.00, '2025-05-07 02:40:17'),
(45, 2025, 500000.00, '2025-05-07 02:40:37'),
(46, 2024, 10000000.00, '2025-05-07 02:40:37'),
(47, 2025, 500000.00, '2025-05-07 02:40:44'),
(48, 2024, 10000000.00, '2025-05-07 02:40:44'),
(49, 2024, 10000000.00, '2025-05-07 02:40:48'),
(50, 2024, 10000000.00, '2025-05-07 03:01:21'),
(51, 2024, 10000000.00, '2025-05-07 03:20:57'),
(52, 2024, 10000000.00, '2025-05-07 03:21:01');

-- --------------------------------------------------------

--
-- Table structure for table `budget_categories`
--

CREATE TABLE `budget_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget_categories`
--

INSERT INTO `budget_categories` (`category_id`, `category_name`, `percentage`, `description`) VALUES
(1, 'Payroll', 40.00, 'Employee salaries and benefits'),
(2, 'Maintenance', 15.00, 'Facility maintenance and repairs'),
(3, 'Utilities', 10.00, 'Electricity, water, and other utilities'),
(4, 'Academic Operations', 20.00, 'Educational materials and resources'),
(5, 'Administrative', 10.00, 'Office supplies and administrative costs'),
(6, 'Emergency Fund', 5.00, 'Reserved for unexpected expenses');

-- --------------------------------------------------------

--
-- Table structure for table `budget_forecasts`
--

CREATE TABLE `budget_forecasts` (
  `forecast_id` int(11) NOT NULL,
  `start_year` int(4) NOT NULL,
  `end_year` int(4) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `projected_amount` decimal(15,2) NOT NULL,
  `growth_rate` decimal(5,2) DEFAULT NULL,
  `assumptions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `budget_revisions`
--

CREATE TABLE `budget_revisions` (
  `revision_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `allocation_id` int(11) DEFAULT NULL,
  `previous_amount` decimal(15,2) NOT NULL,
  `revised_amount` decimal(15,2) NOT NULL,
  `reason` text NOT NULL,
  `revision_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `department_budget`
--

CREATE TABLE `department_budget` (
  `dept_budget_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `allocation_id` int(11) NOT NULL,
  `budget_percentage` decimal(5,2) NOT NULL,
  `allocated_amount` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department_budget`
--

INSERT INTO `department_budget` (`dept_budget_id`, `department_name`, `allocation_id`, `budget_percentage`, `allocated_amount`) VALUES
(1, 'Academic Affairs', 1, 30.00, 3000000.00),
(2, 'Student Services', 1, 20.00, 2000000.00),
(3, 'Administration', 1, 25.00, 2500000.00),
(4, 'Research', 1, 15.00, 1500000.00),
(5, 'Facilities', 1, 10.00, 1000000.00),
(6, 'Academic Affairs', 2, 30.00, 3000000.00),
(7, 'Student Services', 2, 20.00, 2000000.00),
(8, 'Administration', 2, 25.00, 2500000.00),
(9, 'Research', 2, 15.00, 1500000.00),
(10, 'Facilities', 2, 10.00, 1000000.00),
(11, 'Academic Affairs', 3, 30.00, 3000000.00),
(12, 'Student Services', 3, 20.00, 2000000.00),
(13, 'Administration', 3, 25.00, 2500000.00),
(14, 'Research', 3, 15.00, 1500000.00),
(15, 'Facilities', 3, 10.00, 1000000.00),
(16, 'Academic Affairs', 4, 30.00, 3000000.00),
(17, 'Student Services', 4, 20.00, 2000000.00),
(18, 'Administration', 4, 25.00, 2500000.00),
(19, 'Research', 4, 15.00, 1500000.00),
(20, 'Facilities', 4, 10.00, 1000000.00),
(21, 'Academic Affairs', 5, 30.00, 3000000.00),
(22, 'Student Services', 5, 20.00, 2000000.00),
(23, 'Administration', 5, 25.00, 2500000.00),
(24, 'Research', 5, 15.00, 1500000.00),
(25, 'Facilities', 5, 10.00, 1000000.00),
(26, 'Academic Affairs', 6, 30.00, 3000000.00),
(27, 'Student Services', 6, 20.00, 2000000.00),
(28, 'Administration', 6, 25.00, 2500000.00),
(29, 'Research', 6, 15.00, 1500000.00),
(30, 'Facilities', 6, 10.00, 1000000.00),
(31, 'Academic Affairs', 7, 30.00, 3000000.00),
(32, 'Student Services', 7, 20.00, 2000000.00),
(33, 'Administration', 7, 25.00, 2500000.00),
(34, 'Research', 7, 15.00, 1500000.00),
(35, 'Facilities', 7, 10.00, 1000000.00),
(36, 'Academic Affairs', 8, 30.00, 3000000.00),
(37, 'Student Services', 8, 20.00, 2000000.00),
(38, 'Administration', 8, 25.00, 2500000.00),
(39, 'Research', 8, 15.00, 1500000.00),
(40, 'Facilities', 8, 10.00, 1000000.00),
(41, 'Academic Affairs', 9, 30.00, 3000000.00),
(42, 'Student Services', 9, 20.00, 2000000.00),
(43, 'Administration', 9, 25.00, 2500000.00),
(44, 'Research', 9, 15.00, 1500000.00),
(45, 'Facilities', 9, 10.00, 1000000.00),
(46, 'Academic Affairs', 10, 30.00, 3000000.00),
(47, 'Student Services', 10, 20.00, 2000000.00),
(48, 'Administration', 10, 25.00, 2500000.00),
(49, 'Research', 10, 15.00, 1500000.00),
(50, 'Facilities', 10, 10.00, 1000000.00),
(51, 'Academic Affairs', 11, 30.00, 3000000.00),
(52, 'Student Services', 11, 20.00, 2000000.00),
(53, 'Administration', 11, 25.00, 2500000.00),
(54, 'Research', 11, 15.00, 1500000.00),
(55, 'Facilities', 11, 10.00, 1000000.00),
(56, 'Academic Affairs', 12, 30.00, 3000000.00),
(57, 'Student Services', 12, 20.00, 2000000.00),
(58, 'Administration', 12, 25.00, 2500000.00),
(59, 'Research', 12, 15.00, 1500000.00),
(60, 'Facilities', 12, 10.00, 1000000.00),
(61, 'Academic Affairs', 13, 30.00, 3000000.00),
(62, 'Student Services', 13, 20.00, 2000000.00),
(63, 'Administration', 13, 25.00, 2500000.00),
(64, 'Research', 13, 15.00, 1500000.00),
(65, 'Facilities', 13, 10.00, 1000000.00),
(66, 'Academic Affairs', 14, 30.00, 3000000.00),
(67, 'Student Services', 14, 20.00, 2000000.00),
(68, 'Administration', 14, 25.00, 2500000.00),
(69, 'Research', 14, 15.00, 1500000.00),
(70, 'Facilities', 14, 10.00, 1000000.00),
(71, 'Academic Affairs', 15, 30.00, 3000000.00),
(72, 'Student Services', 15, 20.00, 2000000.00),
(73, 'Administration', 15, 25.00, 2500000.00),
(74, 'Research', 15, 15.00, 1500000.00),
(75, 'Facilities', 15, 10.00, 1000000.00),
(76, 'Academic Affairs', 16, 30.00, 3000000.00),
(77, 'Student Services', 16, 20.00, 2000000.00),
(78, 'Administration', 16, 25.00, 2500000.00),
(79, 'Research', 16, 15.00, 1500000.00),
(80, 'Facilities', 16, 10.00, 1000000.00),
(81, 'Academic Affairs', 17, 30.00, 3000000.00),
(82, 'Student Services', 17, 20.00, 2000000.00),
(83, 'Administration', 17, 25.00, 2500000.00),
(84, 'Research', 17, 15.00, 1500000.00),
(85, 'Facilities', 17, 10.00, 1000000.00),
(86, 'Academic Affairs', 18, 30.00, 3000000.00),
(87, 'Student Services', 18, 20.00, 2000000.00),
(88, 'Administration', 18, 25.00, 2500000.00),
(89, 'Research', 18, 15.00, 1500000.00),
(90, 'Facilities', 18, 10.00, 1000000.00),
(91, 'Academic Affairs', 19, 30.00, 3000000.00),
(92, 'Student Services', 19, 20.00, 2000000.00),
(93, 'Administration', 19, 25.00, 2500000.00),
(94, 'Research', 19, 15.00, 1500000.00),
(95, 'Facilities', 19, 10.00, 1000000.00),
(96, 'Academic Affairs', 20, 30.00, 3000000.00),
(97, 'Student Services', 20, 20.00, 2000000.00),
(98, 'Administration', 20, 25.00, 2500000.00),
(99, 'Research', 20, 15.00, 1500000.00),
(100, 'Facilities', 20, 10.00, 1000000.00),
(101, 'Academic Affairs', 21, 30.00, 3000000.00),
(102, 'Student Services', 21, 20.00, 2000000.00),
(103, 'Administration', 21, 25.00, 2500000.00),
(104, 'Research', 21, 15.00, 1500000.00),
(105, 'Facilities', 21, 10.00, 1000000.00),
(106, 'Academic Affairs', 22, 30.00, 3000000.00),
(107, 'Student Services', 22, 20.00, 2000000.00),
(108, 'Administration', 22, 25.00, 2500000.00),
(109, 'Research', 22, 15.00, 1500000.00),
(110, 'Facilities', 22, 10.00, 1000000.00),
(111, 'Academic Affairs', 23, 30.00, 3000000.00),
(112, 'Student Services', 23, 20.00, 2000000.00),
(113, 'Administration', 23, 25.00, 2500000.00),
(114, 'Research', 23, 15.00, 1500000.00),
(115, 'Facilities', 23, 10.00, 1000000.00),
(116, 'Academic Affairs', 24, 30.00, 150000.00),
(117, 'Student Services', 24, 20.00, 100000.00),
(118, 'Administration', 24, 25.00, 125000.00),
(119, 'Research', 24, 15.00, 75000.00),
(120, 'Facilities', 24, 10.00, 50000.00),
(121, 'Academic Affairs', 25, 30.00, 3000000.00),
(122, 'Student Services', 25, 20.00, 2000000.00),
(123, 'Administration', 25, 25.00, 2500000.00),
(124, 'Research', 25, 15.00, 1500000.00),
(125, 'Facilities', 25, 10.00, 1000000.00),
(126, 'Academic Affairs', 26, 30.00, 3000000.00),
(127, 'Student Services', 26, 20.00, 2000000.00),
(128, 'Administration', 26, 25.00, 2500000.00),
(129, 'Research', 26, 15.00, 1500000.00),
(130, 'Facilities', 26, 10.00, 1000000.00),
(131, 'Academic Affairs', 27, 30.00, 3000000.00),
(132, 'Student Services', 27, 20.00, 2000000.00),
(133, 'Administration', 27, 25.00, 2500000.00),
(134, 'Research', 27, 15.00, 1500000.00),
(135, 'Facilities', 27, 10.00, 1000000.00),
(136, 'Academic Affairs', 28, 30.00, 3000000.00),
(137, 'Student Services', 28, 20.00, 2000000.00),
(138, 'Administration', 28, 25.00, 2500000.00),
(139, 'Research', 28, 15.00, 1500000.00),
(140, 'Facilities', 28, 10.00, 1000000.00),
(141, 'Academic Affairs', 29, 30.00, 3000000.00),
(142, 'Student Services', 29, 20.00, 2000000.00),
(143, 'Administration', 29, 25.00, 2500000.00),
(144, 'Research', 29, 15.00, 1500000.00),
(145, 'Facilities', 29, 10.00, 1000000.00),
(146, 'Academic Affairs', 30, 30.00, 3000000.00),
(147, 'Student Services', 30, 20.00, 2000000.00),
(148, 'Administration', 30, 25.00, 2500000.00),
(149, 'Research', 30, 15.00, 1500000.00),
(150, 'Facilities', 30, 10.00, 1000000.00),
(151, 'Academic Affairs', 31, 30.00, 3000000.00),
(152, 'Student Services', 31, 20.00, 2000000.00),
(153, 'Administration', 31, 25.00, 2500000.00),
(154, 'Research', 31, 15.00, 1500000.00),
(155, 'Facilities', 31, 10.00, 1000000.00),
(156, 'Academic Affairs', 32, 30.00, 3000000.00),
(157, 'Student Services', 32, 20.00, 2000000.00),
(158, 'Administration', 32, 25.00, 2500000.00),
(159, 'Research', 32, 15.00, 1500000.00),
(160, 'Facilities', 32, 10.00, 1000000.00),
(161, 'Academic Affairs', 33, 30.00, 3000000.00),
(162, 'Student Services', 33, 20.00, 2000000.00),
(163, 'Administration', 33, 25.00, 2500000.00),
(164, 'Research', 33, 15.00, 1500000.00),
(165, 'Facilities', 33, 10.00, 1000000.00),
(166, 'Academic Affairs', 34, 30.00, 210000.00),
(167, 'Student Services', 34, 20.00, 140000.00),
(168, 'Administration', 34, 25.00, 175000.00),
(169, 'Research', 34, 15.00, 105000.00),
(170, 'Facilities', 34, 10.00, 70000.00),
(171, 'Academic Affairs', 35, 30.00, 3000000.00),
(172, 'Student Services', 35, 20.00, 2000000.00),
(173, 'Administration', 35, 25.00, 2500000.00),
(174, 'Research', 35, 15.00, 1500000.00),
(175, 'Facilities', 35, 10.00, 1000000.00),
(176, 'Academic Affairs', 36, 30.00, 3000000.00),
(177, 'Student Services', 36, 20.00, 2000000.00),
(178, 'Administration', 36, 25.00, 2500000.00),
(179, 'Research', 36, 15.00, 1500000.00),
(180, 'Facilities', 36, 10.00, 1000000.00),
(181, 'Academic Affairs', 37, 30.00, 3000000.00),
(182, 'Student Services', 37, 20.00, 2000000.00),
(183, 'Administration', 37, 25.00, 2500000.00),
(184, 'Research', 37, 15.00, 1500000.00),
(185, 'Facilities', 37, 10.00, 1000000.00),
(186, 'Academic Affairs', 38, 30.00, 3000000.00),
(187, 'Student Services', 38, 20.00, 2000000.00),
(188, 'Administration', 38, 25.00, 2500000.00),
(189, 'Research', 38, 15.00, 1500000.00),
(190, 'Facilities', 38, 10.00, 1000000.00),
(191, 'Academic Affairs', 39, 30.00, 3000000.00),
(192, 'Student Services', 39, 20.00, 2000000.00),
(193, 'Administration', 39, 25.00, 2500000.00),
(194, 'Research', 39, 15.00, 1500000.00),
(195, 'Facilities', 39, 10.00, 1000000.00),
(196, 'Academic Affairs', 40, 30.00, 3000000.00),
(197, 'Student Services', 40, 20.00, 2000000.00),
(198, 'Administration', 40, 25.00, 2500000.00),
(199, 'Research', 40, 15.00, 1500000.00),
(200, 'Facilities', 40, 10.00, 1000000.00),
(201, 'Academic Affairs', 41, 30.00, 3000000.00),
(202, 'Student Services', 41, 20.00, 2000000.00),
(203, 'Administration', 41, 25.00, 2500000.00),
(204, 'Research', 41, 15.00, 1500000.00),
(205, 'Facilities', 41, 10.00, 1000000.00),
(206, 'Academic Affairs', 42, 30.00, 3000000.00),
(207, 'Student Services', 42, 20.00, 2000000.00),
(208, 'Administration', 42, 25.00, 2500000.00),
(209, 'Research', 42, 15.00, 1500000.00),
(210, 'Facilities', 42, 10.00, 1000000.00),
(211, 'Academic Affairs', 43, 30.00, 3000000.00),
(212, 'Student Services', 43, 20.00, 2000000.00),
(213, 'Administration', 43, 25.00, 2500000.00),
(214, 'Research', 43, 15.00, 1500000.00),
(215, 'Facilities', 43, 10.00, 1000000.00),
(216, 'Academic Affairs', 44, 30.00, 3000000.00),
(217, 'Student Services', 44, 20.00, 2000000.00),
(218, 'Administration', 44, 25.00, 2500000.00),
(219, 'Research', 44, 15.00, 1500000.00),
(220, 'Facilities', 44, 10.00, 1000000.00),
(221, 'Academic Affairs', 45, 30.00, 150000.00),
(222, 'Student Services', 45, 20.00, 100000.00),
(223, 'Administration', 45, 25.00, 125000.00),
(224, 'Research', 45, 15.00, 75000.00),
(225, 'Facilities', 45, 10.00, 50000.00),
(226, 'Academic Affairs', 46, 30.00, 3000000.00),
(227, 'Student Services', 46, 20.00, 2000000.00),
(228, 'Administration', 46, 25.00, 2500000.00),
(229, 'Research', 46, 15.00, 1500000.00),
(230, 'Facilities', 46, 10.00, 1000000.00),
(231, 'Academic Affairs', 47, 30.00, 150000.00),
(232, 'Student Services', 47, 20.00, 100000.00),
(233, 'Administration', 47, 25.00, 125000.00),
(234, 'Research', 47, 15.00, 75000.00),
(235, 'Facilities', 47, 10.00, 50000.00),
(236, 'Academic Affairs', 48, 30.00, 3000000.00),
(237, 'Student Services', 48, 20.00, 2000000.00),
(238, 'Administration', 48, 25.00, 2500000.00),
(239, 'Research', 48, 15.00, 1500000.00),
(240, 'Facilities', 48, 10.00, 1000000.00),
(241, 'Academic Affairs', 49, 30.00, 3000000.00),
(242, 'Student Services', 49, 20.00, 2000000.00),
(243, 'Administration', 49, 25.00, 2500000.00),
(244, 'Research', 49, 15.00, 1500000.00),
(245, 'Facilities', 49, 10.00, 1000000.00),
(246, 'Academic Affairs', 50, 30.00, 3000000.00),
(247, 'Student Services', 50, 20.00, 2000000.00),
(248, 'Administration', 50, 25.00, 2500000.00),
(249, 'Research', 50, 15.00, 1500000.00),
(250, 'Facilities', 50, 10.00, 1000000.00),
(251, 'Academic Affairs', 51, 30.00, 3000000.00),
(252, 'Student Services', 51, 20.00, 2000000.00),
(253, 'Administration', 51, 25.00, 2500000.00),
(254, 'Research', 51, 15.00, 1500000.00),
(255, 'Facilities', 51, 10.00, 1000000.00),
(256, 'Academic Affairs', 52, 30.00, 3000000.00),
(257, 'Student Services', 52, 20.00, 2000000.00),
(258, 'Administration', 52, 25.00, 2500000.00),
(259, 'Research', 52, 15.00, 1500000.00),
(260, 'Facilities', 52, 10.00, 1000000.00);

-- --------------------------------------------------------

--
-- Table structure for table `department_budget_allocations`
--

CREATE TABLE `department_budget_allocations` (
  `allocation_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `allocated_amount` decimal(15,2) NOT NULL,
  `allocation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `expense_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `dept_budget_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `expense_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`expense_id`, `category_id`, `dept_budget_id`, `amount`, `description`, `expense_date`, `created_at`) VALUES
(3, 1, 1, 500000.00, 'test', '2025-05-07', '2025-05-07 02:30:10');

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE `fees` (
  `fee_id` int(11) NOT NULL,
  `fee_name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `academic_year` varchar(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forecast_factors`
--

CREATE TABLE `forecast_factors` (
  `factor_id` int(11) NOT NULL,
  `forecast_id` int(11) NOT NULL,
  `factor_name` varchar(100) NOT NULL,
  `impact_percentage` decimal(5,2) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `enrollment_date` date NOT NULL,
  `program` varchar(100) NOT NULL,
  `year_level` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_fees`
--

CREATE TABLE `student_fees` (
  `student_fee_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `fee_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('paid','partial','unpaid') DEFAULT 'unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `annual_budget_plans`
--
ALTER TABLE `annual_budget_plans`
  ADD PRIMARY KEY (`plan_id`);

--
-- Indexes for table `budget_allocation`
--
ALTER TABLE `budget_allocation`
  ADD PRIMARY KEY (`allocation_id`);

--
-- Indexes for table `budget_categories`
--
ALTER TABLE `budget_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `budget_forecasts`
--
ALTER TABLE `budget_forecasts`
  ADD PRIMARY KEY (`forecast_id`);

--
-- Indexes for table `budget_revisions`
--
ALTER TABLE `budget_revisions`
  ADD PRIMARY KEY (`revision_id`),
  ADD KEY `plan_id` (`plan_id`),
  ADD KEY `allocation_id` (`allocation_id`);

--
-- Indexes for table `department_budget`
--
ALTER TABLE `department_budget`
  ADD PRIMARY KEY (`dept_budget_id`),
  ADD KEY `allocation_id` (`allocation_id`);

--
-- Indexes for table `department_budget_allocations`
--
ALTER TABLE `department_budget_allocations`
  ADD PRIMARY KEY (`allocation_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`expense_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `dept_budget_id` (`dept_budget_id`);

--
-- Indexes for table `fees`
--
ALTER TABLE `fees`
  ADD PRIMARY KEY (`fee_id`);

--
-- Indexes for table `forecast_factors`
--
ALTER TABLE `forecast_factors`
  ADD PRIMARY KEY (`factor_id`),
  ADD KEY `forecast_id` (`forecast_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`);

--
-- Indexes for table `student_fees`
--
ALTER TABLE `student_fees`
  ADD PRIMARY KEY (`student_fee_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fee_id` (`fee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `annual_budget_plans`
--
ALTER TABLE `annual_budget_plans`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `budget_allocation`
--
ALTER TABLE `budget_allocation`
  MODIFY `allocation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `budget_categories`
--
ALTER TABLE `budget_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `budget_forecasts`
--
ALTER TABLE `budget_forecasts`
  MODIFY `forecast_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `budget_revisions`
--
ALTER TABLE `budget_revisions`
  MODIFY `revision_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `department_budget`
--
ALTER TABLE `department_budget`
  MODIFY `dept_budget_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=261;

--
-- AUTO_INCREMENT for table `department_budget_allocations`
--
ALTER TABLE `department_budget_allocations`
  MODIFY `allocation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fees`
--
ALTER TABLE `fees`
  MODIFY `fee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forecast_factors`
--
ALTER TABLE `forecast_factors`
  MODIFY `factor_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_fees`
--
ALTER TABLE `student_fees`
  MODIFY `student_fee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `budget_revisions`
--
ALTER TABLE `budget_revisions`
  ADD CONSTRAINT `budget_revisions_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `annual_budget_plans` (`plan_id`),
  ADD CONSTRAINT `budget_revisions_ibfk_2` FOREIGN KEY (`allocation_id`) REFERENCES `department_budget_allocations` (`allocation_id`);

--
-- Constraints for table `department_budget`
--
ALTER TABLE `department_budget`
  ADD CONSTRAINT `department_budget_ibfk_1` FOREIGN KEY (`allocation_id`) REFERENCES `budget_allocation` (`allocation_id`);

--
-- Constraints for table `department_budget_allocations`
--
ALTER TABLE `department_budget_allocations`
  ADD CONSTRAINT `department_budget_allocations_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `annual_budget_plans` (`plan_id`);

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `budget_categories` (`category_id`),
  ADD CONSTRAINT `expenses_ibfk_2` FOREIGN KEY (`dept_budget_id`) REFERENCES `department_budget` (`dept_budget_id`);

--
-- Constraints for table `forecast_factors`
--
ALTER TABLE `forecast_factors`
  ADD CONSTRAINT `forecast_factors_ibfk_1` FOREIGN KEY (`forecast_id`) REFERENCES `budget_forecasts` (`forecast_id`);

--
-- Constraints for table `student_fees`
--
ALTER TABLE `student_fees`
  ADD CONSTRAINT `student_fees_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `student_fees_ibfk_2` FOREIGN KEY (`fee_id`) REFERENCES `fees` (`fee_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
