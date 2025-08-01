-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 11, 2025 at 06:30 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create the project database
DROP DATABASE IF EXISTS `questiontest`;
CREATE DATABASE `questiontest`;
USE `questiontest`;

-- 1. USERS TABLE
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) UNIQUE NOT NULL,
  `phone_number` VARCHAR(8) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `reset_token` VARCHAR(255) NULL,
  `reset_token_expiration` DATETIME NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_points`
--

INSERT INTO `user_points` (`user_id`, `points`) VALUES
(2, 25),
(3, 60);

-- --------------------------------------------------------

--
-- Table structure for table `user_powerups`
--

CREATE TABLE `user_powerups` (
  `user_id` int(11) NOT NULL,
  `powerup_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_powerups`
--

INSERT INTO `user_powerups` (`user_id`, `powerup_id`, `quantity`) VALUES
(2, 1, 0),
(2, 3, 0),
(3, 1, 0),
(3, 4, 0),
(3, 5, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_progress`
--

CREATE TABLE `user_progress` (
  `progress_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT 1,
  `level_id` int(11) DEFAULT NULL,
  `current_score` int(11) DEFAULT 0,
  `questions_answered` int(11) DEFAULT 0,
  `questions_correct` int(11) DEFAULT 0,
  `level_completed` tinyint(1) DEFAULT 0,
  `completion_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_progress`
--

INSERT INTO `user_progress` (`progress_id`, `user_id`, `level_id`, `current_score`, `questions_answered`, `questions_correct`, `level_completed`, `completion_time`, `created_at`) VALUES
(1, 1, 1, 45, 5, 4, 1, '2025-06-29 09:00:00', '2025-06-29 13:49:06'),
(2, 2, 1, 80, 5, 3, 0, '2025-06-29 09:28:01', '2025-06-29 13:49:06'),
(3, 1, 2, 10, 2, 1, 0, NULL, '2025-06-29 13:49:06'),
(5, 2, 2, 71, 7, 5, 1, '2025-06-29 10:22:20', '2025-06-29 15:28:37'),
(7, 3, 1, 80, 5, 3, 1, '2025-07-03 09:04:00', '2025-07-03 15:04:00'),
(8, 3, 2, 100, 7, 1, 1, NULL, '2025-07-03 15:04:45'),
(34, 3, 4, 100, 5, 5, 1, '2025-07-10 00:08:08', '2025-07-10 06:08:08'),
(36, 2, 4, 100, 5, 5, 1, '2025-07-10 12:17:32', '2025-07-10 18:17:32'),
(37, 2, 5, 100, 5, 5, 1, '2025-07-10 12:17:59', '2025-07-10 18:17:59'),
(44, 3, 5, 100, 5, 5, 1, '2025-07-10 22:25:35', '2025-07-11 04:25:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`category_id`,`level_id`);

--
-- Indexes for table `powerups`
--
ALTER TABLE `powerups`
  ADD PRIMARY KEY (`powerup_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone_number` (`phone_number`);

--
-- Indexes for table `user_answers`
--
ALTER TABLE `user_answers`
  ADD PRIMARY KEY (`answer_id`);

--
-- Indexes for table `user_points`
--
ALTER TABLE `user_points`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_powerups`
--
ALTER TABLE `user_powerups`
  ADD PRIMARY KEY (`user_id`,`powerup_id`);

--
-- Indexes for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD PRIMARY KEY (`progress_id`),
  ADD UNIQUE KEY `unique_user_level` (`user_id`,`level_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `powerups`
--
ALTER TABLE `powerups`
  MODIFY `powerup_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_answers`
--
ALTER TABLE `user_answers`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_progress`
--
ALTER TABLE `user_progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;





-- My Forensics Questions Table
CREATE TABLE IF NOT EXISTS `my_forensics_questions` (
  question_id INT AUTO_INCREMENT PRIMARY KEY,
  question_text TEXT NOT NULL,
  description TEXT,
  question_type ENUM('MCQ', 'ShortAnswer', 'LongAnswer') NOT NULL,
  options JSON,
  correct_answer TEXT,
  difficulty ENUM('Beginner','Intermediate','Advanced') DEFAULT 'Beginner'
);

-- My Cryptography Questions Table
CREATE TABLE IF NOT EXISTS `my_crypto_questions` (
  question_id INT AUTO_INCREMENT PRIMARY KEY,
  question_text TEXT NOT NULL,
  description TEXT,
  question_type ENUM('MCQ', 'ShortAnswer', 'LongAnswer') NOT NULL,
  options JSON,
  correct_answer TEXT,
  difficulty ENUM('Beginner','Intermediate','Advanced') DEFAULT 'Beginner'
);

-- ACCESS BASED CONTROL --
ALTER TABLE users 
ADD COLUMN user_role ENUM('student','admin') DEFAULT 'student' AFTER phone_number;
