-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 04, 2025 at 12:51 PM
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
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `category_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `category_description`, `created_at`) VALUES
(1, 'CyberSecurity', 'Questions related to Cyber Security topics.', '2025-06-29 05:49:06'),
(2, 'Forensics', 'Questions related to Forensics topics', '2025-07-03 06:59:22');

-- --------------------------------------------------------

--
-- Table structure for table `challenges`
--

CREATE TABLE `challenges` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `difficulty` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `flag` varchar(100) DEFAULT NULL,
  `solves` int(11) DEFAULT 0,
  `question` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `challenges`
--

INSERT INTO `challenges` (`id`, `title`, `category`, `difficulty`, `description`, `flag`, `solves`, `question`) VALUES
(1, 'Simple SQLi', 'Web Exploitation', 'Easy', 'Bypass the login using SQL injection.', 'flag{sql_works}', 35, NULL),
(2, 'Steal the Session', 'Web Exploitation', 'Medium', NULL, NULL, 0, NULL),
(3, 'Admin Bypass', 'Web Exploitation', 'Hard', NULL, NULL, 0, NULL),
(4, 'CSRF Token Bypass', 'Web Exploitation', 'Easy', 'Craft a POST request without CSRF token.', 'cybersite{csrf_token_not_verified}', 5, 'The site uses CSRF protection. Can you find a way to submit a request without the token?'),
(5, 'Guess the ID', 'Web Exploitation', 'Easy', 'Use IDOR to access unauthorized data.', 'cybersite{predictable_ids_are_dangerous}', 94, 'User IDs are numeric and predictable. Can you access someone else\'s profile?'),
(6, 'Upload Madness', 'Web Exploitation', 'Easy', 'Upload a web shell bypassing file validation.', 'cybersite{webshell_uploaded}', 63, 'You can upload files, but not all types. Can you upload something malicious?'),
(7, 'Robot Spy', 'Web Exploitation', 'Easy', 'Access hidden directory listed in robots.txt.', 'cybersite{robots_txt_reveals_secret}', 74, 'Robots.txt isn’t meant for your eyes... but maybe it hides something interesting?'),
(8, 'View Source', 'Web Exploitation', 'Easy', 'Reveal flag in source code comment.', 'cybersite{always_view_source}', 87, 'Everything looks normal — but have you checked the source code of the page?'),
(9, 'Hidden Files', 'Web Exploitation', 'Easy', 'Access `.git/` or backup files for flag.', 'cybersite{.git_exposed}', 54, 'There might be leftover developer files or backups. Can you find and exploit them?'),
(10, 'Include Me', 'Web Exploitation', 'Easy', 'Exploit LFI to read sensitive files.', 'cybersite{lfi_pwned}', 96, 'Can you trick the site into loading local files on the server? (LFI Challenge)'),
(11, 'Auth Skip', 'Web Exploitation', 'Easy', 'Bypass login using SQL logic flaw.', 'cybersite{bypass_logic_flaw}', 250, 'Bypass the login logic using a simple trick — no credentials needed.'),
(12, 'JavaScript Ninja', 'Web Exploitation', 'Easy', 'Use browser dev tools to find JS variable.', 'cybersite{js_debugger_reveals_flag}', 123, 'Inspect the browser console and debug tools — maybe there\'s something hiding in JS?');

-- --------------------------------------------------------

--
-- Table structure for table `levels`
--

CREATE TABLE `levels` (
  `category_id` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `level_name` varchar(255) NOT NULL,
  `level_description` text DEFAULT NULL,
  `required_score` int(11) DEFAULT 40,
  `questions_count` int(11) DEFAULT 5,
  `unlock_previous_level` int(11) DEFAULT NULL,
  `badge_icon` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `my_crypto_questions`
--

CREATE TABLE `my_crypto_questions` (
  `question_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `description` text DEFAULT NULL,
  `question_type` enum('MCQ','ShortAnswer','LongAnswer') NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `correct_answer` text DEFAULT NULL,
  `difficulty` enum('Beginner','Intermediate','Advanced') DEFAULT 'Beginner'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `my_forensics_questions`
--

CREATE TABLE `my_forensics_questions` (
  `question_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `description` text DEFAULT NULL,
  `question_type` enum('MCQ','ShortAnswer','LongAnswer') NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `correct_answer` text DEFAULT NULL,
  `difficulty` enum('Beginner','Intermediate','Advanced') DEFAULT 'Beginner'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `powerups`
--

CREATE TABLE `powerups` (
  `powerup_id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cost` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `powerups`
--

INSERT INTO `powerups` (`powerup_id`, `name`, `description`, `cost`) VALUES
(1, '50/50', 'Removes two incorrect options from a multiple-choice question.', 20),
(2, 'Second Chance', 'Allows another attempt after a wrong answer.', 30),
(3, 'Skip Question', 'Lets you skip a difficult question.', 15),
(4, 'Time Freeze', 'Pauses the timer for 10 seconds.', 25),
(5, 'Reveal Hint', 'Shows a helpful hint for the current question.', 10);

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
(1, 'Strings', 'Strings are defined by enclosing the sequence of characters within either single quotes, double quotes, or triple quotes in a print() function.', 'print(\"I love python!\")', 'Print a string.', ''),
(2, 'Variables', 'Variables are symbolic names that act as containers for storing data values. Variables are created the moment you first assign a value to them using the assignment operator (=).', 'age = 30\r\nname = \"John\"', 'Assign variable \"x\" a value and print it out.', ''),
(3, 'Conditions', 'Python supports the usual logical conditions from mathematics. \r\nEquals: a == b\r\nNot Equals: a != b\r\nLess than: a < b\r\nLess than or equal to: a <= b\r\nGreater than: a > b\r\nGreater than or equal to: a >= b\r\nThese conditions can be used in several ways, most commonly in \"if statements\" and loops.', '', '', '');

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
(1, 1, 'String Concatenation', 'To concatenate, or combine, two strings you can use the + operator.', 'Merge variable a with variable b into variable c:\r\na = \"Hello\"\r\nb = \"World\"\r\nc = a + b\r\nprint(c)\r\n\r\nTo add a space between them, add a \" \":\r\na = \"Hello\"\r\nb = \"World\"\r\nc = a + \" \" + b\r\nprint(c)', 'Using string concatenation, assign 4 variables and print \"I Love Python!\".', ''),
(2, 2, 'Variable Names', 'A variable can have a short name (like x and y) or a more descriptive name (age, carname, total_volume). \r\nRules for Python variables:\r\n-A variable name must start with a letter or the underscore character\r\n-A variable name cannot start with a number\r\n-A variable name can only contain alpha-numeric characters and underscores (A-z, 0-9, and _ )\r\n-Variable names are case-sensitive (age, Age and AGE are three different variables)\r\n-A variable name cannot be any of the Python keywords', 'Legal variable names:\r\nmyvar = \"John\"\r\nmy_var = \"John\"\r\nmyVar = \"John\"\r\nMYVAR = \"John\"\r\nmyvar2 = \"John\"\r\n\r\nIllegal variable names:\r\n2myvar = \"John\"\r\nmy-var = \"John\"\r\nmy var = \"John\"', '', ''),
(3, 2, 'Assign Multiple Values', 'Python allows you to assign values to multiple variables in one line.', 'x, y, z = \"Orange\", \"Banana\", \"Cherry\"\r\nprint(x)\r\nprint(y)\r\nprint(z)', '', ''),
(4, 3, 'If statements', 'An \"if statement\" is written by using the \"if\" keyword.', 'a = 33\r\nb = 200\r\nif b > a:\r\n   print(\"b is greater than a\")', '', ''),
(5, 3, 'Indentation', 'Python relies on indentation (whitespace at the beginning of a line) to define scope in the code. Other programming languages often use curly-brackets for this purpose.\r\nTo add indentation, you can press \"tab\" on your keyboard, or simply press the space bar three times. Indentation is normally added automatically upon pressing enter to proceed to the next line when writing \"if\" statements.', 'If statement, without indentation (will raise an error):\r\na = 33\r\nb = 200\r\nif b > a:\r\nprint(\"b is greater than a\") # you will get an error', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `solves`
--

CREATE TABLE `solves` (
  `user_id` int(11) DEFAULT NULL,
  `challenge_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(8) NOT NULL,
  `user_role` enum('student','admin') DEFAULT 'student',
  `password_hash` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiration` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `phone_number`, `user_role`, `password_hash`, `reset_token`, `reset_token_expiration`) VALUES
(1, 'admin101', 'admin@gmail.com', '98769876', 'admin', '$2y$10$PQTTnPSVX5V.JthPPzDqm.TIH8O/pVSrXgfQSDAnSIW.6zSh5UZoe', NULL, NULL),
(2, 'student', 'student@gmail.com', '90909090', 'student', '$2y$10$/LW2s5lFwBt47efdbQL.Wuo9ctTErw0DQLnSoZQ5UtxlnQ/HyxmbS', NULL, NULL);

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `insert_user_points_after_user` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    INSERT INTO user_points (user_id, points)
    VALUES (NEW.user_id, 0);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users1`
--

CREATE TABLE `users1` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `flag` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users1`
--

INSERT INTO `users1` (`id`, `username`, `email`, `flag`) VALUES
(1, 'admin', 'admin@cybersite.local', 'cybersite{predictable_ids_are_dangerous}'),
(2, 'user1', 'user1@cybersite.local', NULL),
(3, 'user2', 'user2@cybersite.local', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_answers`
--

CREATE TABLE `user_answers` (
  `answer_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT 1,
  `question_id` int(11) DEFAULT NULL,
  `selected_answer` text DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `points_earned` int(11) DEFAULT 0,
  `answered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_answers`
--

INSERT INTO `user_answers` (`answer_id`, `user_id`, `question_id`, `selected_answer`, `is_correct`, `points_earned`, `answered_at`) VALUES
(1, 1, 1, 'A', 1, 10, '2025-06-29 05:49:06'),
(2, 1, 2, 'C', 0, 0, '2025-06-29 05:49:06'),
(3, 2, 1, 'B', 0, 0, '2025-06-29 05:49:06'),
(4, 2, 3, 'D', 1, 10, '2025-06-29 05:49:06');

-- --------------------------------------------------------

--
-- Table structure for table `user_points`
--

CREATE TABLE `user_points` (
  `user_id` int(11) NOT NULL,
  `points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_points`
--

INSERT INTO `user_points` (`user_id`, `points`) VALUES
(2, 25),
(3, 60),
(4, 275),
(5, 30);

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
(3, 5, 0),
(4, 1, 0),
(4, 3, 0),
(5, 1, 0),
(5, 5, 1);

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
(1, 1, 1, 45, 5, 4, 1, '2025-06-29 01:00:00', '2025-06-29 05:49:06'),
(2, 2, 1, 80, 5, 3, 0, '2025-06-29 01:28:01', '2025-06-29 05:49:06'),
(3, 1, 2, 10, 2, 1, 0, NULL, '2025-06-29 05:49:06'),
(5, 2, 2, 71, 7, 5, 1, '2025-06-29 02:22:20', '2025-06-29 07:28:37'),
(7, 3, 1, 80, 5, 3, 1, '2025-07-03 01:04:00', '2025-07-03 07:04:00'),
(8, 3, 2, 100, 7, 1, 1, NULL, '2025-07-03 07:04:45'),
(34, 3, 4, 100, 5, 5, 1, '2025-07-09 16:08:08', '2025-07-09 22:08:08'),
(36, 2, 4, 100, 5, 5, 1, '2025-07-10 04:17:32', '2025-07-10 10:17:32'),
(37, 2, 5, 100, 5, 5, 1, '2025-07-10 04:17:59', '2025-07-10 10:17:59'),
(44, 3, 5, 100, 5, 5, 1, '2025-07-10 14:25:35', '2025-07-10 20:25:35'),
(45, 5, 1, 100, 5, 5, 1, '2025-07-15 03:04:46', '2025-07-15 09:04:46'),
(46, 5, 4, 100, 5, 0, 1, NULL, '2025-07-17 08:38:07'),
(48, 4, 4, 100, 5, 5, 1, '2025-07-22 20:21:50', '2025-07-23 02:21:50'),
(50, 4, 5, 100, 5, 5, 1, '2025-07-22 20:55:41', '2025-07-23 02:55:41'),
(51, 4, 6, 100, 6, 6, 1, '2025-07-22 20:56:14', '2025-07-23 02:56:14'),
(52, 4, 1, 80, 5, 4, 1, '2025-07-22 20:57:09', '2025-07-23 02:57:09'),
(53, 4, 2, 86, 7, 6, 1, '2025-07-23 03:45:40', '2025-07-23 09:45:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `challenges`
--
ALTER TABLE `challenges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`category_id`,`level_id`);

--
-- Indexes for table `my_crypto_questions`
--
ALTER TABLE `my_crypto_questions`
  ADD PRIMARY KEY (`question_id`);

--
-- Indexes for table `my_forensics_questions`
--
ALTER TABLE `my_forensics_questions`
  ADD PRIMARY KEY (`question_id`);

--
-- Indexes for table `powerups`
--
ALTER TABLE `powerups`
  ADD PRIMARY KEY (`powerup_id`);

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
-- Indexes for table `solves`
--
ALTER TABLE `solves`
  ADD UNIQUE KEY `user_id` (`user_id`,`challenge_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone_number` (`phone_number`);

--
-- Indexes for table `users1`
--
ALTER TABLE `users1`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `challenges`
--
ALTER TABLE `challenges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `my_crypto_questions`
--
ALTER TABLE `my_crypto_questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `my_forensics_questions`
--
ALTER TABLE `my_forensics_questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `powerups`
--
ALTER TABLE `powerups`
  MODIFY `powerup_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `python`
--
ALTER TABLE `python`
  MODIFY `python_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `python_subtopics`
--
ALTER TABLE `python_subtopics`
  MODIFY `subtopic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_answers`
--
ALTER TABLE `user_answers`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_progress`
--
ALTER TABLE `user_progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

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
