-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 26, 2026 at 02:20 PM
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
-- Database: `makueni_digital_hub`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `centre_id` int(10) UNSIGNED DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'centre_admin',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `must_change_password` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `centre_id`, `role`, `status`, `created_at`, `must_change_password`) VALUES
(1, 'superadmin', '$2y$10$YMbwOFMfWPCNvJ/BQ7e9j.3zHcTZ5lKRFtkIx/rV9c2tYRHDRDJ7i', NULL, 'super_admin', 'active', '2026-06-08 07:37:45', 0),
(2, 'Wote', '$2y$10$awjuT6x.14EoZnDUlX74t.HKxrSeieQ3onjUAKhha6mcIdNGBe1ce', 18, 'super_admin', 'active', '2026-06-15 14:05:21', 0),
(3, 'kibwezi', '$2y$10$1UnYreMVj2WDLrRjYf8LMuz3YbXSMQT9B1NUAF0cshKCHl.xHX4Ay', 6, 'centre_admin', 'active', '2026-06-15 14:05:56', 0),
(4, 'UkiaSuperadmin', '$2y$10$IKpFr3XjDDc/nzT8SURk0.kC6SVqNOMujzC4ET7CfpfhyoV.XvwF2', 17, 'super_admin', 'active', '2026-06-15 14:09:32', 1),
(5, 'MulalaSuperadmin', '$2y$10$iV6pGwZVbj39GmLRoO3cr.vFABcaAyKeap4m.DQEGGsbEuahmOOy6', 12, 'super_admin', 'active', '2026-06-15 14:10:34', 1),
(17, 'admin1', '$2y$10$9IaS3fNtPPlrUI5s5F/FPOPokIra.vkv.6CtovXtF55h55ot6tng6', 1, 'super_admin', 'active', '2026-06-24 12:46:08', 0),
(18, 'Darajani', '$2y$10$PftqEwNHbHSA5YJOT8pOSeZxLL0VCsryLdSO1wVUt6aS4wpj.WJXS', 1, 'centre_admin', 'active', '2026-06-26 07:10:50', 1),
(19, 'Kalawa', '$2y$10$/oxAvrI6i9yXq4YxzlRkA.HuI7ZSbfreEe.dBiiiJUcbVyzj5zU5y', 2, 'centre_admin', 'active', '2026-06-26 07:11:21', 1),
(20, 'Kasikeu', '$2y$10$YnyYo3z2C1CiJ9xCG8md5OhPpfjc4pQmWHMYey6.pd6HZiGPlX0G.', 3, 'centre_admin', 'active', '2026-06-26 07:11:42', 0),
(21, 'woteadmin', '$2y$10$cHJq.Hgihfr86pvsSxJUDOmr4.pfImZB59Okw6sPoZ1MNqyf9x9aS', 18, 'centre_admin', 'active', '2026-06-26 09:41:38', 0);

-- --------------------------------------------------------

--
-- Table structure for table `admin_sessions`
--

CREATE TABLE `admin_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `admin_id` int(10) UNSIGNED NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `activity` varchar(255) DEFAULT NULL,
  `logout_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_sessions`
--

INSERT INTO `admin_sessions` (`id`, `admin_id`, `login_time`, `ip_address`, `activity`, `logout_time`) VALUES
(15, 1, '2026-06-16 11:29:13', '::1', 'Admin Logged In', '0000-00-00 00:00:00'),
(16, 1, '2026-06-16 11:50:22', '::1', 'Admin Logged In', '0000-00-00 00:00:00'),
(17, 1, '2026-06-23 07:48:24', '::1', 'Admin Logged Out', '2026-06-23 09:19:43'),
(18, 1, '2026-06-24 09:05:29', '::1', 'Admin Logged In', '0000-00-00 00:00:00'),
(19, 1, '2026-06-24 12:33:27', '::1', 'Admin Logged In', '0000-00-00 00:00:00'),
(20, 1, '2026-06-24 12:45:11', '::1', 'Admin Logged Out', '2026-06-26 07:11:49'),
(21, 17, '2026-06-24 13:21:15', '::1', 'Admin Logged Out', '2026-06-24 13:37:47'),
(22, 17, '2026-06-24 13:37:49', '::1', 'Admin Logged Out', '2026-06-24 13:38:40'),
(23, 17, '2026-06-24 13:38:49', '::1', 'Admin Logged Out', '2026-06-24 14:05:11'),
(24, 17, '2026-06-24 15:23:40', '::1', 'Admin Logged In', '0000-00-00 00:00:00'),
(25, 1, '2026-06-26 08:32:45', '::1', 'Admin Logged In', '0000-00-00 00:00:00'),
(26, 1, '2026-06-26 08:49:03', '::1', 'Admin Logged In', '0000-00-00 00:00:00'),
(27, 2, '2026-06-26 08:59:50', '::1', 'Admin Logged In', '0000-00-00 00:00:00'),
(28, 20, '2026-06-26 08:59:59', '::1', 'Admin Logged In', '0000-00-00 00:00:00'),
(29, 1, '2026-06-26 09:00:06', '::1', 'Admin Logged In', '0000-00-00 00:00:00'),
(30, 2, '2026-06-26 09:01:12', '::1', 'Admin Logged In', '0000-00-00 00:00:00'),
(31, 20, '2026-06-26 09:01:29', '::1', 'Admin Logged In', '0000-00-00 00:00:00'),
(32, 1, '2026-06-26 09:05:56', '::1', 'Admin Logged In', '0000-00-00 00:00:00'),
(33, 2, '2026-06-26 09:09:36', '::1', 'Admin Logged In', '0000-00-00 00:00:00'),
(34, 20, '2026-06-26 09:29:20', '::1', 'Admin Logged In', '0000-00-00 00:00:00'),
(35, 2, '2026-06-26 09:40:45', '::1', 'Admin Logged In', '0000-00-00 00:00:00'),
(36, 2, '2026-06-26 09:44:48', '::1', 'Admin Logged In', '0000-00-00 00:00:00'),
(37, 2, '2026-06-26 11:29:56', '::1', 'Admin Logged In', '0000-00-00 00:00:00'),
(38, 17, '2026-06-26 11:36:24', '::1', 'Admin Logged In', '0000-00-00 00:00:00'),
(39, 2, '2026-06-26 12:07:09', '::1', 'Admin Logged In', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `ict_centres`
--

CREATE TABLE `ict_centres` (
  `id` int(10) UNSIGNED NOT NULL,
  `centre_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ict_centres`
--

INSERT INTO `ict_centres` (`id`, `centre_name`, `created_at`) VALUES
(1, 'Darajani Community Information center', '2026-06-16 11:13:01'),
(2, 'Kalawa Community Information Center', '2026-06-16 11:13:01'),
(3, 'Kasikeu Community Information Center', '2026-06-16 11:13:01'),
(4, 'kathonzweni Community Information Center', '2026-06-16 11:13:01'),
(5, 'Kiangini Community Information Center', '2026-06-16 11:13:01'),
(6, 'Kibwezi Ajira Center', '2026-06-16 11:13:01'),
(7, 'Kikima Community Information Center', '2026-06-16 11:13:01'),
(8, 'Kisayani Ajira Centre', '2026-06-16 11:13:01'),
(9, 'Matiliku Community Information Center', '2026-06-16 11:13:01'),
(10, 'Mavindini Community Information Center', '2026-06-16 11:13:01'),
(11, 'Mtito Andei Community Information Center', '2026-06-16 11:13:01'),
(12, 'Mulala Community Information Center', '2026-06-16 11:13:01'),
(13, 'Nthongoni Community Information Center', '2026-06-16 11:13:01'),
(14, 'Nunguni Community Information Center', '2026-06-16 11:13:01'),
(15, 'Tawa Community Information Center', '2026-06-16 11:13:01'),
(16, 'Thange Community Information Center', '2026-06-16 11:13:01'),
(17, 'Ukia Community Infomation Center', '2026-06-16 11:13:01'),
(18, 'Wote Innovation Hub', '2026-06-16 11:13:01');

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `centre_id` int(10) UNSIGNED NOT NULL,
  `uploaded_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `centre_id` int(10) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `student_id`, `centre_id`, `rating`, `review`, `created_at`) VALUES
(2, 11, 18, 1, 'fdfvfv', '2026-06-24 15:42:27');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(10) UNSIGNED NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `centre_id` int(10) UNSIGNED NOT NULL,
  `registration_fee_status` varchar(50) DEFAULT 'pending',
  `training_fee_note` text DEFAULT NULL,
  `training_start_date` date DEFAULT current_timestamp(),
  `training_status` varchar(50) DEFAULT 'Upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','removed') DEFAULT 'active',
  `removal_reason` text DEFAULT NULL,
  `removed_at` timestamp NULL DEFAULT NULL,
  `completion_status` enum('incomplete','completed') DEFAULT 'incomplete'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `fullname`, `username`, `email`, `phone`, `password`, `centre_id`, `registration_fee_status`, `training_fee_note`, `training_start_date`, `training_status`, `created_at`, `status`, `removal_reason`, `removed_at`, `completion_status`) VALUES
(11, 'wote', 'wote', 'wote@gmail.com', '0712345678', '$2y$10$qfykXfAU.1s9T8JofAC0I..fk2OmeoE2KKeqwkHwwAGLh.p759SIK', 18, 'pending', 'Student will pay Ksh. 1000 certificate fee after completing training at the training centre', '2026-05-07', 'completed', '2026-06-16 11:23:37', 'active', NULL, NULL, 'completed'),
(12, 'testdata', 'test', 'tes@makueni.go.ke', '0712345678', '$2y$10$A11ZUwCL1THRVgOffli9suSnOAMUR0iBsRc8l6iDS/ycxTOTd4oxe', 18, 'pending', 'Student will pay Ksh. 1000 certificate fee after completing training at the training centre', '2026-07-27', 'Upcoming', '2026-06-22 13:09:41', 'active', NULL, NULL, 'incomplete'),
(13, 'Student 1', '@kasikeu', 'kasikeu@gmail.com', '0712345678', '$2y$10$8Ep6lBbsg7q/iurz/hvv8.2ws8dv1Z7VWjE/w1W6WdFzDHizmcOO6', 3, 'pending', NULL, '2026-06-26', 'in training', '2026-06-26 07:15:31', 'active', NULL, NULL, 'incomplete'),
(14, 'student 2', '@kasikeu1', 'student2@gmail.com', '0712345678', '$2y$10$Rm927T4dmQlJZGG2836Hq.TjZA2UQ2wQCuVAfalcaa7aFLhKU5RCi', 3, 'pending', NULL, '2026-06-26', 'in training', '2026-06-26 07:16:27', 'active', NULL, NULL, 'incomplete'),
(15, 'student 3', '@student3', 'student3@gmail.com', '0712345678', '$2y$10$AQGCrT4dKudKzK.GayUqwOWmqSIEIzTAt5etYgT8qsJ3tTmo9TLB.', 3, 'pending', 'Student will pay Ksh. 1000 certificate fee after completing training at the training centre', '2026-07-27', 'Upcoming', '2026-06-26 07:21:19', 'active', NULL, NULL, 'incomplete'),
(16, 'wote1', '@wotestudent', 'wotestudent@gmail.com', '0712345678', '$2y$10$tGKA.ZeMi1seUw2ctlNA5.e7HHSnHovj82sqaHUsqmn7gT6IHeomi', 18, 'pending', NULL, '2026-06-26', 'in training', '2026-06-26 09:45:56', 'active', NULL, NULL, 'incomplete');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(10) UNSIGNED NOT NULL,
  `action` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `admin_id`, `action`, `created_at`) VALUES
(1, 1, 'Updated completion status for 0 student(s) automatically after 5 weeks.', '2026-06-24 12:42:27'),
(2, 1, 'Updated completion status for 0 student(s) automatically after 5 weeks.', '2026-06-24 12:42:38'),
(3, 1, 'Updated completion status for 1 student(s) automatically after 5 weeks.', '2026-06-24 12:43:59'),
(4, 20, 'Updated completion status for 0 student(s) automatically after 5 weeks.', '2026-06-26 09:37:26'),
(5, 20, 'Updated completion status for 0 student(s) automatically after 5 weeks.', '2026-06-26 09:38:48'),
(6, 20, 'Updated completion status for 0 student(s) automatically after 5 weeks.', '2026-06-26 09:39:39'),
(7, 20, 'Updated completion status for 0 student(s) automatically after 5 weeks.', '2026-06-26 09:39:43'),
(8, 2, 'Updated completion status for 0 student(s) automatically after 5 weeks.', '2026-06-26 09:40:50'),
(9, 21, 'Updated completion status for 0 student(s) automatically after 5 weeks.', '2026-06-26 09:42:20'),
(10, 2, 'Updated completion status for 0 student(s) automatically after 5 weeks.', '2026-06-26 09:44:51'),
(11, 2, 'Updated completion status for 0 student(s) automatically after 5 weeks.', '2026-06-26 09:46:05'),
(12, 2, 'Updated completion status for 0 student(s) automatically after 5 weeks.', '2026-06-26 09:46:49'),
(13, 2, 'Updated completion status for 0 student(s) automatically after 5 weeks.', '2026-06-26 09:48:02'),
(14, 2, 'Updated completion status for 2 student(s) automatically after 5 weeks.', '2026-06-26 09:57:15'),
(15, 2, 'Updated completion status for 0 student(s) automatically after 5 weeks.', '2026-06-26 10:02:32'),
(16, 2, 'Updated completion status for 0 student(s) automatically after 5 weeks.', '2026-06-26 10:02:47'),
(17, 2, 'Updated completion status for 0 student(s) automatically after 5 weeks.', '2026-06-26 11:23:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_admins_centre` (`centre_id`),
  ADD KEY `centre_id` (`centre_id`),
  ADD KEY `id` (`id`),
  ADD KEY `role` (`role`);

--
-- Indexes for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_sessions_admin` (`admin_id`);

--
-- Indexes for table `ict_centres`
--
ALTER TABLE `ict_centres`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notes_centre` (`centre_id`),
  ADD KEY `idx_notes_uploaded_by` (`uploaded_by`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reviews_student` (`student_id`),
  ADD KEY `idx_reviews_centre` (`centre_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_students_centre` (`centre_id`),
  ADD KEY `centre_id` (`centre_id`),
  ADD KEY `status` (`status`),
  ADD KEY `completion_status` (`completion_status`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `ict_centres`
--
ALTER TABLE `ict_centres`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `fk_admins_centre` FOREIGN KEY (`centre_id`) REFERENCES `ict_centres` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  ADD CONSTRAINT `fk_admin_sessions_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `fk_notes_admin` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notes_centre` FOREIGN KEY (`centre_id`) REFERENCES `ict_centres` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_centre` FOREIGN KEY (`centre_id`) REFERENCES `ict_centres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_centre` FOREIGN KEY (`centre_id`) REFERENCES `ict_centres` (`id`);

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
