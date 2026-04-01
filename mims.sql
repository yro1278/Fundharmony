-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 29, 2026 at 03:45 AM
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
-- Database: `mims`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `account_number` int(10) NOT NULL,
  `customer` int(10) NOT NULL,
  `account_type` int(10) NOT NULL,
  `open_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `account_status` int(10) NOT NULL,
  `user_confirmed` tinyint(1) DEFAULT 0,
  `user_id` int(10) DEFAULT NULL,
  `loan_amount` decimal(15,2) DEFAULT 0.00,
  `bank_name` varchar(100) DEFAULT NULL,
  `disbursement_account` varchar(50) DEFAULT NULL,
  `disbursement_account_name` varchar(100) DEFAULT NULL,
  `branch` varchar(100) DEFAULT NULL,
  `loan_balance` decimal(15,2) DEFAULT 0.00,
  `disbursement_method` varchar(50) DEFAULT NULL,
  `ewallet_type` varchar(50) DEFAULT NULL,
  `ewallet_number` varchar(50) DEFAULT NULL,
  `ewallet_account_name` varchar(100) DEFAULT NULL,
  `pickup_location` varchar(100) DEFAULT NULL,
  `interest` decimal(15,2) DEFAULT 0.00,
  `loan_term` int(11) DEFAULT 1,
  `due_date` date DEFAULT NULL,
  `overdue_interest` decimal(15,2) DEFAULT 0.00,
  `reject_notes` text DEFAULT NULL,
  `penalty` decimal(15,2) DEFAULT 0.00,
  `approval_date` date DEFAULT NULL,
  `release_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Account for customera';

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`account_number`, `customer`, `account_type`, `open_date`, `account_status`, `user_confirmed`, `user_id`, `loan_amount`, `bank_name`, `disbursement_account`, `disbursement_account_name`, `branch`, `loan_balance`, `disbursement_method`, `ewallet_type`, `ewallet_number`, `ewallet_account_name`, `pickup_location`, `interest`, `loan_term`, `due_date`, `overdue_interest`, `reject_notes`, `penalty`, `approval_date`, `release_date`) VALUES
(5777, 980614528, 123, '2026-03-27 06:49:10', 8, 0, 11851, 20000.00, '', '', '', '', 20400.00, 'Cash', 'GCash', '', '', 'xzcas', 400.00, 1, '2026-04-27', 0.00, NULL, 0.00, '2026-03-27', NULL),
(1146269, 893619828, 123, '2026-03-16 20:54:47', -3, 0, 369707, 20000.00, '', '', '', '', 0.00, 'Cash', 'GCash', '', '', 'Main Branch', 1200.00, 3, '2026-06-14', 0.00, NULL, 0.00, NULL, NULL),
(3448885, 893619828, 123, '2026-03-13 07:26:13', -3, 0, 369707, 20000.00, '', '', '', '', 0.00, 'Cash', 'GCash', '', '', 'Main Branch', 400.00, 1, '2026-04-12', 0.00, NULL, 0.00, NULL, NULL),
(3587450, 893619828, 123, '2026-03-13 07:28:18', 3, 0, 369707, 1000.00, '', '', '', '', 0.00, 'Cash', 'GCash', '', '', 'Main Branch', 0.00, 1, NULL, 0.00, 'sorry only we con offer is 500\r\n', 0.00, NULL, NULL),
(4111624, 893619828, 123, '2026-03-12 04:49:42', -3, 0, 369707, 9000.00, '', '', '', '', 0.00, 'Cash', 'GCash', '', '', 'Main Branch', 405.00, 3, '2026-06-09', 0.00, NULL, 0.00, NULL, NULL),
(4178854, 893619828, 123, '2026-03-12 20:51:01', 0, 0, 369707, 20000.00, '', '', '', '', 21200.00, 'E-Wallet', 'GCash', '09919094456', 'Tyrone Alariao', 'Main Branch', 1200.00, 3, '2026-06-12', 0.00, NULL, 0.00, NULL, NULL),
(4618634, 893619828, 123, '2026-03-10 11:32:29', -3, 0, 369707, 30000.00, '', '', '', '', 0.00, 'Cash', 'GCash', '', '', 'Main Branch', 450.00, 1, '2026-04-09', 0.00, NULL, 0.00, NULL, NULL),
(5154873, 893619828, 541409724, '2026-03-12 11:07:20', -3, 0, 369707, 50000.00, '', '', '', '', 0.00, 'Cash', 'GCash', '', '', 'Main Branch', 12000.00, 6, '2026-09-11', 0.00, NULL, 0.00, NULL, NULL),
(5214063, 893619828, 541409724, '2026-03-10 11:39:01', -3, 0, 369707, 10000.00, '', '', '', '', 0.00, 'Cash', 'GCash', '', '', 'Main Branch', 900.00, 6, '2026-09-09', 0.00, NULL, 0.00, NULL, NULL),
(5501787, 893619828, 355999221, '2026-03-12 07:28:19', -3, 0, 369707, 20000.00, 'gotyme', '546456', 'tyrone', 'asd', 0.00, 'Bank Transfer', 'GCash', '', '', 'Main Branch', 300.00, 1, '2026-04-11', 0.00, NULL, 0.00, NULL, NULL),
(6190708, 893619828, 355999221, '2026-03-13 07:39:59', 3, 0, 369707, 20000.00, '', '', '', '', 0.00, 'Cash', 'GCash', '', '', 'Main Branch', 0.00, 3, NULL, 0.00, 'yoko\r\n', 0.00, NULL, NULL),
(6452229, 893619828, 123, '2026-03-10 11:26:09', -3, 0, 369707, 2000.00, '', '', '', '', 0.00, 'Cash', 'GCash', '', '', 'Main Branch', 90.00, 3, '2026-06-09', 0.00, NULL, 0.00, NULL, NULL),
(6461143, 893619828, 123, '2026-03-17 02:03:12', -3, 0, 369707, 20000.00, '', '', '', '', 0.00, 'Cash', 'GCash', '', '', 'Main Branch', 1200.00, 3, '2026-06-16', 0.00, NULL, 0.00, NULL, NULL),
(6814101, 893619828, 123, '2026-03-12 07:54:48', -3, 0, 369707, 40000.00, '', '', '', '', 0.00, 'Cash', 'GCash', '', '', 'Main Branch', 600.00, 1, '2026-04-11', 0.00, NULL, 0.00, NULL, NULL),
(7270237, 893619828, 123, '2026-03-27 08:53:28', 5, 0, 369707, 20000.00, '', '', '', '', 16200.00, 'E-Wallet', 'GCash', '09777698003', 'Tyrone Alariao', 'Main Branch', 1200.00, 3, '2026-06-17', 0.00, NULL, 0.00, '2026-03-17', NULL),
(980614528, 980614528, 123, '2026-03-27 06:46:30', 4, 0, 11851, 20000.00, '', '', '', '', 20400.00, 'Cash', 'GCash', '', '', 'basta', 400.00, 1, '2026-04-27', 0.00, NULL, 0.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `account_status`
--

CREATE TABLE `account_status` (
  `account_status_number` int(10) NOT NULL,
  `account_status_name` varchar(50) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_status`
--

INSERT INTO `account_status` (`account_status_number`, `account_status_name`, `registration_date`) VALUES
(-3, 'Closed', '2026-03-11 09:23:43'),
(-2, 'Active', '2026-02-25 15:52:17'),
(0, 'Declined', '2026-03-09 01:44:40'),
(3, 'Rejected', '2026-02-26 10:47:44'),
(4, 'Pending', '2026-02-26 10:44:46'),
(5, 'Partial', '2026-03-11 10:02:31'),
(6, 'Due Date', '2026-03-11 10:02:31'),
(7, 'Up to Date', '2026-03-11 10:02:31'),
(8, 'Approved', '2026-03-10 11:43:28');

-- --------------------------------------------------------

--
-- Table structure for table `account_type`
--

CREATE TABLE `account_type` (
  `account_type_number` int(10) NOT NULL,
  `account_type_name` varchar(50) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_type`
--

INSERT INTO `account_type` (`account_type_number`, `account_type_name`, `registration_date`) VALUES
(123, 'Emergency Loan', '2026-02-26 09:02:59'),
(640124, 'yro', '2026-02-25 15:49:08'),
(355999221, 'Personal Loan', '2026-02-26 09:04:52'),
(541409724, 'Business Loan', '2026-02-26 09:04:52'),
(959813468, 'Educational Loan', '2026-02-26 09:04:52');

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `user_type` enum('admin','customer') DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `username`, `user_type`, `action`, `description`, `ip_address`, `created_at`) VALUES
(1, 898457, 'yro', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 04:33:39'),
(2, 0, 'admin', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-03 04:36:28'),
(3, 898457, 'yro', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 04:36:40'),
(4, 898457, 'yro', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-03 04:42:01'),
(5, 0, 'yro', NULL, 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-03 04:42:06'),
(6, 898457, 'yro', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 04:42:13'),
(7, 898457, 'yro', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-03 04:42:17'),
(8, 0, 'yro', NULL, 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-03 04:42:29'),
(9, 898457, 'yro', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 04:42:39'),
(10, 898457, 'yro', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-03 04:42:42'),
(11, 0, 'yro', NULL, 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-03 04:42:48'),
(12, 0, 'yro', NULL, 'Failed Login', 'Failed login attempt - 2 attempt(s)', '::1', '2026-03-03 04:44:04'),
(13, 0, 'yro', NULL, 'Failed Login', 'Failed login attempt - 3 attempt(s)', '::1', '2026-03-03 04:44:09'),
(14, 0, 'yro', NULL, 'Failed Login', 'Failed login attempt - 4 attempt(s)', '::1', '2026-03-03 04:44:14'),
(15, 0, 'yro', NULL, 'Account Locked', 'Account locked after 5 failed login attempts', '::1', '2026-03-03 04:44:20'),
(16, 0, 'yro', NULL, 'Account Locked', 'Account locked after 5 failed login attempts', '::1', '2026-03-03 04:50:27'),
(17, 898457, 'yro', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 05:02:07'),
(18, 898457, 'yro', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-03 05:02:17'),
(19, 898457, 'yro', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 05:02:26'),
(20, 898457, 'yro', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-03 05:02:38'),
(21, 0, 'Yro', NULL, 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-03 05:04:28'),
(22, 0, 'Yro', NULL, 'Failed Login', 'Failed login attempt - 2 attempt(s)', '::1', '2026-03-03 05:04:57'),
(23, 0, 'yro', NULL, 'Failed Login', 'Failed login attempt - 3 attempt(s)', '::1', '2026-03-03 05:05:31'),
(24, 898457, 'yro', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 05:06:03'),
(25, 898457, 'yro', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-03 05:06:05'),
(26, 0, 'yro', NULL, 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-03 05:07:56'),
(27, 529891, 'tyrone', NULL, 'Admin Registration', 'New admin account registered: tyrone', '::1', '2026-03-03 05:09:31'),
(28, 529891, 'tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 05:09:49'),
(29, 529891, 'tyrone', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-03 05:10:29'),
(30, 898457, 'yro', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 05:16:35'),
(31, 898457, 'yro', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-03 05:16:39'),
(32, 0, 'yro', NULL, 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-03 05:17:04'),
(33, 0, 'tyronealariao06@gmail.com', NULL, 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-03 05:37:35'),
(34, 0, 'tyronealariao06@gmail.com', NULL, 'Failed Login', 'Failed login attempt - 2 attempt(s)', '::1', '2026-03-03 05:37:53'),
(35, 369707, 'Tyrone', NULL, 'Admin Registration', 'New admin account registered: Tyrone', '::1', '2026-03-03 05:38:25'),
(36, 369707, 'tyronealariao06@gmail.com', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 05:38:30'),
(37, 369707, 'Tyrone', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-03 05:39:45'),
(38, 369707, 'tyronealariao06@gmail.com', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 06:04:56'),
(39, 369707, 'Tyrone', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-03 06:10:59'),
(40, 369707, 'tyronealariao06@gmail.com', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 06:23:19'),
(41, 369707, 'Tyrone', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-03 06:24:10'),
(42, 369707, 'tyronealariao06@gmail.com', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 06:31:15'),
(43, 369707, 'tyronealariao06@gmail.com', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 06:31:15'),
(44, 369707, 'Tyrone', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-03 06:31:18'),
(45, 369707, 'tyronealariao06@gmail.com', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 06:31:27'),
(46, 369707, 'Tyrone', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-03 06:31:30'),
(47, 369707, 'tyronealariao06@gmail.com', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 06:32:58'),
(48, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 2505619 - Amount: ₱30,000.00', '::1', '2026-03-03 06:33:16'),
(49, 369707, 'Tyrone', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-03 06:36:25'),
(50, 369707, 'tyronealariao06@gmail.com', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 06:38:58'),
(51, 369707, 'Tyrone', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-03 06:39:28'),
(52, 369707, 'Tyrone', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-03 06:39:29'),
(53, 369707, 'tyronealariao06@gmail.com', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 06:39:58'),
(54, 369707, 'Tyrone', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-03 06:41:37'),
(55, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 06:42:24'),
(56, 369707, 'Tyrone', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-03 07:08:39'),
(57, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 07:12:19'),
(58, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 07:34:41'),
(59, 859568034, 'tyrone alariao', NULL, 'Customer Login', 'Customer logged in successfully', '::1', '2026-03-03 07:36:50'),
(60, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-03 19:50:19'),
(61, 859568034, 'tyrone alariao', NULL, 'Customer Login', 'Customer logged in successfully', '::1', '2026-03-03 19:50:30'),
(62, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-08 05:28:09'),
(63, 369707, 'Tyrone', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-08 05:28:15'),
(64, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-08 05:54:52'),
(65, 369707, 'Tyrone', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-08 06:03:03'),
(66, 369707, 'Tyrone', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-08 06:03:03'),
(67, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-08 06:03:36'),
(68, 859568034, 'tyrone alariao', NULL, 'Customer Login', 'Customer logged in successfully', '::1', '2026-03-08 08:49:53'),
(69, 0, 'admin', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-08 08:50:22'),
(70, 0, 'admin', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-08 08:50:22'),
(71, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-09 00:49:38'),
(72, 0, 'tyronealariao06@gmail.com', NULL, 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-09 00:49:46'),
(73, 859568034, 'tyrone alariao', NULL, 'Customer Login', 'Customer logged in successfully', '::1', '2026-03-09 00:49:59'),
(74, 369707, 'Tyrone', NULL, 'Reject Loan', 'Rejected loan account ID: 1031425 - Reason: kupal', '::1', '2026-03-09 01:10:15'),
(75, 0, 'tyronealariao06@gmail.com', NULL, 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-09 01:15:43'),
(76, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-09 01:16:27'),
(77, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 6562122 - Amount: ₱1,000.00', '::1', '2026-03-09 01:22:14'),
(78, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 1320210 - Amount: ₱50,000.00', '::1', '2026-03-09 01:35:36'),
(79, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 1320210 - Amount: ₱50,000.00', '::1', '2026-03-09 01:42:02'),
(80, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 1320210 - Amount: ₱50,000.00', '::1', '2026-03-09 01:47:14'),
(81, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 4502808 - Amount: ₱100,000.00', '::1', '2026-03-09 01:57:51'),
(82, 369707, 'Tyrone', NULL, 'Delete Account', 'Deleted account ID: 6562122', '::1', '2026-03-09 02:13:44'),
(83, 369707, 'Tyrone', NULL, 'Delete Account', 'Deleted account ID: 6562122', '::1', '2026-03-09 02:13:48'),
(84, 369707, 'Tyrone', NULL, 'Delete Account', 'Deleted account ID: 6562122', '::1', '2026-03-09 02:45:39'),
(85, 369707, 'Tyrone', NULL, 'Delete Account', 'Deleted account ID: 2505619', '::1', '2026-03-09 02:45:42'),
(86, 369707, 'Tyrone', NULL, 'Delete Account', 'Deleted account ID: 6562122', '::1', '2026-03-09 02:46:17'),
(87, 369707, 'Tyrone', NULL, 'Delete Account', 'Deleted account ID: 6562122', '::1', '2026-03-09 02:46:24'),
(88, 369707, 'Tyrone', NULL, 'Delete Account', 'Deleted account ID: 2505619', '::1', '2026-03-09 02:46:27'),
(89, 369707, 'Tyrone', NULL, 'Delete Account', 'Deleted account ID: 2505619', '::1', '2026-03-09 02:46:34'),
(90, 369707, 'Tyrone', NULL, 'Delete Account', 'Deleted account ID: 6562122', '::1', '2026-03-09 02:46:36'),
(91, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 8846340 - Amount: ₱100,000.00', '::1', '2026-03-09 02:48:54'),
(92, 369707, 'Tyrone', NULL, 'Delete Account', 'Deleted account ID: 2505619', '::1', '2026-03-08 07:23:16'),
(93, 859568034, 'tyrone alariao', NULL, 'Customer Login', 'Customer logged in successfully', '::1', '2026-03-11 08:47:14'),
(94, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-11 08:48:33'),
(95, 859568034, 'tyrone alariao', NULL, 'Customer Login', 'Customer logged in successfully', '::1', '2026-03-11 09:14:46'),
(96, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-11 09:15:26'),
(97, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 9367365 - Amount: ₱7,777.00', '::1', '2026-03-11 09:30:39'),
(98, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 9296576 - Amount: ₱5,000.00', '::1', '2026-03-11 09:47:38'),
(99, 0, 'tyronealariao06@gmail.com', NULL, 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-11 10:40:57'),
(100, 0, 'tyronealariao06@gmail.com', NULL, 'Failed Login', 'Failed login attempt - 2 attempt(s)', '::1', '2026-03-11 10:41:15'),
(101, 417803011, 'tyrone alariao', NULL, 'Customer Login', 'Customer logged in successfully', '::1', '2026-03-11 10:42:34'),
(102, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-11 10:43:49'),
(103, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 9605565 - Amount: ₱50,000.00', '::1', '2026-03-11 10:48:03'),
(104, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 6507135 - Amount: ₱4,000.00', '::1', '2026-03-12 11:03:05'),
(105, 0, 'tyronealariao06@gmail.com', NULL, 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-12 11:08:51'),
(106, 417803011, 'tyrone alariao', NULL, 'Customer Login', 'Customer logged in successfully', '::1', '2026-03-12 11:08:58'),
(107, 0, 'tyronealariao06@gmail.com', NULL, 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-12 11:09:14'),
(108, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-12 11:10:11'),
(109, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 7716164 - Amount: ₱50,000.00', '::1', '2026-03-12 11:18:10'),
(110, 417803011, 'tyrone alariao', NULL, 'Customer Login', 'Customer logged in successfully', '::1', '2026-03-09 19:40:10'),
(111, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-09 19:40:32'),
(112, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 7992160 - Amount: ₱4,000.00', '::1', '2026-03-09 20:30:25'),
(113, 369707, 'Tyrone', NULL, 'Record Payment', 'Recorded payment of ₱500.00 for account ID: 6507135', '::1', '2026-03-09 20:44:48'),
(114, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 3297854 - Amount: ₱20,000.00', '::1', '2026-03-09 21:10:42'),
(115, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 3968318 - Amount: ₱5,000.00', '::1', '2026-03-09 21:20:07'),
(116, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 7343036 - Amount: ₱40,000.00', '::1', '2026-03-09 21:25:15'),
(117, 893619828, 'tyrone alariao', NULL, 'Customer Login', 'Customer logged in successfully', '::1', '2026-03-09 21:41:36'),
(118, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-09 21:42:25'),
(119, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 6452229 - Amount: ₱2,000.00', '::1', '2026-03-09 21:42:54'),
(120, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-10 11:02:58'),
(121, 893619828, 'tyrone alariao', NULL, 'Customer Login', 'Customer logged in successfully', '::1', '2026-03-10 11:03:23'),
(122, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 4618634 - Amount: ₱30,000.00', '::1', '2026-03-10 11:27:33'),
(123, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 5214063 - Amount: ₱10,000.00', '::1', '2026-03-10 11:33:25'),
(124, 369707, 'Tyrone', NULL, 'Approve Loan', 'Approved loan account ID: 4111624 - Amount: ₱9,000.00', '::1', '2026-03-10 11:43:32'),
(125, 369707, 'Tyrone', NULL, 'Admin Logout', 'Admin logged out', '::1', '2026-03-10 13:11:00'),
(126, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-10 13:11:26'),
(127, 893619828, 'tyrone alariao', NULL, 'Customer Login', 'Customer logged in successfully', '::1', '2026-03-10 13:12:44'),
(128, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-10 21:07:39'),
(129, 893619828, 'tyrone alariao', NULL, 'Customer Login', 'Customer logged in successfully', '::1', '2026-03-10 21:13:42'),
(130, 369707, 'Tyrone', NULL, 'Add Customer', 'Created new customer: yro cala (ID: 904520296)', '::1', '2026-03-10 22:02:14'),
(131, 369707, 'Tyrone', NULL, 'Deactivate Customer', 'Deactivated customer ID: 893619828', '::1', '2026-03-10 22:51:03'),
(132, 369707, 'Tyrone', NULL, 'Activate Customer', 'Activated customer ID: 893619828', '::1', '2026-03-10 22:51:23'),
(133, 369707, 'Tyrone', NULL, 'Deactivate Customer', 'Deactivated customer ID: 893619828', '::1', '2026-03-11 00:18:39'),
(134, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-11 02:13:52'),
(135, 369707, 'Tyrone', NULL, 'Activate Customer', 'Activated customer ID: 893619828', '::1', '2026-03-11 02:39:04'),
(136, 369707, 'Tyrone', NULL, 'Deactivate Customer', 'Deactivated customer ID: 893619828', '::1', '2026-03-11 02:39:19'),
(137, 369707, 'Tyrone', NULL, 'Activate Customer', 'Activated customer ID: 893619828', '::1', '2026-03-11 02:39:33'),
(138, 369707, 'Tyrone', NULL, 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-12 03:52:48'),
(139, 893619828, 'tyrone alariao', NULL, 'Customer Login', 'Customer logged in successfully', '::1', '2026-03-12 03:52:57'),
(140, 369707, 'Tyrone', 'admin', 'Record Payment', 'Recorded payment of ₱4,000.00 for account ID: 4111624', '::1', '2026-03-12 17:49:42'),
(141, 369707, 'Tyrone', 'admin', 'Approve Loan', 'Approved loan account ID: 5501787 - Amount: ₱20,000.00', '::1', '2026-03-12 17:54:38'),
(142, 893619828, 'tyrone alariao', 'customer', 'Confirm Loan', 'Customer confirmed loan account ID: 5501787', '::1', '2026-03-12 17:55:01'),
(143, 893619828, 'tyrone alariao', 'customer', 'Customer Payment', 'Made payment of ₱5,300.00 for account ID: 5501787', '::1', '2026-03-12 20:28:19'),
(144, 893619828, 'tyrone alariao', 'customer', 'Customer Payment', 'Made payment of ₱5,300.00 for account ID: 5501787', '::1', '2026-03-12 20:29:29'),
(145, 369707, 'Tyrone', 'admin', 'Approve Loan', 'Approved loan account ID: 6814101 - Amount: ₱40,000.00', '::1', '2026-03-12 20:31:10'),
(146, 893619828, 'tyrone alariao', 'customer', 'Confirm Loan', 'Customer confirmed loan account ID: 6814101', '::1', '2026-03-12 20:31:26'),
(147, 893619828, 'tyrone alariao', 'customer', 'Customer Payment', 'Made payment of ₱10,000.00 for account ID: 6814101', '::1', '2026-03-12 20:31:42'),
(148, 893619828, 'tyrone alariao', 'customer', 'Customer Payment', 'Made payment of ₱10,000.00 for account ID: 6814101', '::1', '2026-03-12 20:47:49'),
(149, 893619828, 'tyrone alariao', 'customer', 'Customer Payment', 'Made payment of ₱10,000.00 for account ID: 6814101', '::1', '2026-03-12 20:52:29'),
(150, 893619828, 'tyrone alariao', 'customer', 'Customer Payment', 'Made payment of ₱10,000.00 for account ID: 6814101', '::1', '2026-03-12 20:53:46'),
(151, 369707, 'Tyrone', 'admin', 'Record Payment', 'Recorded payment of ₱600.00 for account ID: 6814101', '::1', '2026-03-12 20:54:48'),
(152, 893619828, 'tyrone alariao', 'customer', 'Customer Payment', 'Made payment of ₱10,600.00 for account ID: 6814101', '::1', '2026-03-12 20:55:33'),
(153, 369707, 'Tyrone', 'admin', 'Approve Loan', 'Approved loan account ID: 5154873 - Amount: ₱50,000.00', '::1', '2026-03-12 23:26:08'),
(154, 893619828, 'tyrone alariao', 'customer', 'Confirm Loan', 'Customer confirmed loan account ID: 5154873', '::1', '2026-03-12 23:26:27'),
(155, 369707, 'Tyrone', 'admin', 'Deactivate Customer', 'Deactivated customer ID: 893619828', '::1', '2026-03-12 23:26:56'),
(156, 369707, 'Tyrone', 'admin', 'Activate Customer', 'Activated customer ID: 893619828', '::1', '2026-03-12 23:29:43'),
(157, 893619828, 'tyrone alariao', 'customer', 'Customer Payment', 'Made payment of ₱62,000.00 for account ID: 5154873', '::1', '2026-03-13 00:07:20'),
(158, 893619828, 'tyrone alariao', 'customer', 'Customer Payment', 'Made payment of ₱62,000.00 for account ID: 5154873', '::1', '2026-03-13 00:07:32'),
(159, 0, 'tyronealariao06@gmail.com', 'admin', 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-13 09:38:34'),
(160, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-13 09:38:46'),
(161, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully', '::1', '2026-03-13 09:40:40'),
(162, 369707, 'Tyrone', 'admin', 'Approve Loan', 'Approved loan account ID: 4178854 - Amount: ₱20,000.00', '::1', '2026-03-13 09:44:46'),
(163, 0, 'tyronealariao06@gmail.com', 'admin', 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-13 09:48:23'),
(164, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-13 09:48:54'),
(165, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-13 10:47:36'),
(166, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully', '::1', '2026-03-13 10:48:16'),
(167, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-13 10:48:21'),
(168, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-13 10:48:38'),
(169, 0, 'customer', 'customer', 'Customer Logout', 'Customer logged out', '::1', '2026-03-13 10:48:43'),
(170, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully', '::1', '2026-03-13 10:50:07'),
(171, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-13 10:52:08'),
(172, 893619828, 'tyrone alariao', 'customer', 'Customer Logout', 'Customer logged out', '::1', '2026-03-13 11:16:34'),
(173, 0, 'admin', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-13 11:16:34'),
(174, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-13 11:20:33'),
(175, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully', '::1', '2026-03-13 11:20:49'),
(176, 893619828, 'tyrone alariao', 'customer', 'Customer Logout', 'Customer logged out', '::1', '2026-03-13 11:36:05'),
(177, 0, 'admin', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-13 11:39:26'),
(178, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-13 12:24:39'),
(179, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-13 12:29:53'),
(180, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-13 16:08:42'),
(181, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully', '::1', '2026-03-13 16:09:20'),
(182, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-13 16:09:33'),
(183, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-13 16:14:56'),
(184, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully (OTP verified)', '::1', '2026-03-13 16:15:44'),
(185, 893619828, 'tyrone alariao', 'customer', 'Customer Logout', 'Customer logged out', '::1', '2026-03-13 16:22:15'),
(186, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-13 16:23:48'),
(187, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully (OTP verified)', '::1', '2026-03-13 16:26:08'),
(188, 893619828, 'tyrone alariao', 'customer', 'Customer Logout', 'Customer logged out', '::1', '2026-03-13 16:32:26'),
(189, 0, 'admin', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-13 16:32:49'),
(190, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-13 20:00:38'),
(191, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully (OTP verified)', '::1', '2026-03-13 20:01:59'),
(192, 369707, 'Tyrone', 'admin', 'Approve Loan', 'Approved loan account ID: 3448885 - Amount: ₱20,000.00', '::1', '2026-03-13 20:17:02'),
(193, 893619828, 'tyrone alariao', 'customer', 'Confirm Loan', 'Customer confirmed loan account ID: 3448885', '::1', '2026-03-13 20:17:14'),
(194, 893619828, 'tyrone alariao', 'customer', 'Customer Logout', 'Customer logged out', '::1', '2026-03-13 20:18:10'),
(195, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-13 20:25:05'),
(196, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-13 20:25:15'),
(197, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully (OTP verified)', '::1', '2026-03-13 20:25:51'),
(198, 893619828, 'tyrone alariao', 'customer', 'Customer Payment', 'Made payment of ₱20,400.00 for account ID: 3448885', '::1', '2026-03-13 20:26:13'),
(199, 893619828, 'tyrone alariao', 'customer', 'Customer Payment', 'Made payment of ₱20,400.00 for account ID: 3448885', '::1', '2026-03-13 20:26:19'),
(200, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-13 20:27:50'),
(201, 369707, 'Tyrone', 'admin', 'Reject Loan', 'Rejected loan account ID: 3587450 - Reason: sorry only we con offer is 500\r\n', '::1', '2026-03-13 20:28:18'),
(202, 893619828, 'tyrone alariao', 'customer', 'Customer Logout', 'Customer logged out', '::1', '2026-03-13 20:30:32'),
(203, 0, 'admin', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-13 20:32:42'),
(204, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-13 20:34:32'),
(205, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-13 20:35:11'),
(206, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully (OTP verified)', '::1', '2026-03-13 20:35:46'),
(207, 369707, 'Tyrone', 'admin', 'Reject Loan', 'Rejected loan account ID: 6190708 - Reason: yoko\r\n', '::1', '2026-03-13 20:40:54'),
(208, 369707, 'Tyrone', 'admin', 'Deactivate Customer', 'Deactivated customer ID: 893619828', '::1', '2026-03-13 20:45:31'),
(209, 369707, 'Tyrone', 'admin', 'Activate Customer', 'Activated customer ID: 893619828', '::1', '2026-03-13 20:47:04'),
(210, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-13 20:53:07'),
(211, 0, 'customer', 'customer', 'Customer Logout', 'Customer logged out', '::1', '2026-03-13 20:53:13'),
(212, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-14 22:34:23'),
(213, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully (OTP verified)', '::1', '2026-03-14 22:35:57'),
(214, 0, 'tyronealariao06@gmail.com', 'admin', 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-15 19:21:39'),
(215, 0, 'tyronealariao06@gmail.com', 'admin', 'Failed Login', 'Failed login attempt - 2 attempt(s)', '::1', '2026-03-15 19:21:51'),
(216, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-15 19:22:40'),
(217, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-15 19:30:28'),
(218, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully (OTP verified)', '::1', '2026-03-15 19:32:38'),
(219, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully (OTP verified)', '::1', '2026-03-15 20:53:08'),
(220, 0, 'admin', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-15 20:53:49'),
(221, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-15 20:54:45'),
(222, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully (OTP verified)', '::1', '2026-03-15 20:55:51'),
(223, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-15 21:01:27'),
(224, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-15 21:01:27'),
(225, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-15 21:01:56'),
(226, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully (OTP verified)', '::1', '2026-03-15 21:03:14'),
(227, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-15 21:29:43'),
(228, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully (OTP verified)', '::1', '2026-03-15 21:30:26'),
(229, 369707, 'Tyrone', 'admin', 'Approve Loan', 'Approved loan account ID: 1146269 - Amount: ₱20,000.00', '::1', '2026-03-15 21:34:09'),
(230, 893619828, 'tyrone alariao', 'customer', 'Customer Logout', 'Customer logged out', '::1', '2026-03-15 21:43:25'),
(231, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully (OTP verified)', '::1', '2026-03-15 21:52:11'),
(232, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-15 21:52:34'),
(233, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-15 21:55:23'),
(234, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully (OTP verified)', '::1', '2026-03-15 21:57:51'),
(235, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-15 22:04:54'),
(236, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-15 22:07:52'),
(237, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully (OTP verified)', '::1', '2026-03-15 22:08:29'),
(238, 893619828, 'tyrone alariao', 'customer', 'Customer Logout', 'Customer logged out', '::1', '2026-03-15 22:13:50'),
(239, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-15 22:14:56'),
(240, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-15 22:22:51'),
(241, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-15 22:42:16'),
(242, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully (OTP verified)', '::1', '2026-03-15 22:58:17'),
(243, 893619828, 'tyrone alariao', 'customer', 'Customer Login', 'Customer logged in successfully (OTP verified)', '::1', '2026-03-15 22:58:42'),
(244, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-15 22:59:50'),
(245, 893619828, 'tyrone alariao', 'customer', 'Confirm Loan', 'Customer confirmed loan account ID: 1146269', '::1', '2026-03-15 22:59:56'),
(246, 893619828, 'tyrone alariao', 'customer', 'Customer Payment', 'Made payment of ₱5,000.00 for account ID: 1146269', '::1', '2026-03-15 23:00:33'),
(247, 893619828, 'tyrone alariao', 'customer', 'Customer Logout', 'Customer logged out', '::1', '2026-03-16 06:12:09'),
(248, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-16 06:12:45'),
(249, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-16 18:01:22'),
(250, 893619828, 'tyrone alariao', 'customer', 'User Login', 'User logged in successfully (OTP verified)', '::1', '2026-03-16 18:02:26'),
(251, 893619828, 'tyrone alariao', 'customer', 'User Logout', 'User logged out', '::1', '2026-03-16 18:33:16'),
(252, 0, 'admin', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-16 18:33:16'),
(253, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-16 22:39:37'),
(254, 893619828, 'tyrone alariao', 'customer', 'User Login', 'User logged in successfully (OTP verified)', '::1', '2026-03-16 22:41:33'),
(255, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-16 22:41:49'),
(256, 0, 'tyronealariao06@gmail.com', 'admin', 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-16 22:41:58'),
(257, 893619828, 'tyrone alariao', 'customer', 'User Login', 'User logged in successfully (OTP verified)', '::1', '2026-03-16 22:42:51'),
(258, 893619828, 'tyrone alariao', 'customer', 'User Logout', 'User logged out', '::1', '2026-03-16 22:44:03'),
(259, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 03:27:34'),
(260, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 03:55:31'),
(261, 893619828, 'tyrone alariao', 'customer', 'User Login', 'User logged in successfully (OTP verified)', '::1', '2026-03-17 03:57:54'),
(262, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-17 04:03:09'),
(263, 893619828, 'tyrone alariao', 'customer', 'User Logout', 'User logged out', '::1', '2026-03-17 04:03:10'),
(264, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 04:57:10'),
(265, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 06:18:17'),
(266, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 06:26:05'),
(267, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 06:55:03'),
(268, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 07:26:39'),
(269, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 07:43:06'),
(270, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 07:59:28'),
(271, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 16:37:19'),
(272, 893619828, 'tyrone alariao', 'customer', 'User Login', 'User logged in successfully (OTP verified)', '::1', '2026-03-17 16:46:15'),
(273, 893619828, 'tyrone alariao', 'customer', 'User Payment', 'Made payment of ₱16,200.00 for account ID: 1146269', '::1', '2026-03-17 16:54:47'),
(274, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-17 17:13:26'),
(275, 0, 'customer', 'customer', 'User Logout', 'User logged out', '::1', '2026-03-17 17:16:22'),
(276, 0, 'tyronealariao06@gmail.com', 'admin', 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-17 17:42:15'),
(277, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 17:42:40'),
(278, 893619828, 'tyrone alariao', 'customer', 'User Login', 'User logged in successfully (OTP verified)', '::1', '2026-03-17 17:57:56'),
(279, 369707, 'Tyrone', 'admin', 'Approve Loan', 'Approved loan account ID: 6461143 - Amount: ₱20,000.00', '::1', '2026-03-17 17:58:33'),
(280, 893619828, 'tyrone alariao', 'customer', 'Confirm Loan', 'User confirmed loan account ID: 6461143', '::1', '2026-03-17 17:58:43'),
(281, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-17 18:05:33'),
(282, 0, 'customer', 'customer', 'User Logout', 'User logged out', '::1', '2026-03-17 18:05:40'),
(283, 0, 'tyronealariao06@gmail.com', 'admin', 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-17 19:19:53'),
(284, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 19:20:23'),
(285, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-18 00:23:58'),
(286, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-17 04:41:05'),
(287, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 04:41:36'),
(288, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-17 04:51:15'),
(289, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 04:51:44'),
(290, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-17 04:56:48'),
(291, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 04:57:19'),
(292, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-17 04:59:49'),
(293, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 05:00:22'),
(294, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-17 05:04:24'),
(295, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 05:04:54'),
(296, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-17 05:06:54'),
(297, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 05:07:23'),
(298, 893619828, 'tyrone alariao', 'customer', 'User Login', 'User logged in successfully (OTP verified)', '::1', '2026-03-17 05:08:51'),
(299, 893619828, 'tyrone alariao', 'customer', 'User Logout', 'User logged out', '::1', '2026-03-17 05:14:15'),
(300, 893619828, 'tyrone alariao', 'customer', 'User Login', 'User logged in successfully (OTP verified)', '::1', '2026-03-17 05:14:48'),
(301, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 05:15:20'),
(302, 893619828, 'tyrone alariao', 'customer', 'User Logout', 'User logged out', '::1', '2026-03-17 05:17:48'),
(303, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 05:18:53'),
(304, 893619828, 'tyrone alariao', 'customer', 'User Login', 'User logged in successfully (OTP verified)', '::1', '2026-03-16 09:21:54'),
(305, 893619828, 'tyrone alariao', 'customer', 'User Logout', 'User logged out', '::1', '2026-03-16 09:27:22'),
(306, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-16 09:29:04'),
(307, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-16 10:04:29'),
(308, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-16 12:13:58'),
(309, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-16 18:14:58'),
(310, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-16 18:23:41'),
(311, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 01:14:43'),
(312, 0, 'tyronealariao06@gmail.com', 'customer', 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-17 02:00:53'),
(313, 893619828, 'tyrone alariao', 'customer', 'User Login', 'User logged in successfully (OTP verified)', '::1', '2026-03-17 02:01:52'),
(314, 893619828, 'tyrone alariao', 'customer', 'User Payment', 'Made payment of ₱16,200.00 for account ID: 6461143', '::1', '2026-03-17 02:03:12'),
(315, 369707, 'Tyrone', 'admin', 'Approve Loan', 'Approved loan account ID: 7270237 - Amount: ₱20,000.00', '::1', '2026-03-17 02:10:24'),
(316, 893619828, 'tyrone alariao', 'customer', 'Confirm Loan', 'User confirmed loan account ID: 7270237', '::1', '2026-03-17 02:24:49'),
(317, 893619828, 'tyrone alariao', 'customer', 'User Payment', 'Made payment of ₱5,000.00 for account ID: 7270237', '::1', '2026-03-17 02:29:05'),
(318, 893619828, 'tyrone alariao', 'customer', 'User Logout', 'User logged out', '::1', '2026-03-17 02:35:21'),
(319, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-17 02:42:39'),
(320, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-19 01:43:29'),
(321, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-19 01:45:10'),
(322, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-19 01:46:38'),
(323, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-19 01:52:07'),
(324, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-19 01:55:16'),
(325, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-19 01:55:29'),
(326, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-19 01:55:29'),
(327, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-19 02:06:02'),
(328, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-19 02:06:46'),
(329, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-19 02:14:24'),
(330, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-19 02:16:26'),
(331, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (QR code verified)', '::1', '2026-03-19 03:10:37'),
(332, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (QR code verified)', '::1', '2026-03-19 03:13:38'),
(333, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-19 03:28:39'),
(334, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-19 03:30:29'),
(335, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-19 03:30:29'),
(336, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-21 16:18:42'),
(337, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-26 07:10:56'),
(338, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-26 09:24:25'),
(339, 0, 'tyronealariao06@gmail.com', 'admin', 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-26 09:26:02'),
(340, 0, 'tyronealariao06@gmail.com', 'admin', 'Failed Login', 'Failed login attempt - 2 attempt(s)', '::1', '2026-03-26 09:26:12'),
(341, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-26 09:27:02'),
(342, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-26 09:37:34'),
(343, 0, 'tyronealariao06@gmail.com', 'admin', 'Failed Login', 'Failed login attempt - 1 attempt(s)', '::1', '2026-03-26 09:38:34'),
(344, 0, 'tyronealariao06@gmail.com', 'admin', 'Failed Login', 'Failed login attempt - 2 attempt(s)', '::1', '2026-03-26 09:48:24'),
(345, 0, 'tyronealariao06@gmail.com', 'admin', 'Failed Login', 'Failed login attempt - 3 attempt(s)', '::1', '2026-03-26 09:57:50'),
(346, 92414, 'JOA', 'admin', 'Admin Registration', 'New admin account registered: JOA', '::1', '2026-03-26 09:58:26'),
(347, 11851, 'JOAQUIN', 'admin', 'Admin Registration', 'New admin account registered: JOAQUIN', '::1', '2026-03-26 10:01:30'),
(348, 11851, 'JOAQUIN', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-26 10:02:01'),
(349, 11851, 'JOAQUIN', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-27 06:35:35'),
(350, 11851, 'JOAQUIN', 'admin', 'Add Customer', 'Created new customer: Joaquin Mayrina (ID: 980614528)', '::1', '2026-03-27 06:42:17'),
(351, 11851, 'JOAQUIN', 'admin', 'Create Account', 'Created new loan account ID: 980614528 for customer: 980614528', '::1', '2026-03-27 06:46:30'),
(352, 11851, 'JOAQUIN', 'admin', 'Create Account', 'Created new loan account ID: 5777 for customer: 980614528', '::1', '2026-03-27 06:48:48'),
(353, 11851, 'JOAQUIN', 'admin', 'Approve Loan', 'Approved loan account ID: 5777 - Amount: ₱20,000.00', '::1', '2026-03-27 06:49:14'),
(354, 11851, 'JOAQUIN', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-27 06:56:07'),
(355, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-27 07:05:36'),
(356, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-27 07:30:35'),
(357, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-27 07:34:45'),
(358, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-27 07:36:18'),
(359, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-27 07:39:07'),
(360, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-27 07:42:01'),
(361, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-27 07:42:32'),
(362, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-27 07:45:24'),
(363, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-27 07:51:08'),
(364, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-27 08:45:36'),
(365, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-27 08:46:05'),
(366, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-27 08:56:52'),
(367, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-27 08:58:09'),
(368, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-27 09:04:00'),
(369, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-27 09:18:23'),
(370, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-27 09:20:23'),
(371, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-27 09:22:18'),
(372, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-27 09:23:14'),
(373, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-27 09:24:51'),
(374, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-27 09:27:41'),
(375, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-27 09:29:52'),
(376, 369707, 'Tyrone', 'admin', 'Admin Login', 'Admin logged in successfully (OTP verified)', '::1', '2026-03-27 09:30:23'),
(377, 369707, 'Tyrone', 'admin', 'Admin Logout', 'Admin logged out', '::1', '2026-03-27 09:31:17');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_number` int(10) NOT NULL,
  `customer_type` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `surname` varchar(50) NOT NULL,
  `gender` char(1) NOT NULL,
  `date_of_birth` date NOT NULL,
  `nationality` varchar(50) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_id` int(10) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `full_address` varchar(255) DEFAULT NULL,
  `municipality` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `deactivated_date` date DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_number` varchar(20) DEFAULT NULL,
  `emergency_contact_relationship` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `gov_id_number` varchar(50) DEFAULT '',
  `qr_code` varchar(64) DEFAULT NULL,
  `qr_code_enabled` tinyint(1) DEFAULT 0,
  `gov_id_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_number`, `customer_type`, `first_name`, `middle_name`, `surname`, `gender`, `date_of_birth`, `nationality`, `registration_date`, `user_id`, `email`, `phone`, `password`, `region`, `city`, `barangay`, `zip_code`, `full_address`, `municipality`, `is_active`, `deactivated_date`, `last_login`, `emergency_contact_name`, `emergency_contact_number`, `emergency_contact_relationship`, `status`, `gov_id_number`, `qr_code`, `qr_code_enabled`, `gov_id_type`) VALUES
(893619828, 12, 'tyrone', NULL, 'alariao', 'M', '2008-03-09', 'Filipino', '2026-03-17 02:01:52', 369707, 'tyronealariao06@gmail.com', '09919094456', 'TyroneAlariao05!', 'Region VI', 'Bacolod', 'Alangilan', '6100', 'basta', NULL, 1, '0000-00-00', '2026-03-17 10:01:52', NULL, NULL, NULL, 1, '', NULL, 0, NULL),
(904520296, 2, 'yro', 'lili', 'cala', 'M', '2022-03-12', 'Filipino', '2026-03-10 22:02:14', 369707, 'yro@gmail.com', '09929025765', 'AlariaoTyrone05!', 'Region XII', 'Cotabato', 'Tulunan', '3333', 'wow', NULL, 1, NULL, NULL, 'Tyrone Alariao', '09919094456', 'Child', 1, '', NULL, 0, NULL),
(980614528, 2, 'Joaquin', 'T', 'Mayrina', 'M', '1999-07-24', 'Filipino', '2026-03-27 06:42:17', 11851, 'Joaquin@gmail.com', '09919094456', 'JoaquinMayrina00!', 'Region VI', 'Iloilo', 'Batuan', '6100', 'basta', NULL, 1, NULL, NULL, 'Tyrone Alariao', '09919094456', 'Parent', 1, '09887656754', NULL, 0, 'Driver\'s License');

-- --------------------------------------------------------

--
-- Table structure for table `customers_type`
--

CREATE TABLE `customers_type` (
  `customer_type_number` int(10) NOT NULL,
  `customer_type_name` varchar(50) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `customer_type_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers_type`
--

INSERT INTO `customers_type` (`customer_type_number`, `customer_type_name`, `registration_date`, `customer_type_description`) VALUES
(1, 'Student', '2026-03-10 22:02:13', 'Student customer'),
(2, 'Employee', '2026-03-10 22:02:13', 'Employed customer'),
(3, 'Self-Employed', '2026-03-10 22:02:13', 'Self-employed customer'),
(4, 'Business Owner', '2026-03-10 22:02:13', 'Business owner customer'),
(5, 'OFW', '2026-03-10 22:02:13', 'Overseas Filipino Worker'),
(6, 'Senior Citizen', '2026-03-10 22:02:13', 'Senior citizen customer'),
(7, 'Pensioner', '2026-03-10 22:02:13', 'Pensioner customer'),
(8, 'Unemployed', '2026-03-10 22:02:13', 'Unemployed customer'),
(12, 'personal', '2026-02-26 14:16:16', NULL),
(13456, 'VIP', '2026-02-26 06:29:54', NULL),
(123456, 'regular', '2026-02-25 15:45:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `loan_notifications`
--

CREATE TABLE `loan_notifications` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `notification_type` varchar(20) DEFAULT 'reminder',
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_notifications`
--

INSERT INTO `loan_notifications` (`id`, `customer_id`, `account_number`, `message`, `notification_type`, `is_read`, `created_at`) VALUES
(1, 859568034, '8846340', 'This is a friendly reminder that your loan payment is due.\n\nAccount: 8846340\nDue Date: 2026-04-10\nMonthly Payment: ₱9,833.33\nRemaining Balance: ₱110,000.00\n\nPlease make your payment on time to avoid penalties.\n\nThank you!', 'reminder', 1, '2026-03-10 20:49:15'),
(2, 417803011, '6507135', 'This is a friendly reminder that your loan payment is due.\n\nAccount: 6507135\nDue Date: 2026-04-11\nMonthly Payment: ₱393.33\nRemaining Balance: ₱4,720.00\n\nPlease make your payment on time to avoid penalties.\n\nThank you!', 'reminder', 1, '2026-03-11 23:13:44'),
(3, 417803011, '9605565', 'This is a friendly reminder that your loan payment is due.\n\nAccount: 9605565\nDue Date: 2026-04-10\nMonthly Payment: ₱17,416.67\nRemaining Balance: ₱52,250.00\n\nPlease make your payment on time to avoid penalties.\n\nThank you!', 'reminder', 1, '2026-03-09 08:52:04'),
(4, 893619828, '6452229', 'This is a friendly reminder that your loan payment is due.\n\nAccount: 6452229\nDue Date: 2026-04-09\nMonthly Payment: ₱696.67\nRemaining Balance: ₱1,090.00\n\nPlease make your payment on time to avoid penalties.\n\nThank you!', 'reminder', 1, '2026-03-09 23:25:19'),
(5, 893619828, '4111624', 'This is a friendly reminder that your loan payment is due.\n\nAccount: 4111624\nDue Date: 2026-04-10\nMonthly Payment: ₱3,135.00\nRemaining Balance: ₱8,405.00\n\nPlease make your payment on time to avoid penalties.\n\nThank you!', 'reminder', 1, '2026-03-10 10:52:04'),
(6, 893619828, '4111624', 'This is a friendly reminder that your loan payment is due.\n\nAccount: 4111624\nDue Date: 2026-04-10\nMonthly Payment: ₱3,135.00\nRemaining Balance: ₱8,405.00\n\nPlease make your payment on time to avoid penalties.\n\nThank you!', 'reminder', 1, '2026-03-10 10:59:45'),
(7, 893619828, '5154873', 'This is a friendly reminder that your loan payment is due.\n\nAccount: 5154873\nDue Date: 2026-04-11\nMonthly Payment: ₱10,333.33\nRemaining Balance: ₱62,000.00\n\nPlease make your payment on time to avoid penalties.\n\nThank you!', 'reminder', 1, '2026-03-11 22:26:34'),
(8, 893619828, '6461143', 'This is a friendly reminder that your loan payment is due.\n\nAccount: 6461143\nDue Date: 2026-04-16\nMonthly Payment: ₱7,066.67\nRemaining Balance: ₱21,200.00\n\nPlease make your payment on time to avoid penalties.\n\nThank you!', 'reminder', 1, '2026-03-16 09:58:52');

-- --------------------------------------------------------

--
-- Table structure for table `loan_requirements`
--

CREATE TABLE `loan_requirements` (
  `id` int(11) NOT NULL,
  `account_number` int(11) NOT NULL,
  `customer_number` int(11) NOT NULL,
  `requirement_type` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_requirements`
--

INSERT INTO `loan_requirements` (`id`, `account_number`, `customer_number`, `requirement_type`, `file_path`, `status`, `created_at`) VALUES
(1, 7019282, 502925550, 'Government ID', 'uploads/requirements/7019282_government_id_1772102686.jpg', 'approved', '2026-02-26 10:44:46'),
(2, 7019282, 502925550, 'Proof of Income', 'uploads/requirements/7019282_proof_of_income_1772102686.jpg', 'approved', '2026-02-26 10:44:46'),
(3, 7019282, 502925550, 'Bank Statement', 'uploads/requirements/7019282_bank_statement_1772102686.jpg', 'approved', '2026-02-26 10:44:46'),
(4, 6338449, 502925550, 'Government ID', 'uploads/requirements/6338449_government_id_1772102973.jpg', 'rejected', '2026-02-26 10:49:33'),
(5, 6338449, 502925550, 'Proof of Income', 'uploads/requirements/6338449_proof_of_income_1772102973.jpg', 'rejected', '2026-02-26 10:49:33'),
(6, 6338449, 502925550, 'Bank Statement', 'uploads/requirements/6338449_bank_statement_1772102973.jpg', 'rejected', '2026-02-26 10:49:33'),
(7, 6960187, 502925550, 'Government ID', 'uploads/requirements/6960187_government_id_1772103372.jpg', 'approved', '2026-02-26 10:56:12'),
(8, 6960187, 502925550, 'Proof of Income', 'uploads/requirements/6960187_proof_of_income_1772103372.jpg', 'approved', '2026-02-26 10:56:12'),
(9, 6960187, 502925550, 'Bank Statement', 'uploads/requirements/6960187_bank_statement_1772103372.jpg', 'approved', '2026-02-26 10:56:12'),
(10, 8424455, 502925550, 'Government ID', 'uploads/requirements/8424455_government_id_1772104155.jpg', 'rejected', '2026-02-26 11:09:15'),
(11, 8424455, 502925550, 'Proof of Income', 'uploads/requirements/8424455_proof_of_income_1772104155.jpg', 'rejected', '2026-02-26 11:09:15'),
(12, 8424455, 502925550, 'Bank Statement', 'uploads/requirements/8424455_bank_statement_1772104155.jpg', 'rejected', '2026-02-26 11:09:15'),
(13, 9384505, 502925550, 'Government ID', 'uploads/requirements/9384505_government_id_1772106705.jpg', 'approved', '2026-02-26 11:51:45'),
(14, 9384505, 502925550, 'Proof of Income', 'uploads/requirements/9384505_proof_of_income_1772106705.jpg', 'approved', '2026-02-26 11:51:45'),
(15, 9384505, 502925550, 'Bank Statement', 'uploads/requirements/9384505_bank_statement_1772106705.jpg', 'approved', '2026-02-26 11:51:45'),
(16, 1235924, 502925550, 'Government ID', 'uploads/requirements/1235924_government_id_1772107079.jpg', 'approved', '2026-02-26 11:57:59'),
(17, 1235924, 502925550, 'Proof of Income', 'uploads/requirements/1235924_proof_of_income_1772107079.jpg', 'approved', '2026-02-26 11:57:59'),
(18, 1235924, 502925550, 'Bank Statement', 'uploads/requirements/1235924_bank_statement_1772107079.jpg', 'approved', '2026-02-26 11:57:59'),
(19, 9352095, 502925550, 'Government ID', 'uploads/requirements/9352095_government_id_1772107733.jpg', 'approved', '2026-02-26 12:08:53'),
(20, 9352095, 502925550, 'Proof of Income', 'uploads/requirements/9352095_proof_of_income_1772107733.jpg', 'approved', '2026-02-26 12:08:53'),
(21, 9352095, 502925550, 'Bank Statement', 'uploads/requirements/9352095_bank_statement_1772107733.jpg', 'approved', '2026-02-26 12:08:53'),
(22, 6148328, 502925550, 'Government ID', 'uploads/requirements/6148328_government_id_1772107931.jpg', 'approved', '2026-02-26 12:12:11'),
(23, 6148328, 502925550, 'Proof of Income', 'uploads/requirements/6148328_proof_of_income_1772107931.jpg', 'approved', '2026-02-26 12:12:11'),
(24, 6148328, 502925550, 'Bank Statement', 'uploads/requirements/6148328_bank_statement_1772107931.jpg', 'approved', '2026-02-26 12:12:11'),
(25, 3040009, 809934416, 'Government ID', 'uploads/requirements/3040009_government_id_1772116491.jpg', 'approved', '2026-02-26 14:34:51'),
(26, 3040009, 809934416, 'Proof of Income', 'uploads/requirements/3040009_proof_of_income_1772116491.jpg', 'approved', '2026-02-26 14:34:51'),
(27, 3040009, 809934416, 'Bank Statement', 'uploads/requirements/3040009_bank_statement_1772116491.jpg', 'approved', '2026-02-26 14:34:51'),
(28, 3090693, 337498586, 'Government ID', 'uploads/requirements/3090693_government_id_1772117036.jpg', 'approved', '2026-02-26 14:43:56'),
(29, 3090693, 337498586, 'Proof of Income', 'uploads/requirements/3090693_proof_of_income_1772117036.jpg', 'approved', '2026-02-26 14:43:56'),
(30, 3090693, 337498586, 'Bank Statement', 'uploads/requirements/3090693_bank_statement_1772117036.jpg', 'approved', '2026-02-26 14:43:56'),
(31, 6302925, 337498586, 'Government ID', 'uploads/requirements/6302925_government_id_1772140397.jpg', 'approved', '2026-02-26 21:13:17'),
(32, 6302925, 337498586, 'Proof of Income', 'uploads/requirements/6302925_proof_of_income_1772140397.jpg', 'approved', '2026-02-26 21:13:17'),
(33, 6302925, 337498586, 'Bank Statement', 'uploads/requirements/6302925_bank_statement_1772140397.jpg', 'approved', '2026-02-26 21:13:17'),
(34, 4235923, 337498586, 'Government ID', 'uploads/requirements/4235923_government_id_1772140730.jpg', 'approved', '2026-02-26 21:18:50'),
(35, 4235923, 337498586, 'Proof of Income', 'uploads/requirements/4235923_proof_of_income_1772140730.jpg', 'approved', '2026-02-26 21:18:50'),
(36, 4235923, 337498586, 'Bank Statement', 'uploads/requirements/4235923_bank_statement_1772140730.jpg', 'approved', '2026-02-26 21:18:50'),
(37, 6398124, 337498586, 'Government ID', 'uploads/requirements/6398124_government_id_1772142772.jpg', 'approved', '2026-02-26 21:52:52'),
(38, 6398124, 337498586, 'Proof of Income', 'uploads/requirements/6398124_proof_of_income_1772142772.jpg', 'approved', '2026-02-26 21:52:52'),
(39, 6398124, 337498586, 'Bank Statement', 'uploads/requirements/6398124_bank_statement_1772142772.jpg', 'approved', '2026-02-26 21:52:52'),
(40, 5663214, 337498586, 'Government ID', 'uploads/requirements/5663214_government_id_1772145345.jpg', 'approved', '2026-02-26 22:35:45'),
(41, 5663214, 337498586, 'Proof of Income', 'uploads/requirements/5663214_proof_of_income_1772145345.jpg', 'approved', '2026-02-26 22:35:45'),
(42, 5663214, 337498586, 'Bank Statement', 'uploads/requirements/5663214_bank_statement_1772145345.jpg', 'approved', '2026-02-26 22:35:45'),
(43, 5939277, 337498586, 'Government ID', 'uploads/requirements/5939277_government_id_1772175431.jpg', 'approved', '2026-02-27 06:57:11'),
(44, 5939277, 337498586, 'Proof of Income', 'uploads/requirements/5939277_proof_of_income_1772175431.jpg', 'approved', '2026-02-27 06:57:11'),
(45, 5939277, 337498586, 'Bank Statement', 'uploads/requirements/5939277_bank_statement_1772175431.jpg', 'approved', '2026-02-27 06:57:11'),
(46, 3228180, 337498586, 'Government ID', 'uploads/requirements/3228180_government_id_1772251629.jpg', 'rejected', '2026-02-28 04:07:09'),
(47, 3228180, 337498586, 'Proof of Income', 'uploads/requirements/3228180_proof_of_income_1772251629.jpg', 'rejected', '2026-02-28 04:07:09'),
(48, 3228180, 337498586, 'Bank Statement', 'uploads/requirements/3228180_bank_statement_1772251629.jpg', 'rejected', '2026-02-28 04:07:09'),
(49, 1384765, 201195046, 'Government ID', 'uploads/requirements/1384765_government_id_1772268055.jpg', 'approved', '2026-02-28 08:40:55'),
(50, 1384765, 201195046, 'Proof of Income', 'uploads/requirements/1384765_proof_of_income_1772268055.jpg', 'approved', '2026-02-28 08:40:55'),
(51, 1384765, 201195046, 'Bank Statement', 'uploads/requirements/1384765_bank_statement_1772268055.jpg', 'approved', '2026-02-28 08:40:55'),
(52, 2767280, 809934416, 'Government ID', 'uploads/requirements/2767280_government_id_1772268943.jpg', 'approved', '2026-02-28 08:55:43'),
(53, 2767280, 809934416, 'Proof of Income', 'uploads/requirements/2767280_proof_of_income_1772268943.jpg', 'approved', '2026-02-28 08:55:43'),
(54, 2767280, 809934416, 'Bank Statement', 'uploads/requirements/2767280_bank_statement_1772268943.jpg', 'approved', '2026-02-28 08:55:43'),
(55, 1075043, 809934416, 'Government ID', 'uploads/requirements/1075043_government_id_1772316317.jpg', 'approved', '2026-02-28 22:05:17'),
(56, 1075043, 809934416, 'Proof of Income', 'uploads/requirements/1075043_proof_of_income_1772316317.jpg', 'approved', '2026-02-28 22:05:17'),
(57, 1075043, 809934416, 'Bank Statement', 'uploads/requirements/1075043_bank_statement_1772316317.jpg', 'approved', '2026-02-28 22:05:17'),
(58, 2505619, 859568034, 'Government ID', 'uploads/requirements/2505619_government_id_1772519571.jpg', 'approved', '2026-03-03 06:32:51'),
(59, 2505619, 859568034, 'Proof of Income', 'uploads/requirements/2505619_proof_of_income_1772519571.jpg', 'approved', '2026-03-03 06:32:51'),
(60, 2505619, 859568034, 'Bank Statement', 'uploads/requirements/2505619_bank_statement_1772519571.jpg', 'approved', '2026-03-03 06:32:51'),
(61, 1031425, 859568034, 'Government ID', 'uploads/requirements/1031425_government_id_1773017794.pdf', 'rejected', '2026-03-09 00:56:34'),
(62, 1031425, 859568034, 'Proof of Income', 'uploads/requirements/1031425_proof_of_income_1773017794.jpg', 'rejected', '2026-03-09 00:56:34'),
(63, 1031425, 859568034, 'Bank Statement', 'uploads/requirements/1031425_bank_statement_1773017794.pdf', 'rejected', '2026-03-09 00:56:34'),
(64, 6562122, 859568034, 'Government ID', 'uploads/requirements/6562122_government_id_1773018593.jpg', 'approved', '2026-03-09 01:09:53'),
(65, 6562122, 859568034, 'Proof of Income', 'uploads/requirements/6562122_proof_of_income_1773018593.jpg', 'approved', '2026-03-09 01:09:53'),
(66, 6562122, 859568034, 'Bank Statement', 'uploads/requirements/6562122_bank_statement_1773018593.jpg', 'approved', '2026-03-09 01:09:53'),
(67, 1320210, 859568034, 'Government ID', 'uploads/requirements/1320210_government_id_1773019932.pdf', 'approved', '2026-03-09 01:32:12'),
(68, 1320210, 859568034, 'Proof of Income', 'uploads/requirements/1320210_proof_of_income_1773019932.pdf', 'approved', '2026-03-09 01:32:12'),
(69, 1320210, 859568034, 'Bank Statement', 'uploads/requirements/1320210_bank_statement_1773019932.pdf', 'approved', '2026-03-09 01:32:12'),
(70, 4502808, 859568034, 'Government ID', 'uploads/requirements/4502808_government_id_1773021458.pdf', 'approved', '2026-03-09 01:57:38'),
(71, 4502808, 859568034, 'Proof of Income', 'uploads/requirements/4502808_proof_of_income_1773021458.jpg', 'approved', '2026-03-09 01:57:38'),
(72, 4502808, 859568034, 'Bank Statement', 'uploads/requirements/4502808_bank_statement_1773021458.pdf', 'approved', '2026-03-09 01:57:38'),
(73, 8846340, 859568034, 'Government ID', 'uploads/requirements/8846340_government_id_1773024505.pdf', 'approved', '2026-03-09 02:48:25'),
(74, 8846340, 859568034, 'Proof of Income', 'uploads/requirements/8846340_proof_of_income_1773024505.pdf', 'approved', '2026-03-09 02:48:25'),
(75, 8846340, 859568034, 'Bank Statement', 'uploads/requirements/8846340_bank_statement_1773024505.pdf', 'approved', '2026-03-09 02:48:25'),
(76, 9367365, 859568034, 'Government ID', 'uploads/requirements/9367365_government_id_1773221407.pdf', 'approved', '2026-03-11 09:30:07'),
(77, 9367365, 859568034, 'Proof of Income', 'uploads/requirements/9367365_proof_of_income_1773221407.pdf', 'approved', '2026-03-11 09:30:07'),
(78, 9367365, 859568034, 'Bank Statement', 'uploads/requirements/9367365_bank_statement_1773221407.pdf', 'approved', '2026-03-11 09:30:07'),
(79, 9296576, 859568034, 'Government ID', 'uploads/requirements/9296576_government_id_1773222215.jpg', 'approved', '2026-03-11 09:43:35'),
(80, 9296576, 859568034, 'Proof of Income', 'uploads/requirements/9296576_proof_of_income_1773222215.jpg', 'approved', '2026-03-11 09:43:35'),
(81, 9296576, 859568034, 'Bank Statement', 'uploads/requirements/9296576_bank_statement_1773222215.pdf', 'approved', '2026-03-11 09:43:35'),
(82, 9605565, 417803011, 'Government ID', 'uploads/requirements/9605565_government_id_1773225792.pdf', 'approved', '2026-03-11 10:43:12'),
(83, 9605565, 417803011, 'Proof of Income', 'uploads/requirements/9605565_proof_of_income_1773225792.jpg', 'approved', '2026-03-11 10:43:12'),
(84, 9605565, 417803011, 'Bank Statement', 'uploads/requirements/9605565_bank_statement_1773225792.pdf', 'approved', '2026-03-11 10:43:12'),
(85, 6507135, 417803011, 'Government ID', 'uploads/requirements/6507135_government_id_1773313371.pdf', 'approved', '2026-03-12 11:02:51'),
(86, 6507135, 417803011, 'Proof of Income', 'uploads/requirements/6507135_proof_of_income_1773313371.pdf', 'approved', '2026-03-12 11:02:51'),
(87, 6507135, 417803011, 'Bank Statement', 'uploads/requirements/6507135_bank_statement_1773313371.jpg', 'approved', '2026-03-12 11:02:51'),
(88, 7716164, 417803011, 'Government ID', 'uploads/requirements/7716164_government_id_1773314281.pdf', 'approved', '2026-03-12 11:18:01'),
(89, 7716164, 417803011, 'Proof of Income', 'uploads/requirements/7716164_proof_of_income_1773314281.jpg', 'approved', '2026-03-12 11:18:01'),
(90, 7716164, 417803011, 'Bank Statement', 'uploads/requirements/7716164_bank_statement_1773314281.pdf', 'approved', '2026-03-12 11:18:01'),
(91, 7992160, 417803011, 'Government ID', 'uploads/requirements/7992160_government_id_1773088211.pdf', 'approved', '2026-03-09 20:30:11'),
(92, 7992160, 417803011, 'Proof of Income', 'uploads/requirements/7992160_proof_of_income_1773088211.pdf', 'approved', '2026-03-09 20:30:11'),
(93, 7992160, 417803011, 'Bank Statement', 'uploads/requirements/7992160_bank_statement_1773088211.pdf', 'approved', '2026-03-09 20:30:11'),
(94, 3297854, 417803011, 'Government ID', 'uploads/requirements/3297854_government_id_1773090625.jpg', 'approved', '2026-03-09 21:10:25'),
(95, 3297854, 417803011, 'Proof of Income', 'uploads/requirements/3297854_proof_of_income_1773090625.jpg', 'approved', '2026-03-09 21:10:25'),
(96, 3297854, 417803011, 'Bank Statement', 'uploads/requirements/3297854_bank_statement_1773090625.jpg', 'approved', '2026-03-09 21:10:25'),
(97, 3968318, 417803011, 'Government ID', 'uploads/requirements/3968318_government_id_1773091188.jpg', 'approved', '2026-03-09 21:19:48'),
(98, 3968318, 417803011, 'Proof of Income', 'uploads/requirements/3968318_proof_of_income_1773091188.jpg', 'approved', '2026-03-09 21:19:48'),
(99, 3968318, 417803011, 'Bank Statement', 'uploads/requirements/3968318_bank_statement_1773091188.jpg', 'approved', '2026-03-09 21:19:48'),
(100, 7343036, 417803011, 'Government ID', 'uploads/requirements/7343036_government_id_1773091498.jpg', 'approved', '2026-03-09 21:24:58'),
(101, 7343036, 417803011, 'Proof of Income', 'uploads/requirements/7343036_proof_of_income_1773091498.pdf', 'approved', '2026-03-09 21:24:58'),
(102, 7343036, 417803011, 'Bank Statement', 'uploads/requirements/7343036_bank_statement_1773091498.jpg', 'approved', '2026-03-09 21:24:58'),
(103, 6452229, 893619828, 'Government ID', 'uploads/requirements/6452229_government_id_1773092528.jpg', 'approved', '2026-03-09 21:42:08'),
(104, 6452229, 893619828, 'Proof of Income', 'uploads/requirements/6452229_proof_of_income_1773092528.jpg', 'approved', '2026-03-09 21:42:08'),
(105, 6452229, 893619828, 'Bank Statement', 'uploads/requirements/6452229_bank_statement_1773092528.jpg', 'approved', '2026-03-09 21:42:08'),
(106, 4618634, 893619828, 'Government ID', 'uploads/requirements/4618634_government_id_1773142041.pdf', 'approved', '2026-03-10 11:27:21'),
(107, 4618634, 893619828, 'Proof of Income', 'uploads/requirements/4618634_proof_of_income_1773142041.pdf', 'approved', '2026-03-10 11:27:21'),
(108, 4618634, 893619828, 'Bank Statement', 'uploads/requirements/4618634_bank_statement_1773142041.pdf', 'approved', '2026-03-10 11:27:21'),
(109, 5214063, 893619828, 'Government ID', 'uploads/requirements/5214063_government_id_1773142392.pdf', 'approved', '2026-03-10 11:33:12'),
(110, 5214063, 893619828, 'Proof of Income', 'uploads/requirements/5214063_proof_of_income_1773142392.pdf', 'approved', '2026-03-10 11:33:12'),
(111, 5214063, 893619828, 'Bank Statement', 'uploads/requirements/5214063_bank_statement_1773142392.pdf', 'approved', '2026-03-10 11:33:12'),
(112, 4111624, 893619828, 'Government ID', 'uploads/requirements/4111624_government_id_1773142819.pdf', 'approved', '2026-03-10 11:40:19'),
(113, 4111624, 893619828, 'Proof of Income', 'uploads/requirements/4111624_proof_of_income_1773142819.pdf', 'approved', '2026-03-10 11:40:19'),
(114, 4111624, 893619828, 'Bank Statement', 'uploads/requirements/4111624_bank_statement_1773142819.jpg', 'approved', '2026-03-10 11:40:19'),
(115, 5501787, 893619828, 'Government ID', 'uploads/requirements/5501787_government_id_1773291256.pdf', 'approved', '2026-03-12 04:54:16'),
(116, 5501787, 893619828, 'Proof of Income', 'uploads/requirements/5501787_proof_of_income_1773291256.pdf', 'approved', '2026-03-12 04:54:16'),
(117, 5501787, 893619828, 'Bank Statement', 'uploads/requirements/5501787_bank_statement_1773291256.pdf', 'approved', '2026-03-12 04:54:16'),
(118, 6814101, 893619828, 'Government ID', 'uploads/requirements/6814101_government_id_1773300605.pdf', 'approved', '2026-03-12 07:30:05'),
(119, 6814101, 893619828, 'Proof of Income', 'uploads/requirements/6814101_proof_of_income_1773300605.pdf', 'approved', '2026-03-12 07:30:05'),
(120, 6814101, 893619828, 'Bank Statement', 'uploads/requirements/6814101_bank_statement_1773300605.pdf', 'approved', '2026-03-12 07:30:05'),
(121, 5154873, 893619828, 'Government ID', 'uploads/requirements/5154873_government_id_1773311154.pdf', 'approved', '2026-03-12 10:25:54'),
(122, 5154873, 893619828, 'Proof of Income', 'uploads/requirements/5154873_proof_of_income_1773311154.pdf', 'approved', '2026-03-12 10:25:54'),
(123, 5154873, 893619828, 'Bank Statement', 'uploads/requirements/5154873_bank_statement_1773311154.pdf', 'approved', '2026-03-12 10:25:55'),
(124, 4178854, 893619828, 'Government ID', 'uploads/requirements/4178854_government_id_1773348233.pdf', 'approved', '2026-03-12 20:43:53'),
(125, 4178854, 893619828, 'Proof of Income', 'uploads/requirements/4178854_proof_of_income_1773348233.pdf', 'approved', '2026-03-12 20:43:53'),
(126, 4178854, 893619828, 'Bank Statement', 'uploads/requirements/4178854_bank_statement_1773348233.pdf', 'approved', '2026-03-12 20:43:53'),
(127, 3448885, 893619828, 'Government ID', 'uploads/requirements/3448885_government_id_1773386203.png', 'approved', '2026-03-13 07:16:43'),
(128, 3448885, 893619828, 'Proof of Income', 'uploads/requirements/3448885_proof_of_income_1773386203.png', 'approved', '2026-03-13 07:16:43'),
(129, 3448885, 893619828, 'Bank Statement', 'uploads/requirements/3448885_bank_statement_1773386203.png', 'approved', '2026-03-13 07:16:43'),
(130, 3587450, 893619828, 'Government ID', 'uploads/requirements/3587450_government_id_1773386806.png', 'rejected', '2026-03-13 07:26:46'),
(131, 3587450, 893619828, 'Proof of Income', 'uploads/requirements/3587450_proof_of_income_1773386806.png', 'rejected', '2026-03-13 07:26:46'),
(132, 3587450, 893619828, 'Bank Statement', 'uploads/requirements/3587450_bank_statement_1773386806.png', 'rejected', '2026-03-13 07:26:46'),
(133, 6190708, 893619828, 'Government ID', 'uploads/requirements/6190708_government_id_1773387380.png', 'rejected', '2026-03-13 07:36:20'),
(134, 6190708, 893619828, 'Proof of Income', 'uploads/requirements/6190708_proof_of_income_1773387380.png', 'rejected', '2026-03-13 07:36:20'),
(135, 6190708, 893619828, 'Bank Statement', 'uploads/requirements/6190708_bank_statement_1773387380.png', 'rejected', '2026-03-13 07:36:20'),
(136, 1146269, 893619828, 'Valid ID', 'uploads/requirements/1146269_valid_id_1773563486.png', 'approved', '2026-03-15 08:31:26'),
(137, 1146269, 893619828, 'Proof of Active Membership', 'uploads/requirements/1146269_active_member_1773563486.png', 'approved', '2026-03-15 08:31:26'),
(138, 6461143, 893619828, 'Valid ID', 'uploads/requirements/6461143_valid_id_1773698303.pdf', 'approved', '2026-03-16 21:58:23'),
(139, 6461143, 893619828, 'Proof of Active Membership', 'uploads/requirements/6461143_active_member_1773698303.pdf', 'approved', '2026-03-16 21:58:23'),
(140, 7270237, 893619828, 'Valid ID', 'uploads/requirements/7270237_valid_id_1773713323.pdf', 'approved', '2026-03-17 02:08:43'),
(141, 7270237, 893619828, 'Proof of Active Membership', 'uploads/requirements/7270237_active_member_1773713323.pdf', 'approved', '2026-03-17 02:08:43');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempts` int(11) DEFAULT 1,
  `locked_until` timestamp NULL DEFAULT NULL,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `username`, `ip_address`, `attempts`, `locked_until`, `last_attempt`) VALUES
(6, 'yro', '::1', 1, NULL, '2026-03-03 05:17:04');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `user_type` enum('admin','customer') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `email`, `token`, `user_type`, `created_at`, `expires_at`) VALUES
(11, 'tyronealariao06@gmail.com', '446693', 'admin', '2026-03-13 07:32:47', '2026-03-13 08:42:47');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_number` int(10) NOT NULL,
  `account_number` int(10) NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_number`, `account_number`, `payment_amount`, `payment_date`, `payment_method`, `notes`, `created_at`, `user_id`) VALUES
(120857, 6452229, 1090.00, '2026-03-10', 'Cash', '', '2026-03-10 11:26:10', 369707),
(151517, 6814101, 600.00, '2026-03-12', 'Cash', '', '2026-03-12 07:54:48', 369707),
(174369, 5501787, 5300.00, '2026-03-12', 'Cash', '', '2026-03-12 07:28:19', 369707),
(244420, 6814101, 10600.00, '2026-03-12', 'Cash', '', '2026-03-12 07:55:33', 369707),
(255120, 5154873, 62000.00, '2026-03-12', 'Cash', '', '2026-03-12 11:07:32', 369707),
(258960, 4618634, 30450.00, '2026-03-10', 'Cash', '', '2026-03-10 11:32:35', 369707),
(277329, 5214063, 10900.00, '2026-03-10', 'Cash', '', '2026-03-10 11:39:01', 369707),
(309767, 4111624, 4000.00, '2026-03-12', 'E-Wallet', '', '2026-03-12 04:49:42', 369707),
(347787, 6814101, 10000.00, '2026-03-12', 'E-Wallet', ' | Type: GCash, Number: 09919094456, Ref: 3434', '2026-03-12 07:53:46', 369707),
(356652, 6461143, 5000.00, '2026-03-17', 'Cash', '', '2026-03-17 01:55:17', 369707),
(378349, 6814101, 10000.00, '2026-03-12', 'Cash', '', '2026-03-12 07:31:42', 369707),
(388783, 4111624, 4000.00, '2026-03-12', 'E-Wallet', '', '2026-03-12 04:48:21', 369707),
(437596, 5501787, 5300.00, '2026-03-12', 'Cash', '', '2026-03-12 07:29:29', 369707),
(550092, 6814101, 10000.00, '2026-03-12', 'E-Wallet', ' | Type: GCash, Number: 09919094456, Ref: 3434', '2026-03-12 07:47:49', 369707),
(649213, 1146269, 16200.00, '2026-03-17', 'Cash', '', '2026-03-16 20:54:47', 369707),
(684507, 4111624, 1000.00, '2026-03-10', 'Cash', '', '2026-03-10 11:52:24', 369707),
(706651, 6452229, 1000.00, '2026-03-10', 'Cash', '', '2026-03-10 11:24:01', 369707),
(768918, 6461143, 16200.00, '2026-03-17', 'Cash', '', '2026-03-17 02:03:12', 369707),
(769597, 5214063, 10900.00, '2026-03-10', 'Cash', '', '2026-03-10 11:39:42', 369707),
(795925, 4111624, 4000.00, '2026-03-12', 'E-Wallet', '', '2026-03-12 04:46:19', 369707),
(825414, 3448885, 20400.00, '2026-03-13', 'Cash', '', '2026-03-13 07:26:13', 369707),
(836644, 6814101, 10000.00, '2026-03-12', 'E-Wallet', ' | Type: GCash, Number: 09919094456, Ref: 3434', '2026-03-12 07:52:29', 369707),
(899533, 4618634, 30450.00, '2026-03-10', 'Cash', '', '2026-03-10 11:32:29', 369707),
(949680, 3448885, 20400.00, '2026-03-13', 'Cash', '', '2026-03-13 07:26:19', 369707),
(961425, 5501787, 5000.00, '2026-03-12', 'Cash', '', '2026-03-12 04:57:04', 369707),
(974903, 5154873, 62000.00, '2026-03-12', 'Cash', '', '2026-03-12 11:07:20', 369707),
(989566, 1146269, 5000.00, '2026-03-15', 'Cash', '', '2026-03-15 10:00:33', 369707),
(999033, 7270237, 5000.00, '2026-03-17', 'E-Wallet', ' | Type: GCash, Number: 09919094456, Ref: 123453', '2026-03-17 02:29:05', 369707);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_number` int(5) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `role` enum('admin','customer') DEFAULT 'admin',
  `qr_code` varchar(64) DEFAULT NULL,
  `qr_code_enabled` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_number`, `username`, `password`, `email`, `role`, `qr_code`, `qr_code_enabled`) VALUES
(11851, 'JOAQUIN', 'AlariaoTyrone05!', 'tyronealariao06@gmail.com', 'admin', NULL, 0),
(92414, 'JOA', 'AlariaoTyrone05!', 'JOA@gmail.com', 'admin', NULL, 0),
(369707, 'Tyrone', 'tyroneAlariao05!', 'tyronealariao06@gmail.com', 'admin', '7305246fe9c67d19805750844f568e209301f75f5675b9b5efdd7ed0d08938d5', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`account_number`),
  ADD KEY `customer` (`customer`),
  ADD KEY `account_type` (`account_type`),
  ADD KEY `account_status` (`account_status`);

--
-- Indexes for table `account_status`
--
ALTER TABLE `account_status`
  ADD PRIMARY KEY (`account_status_number`);

--
-- Indexes for table `account_type`
--
ALTER TABLE `account_type`
  ADD PRIMARY KEY (`account_type_number`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_number`),
  ADD KEY `customer_type` (`customer_type`);

--
-- Indexes for table `customers_type`
--
ALTER TABLE `customers_type`
  ADD PRIMARY KEY (`customer_type_number`);

--
-- Indexes for table `loan_notifications`
--
ALTER TABLE `loan_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_read` (`is_read`);

--
-- Indexes for table `loan_requirements`
--
ALTER TABLE `loan_requirements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_ip` (`ip_address`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=378;

--
-- AUTO_INCREMENT for table `loan_notifications`
--
ALTER TABLE `loan_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `loan_requirements`
--
ALTER TABLE `loan_requirements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`customer`) REFERENCES `customers` (`customer_number`),
  ADD CONSTRAINT `accounts_ibfk_2` FOREIGN KEY (`account_type`) REFERENCES `account_type` (`account_type_number`),
  ADD CONSTRAINT `accounts_ibfk_3` FOREIGN KEY (`account_status`) REFERENCES `account_status` (`account_status_number`);

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`customer_type`) REFERENCES `customers_type` (`customer_type_number`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
