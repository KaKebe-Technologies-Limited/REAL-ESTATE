-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 12:23 AM
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
-- Database: `u850523537_allea`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'new',
  `icon_class` varchar(50) DEFAULT NULL,
  `icon_bg_class` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `activity_type`, `title`, `description`, `created_at`, `status`, `icon_class`, `icon_bg_class`) VALUES
(1, 'property_added', 'New Property Added', 'dragon in ', '2025-04-03 17:57:37', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(2, 'owner_registered', 'New Owner Registered', NULL, '2025-04-03 18:09:07', 'new', 'fas fa-user-plus', 'bg-soft-warning'),
(3, 'property_added', 'New Property Added', 'Mecha in ', '2025-04-03 18:11:28', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(4, 'owner_registered', 'New Owner Registered', NULL, '2025-04-03 19:06:00', 'new', 'fas fa-user-plus', 'bg-soft-warning'),
(5, 'manager_registered', 'New Manager Registered', NULL, '2025-04-03 19:14:53', 'new', 'fas fa-user-plus', 'bg-soft-warning'),
(6, 'property_added', 'New Property Added', 'kingdom in lira', '2025-04-04 11:47:00', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(7, 'property_added', 'New Property Added', 'neyo in lira', '2025-04-04 12:07:59', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(8, 'property_added', 'New Property Added', 'fox in lira', '2025-04-04 12:14:42', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(9, 'property_added', 'New Property Added', 'naayla in lira', '2025-04-04 12:29:07', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(10, 'property_added', 'New Property Added', 'Amelie in lira', '2025-04-04 12:39:14', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(11, 'property_added', 'New Property Added', 'oyo in lira', '2025-04-04 13:29:53', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(12, 'property_added', 'New Property Added', 'mafox in lira', '2025-04-04 13:41:16', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(13, 'property_added', 'New Property Added', 'jerom in lira', '2025-04-04 13:44:08', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(14, 'property_added', 'New Property Added', 'colla in lira', '2025-04-04 13:55:18', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(15, 'property_registered', 'Property Deleted', ' in ', '2025-04-05 11:56:01', 'new', 'fas fa-trash', 'bg-soft-warning'),
(16, 'property_registered', 'Property Deleted', ' in ', '2025-04-05 12:01:51', 'new', 'fas fa-trash', 'bg-soft-error'),
(17, 'property_registered', 'Property Deleted', ' in ', '2025-04-05 12:09:56', 'new', 'fas fa-trash', 'bg-soft-danger'),
(18, 'property_registered', 'Property Deleted', ' in ', '2025-04-05 12:20:49', 'new', 'fas fa-trash', 'bg-soft-danger'),
(19, 'property_registered', 'Property Deleted', 'tyrd in cdr', '2025-04-05 12:23:14', 'new', 'fas fa-trash', 'bg-soft-danger'),
(20, 'property_registered', 'Property Updated', ' in ', '2025-04-05 12:32:14', 'new', 'fas fa-trash', 'bg-soft-danger'),
(21, 'property_registered', 'Property Updated', 'monkey in lira city west', '2025-04-05 12:34:29', 'new', 'fas fa-trash', 'bg-soft-danger'),
(22, 'property_added', 'New Property Added', 'trial in lira', '2025-04-06 20:05:12', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(23, 'property_registered', 'Property Updated', 'monkey in lira', '2025-04-09 14:17:53', 'new', 'fas fa-trash', 'bg-soft-warning'),
(24, 'property_registered', 'Property Updated', 'monkeys in lira', '2025-04-09 14:18:20', 'new', 'fas fa-trash', 'bg-soft-warning'),
(25, 'property_registered', 'Property Updated', 'monkey in lira', '2025-04-09 15:01:44', 'new', 'fas fa-trash', 'bg-soft-warning'),
(26, 'property_registered', 'Property Updated', 'dragon in lira', '2025-04-09 15:03:05', 'new', 'fas fa-trash', 'bg-soft-warning'),
(27, 'property_registered', 'Property Updated', 'Mecha in lira', '2025-04-09 15:05:13', 'new', 'fas fa-trash', 'bg-soft-warning'),
(28, 'property_registered', 'Property Updated', 'Amelie in lira', '2025-04-09 15:12:03', 'new', 'fas fa-trash', 'bg-soft-warning'),
(29, 'property_registered', 'Property Updated', 'kingdom in lira', '2025-04-09 15:18:01', 'new', 'fas fa-trash', 'bg-soft-warning'),
(30, 'property_registered', 'Property Updated', 'neyo in lira', '2025-04-09 15:20:33', 'new', 'fas fa-trash', 'bg-soft-warning'),
(31, 'property_registered', 'Property Updated', 'neyo in lira', '2025-04-09 15:25:09', 'new', 'fas fa-trash', 'bg-soft-warning'),
(32, 'property_registered', 'Property Updated', 'naayla in lira', '2025-04-09 15:44:23', 'new', 'fas fa-trash', 'bg-soft-warning'),
(33, 'property_registered', 'Property Updated', 'monkey in lira', '2025-04-09 20:55:34', 'new', 'fas fa-trash', 'bg-soft-warning'),
(34, 'property_registered', 'Property Updated', 'monkey in lira', '2025-04-09 21:14:45', 'new', 'fas fa-trash', 'bg-soft-warning'),
(35, 'property_registered', 'Property Updated', 'oyo in lira', '2025-04-09 21:15:37', 'new', 'fas fa-trash', 'bg-soft-warning'),
(36, 'property_registered', 'Property Updated', 'monkeys in lira', '2025-04-09 21:57:00', 'new', 'fas fa-trash', 'bg-soft-warning'),
(37, 'property_registered', 'Property Updated', 'monkeys in lira', '2025-04-09 21:57:00', 'new', 'fas fa-trash', 'bg-soft-warning'),
(38, 'property_registered', 'Property Updated', 'monkey in lira', '2025-04-09 21:58:09', 'new', 'fas fa-trash', 'bg-soft-warning'),
(39, 'property_registered', 'Property Updated', 'monkey in lira', '2025-04-09 21:58:09', 'new', 'fas fa-trash', 'bg-soft-warning'),
(40, 'property_registered', 'Property Deleted', '0 in rental', '2025-04-14 17:10:10', 'new', 'fas fa-trash', 'bg-soft-warning'),
(41, 'property_registered', 'Property Deleted', '0 in rental', '2025-04-14 17:10:10', 'new', 'fas fa-trash', 'bg-soft-warning'),
(42, 'property_registered', 'Property Deleted', '0 in rental', '2025-04-14 17:10:10', 'new', 'fas fa-trash', 'bg-soft-warning'),
(43, 'property_registered', 'Property Deleted', '0 in rental', '2025-04-14 17:28:51', 'new', 'fas fa-trash', 'bg-soft-warning'),
(44, 'property_registered', 'Property Deleted', '0 in rental', '2025-04-14 17:28:51', 'new', 'fas fa-trash', 'bg-soft-warning'),
(45, 'property_registered', 'Property Deleted', '0 in rental', '2025-04-14 17:28:51', 'new', 'fas fa-trash', 'bg-soft-warning'),
(46, 'property_registered', 'Property Deleted', '3 in rental', '2025-04-14 20:27:56', 'new', 'fas fa-trash', 'bg-soft-warning'),
(47, 'property_registered', 'Property Deleted', '3 in rental', '2025-04-14 20:27:56', 'new', 'fas fa-trash', 'bg-soft-warning'),
(48, 'property_registered', 'Property Deleted', '3 in rental', '2025-04-14 20:27:56', 'new', 'fas fa-trash', 'bg-soft-warning'),
(49, 'manager_registered', 'New Manager Registered', NULL, '2025-04-14 21:00:20', 'new', 'fas fa-user-plus', 'bg-soft-warning'),
(50, 'manager_registered', 'New Manager Registered', NULL, '2025-04-14 21:00:21', 'new', 'fas fa-user-plus', 'bg-soft-warning'),
(51, 'property_added', 'New Property Added', 'Edge Apartments in Gulu', '2025-04-14 21:06:55', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(52, 'property_added', 'New Property Added', 'Naaya Estates in Gulu', '2025-04-14 21:10:32', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(53, 'property_registered', 'Property Updated', 'Edge Apartments in Gulu', '2025-04-14 21:15:00', 'new', 'fas fa-trash', 'bg-soft-warning'),
(54, 'property_registered', 'Property Updated', 'Edge Apartments in Gulu', '2025-04-14 21:15:00', 'new', 'fas fa-trash', 'bg-soft-warning'),
(55, 'property_registered', 'Property Updated', 'Edge Apartments in Gulu', '2025-04-14 21:15:00', 'new', 'fas fa-trash', 'bg-soft-warning'),
(56, 'property_registered', 'Property Updated', 'Edge Apartments in Gulu', '2025-04-14 21:15:00', 'new', 'fas fa-trash', 'bg-soft-warning'),
(57, 'property_added', 'New Property Added', 'Skyz Apartments in Kampala', '2025-04-14 21:19:54', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(58, 'property_registered', 'Property Updated', 'Skyz Apartments in Kampala', '2025-04-14 21:21:32', 'new', 'fas fa-trash', 'bg-soft-warning'),
(59, 'property_registered', 'Property Updated', 'Skyz Apartments in Kampala', '2025-04-14 21:21:32', 'new', 'fas fa-trash', 'bg-soft-warning'),
(60, 'property_registered', 'Property Updated', 'Skyz Apartments in Kampala', '2025-04-14 21:21:33', 'new', 'fas fa-trash', 'bg-soft-warning'),
(61, 'property_registered', 'Property Updated', 'Skyz Apartments in Kampala', '2025-04-14 21:21:33', 'new', 'fas fa-trash', 'bg-soft-warning'),
(62, 'property_added', 'New Property Added', 'Good News Hotel in lira', '2025-04-14 21:34:07', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(63, 'property_registered', 'Property Updated', 'Skyz Apartmentsss in Kampala', '2025-04-14 22:18:08', 'new', 'fas fa-trash', 'bg-soft-warning'),
(64, 'property_registered', 'Property Updated', 'Skyz Apartmentsss in Kampala', '2025-04-14 22:18:08', 'new', 'fas fa-trash', 'bg-soft-warning'),
(65, 'property_registered', 'Property Updated', 'Skyz Apartmentsss in Kampala', '2025-04-14 22:18:08', 'new', 'fas fa-trash', 'bg-soft-warning'),
(66, 'property_registered', 'Property Updated', 'Skyz Apartmentsss in Kampala', '2025-04-14 22:18:09', 'new', 'fas fa-trash', 'bg-soft-warning'),
(67, 'property_added', 'New Property Added', 'Jungle Apartments in Gulu', '2025-04-15 10:56:45', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(68, 'property_added', 'New Property Added', 'Amulem Complex in Kampala', '2025-04-15 11:00:17', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(69, 'property_registered', 'Property Deleted', '14 in rental', '2025-04-15 11:04:24', 'new', 'fas fa-trash', 'bg-soft-warning'),
(70, 'property_registered', 'Property Deleted', '14 in rental', '2025-04-15 11:04:24', 'new', 'fas fa-trash', 'bg-soft-warning'),
(71, 'property_registered', 'Property Deleted', '14 in rental', '2025-04-15 11:04:24', 'new', 'fas fa-trash', 'bg-soft-warning'),
(72, 'property_added', 'New Property Added', 'Gracious Palace in Gulu', '2025-04-15 11:54:01', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(73, 'property_added', 'New Property Added', 'Angels Apartments in lira', '2025-04-15 12:47:31', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(74, 'property_added', 'New Property Added', 'monkeys in lira', '2025-04-16 18:43:21', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(75, 'property_registered', 'Property Updated', 'monkey in lira', '2025-04-16 19:03:02', 'new', 'fas fa-trash', 'bg-soft-warning'),
(76, 'property_registered', 'Property Updated', 'monkey in lira', '2025-04-16 19:03:02', 'new', 'fas fa-trash', 'bg-soft-warning'),
(77, 'property_added', 'New Property Added', 'lions in lira', '2025-04-16 20:40:48', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(78, 'property_added', 'New Property Added', 'Jogo Apartments in Gulu', '2025-04-16 20:45:23', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(79, 'property_added', 'New Property Added', 'Amazing Apartments  in Kampala', '2025-04-16 21:36:34', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(80, 'property_added', 'New Property Added', 'American Apartments in Gulu', '2025-04-17 10:42:27', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(81, 'property_added', 'New Property Added', 'California Apartments in lira', '2025-04-17 10:50:15', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(82, 'property_added', 'New Property Added', 'Los Angelos Apartments in Kampala', '2025-04-17 10:54:17', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(83, 'registration', 'New Owner Registered', 'Owner Omara Ben has registered', '2025-04-28 08:22:39', 'new', 'fas fa-user-plus', 'bg-success'),
(84, 'property_added', 'New Property Added', 'Anyach Apartments in lira', '2025-04-28 08:29:08', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(85, 'registration', 'New Owner Registered', 'Owner Ivan Nyayo has registered and paid registration fee', '2025-05-02 09:14:13', 'new', 'fas fa-user-plus', 'bg-success'),
(86, 'registration', 'Registration Initiated', 'User ivan has initiated registration', '2025-05-02 11:55:14', 'new', 'fas fa-user-plus', 'bg-info'),
(87, 'registration', 'Registration Initiated', 'User ivan has initiated registration', '2025-05-02 11:59:15', 'new', 'fas fa-user-plus', 'bg-info'),
(88, 'registration', 'New Owner Registered', 'Owner Ivan Nyayo has registered and paid registration fee', '2025-05-06 20:27:41', 'new', 'fas fa-user-plus', 'bg-success'),
(89, 'registration', 'New Owner Registered', 'Owner Mafox Nyayo has registered and paid registration fee', '2025-05-06 21:50:28', 'new', 'fas fa-user-plus', 'bg-success'),
(90, 'property_added', 'New Property Added', 'Omich Apartments in lira', '2025-05-07 10:49:00', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(91, 'property_added', 'New Property Added', 'Mafox Apartments in lira', '2025-05-07 10:56:38', 'new', 'fas fa-plus-circle', 'bg-soft-primary'),
(92, 'subscription_renewed', 'Subscription Renewed', 'Owner Mafox Nyayo renewed subscription for 4 months until 2025-09-09', '2025-05-09 13:12:14', 'new', 'fas fa-sync', 'bg-success'),
(93, 'registration', 'New Owner Registered', 'Owner Okot Daniel has registered and paid registration fee', '2025-05-09 13:21:14', 'new', 'fas fa-user-plus', 'bg-success'),
(94, 'subscription_renewed', 'Subscription Renewed', 'Owner Okwel  Moses renewed subscription for 4 months until 2025-09-09', '2025-05-09 14:27:36', 'new', 'fas fa-sync', 'bg-success'),
(95, 'registration', 'New Owner Registered', 'Owner Okwel  Moses has registered and paid registration fee. Initial subscription activated for 4 months.', '2025-05-09 14:27:36', 'new', 'fas fa-user-plus', 'bg-success'),
(96, 'subscription_renewed', 'Subscription Renewed', 'Owner Ivan Nyayo renewed subscription for 1 months until 2025-10-07', '2025-05-09 14:48:54', 'new', 'fas fa-sync', 'bg-success'),
(97, 'subscription_renewed', 'Subscription Renewed', 'Owner Test User renewed subscription for 4 months until 2025-09-11', '2025-05-11 10:57:30', 'new', 'fas fa-sync', 'bg-success'),
(98, 'registration', 'New Owner Registered', 'Owner Test User has registered and paid registration fee. Initial subscription activated for 4 months.', '2025-05-11 10:57:30', 'new', 'fas fa-user-plus', 'bg-success'),
(99, 'subscription_renewed', 'Subscription Renewed', 'Owner Opollo  Milton renewed subscription for 4 months until 2025-09-11', '2025-05-11 11:09:54', 'new', 'fas fa-sync', 'bg-success'),
(100, 'registration', 'New Owner Registered', 'Owner Opollo  Milton has registered and paid registration fee. Initial subscription activated for 4 months.', '2025-05-11 11:09:54', 'new', 'fas fa-user-plus', 'bg-success');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `first_name`, `last_name`, `email`, `phone`, `password`, `profile_picture`, `date_created`) VALUES
(1, 'nyayo', 'John', 'Ogwal', 'nyayo@gmail.com', '07564537464', '$2y$10$9NItHoiEQB79htWtdnrp/.jNn847zXquZvNpmhMywTjQrYy.eq1pi', 'uploads/profile_picture/1_1745327917.jpeg', '2025-03-29 19:26:25');

-- --------------------------------------------------------

--
-- Table structure for table `owner_payments`
--

CREATE TABLE `owner_payments` (
  `payment_id` int(11) NOT NULL,
  `owner_id` int(10) UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'UGX',
  `transaction_id` varchar(100) DEFAULT NULL,
  `order_tracking_id` varchar(100) DEFAULT NULL,
  `merchant_reference` varchar(100) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','cancelled') DEFAULT 'pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owner_payments`
--

INSERT INTO `owner_payments` (`payment_id`, `owner_id`, `amount`, `currency`, `transaction_id`, `order_tracking_id`, `merchant_reference`, `payment_method`, `payment_status`, `payment_date`) VALUES
(1, 9, 500.00, 'UGX', '7465632377376008304281', '14969322-af7a-4e2b-b0ce-dbd77c5ec2fe', 'REG-1746563096-2797', 'Visa', 'completed', '2025-05-06 20:27:40'),
(2, 10, 500.00, 'UGX', '7465681036976351304277', 'bd3c2a1a-7e2b-407d-9355-dbd7ad1b3632', 'REG-1746567966-1318', 'Visa', 'completed', '2025-05-06 21:50:27'),
(3, 11, 500.00, 'UGX', '32724174245', '21bac9dd-556a-4091-82ec-dbd47d0f37a6', 'REG-1746796796-7802', 'MTNUG', 'completed', '2025-05-09 13:21:14'),
(4, 12, 500.00, 'UGX', '32724482144', '7ca22add-d845-4dc3-bcad-dbd4779ed1da', 'REG-1746797797-4212', 'MTNUG', 'completed', '2025-05-09 13:37:56'),
(5, 13, 500.00, 'UGX', '32725431456', '3f9c366f-a355-49e5-b73d-dbd4a0cf4d11', 'REG-1746800703-6603', 'MTNUG', 'completed', '2025-05-09 14:27:36'),
(6, 14, 500.00, 'UGX', 'TEST1746961050', 'TEST_ORDER_1746961050', 'TEST_MERCHANT_1746961050', 'test_method', 'completed', '2025-05-11 10:57:30'),
(7, 15, 500.00, 'UGX', '32760140235', '9a2b67cf-5cf8-4765-acb6-dbd21e36c190', 'REG-1746961732-4992', 'MTNUG', 'completed', '2025-05-11 11:09:54');

-- --------------------------------------------------------

--
-- Table structure for table `owner_subscriptions`
--

CREATE TABLE `owner_subscriptions` (
  `subscription_id` int(11) NOT NULL,
  `owner_id` int(10) UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'UGX',
  `transaction_id` varchar(100) DEFAULT NULL,
  `order_tracking_id` varchar(100) DEFAULT NULL,
  `merchant_reference` varchar(100) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','cancelled') DEFAULT 'pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `subscription_months` int(11) DEFAULT 4,
  `subscription_start_date` timestamp NULL DEFAULT NULL,
  `subscription_end_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owner_subscriptions`
--

INSERT INTO `owner_subscriptions` (`subscription_id`, `owner_id`, `amount`, `currency`, `transaction_id`, `order_tracking_id`, `merchant_reference`, `payment_method`, `payment_status`, `payment_date`, `subscription_months`, `subscription_start_date`, `subscription_end_date`) VALUES
(1, 10, 50000.00, 'UGX', 'e15782f9-7378-4698-a3c1-dbd46625eda5', NULL, NULL, 'MTNUG', 'completed', '2025-05-09 13:12:14', 4, '2025-05-09 12:12:13', '2025-09-09 12:12:13'),
(2, 10, 500.00, 'UGX', '', 'e15782f9-7378-4698-a3c1-dbd46625eda5', 'SUB-1746796263-5930', 'MTNUG', 'pending', '2025-05-09 13:12:14', 4, '2025-05-09 12:12:13', '2025-09-09 12:12:13'),
(3, 13, 50000.00, 'UGX', '32725431456', NULL, NULL, 'MTNUG', 'completed', '2025-05-09 14:27:36', 4, '2025-05-09 13:27:36', '2025-09-09 13:27:36'),
(4, 9, 50000.00, 'UGX', 'admin_extension', NULL, NULL, 'Admin Extension', 'completed', '2025-05-09 14:48:54', 1, '2025-05-09 13:48:54', '2025-10-07 17:11:57'),
(5, 14, 50000.00, 'UGX', 'TEST1746961050', NULL, NULL, 'test_method', 'completed', '2025-05-11 10:57:30', 4, '2025-05-11 09:57:30', '2025-09-11 09:57:30'),
(6, 15, 50000.00, 'UGX', '32760140235', NULL, NULL, 'MTNUG', 'completed', '2025-05-11 11:09:54', 4, '2025-05-11 10:09:54', '2025-09-11 10:09:54');

-- --------------------------------------------------------

--
-- Table structure for table `owner_subscription_notes`
--

CREATE TABLE `owner_subscription_notes` (
  `note_id` int(11) NOT NULL,
  `owner_id` int(10) UNSIGNED DEFAULT NULL,
  `admin_id` int(10) UNSIGNED DEFAULT NULL,
  `note` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_manager`
--

CREATE TABLE `property_manager` (
  `manager_id` int(11) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `id_type` varchar(100) NOT NULL,
  `id_num` varchar(100) NOT NULL,
  `profile_picture` text NOT NULL DEFAULT 'uploads/profile_picture/default-profile.jpeg',
  `experience` int(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_manager`
--

INSERT INTO `property_manager` (`manager_id`, `username`, `first_name`, `last_name`, `email`, `password`, `phone`, `id_type`, `id_num`, `profile_picture`, `experience`, `address`, `date_created`) VALUES
(1, 'opio', 'Ojok', 'Ben', 'nyayoi799@gmail.com', '$2y$10$Rv9YaBi69jRMJe4Xo4cHGOo642Jf/OEcPYxo4wZ.qIR8MDKopDxce', '07564537464', 'National ID', '2345678', 'uploads/managers/manager_1_1743972188.jpeg', 2, 'Plot 11 Oyite Ojok Lane', '2025-03-31 19:58:17'),
(2, 'emma', 'ogwal', 'emma', 'emma@gmail.com', '$2y$10$7uECGPYq8AZaM1v8UCEcYeOcbknVY2Rf3WvRT6VK8IAJI/G8kuBxC', '07564537464', 'National ID', '2345678', 'uploads/profile_picture/1_1743804567.jpeg', 10, 'lira', '2025-03-31 20:05:10'),
(4, 'oscar', 'Ivan', 'Nyayo', 'nyayoi799@gmail.com', '$2y$10$Y40BR3w2UOaDh1Peq4S08unNKQZDknboiV2id7wzVfACcTSEL/o/O', '256764119058', 'National ID', '6745565', '', 5, 'Plot 11 Oyite Ojok Lane', '2025-04-14 21:00:20');

-- --------------------------------------------------------

--
-- Table structure for table `property_owner`
--

CREATE TABLE `property_owner` (
  `owner_id` int(11) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `id_type` varchar(100) NOT NULL,
  `id_num` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `profile_picture` text NOT NULL DEFAULT 'uploads/profile_picture/default-profile.jpeg',
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `subscription_start_date` timestamp NULL DEFAULT NULL,
  `subscription_end_date` timestamp NULL DEFAULT NULL,
  `subscription_status` enum('active','expired','pending') DEFAULT 'pending',
  `last_renewal_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_owner`
--

INSERT INTO `property_owner` (`owner_id`, `username`, `first_name`, `last_name`, `email`, `password`, `phone`, `id_type`, `id_num`, `address`, `profile_picture`, `date_created`, `payment_status`, `subscription_start_date`, `subscription_end_date`, `subscription_status`, `last_renewal_date`) VALUES
(1, 'pete', 'Peter', 'Okello', 'peter@gmail.com', '$2y$10$6wJhMre.hfNJ32/WpKkgtOFk8ntjBgejnoCZSTb7MGAQCWfbpf3zC', '2147483647', 'National ID', '567455674', 'lira', 'uploads/owners/owner_1_1744367724.jpeg', '2025-03-30 20:01:07', 'pending', NULL, NULL, 'pending', NULL),
(2, 'sedu', 'otolo', 'sedrick', 'otolo@gmail.com', '$2y$10$Is8aYtjjuX9.45tWIhszU.dv045bnt/OYTO9Fml9BEmmYSZZl.8va', '764119058', 'Passport', '6745565', 'Gulu', 'assets/images/profiles/owner_2_1745328123.jpeg', '2025-03-30 20:29:15', 'pending', NULL, NULL, 'pending', NULL),
(3, 'nam', 'nam', 'Benerd', 'nam@gmail.com', '$2y$10$QCIicfRrBjCnimIbiN58JOxeugQz4vvCxHk.dcTBBEkR.7gROTRzy', '777417207', 'Passport', '7889734', 'Otuke', '', '2025-03-31 08:32:53', 'pending', NULL, NULL, 'pending', NULL),
(4, 'dan', 'Ojok', 'Dan', 'dan@gmail.com', '$2y$10$SApNoRL2uHBcLMs8fWeso.0yEqkdv1IMDKNBtGYUudwnENSAFi9JS', '760703667', 'Passport', '345345647', 'Oyam', '', '2025-03-31 20:03:45', 'pending', NULL, NULL, 'pending', NULL),
(5, 'anyach', 'Anyach', 'Moses', 'anyach@gmail.com', '$2y$10$F1A0qI.mrqn.ZVytAPQtbO7lOsmy9DCqK1mclDGenVl.25D90D/jW', '777417207', 'National ID', '2147483647', 'lira', '', '2025-04-03 18:09:07', 'pending', NULL, NULL, 'pending', NULL),
(7, 'omara', 'Omara', 'Ben', 'omara@gmail.com', '$2y$10$7vtfH2D4NZi6lC5dWaI4au789Gyf92XkhcGQ/MDUAhGfKRidNof/S', '2147483647', 'National ID', '2147483647', 'Lira', '', '2025-04-28 08:22:39', 'pending', NULL, NULL, 'pending', NULL),
(9, 'ivan', 'Ivan', 'Nyayo', 'nyayoi799@gmail.com', '$2y$10$lNjUv1BmTnjQ4zigkDlG2ObUWyAfhF1joWfP.jGJV1hGQeNEaAGVS', '+256764119058', 'National ID', '1234235342645', 'Plot 11 Oyite Ojok Lane', '', '2025-05-06 20:27:40', 'paid', '2025-05-09 13:48:54', '2025-10-07 17:11:57', 'active', '2025-05-09 14:48:54'),
(10, 'mafoxxin', 'Mafox', 'Nyayo', 'ivannyayo@gmail.com', '$2y$10$ogZBKAI8yQieXYwbS5fd/e0a87TAyvFYbFUmIrTwGM.7nfxMZfxdK', '+256764119058', 'National ID', '66575685678', 'Plot 11 Oyite Ojok Lane', 'uploads/profile_picture/default-profile.jpeg', '2025-05-06 21:50:27', 'paid', '2025-05-09 12:12:13', '2025-09-09 12:12:13', 'active', '2025-05-09 13:12:13'),
(11, 'daniel', 'Okot', 'Daniel', 'danielokot@gmail.com', '$2y$10$xHLbJvBmDK53XMNouigWn.KWpV0Ueh1o2wh8SVmUaw3NMfCxUV.L6', '+256764119058', 'National ID', '66575685678', 'Plot 11 Oyite Ojok Lane', 'uploads/profile_picture/default-profile.jpeg', '2025-05-09 13:21:14', 'paid', NULL, NULL, 'pending', NULL),
(12, 'jacksons', 'Okello ', 'Jackson', 'okellojackson@gmail.com', '$2y$10$GXNF/1NWb.mZpDEv3LJXKeM8OpI9qWc4BCVVVTEVkqYu/mqrllzdi', '+256764119058', 'National ID', '1234235342645', 'Plot 11 Oyite Ojok Lane', 'uploads/profile_picture/default-profile.jpeg', '2025-05-09 13:37:56', 'paid', NULL, NULL, 'pending', NULL),
(13, 'okwel', 'Okwel ', 'Moses', 'okwel@gmail.com', '$2y$10$VC6sSBVXD1JUjCdP27inyekkzKX4XpcpMZ1bUUN9lt4HcQfkXn5zO', '+256764119058', 'National ID', '1234235342645', 'Plot 11 Oyite Ojok Lane', 'uploads/profile_picture/default-profile.jpeg', '2025-05-09 14:27:36', 'paid', '2025-05-09 13:27:36', '2025-09-09 13:27:36', 'active', '2025-05-09 14:27:36'),
(14, 'testuser1746961050', 'Test', 'User', 'test1746961050@example.com', '$2y$10$.ktGPY/ZyWeayu.vHo3gUOgq/HUJiGKYN8Im8qRMnHx792DTh596G', '+256701234567', 'National ID', 'ID1746961050', 'Test Address', 'uploads/profile_picture/default-profile.jpeg', '2025-05-11 10:57:30', 'paid', '2025-05-11 09:57:30', '2025-09-11 09:57:30', 'active', '2025-05-11 10:57:30'),
(15, 'milton', 'Opollo ', 'Milton', 'miltonopollo@gmail.com', '$2y$10$0grdIPK87fbaPud9k4oyH.CDw2jlWTZmRuyWns2yNaqo/MwZB6Ai6', '+256764119058', 'National ID', '66575685678', 'Plot 11 Oyite Ojok Lane', 'uploads/profile_picture/default-profile.jpeg', '2025-05-11 11:09:53', 'paid', '2025-05-11 10:09:54', '2025-09-11 10:09:54', 'active', '2025-05-11 11:09:54');

-- --------------------------------------------------------

--
-- Table structure for table `property_ratings`
--

CREATE TABLE `property_ratings` (
  `rating_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `property_type` enum('rental','sale') NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_ratings`
--

INSERT INTO `property_ratings` (`rating_id`, `property_id`, `property_type`, `user_name`, `user_email`, `rating`, `review_text`, `created_at`) VALUES
(1, 28, 'rental', 'Ivan', '0', 3, 'Best service', '2025-04-21 21:29:00'),
(2, 27, 'rental', 'John', '0', 3, 'house', '2025-04-21 21:39:32');

-- --------------------------------------------------------

--
-- Table structure for table `rental_property`
--

CREATE TABLE `rental_property` (
  `property_id` int(11) NOT NULL,
  `property_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `landlord` varchar(255) NOT NULL,
  `security` varchar(255) NOT NULL,
  `utilities` varchar(255) NOT NULL,
  `property_type` varchar(255) NOT NULL,
  `convenience` varchar(50) NOT NULL,
  `amenities` varchar(255) NOT NULL,
  `property_class` varchar(255) NOT NULL,
  `property_size` int(255) NOT NULL,
  `bedrooms` int(100) NOT NULL DEFAULT 0,
  `bathrooms` int(100) NOT NULL DEFAULT 0,
  `parking` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `owner_id` int(255) UNSIGNED NOT NULL,
  `country` varchar(50) NOT NULL,
  `region` varchar(100) NOT NULL,
  `subregion` varchar(100) NOT NULL,
  `parish` varchar(100) NOT NULL,
  `ward` varchar(100) NOT NULL,
  `cell` varchar(100) NOT NULL,
  `manager_id` int(11) UNSIGNED NOT NULL,
  `images` text NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rental_property`
--

INSERT INTO `rental_property` (`property_id`, `property_name`, `description`, `price`, `landlord`, `security`, `utilities`, `property_type`, `convenience`, `amenities`, `property_class`, `property_size`, `bedrooms`, `bathrooms`, `parking`, `status`, `owner_id`, `country`, `region`, `subregion`, `parish`, `ward`, `cell`, `manager_id`, `images`, `date_created`) VALUES
(6, 'axe', '', 23456.00, 'resident', 'cctv', 'included', 'residential', 'private', 'water', 'hostel', 1200, 0, 0, 'yes', '', 3, 'uganda', 'northern', 'lira', 'kakoge', 'kakoge B', 'yes', 2, 'uploads/property10.jpeg', '2025-04-01 20:01:35'),
(12, 'dragons', '', 233456.00, 'resident', 'cctv,guards', 'not_included', 'flat', 'private', 'market,school', 'commercial', 3000, 0, 0, 'yse', '', 3, 'uganda', 'lira', 'lira city ', 'lira city west', 'kakoge', 'kakoge B', 1, 'REAL-ESTATE/REAL-ESTATE/uploads/rentals/67f6ad2059484.jpg,uploads/rentals/67fd851441995.jpeg,uploads/rentals/67fd851447dcf.jpeg,uploads/rentals/67fd85144e26b.png,uploads/rentals/67fd85144e664.jpeg', '2025-04-03 17:57:37'),
(13, 'Mecha', '', 12345678.00, 'non_resident', 'cctv,guards,electric_fence', 'not_included', 'semi_permanent', 'self_contained', 'market,school', 'hostel', 400, 0, 0, 'yse', '', 5, 'uganda', 'lira', 'lira city ', 'lira city west', 'kakoge', 'kakoge B', 2, 'uploads/rentals/67f6acdfe7470.png', '2025-04-03 18:11:28'),
(15, 'neyo', '', 56445.00, 'resident', 'security_lights', 'not_included', 'semi_permanent', 'private', 'market', 'commercial', 600, 0, 0, 'yse', '', 2, 'uganda', 'lira', 'lira city ', 'lira city west', 'kakoge', 'kakoge B', 2, 'uploads/rentals/67f6ac8d06dcb.png,uploads/rentals/67f6ac8d08ceb.png', '2025-04-04 12:07:59'),
(16, 'fox', '', 456.00, 'resident', 'alarm', 'not_included', 'permanent', 'private', 'market,school', 'residential', 300, 0, 0, 'yse', '', 1, 'uganda', 'lira', 'lira city ', 'lira city west', 'kakoge', 'kakoge B', 1, 'uploads/rentals/67f6ac5ee02eb.png,uploads/rentals/67f6ac5ee0553.png,uploads/rentals/67f6ac5ee0777.jpeg', '2025-04-04 12:14:42'),
(17, 'naayla', '', 5800.00, 'resident', 'security_lights', 'included', 'flat', 'crowded', 'market,school', 'residential', 500, 0, 0, 'yse', '', 2, 'uganda', 'lira', 'lira city ', 'lira city west', 'kakoge', 'kakoge B', 2, 'uploads/rentals/67f69bb726977.png,uploads/rentals/67f69bb726cef.jpeg,uploads/rentals/67f6a6e268cbc.png', '2025-04-04 12:29:07'),
(18, 'Amelieq', '', 23412.00, 'resident', 'guards', 'included', 'permanent', 'crowded', 'market', 'hotel', 3422, 0, 0, 'yse', '', 4, 'uganda', 'lira', 'lira city ', 'lira city west', 'kakoge', 'kakoge B', 2, 'uploads/rentals/67f6a33dcbd3f.jpg,uploads/rentals/67f6a39bc70d4.jpg', '2025-04-04 12:39:14'),
(20, 'trial', '', 232.00, 'resident', 'cctv,guards', 'included', 'flat', 'crowded', 'school', 'commercial', 345, 0, 0, 'yse', '', 2, 'uganda', 'lira', 'lira city ', 'lira city west', 'kakoge', 'kakoge B', 1, 'uploads/rentals/2025/04/67f2de7806599_contact.jpeg,uploads/rentals/2025/04/67f2de78070a8_21.jpeg,uploads/rentals/2025/04/67f2de7807947_contact.jpeg,uploads/rentals/2025/04/67f2de78080a8_21.jpeg', '2025-04-06 20:05:12'),
(21, 'Naaya Estatesss', '', 15000000.00, 'non_resident', 'cctv,guards,electric_fence', 'included', 'flat', 'self_contained', 'hospital', 'residential', 6000, 0, 0, 'yes', '', 1, 'uganda', 'Gulu', 'Gulu city', 'Gulu city west', 'Omoro', 'Omoro C', 4, 'REAL-ESTATE/uploads/rentals/67fd7a8e814c7.jpeg,REAL-ESTATE/uploads/rentals/67fd7a8e818a3.jpg,REAL-ESTATE/uploads/rentals/67fd7a8e81c2d.jpeg,REAL-ESTATE/uploads/rentals/67fd7a8e81fc8.jpeg,REAL-ESTATE/uploads/rentals/67fd7a8e8238b.jpeg,REAL-ESTATE/uploads/rentals/67fd7a8e8275b.jpeg', '2025-04-14 21:10:32'),
(22, 'Good News Hotel', '', 300000.00, 'non_resident', 'cctv', 'partial', 'flat', 'private', 'hospital', 'commercial', 400, 0, 0, 'yes', '', 5, 'uganda', 'lira', 'lira city ', 'lira city west', 'kakoge', 'kakoge B', 4, 'uploads/rentals/67fd7f4f3abbd.jpeg,uploads/rentals/67fd7f4f3afc1.jpg,uploads/rentals/67fd7f4f3b356.jpeg,uploads/rentals/67fd7f4f3b6dd.jpeg,uploads/rentals/67fd7f4f3bab0.jpeg,uploads/rentals/67fd7f4f3fbfd.jpeg,uploads/rentals/67fd7f4f40009.jpg,uploads/rentals/67fd7f4f4047d.jpeg,uploads/rentals/67fd7f4f40a2c.jpeg,uploads/rentals/67fd7f4f40e92.jpeg', '2025-04-14 21:34:07'),
(23, 'Jungle Apartments', '', 2000000.00, 'Resident', 'CCTV,Guards', 'Included', 'Semi-permanent', 'Crowded', 'Market', 'Commercial', 2000, 0, 0, 'Yes', '', 5, 'Uganda', 'Gulu', 'Gulu city', 'Gulu city west', 'Omoro', 'Omoro C', 4, 'uploads/rentals/67fe3b6d60d8e.jpeg,uploads/rentals/67fe3b6d6122a.jpeg', '2025-04-15 10:56:45'),
(24, 'Amulem Complex', '', 1500000.00, 'Non Resident', 'CCTV', 'Not Included', 'Flat', 'Crowded', 'Market', 'Commercial', 400, 0, 0, 'No', '', 2, 'Uganda', 'Kampala', 'Kira ', 'Kira West', 'Kira war', 'Kira cell', 4, 'REAL-ESTATE/uploads/rentals/67fe3c41a5d02.png,REAL-ESTATE/uploads/rentals/67fe3c41a60e9.jpg,REAL-ESTATE/uploads/rentals/67fe3c41a64d7.png,REAL-ESTATE/uploads/rentals/67fe3c41aa85e.png', '2025-04-15 11:00:17'),
(25, 'Angels Apartments', 'Just know', 400000.00, 'Resident', 'CCTV', 'Included', 'Flat', 'Private', 'School', 'Residential', 4000, 0, 0, 'Yes', 'Unavailable', 2, 'Uganda', 'lira', 'lira city ', 'lira city west', 'kakoge', 'kakoge B', 1, 'REAL-ESTATE/REAL-ESTATE/uploads/rentals/67fe5563ddd1d.jpeg,REAL-ESTATE/REAL-ESTATE/uploads/rentals/67fe5563de713.jpeg,REAL-ESTATE/REAL-ESTATE/uploads/rentals/67fe5563df095.jpeg,REAL-ESTATE/REAL-ESTATE/uploads/rentals/67fe5563dfa4b.jpeg,REAL-ESTATE/REAL-ESTATE/uploads/rentals/67fe5563e0435.jpeg', '2025-04-15 12:47:31'),
(26, 'Amazing Apartments ', 'aMA', 12000000.00, 'Resident', 'CCTV', 'Included', 'Flat', 'Private', 'Market', 'Residential', 6500, 0, 0, 'Available', 'Available', 5, 'Uganda', 'Kampala', 'Kira ', 'Kira West', 'Kira war', 'Kira cell', 2, 'REAL-ESTATE/uploads/rentals/680022e2cfab9.jpeg,REAL-ESTATE/uploads/rentals/680022e2cff27.jpeg,REAL-ESTATE/uploads/rentals/680022e2d0388.jpeg,REAL-ESTATE/uploads/rentals/680022e2d099a.jpeg,REAL-ESTATE/uploads/rentals/680022e2d0dde.jpeg', '2025-04-16 21:36:34'),
(27, 'American Apartments', 'Always determined', 6000000.00, 'Resident', 'CCTV', 'Not Included', 'Semi-permanent', 'Crowded', 'Yes', 'Commercial', 300, 4, 4, 'Available', 'Market', 4, 'Uganda', 'Gulu', 'Gulu city', 'Gulu city west', 'Omoro', 'Omoro C', 1, 'uploads/rentals/6800db12eb0f2.jpeg,uploads/rentals/6800db12eb556.jpeg,uploads/rentals/6800db12eb903.jpeg,uploads/rentals/6800db12ebcc5.jpeg,uploads/rentals/6800db12ec08e.jpeg', '2025-04-17 10:42:26'),
(28, 'California Apartments', 'All the best', 8500000.00, 'Resident', 'Security Lights', 'Included', 'Permanent', 'Crowded', 'Market', 'Hall', 700, 8, 9, 'No', 'Unavailable', 1, 'Uganda', 'lira', 'lira city ', 'lira city west', 'kakoge', 'kakoge B', 4, 'uploads/rentals/6800dce730724.jpeg,uploads/rentals/6800dce730b96.jpeg,uploads/rentals/6800dce735620.jpeg,uploads/rentals/6800dce7359f1.jpeg,uploads/rentals/6800dce735da6.jpeg', '2025-04-17 10:50:15'),
(29, 'Anyach Apartments', 'Detailed', 6000000.00, 'Resident', 'Guards', 'Included', 'Flat', 'Crowded', 'School', 'Hostel', 900, 5, 9, 'Yes', 'Available', 7, 'Uganda', 'lira', 'lira city ', 'lira city west', 'kakoge', 'kakoge B', 1, 'uploads/rentals/680f3c5476cde.jpeg,uploads/rentals/680f3c547750a.jpg,uploads/rentals/680f3c5477bcc.jpeg', '2025-04-28 08:29:08'),
(30, 'Omich Apartments', 'Its amazing', 12000000.00, 'Resident', 'Guards', 'Included', 'Permanent', 'Crowded', 'School', 'Residential', 5000, 4, 8, 'Yes', 'Available', 2, 'Uganda', 'lira', 'lira city ', 'lira city west', 'kakoge', 'kakoge B', 2, 'uploads/rentals/681b3a9bd9866.jpeg,uploads/rentals/681b3a9bd9f4f.jpg,uploads/rentals/681b3a9bda81b.jpeg,uploads/rentals/681b3a9bdaedc.jpeg,uploads/rentals/681b3a9bdb537.jpeg', '2025-05-07 10:48:59'),
(31, 'Mafox Apartments', 'FFFFFFFF', 6000000.00, 'Resident', 'CCTV,Guards', 'Included', 'Flat', 'Crowded', 'Market,School', 'Commercial', 700, 4, 1, 'Yes', 'Available', 10, 'Uganda', 'lira', 'lira city ', 'lira city west', 'kakoge', 'kakoge B', 4, 'uploads/rentals/681b3c6662a3d.jpeg,uploads/rentals/681b3c66632ec.jpeg,uploads/rentals/681b3c6663b05.jpeg,uploads/rentals/681b3c6664241.jpeg,uploads/rentals/681b3c6664a51.jpeg', '2025-05-07 10:56:38');

-- --------------------------------------------------------

--
-- Table structure for table `sales_property`
--

CREATE TABLE `sales_property` (
  `property_id` int(10) UNSIGNED NOT NULL,
  `property_name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` varchar(100) NOT NULL,
  `property_type` varchar(100) NOT NULL,
  `property_size` int(100) NOT NULL,
  `bedrooms` int(100) NOT NULL DEFAULT 0,
  `bathrooms` int(100) NOT NULL DEFAULT 0,
  `utilities` varchar(100) NOT NULL,
  `amenities` varchar(100) NOT NULL,
  `status` varchar(255) NOT NULL,
  `title` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `region` varchar(100) NOT NULL,
  `subregion` varchar(100) NOT NULL,
  `parish` varchar(100) NOT NULL,
  `ward` varchar(100) NOT NULL,
  `cell` varchar(100) NOT NULL,
  `owner_id` int(10) UNSIGNED NOT NULL,
  `manager_id` int(10) UNSIGNED NOT NULL,
  `images` text NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_property`
--

INSERT INTO `sales_property` (`property_id`, `property_name`, `description`, `price`, `property_type`, `property_size`, `bedrooms`, `bathrooms`, `utilities`, `amenities`, `status`, `title`, `country`, `region`, `subregion`, `parish`, `ward`, `cell`, `owner_id`, `manager_id`, `images`, `date_created`) VALUES
(1, 'monkey', '', 'cash', 'permanent', 2000, 0, 0, 'included', 'institution,hospital', '', 'yes', 'uganda', 'lira', 'lira city ', 'lira city west', 'kakoge', 'kakoge B', 1, 1, 'uploads/salesproperty2.jpg,uploads/sales/67f6e34582b9a.png,uploads/sales/67f6ed2c57352.jpeg,uploads/sales/67f6ed2c5550e.jpeg', '2025-04-09 21:58:09'),
(2, 'oyo', '', 'cash', 'flat', 300, 0, 0, 'included', 'market', '', 'yes', 'uganda', 'lira', 'lira city ', 'lira city west', 'kakoge', 'kakoge B', 1, 2, 'uploads/sales/67f6e378e40e7.png,uploads/sales/67f6e378e4560.png,uploads/sales/67f6e378e567a.jpeg', '2025-04-09 21:17:07'),
(3, 'mafox', '', 'installments', 'permanent', 23654, 0, 0, 'included', 'school', '', 'yes', 'uganda', 'lira', 'lira city ', 'lira city west', 'kakoge', 'kakoge B', 2, 1, 'uploads/sales/2025/04/67efe17c08648_21.jpeg,uploads/sales/2025/04/67efe17c09011_21.jpeg', '2025-04-04 13:41:16'),
(4, 'colla', '', 'cash', 'land', 3456, 0, 0, 'included', 'school,church', '', 'yes', 'uganda', 'lira', 'lira city ', 'lira city west', 'kakoge', 'kakoge B', 2, 2, 'uploads/sales/2025/04/67efe4c6c71df_21.jpeg,uploads/sales/2025/04/67efe4c6cc713_21.jpeg', '2025-04-04 13:55:18'),
(5, 'Edge Apartments', '', 'installments', 'flat', 400, 0, 0, 'included', 'market,hospital', '', 'no', 'uganda', 'Gulu', 'Gulu city', 'Gulu city west', 'Omoro', 'Omoro C', 2, 4, 'uploads/sales/67fd7ad4a36d8.png,uploads/sales/67fd7ad4a3b18.jpeg,uploads/sales/67fd7ad4a3f2d.jpg,uploads/sales/67fd7ad4a4326.jpeg,uploads/sales/67fd7ad4a46c6.png,uploads/sales/67fd7ad4a4b43.jpeg,uploads/sales/67fd7ad4acfef.png,uploads/sales/67fd7ad4ae3bd.jpeg,uploads/sales/67fd7ad4b2b1d.jpg,uploads/sales/67fd7ad4b6d6f.jpeg,uploads/sales/67fd7ad4b71f4.png,uploads/sales/67fd7ad4b761d.jpeg', '2025-04-14 21:15:00'),
(6, 'Skyz Apartmentsss', '', 'cash', 'ground', 8000, 0, 0, 'partial', 'institution,hospital', '', 'yes', 'uganda', 'Kampala', 'Kira ', 'Kira West', 'Kira war', 'Kira cell', 3, 1, 'uploads/sales/67fd7c5cbe97d.jpeg,uploads/sales/67fd7c5cc1e0e.jpg,uploads/sales/67fd7c5cc2406.jpeg,uploads/sales/67fd7c5cc2d75.jpeg,uploads/sales/67fd7c5cc337d.jpeg,uploads/sales/67fd7c5cc3814.jpeg,uploads/sales/67fd7c5cbc84e.jpeg,uploads/sales/67fd7c5cc3a94.jpg,uploads/sales/67fd7c5ccb650.jpeg,uploads/sales/67fd7c5cd3123.jpeg,uploads/sales/67fd7c5cd3725.jpeg,uploads/sales/67fd7c5cdac62.jpeg', '2025-04-14 22:18:08'),
(7, 'Gracious Palace', '', 'Cash', 'Flat', 300, 0, 0, 'Included', 'School', '', 'Yes', 'Uganda', 'Gulu', 'Gulu city', 'Gulu city west', 'Omoro', 'Omoro C', 2, 4, 'uploads/sales/67fe48d91cfff.jpeg,uploads/sales/67fe48d91d442.jpeg,uploads/sales/67fe48d91d861.jpeg,uploads/sales/67fe48d91dc36.png,uploads/sales/67fe48d92095f.jpeg', '2025-04-15 11:54:01'),
(8, 'monkey', 'If you have additional questions, concerns, or suggestions for improvement, we\'d love to hear them. Please reach out to us!', 'Installments', 'Flat', 900, 5, 9, 'Included', 'School', '', 'Yes', 'Uganda', 'lira', 'lira city ', 'lira city west', 'kakoge', 'kakoge B', 2, 4, 'uploads/sales/67fffa4929fde.jpeg,uploads/sales/67fffa492a653.jpeg,uploads/sales/67fffa492acf2.jpeg,uploads/sales/67fffa492b3e1.jpeg,uploads/sales/67fffa492ba91.jpeg', '2025-04-16 19:03:02'),
(9, 'lions', 'Best apartments in the area', 'Installments', 'Permanent', 10, 4, 0, 'Included', 'School', '3000', 'Available', 'Uganda', 'lira', 'lira city ', 'lira city west', 'kakoge', 'kakoge B', 5, 1, 'uploads/sales/680015d054534.jpeg,uploads/sales/680015d07df0a.jpeg,uploads/sales/680015d07e2d4.jpeg,uploads/sales/680015d098617.jpeg,uploads/sales/680015d098a48.jpeg', '2025-04-16 20:40:48'),
(10, 'Jogo Apartments', 'Affordabe settlement in the country', 'Installments', 'Flat', 6000, 4, 5, 'Included', 'School', 'Available', 'Yes', 'Uganda', 'Gulu', 'Gulu city', 'Gulu city west', 'Omoro', 'Omoro C', 5, 4, 'uploads/sales/680016e2c417f.jpeg,uploads/sales/680016e2c46e9.jpeg,uploads/sales/680016e2c4aea.jpeg,uploads/sales/680016e2c4edc.jpeg,uploads/sales/680016e2c52ac.jpeg', '2025-04-16 20:46:57'),
(12, 'Los Angelos Apartments', 'Just know', 'Cash', 'Semi Permanent', 100, 4, 6, 'Included', 'School', 'Available', 'Yes', 'Uganda', 'Kampala', 'Kira ', 'Kira West', 'Kira war', 'Kira cell', 1, 1, 'uploads/sales/6800ddd98cf52.jpeg,uploads/sales/6800ddd98d3cb.jpeg,uploads/sales/6800ddd98d89b.jpeg,uploads/sales/6800ddd98dcd5.jpeg,uploads/sales/6800ddd98e1ce.jpeg', '2025-04-17 10:54:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `owner_payments`
--
ALTER TABLE `owner_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `owner_subscriptions`
--
ALTER TABLE `owner_subscriptions`
  ADD PRIMARY KEY (`subscription_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `owner_subscription_notes`
--
ALTER TABLE `owner_subscription_notes`
  ADD PRIMARY KEY (`note_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `property_manager`
--
ALTER TABLE `property_manager`
  ADD PRIMARY KEY (`manager_id`);

--
-- Indexes for table `property_owner`
--
ALTER TABLE `property_owner`
  ADD PRIMARY KEY (`owner_id`);

--
-- Indexes for table `property_ratings`
--
ALTER TABLE `property_ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `property_id` (`property_id`,`property_type`),
  ADD KEY `rating` (`rating`);

--
-- Indexes for table `rental_property`
--
ALTER TABLE `rental_property`
  ADD PRIMARY KEY (`property_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `sales_property`
--
ALTER TABLE `sales_property`
  ADD PRIMARY KEY (`property_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `owner_payments`
--
ALTER TABLE `owner_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `owner_subscriptions`
--
ALTER TABLE `owner_subscriptions`
  MODIFY `subscription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `owner_subscription_notes`
--
ALTER TABLE `owner_subscription_notes`
  MODIFY `note_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_manager`
--
ALTER TABLE `property_manager`
  MODIFY `manager_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `property_owner`
--
ALTER TABLE `property_owner`
  MODIFY `owner_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `property_ratings`
--
ALTER TABLE `property_ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rental_property`
--
ALTER TABLE `rental_property`
  MODIFY `property_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `sales_property`
--
ALTER TABLE `sales_property`
  MODIFY `property_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `owner_payments`
--
ALTER TABLE `owner_payments`
  ADD CONSTRAINT `owner_payments_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `property_owner` (`owner_id`) ON DELETE CASCADE;

--
-- Constraints for table `owner_subscriptions`
--
ALTER TABLE `owner_subscriptions`
  ADD CONSTRAINT `owner_subscriptions_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `property_owner` (`owner_id`) ON DELETE CASCADE;

--
-- Constraints for table `owner_subscription_notes`
--
ALTER TABLE `owner_subscription_notes`
  ADD CONSTRAINT `owner_subscription_notes_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `property_owner` (`owner_id`) ON DELETE CASCADE;

--
-- Constraints for table `rental_property`
--
ALTER TABLE `rental_property`
  ADD CONSTRAINT `rental_property_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `property_owner` (`owner_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rental_property_ibfk_2` FOREIGN KEY (`manager_id`) REFERENCES `property_manager` (`manager_id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_property`
--
ALTER TABLE `sales_property`
  ADD CONSTRAINT `sales_property_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `property_owner` (`owner_id`),
  ADD CONSTRAINT `sales_property_ibfk_2` FOREIGN KEY (`manager_id`) REFERENCES `property_manager` (`manager_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
