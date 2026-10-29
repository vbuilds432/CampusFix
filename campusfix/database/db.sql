-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 01, 2026 at 09:54 AM
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
-- Database: `campusfix`
--
CREATE DATABASE IF NOT EXISTS `campusfix` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `campusfix`;

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `complaint_id`, `staff_id`, `assigned_by`, `assigned_at`) VALUES
(1, 1, 8, 1, '2026-04-17 07:45:54'),
(4, 3, 11, 1, '2026-04-17 08:16:15'),
(8, 4, 11, 1, '2026-04-17 08:58:04'),
(9, 5, 8, 1, '2026-04-17 09:03:34'),
(10, 7, 8, 1, '2026-04-18 15:39:54'),
(11, 8, 11, 1, '2026-04-18 16:19:16'),
(12, 9, 8, 1, '2026-04-19 16:45:26'),
(14, 2, 8, 1, '2026-04-22 11:04:23');

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` enum('Complaint Proof','Resolution Proof') NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attachments`
--

INSERT INTO `attachments` (`id`, `complaint_id`, `file_name`, `file_path`, `file_type`, `uploaded_by`, `uploaded_at`) VALUES
(1, 4, 'Screenshot 2026-04-17 135212.png', 'uploads/resolutions/res_69e1ee28617bb7.01590997.png', 'Resolution Proof', 11, '2026-04-17 08:24:08'),
(2, 4, 'Screenshot 2026-04-17 135212.png', 'uploads/resolutions/res_69e1efe6a92e74.14721123.png', 'Resolution Proof', 11, '2026-04-17 08:31:34'),
(3, 4, 'Screenshot 2026-04-17 135212.png', 'uploads/resolutions/res_69e1f039a00ca2.26916014.png', 'Resolution Proof', 11, '2026-04-17 08:32:57'),
(4, 5, 'Screenshot 2026-04-17 143041.png', 'uploads/proofs/proof_69e1f7065d4642.66142193.png', 'Complaint Proof', 9, '2026-04-17 09:01:58'),
(5, 5, 'tube light.pdf', 'uploads/resolutions/res_69e1f800067f90.70912850.pdf', 'Resolution Proof', 8, '2026-04-17 09:06:08'),
(6, 6, 'water leakage.png', 'uploads/proofs/proof_69e1f9574771a0.24561003.png', 'Complaint Proof', 10, '2026-04-17 09:11:51');

-- --------------------------------------------------------

--
-- Table structure for table `building`
--

CREATE TABLE `building` (
  `id` int(11) NOT NULL,
  `campus_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `building`
--

INSERT INTO `building` (`id`, `campus_id`, `name`, `status`) VALUES
(1, 1, 'EC Department', 'Active'),
(2, 1, 'Computer Department', 'Active'),
(4, 2, 'Civil Department', 'Active'),
(5, 5, 'Computer Department', 'Active'),
(6, 2, 'Computer Department', 'Active'),
(7, 4, 'Mechanical Department', 'Active'),
(8, 4, 'Electrical department', 'Active'),
(9, 4, 'Computer Department', 'Active'),
(10, 4, 'EC Department', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `campus`
--

CREATE TABLE `campus` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campus`
--

INSERT INTO `campus` (`id`, `name`, `status`) VALUES
(1, 'GEC Bhavnagar', 'Active'),
(2, 'GEC Rajkot', 'Active'),
(4, 'GEC Gandhinagar', 'Active'),
(5, 'MS University', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `complaint_id_str` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `category_id` int(11) NOT NULL,
  `other_category` varchar(150) DEFAULT NULL,
  `campus_id` int(11) NOT NULL,
  `building_name` varchar(150) NOT NULL,
  `spot_name` varchar(150) NOT NULL,
  `exact_location` varchar(255) NOT NULL,
  `priority` enum('High','Medium','Low') NOT NULL,
  `status` enum('Submitted','Verified','Assigned','In Progress','Resolved','Closed','Reopened','Forwarded') NOT NULL DEFAULT 'Submitted',
  `approval_status` enum('Pending','Approved','Rejected') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `assigned_to` int(11) DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `complaint_id_str`, `user_id`, `title`, `description`, `category_id`, `other_category`, `campus_id`, `building_name`, `spot_name`, `exact_location`, `priority`, `status`, `approval_status`, `created_at`, `updated_at`, `assigned_to`, `assigned_at`) VALUES
(1, 'CMP_001', 4, 'Classroom projector not working', 'The projector installed in Room 101 is not turning on during lectures. Faculty members are unable to display presentations, which is affecting teaching. The power supply has been checked, but the issue still persists. Kindly resolve this at the earliest.', 8, 'IT / Equipment', 2, 'Computer Department', 'Room no. 101', 'room no.101 CE department', 'Medium', 'Closed', NULL, '2026-04-17 07:44:44', '2026-04-17 07:47:33', 8, NULL),
(2, 'CMP_002', 4, 'Fan not working in lab', 'Fan stopped working during practical session.', 1, '', 1, 'EC Department', 'Lab 1', 'last row&#039;s 1fan from entrance in last row', 'Medium', 'Assigned', NULL, '2026-04-17 07:49:36', '2026-04-22 11:04:23', 8, '2026-04-22 11:04:23'),
(3, 'CMP_003', 10, 'internet not working in lab', 'Internet was fixed but still unstable.', 8, 'Network', 4, 'Mechanical Department', 'Lab 1', 'lab 1 mechanical department Gec gandhinagar', 'High', 'Closed', 'Approved', '2026-04-17 07:58:03', '2026-04-18 16:02:56', 11, NULL),
(4, 'CMP_004', 9, 'Electrical Panel Overheating', 'Electrical panel is overheating and smells like burning wires.', 7, '', 5, 'Computer Department', 'Lab - 2', 'Near switchboard', 'High', 'Closed', 'Approved', '2026-04-17 08:22:15', '2026-04-18 16:02:08', 11, NULL),
(5, 'CMP_005', 9, 'Broken Tube Light', 'Tube light is flickering continuously. Attached image shows the issue clearly.', 2, '', 5, 'Computer Department', 'classroom 2', 'class 2', 'High', 'Closed', NULL, '2026-04-17 09:01:58', '2026-04-18 16:01:13', 8, NULL),
(6, 'CMP_006', 10, 'Water Leakage Near Switch Board', 'Water leakage observed near switchboard. Very dangerous situation.', 5, '', 4, 'Mechanical Department', 'Lab 1', 'Near drainage pipe, lab 1', 'High', 'Submitted', NULL, '2026-04-17 09:11:51', '2026-04-17 09:11:51', NULL, NULL),
(7, 'CMP_007', 9, 'Unclean Washroom in Civil Department', 'The washroom on the ground floor of the Civil Department is not cleaned regularly. There is water accumulation on the floor, and the condition is unhygienic. This is causing discomfort for students and needs immediate cleaning and maintenance.', 8, 'Hygiene', 2, 'Civil Department', 'Ground Floor Washroom', 'Ground Floor Washroom', 'Medium', 'In Progress', NULL, '2026-04-18 15:38:42', '2026-04-18 15:40:36', 8, NULL),
(8, 'CMP_008', 10, 'Broken Window Glass in Classroom', 'Window glass in Room 416 is broken, allowing dust and insects to enter the classroom. It needs replacement.', 8, 'infrastructure', 1, 'EC Department', 'Classroom 416', 'last row', 'Medium', 'Resolved', NULL, '2026-04-18 16:18:46', '2026-04-18 16:34:21', 11, NULL),
(9, 'CMP_009', 4, 'rack in Classroom Wall', 'There is a visible crack on the wall of Room No. 2 on the computer Department building. The crack has been increasing in size over the past few days and pieces of plaster have started falling off. This may cause safety issues for students attending lectures in this classroom. Immediate inspection and repair are required to avoid any accident.', 3, '', 5, 'Computer Department', 'classroom 2', 'class 2', 'Medium', 'Assigned', NULL, '2026-04-19 15:43:39', '2026-04-19 16:45:26', 8, '2026-04-19 16:45:26'),
(10, 'CMP_010', 9, 'broken bechese', '2 bacheses are broken in classroom 101', 4, '', 2, 'Computer Department', 'Room no. 101', 'last two', 'Low', 'Reopened', 'Pending', '2026-04-22 06:30:23', '2026-04-22 06:32:54', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `complaint_categories`
--

CREATE TABLE `complaint_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaint_categories`
--

INSERT INTO `complaint_categories` (`id`, `name`, `is_active`) VALUES
(1, 'Fan not working', 1),
(2, 'Light not working', 1),
(3, 'Wall damage', 1),
(4, 'Furniture broken', 1),
(5, 'Water leakage', 1),
(6, 'Washroom issue', 1),
(7, 'Electrical fault', 1),
(8, 'Other', 1);

-- --------------------------------------------------------

--
-- Table structure for table `complaint_history`
--

CREATE TABLE `complaint_history` (
  `id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `remarks` text DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaint_history`
--

INSERT INTO `complaint_history` (`id`, `complaint_id`, `status`, `remarks`, `updated_by`, `timestamp`) VALUES
(1, 1, 'Submitted', 'Initial Submission', 4, '2026-04-17 07:44:44'),
(2, 1, 'Assigned', 'Assigned to Amit Parmar', 1, '2026-04-17 07:45:54'),
(3, 1, 'Resolved', 'assigned new projector to room number 101', 8, '2026-04-17 07:46:40'),
(4, 1, 'Closed', 'done', 1, '2026-04-17 07:47:33'),
(5, 2, 'Submitted', 'Initial Submission', 4, '2026-04-17 07:49:36'),
(6, 3, 'Submitted', 'Initial Submission', 10, '2026-04-17 07:58:03'),
(7, 3, 'Assigned', 'Assigned to Jignesh Vala', 1, '2026-04-17 07:58:55'),
(8, 3, 'In Progress', 'assigned technician for it', 11, '2026-04-17 08:01:36'),
(9, 3, 'Resolved', 'solved network issue', 11, '2026-04-17 08:02:19'),
(10, 3, 'Closed', 'solved', 1, '2026-04-17 08:03:01'),
(11, 3, 'Reopened', 'still issue with slow internet speed', 10, '2026-04-17 08:03:28'),
(12, 3, 'Resolved', 'solved network issue', 11, '2026-04-17 08:03:36'),
(13, 3, 'Closed', 'solved', 1, '2026-04-17 08:03:53'),
(14, 3, 'Reopened', 'still issue with slow internet speed', 10, '2026-04-17 08:12:46'),
(15, 3, 'Approved', 'Reopen approved by admin', 1, '2026-04-17 08:14:27'),
(16, 3, 'Assigned', 'Assigned to Jignesh Vala', 1, '2026-04-17 08:15:05'),
(17, 3, 'Resolved', 'replaced old LAN Cables with new ones', 11, '2026-04-17 08:15:41'),
(18, 3, 'Assigned', 'Assigned to Jignesh Vala', 1, '2026-04-17 08:16:15'),
(19, 3, 'Closed', 'done', 1, '2026-04-17 08:16:57'),
(20, 4, 'Submitted', 'Initial Submission', 9, '2026-04-17 08:22:15'),
(21, 4, 'Assigned', 'Assigned to Amit Parmar', 1, '2026-04-17 08:22:37'),
(22, 4, 'Verified', 'verified panel', 1, '2026-04-17 08:23:04'),
(23, 4, 'Assigned', 'Assigned to Jignesh Vala', 1, '2026-04-17 08:23:19'),
(24, 4, 'Resolved', 'new panel replaced', 11, '2026-04-17 08:24:08'),
(25, 4, 'Assigned', 'Assigned to Jignesh Vala', 1, '2026-04-17 08:24:17'),
(26, 4, 'Resolved', 'new panel replaced', 11, '2026-04-17 08:31:34'),
(27, 4, 'Closed', 'done', 1, '2026-04-17 08:32:14'),
(28, 4, 'Reopened', 'new panels are having problem with wiring', 9, '2026-04-17 08:32:53'),
(29, 4, 'Resolved', 'new panel replaced', 11, '2026-04-17 08:32:57'),
(30, 4, 'Closed', 'done', 1, '2026-04-17 08:33:07'),
(31, 4, 'Reopened', 'new panels are having problem with wiring', 9, '2026-04-17 08:55:16'),
(32, 4, 'Closed', 'Reopen request rejected by admin (from dashboard)', 1, '2026-04-17 08:55:30'),
(33, 4, 'Reopened', 'new panels are having problem with wiring', 9, '2026-04-17 08:56:00'),
(34, 4, 'Closed', 'Reopen request rejected by admin', 1, '2026-04-17 08:56:15'),
(35, 4, 'Reopened', 'new panels are having problem with wiring', 9, '2026-04-17 08:56:19'),
(36, 4, 'Closed', 'Reopen request rejected by admin', 1, '2026-04-17 08:56:44'),
(37, 4, 'Reopened', 'new panels are having problem with wiring', 9, '2026-04-17 08:56:50'),
(38, 4, 'Approved', 'Reopen approved by admin (from dashboard)', 1, '2026-04-17 08:57:04'),
(39, 4, 'Assigned', 'Assigned to Jignesh Vala', 1, '2026-04-17 08:58:04'),
(40, 4, 'In Progress', 'done', 11, '2026-04-17 08:58:32'),
(41, 4, 'Resolved', 'done', 11, '2026-04-17 08:58:38'),
(42, 4, 'Closed', 'doen', 1, '2026-04-17 08:59:03'),
(43, 5, 'Submitted', 'Initial Submission', 9, '2026-04-17 09:01:58'),
(44, 5, 'Verified', 'broken tubelight', 1, '2026-04-17 09:03:31'),
(45, 5, 'Assigned', 'Assigned to Amit Parmar', 1, '2026-04-17 09:03:34'),
(46, 5, 'In Progress', 'assigned cleaner to clean pieaces of glass', 8, '2026-04-17 09:04:13'),
(47, 5, 'Resolved', 'replaced new tube light', 8, '2026-04-17 09:06:08'),
(48, 5, 'Closed', 'new tube light class 2', 1, '2026-04-17 09:07:06'),
(49, 6, 'Submitted', 'Initial Submission', 10, '2026-04-17 09:11:51'),
(50, 7, 'Submitted', 'Initial Submission', 9, '2026-04-18 15:38:42'),
(51, 7, 'Verified', 'uncleaned washroom', 1, '2026-04-18 15:39:52'),
(52, 7, 'Assigned', 'Assigned to Amit Parmar', 1, '2026-04-18 15:39:54'),
(53, 7, 'In Progress', 'cleaner assigned', 8, '2026-04-18 15:40:36'),
(54, 8, 'Submitted', 'Initial Submission', 10, '2026-04-18 16:18:46'),
(55, 8, 'Verified', 'ok', 1, '2026-04-18 16:19:12'),
(56, 8, 'Assigned', 'Assigned to Jignesh Vala', 1, '2026-04-18 16:19:16'),
(57, 8, 'In Progress', 'new glass will arrive tomorrow', 11, '2026-04-18 16:19:43'),
(58, 8, 'Resolved', 'new glass is fitted', 11, '2026-04-18 16:34:21'),
(59, 9, 'Submitted', 'Initial Submission', 4, '2026-04-19 15:43:39'),
(60, 9, 'Verified', 'ok', 1, '2026-04-19 16:44:59'),
(61, 9, 'Assigned', 'Assigned to Amit Parmar', 1, '2026-04-19 16:45:26'),
(62, 10, 'Submitted', 'Initial Submission', 9, '2026-04-22 06:30:23'),
(63, 10, 'Verified', 'ok', 1, '2026-04-22 06:31:38'),
(64, 10, 'Assigned', 'Assigned to Amit Parmar', 1, '2026-04-22 06:31:41'),
(65, 10, 'In Progress', 'in progress', 8, '2026-04-22 06:31:54'),
(66, 10, 'Resolved', 'replace new benchese', 8, '2026-04-22 06:32:06'),
(67, 10, 'Closed', 'done', 1, '2026-04-22 06:32:19'),
(68, 10, 'Reopened', '1 more bench is broken', 9, '2026-04-22 06:32:54'),
(69, 2, 'Verified', 'ok', 1, '2026-04-22 11:04:14'),
(70, 2, 'Assigned', 'Assigned to Amit Parmar', 1, '2026-04-22 11:04:23');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'Admin'),
(2, 'Staff'),
(3, 'Student');

-- --------------------------------------------------------

--
-- Table structure for table `spot`
--

CREATE TABLE `spot` (
  `id` int(11) NOT NULL,
  `building_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spot`
--

INSERT INTO `spot` (`id`, `building_id`, `name`, `status`) VALUES
(1, 1, 'Classroom 416', 'Active'),
(2, 1, 'Lab 1', 'Active'),
(3, 1, 'Washroom', 'Active'),
(6, 5, 'Lab - 1', 'Active'),
(7, 5, 'Lab - 2', 'Active'),
(8, 5, 'classroom 1', 'Active'),
(9, 5, 'classroom 2', 'Active'),
(10, 6, 'Room no. 101', 'Active'),
(11, 6, 'Room no. 102', 'Active'),
(12, 6, 'Lab 1', 'Active'),
(13, 6, 'Lab 2', 'Active'),
(14, 6, 'Washroom 1', 'Active'),
(15, 6, 'Washroom 2', 'Active'),
(16, 2, 'Room  no.401', 'Active'),
(17, 2, 'Room no. 402', 'Active'),
(18, 2, 'Lab 1', 'Active'),
(19, 2, 'Lab 2', 'Active'),
(20, 2, 'Washroom 1', 'Active'),
(21, 2, 'Washroom 2', 'Active'),
(22, 9, 'Lab 1', 'Active'),
(23, 9, 'Lab 2', 'Active'),
(24, 9, 'Room no. 101', 'Active'),
(25, 10, 'Washroom 1', 'Active'),
(26, 8, 'Room no. 101', 'Active'),
(27, 7, 'Lab 1', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `approval_status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role_id`, `approval_status`, `created_at`) VALUES
(1, 'Admin User', 'admin@campusfix.com', '$2y$10$21l597xnLTHucyIYM1bAouGM6dVKhpSfAqcSlPQHO4rvstCaJU0Wi', 1, 'Approved', '2026-04-13 16:10:09'),
(4, 'Vaibhav vala', 'valavaibhav315@gmail.com', '$2y$10$xNju7Wgp7u6a2Ibqt31tZeP2aWOj8ugPDVUoY0waUccaCjCZqsRXi', 3, 'Approved', '2026-04-13 16:11:13'),
(8, 'Amit Parmar', 'amit123@gmail.com', '$2y$10$bMV.BnGUosUDkQgFQ.a4m.4QnfKxh7xCh39SOyhFTrQYZIeeuM/8a', 2, 'Approved', '2026-04-13 16:40:41'),
(9, 'kush vaghani', 'kush@gmail.com', '$2y$10$Z3lhwOMORFkbrBf8XGoLl.zZHaraibR2abfrJe8nAPtYd2Kk3ixem', 3, 'Approved', '2026-04-15 15:11:31'),
(10, 'Arjun parmar', 'arjun@gmail.com', '$2y$10$q75dPebIlxey/OzlXWoOPOKgDTpa3YgJEfLD1Z0cEZw8sDypLjkla', 3, 'Approved', '2026-04-15 16:17:01'),
(11, 'Jignesh Vala', 'jignesh@gmail.com', '$2y$10$IOo83Xns8nT0pMnwYhSsCuakDPzXRRbHki6nMjH7P8n4/Rue2q6iC', 2, 'Approved', '2026-04-15 16:58:55'),
(12, 'Parth Kher', 'parth@gmail.com', '$2y$10$mZ./mVHNgYrskrEmfdP9iulKm6QH/.N2IO52IHSZFoknOjtQhyZum', 2, 'Pending', '2026-04-17 09:13:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assign_comp_id` (`complaint_id`),
  ADD KEY `assign_staff_id` (`staff_id`);

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attach_comp_id` (`complaint_id`),
  ADD KEY `attach_ibfk_user` (`uploaded_by`);

--
-- Indexes for table `building`
--
ALTER TABLE `building`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campus_id` (`campus_id`);

--
-- Indexes for table `campus`
--
ALTER TABLE `campus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_campus_name` (`name`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `complaint_id_str` (`complaint_id_str`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `campus_id` (`campus_id`),
  ADD KEY `building_id` (`building_name`),
  ADD KEY `spot_id` (`spot_name`),
  ADD KEY `comp_ibfk_staff` (`assigned_to`);

--
-- Indexes for table `complaint_categories`
--
ALTER TABLE `complaint_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `complaint_history`
--
ALTER TABLE `complaint_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `history_comp_id` (`complaint_id`),
  ADD KEY `history_user_id` (`updated_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `spot`
--
ALTER TABLE `spot`
  ADD PRIMARY KEY (`id`),
  ADD KEY `building_id` (`building_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `building`
--
ALTER TABLE `building`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `campus`
--
ALTER TABLE `campus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `complaint_categories`
--
ALTER TABLE `complaint_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `complaint_history`
--
ALTER TABLE `complaint_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `spot`
--
ALTER TABLE `spot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assign_ibfk_comp` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assign_ibfk_staff` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `attachments`
--
ALTER TABLE `attachments`
  ADD CONSTRAINT `attach_ibfk_comp` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attach_ibfk_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `building`
--
ALTER TABLE `building`
  ADD CONSTRAINT `building_ibfk_1` FOREIGN KEY (`campus_id`) REFERENCES `campus` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `comp_ibfk_cam` FOREIGN KEY (`campus_id`) REFERENCES `campus` (`id`),
  ADD CONSTRAINT `comp_ibfk_cat` FOREIGN KEY (`category_id`) REFERENCES `complaint_categories` (`id`),
  ADD CONSTRAINT `comp_ibfk_staff` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `comp_ibfk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `complaint_history`
--
ALTER TABLE `complaint_history`
  ADD CONSTRAINT `hist_ibfk_comp` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hist_ibfk_user` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `spot`
--
ALTER TABLE `spot`
  ADD CONSTRAINT `spot_ibfk_1` FOREIGN KEY (`building_id`) REFERENCES `building` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
--

COMMIT;

