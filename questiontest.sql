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

--CTF Challenges--
CREATE TABLE challenges (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(100),
  category VARCHAR(50),
  difficulty VARCHAR(20),
  description TEXT,
  flag VARCHAR(100),
  solves INT DEFAULT 0
  question VARCHAR(100),
);

CREATE TABLE solves (
  user_id INT,
  challenge_id INT,
  UNIQUE(user_id, challenge_id)
);

CREATE TABLE users1 (
    id INT PRIMARY KEY,
    username VARCHAR(100),
    email VARCHAR(100),
    flag TEXT
);

INSERT INTO users1 (id, username, email, flag) VALUES
(1, 'admin', 'admin@cybersite.local', 'cybersite{predictable_ids_are_dangerous}'),
(2, 'user1', 'user1@cybersite.local', NULL),
(3, 'user2', 'user2@cybersite.local', NULL);


-- Insert sample challenge
INSERT INTO challenges (title, category, difficulty, description, flag, solves)
VALUES
("Simple SQLi", "Web Exploitation", "Easy", "Bypass the login using SQL injection.", "flag{sql_works}", 35);

INSERT INTO challenges (title, category, difficulty,)
VALUES 
-- Challenge 1: CSRF
('Steal the Session', 'Web Exploitation', 'Medium'),

-- Challenge 2: Broken Access Control
('Admin Bypass', 'Web Exploitation', 'Hard');

INSERT INTO challenges (title, difficulty, category, flag, description, solves, question) VALUES
('CSRF Token Bypass', 'Easy', 'Web Exploitation', 'cybersite{csrf_token_not_verified}', 'Craft a POST request without CSRF token.', 5, 'The site uses CSRF protection. Can you find a way to submit a request without the token?'),
('Guess the ID', 'Easy', 'Web Exploitation', 'cybersite{predictable_ids_are_dangerous}', 'Use IDOR to access unauthorized data.', 94, 'User IDs are numeric and predictable. Can you access someone else's profile?'),
('Upload Madness', 'Easy', 'Web Exploitation', 'cybersite{webshell_uploaded}', 'Upload a web shell bypassing file validation.', 63, 'You can upload files, but not all types. Can you upload something malicious?'),
('Robot Spy', 'Easy', 'Web Exploitation', 'cybersite{robots_txt_reveals_secret}', 'Access hidden directory listed in robots.txt.', 74, 'Robots.txt isn’t meant for your eyes... but maybe it hides something interesting?'),
('View Source', 'Easy', 'Web Exploitation', 'cybersite{always_view_source}', 'Reveal flag in source code comment.', 87, 'Everything looks normal — but have you checked the source code of the page?'),
('Hidden Files', 'Easy', 'Web Exploitation', 'cybersite{.git_exposed}', 'Access `.git/` or backup files for flag.', 54, 'There might be leftover developer files or backups. Can you find and exploit them?'),
('Include Me', 'Easy', 'Web Exploitation', 'cybersite{lfi_pwned}', 'Exploit LFI to read sensitive files.', 96, 'Can you trick the site into loading local files on the server? (LFI Challenge)'),
('Auth Skip', 'Easy', 'Web Exploitation', 'cybersite{bypass_logic_flaw}', 'Bypass login using SQL logic flaw.', 250, 'Bypass the login logic using a simple trick — no credentials needed.'),
('JavaScript Ninja', 'Easy', 'Web Exploitation', 'cybersite{js_debugger_reveals_flag}', 'Use browser dev tools to find JS variable.', 123, 'Inspect the browser console and debug tools — maybe there's something hiding in JS?');

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
(5, 'Strings', 'Strings are defined by enclosing the sequence of characters within either single quotes, double quotes, or triple quotes in a print() function. ', 'print(\"Hello World\")', 'Print a string.', ''),
(7, 'Variables', 'Variables are symbolic names that act as containers for storing data values. Variables are created the moment you first assign a value to them using the assignment operator (=).      \r\n', 'age = 30\r\nname = \"John\"', 'Assign a variable, x ,a value and print it out.', ''),
(8, 'Conditions', 'Python supports the usual logical conditions from mathematics.\r\nEquals: a == b\r\nNot Equals: a != b\r\nLess than: a < b\r\nLess than or equal to: a <= b\r\nGreater than: a > b\r\nGreater than or equal to: a >= b\r\nThese conditions can be used in several ways, most commonly in \"if statements\" and loops.', '', '', '');

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
(4, 7, 'Variable Names', 'A variable can have a short name (like x and y) or a more descriptive name (age, carname, total_volume).\r\nRules for Python variables:\r\n-A variable name must start with a letter or the underscore character\r\n-A variable name cannot start with a number\r\n-A variable name can only contain alpha-numeric characters and underscores (A-z, 0-9, and _ )\r\n-Variable names are case-sensitive (age, Age and AGE are three different variables)\r\n-A variable name cannot be any of the Python keywords.    ', 'Legal variable names:\r\nmyvar = \"John\"\r\nmy_var = \"John\"\r\n_my_var = \"John\"\r\nmyVar = \"John\"\r\nMYVAR = \"John\"\r\nmyvar2 = \"John\"\r\n\r\nIllegal variable names:\r\n2myvar = \"John\"\r\nmy-var = \"John\"\r\nmy var = \"John\"\r\n', '', ''),
(5, 7, 'Assign Multiple Values', 'Python allows you to assign values to multiple variables in one line.', 'x, y, z = \"Orange\", \"Banana\", \"Cherry\"\r\nprint(x)\r\nprint(y)\r\nprint(z)', '', ''),
(7, 5, 'String Concatenation', 'To concatenate, or combine, two strings you can use the + operator.', 'Merge variable a with variable b into variable c:\r\na = \"Hello\"\r\nb = \"World\"\r\nc = a + b\r\nprint(c)\r\n\r\nTo add a space between them, add a \" \":\r\na = \"Hello\"\r\nb = \"World\"\r\nc = a + \" \" + b\r\nprint(c)', '', ''),
(11, 8, 'If statements', 'An \"if statement\" is written by using the if keyword.', 'a = 33\r\nb = 200\r\nif b > a:\r\n  print(\"b is greater than a\")', '', ''),
(12, 8, 'Indentation', 'Python relies on indentation (whitespace at the beginning of a line) to define scope in the code. Other programming languages often use curly-brackets for this purpose.', 'If statement, without indentation (will raise an error):\r\na = 33\r\nb = 200\r\nif b > a:\r\nprint(\"b is greater than a\") # you will get an error', '', '');

-- --------------------------------------------------------

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
-- Constraints for table `python_subtopics`
--
ALTER TABLE `python_subtopics`
  ADD CONSTRAINT `python_subtopics_ibfk_1` FOREIGN KEY (`python_id`) REFERENCES `python` (`python_id`);
COMMIT;