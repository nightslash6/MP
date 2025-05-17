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
  `phone_number` VARCHAR(9) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
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