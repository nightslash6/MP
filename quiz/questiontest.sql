SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
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

INSERT INTO `users` (`name`, `email`, `phone_number`, `password_hash`) 
VALUES 
('Student', 'student@gmail.com', '99992222', 'passwordhash');


CREATE TABLE IF NOT EXISTS `questions` (
    `question_id` INT(11) NOT NULL AUTO_INCREMENT,
    `question_text` TEXT NOT NULL,
    `description` TEXT DEFAULT NULL,
    `question_type` ENUM('MCQ', 'ShortAnswer', 'LongAnswer') NOT NULL,
    `options` JSON DEFAULT NULL,    
    `correct_answer` TEXT DEFAULT NULL,
    `is_completed` TINYINT(1) DEFAULT 0,
    PRIMARY KEY (`question_id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `questions` (`question_text`, `description`, `question_type`, `options`, `correct_answer`)
VALUES 
('What is the correct answer?', NULL, 'MCQ', '["Monkey", "Not This", "Not This", "Not This"]', 'Monkey');

INSERT INTO `questions` (`question_text`, `description`, `question_type`, `options`, `correct_answer`)
VALUES 
('Cryptography can be easy, do you know what ROT13 is?', "cvpbPGS{arkg_gvzr_V'yy_gel_2_ebhaqf_bs_ebg13_nSkgmDJE}", 'LongAnswer', NULL, "picoCTF{next_time_I'll_try_2_rounds_of_rot13_aFxtzQWR}")

CREATE TABLE IF NOT EXISTS `questions` (
    `QuestionID` INT(11) NOT NULL AUTO_INCREMENT,
    `QuestionText` TEXT NOT NULL,
    `Description` TEXT DEFAULT NULL,
    `QuestionType` ENUM('MCQ', 'ShortAnswer', 'LongAnswer') NOT NULL,
    `Options` JSON DEFAULT NULL,    
    `CorrectAnswer` TEXT DEFAULT NULL,
    PRIMARY KEY (`QuestionID`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- ACCESS BASED CONTROL --
ALTER TABLE users
ADD COLUMN user_role ENUM('student','admin') DEFAULT 'student' AFTER phone_number;