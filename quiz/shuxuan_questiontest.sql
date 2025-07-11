-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 11, 2025 at 07:51 AM
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
-- Database: `questiontest`
--

-- --------------------------------------------------------

--
-- Table structure for table `python`
--

CREATE TABLE `python` (
  `python_id` int(11) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `example` text DEFAULT NULL,
  `question` text DEFAULT NULL,
  `answer` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `python`
--

INSERT INTO `python` (`python_id`, `topic`, `content`, `example`, `question`, `answer`) VALUES
(1, 'Strings', 'Strings are defined by enclosing the sequence of characters within either single quotes, double quotes, or triple quotes in a print() function.', 'print(\"Hello World\")', 'Print a string.', NULL),
(2, 'Variables', 'Variables are symbolic names that act as containers for storing data values. Variables are created the moment you first assign a value to them using the assignment operator (=).', 'age = 30//name = \"John\"', 'Assign a variable, x ,a value and print it out.', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `python_subtopics`
--

CREATE TABLE `python_subtopics` (
  `subtopic_id` int(11) NOT NULL,
  `python_id` int(11) NOT NULL,
  `subtopic_title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `example` text DEFAULT NULL,
  `question` text DEFAULT NULL,
  `answer` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `python_subtopics`
--

INSERT INTO `python_subtopics` (`subtopic_id`, `python_id`, `subtopic_title`, `content`, `example`, `question`, `answer`) VALUES
(1, 2, 'Variable Names', 'A variable can have a short name (like x and y) or a more descriptive name (age, carname, total_volume).//Rules for Python variables://A variable name must start with a letter or the underscore character//A variable name cannot start with a number//A variable name can only contain alpha-numeric characters and underscores (A-z, 0-9, and _ )//Variable names are case-sensitive (age, Age and AGE are three different variables)//A variable name cannot be any of the Python keywords.', 'Legal variable names://myvar = \"John\"//my_var = \"John\"//_my_var = \"John\"//myVar = \"John\"//MYVAR = \"John\"//myvar2 = \"John\"////Illegal variable names://2myvar = \"John\"//my-var = \"John\"//my var = \"John\"', NULL, NULL),
(2, 2, 'Assign Multiple Values', 'Python allows you to assign values to multiple variables in one line.', 'x, y, z = \"Orange\", \"Banana\", \"Cherry\"//print(x)//print(y)//print(z)', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `question _id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `description` text DEFAULT NULL,
  `question_type` enum('MCQ','ShortAnswer','LongAnswer') NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `correct_answer` text DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`question _id`, `question_text`, `description`, `question_type`, `options`, `correct_answer`, `is_completed`) VALUES
(1, 'What is the correct answer?', NULL, 'MCQ', '[\"Monkey\", \"Not This\", \"Not This\", \"Not This\"]', 'Monkey', 0),
(2, 'Cryptography can be easy, do you know what ROT13 is?', 'cvpbPGS{arkg_gvzr_V\'yy_gel_2_ebhaqf_bs_ebg13_nSkgmDJE}', 'LongAnswer', NULL, 'picoCTF{next_time_I\'ll_try_2_rounds_of_rot13_aFxtzQWR}', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(9) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiration` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `phone_number`, `password_hash`, `reset_token`, `reset_token_expiration`) VALUES
(1, 'Student', 'student@gmail.com', '99992222', 'passwordhash', NULL, NULL),
(2, 'test', 'test@gmail.com', '9988', '$2y$10$w3usEOEIzWfI9diruVvcReKM/SzKvIH0s86/qrw8P5u/OoJ2zETO2', NULL, NULL),
(3, 'test1', 'test1@gmail.com', '67676767', '$2y$10$Csgk5Wosi6g8RZgYw5JUdeDK9hzHrpv9w8clZ6oQQCH8AEsys/OnW', NULL, NULL),
(4, 'test2', 'test2@gmail.com', '12345678', '$2y$10$77d/mRMWdaK2Ji/iChaFrOFyJnaNWEovsAjgYtVTG8/gkHBjhzvGy', NULL, NULL),
(5, 'lol', 'lol@gmail.com', '1111 222', '$2y$10$yvsMD8oTufVeeRAzM.uKc.ge7lAuwp/yb.PRn8mxx6O2fp4F1A8uW', NULL, NULL),
(6, 'tester', 'tester@gmail.com', '2345 2345', '$2y$10$5mfobA67t0Y6AoM3a/DeBeiIJne/aAMijjYg47VgM7wzSgQqg6/b6', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `python`
--
ALTER TABLE `python`
  ADD PRIMARY KEY (`python_id`);

--
-- Indexes for table `python_subtopics`
--
ALTER TABLE `python_subtopics`
  ADD PRIMARY KEY (`subtopic_id`),
  ADD KEY `python_id` (`python_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question _id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `Email` (`email`),
  ADD UNIQUE KEY `PhoneNumber` (`phone_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `python`
--
ALTER TABLE `python`
  MODIFY `python_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `python_subtopics`
--
ALTER TABLE `python_subtopics`
  MODIFY `subtopic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question _id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `python_subtopics`
--
ALTER TABLE `python_subtopics`
  ADD CONSTRAINT `python_subtopics_ibfk_1` FOREIGN KEY (`python_id`) REFERENCES `python` (`python_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
